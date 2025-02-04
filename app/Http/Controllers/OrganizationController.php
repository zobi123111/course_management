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

class OrganizationController extends Controller
{
    public function index()
    {
        $organizationUnitsData = DB::table('organization_units')
            ->join('users', 'organization_units.id', '=', 'users.ou_id')
            ->select('organization_units.id','organization_units.org_unit_name','organization_units.description','organization_units.status', 'users.id as user_id','users.fname','users.lname','users.email','users.role','users.password','users.ou_id')
            ->get();
        return view('Organization.index', compact('organizationUnitsData'));
    }

    public function saveOrgUnit(Request $request)
    {
        // dd($request);
        $request->validate([
                'org_unit_name' => 'required',
                'description' => 'required',
                'status' => 'required',
                'firstname' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|min:6|confirmed'
            ],
            [
            'org_unit_name.required' => 'The Organizational Unit name field is required',
            'description.required' => 'Description field is required',
            'status.required' => 'Status field is required',
            'firstname.required' => 'The Firstname field is required',
            'lastname.required' => 'The Lastname field is required'
            ]
        );

        DB::beginTransaction();

        try {

            // Step 1: Store the organizational unit data
            $orgUnit = OrganizationUnits::create([
                'org_unit_name' => $request->org_unit_name,
                'description' => $request->description,
                'status' => $request->status
            ]);

            // Step 2: Store the user data and associate with 'ou_id'
            $user = User::create([
                'fname' => $request->firstname,
                'lname' => $request->lastname,
                'email' => $request->email,
                "password"=> Hash::make($request->password),
                'role' => 1,
                'ou_id' => $orgUnit->id,
            ]);

            $mailData = [
                'username' => $request->firstname.' '.$request->lastname,
                'email' => $request->email,
                "password" => $request->password,
                "site_url" => config('app.url')
            ];
    
            DB::commit(); // Commit transaction if everything is successful
    
            // Success
            Session::flash('message', 'Organizational unit and user created successfully');
            return response()->json(['success' => 'Organizational unit and user created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
        
            // Use Log correctly
            Log::error('Error creating organizational unit and user: ' . $e->getMessage());
        
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getOrgUnit(Request $request) 
    {
        $organizationUnit = OrganizationUnits::find($request->orgId);
        $user = User::find($request->userId);
        if (!$organizationUnit && !$user) {
            return response()->json(['error' => 'Organizational Unit not found']);
        }
        return response()->json(['organizationUnit' => $organizationUnit,'user'=>$user]);
    }

    public function updateOrgUnit(Request $request)
    {
            $validatedData = $request->validate([
                'org_unit_name' => 'required',
                'description' => 'required',
                'status' => 'required',
                'edit_firstname' => 'required',
                'edit_lastname' => 'required',
                'edit_email'  => 'required',
            ],
            [
                'org_unit_name.required' => 'The Organizational Unit name field is required',
                'description.required' => 'Description field is required',
                'status.required' => 'Status field is required',
                'edit_firstname.required' => 'The Firstname field is required',
                'edit_lastname.required' => 'The Lastname field is required',
                'edit_email.required' => 'The Email field is required',
            ]);

            DB::beginTransaction();

            try {
                // Step 1: Find the Organizational Unit and update
                $orgUnit = OrganizationUnits::findOrFail($request->org_unit_id);
                $orgUnit->update([
                    'org_unit_name' => $request->org_unit_name,
                    'description' => $request->description,
                    'status' => $request->status,
                ]);
        
                // Step 2: Find the user and update user details
                $user = User::findOrFail($request->user_id);
                $user->update([
                    'fname' => $request->edit_firstname,
                    'lname' => $request->edit_lastname,
                    'email' => $request->edit_email
                ]);
        
                DB::commit(); // Commit transaction if everything is successful
        
                // Success
                Session::flash('message', 'Organizational Unit and User updated successfully');
                return response()->json(['success' => 'Organizational unit and user updated successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
        
                // Log error
                Log::error('Error updating organizational unit and user: ' . $e->getMessage());
        
                return response()->json(['error' => $e->getMessage()]);
            }
            
    }

    public function deleteOrgUnit(Request $request)
    {        
        $organizationUnit = OrganizationUnits::findOrFail($request->id);
        if ($organizationUnit) {
            $organizationUnit->delete();
            return redirect()->route('orgunit.index')->with('message', 'Organizational Unit deleted successfully');
        }
    }


    
}
