<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OrganizationUnits;
use App\Models\Role;
use App\Models\UserActivityLog;
use App\Models\Rating;
use App\Models\UserRating;
use App\Models\UserDocument;
use App\Models\OuRating;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ParentRating;


class UserController extends Controller
{

    public function getData(Request $request)
    {
        $authUser = auth()->user();
        $ou_id = $authUser->ou_id;
        $is_owner = $authUser->is_owner;
        $is_admin = $authUser->is_admin;

        $organizationUnits = OrganizationUnits::all();
        $roles = Role::all();
        $rating = Rating::where('status', 1)->get();


        $ou_id = auth()->user()->ou_id;
        if ($ou_id != null) {
            $rating = Rating::with(['ou_ratings.organization_unit'])->where('status', 1)->whereHas('ou_ratings')->get();
        }

        if ($request->ajax()) {
            $query = User::query()
                ->leftJoin('roles', 'users.role', '=', 'roles.id')
                ->leftJoin('organization_units', 'users.ou_id', '=', 'organization_units.id')
                ->select([
                    'users.id',
                    'users.image',
                    'users.fname',
                    'users.lname',
                    'users.email',
                    'roles.role_name as position',
                    'organization_units.org_unit_name as organization',
                    'users.status'
                ]);

            // Filter based on permissions
            if (!$is_owner) {
                if ($is_admin) {
                    // Admin: only users from same OU
                    $query->where('users.ou_id', $ou_id);
                } else {
                    // Normal user: only their own record
                    $query->where('users.id', $authUser->id);
                }
            }

            return DataTables::of($query)
                ->filterColumn('position', function ($query, $keyword) {
                    $query->where('roles.role_name', 'LIKE', "%{$keyword}%");
                })
                ->filterColumn('organization', function ($query, $keyword) {
                    $query->where('organization_units.org_unit_name', 'LIKE', "%{$keyword}%");
                })
                ->addColumn('status', function ($user) {
                    return $user->status == 1 ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->filterColumn('organization', function ($query, $keyword) {
                    $query->where('organization_units.org_unit_name', 'LIKE', "%{$keyword}%");
                })
                ->addColumn('status', function ($user) {
                    return $user->status == 1 ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) use ($is_owner, $is_admin) {
                    $viewUrl = url('users/show/' . encode_id($row->id));
                    $viewBtn = '';
                    $editBtn = '';
                    $delete = '';

                    if (checkAllowedModule('users', 'user.index')->isNotEmpty() && ($is_owner || $is_admin)) {
                        $viewBtn = '<a href="' . $viewUrl . '" class="view-icon" title="View User" 
                                      style="font-size:18px; cursor: pointer;">
                                      <i class="fa fa-eye text-danger me-2"></i>
                                   </a>';
                    }

                    if (checkAllowedModule('users', 'user.get')->isNotEmpty()) {
                        $editBtn = '<i class="fa fa-edit edit-user-icon text-primary me-2" 
                                          style="font-size:18px; cursor: pointer;" 
                                          data-user-id="' . encode_id($row->id) . '">
                                       </i>';
                    }


                    if (checkAllowedModule('users', 'user.destroy')->isNotEmpty() && ($is_owner || $is_admin)) {
                        $delete =  '<i class="fa-solid fa-trash delete-icon text-danger"  
                                      style="font-size:18px; cursor: pointer;" 
                                      data-user-id="' . encode_id($row->id) . '">
                                    </i>';
                    }

                    return $viewBtn . ' ' . $editBtn . ' ' . $delete;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('users.index', compact('roles', 'organizationUnits', 'rating'));
    }

    public function userData(Request $request)
    {
        if ($request->ajax()) {
            $query = User::query()->select([
                'users.id',
                'users.fname',
                'users.lname',
                'users.licence_required',
                'users.passport_required',
                'users.rating_required',
                'users.medical'
            ]);

            $query->orderBy('users.id', 'asc');

            return DataTables::of($query)
                ->addColumn('fullname', function ($user) {
                    return $user->fname . ' ' . $user->lname;
                })
                ->filterColumn('fullname', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(users.fname, ' ', users.lname) LIKE ?", ["%{$keyword}%"]);
                })
                ->orderColumn('fullname', function ($query, $order) {
                    $query->orderByRaw("CONCAT(users.fname, ' ', users.lname) {$order}");
                })
                ->addColumn('licence_required', function ($user) {
                    return $user->licence_required == 1
                        ? '<i class="bi bi-check text-success fs-4"></i>'
                        : '<i class="bi bi-x text-danger fs-4"></i>';
                })
                ->addColumn('passport_required', function ($user) {
                    return $user->passport_required == 1
                        ? '<i class="bi bi-check text-success fs-4"></i>'
                        : '<i class="bi bi-x text-danger fs-4"></i>';
                })
                ->addColumn('rating_required', function ($user) {
                    return $user->rating_required == 1
                        ? '<i class="bi bi-check text-success fs-4"></i>'
                        : '<i class="bi bi-x text-danger fs-4"></i>';
                })
                ->addColumn('medical', function ($user) {
                    return $user->medical == 1
                        ? '<i class="bi bi-check text-success fs-4"></i>'
                        : '<i class="bi bi-x text-danger fs-4"></i>';
                })
                ->rawColumns(['licence_required', 'passport_required', 'rating_required', 'medical'])
                ->make(true);
        }

        return view('users.document-table');
    }

    public function profile()
    {
        $id = auth()->id();

        $user = User::with([
            'usrRatings.rating',
            'usrRatings.parent',
            'documents'
        ])->findOrFail($id);
  $rawUserRatings = $user->usrRatings;

$grouped = [];

foreach ($rawUserRatings as $rating) {
    $linkedTo = $rating->linked_to ?? 'unlinked';

    if (is_null($rating->parent_id)) {
        // Rating is a parent
        $grouped[$linkedTo][$rating->rating_id]['parent'] = $rating;
    } else {
        // Rating is a child
        $parentId = $rating->parent_id;

        // Ensure group for this parent under this linked_to exists
        if (!isset($grouped[$linkedTo][$parentId])) {
            $grouped[$linkedTo][$parentId] = [];
        }

        // Add child
        $grouped[$linkedTo][$parentId]['children'][] = $rating;

        // If parent not already added from user_ratings, fetch from ratings table
        if (!isset($grouped[$linkedTo][$parentId]['parent'])) {
            $parentRatingModel = \App\Models\Rating::find($parentId);
            if ($parentRatingModel) {
                // Wrap it in a fake UserRating instance (so your view works the same way)
                $fakeParent = new \App\Models\UserRating([
                    'rating_id' => $parentRatingModel->id,
                    'parent_id' => null,
                    'linked_to' => $linkedTo,
                ]);
                $fakeParent->setRelation('rating', $parentRatingModel);

                $grouped[$linkedTo][$parentId]['parent'] = $fakeParent;
            }
        }
    }
}


    //  dd($grouped);
        // Fix: Treat parent-only entries (rating_id is NULL, parent_id is set)
        $userRatings = $rawUserRatings->filter(function ($ur) {
            return $ur->rating_id !== null || $ur->parent_id !== null;
        });

        // Normalize all ratings: if rating_id is null but parent_id exists, we use parent_id as rating_id
        foreach ($userRatings as $ur) {
            if ($ur->rating_id === null && $ur->parent_id !== null) {
                $ur->rating_id = $ur->parent_id;
                $ur->rating = $ur->parent; // Assign parent as the rating object
            }
        }

        // Filter by licence_1
        $licence1Ratings = $userRatings->filter(function ($ur) use ($userRatings) {
            if ($ur->linked_to === 'licence_1') return true;

            $parent = $userRatings->firstWhere('rating_id', $ur->parent_id);
            return $parent && $parent->linked_to === 'licence_1';
        })->values();

        // Filter by licence_2
        $licence2Ratings = $userRatings->filter(function ($ur) use ($userRatings) {
            if ($ur->linked_to === 'licence_2') return true;

            $parent = $userRatings->firstWhere('rating_id', $ur->parent_id);
            return $parent && $parent->linked_to === 'licence_2';
        })->values();

        // Get selected rating IDs
        $selectedIdsLicence1 = $licence1Ratings->pluck('rating_id')->unique();
        $selectedIdsLicence2 = $licence2Ratings->pluck('rating_id')->unique();

        // Determine parent IDs for each licence
        $parentIdsLicence1 = $licence1Ratings
            ->pluck('rating.parent_id')
            ->merge($licence1Ratings->pluck('rating_id')->filter(fn($id) => is_null(optional($userRatings->firstWhere('rating_id', $id))->rating->parent_id)))
            ->unique()->values();

        $parentIdsLicence2 = $licence2Ratings
            ->pluck('rating.parent_id')
            ->merge($licence2Ratings->pluck('rating_id')->filter(fn($id) => is_null(optional($userRatings->firstWhere('rating_id', $id))->rating->parent_id)))
            ->unique()->values();

        // Missing parent ratings (not saved but required)
        $existingUserRatingIds = $userRatings->pluck('rating_id')->filter();
        $missingParentIdsLicence1 = $parentIdsLicence1->diff($existingUserRatingIds);
        $missingParentIdsLicence2 = $parentIdsLicence2->diff($existingUserRatingIds);

        $missingParentRatingsLicence1 = Rating::whereIn('id', $missingParentIdsLicence1)->get();
        $missingParentRatingsLicence2 = Rating::whereIn('id', $missingParentIdsLicence2)->get();

        // Group child ratings
        $childRatingsGrouped = ParentRating::with('child')->get()->groupBy('parent_id');

        // Map for quick access to userRating data
        $userRatingsMap = $userRatings->keyBy('rating_id');

        // Ratings list (not used in your view, but kept if needed)
        $ratings = $userRatings->pluck('rating');

        return view('users.profile', compact(
            'user',
            'ratings',
            'licence1Ratings',
            'licence2Ratings',
            'selectedIdsLicence1',
            'selectedIdsLicence2',
            'childRatingsGrouped',
            'userRatingsMap',
            'parentIdsLicence1',
            'parentIdsLicence2',
            'missingParentRatingsLicence1',
            'missingParentRatingsLicence2',
            'grouped'
        ));
    }


    public function profileUpdate(Request $request)
    {
          $issueDates = $request->input('issue_date', []);
          $expiry_date = $request->input('expiry_date', []);
          
          foreach ($issueDates as $val  ) {
                $ratingId     = $val['id'] ?? null;
                $userId       = $val['user_id'] ?? null;
                $parentid     = $val['parentid'] ?? null;
                $linkedTo     = $val['linked_to'] ?? null;
                $issueDate   = $val['issue_date'] ?? null;


            $update_issue_date_data  = array(
                "parentId"   => $val['parentid'] ?? null,
                "issue_date"  => $val['issue_date'] ?? null,
                "userId"     => $val['user_id']  ?? null,
                "linkedTo"   => $val['linked_to'] ?? null
            );
            if ($userId && $issueDate) {
            UserRating::where('user_id', $userId)->where('parent_id', $parentid)->where('linked_to', $linkedTo)->update(["issue_date" => $issueDate]);
            }
        }

        foreach($expiry_date as $val){ 
            // dd($val);
             $ratingId   = $val['id'] ?? NULL; 
             $userId     = $val['user_id'] ?? NULL ;  
             $expiry_date  = $val['expiry_date'] ?? NULL;
             $parentid     = $val['parentid'] ?? null;
            // dd($expiry_date);
             $linkedTo     = $val['linked_to'] ?? null;

            $update_expiry_data = array(
                "ratingId"   => $val['id'] ?? null,
                "parentId"   => $val['parentid'] ?? null,
                "expiry_date"  => $val['expiry_date'] ?? null,
                "userId"     => $val['user_id'] ?? NULL,
                "linkedTo"   => $val['linked_to'] ?? null
            );
         
            if ($userId && $expiry_date) {
                UserRating::where('user_id', $userId)->where('parent_id', $parentid)->where('linked_to', $linkedTo)->update(["expiry_date" => $expiry_date]);

            }
          
        }

        //------------------------------------------------------------------------------------
        // Licence 2
        $issue_date_licence2 = $request->input('issue_date_licence2', []);
                foreach ($issue_date_licence2 as $val  ) {
                $ratingId     = $val['id'] ?? null;
                $userId       = $val['user_id'] ?? null;
                $parentid     = $val['parentid'] ?? null;
                $linkedTo     = $val['linked_to'] ?? null;
                $issueDate   = $val['issue_date'] ?? null;


            $update_issue_date_data  = array(
                "parentId"   => $val['parentid'] ?? null,
                "issue_date"  => $val['issue_date'] ?? null,
                "userId"     => $val['user_id']  ?? null,
                "linkedTo"   => $val['linked_to'] ?? null
            );
            if ($userId && $issueDate) {
            UserRating::where('user_id', $userId)->where('parent_id', $parentid)->where('linked_to', $linkedTo)->update(["issue_date" => $issueDate]);
            }
        }

          $expiry_date_licence2 = $request->input('expiry_date_licence2', []);
             foreach($expiry_date_licence2 as $val){ 
            // dd($val);
             $ratingId   = $val['id'] ?? NULL; 
             $userId     = $val['user_id'] ?? NULL ;  
             $expiry_date  = $val['expiry_date'] ?? NULL;
             $parentid     = $val['parentid'] ?? null;
            // dd($expiry_date);
             $linkedTo     = $val['linked_to'] ?? null;

            $update_expiry_data = array(
                "ratingId"   => $val['id'] ?? null,
                "parentId"   => $val['parentid'] ?? null,
                "expiry_date"  => $val['expiry_date'] ?? null,
                "userId"     => $val['user_id'] ?? NULL,
                "linkedTo"   => $val['linked_to'] ?? null
            );
         
            if ($userId && $expiry_date) {
                UserRating::where('user_id', $userId)->where('parent_id', $parentid)->where('linked_to', $linkedTo)->update(["expiry_date" => $expiry_date]);

            }
          
        }

       

        $userToUpdate = User::find($request->id);
        $document = UserDocument::where('user_id', $userToUpdate->id)->first();
        // dd($request->has('non_expiring_licence'));
        if ($userToUpdate) {
            $rules = [];

            $licenceFileUploaded = $document?->licence_file_uploaded ?? false;

            $licenceFileUploaded = false;

            if ($userToUpdate->licence_required == 1) {
                if ($request->hasFile('licence_file')) {
                    if ($document && $document->licence_file) {
                        Storage::disk('public')->delete($document->licence_file);
                    }

                    $licenceFilePath = $request->file('licence_file')->store('licence_files', 'public');
                    $licenceFileUploaded = true;
                    $userToUpdate->update(['licence_verified' => 0]);
                } else {
                    $licenceFilePath = $request->old_licence_file ?? $document?->licence_file;
                    $licenceFileUploaded = $document?->licence_file_uploaded ?? false;
                }
            }




            if ($request->hasFile('profile_image')) {
                // Delete old image if it exists
                if ($userToUpdate->image && Storage::disk('public')->exists($userToUpdate->image)) {
                    Storage::disk('public')->delete($userToUpdate->image);
                }

                // Store new image
                $filePath = $request->file('profile_image')->store('users', 'public');
            } else {
                $filePath = $userToUpdate->image; // Retain existing image
            }

            if ($userToUpdate->passport_required == 1) {
                if ($request->filled('passport')) {
                    $rules['passport'] = 'required';
                    $rules['passport_expiry_date'] = 'nullable';

                    if (!$userToUpdate->passport_file) {
                        $rules['passport_file'] = 'nullable|file|mimes:pdf,jpg,jpeg,png';
                    }
                }
            }

            if ($userToUpdate->medical == 1) {
                if ($request->filled('issued_by')) {
                    if (!$userToUpdate->medical_issuedby) {
                        $rules['issued_by'] = 'required';
                    }
                    if (!$userToUpdate->medical_class) {
                        $rules['medical_class'] = 'required';
                    }

                    $rules['medical_issue_date'] = 'nullable';
                    $rules['medical_expiry_date'] = 'nullable';
                    // $rules['medical_detail'] = 'required';

                    if (!$userToUpdate->medical_file) {
                        $rules['medical_file'] = 'nullable|file|mimes:pdf,jpg,jpeg,png';
                    }
                }
            }

            if ($request->filled('issued_by_2')) {
                $rules['medical_issue_date_2'] = 'nullable';
                $rules['medical_expiry_date_2'] = 'nullable';

                if (!$document || !$document->medical_file_2) {
                    $rules['medical_file_2'] = 'nullable|file|mimes:pdf,jpg,jpeg,png';
                }
            }

            $customAttributes = [
                'medical_issue_date_2' => 'medical issue date',
                'medical_expiry_date_2' => 'medical expiry date',
                'medical_file_2' => 'medical file',
            ];

            $this->validate($request, $rules, [], $customAttributes);

            if ($request->filled('licence_2')) {

                if (!$request->has('licence_expiry_date_2') || !$request->has('non_expiring_licence_2')) {
                    $rules['licence_expiry_date_2'] = 'nullable';
                }
                // $rules['licence_file_2'] = 'required';

                if ($document && !$document->licence_file_2) {
                    $rules['licence_file_2'] = 'nullable|file|mimes:pdf,jpg,jpeg,png';
                } else {
                    $rules['licence_file_2'] = 'nullable|file|mimes:pdf,jpg,jpeg,png';
                }
            }

            $customAttributes = [
                'licence_expiry_date_2' => 'licence expiry date',
                'licence_file_2' => 'licence 2 file',
            ];

            $this->validate($request, $rules, [], $customAttributes);

            $medicalFileUploaded = $document?->medical_file_uploaded ?? false;
            $medicalFileUploaded_2 = $document?->medical_file_uploaded_2 ?? false;
            $licenceFileUploaded = $document?->licence_file_uploaded ?? false;
            $licenceFileUploaded_2 = $document?->licence_file_uploaded_2 ?? false;
            $passportFileUploaded = $document?->passport_file_uploaded ?? false;
            if ($request->hasFile('medical_file')) {
                if ($document && $document->medical_file) {
                    Storage::disk('public')->delete($document->medical_file);
                }

                $medicalFilePath = $request->file('medical_file')->store('medical_file', 'public');

                $medicalFileUploaded = true;
                $userToUpdate->update(['medical_verified' => 0]);
            } else {
                $medicalFilePath = $request->input('old_medical_file');
            }

            if ($request->hasFile('medical_file_2')) {

                if ($document && $document->medical_file_2) {
                    Storage::disk('public')->delete($document->medical_file_2);
                }
                $medicalFilePath_2 = $request->file('medical_file_2')->store('medical_file', 'public');

                $medicalFileUploaded_2 = true;
                // $document->update(['medical_verified_2' => 0]);
                $document = UserDocument::updateOrCreate(
                    ['user_id' => $userToUpdate->id], // Matching condition
                    ['medical_verified_2' => 0] // Fields to update or set on creation
                );
            } else {
                $medicalFilePath_2 = $request->old_medical_file_2;
            }

            if ($userToUpdate->currency_required == 1 && !$userToUpdate->currency) {
                $rules['currency'] = 'required|string';
            }

            if ($userToUpdate->rating_required == 1 && $request->filled('issue_date')) {
                foreach ($request->input('issue_date') as $ratingId => $issueDate) {
                    if (!empty($issueDate)) {
                        $rules["issue_date.$ratingId"] = 'required|date';
                        $rules["expiry_date.$ratingId"] = 'required|date|after_or_equal:issue_date.' . $ratingId;

                        // Check if existing UserRating has file_path
                        $existingRating = UserRating::where('user_id', $userToUpdate->id)
                            ->where('rating_id', $ratingId)
                            ->first();

                        if (empty($existingRating?->file_path)) {
                            $rules["rating_file.$ratingId"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:15360';
                        } else {
                            $rules["rating_file.$ratingId"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:15360';
                        }
                    }
                }
            }

            if (!empty($rules)) {
                $request->validate($rules);
            }
        }

        // Handle Licence File Upload
        if ($userToUpdate->licence_required == 1) {
            if ($request->hasFile('licence_file')) {
                if ($document && $document->licence_file) {
                    Storage::disk('public')->delete($document->licence_file);
                }
                $licenceFilePath = $request->file('licence_file')->store('licence_files', 'public');
                $licenceFileUploaded = true;
                $userToUpdate->update(['licence_verified' => 0]);
            } else {
                $licenceFilePath = $request->old_licence_file;
            }
        } else {
            $licenceFilePath = $document->licence_file;
        }

        if ($request->hasFile('licence_file_2')) {
            if ($document && $document->licence_file_2) {
                Storage::disk('public')->delete($document->licence_file_2);
            }
            $licenceFilePath_2 = $request->file('licence_file_2')->store('licence_files', 'public');
            $licenceFileUploaded_2 = true;
            $document->update(['licence_verified_2' => 0]);
        } else {
            $licenceFilePath_2 = $request->old_licence_file_2;
        }

        //Handle Passport File Upload
        if ($userToUpdate->passport_required == 1) {
            if ($request->hasFile('passport_file')) {
                if ($document && $document->passport_file) {
                    Storage::disk('public')->delete($document->passport_file);
                }
                $passportFilePath = $request->file('passport_file')->store('passport_files', 'public');
                $passportFileUploaded = true;
                $userToUpdate->update(['passport_verified' => 0]);
            } else {
                $passportFilePath = $request->old_passport_file ?? NULL;
            }
        } else {
            $passportFilePath = $document->passport_file;
        }

        if ($userToUpdate->currency_required == 1) {
            $request->validate([
                'currency' => 'required|string',
            ]);
        }
        if ($userToUpdate->custom_field_required == 1) {
            if ($request->has('custom_date_checkbox')) {
                $request->validate([
                    'custom_field_date' => 'required|date',
                ]);
            } elseif ($request->has('custom_text_checkbox')) {
                $request->validate([
                    'custom_field_text' => 'required|string',
                ]);
            }
        }

        $newData = [
            'fname' => $request->firstName,
            'lname' => $request->lastName,
            'email' => $request->email,
            'image' => $filePath ?? null,
            'licence' => $request->licence ?? $userToUpdate->licence,
            'licence_expiry_date' => $request->licence_expiry_date ?? null,
            'licence_file' => $licenceFilePath,
            'licence_non_expiring' => $request->has('non_expiring_licence') ? 1 : 0,
            'passport' => $request->passport ?? $userToUpdate->passport,
            'passport_expiry_date' => $request->passport_expiry_date ?? $userToUpdate->passport_expiry_date,
            'passport_file' => $passportFilePath,
            'currency' => $request->currency ?? $userToUpdate->currency,
            'medical_issuedby' => $request->issued_by ?? $userToUpdate->medical_issuedby,
            'medical_class' => $request->medical_class ?? $userToUpdate->medical_class,
            'medical_issuedate' => $request->medical_issue_date ?? $userToUpdate->medical_issuedate,
            'medical_expirydate' => $request->medical_expiry_date ?? $userToUpdate->medical_expirydate,
            'medical_restriction' => $request->medical_detail ?? $userToUpdate->medical_restriction,
            'medical_file' => $medicalFilePath,
            'licence_file_uploaded' => $licenceFileUploaded,
            'passport_file_uploaded' => $passportFileUploaded,
            'medical_file_uploaded' => $medicalFileUploaded,
            'custom_field_date' => $request->custom_field_date ?? $userToUpdate->custom_field_date,
            'custom_field_text' => $request->custom_field_text ?? $userToUpdate->custom_field_text
        ];

         

        $oldData = $userToUpdate->only(array_keys($newData));
        $changes = [];

        foreach ($newData as $key => $value) {
            $oldValue = $oldData[$key] ?? 'N/A';
            $newValue = $value ?? 'N/A';

            if ($oldValue != $newValue) {
                $formattedKey = ucfirst(str_replace('_', ' ', $key)); // Format field names
                $changes[] = "$formattedKey changed from '$oldValue' to '$newValue'";
            }
        }

        \Log::info('Updating user with:', $newData);
        $userToUpdate->update($newData);
        \Log::info('User updated:', $userToUpdate->only(['licence_file', 'licence_file_uploaded']));

        if (!empty($changes)) {
            UserActivityLog::create([
                'user_id' => auth()->user()->id,
                'log_type' => 'Profile Update',
                'description' => implode("\n", $changes), // New line for better readability
            ]);
        }
        //dd($licenceFileUploaded_2);
        \Log::info('Updating documents with:', [
            'licence_file' => $licenceFilePath,
            'licence_file_uploaded' => $licenceFileUploaded,
            'old_licence_file' => $request->old_licence_file ?? 'N/A',
            'has new file' => $request->hasFile('licence_file') ? 'yes' : 'no',
        ]);

        $userToUpdate->documents()->updateOrCreate(

            ['user_id' => $userToUpdate->id], // Unique identifying condition

            [   //Fields to update or set
                'licence' => $request->licence ?? null,
                'licence_file' => $licenceFilePath ?? null,
                'licence_expiry_date' => $request->licence_expiry_date ?? null,
                'licence_non_expiring' => $request->has('non_expiring_licence') ? 1 : 0,
                'licence_file_uploaded' => $request->hasFile('licence_file')
                    ? true
                    : ($document?->licence_file_uploaded ?? false),


                'licence_2' => $request->licence_2 ?? null,
                'licence_file_2' => $licenceFilePath_2 ?? null,
                'licence_expiry_date_2' => $request->licence_expiry_date_2 ?? null,
                'licence_non_expiring_2' => $request->has('non_expiring_licence_2') ? 1 : 0,
                'licence_file_uploaded_2' => $request->hasFile('licence_file_2')
                    ? true
                    : ($document?->licence_file_uploaded_2 ?? false),


                'passport' => $request->passport ?? null,
                'passport_expiry_date' => $request->passport_expiry_date ?? null,
                'passport_file' => $passportFilePath ?? null,
                'passport_file_uploaded' => $passportFileUploaded ?? $document?->passport_file_uploaded,

                'medical' => $userToUpdate->medical,
                'medical_issuedby' => $request->issued_by ?? null,
                'medical_class' => $request->medical_class ?? null,
                'medical_issuedate' => $request->medical_issue_date ?? null,
                'medical_expirydate' => $request->medical_expiry_date ?? null,
                'medical_restriction' => $request->medical_detail ?? null,
                'medical_file' => $medicalFilePath ?? null,
                'medical_file_uploaded' => $medicalFileUploaded ?? $document?->medical_file_uploaded,

                'medical_2' => $userToUpdate->medical,
                'medical_issuedby_2' => $request->issued_by_2 ?? null,
                'medical_class_2' => $request->medical_class_2 ?? null,
                'medical_issuedate_2' => $request->medical_issue_date_2 ?? null,
                'medical_expirydate_2' => $request->medical_expiry_date_2 ?? null,
                'medical_restriction_2' => $request->medical_detail_2 ?? null,
                'medical_file_2' => $medicalFilePath_2 ?? null,
                'medical_file_uploaded_2' => $medicalFileUploaded_2 ?? $document?->medical_file_uploaded_2,
            ]
        );


        // $document->save();


        if ($userToUpdate->rating_required == 1 && $request->has('issue_date')) {
            foreach ($request->issue_date as $ratingId => $issueDate) {
                $expiryDate = $request->expiry_date[$ratingId] ?? null;
                $file = $request->file("rating_file.$ratingId");

                $filePath = null;
                if ($file) {
                    $originalName = $file->getClientOriginalName();
                    $filePath = $file->storeAs("rating_files", $originalName, "public");
                }

                // Find existing rating or create a new one
                $userRating = UserRating::firstOrNew([
                    'user_id' => $userToUpdate->id,
                    'rating_id' => $ratingId,
                ]);

                $userRating->issue_date = $issueDate;
                $userRating->expiry_date = $expiryDate;
                if ($filePath) {
                    // Delete old file if exists
                    if ($userRating->file) {
                        Storage::disk('public')->delete($userRating->file);
                    }
                    $userRating->file_path = $filePath;
                    $userRating->admin_verified = 0;
                }
                $userRating->save();
            }
        }

        //   Session::flash('message', 'User saved successfully');
        return response()->json(['success' => true, 'message' => "User profile updated successfully"]);
    }

    public function save_user(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:25600',
            'password' => 'required|min:6|confirmed',
            'role_name' => 'required',
            'status' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'extra_roles' => 'array',
            'extra_roles.*' => 'exists:roles,id', // Ensure all user IDs exist
            'rating' => $request->has('rating_checkbox') ? 'required|array|min:1' : 'nullable',
        ]);

        // Check if the rating checkbox is checked, and validate rating field if so
        if ($request->has('rating_checkbox') && $request->rating_checkbox) {
            if (empty($request->rating)) {
                return redirect()->back()->withInput()->withErrors(['rating' => 'Please select at least one rating.']);
            }
        }

        $licence_required = null;
        $passport_required = null;
        $rating_required = null;
        $currency_required = null;

        if ($request->has('licence_checkbox') && $request->licence_checkbox) {
            // $request->validate([
            //     'licence' => 'required|string',
            //     'licence_file' => 'required|mimes:pdf,jpg,jpeg,png',
            // ]);
            $licence_required = 1;
        }

        if ($request->has('passport_checkbox') && $request->passport_checkbox) {
            $passport_required = 1;
        }

        if ($request->has('rating_checkbox') && $request->rating_checkbox) {
            $rating_required = 1;
        }

        if ($request->has('currency_checkbox') && $request->currency_checkbox) {
            $currency_required = 1;
        }
        $medical_checkbox              = null;
        $medical_verification_required = null;
        $medical_issued_by             = null;
        $medical_class                 = null;
        $medical_issue_date            = null;
        $medical_expiry_date           = null;
        $medical_detail                = null;

        $medical_issued_by_2 = null;
        $medical_class_2 = null;
        $medical_issue_date_2 = null;
        $medical_expiry_date_2 = null;
        $medical_detail_2 = null;
        $medicalFilePath_2 = null;

        if ($request->has('medical_checkbox')) {
            $medical_checkbox              = $request->medical_checkbox;
            $medical_verification_required = $request->medical_verification_required;
            $medical_issued_by             = $request->issued_by;
            $medical_class                 = $request->medical_class;
            $medical_issue_date            = $request->medical_issue_date;
            $medical_expiry_date           = $request->medical_expiry_date;
            $medical_detail                = $request->medical_detail;
        }


        $medicalFileUploaded = false;
        $medicalFileUploaded_2 = false;
        $licenceFileUploaded = false;
        $licenceFileUploaded_2 = false;
        $passportFileUploaded = false;

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('users', 'public');
        }

        if ($request->hasFile('licence_file')) {
            $licence_file = $request->file('licence_file')->store('user_documents', 'public');
            $licenceFileUploaded = true;
        }
        if ($request->hasFile('licence_file_2')) {
            $licence_file_2 = $request->file('licence_file_2')->store('user_documents', 'public');
            $licenceFileUploaded_2 = true;
        }

        if ($request->hasFile('passport_file')) {
            $passport_file = $request->file('passport_file')->store('user_documents', 'public');
            $passportFileUploaded = true;
        }

        if ($request->hasFile('medical_file')) {
            $medicalFilePath = $request->file('medical_file')->store('medical_file', 'public');
            $medicalFileUploaded = true;
        }

        if ($request->hasFile('medical_file_2')) {
            $medicalFilePath_2 = $request->file('medical_file_2')->store('medical_file', 'public');
            $medicalFileUploaded_2 = true;
        }


        // Determine is_admin value
        $is_admin = (!empty($request->ou_id) && $request->role_name == 1) || (auth()->user()->is_admin == 1 && $request->role_name == 1) ? 1 : null;
        // dd($is_admin);

        $store_user = array(
            "fname"               => $request->firstname,
            "lname"               => $request->lastname,
            "email"               => $request->email,
            'image'               => $filePath ?? null,
            "password"            => Hash::make($request->password),
            "role"                => $request->role_name,
            'licence_required'    => $licence_required,
            "licence"             => $request->licence ?? null,
            "licence_file"        => $licence_file ?? null,
            "licence_admin_verification_required"        => $request->licence_verification_required ?? 0,
            "licence_2_required"        => $request->licence_2_checkbox ?? 0,
            "licence_2_admin_verification_required"        => $request->licence_2_verification_required ?? 0,
            "passport_required"   => $passport_required,
            "passport"            => $request->passport ?? null,
            "passport_file"       => $passport_file ?? null,
            "passport_admin_verification_required"       => $request->passport_verification_required ?? 0,
            "rating_required"     => $rating_required,
            "rating" => $request->has('rating') ? json_encode($request->rating) : null,
            "currency_required"   => $currency_required,
            "currency"            => $request->currency ?? null,
            "custom_field_name"   => $request->custom_field_name ?? null,
            "custom_field_value"  => $request->custom_field_value ?? null,
            "custom_field_admin_verification_required"  => $request->customField_verification_required ?? 0,
            'status'              => $request->status,
            "ou_id"               => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
            "extra_roles"         => !empty($request->extra_roles) ? json_encode($request->extra_roles) : json_encode([]),
            "custom_field_required"  => $request->custom_field_checkbox ?? 0,
            "custom_field_date"  => $request->custom_field_date ?? null,
            "custom_field_text"  => $request->custom_field_text ?? null,
            'medical'               => $medical_checkbox,
            'medical_adminRequired' => $medical_verification_required,
            'medical_issuedby'      => $medical_issued_by,
            'medical_class'         => $medical_class,
            'medical_issuedate'     => $medical_issue_date,
            'medical_expirydate'    => $medical_expiry_date,
            'medical_restriction'   => $medical_detail,
            'medical_file'          => $medicalFilePath ?? null,
            "medical_2_required"        => $request->medical_2_checkbox ?? 0,
            "medical_2_adminRequired"        => $request->medical_2_verification_required ?? 0,
            "is_admin"              => $is_admin
        );

        // dd($store_user);

        $store = User::create($store_user);
        if ($store) {

            UserDocument::create([
                'user_id' => $store->id,
                'licence' =>  $request->licence ?? null,
                'licence_file' => $licence_file ?? null,
                'licence_admin_verification_required' => $request->licence_verification_required ?? 0,
                'licence_file_uploaded' => $licenceFileUploaded,
                'licence_2' => $request->licence_2 ?? null,
                'licence_file_2' => $licence_file_2 ?? null,
                'licence_admin_verification_required_2' => $request->licence_verification_required_2 ?? 0,
                'licence_file_uploaded' => $licenceFileUploaded_2,
                'passport' => $request->passport ?? null,
                'passport_file' => $passport_file ?? null,
                'passport_admin_verification_required' => $request->passport_verification_required ?? 0,
                'passport_file_uploaded' => $passportFileUploaded,
                'medical' => $medical_checkbox,
                'medical_issuedby' => $medical_issued_by,
                'medical_class' => $medical_class,
                'medical_issuedate' =>  $medical_issue_date,
                'medical_expirydate' =>  $medical_expiry_date,
                'medical_restriction' => $medical_detail,
                'medical_file' => $medicalFilePath ?? null,
                'medical_file_uploaded' => $medicalFileUploaded,
                'medical_2' => $medical_checkbox,
                'medical_issuedby_2' => $request->issued_by_2,
                'medical_class_2' => $request->medical_class_2,
                'medical_issuedate_2' =>  $request->medical_issue_date_2,
                'medical_expirydate_2' => $request->medical_expiry_date_2,
                'medical_restriction_2' => $request->medical_detail_2,
                'medical_file_2' => $medicalFilePath_2 ?? null,
                'medical_file_uploaded_2' => $medicalFileUploaded_2
            ]);

            // ✅ Save general ratings (already present — leave untouched)
            if ($request->has('rating') && is_array($request->rating)) {
                foreach ($request->rating as $ratingId) {
                    UserRating::create([
                        'user_id'    => $store->id,
                        'rating_id'  => $ratingId,
                        'issue_date' => null,
                        'expiry_date' => null,
                        'file_path'  => null,
                        'linked_to'  => 'general' // optional, if you want to differentiate
                    ]);
                }
            }

            // ✅ NEW: Save Licence 1 Ratings
            // if ($request->has('licence_1_ratings') && is_array($request->licence_1_ratings)) {
            //     foreach ($request->licence_1_ratings as $ratingId) {
            //         UserRating::create([
            //             'user_id'    => $store->id,
            //             'rating_id'  => $ratingId,
            //             'issue_date' => null,
            //             'expiry_date'=> null,
            //             'file_path'  => null,
            //             'linked_to'  => 'licence_1'
            //         ]);
            //     }
            // }

            // if ($request->has('ratings') && is_array($request->ratings)) {
            foreach ($request->ratings as $ratingGroup) {
                $parentId = $ratingGroup['parent'] ?? null;
                $childIds = $ratingGroup['child'] ?? [];
                // dd($childIds);

                if ($parentId) {
                    if (is_array($childIds) && count($childIds)) {
                        foreach ($childIds as $childId) {

                            UserRating::create([
                                'user_id'     => $store->id,
                                'rating_id'   => $childId,
                                'parent_id'   => $parentId,
                                'issue_date'  => null,
                                'expiry_date' => null,
                                'file_path'   => null,
                                'linked_to'   => 'licence_1',
                            ]);
                        }
                    } else {
                        $data = array(
                            'user_id'     => 2,
                            'rating_id'   => null,
                            'parent_id'   => $parentId,
                            'issue_date'  => null,
                            'expiry_date' => null,
                            'file_path'   => null,
                            'linked_to'   => 'licence_1',
                        );
                        // Optional: Save just the parent with rating_id = null
                        UserRating::create([
                            'user_id'     => $store->id,
                            'rating_id'   => null,
                            'parent_id'   => $parentId,
                            'issue_date'  => null,
                            'expiry_date' => null,
                            'file_path'   => null,
                            'linked_to'   => 'licence_1',
                        ]);
                    }
                }
            }

            // }

            // ✅ NEW: Save Licence 2 Ratings
            if ($request->has('licence_2_ratings') && is_array($request->licence_2_ratings)) {
                // foreach ($request->licence_2_ratings as $ratingId) {
                //     UserRating::create([
                //         'user_id'    => $store->id,
                //         'rating_id'  => $ratingId,
                //         'issue_date' => null,
                //         'expiry_date'=> null,
                //         'file_path'  => null,
                //         'linked_to'  => 'licence_2'
                //     ]);
                // }
                foreach ($request->licence_2_ratings as $ratingGroup) {
                    $parentId = $ratingGroup['parent'] ?? null;
                    $childIds = $ratingGroup['child'] ?? [];

                    if ($parentId) {
                        if (is_array($childIds) && count($childIds)) {
                            foreach ($childIds as $childId) {

                                UserRating::create([
                                    'user_id'     => $store->id,
                                    'rating_id'   => $childId,
                                    'parent_id'   => $parentId,
                                    'issue_date'  => null,
                                    'expiry_date' => null,
                                    'file_path'   => null,
                                    'linked_to'   => 'licence_2',
                                ]);
                            }
                        } else {
                            $data = array(
                                'user_id'     => 2,
                                'rating_id'   => null,
                                'parent_id'   => $parentId,
                                'issue_date'  => null,
                                'expiry_date' => null,
                                'file_path'   => null,
                                'linked_to'   => 'licence_2',
                            );
                            // Optional: Save just the parent with rating_id = null
                            UserRating::create([
                                'user_id'     => $store->id,
                                'rating_id'   => null,
                                'parent_id'   => $parentId,
                                'issue_date'  => null,
                                'expiry_date' => null,
                                'file_path'   => null,
                                'linked_to'   => 'licence_2',
                            ]);
                        }
                    }
                }
            }

            // Generate password to send in the email
            // $password = $request->password;

            UserActivityLog::create([
                'user_id' => auth()->id(),
                'log_type' => 'User Created',
                'description' => "User '{$store->fname} {$store->lname}' (ID: {$store->id}) was created by " . auth()->user()->fname . " " . auth()->user()->lname,
            ]);

            // Send emailx
            // Mail::to($store->email)->send(new UserCreated($store, $password));

            Session::flash('message', 'User saved successfully');
            return response()->json(['success' => 'User saved successfully']);
        }
    }
    public function get_child_ratings(Request $request)
    {
        $parentId = $request->parentId;

        $childRatings = Rating::whereIn('id', function ($query) use ($parentId) {
            $query->select('rating_id')
                ->from('parent_rating')
                ->where('parent_id', $parentId);
        })->distinct()->get();

        return response()->json($childRatings);
    }

    public function getChildren(Request $request)
    {
        $parentId = $request->parent_id;
        $childRatings = Rating::whereIn('id', function ($query) use ($parentId) {
            $query->select('rating_id')
                ->from('parent_rating')
                ->where('parent_id', $parentId);
        })->distinct()->get();
        return response()->json(['children' => $childRatings]);
    }


    public function update(Request $request)
    {
        $userToUpdate = User::find($request->edit_form_id);
        $UserDocument = UserDocument::where('user_id', $userToUpdate->id)->first();

        if ($userToUpdate) {
            $validatedData = $request->validate([
                'edit_firstname' => 'required',
                'edit_lastname' => 'required',
                'edit_email' => 'required|email|max:255|unique:users,email,' . $request->edit_form_id . ',id,deleted_at,NULL',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:25600',
                'edit_role_name' => 'required',
                'password' => 'confirmed',
                'status' => 'required',
                'ou_id' => [
                    function ($attribute, $value, $fail) {
                        if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                            $fail('The Organizational Unit (OU) is required for Super Admin.');
                        }
                    }
                ],
                'general_ratings' => $request->has('edit_rating_checkbox') ? 'required|array|min:1' : 'nullable',
            ], [
                'edit_firstname.required' => 'The First Name is required',
                'edit_lastname.required' => 'The Last Name is required',
                'edit_email.required' => 'The Email is required',
                'edit_email.email' => 'Please enter a valid Email',
                'password' => 'The password and confirm password do not match.',
                'general_ratings' => 'Rating is required',
            ]);

            // Handle Image Upload
            if ($request->hasFile('image')) {
                if ($userToUpdate->image) {
                    Storage::disk('public')->delete($userToUpdate->image);
                }
                $filePath = $request->file('image')->store('users', 'public');
            } else {
                $filePath = $userToUpdate->image;
            }

            // Handle Licence
            // if ($request->has('edit_licence_checkbox') && $request->edit_licence_checkbox == 'on') {
            //     $licence_required = 1;
            //     $licenceFilePath = $request->hasFile('edit_licence_file')? $request->file('edit_licence_file')->store('licence_files', 'public'): $UserDocument->licence_file;
            // } else {
            //     $licence_required = null;
            //     $licenceFilePath = $UserDocument->licence_file;
            // }

            $medicalFileUploaded = $UserDocument?->medical_file_uploaded ?? false;
            $medicalFileUploaded_2 = $UserDocument?->medical_file_uploaded_2 ?? false;
            $licenceFileUploaded = $UserDocument?->licence_file_uploaded ?? false;
            $licenceFileUploaded_2 = $UserDocument?->licence_file_uploaded_2 ?? false;
            $passportFileUploaded = $UserDocument?->passport_file_uploaded ?? false;

            if ($request->has('edit_licence_checkbox') && $request->edit_licence_checkbox) {
                $licence_required = $request->edit_licence_checkbox;
                if ($request->hasFile('edit_licence_file')) {
                    $licenceFileUploaded = true;
                    $licenceFilePath = $request->file('edit_licence_file')->store('licence_files', 'public');
                } else {
                    $licenceFilePath = $UserDocument ? $UserDocument?->licence_file : null;
                }
            } else {
                $licence_required = null;
                $licenceFilePath = $UserDocument ? $UserDocument?->licence_file : null;
            }

            if ($request->has('edit_licence_2_checkbox') && $request->edit_licence_2_checkbox) {
                $licence_2_required = $request->edit_licence_2_checkbox;
                if ($request->hasFile('edit_licence_file_2')) {
                    $licenceFileUploaded_2 = true;
                    $licenceFilePath_2 = $request->file('edit_licence_file_2')->store('licence_files', 'public');
                } else {
                    $licenceFilePath_2 = $UserDocument ? $UserDocument?->licence_file_2 : null;
                }
            } else {
                $licence_2_required = 0;
            }


            // Handle Passport
            if ($request->has('edit_passport_checkbox') && $request->edit_passport_checkbox == 'on') {
                $passport_required = 1;
                if ($request->hasFile('edit_passport_file')) {
                    $passportFileUploaded = true;
                    $passportFilePath = $request->file('edit_passport_file')->store('passport_files', 'public');
                } else {
                    $passportFilePath = $UserDocument ? $UserDocument?->passport_file : null;
                }
            } else {
                $passport_required = null;
                $passportFilePath =  $UserDocument ? $UserDocument?->passport_file : null;
            }

            // Handle Medical
            $medicalFilePath = null;
            if ($request->has('editmedical_checkbox') && $request->editmedical_checkbox == 1) {
                if ($request->hasFile('editmedical_file')) {
                    $medicalFilePath = $request->file('editmedical_file')->store('medical_file', 'public');
                    $medicalFileUploaded = true;
                }
            } else {
                $medicalFilePath = $UserDocument ? $UserDocument?->medical_file : null;
            }

            if ($request->hasFile('editmedical_file_2')) {
                $medicalFilePath_2 = $request->file('editmedical_file_2')->store('medical_file', 'public');
                $medicalFileUploaded_2 = true;
            } else {
                $medicalFilePath_2 = $UserDocument ? $UserDocument?->medical_file_2 : null;
            }

            // Handle Currency Requirement
            $currency_required = $request->has('edit_currency_checkbox') ? 1 : null;

            // Handle Rating Requirement
            $rating_required = $request->has('edit_rating_checkbox') ? 1 : $userToUpdate->rating_required;

            // Handle Custom Fields
            $editcustom_field_date = $request->has('editcustom_date_checkbox') ? $request->editcustom_field_date : null;
            $editcustom_field_text = $request->has('editcustom_text_checkbox') ? $request->editcustom_field_text : null;

            // Handle Password Flag
            $password_flag = $request->has('edit_custom_field_checkbox') ? 1 : $userToUpdate->password_flag;

            // Handle Password
            $password = $request->has('password') && $request->password ? Hash::make($request->password) : $userToUpdate->password;

            // Handle Extra Roles
            $extra_roles = $request->has('extra_roles') ? json_encode($request->extra_roles) : $userToUpdate->extra_roles;

            // Determine is_admin value
            if ((!empty($request->ou_id) && $request->edit_role_name == 1) || ($request->edit_role_name == 1 && auth()->user()->is_admin == 1)) {
                $is_admin = 1;
            } else {
                $is_admin = null;
            }

            // Handle Medical Information
            $medical_checkbox              = $request->has('editmedical_checkbox') ? $request->editmedical_checkbox : null;
            $medical_verification_required = $request->has('editmedical_checkbox') ? $request->editmedical_verification_required : null;
            $medical_issued_by             = $request->has('editmedical_checkbox') ? $request->editissued_by : null;
            $medical_class                 = $request->has('editmedical_checkbox') ? $request->editmedical_class : null;
            $medical_issue_date            = $request->has('editmedical_checkbox') ? $request->editmedical_issue_date : null;
            $medical_expiry_date           = $request->has('editmedical_checkbox') ? $request->editmedical_expiry_date : null;
            $medical_detail                = $request->has('editmedical_checkbox') ? $request->editmedical_detail : null;
            $medical_2_checkbox              = $request->has('edit_medical_2_checkbox') ? $request->edit_medical_2_checkbox : 0;
            $medical_2_adminRequired              = $request->has('edit_medical_2_verification_required') ? $request->edit_medical_2_verification_required : 0;
            // Handle Custom Fields Requirement
            $custom_field_required = $request->custom_field_checkbox ?? $userToUpdate->custom_field_required;
            // Prepare Data for Update
            $newData = [
                'fname' => $request->edit_firstname,
                'lname' => $request->edit_lastname,
                'email' => $request->edit_email,
                'password' => $password,
                'image' => $filePath,
                'role' => $request->edit_role_name,
                'status' => $request->status,
                'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
                'licence' => $request->edit_licence ?? null,
                'licence_required' => $licence_required,
                'licence_admin_verification_required' => $request->edit_licence_verification_required ?? 0,
                'passport_admin_verification_required' => $request->edit_passport_verification_required ?? 0,
                'licence_file' => $licenceFilePath,
                'licence_2_required' => $licence_2_required,
                'licence_2_admin_verification_required' => $request->edit_licence_2_verification_required ?? 0,
                'passport_required' => $passport_required,
                'passport' => $request->edit_passport ?? null,
                'passport_file' => $passportFilePath,
                'rating_required' => $rating_required,
                "rating" => $request->has('edit_rating') ? json_encode($request->edit_rating) : null,
                'currency_required' => $currency_required,
                'currency' => $request->edit_currency ?? null,
                'custom_field_required' => $custom_field_required,
                'custom_field_date' => $editcustom_field_date,
                'custom_field_text' => $editcustom_field_text,
                'custom_field_admin_verification_required' => $request->edit_custom_field_verification_required ?? 0,
                'password_flag' => $password_flag,
                'extra_roles' => $extra_roles,
                'medical' => $medical_checkbox,
                'medical_adminRequired' => $medical_verification_required ?? null,
                'medical_issuedby' => $medical_issued_by,
                'medical_class' => $medical_class,
                'medical_issuedate' => $medical_issue_date,
                'medical_expirydate' => $medical_expiry_date,
                'medical_restriction' => $medical_detail,
                'medical_file'  => $medicalFilePath,
                'medical_2_required' => $medical_2_checkbox,
                'medical_2_adminRequired' => $medical_2_adminRequired ?? 0,
                'is_admin' => $is_admin
            ];

            // Track Changes
            $oldData = $userToUpdate->only(array_keys($newData));
            $changes = [];
            foreach ($newData as $key => $value) {
                if ($oldData[$key] != $value) {
                    $changes[] = ucfirst($key) . " changed from '{$oldData[$key]}' to '{$value}'";
                }
            }

            // Update User
            $store =  $userToUpdate->update($newData);

            UserDocument::updateOrCreate(
                ['user_id' => $userToUpdate->id], // Search criteria
                [
                    'licence' =>  $request->edit_licence ?? null,
                    'licence_file' => $licenceFilePath ?? null,
                    'licence_admin_verification_required' => $request->edit_licence_verification_required ?? 0,
                    'licence_file_uploaded' => $licenceFileUploaded ?? $UserDocument?->licence_file_uploaded,
                    'licence_2' => $request->edit_licence_2 ?? null,
                    'licence_file_2' => $licenceFilePath_2 ?? null,
                    'licence_admin_verification_required_2' => $request->edit_licence_verification_required_2 ?? 0,
                    'licence_file_uploaded_2' => $licenceFileUploaded_2 ?? $UserDocument?->licence_file_uploaded_2,
                    'passport' => $request->edit_passport ?? null,
                    'passport_file' => $passportFilePath ?? null,
                    'passport_admin_verification_required' => $request->edit_passport_verification_required ?? 0,
                    'passport_file_uploaded' => $passportFileUploaded ?? $UserDocument?->passport_file_uploaded,
                    'medical' => $medical_checkbox,
                    'medical_issuedby' => $medical_issued_by,
                    'medical_class' => $medical_class,
                    'medical_issuedate' =>  $medical_issue_date,
                    'medical_expirydate' =>  $medical_expiry_date,
                    'medical_restriction' => $medical_detail,
                    'medical_file' => $medicalFilePath ?? null,
                    'medical_file_uploaded' => $medicalFileUploaded ?? $UserDocument?->medical_file_uploaded,
                    'medical_2' => $medical_checkbox,
                    'medical_issuedby_2' => $request->editissued_by_2,
                    'medical_class_2' => $request->editmedical_class_2,
                    'medical_issuedate_2' =>  $request->editmedical_issue_date_2,
                    'medical_expirydate_2' => $request->editmedical_expiry_date_2,
                    'medical_restriction_2' => $request->editmedical_detail_2,
                    'medical_file_2' => $medicalFilePath_2 ?? null,
                    'medical_file_uploaded_2' => $medicalFileUploaded_2 ?? $UserDocument?->medical_file_uploaded_2,
                ]
            );


            // === Handle User Ratings (NEW) ===
            // === Handle User Ratings with Linked Flag ===
            UserRating::where('user_id', $userToUpdate->id)->delete(); // Remove all existing ratings

            $ratingsData = [
                'licence_1' => $request->input('licence_1_ratings', []),
                'licence_2' => $request->input('licence_2_ratings', []),
                'general'   => $request->input('general_ratings', []),
            ];
            // ✅ NEW: Save Licence 2 Ratings

            if ($request->has('licence_1_ratings') && is_array($request->licence_1_ratings)) {
           
                foreach ($request->licence_1_ratings as $ratingGroup) {
                    $parentId = $ratingGroup['parent'] ?? null;
                    $childIds = $ratingGroup['child'] ?? [];

                    if ($parentId) {
                        if (is_array($childIds) && count($childIds)) {
                            foreach ($childIds as $childId) {

                                UserRating::create([
                                    'user_id'     => $request->edit_form_id,
                                    'rating_id'   => $childId,
                                    'parent_id'   => $parentId,
                                    'issue_date'  => null,
                                    'expiry_date' => null,
                                    'file_path'   => null,
                                    'linked_to'   => 'licence_1',
                                ]);
                            }
                        } else {
                            // Optional: Save just the parent with rating_id = null
                            UserRating::create([
                                'user_id'     => $request->edit_form_id,
                                'rating_id'   => null,
                                'parent_id'   => $parentId,
                                'issue_date'  => null,
                                'expiry_date' => null,
                                'file_path'   => null,
                                'linked_to'   => 'licence_1',
                            ]);
                        }
                    }
                }
            }

             if ($request->has('licence_2_ratings') && is_array($request->licence_2_ratings)) {
                     foreach ($request->licence_2_ratings as $ratingGroup) { 
                    $parentId = $ratingGroup['parent'] ?? null;
                    $childIds = $ratingGroup['child'] ?? [];

                    if ($parentId) {
                        if (is_array($childIds) && count($childIds)) {
                            foreach ($childIds as $childId) {

                                UserRating::create([
                                    'user_id'     => $request->edit_form_id,
                                    'rating_id'   => $childId,
                                    'parent_id'   => $parentId,
                                    'issue_date'  => null,
                                    'expiry_date' => null,
                                    'file_path'   => null,
                                    'linked_to'   => 'licence_2',
                                ]);
                            }
                        } else {
                            // Optional: Save just the parent with rating_id = null
                            UserRating::create([
                                'user_id'     => $request->edit_form_id,
                                'rating_id'   => null,
                                'parent_id'   => $parentId,
                                'issue_date'  => null,
                                'expiry_date' => null,
                                'file_path'   => null,
                                'linked_to'   => 'licence_2',
                            ]);
                        }
                    }
                }
             }
            //    foreach ($ratingsData as $linkedTo => $ratingIds) {
            //         foreach ($ratingIds as $ratingId) {
            //             \Log::info("Saving rating {$ratingId} linked to {$linkedTo}");

            //             UserRating::create([
            //                 'user_id'   => $userToUpdate->id,
            //                 'rating_id' => $ratingId,
            //                 'linked_to' => $linkedTo,
            //             ]);
            //         }
            //     }

            // Log Changes
            if (!empty($changes)) {
                UserActivityLog::create([
                    'user_id' => auth()->id(),
                    'log_type' => 'User Updated',
                    'description' => "User '{$userToUpdate->fname} {$userToUpdate->lname}' (ID: {$userToUpdate->id}) was updated by " . auth()->user()->fname . " " . auth()->user()->lname . ". Changes: " . implode(', ', $changes),
                ]);
            }

            return response()->json(['success' => true, 'message' => "User data updated successfully"]);
        }
    }


    public function getUserById(Request $request)
    {
        $user = User::with('usrRatings', 'documents')->find(decode_id($request->id));

        if (!$user) {
            return response()->json(['error' => 'User not found']);
        }

        $userRatings = $user->usrRatings
            ->groupBy('linked_to')
            ->map(function ($items) {
                return $items->pluck('rating_id')->values();
            })
            ->toArray();

        $userRatingsLicence1 = UserRating::where('user_id', decode_id($request->id))
            ->where('linked_to', 'licence_1')
            ->get();

        $userRatingsLicence1Grouped = $userRatingsLicence1
            ->groupBy('parent_id')
            ->map(function ($group) {
                return [
                    'parent_id' => ($group[0]->parent_id),
                    'children'  => $group->pluck('rating_id')->filter()->values() // only non-null child ratings
                ];
            })->values();

        if (!empty($userRatingsLicence1)) {
            $licence1 = 1;
        }

        $userRatingsLicence2 = UserRating::where('user_id', decode_id($request->id))
            ->where('linked_to', 'licence_2')
            ->get();

        $userRatingsLicence2Grouped = $userRatingsLicence2
            ->groupBy('parent_id')
            ->map(function ($group) {
                return [
                    'parent_id' => ($group[0]->parent_id),
                    'children'  => $group->pluck('rating_id')->filter()->values() // only non-null child ratings
                ];
            })->values();

        if (!empty($userRatingsLicence2)) {
            $licence2 = 1;
        }



        return response()->json([
            'user' => $user,
            'user_ratings'          => $userRatings,
            'userRatings_licence_1' => $userRatingsLicence1Grouped,
            'licence1'              => $licence1,
            'userRatings_licence_2' => $userRatingsLicence2Grouped,
            'licence2'              => $licence2,
        ]);
    }



    public function destroy(Request $request)
    {
        $currentUser = auth()->user();
        $user = User::find(decode_id($request->id));

        if ($user) {
            UserActivityLog::create([
                'user_id' => $currentUser->id,
                'log_type' => 'User Deletion',
                'description' => "User '{$user->fname} {$user->lname}' (ID: {$user->id}) was deleted by {$currentUser->fname} {$currentUser->lname}.",
            ]);

            $user->delete();

            return redirect()->route('user.index')->with('message', 'User deleted successfully');
        }

        return redirect()->route('user.index')->with('error', 'User not found');
    }


  public function showUser(Request $request, $user_id)
{
    $user = User::with([
        'roles',
        'organization',
        'usrRatings.rating',
        'usrRatings.parent',
        'documents'
    ])->find(decode_id($user_id));

    if (!$user) {
        return redirect()->back()->with('error', 'User not found.');
    }

    $rawUserRatings = $user->usrRatings;
    

    $grouped = [];

    foreach ($rawUserRatings as $rating) {
        $linkedTo = $rating->linked_to ?? 'unlinked';

        if (is_null($rating->parent_id)) {
            // Rating is a parent
            $grouped[$linkedTo][$rating->rating_id]['parent'] = $rating;
        } else {
            // Rating is a child
            $parentId = $rating->parent_id;
            if (!isset($grouped[$linkedTo][$parentId])) {
                $grouped[$linkedTo][$parentId] = [];
            }
 
            $grouped[$linkedTo][$parentId]['children'][] = $rating;

            if (!isset($grouped[$linkedTo][$parentId]['parent'])) {
                $parentRatingModel = \App\Models\Rating::find($parentId);
                if ($parentRatingModel) {
                      $fakeParent = new \App\Models\UserRating([
                        'rating_id'    => $parentRatingModel->id,
                        'parent_id'    => null,
                        'linked_to'    => $linkedTo,
                        'issue_date'   => null, // explicitly added
                        'expiry_date'  => null, // explicitly added
                        'file_path'    => null,
                        'admin_verified' => 0,
                    ]);
                    $fakeParent->setRelation('rating', $parentRatingModel);
                    $grouped[$linkedTo][$parentId]['parent'] = $fakeParent;
                }
            }
        }
        
    }
    

    // Filter usable ratings
    $userRatings = $rawUserRatings->filter(function ($ur) {
        return $ur->rating_id !== null || $ur->parent_id !== null;
    });

    foreach ($userRatings as $ur) {
        if ($ur->rating_id === null && $ur->parent_id !== null) {
            $ur->rating_id = $ur->parent_id;
            $ur->rating = $ur->parent;
        }
    }

    // Licence 1 ratings
    $licence1Ratings = $userRatings->filter(function ($ur) use ($userRatings) {
        if ($ur->linked_to === 'licence_1') return true;
        $parent = $userRatings->firstWhere('rating_id', $ur->parent_id);
        return $parent && $parent->linked_to === 'licence_1';
    })->values();

    // Licence 2 ratings
    $licence2Ratings = $userRatings->filter(function ($ur) use ($userRatings) {
        if ($ur->linked_to === 'licence_2') return true;
        $parent = $userRatings->firstWhere('rating_id', $ur->parent_id);
        return $parent && $parent->linked_to === 'licence_2';
    })->values();

    // Selected IDs
    $selectedIdsLicence1 = $licence1Ratings->pluck('rating_id')->unique();
    $selectedIdsLicence2 = $licence2Ratings->pluck('rating_id')->unique();

    // Parent IDs
    $parentIdsLicence1 = $licence1Ratings
        ->pluck('rating.parent_id')
        ->merge($licence1Ratings->pluck('rating_id')->filter(fn($id) => is_null(optional($userRatings->firstWhere('rating_id', $id))->rating->parent_id)))
        ->unique()->values();

    $parentIdsLicence2 = $licence2Ratings
        ->pluck('rating.parent_id')
        ->merge($licence2Ratings->pluck('rating_id')->filter(fn($id) => is_null(optional($userRatings->firstWhere('rating_id', $id))->rating->parent_id)))
        ->unique()->values();

    // Missing parent ratings
    $existingUserRatingIds = $userRatings->pluck('rating_id')->filter();
    $missingParentIdsLicence1 = $parentIdsLicence1->diff($existingUserRatingIds);
    $missingParentIdsLicence2 = $parentIdsLicence2->diff($existingUserRatingIds);

    $missingParentRatingsLicence1 = Rating::whereIn('id', $missingParentIdsLicence1)->get();
    $missingParentRatingsLicence2 = Rating::whereIn('id', $missingParentIdsLicence2)->get();

    // Group child ratings
    $childRatingsGrouped = ParentRating::with('child')->get()->groupBy('parent_id');

    // Map for quick access
    $userRatingsMap = $userRatings->keyBy('rating_id');

    // Role names from JSON
    $extraRoles = Role::whereIn('id', json_decode($user->extra_roles ?? '[]'))->pluck('role_name')->toArray();

    return view('users.show', compact(
        'user',
        'extraRoles',
        'licence1Ratings',
        'licence2Ratings',
        'selectedIdsLicence1',
        'selectedIdsLicence2',
        'childRatingsGrouped',
        'userRatingsMap',
        'parentIdsLicence1',
        'parentIdsLicence2',
        'missingParentRatingsLicence1',
        'missingParentRatingsLicence2',
        'grouped'
    ));
}


    public function docsVerify(Request $request)
    {

        // dd($request->all());
        $decodedUserId = decode_id($request->userId);
        $decodedRatingId = $request->ratingId ? decode_id($request->ratingId) : null;
        $document = UserDocument::where('user_id', $decodedUserId)->first();

        // dd($request->all());

        $request->validate([
            'userId' => 'required',
            'documentType' => 'required|in:passport,licence,licence_2,medical,medical_2,user_rating',
            'verified' => 'required|boolean',
        ]);

        if ($request->documentType === 'user_rating') {
            // Ensure rating ID is provided
            if (!$decodedRatingId) {
                return response()->json(['error' => 'Rating ID is required.'], 422);
            }

            $rating = UserRating::find($decodedRatingId);
            if (!$rating) {
                return response()->json(['error' => 'User Rating not found.'], 404);
            }

            // Optional: Validate the rating belongs to the correct user
            if ($rating->user_id !== $decodedUserId) {
                return response()->json(['error' => 'Rating does not belong to the specified user.'], 403);
            }

            $rating->admin_verified = $request->verified;
            $rating->save();

            return response()->json(['success' => 'User rating verification updated successfully.']);
        }

        if ($request->documentType === 'licence_2') {

            if (!$document) {
                return response()->json(['error' => 'User Second Licence not is not found.'], 404);
            }

            $document->licence_verified_2 = $request->verified;
            $document->licence_2_invalidate = false;
            $document->save();

            return response()->json(['success' => 'User Second Licence verification updated successfully.']);
        }

        if ($request->documentType === 'medical_2') {

            // dd("hello");

            if (!$document) {
                return response()->json(['error' => 'User Second Medical not is not found.'], 404);
            }

            $document->medical_verified_2 = $request->verified;
            $document->medical_2_invalidate = false;
            $document->save();

            return response()->json(['success' => 'User Second Medical verification updated successfully.']);
        }


        // For documents
        $user = User::find($decodedUserId);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $column = $request->documentType . '_verified';
        $column2 = $request->documentType . '_invalidate';
        $user->update([$column => $request->verified]);

        $document->update([$column => $request->verified]);
        $document->update([$column2 => 0]);

        return response()->json(['success' => 'Document verification updated successfully.']);
    }

    public function invalidateDocument(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required|in:licence,licence_2,medical,medical_2,passport',
        ]);

        $user = User::where('id', $request->user_id)->first();
        $userDocument = UserDocument::where('user_id', $user->id)->first();

        switch ($request->document_type) {
            case 'licence':
                $user->licence_verified = false;
                $userDocument->licence_verified = false;
                $userDocument->licence_invalidate = true;
                break;

            case 'licence_2':
                $userDocument->licence_verified_2 = false;
                $userDocument->licence_2_invalidate = true;
                break;

            case 'medical':
                $user->medical_verified = false;
                $userDocument->medical_verified = false;
                $userDocument->medical_invalidate = true;
                break;

            case 'medical_2':
                $userDocument->medical_verified_2 = false;
                $userDocument->medical_2_invalidate = true;
                break;

            case 'passport':
                $user->passport_verified = false;
                $userDocument->passport_verified = false;
                $userDocument->passport_invalidate = true;
                break;
        }

        $user->save();
        $userDocument->save();

        return response()->json([
            'success' => true,
            'message' => 'Document invalidated successfully.'
        ]);
    }

    public function switchRole(Request $request)
    {
        // Validate input role ID
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = auth()->user();

        // Ensure extra_roles is properly decoded
        $extra_roles = json_decode($user->extra_roles, true);
        if (!is_array($extra_roles)) {
            $extra_roles = [];
        }

        // Merge main role and extra roles
        $allowed_roles = array_merge([$user->role], $extra_roles);

        // Check if the requested role is allowed
        if (!in_array($request->role_id, $allowed_roles)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized role switch.'], 403);
        }

        // Store selected role in session
        session(['current_role' => $request->role_id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Role switched successfully!',
        ], 200);
    }

    public function showRating()
    {
        $allRatings = Rating::all(); // All ratings (from ratings table)
        $organizationUnits = OrganizationUnits::all(); // For UI context

        // Group parent-child mappings from parent_rating table
        $parentRelations = ParentRating::all()->groupBy('parent_id');

        $ratingDropdownOptions = collect(); // To store formatted dropdown items
        $usedIds = collect(); // To track already added children

        // Build hierarchy from parent_rating
        $this->buildFullHierarchy($parentRelations, $allRatings, null, 0, $ratingDropdownOptions, $usedIds);

        // Add orphan ratings (those not in parent_rating table as children)
        $remaining = $allRatings->whereNotIn('id', $usedIds);
        foreach ($remaining as $rating) {
            $ratingDropdownOptions->push((object)[
                'id' => $rating->id,
                'name' => e($rating->name),
                'parent_id' => null,
                'ou_id' => $rating->ou_id ?? null,
            ]);
        }

        return view('users.ratings.show', [
            'ratings' => $allRatings->groupBy('name'),
            'ratingDropdownOptions' => $allRatings,
            'organizationUnits' => $organizationUnits
        ]);
    }



    private function buildFullHierarchy($relations, $allRatings, $parentId, $depth, &$result, &$usedIds)
    {
        if (!isset($relations[$parentId])) {
            return;
        }

        foreach ($relations[$parentId] as $relation) {
            $childRating = $allRatings->firstWhere('id', $relation->rating_id);
            if (!$childRating) continue;

            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
            $arrow = $depth > 0 ? '↳ ' : '';
            $name = is_object($childRating->name) ? ($childRating->name->en ?? '') : $childRating->name;

            $result->push((object)[
                'id' => $childRating->id,
                'name' => $indent . $arrow . e($name),
                'ou_id' => $childRating->ou_id ?? null,
            ]);

            $usedIds->push($childRating->id);

            $this->buildFullHierarchy($relations, $allRatings, $childRating->id, $depth + 1, $result, $usedIds);
        }
    }


    public function saveRating(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:ratings,name,NULL,id,deleted_at,NULL',
            'status' => 'required|boolean',
            'kind_of_rating' => 'required|string|in:type_rating,class_rating,instrument_rating,instructor_rating,examiner_rating,others',
        ]);

        // Create new rating
        $rating = Rating::create([
            'name' => $request->name,
            'status' => $request->status,
            'kind_of_rating' => $request->kind_of_rating,
            'is_fixed_wing' => $request->has('is_fixed_wing'),
            'is_rotary' => $request->has('is_rotary'),
            'is_instructor' => $request->has('is_instructor'),
            'is_examiner' => $request->has('is_examiner'),
            'ou_id' => NULL
        ]);

        // Save multiple parent relationships
        $parentIds = $request->input('parent_id', []);
        foreach ($parentIds as $parentId) {
            if ($parentId) {
                ParentRating::create([
                    'rating_id' => $rating->id,
                    'parent_id' => $parentId,
                ]);
            }
        }

        Session::flash('message', 'Rating saved successfully');
        return response()->json(['success' => true, 'msg' => 'Rating saved successfully.']);
    }

    public function getRating(Request $request)
    {
        $mainRating = Rating::find(decode_id($request->rating_id));

        if ($mainRating) {
            // Get all ratings with same name (e.g. different versions)
            $relatedRatings = Rating::where('name', $mainRating->name)->get();
            $relatedIds = $relatedRatings->pluck('id')->toArray();

            // Get selected parent IDs from parent_rating table (NOT from ratings table!)
            $selectedParentIds = ParentRating::whereIn('rating_id', $relatedIds)
                ->pluck('parent_id')->filter()->unique()->values()->all();

            $allRatings = Rating::all();
            $relations = \App\Models\ParentRating::all()->groupBy('parent_id');

            $ratingDropdownOptions = collect();
            $usedIds = collect();
            $this->buildFullHierarchy($relations, $allRatings, null, 0, $ratingDropdownOptions, $usedIds);

            // Add orphan ratings (not in parent_rating)
            $remaining = $allRatings->whereNotIn('id', $usedIds);
            foreach ($remaining as $rating) {
                $ratingDropdownOptions->push((object)[
                    'id' => $rating->id,
                    'name' => e($rating->name),
                    'parent_id' => null,
                    'ou_id' => $rating->ou_id ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'rating' => $mainRating,
                'selected' => $selectedParentIds,
                'dropdown' => $ratingDropdownOptions->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => strip_tags(html_entity_decode($item->name)),
                    ];
                }),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'msg' => 'Rating not found',
            ]);
        }
    }


    public function updateRating(Request $request)
    {
        $request->validate([
            'rating_id' => 'required|integer',
            'name' => 'required|string|unique:ratings,name,' . $request->rating_id . ',id,deleted_at,NULL',
            'status' => 'required|in:0,1',
            'kind_of_rating' => 'required|string|in:type_rating,class_rating,instrument_rating,instructor_rating,examiner_rating,others',
        ]);

        $rating = Rating::find($request->rating_id);

        if ($rating) {
            // Update rating details
            $rating->update([
                'name' => $request->name,
                'status' => $request->status,
                'kind_of_rating' => $request->kind_of_rating,
                'is_fixed_wing' => $request->has('is_fixed_wing'),
                'is_rotary' => $request->has('is_rotary'),
                'is_instructor' => $request->has('is_instructor'),
                'is_examiner' => $request->has('is_examiner'),
                'ou_id' => $request->ou_id ?? null,
            ]);

            // Delete old parent relations for this rating
            ParentRating::where('rating_id', $rating->id)->delete();

            // Save new parent relationships
            $parentIds = $request->input('parent_id', []);
            foreach ($parentIds as $parentId) {
                if ($parentId) {
                    ParentRating::create([
                        'rating_id' => $rating->id,
                        'parent_id' => $parentId,
                    ]);
                }
            }

            Session::flash('message', 'Rating updated successfully.');

            return response()->json([
                'success' => true,
                'msg' => 'Rating updated successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'msg' => 'Rating not found.'
            ]);
        }
    }

    public function deleteRating(Request $request)
    {
        $rating = Rating::findOrFail(decode_id($request->rating_id));

        if ($rating) {
            // Soft delete all parent-child mappings involving this rating
            ParentRating::where('rating_id', $rating->id)
                ->orWhere('parent_id', $rating->id)
                ->get()
                ->each(function ($relation) {
                    $relation->delete(); // Uses soft delete
                });

            // Soft delete the rating
            $rating->delete();

            return redirect()->route('users.rating')->with('message', 'Rating and its hierarchy soft deleted successfully.');
        }

        return redirect()->route('users.rating')->with('error', 'Rating not found.');
    }

    public function getRatingsByOU(Request $request)
    {
        $ouId = $request->ou_id;
        $ratingId = decode_id($request->rating_id);
        $selectedParents = is_array($request->selected_parents) ? array_map('intval', $request->selected_parents) : [];

        $allRatings = Rating::query()
            ->when($ouId, fn($q) => $q->where('ou_id', $ouId))
            ->get();

        $relations = ParentRating::all()->groupBy('parent_id');
        $dropdownOptions = collect();
        $usedIds = collect();

        $this->buildFullHierarchy($relations, $allRatings, null, 0, $dropdownOptions, $usedIds);

        // Add selected parents even if not part of current OU
        $missing = Rating::whereIn('id', $selectedParents)->whereNotIn('id', $usedIds)->get();
        foreach ($missing as $rating) {
            $dropdownOptions->push((object)[
                'id' => $rating->id,
                'name' => '↳ ' . e($rating->name),
                'ou_id' => $rating->ou_id
            ]);
        }

        return response()->json([
            'success' => true,
            'selected' => array_map('strval', $selectedParents),
            'dropdown' => $dropdownOptions->map(fn($item) => [
                'id' => $item->id,
                'name' => strip_tags(html_entity_decode($item->name)),
            ]),
        ]);
    }

    public function ou_rating()
    {
        $allRatings = Rating::all(); // All ratings (from ratings table)
        $organizationUnits = OrganizationUnits::all(); // For UI context

        // Group parent-child mappings from parent_rating table
        $parentRelations = ParentRating::all()->groupBy('parent_id');

        $ratingDropdownOptions = collect(); // To store formatted dropdown items
        $usedIds = collect(); // To track already added children

        // Build hierarchy from parent_rating
        $this->buildFullHierarchy($parentRelations, $allRatings, null, 0, $ratingDropdownOptions, $usedIds);

        $ou_id = auth()->user()->ou_id;

        // Get selected rating IDs for current OU
        $selectedRatingIds = OuRating::where('ou_id', $ou_id)->pluck('rating_id')->toArray();

        return view('users.ou_show_rating', [
            'ratings' => $allRatings->groupBy('name'),
            'ratingDropdownOptions' => $allRatings,
            'organizationUnits' => $organizationUnits,
            'selectedRatingIds' => $selectedRatingIds
        ]);
    }

    public function select_rating(Request $request)
    {
        $rating_id = decode_id($request->rating_id);
        $ou_id     = auth()->user()->ou_id;

        // Check if rating already exists for this OU
        $exists = OuRating::where('rating_id', $rating_id)
            ->where('ou_id', $ou_id)
            ->exists();

        if ($exists) {

            return response()->json([
                'success' => false,
                'message' => 'Rating already selected for this OU.'
            ]);
        }

        // Save the new rating
        $store_ou_rating = OuRating::create([
            'rating_id' => $rating_id,
            'ou_id'     => $ou_id
        ]);

        if ($store_ou_rating) {
            Session::flash('message', 'Rating saved successfully');
            return response()->json([
                'success' => true,
                'message' => 'Rating selected successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to select rating.'
        ]);
    }

    public function deselect_rating(Request $request)
    {
        $rating_id = decode_id($request->rating_id);
        $ou_id = auth()->user()->ou_id;

        $deleted = OuRating::where('rating_id', $rating_id)
            ->where('ou_id', $ou_id)
            ->delete();

        if ($deleted) {
            Session::flash('message', 'Rating removed successfully');
            return response()->json([
                'success' => true,
                'message' => 'Rating deselected successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to deselect rating or it was not found.'
        ]);
    }
}
