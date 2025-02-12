<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
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
       if(empty($ou_id)){
           $users = User::all();
        }else{
           $users = User::where('ou_id',$ou_id)->get();
       }
       $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function save_user(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|max:255|unique:users,email',
            'image' => 'required',
            'password' => 'required|min:6|confirmed',
            'status' => 'required',
        ]);
    
        // Get the current logged-in user
        $currentUser = auth()->user();
    
        // Check if the logged-in user has an 'ouid'
        $ouid = $currentUser && $currentUser->ou_id ? $currentUser->ou_id : null;
    

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('users', 'public');
        }

        $store_user = array(
            "fname" => $request->firstname,
            "lname" => $request->lastname,
            "email" => $request->email,
            'image' => $filePath ?? null,
            "password" => Hash::make($request->password),
            "role" => $request->role_name,
            'status' => $request->status,
            "ou_id" => $ouid // Assigning the same 'ouid' as the logged-in user
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
            'status' => 'required'
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

        $userToUpdate->where('id', $request->edit_form_id)
        ->update([
            'Fname' => $validatedData['edit_firstname'],
            'Lname' => $validatedData['edit_lastname'],
            'email' => $validatedData['edit_email'], 
            'image' => $filePath,
            'role' => $validatedData['edit_role_name'],
            'status' => $validatedData['status'],
       
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
