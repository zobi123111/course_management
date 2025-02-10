<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;

class UserController extends Controller
{
    public function users()
    {
       $users = User::all();
       $roles = Role::all();
        return view('users.index', compact('users', 'roles'));
    }

    public function save_user(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed'
        ]);
    
        // Get the current logged-in user
        $currentUser = auth()->user();
    
        // Check if the logged-in user has an 'ouid'
        $ouid = $currentUser && $currentUser->ou_id ? $currentUser->ou_id : null;
    
        $store_user = array(
            "fname" => $request->firstname,
            "lname" => $request->lastname,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => $request->role_name,
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
       
        $userToUpdate = User::find($request->edit_form_id);
        if($userToUpdate){
        
       $validatedData =  $request->validate([
            'fname' => 'required',
            'lname' => 'required',
            'email'  => 'required',
            'role'  => 'required'
        ],
        [
            'fname.required' => 'The  Firstname is required',
            'lname.required' => 'The Lastname is required',
            'email.required' => 'The  Email is required'
        ]);

        $userToUpdate->where('id', $request->edit_form_id)
        ->update([
            'Fname' => $validatedData['fname'],
            'Lname' => $validatedData['lname'],
            'email' => $validatedData['email'], 
            'role' => $validatedData['role'],
       
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
            return redirect()->route('users.index')->with('message', 'User deleted successfully');
        }
    }
}
