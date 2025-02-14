<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\OrganizationUnits;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function users()
    {
       $ou_id =  auth()->user()->ou_id;
       $urganizationUnits = OrganizationUnits::all();
       if(empty($ou_id)){
           $users = User::all();
        }else{
           $users = User::where('ou_id',$ou_id)->get();
       }
       $roles = Role::all();

        return view('users.index', compact('users', 'roles', 'urganizationUnits'));
    }

    public function save_user(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|max:255|unique:users,email',
            'image' => 'required',
            'password' => 'required|min:6|confirmed',
            'status' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);


        if ($request->has('licence_checkbox') && $request->licence_checkbox) {
            $request->validate([
                'licence' => 'required|string',
                'licence_file' => 'required|mimes:pdf,jpg,jpeg,png',
            ]);
        }

        if ($request->has('passport_checkbox') && $request->passport_checkbox) {
            $request->validate([
                'passport' => 'required|string',
                'passport_file' => 'required|mimes:pdf,jpg,jpeg,png',
            ]);
        }

        if ($request->has('rating_checkbox') && $request->rating_checkbox) {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
            ]);
        }

        if ($request->has('currency_checkbox') && $request->currency_checkbox) {
            $request->validate([
                'currency' => 'required|string',
            ]);
        }

        if ($request->has('custom_field_checkbox') && $request->custom_field_checkbox) {
            $request->validate([
                'custom_field_name' => 'required|string',
                'custom_field_value' => 'required|string',
            ]);
        }
    
        // Get the current logged-in user
        // $currentUser = auth()->user();
    
        // Check if the logged-in user has an 'ouid'
        // $ouid = $currentUser && $currentUser->ou_id ? $currentUser->ou_id : null;
    

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('users', 'public');
        }

        if ($request->hasFile('licence_file')) {
            $licence_file = $request->file('licence_file')->store('user_documents', 'public');
        }

        if ($request->hasFile('passport_file')) {
            $passport_file = $request->file('passport_file')->store('user_documents', 'public');
        }
        $store_user = array(
            "fname" => $request->firstname,
            "lname" => $request->lastname,
            "email" => $request->email,
            'image' => $filePath ?? null,
            "password" => Hash::make($request->password),
            "role" => $request->role_name,
            "licence" => $request->licence ?? null,
            "licence_file" => $licence_file ?? null,
            "passport" => $request->passport ?? null,
            "passport_file" => $passport_file ?? null,
            "rating" => $request->rating ?? null,
            "currency" => $request->currency ?? null,
            "custom_field_name" => $request->custom_field_name ?? null,
            "custom_field_value" => $request->custom_field_value ?? null,
            'status' => $request->status,
            "ou_id" => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
        );
    
        $store = User::create($store_user);
        if ($store) {
    
            // Generate password to send in the email
            $password = $request->password;
    
            // Send email

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
                'edit_role_name' => 'required',
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
                    'edit_email.email' => 'Please enter a valid Email'
                ]);
            
                if ($request->hasFile('image')) {
                    if ($userToUpdate->image) {
                        Storage::disk('public')->delete($userToUpdate->image);
                    }
            
                    $filePath = $request->file('image')->store('users', 'public');
                } else {
                    $filePath = $userToUpdate->image;
                }


                if ($request->has('edit_licence_checkbox') && $request->edit_licence_checkbox) {
                    $request->validate([
                        'edit_licence' => 'required|string',
                    ]);
                    if ($request->hasFile('edit_licence_file')) {
                        if ($userToUpdate->licence_file) {
                            Storage::disk('public')->delete($userToUpdate->licence_file);
                        }
                        $licenceFilePath = $request->file('edit_licence_file')->store('licence_files', 'public');
                    } else {
                        $licenceFilePath = $userToUpdate->licence_file;
                    }
                } else {
                    $licenceFilePath = $userToUpdate->licence_file;
                }

                // Handle Passport Upload
                if ($request->has('edit_passport_checkbox') && $request->edit_passport_checkbox) {
                    $request->validate([
                        'edit_passport' => 'required|string',
                    ]);
                    if ($request->hasFile('edit_passport_file')) {
                        if ($userToUpdate->passport_file) {
                            Storage::disk('public')->delete($userToUpdate->passport_file);
                        }
                        $passportFilePath = $request->file('edit_passport_file')->store('passport_files', 'public');
                    } else {
                        $passportFilePath = $userToUpdate->passport_file;
                    }
                } else {
                    $passportFilePath = $userToUpdate->passport_file;
                }

                if ($request->has('edit_rating_checkbox') && $request->edit_rating_checkbox) {
                    $request->validate([
                        'edit_rating' => 'required|integer|min:1|max:5',
                    ]);
                }

                if ($request->has('edit_currency_checkbox') && $request->edit_currency_checkbox) {
                    $request->validate([
                        'edit_currency' => 'required|string',
                    ]);
                }

                if ($request->has('edit_custom_field_checkbox') && $request->edit_custom_field_checkbox) {
                    $request->validate([
                        'edit_custom_field_name' => 'required|string',
                        'edit_custom_field_value' => 'required|string',
                    ]);
                }

                $userToUpdate->where('id', $request->edit_form_id)
                ->update([
                    'Fname' => $validatedData['edit_firstname'],
                    'Lname' => $validatedData['edit_lastname'],
                    'email' => $validatedData['edit_email'], 
                    'image' => $filePath,
                    'role' => $validatedData['edit_role_name'],
                    'status' => $validatedData['status'],
                    "ou_id" => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
                    'licence' => $request->edit_licence ?? null,
                    'licence_file' => $licenceFilePath  ?? null,
                    'passport' => $request->edit_passport  ?? null,
                    'passport_file' => $passportFilePath  ?? null,
                    'rating' => $request->edit_rating ?? null,
                    'currency' => $request->edit_currency ?? null,
                    'custom_field_name' => $request->edit_custom_field_name ?? null,
                    'custom_field_value' => $request->edit_custom_field_value ?? null,
            
                ]);
                return response()->json(['success' => true,'message' => "User data updated successfully"]);
        }
    }

    


    public function getUserById(Request $request) 
    {
        $user = User::find(decode_id($request->id));
        // dd($user);
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
}
