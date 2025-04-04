<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OrganizationUnits;
use App\Models\Role;
use App\Models\UserActivityLog;
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
    
         return view('users.index', compact('roles', 'organizationUnits'));
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
        $id =  auth()->user()->id; 
        $user = User::where('id',$id)->first();

        return view('users.profile', compact('user'));  
    }

    public function profileUpdate(Request $request)
    {

        $userToUpdate = User::find($request->id);  
      
         

            if ($userToUpdate) {
                $rules = [];
           
                if ($userToUpdate->licence_required === 1 && empty($userToUpdate->licence) ) {    
                    $rules['licence'] = 'required';
                    $rules['licence_expiry_date'] = 'required'; 
                     // Require a new file only if there's no existing file
                     if (!$userToUpdate->licence_file) {
                        $rules['licence_file'] = 'required|file|mimes:pdf,jpg,jpeg,png';
                    } 
                   
                    
                }
            
                if ($userToUpdate->passport_required == 1 && empty($userToUpdate->passport) ) {
                    $rules['passport'] = 'required';
                    $rules['passport_expiry_date'] = 'required';
                    $rules['passport_file'] = 'required';
                }
            
                if ($userToUpdate->medical == 1) {
                    $rules['issued_by'] = 'required';
                    $rules['medical_class'] = 'required';
                    $rules['medical_issue_date'] = 'required';
                    $rules['medical_expiry_date'] = 'required';
                    $rules['medical_detail'] = 'required';
                    if (!$userToUpdate->medical_file) { 
                        $rules['medical_file'] = 'required|file|mimes:pdf,jpg,jpeg,png';
                    }
                }
                $licenceFileUploaded = $userToUpdate->licence_file_uploaded;
                $passportFileUploaded = $userToUpdate->passport_file_uploaded;
                $medicalFileUploaded = $userToUpdate->medical_file_uploaded;

                if ($request->hasFile('medical_file')) {
                    if ($userToUpdate->medical_file) {
                        Storage::disk('public')->delete($userToUpdate->medical_file);
                    }
                    $medicalFilePath = $request->file('medical_file')->store('medical_file', 'public');
                   
                    $medicalFileUploaded = true;
                    $userToUpdate->update(['medical_verified' => 0]);
                } else {
                    $medicalFilePath = $userToUpdate->medical_file;
                }

            
                if ($userToUpdate->currency_required == 1) {
                    $rules['currency'] = 'required|string';
                }
            
                if (!empty($rules)) {
                    $request->validate($rules);
                }
            }
          
       
            

            // Handle Licence File Upload
            if ($userToUpdate->licence_required == 1) {
                if ($request->hasFile('licence_file')) {
                    $request->validate([
                        'licence' =>  'required',
                        'licence_expiry_date' => 'required',
                    ]);

                    if ($userToUpdate->licence_file) {
                        Storage::disk('public')->delete($userToUpdate->licence_file);
                    }
                    $licenceFilePath = $request->file('licence_file')->store('licence_files', 'public');
                    $licenceFileUploaded = true;
                    $userToUpdate->update(['licence_verified' => 0]);
                } else {
                    $licenceFilePath = $request->old_licence_file ?? $userToUpdate->licence_file;
                }
            } else {
                $licenceFilePath = $userToUpdate->licence_file;
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


            $newData = [
                'fname' => $request->firstName,
                'lname' => $request->lastName,
                'email' => $request->email,
                'licence' => $request->licence ?? $userToUpdate->licence,
                'licence_expiry_date' => $request->licence_expiry_date ?? $userToUpdate->licence_expiry_date,
                'licence_file' => $licenceFilePath,
                'passport' => $request->passport ?? $userToUpdate->passport,
                'passport_expiry_date' => $request->passport_expiry_date ?? $userToUpdate->passport_expiry_date,
                'passport_file' => $passportFilePath,
                'currency' => $request->currency ?? null,
                'medical_issuedby' => $request->issued_by ?? null,
                'medical_class' => $request->medical_class ?? null,
                'medical_issuedate' => $request->medical_issue_date ?? null,
                'medical_expirydate' => $request->medical_expiry_date ?? null,
                'medical_restriction' => $request->medical_detail ?? null,
                'medical_file' =>$medicalFilePath,
                'licence_file_uploaded' => $licenceFileUploaded,  
                'passport_file_uploaded' => $passportFileUploaded,
                'medical_file_uploaded' => $medicalFileUploaded
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

        //   Session::flash('message', 'User saved successfully');
        return response()->json(['success' => true,'message' => "User profile updated successfully"]);
    }

    public function save_user(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|max:255|unique:users,email',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:25600',
            'password' => 'required|min:6|confirmed',
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
        ]);

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

        if ($request->hasFile('passport_file')) {
            $passport_file = $request->file('passport_file')->store('user_documents', 'public');
        }

        if ($request->has('medical_checkbox') && $request->medical_checkbox == 1) {
            $medicalFilePath = $request->file('medical_file')->store('medical_file', 'public');
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
            "passport_required"   => $passport_required,
            "passport"            => $request->passport ?? null,
            "passport_file"       => $passport_file ?? null,
            "rating_required"     => $rating_required,
            "rating"              => $request->rating ?? null,
            "currency_required"   => $currency_required,
            "currency"            => $request->currency ?? null,
            "custom_field_name"   => $request->custom_field_name ?? null,
            "custom_field_value"  => $request->custom_field_value ?? null,
            'status'              => $request->status,
            "ou_id"               => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
            "extra_roles"         => !empty($request->extra_roles) ? json_encode($request->extra_roles) : json_encode([]),
            "custom_field_file"  => $request->custom_field_date ?? null,
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
        
        $userToUpdate = User::find($request->edit_form_id);

        if ($userToUpdate) {
            $validatedData = $request->validate([
                'edit_firstname' => 'required',
                'edit_lastname' => 'required',
                'edit_email' => 'required|email',
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
                ]
            ], [
                'edit_firstname.required' => 'The First Name is required',
                'edit_lastname.required' => 'The Last Name is required',
                'edit_email.required' => 'The Email is required',
                'edit_email.email' => 'Please enter a valid Email',
                'password' => 'The password and confirm password do not match.',
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
            if ($request->has('edit_licence_checkbox') && $request->edit_licence_checkbox == 'on') {
                $licence_required = 1;
                $licenceFilePath = $request->hasFile('edit_licence_file')
                    ? $request->file('edit_licence_file')->store('licence_files', 'public')
                    : $userToUpdate->licence_file;
            } else {
                $licence_required = null;
                $licenceFilePath = $userToUpdate->licence_file;
            }

            // Handle Passport
            if ($request->has('edit_passport_checkbox') && $request->edit_passport_checkbox == 'on') {
                $passport_required = 1;
                $passportFilePath = $request->hasFile('edit_passport_file')
                    ? $request->file('edit_passport_file')->store('passport_files', 'public')
                    : $userToUpdate->passport_file;
            } else {
                $passport_required = null;
                $passportFilePath = $userToUpdate->passport_file;
            }

              // Handle Medical
              if ($request->has('editmedical_checkbox') && $request->editmedical_checkbox == 1) {
               // $passport_required = 1;
                $medicalFilePath = $request->hasFile('editmedical_file')
                    ? $request->file('editmedical_file')->store('medical_file', 'public')
                    : $userToUpdate->passport_file;
            } 
            else {
              //  $passport_required = null;
                $medicalFilePath = $userToUpdate->medical_file;
            }

            // Handle Currency Requirement
            $currency_required = $request->has('edit_currency_checkbox') ? 1 : null;

            // Handle Rating Requirement
            $rating_required = $request->has('edit_rating_checkbox') ? 1 : $userToUpdate->rating_required;

            // Handle Custom Fields
            $editcustom_field_date = $request->has('editcustom_file_checkbox') ? $request->editcustom_field_date : null;
            $editcustom_field_text = $request->has('editcustom_text_checkbox') ? $request->editcustom_field_text : null;

            // Handle Password Flag
            $password_flag = $request->has('edit_custom_field_checkbox') ? 1 : $userToUpdate->password_flag;

            // Handle Password
            $password = $request->has('password') && $request->password ? Hash::make($request->password) : $userToUpdate->password;

            // Handle Extra Roles
            $extra_roles = $request->has('extra_roles') ? json_encode($request->extra_roles) : $userToUpdate->extra_roles;

            // Determine is_admin value
            $is_admin = (!empty($request->ou_id) && $request->edit_role_name == 1) ? 1 : null;

            // Handle Medical Information
            $medical_checkbox              = $request->has('editmedical_checkbox') ? $request->editmedical_checkbox : null;
            $medical_verification_required = $request->has('editmedical_checkbox') ? $request->editmedical_verification_required : null;
            $medical_issued_by             = $request->has('editmedical_checkbox') ? $request->editissued_by : null;
            $medical_class                 = $request->has('editmedical_checkbox') ? $request->editmedical_class : null;
            $medical_issue_date            = $request->has('editmedical_checkbox') ? $request->editmedical_issue_date : null;
            $medical_expiry_date           = $request->has('editmedical_checkbox') ? $request->editmedical_expiry_date : null;
            $medical_detail                = $request->has('editmedical_checkbox') ? $request->editmedical_detail : null;

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
                'rating' => $request->edit_rating ?? null,
                'rating_required' => $rating_required,
                'currency_required' => $currency_required,
                'currency' => $request->edit_currency ?? null, 
                'custom_field_file' => $editcustom_field_date,
                'custom_field_text' => $editcustom_field_text,
                'password_flag' => $password_flag,
                'extra_roles' => $extra_roles,
                'medical' => $medical_checkbox,
                'medical_adminRequired' => $medical_verification_required,
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
        $user = User::find(decode_id($request->id));
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
        $user = User::with('roles', 'organization')->find(decode_id($user_id));
    
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
    
        // Fetch extra role names directly in one line
        $extraRoles = Role::whereIn('id', json_decode($user->extra_roles ?? '[]'))->pluck('role_name')->toArray();
    // dd($user);
        return view('users.show', compact('user', 'extraRoles'));
    }

    public function docsVerify(Request $request)
    {
       // dump($request->all());
        // Decode the encoded userId
        $decodedUserId = decode_id($request->userId);
       // dump($request->documentType);
        // Validate the incoming request
        $request->validate([
            'userId' => [
                'required',
                function ($attribute, $value, $fail) use ($decodedUserId) {
                    // Check if the decoded userId exists in the users table
                    if (!User::where('id', $decodedUserId)->exists()) {
                        $fail('The selected user id is invalid.');
                    }
                },
            ],
            'documentType' => 'required|in:passport,licence,medical',
            'verified' => 'required|boolean',
        ]);
   
        // Find the user using the decoded userId
        $user = User::find($decodedUserId);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }
    
        // Determine which column to update

        $column = $request->documentType . '_verified'; // Either 'passport_verified' or 'licence_verified'
      
    
        // Update the user record
        $user->update([$column => $request->verified]);
    
        return response()->json(['success' => 'Verification status updated successfully.']);
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
}