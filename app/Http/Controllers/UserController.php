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
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;


class UserController extends Controller
{

    public function getData(Request $request)
    {
        $ou_id = auth()->user()->ou_id; 
        $is_owner = auth()->user()->is_owner; 
    
        $organizationUnits = OrganizationUnits::all();
        $roles = Role::all(); 
        $rating = Rating::where('status', 1)->get(); 

        if ($is_owner) { 
            $users = User::all();
        } else {  
            $users = User::where('ou_id', $ou_id)->get();
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
                    if (!$is_owner && !empty($ou_id)) {
                        $query->where('users.ou_id', $ou_id);
                    }
               
        return DataTables::of($query)
        ->filterColumn('position', function($query, $keyword) {
            $query->where('roles.role_name', 'LIKE', "%{$keyword}%");
        })
        ->filterColumn('organization', function($query, $keyword) {
            $query->where('organization_units.org_unit_name', 'LIKE', "%{$keyword}%");
        })
            ->addColumn('status', function ($user) {
                return $user->status == 1 ? '<span class="badge bg-success">Active</span>' 
                                          : '<span class="badge bg-danger">Inactive</span>';
            })
            ->filterColumn('organization', function($query, $keyword) {
                $query->where('organization_units.org_unit_name', 'LIKE', "%{$keyword}%");
            })
                ->addColumn('status', function ($user) {
                    return $user->status == 1 ? '<span class="badge bg-success">Active</span>' 
                                              : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = url('users/show/' . encode_id($row->id)); 
                    $viewBtn = '';
                    $editBtn = '';
                    $delete = '';

                    if(checkAllowedModule('users','user.index')->isNotEmpty())  {
                        $viewBtn = '<a href="' . $viewUrl . '" class="view-icon" title="View User" 
                                      style="font-size:18px; cursor: pointer;">
                                      <i class="fa fa-eye text-danger me-2"></i>
                                   </a>';   
                    }

                    if(checkAllowedModule('users','user.get')->isNotEmpty())  {
                            $editBtn = '<i class="fa fa-edit edit-user-icon text-primary me-2" 
                                          style="font-size:18px; cursor: pointer;" 
                                          data-user-id="' . encode_id($row->id) . '">
                                       </i>';
                    }      
                      
                
                    if(checkAllowedModule('users','user.destroy')->isNotEmpty())  {
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
    
         return view('users.index', compact('roles', 'organizationUnits','rating'));
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
    
        // Fetch the user along with their related ratings
        $user = User::with('usrRatings.rating', 'documents')->findOrFail($id);
        // dd($user);
        // Extract the related ratings from the user ratings
        $ratings = $user->usrRatings->map(function ($userRating) {
            return $userRating->rating;
        });
    
        return view('users.profile', compact('user', 'ratings'));
    }
    
    public function profileUpdate(Request $request)
    {
        // dd($request->all());
        $userToUpdate = User::find($request->id);  
        $document = UserDocument::where('user_id', $userToUpdate->id )->first();
        // dd($request->has('non_expiring_licence'));
            if ($userToUpdate) {
                $rules = [];
           
                if ($userToUpdate->licence_required === 1) {    
                    if ($request->filled('licence')) {
                        $rules['licence'] = 'required';
                        if(!$request->has('licence_expiry_date') || !$request->has('non_expiring_licence')){
                            $rules['licence_expiry_date'] = 'required'; 
                        }
                        // Require a new file only if there's no existing file
                        if (!$userToUpdate->licence_file) {
                            $rules['licence_file'] = 'required|file|mimes:pdf,jpg,jpeg,png';
                        } 
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
                        $rules['passport_expiry_date'] = 'required';
                
                        if (!$userToUpdate->passport_file) {
                            $rules['passport_file'] = 'required|file|mimes:pdf,jpg,jpeg,png';
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
                
                        $rules['medical_issue_date'] = 'required';
                        $rules['medical_expiry_date'] = 'required';
                        // $rules['medical_detail'] = 'required';
                
                        if (!$userToUpdate->medical_file) {
                            $rules['medical_file'] = 'required|file|mimes:pdf,jpg,jpeg,png';
                        }
                    }
                }

                if ($request->filled('issued_by_2')) {
                    $rules['medical_issue_date_2'] = 'required';
                    $rules['medical_expiry_date_2'] = 'required';

                    if (!$document->medical_file_2) {
                        $rules['medical_file_2'] = 'required|file|mimes:pdf,jpg,jpeg,png';
                    }
                }

                $customAttributes = [
                    'medical_issue_date_2' => 'medical issue date',
                    'medical_expiry_date_2' => 'medical expiry date',
                    'medical_file_2' => 'medical file',
                ];

                $this->validate($request, $rules, [], $customAttributes);

                if ($request->filled('licence_2')) {
                    $rules['licence_expiry_date_2'] = 'required';
                    $rules['licence_file_2'] = 'required';

                    if (!$document->licence_file_2) {
                        $rules['licence_file_2'] = 'required|file|mimes:pdf,jpg,jpeg,png';
                    }
                    else {
                            $rules['licence_file_2'] = 'nullable|file|mimes:pdf,jpg,jpeg,png';
                    }
                }

                $customAttributes = [
                    'licence_expiry_date_2' => 'licence expiry date',
                    'licence_file_2' => 'licence 2 file',
                ];

                $this->validate($request, $rules, [], $customAttributes);


                $licenceFileUploaded = $userToUpdate->licence_file_uploaded;
                $passportFileUploaded = $userToUpdate->passport_file_uploaded;
                $medicalFileUploaded = $userToUpdate->medical_file_uploaded;
                $medicalFileUploaded_2 = null;

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
                    $document->update(['medical_verified_2' => 0]);
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
                                $rules["rating_file.$ratingId"] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
                            } else {
                                $rules["rating_file.$ratingId"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048';
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
                $licenceFileUploaded = true;
                $document->update(['licence_verified_2' => 0]);
            } else {
                $licenceFilePath_2 = $request->old_licence_file_2;
            }

            // Handle Passport File Upload
            if ($userToUpdate->passport_required == 1) {
                if ($request->hasFile('passport_file')) {
                    if ($userToUpdate->passport_file) {
                        Storage::disk('public')->delete($userToUpdate->passport_file);
                    }
                    $passportFilePath = $request->file('passport_file')->store('passport_files', 'public');
                    $passportFileUploaded = true;
                    $userToUpdate->update(['passport_verified' => 0]);
                } else {
                    $passportFilePath = $request->old_passport_file ?? $userToUpdate->passport_file;
                }
            } else {
                $passportFilePath = $userToUpdate->passport_file;
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
                'medical_file' =>$medicalFilePath,
                'licence_file_uploaded' => $licenceFileUploaded,  
                'passport_file_uploaded' => $passportFileUploaded,
                'medical_file_uploaded' => $medicalFileUploaded,
                'custom_field_date' => $request->custom_field_date ?? $userToUpdate->custom_field_date,
                'custom_field_text' => $request->custom_field_text ?? $userToUpdate->custom_field_text
            ];

            //dd($newData);
        
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
            $userToUpdate->update($newData);
            if (!empty($changes)) {
                UserActivityLog::create([
                    'user_id' => auth()->user()->id,
                    'log_type' => 'Profile Update',
                    'description' => implode("\n", $changes), // New line for better readability
                ]);
            }

            // $document = UserDocument::firstOrNew(['user_id' => $userToUpdate->id]);

            // $document->licence = $request->licence ?? null;
            // $document->licence_file = $licenceFilePath ?? null;
            // $document->licence_expiry_date = $request->licence_expiry_date ?? $document->licence_expiry_date;

            // $document->licence_2 = $request->licence_2 ?? null;
            // $document->licence_file_2 = $licenceFilePath_2 ?? null;
            // $document->licence_expiry_date_2 = $request->licence_expiry_date_2 ?? $document->licence_expiry_date_2;

            // $document->passport = $request->passport ?? null;
            // $document->passport_expiry_date = $request->passport_expiry_date ?? $document->passport_expiry_date;
            // $document->passport_file = $passportFilePath ?? null;

            // $document->medical = $userToUpdate->medical;
            // $document->medical_issuedby = $request->issued_by ?? $document->medical_issuedby;
            // $document->medical_class = $request->medical_class ?? $document->medical_class;
            // $document->medical_issuedate = $request->medical_issue_date ?? $document->medical_issuedate;
            // $document->medical_expirydate = $request->medical_expiry_date ?? $document->medical_expirydate;
            // $document->medical_restriction = $request->medical_detail ?? $document->medical_restriction;
            // $document->medical_file = $medicalFilePath ?? $document->medical_file;

            // $document->medical_2 = $userToUpdate->medical;
            // $document->medical_issuedby_2 = $request->issued_by_2 ?? $document->medical_issuedby_2;
            // $document->medical_class_2 = $request->medical_class_2 ?? $document->medical_class_2;
            // $document->medical_issuedate_2 = $request->medical_issue_date_2 ?? $document->medical_issuedate_2;
            // $document->medical_expirydate_2 = $request->medical_expiry_date_2 ?? $document->medical_expirydate_2;
            // $document->medical_restriction_2 = $request->medical_detail_2 ?? $document->medical_restriction_2;
            // $document->medical_file_2 = $medicalFilePath_2 ?? $document->medical_file_2;

            $userToUpdate->documents()->updateOrCreate(
            ['user_id' => $userToUpdate->id], // Unique identifying condition

            [ // Fields to update or set
                'licence' => $request->licence ?? null,
                'licence_file' => $licenceFilePath ?? null,
                'licence_expiry_date' => $request->licence_expiry_date ?? null,

                'licence_2' => $request->licence_2 ?? null,
                'licence_file_2' => $licenceFilePath_2 ?? null,
                'licence_expiry_date_2' => $request->licence_expiry_date_2 ?? null,

                'passport' => $request->passport ?? null,
                'passport_expiry_date' => $request->passport_expiry_date ?? null,
                'passport_file' => $passportFilePath ?? null,

                'medical' => $userToUpdate->medical,
                'medical_issuedby' => $request->issued_by ?? null,
                'medical_class' => $request->medical_class ?? null,
                'medical_issuedate' => $request->medical_issue_date ?? null,
                'medical_expirydate' => $request->medical_expiry_date ?? null,
                'medical_restriction' => $request->medical_detail ?? null,
                'medical_file' => $medicalFilePath ?? null,

                'medical_2' => $userToUpdate->medical,
                'medical_issuedby_2' => $request->issued_by_2 ?? null,
                'medical_class_2' => $request->medical_class_2 ?? null,
                'medical_issuedate_2' => $request->medical_issue_date_2 ?? null,
                'medical_expirydate_2' => $request->medical_expiry_date_2 ?? null,
                'medical_restriction_2' => $request->medical_detail_2 ?? null,
                'medical_file_2' => $medicalFilePath_2 ?? null,
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
            return response()->json(['success' => true,'message' => "User profile updated successfully"]);
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
    

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('users', 'public');
        }

        if ($request->hasFile('licence_file')) {
            $licence_file = $request->file('licence_file')->store('user_documents', 'public');
        }
        if ($request->hasFile('licence_file_2')) {
            $licence_file_2 = $request->file('licence_file_2')->store('user_documents', 'public');
        }

        if ($request->hasFile('passport_file')) {
            $passport_file = $request->file('passport_file')->store('user_documents', 'public');
        }

        if ($request->hasFile('medical_file')) {
            $medicalFilePath = $request->file('medical_file')->store('medical_file', 'public');
        } 

        if ($request->hasFile('medical_file_2')) {
            $medicalFilePath_2 = $request->file('medical_file_2')->store('medical_file', 'public');
        } 
        

        // Determine is_admin value
        $is_admin = (!empty($request->ou_id) && $request->role_name==1)? 1 : null;
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
                'licence_2' => $request->licence_2 ?? null,
                'licence_file_2' => $licence_file_2 ?? null,
                'licence_admin_verification_required_2' => $request->licence_verification_required_2 ?? 0,
                'passport' => $request->passport ?? null,
                'passport_file' => $passport_file ?? null,
                'passport_admin_verification_required' => $request->passport_verification_required ?? 0,
                'medical' => $medical_checkbox,
                'medical_issuedby' => $medical_issued_by,
                'medical_class' => $medical_class,
                'medical_issuedate' =>  $medical_issue_date,
                'medical_expirydate' =>  $medical_expiry_date,
                'medical_restriction' => $medical_detail,
                'medical_file' => $medicalFilePath ?? null,
                'medical_2' => $medical_checkbox,
                'medical_issuedby_2' => $request->issued_by_2,
                'medical_class_2' => $request->medical_class_2,
                'medical_issuedate_2' =>  $request->medical_issue_date_2,
                'medical_expirydate_2' => $request->medical_expiry_date_2 ,
                'medical_restriction_2' => $request->medical_detail_2,
                'medical_file_2' => $medicalFilePath_2 ?? null,
            ]);

            // Save ratings in 'user_ratings' table
            if ($request->has('rating') && is_array($request->rating)) {
                foreach ($request->rating as $ratingId) {
                    UserRating::create([
                        'user_id' => $store->id,
                        'rating_id' => $ratingId,
                        'issue_date' => null,     // you can set default/null or extend your form to collect this
                        'expiry_date' => null,
                        'file_path' => null,
                    ]);
                }
            }
    
            // Generate password to send in the email
            $password = $request->password;
    
            UserActivityLog::create([
                'user_id' => auth()->id(),
                'log_type' => 'User Created',
                'description' => "User '{$store->fname} {$store->lname}' (ID: {$store->id}) was created by " . auth()->user()->fname . " " . auth()->user()->lname,
            ]);
            
            // Send emailx
            Mail::to($store->email)->send(new UserCreated($store, $password));

            Session::flash('message', 'User saved successfully');
            return response()->json(['success' => 'User saved successfully']);
        }
    }
    
    public function update(Request $request)
    {

        // dd($request->all());
        $userToUpdate = User::find($request->edit_form_id);
        $UserDocument = UserDocument::where('user_id', $userToUpdate->id)->first();

        if ($userToUpdate) {
            $validatedData = $request->validate([
                'edit_firstname' => 'required',
                'edit_lastname' => 'required',
                'edit_email' => 'required|email|max:255|unique:users,email,' . $request->edit_form_id.',id,deleted_at,NULL',
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
                'edit_rating' => $request->has('edit_rating_checkbox') ? 'required|array|min:1' : 'nullable',
            ], [
                'edit_firstname.required' => 'The First Name is required',
                'edit_lastname.required' => 'The Last Name is required',
                'edit_email.required' => 'The Email is required',
                'edit_email.email' => 'Please enter a valid Email',
                'password' => 'The password and confirm password do not match.',
                'edit_rating' => 'Rating is required',
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

            if ($request->has('edit_licence_checkbox') && $request->edit_licence_checkbox ) {
                    $licence_required = $request->edit_licence_checkbox;
                if ($request->hasFile('edit_licence_file')) {
                    $licenceFilePath = $request->file('edit_licence_file')->store('licence_files', 'public');
                } else {
                    $licenceFilePath = $UserDocument ? $UserDocument->licence_file : null;
                }
            } else {
                $licence_required = null;
                $licenceFilePath = $UserDocument ? $UserDocument->licence_file : null;
            }


            if ($request->hasFile('edit_licence_file_2')) {
                $licenceFilePath_2 = $request->file('edit_licence_file_2')->store('licence_files', 'public');
            } 
            else {
                $licenceFilePath_2 = $UserDocument ? $UserDocument->licence_file_2 : null;
            }

            // Handle Passport
            if ($request->has('edit_passport_checkbox') && $request->edit_passport_checkbox == 'on') {
                $passport_required = 1;
                $passportFilePath = $request->hasFile('edit_passport_file')
                    ? $request->file('edit_passport_file')->store('passport_files', 'public')
                    : $UserDocument->passport_file;
            } else {
                $passport_required = null;
                $passportFilePath =  $UserDocument ? $UserDocument->passport_file : null;
            }

              // Handle Medical
              $medicalFilePath = null;
            if ($request->has('editmedical_checkbox') && $request->editmedical_checkbox == 1) {
                if($request->hasFile('editmedical_file')){
                    $medicalFilePath = $request->file('editmedical_file')->store('medical_file', 'public');
                }
            } 
            else {
                $medicalFilePath = $UserDocument ? $UserDocument->medical_file : null;
            }

            if ($request->hasFile('editmedical_file_2')) {
                $medicalFilePath_2 = $request->file('editmedical_file_2')->store('medical_file', 'public');
            } 
            else {
                $medicalFilePath_2 = $UserDocument ? $UserDocument->medical_file_2 : null;

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
            if((!empty($request->ou_id) && $request->edit_role_name == 1) || auth()->user()->is_admin==1)
            {
                $is_admin = 1;
            }else{
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
            $userToUpdate->update($newData);

            // UserDocument::where('user_id', $userToUpdate->id)->Createorupdate([
            //     'licence' =>  $request->edit_licence ?? null,
            //     'licence_file' => $licenceFilePath ?? null,
            //     'licence_admin_verification_required' => $request->edit_licence_verification_required ?? 0,
            //     'licence_2' => $request->edit_licence_2 ?? null,
            //     'licence_file_2' => $licenceFilePath_2 ?? null,
            //     'licence_admin_verification_required_2' => $request->edit_licence_verification_required_2 ?? 0,
            //     'passport' => $request->edit_passport ?? null,
            //     'passport_file' => $passportFilePath ?? null,
            //     'passport_admin_verification_required' => $request->edit_passport_verification_required ?? 0,
            //     'medical' => $medical_checkbox,
            //     'medical_issuedby' => $medical_issued_by,
            //     'medical_class' => $medical_class,
            //     'medical_issuedate' =>  $medical_issue_date,
            //     'medical_expirydate' =>  $medical_expiry_date,
            //     'medical_restriction' => $medical_detail,
            //     'medical_file' => $medicalFilePath ?? null,
            //     'medical_2' => $medical_checkbox,
            //     'medical_issuedby_2' => $request->editissued_by_2,
            //     'medical_class_2' => $request->editmedical_class_2,
            //     'medical_issuedate_2' =>  $request->editmedical_issue_date_2,
            //     'medical_expirydate_2' => $request->editmedical_expiry_date_2 ,
            //     'medical_restriction_2' => $request->editmedical_detail_2,
            //     'medical_file_2' => $medicalFilePath_2 ?? null,
            // ]);
            UserDocument::updateOrCreate(
                ['user_id' => $userToUpdate->id], // Search criteria
                [
                    'licence' =>  $request->edit_licence ?? null,
                    'licence_file' => $licenceFilePath ?? null,
                    'licence_admin_verification_required' => $request->edit_licence_verification_required ?? 0,
                    'licence_2' => $request->edit_licence_2 ?? null,
                    'licence_file_2' => $licenceFilePath_2 ?? null,
                    'licence_admin_verification_required_2' => $request->edit_licence_verification_required_2 ?? 0,
                    'passport' => $request->edit_passport ?? null,
                    'passport_file' => $passportFilePath ?? null,
                    'passport_admin_verification_required' => $request->edit_passport_verification_required ?? 0,
                    'medical' => $medical_checkbox,
                    'medical_issuedby' => $medical_issued_by,
                    'medical_class' => $medical_class,
                    'medical_issuedate' =>  $medical_issue_date,
                    'medical_expirydate' =>  $medical_expiry_date,
                    'medical_restriction' => $medical_detail,
                    'medical_file' => $medicalFilePath ?? null,
                    'medical_2' => $medical_checkbox,
                    'medical_issuedby_2' => $request->editissued_by_2,
                    'medical_class_2' => $request->editmedical_class_2,
                    'medical_issuedate_2' =>  $request->editmedical_issue_date_2,
                    'medical_expirydate_2' => $request->editmedical_expiry_date_2,
                    'medical_restriction_2' => $request->editmedical_detail_2,
                    'medical_file_2' => $medicalFilePath_2 ?? null,
                ]
            );
            

            // === Handle User Ratings (NEW) ===
            if ($request->has('edit_rating_checkbox')) {
                // Remove previous ratings
                UserRating::where('user_id', $userToUpdate->id)->delete();

                // Save new ratings
                foreach ($request->edit_rating as $ratingId) {
                    UserRating::create([
                        'user_id' => $userToUpdate->id,
                        'rating_id' => $ratingId
                    ]);
                }
            }

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
    
        return response()->json(['user' => $user]);
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
        $user = User::with(['roles', 'organization', 'usrRatings.rating', 'documents'])->find(decode_id($user_id));
    
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
        // dd($user);
            // Fetch extra role names directly in one line
            $extraRoles = Role::whereIn('id', json_decode($user->extra_roles ?? '[]'))->pluck('role_name')->toArray();
        // dd($user);
        return view('users.show', compact('user', 'extraRoles'));
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

    //Rating Methods
    public function showRating()
    {
        $ratings = Rating::all();
        return view('users.ratings.show', compact('ratings'));
    } 

    public function saveRating(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:ratings,name,NULL,id,deleted_at,NULL',
            'status' => 'required|boolean',
        ]);
    
        Rating::create($request->only('name', 'status'));
    
        Session::flash('message', 'Rating saved successfully');
        return response()->json(['success' => true, 'msg'=> 'Rating saved successfully.']);
    }

    public function getRating(Request $request)
    {
       $rating = Rating::find(decode_id($request->rating_id));
       if($rating){
            return response()->json(['success'=> true,'rating'=> $rating]);
       }else{
            return response()->json(['success'=> false,'msg'=> 'Rating not gound']);            
       }
    }

    public function updateRating(Request $request)
    {
        $request->validate([
            'rating_id' => 'required|integer',
            'name' => 'required|string|unique:ratings,name,' . $request->rating_id . ',id,deleted_at,NULL',
            'status' => 'required|in:0,1',
        ]);
    
        $rating = Rating::find($request->rating_id);
    
        if ($rating) {
            $rating->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);
    
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
            $rating->delete();
            return redirect()->route('users.rating')->with('message', 'Rating deleted successfully.');
        }
        return redirect()->route('users.rating')->with('error', 'Rating not found.');
    }
    
    


}