<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationUnits;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;
use App\Mail\OrgUnitCreated;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizationUnitsData = OrganizationUnits::with(['roleOneUsers'])
        ->withCount('users') // Count all users linked to organization
        ->whereNull('deleted_at')
        ->get();
        // $organizationUnitsData = OrganizationUnits::with(['roleOneUsers'])
        // ->withCount(['users as non_role_one_users_count' => function ($query) {
        //     $query->where('role', '!=', 1);
        // }])
        // ->whereNull('deleted_at')
        // ->get();
        // dd($organizationUnitsData);
        return view('organization.index', compact('organizationUnitsData'));
    }



    public function saveOrgUnit(Request $request)
    {
        $rules = [
            'org_unit_name' => 'required|unique:organization_units',
            'description' => 'required',
            'status' => 'required',
        ];
    
        if ($request->filled('email') || $request->filled('firstname') || $request->filled('lastname')) {
            $rules = array_merge($rules, [
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|min:6|confirmed',
            ]);
        }
    
        $request->validate($rules, [
            'description.required' => 'Description field is required',
            'status.required' => 'Status field is required',
        ]);
    
        // DB::beginTransaction();
    
        try {
            // Step 1: Store the organizational unit data
            $orgUnit = OrganizationUnits::create([
                'org_unit_name' => $request->org_unit_name,
                'description' => $request->description,
                'status' => $request->status
            ]);
    
            // Step 2: Store the user data only if email is provided
            if($orgUnit){                
                $user = null;
                if ($request->filled('email') && $request->filled('firstname') && $request->filled('lastname')) {
                    $user = User::create([
                        'fname' => $request->firstname,
                        'lname' => $request->lastname,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => 1,
                        'ou_id' => $orgUnit->id,
                    ]);
                }
            
                if ($user) {  
                    // Generate password to send in the email (if user exists and password is provided)
                    $password = $request->password;
                    
                    // Send email
                    Mail::to($user->email)->send(new OrgUnitCreated($user, $password, $orgUnit));
                }
        
                Session::flash('message', 'Organizational unit created successfully' . ($user ? ' with user' : ''));
                return response()->json(['success' => 'Organizational unit created successfully' . ($user ? ' with user' : '')]);
            }else{
                Session::flash('error', 'Something went wrong while creating organizational unit, Please try after some time');
                return response()->json(['error' => 'Something went wrong while creating organizational unit, Please try after some time']);
            }
            // DB::commit(); // Commit transaction if everything is successful
    
            // Success response
        } catch (\Exception $e) {
            DB::rollBack();
        
            Log::error('Error creating organizational unit and user: ' . $e->getMessage());
        
            return response()->json(['error' => $e->getMessage()]);
        }
    }
    


    public function getOrgUnit(Request $request) 
    {
        // Check if orgId and userId are filled before decoding
        $organizationUnit = $request->filled('orgId') ? OrganizationUnits::find(decode_id($request->orgId)) : null;
        $user = $request->filled('userId') ? User::find(decode_id($request->userId)) : null;

        // Handle missing or not found errors
        if ($request->filled('orgId') && !$organizationUnit) {
            return response()->json(['error' => 'Organizational Unit not found'], 404);
        }
        if ($request->filled('userId') && !$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if (!$request->filled('orgId') && !$request->filled('userId')) {
            return response()->json(['error' => 'At least one of orgId or userId is required'], 400);
        }

        return response()->json([
            'organizationUnit' => $organizationUnit,
            'user' => $user
        ]);
    }


    public function updateOrgUnit(Request $request)
    {
        // dd($request);
        $rules = [
            'org_unit_name' => 'required|unique:organization_units,org_unit_name,' . $request->org_unit_id,
            'description' => 'required',
            'status' => 'required',
        ];

        if ($request->filled('user_id') && ($request->filled('edit_email') || $request->filled('edit_firstname') || $request->filled('edit_lastname'))) {
            $rules = array_merge($rules, [
                'edit_firstname' => 'required',
                'edit_lastname' => 'required',
                'edit_email' => 'required|email|unique:users,email,' . $request->user_id,
            ]);
        }
        if(!$request->filled('user_id') && ($request->filled('edit_email') || $request->filled('edit_firstname') || $request->filled('edit_lastname') || $request->filled('password'))){
            $rules = array_merge($rules, [
                'edit_firstname' => 'required',
                'edit_lastname' => 'required',
                'edit_email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
            ]);
        }

        $validatedData = $request->validate($rules, [
            'org_unit_name.required' => 'The Organizational Unit name field is required',
            'org_unit_name.unique' => 'The Organizational Unit name must be unique.',
            'description.required' => 'Description field is required',
            'status.required' => 'Status field is required',
            'edit_firstname.required' => 'The Firstname field is required',
            'edit_lastname.required' => 'The Lastname field is required',
            'edit_email.required' => 'The Email field is required',
        ]);

        DB::beginTransaction();

        try {
            // Step 1: Update Organizational Unit
            $orgUnit = OrganizationUnits::findOrFail($request->org_unit_id);
            $orgUnit->update([
                'org_unit_name' => $request->org_unit_name,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Step 2: Update existing user
            if ($request->filled('user_id') && $request->filled('edit_email')) {
                $user = User::findOrFail($request->user_id);
                $user->update([
                    'fname' => $request->edit_firstname,
                    'lname' => $request->edit_lastname,
                    'email' => $request->edit_email
                ]);
            } 
            // Step 3: Create a new user if no user_id exists
            elseif (!$request->filled('user_id') && $request->filled('edit_email') && $request->filled('password')) {
                $user = User::create([
                    'fname' => $request->edit_firstname,
                    'lname' => $request->edit_lastname,
                    'email' => $request->edit_email,
                    'password' => Hash::make($request->password),
                    'role' => 1,
                    'ou_id' => $request->org_unit_id,
                ]);

                // Send email only if user was created and password exists
                if ($user) {  
                    Mail::to($user->email)->send(new OrgUnitCreated($user, $request->password, $orgUnit));
                }
            }

            DB::commit(); // Commit transaction if everything is successful

            // Success
            Session::flash('message', 'Organizational Unit updated successfully' . ($request->filled('edit_email') ? ' with user' : ''));
            return response()->json(['success' => 'Organizational unit updated successfully' . ($request->filled('edit_email') ? ' with user' : '')]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log error
            Log::error('Error updating organizational unit and user: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()]);
        }
    }


    public function deleteOrgUnit(Request $request)
    {        
        $organizationUnit = $request->filled('org_id')? OrganizationUnits::findOrFail(decode_id($request->org_id)): null;
        if ($organizationUnit) {
            $organizationUnit->delete();
            if($request->filled('user_id')){
                $user = User::findOrFail(decode_id($request->user_id));
                $user->delete();
                return redirect()->route('orgunit.index')->with('message', 'Organizational Unit and OU User deleted successfully');
            }          
            return redirect()->route('orgunit.index')->with('message', 'Organizational Unit deleted successfully');  
        }
    }


    
}
