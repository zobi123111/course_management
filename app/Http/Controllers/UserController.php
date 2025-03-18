<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OrganizationUnits;
use App\Models\Role;
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
    $organizationUnits = OrganizationUnits::all();
    $roles = Role::all(); 

    if (empty($ou_id)) {
        $users = User::all();
        // dd($roles);
    } else { 
        $users = User::where('ou_id', $ou_id)->get();
        // $roles = Role::where('id', '!=', 1)->get(); 
        // dd($roles);
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
            ->addColumn('action', function ($row) {
                $viewUrl = url('users/show/' . encode_id($row->id));
                $editBtn = '<i class="fa fa-edit edit-user-icon text-primary me-2" 
                                style="font-size:18px; cursor: pointer;" 
                                data-user-id="' . encode_id($row->id) . '">
                            </i>';
                
                $viewBtn = '<a href="' . $viewUrl . '" class="view-icon" title="View User" 
                                style="font-size:18px; cursor: pointer;">
                                <i class="fa fa-eye text-danger me-2"></i>
                            </a>';
                $delete =  '<i class="fa-solid fa-trash delete-icon text-danger" 
                                style="font-size:18px; cursor: pointer;" 
                                data-user-id="' . encode_id($row->id) . '">
                            </i>';
                return $viewBtn . ' ' . $editBtn . ' ' . $delete;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    return view('users.index', compact('roles', 'organizationUnits'));
}


    public function profile()
    {
        $id =  auth()->user()->id;
        $user = User::where('id',$id)->first();

        return view('users.profile', compact('user'));
    }


    public function profileUpdate(Request $request)
    {
        // dd($request->all());
        $userToUpdate = User::find($request->id);

        // dd($userToUpdate);
            if($userToUpdate){
                            
                // if ($request->hasFile('image')) {
                //     if ($userToUpdate->image) {
                //         Storage::disk('public')->delete($userToUpdate->image);
                //     }
            
                //     $filePath = $request->file('image')->store('users', 'public');
                // } else {
                //     $filePath = $userToUpdate->image;
                // }

                if ($userToUpdate->currency_required == 1) {
                    $request->validate([
                        'currency' => 'required|string',
                    ]);
                }

                // Handle Licence File Upload
                if ($userToUpdate->licence_required == 1) {
                    if ($request->hasFile('licence_file')) {
                        // Delete old licence file if it exists
                        if ($userToUpdate->licence_file) {
                            Storage::disk('public')->delete($userToUpdate->licence_file);
                        }
                        // Store new licence file
                        $licenceFilePath = $request->file('licence_file')->store('licence_files', 'public');
                    } else {
                        // If no new file is uploaded, keep the old one
                        $licenceFilePath = $request->old_licence_file ?? $userToUpdate->licence_file;
                    }
                } else {
                    // If licence is not required, keep the old file
                    $licenceFilePath = $userToUpdate->licence_file;
                }

                // Handle Passport File Upload
                if ($userToUpdate->passport_required == 1) {
                    if ($request->hasFile('passport_file')) {
                        // Delete old passport file if it exists
                        if ($userToUpdate->passport_file) {
                            Storage::disk('public')->delete($userToUpdate->passport_file);
                        }
                        // Store new passport file
                        $passportFilePath = $request->file('passport_file')->store('passport_files', 'public');
                    } else {
                        // If no new file is uploaded, keep the old one
                        $passportFilePath = $request->old_passport_file ?? $userToUpdate->passport_file;
                    }
                } else {
                    // If passport is not required, keep the old file
                    $passportFilePath = $userToUpdate->passport_file;
                }

                // if ($request->has('edit_rating_checkbox') && $request->edit_rating_checkbox) {
                //     // $request->validate([
                //     //     'edit_rating' => 'required|integer|min:1|max:5',
                //     // ]);
                //     $userToUpdate->rating_required = 1;

                // }

                if ($userToUpdate->currency_required == 1) {
                    $request->validate([
                        'currency' => 'required|string',
                    ]);
                }

                // if ($request->has('edit_custom_field_checkbox') && $request->edit_custom_field_checkbox) {
                //     $request->validate([
                //         'edit_custom_field_name' => 'required|string',
                //         'edit_custom_field_value' => 'required|string',
                //     ]);
                // }


                // if ($request->has('edit_custom_field_checkbox') && $request->edit_custom_field_checkbox) {
                //     $userToUpdate->password_flag = 1;
                // }

                
                $userToUpdate->where('id', $request->id)
                ->update([
                    // 'image' => $filePath,
                    'licence' => $request->licence ?? null,
                    'licence_file' => $licenceFilePath  ?? null,
                    'passport' => $request->passport  ?? null,
                    'passport_file' => $passportFilePath  ?? null,
                    // 'rating' => $request->edit_rating ?? null,
                    'currency' => $request->currency ?? null,
                    // 'custom_field_name' => $request->edit_custom_field_name ?? null,
                    // 'custom_field_value' => $request->edit_custom_field_value ?? null,
                ]);
                
                return response()->json(['success' => true,'message' => "User profile updated successfully"]);
        }
    }  

    public function save_user(Request $request)
    {
        // dd($request->all());
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
            // $request->validate([
            //     'passport' => 'required|string',
            //     'passport_file' => 'required|mimes:pdf,jpg,jpeg,png',
            // ]);
            $passport_required = 1;
        }

        if ($request->has('rating_checkbox') && $request->rating_checkbox) {
            // $request->validate([
            //     'rating' => 'required|integer|min:1|max:5',
            // ]);
            $rating_required = 1;
        }

        if ($request->has('currency_checkbox') && $request->currency_checkbox) {
            // $request->validate([
            //     'currency' => 'required|string',
            // ]);
            $currency_required = 1;
        }

        if ($request->has('custom_field_checkbox') && $request->custom_field_checkbox) {
            $request->validate([
                'custom_field_name' => 'required|string',
                'custom_field_value' => 'required|string',
            ]);
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

        // Determine is_admin value
        $is_admin = (!empty($request->ou_id) && $request->role_name==1)? 1 : null;
        // dd($is_admin);

        $store_user = array(
            "fname" => $request->firstname, 
            "lname" => $request->lastname,
            "email" => $request->email,
            'image' => $filePath ?? null,
            "password" => Hash::make($request->password),
            "role" => $request->role_name,
            'licence_required' => $licence_required,
            "licence" => $request->licence ?? null,
            "licence_file" => $licence_file ?? null,
            "passport_required" => $passport_required,
            "passport" => $request->passport ?? null,
            "passport_file" => $passport_file ?? null,
            "rating_required" => $rating_required,
            "rating" => $request->rating ?? null,
            "currency_required" => $currency_required,
            "currency" => $request->currency ?? null,
            "custom_field_name" => $request->custom_field_name ?? null,
            "custom_field_value" => $request->custom_field_value ?? null,
            'status' => $request->status,
            "ou_id" => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            "extra_roles" => !empty($request->extra_roles) ? json_encode($request->extra_roles) : json_encode([]),
            "is_admin" => $is_admin
        );

        // dd($store_user);
    
        $store = User::create($store_user);
        if ($store) {
    
            // Generate password to send in the email
            $password = $request->password;
    
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
        if($userToUpdate){
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
                'password' => 'The password and confirm password does not match.',
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

                if ($request->hasFile('edit_licence_file')) {
                    if ($userToUpdate->licence_file) {
                        Storage::disk('public')->delete($userToUpdate->licence_file);
                    }
                    $licenceFilePath = $request->file('edit_licence_file')->store('licence_files', 'public');
                } else {
                    $licenceFilePath = $userToUpdate->licence_file;
                }
            } else {
                $licence_required = null;
                $licenceFilePath = $userToUpdate->licence_file;
            }

            // Handle Passport
            if ($request->has('edit_passport_checkbox') && $request->edit_passport_checkbox == 'on') {
                $passport_required = 1;

                if ($request->hasFile('edit_passport_file')) {
                    if ($userToUpdate->passport_file) {
                        Storage::disk('public')->delete($userToUpdate->passport_file);
                    }
                    $passportFilePath = $request->file('edit_passport_file')->store('passport_files', 'public');
                } else {
                    $passportFilePath = $userToUpdate->passport_file;
                }
            } else {
                $passport_required = null;  // Set to null if checkbox is unchecked
                $passportFilePath = $userToUpdate->passport_file;  // Retain the existing file if no new file is uploaded
            }
            
            // Handle Currency Requirement
            if ($request->has('edit_currency_checkbox') && $request->edit_currency_checkbox) {
                $currency_required = 1;
            } else {
                $currency_required = null;
            }

            // Handle Rating Requirement
            if ($request->has('edit_rating_checkbox') && $request->edit_rating_checkbox) {
                $userToUpdate->rating_required = 1;
            }

            // Handle Custom Field Validation
            if ($request->has('edit_custom_field_checkbox') && $request->edit_custom_field_checkbox) {
                $request->validate([
                    'edit_custom_field_name' => 'required|string',
                    'edit_custom_field_value' => 'required|string',
                ]);
            }

            if ($request->has('edit_custom_field_checkbox') && $request->edit_custom_field_checkbox) {
                $userToUpdate->password_flag = 1;
            }

            if ($request->has('password') && $request->password) {
                $password =   Hash::make($request->password);
            }else{
                $password =  $userToUpdate->password;
            }

             // Handle Extra Roles
            $extra_roles = $request->has('extra_roles') ? json_encode($request->extra_roles) : $userToUpdate->extra_roles;

            // Determine is_admin value
            $is_admin = (!empty($request->ou_id) && $request->edit_role_name==1)? 1 : null;

            // Update User Information
            $userToUpdate->where('id', $request->edit_form_id)
                ->update([
                    'Fname' => $validatedData['edit_firstname'],
                    'Lname' => $validatedData['edit_lastname'],
                    'email' => $validatedData['edit_email'],
                    "password" => $password,
                    'image' => $filePath,
                    'role' => $validatedData['edit_role_name'],
                    'status' => $validatedData['status'],
                    'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
                    'licence' => $request->edit_licence ?? null,
                    'licence_required' => $licence_required,
                    'licence_file' => $licenceFilePath ?? null,
                    'passport_required' => $passport_required,
                    'passport' => $request->edit_passport ?? null,
                    'passport_file' => $passportFilePath ?? null,
                    'rating' => $request->edit_rating ?? null,
                    'currency_required' => $currency_required,
                    'currency' => $request->edit_currency ?? null,
                    'custom_field_name' => $request->edit_custom_field_name ?? null,
                    'custom_field_value' => $request->edit_custom_field_value ?? null,
                    'password_flag' => $request->edit_update_password,
                    'extra_roles' => $extra_roles,
                    'is_admin' => $is_admin
                ]);

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
       
        $user = User::find(decode_id($request->id));

        if ($user) {
            $user->delete();
            return redirect()->route('user.index')->with('message', 'User deleted successfully');
        }
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