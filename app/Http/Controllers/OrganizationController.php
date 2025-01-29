<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationUnits;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizationUnits = OrganizationUnits::all();
        $roles = Role::all();
        return view('Organization.index', compact('organizationUnits','roles'));
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
                'password' => 'required|min:6|confirmed',
                'role_name'  => 'required'
            ],
            [
            'org_unit_name.required' => 'The Organizational Unit name is required',
            'description.required' => 'Description is required',
            'status.required' => 'Status is required',
            'role_name.required'  => 'The Role is required'   
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
                'role' => $request->role_name,
                'ou_id' => $orgUnit->id,
            ]);
    
            DB::commit(); // Commit transaction if everything is successful
    
            // Success
            Session::flash('message', 'Organizational unit and user created successfully');
            return response()->json(['success' => 'Organizational unit and user created successfully']);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback if there is any error
    
            // Failure
            return response()->json(['error' => 'There was an issue creating the organizational unit and user']);
        }

        $userData = 

        $store =  OrganizationUnits::create($request->all());
        if($store){
            Session::flash('message', 'Organizational unit created successfully');
            return response()->json(['success' => 'Organizational unit created successfully']); 
        }
    }

    public function getOrgUnit(Request $request) 
    {
        $organizationUnit = OrganizationUnits::find($request->id);
        if (!$organizationUnit) {
            return response()->json(['error' => 'Organizational Unit not found']);
        }
        return response()->json(['organizationUnit' => $organizationUnit]);
    }

    public function updateOrgUnit(Request $request)
    {
            $validatedData = $request->validate([
                'org_unit_name' => 'required',
                'description' => 'required',
                'status' => 'required'
            ],
            [
                'org_unit_name.required' => 'The Organizational Unit name is required',
                'description.required' => 'Description is required',
                'status.required' => 'Status is required'
            ]);

            $orgUnit = OrganizationUnits::findOrFail($request->org_unit_id);
            $updateOrgUnit = $orgUnit->update($validatedData);
            if($updateOrgUnit){
                Session::flash('message', 'Organizational Unit updated successfully');
                return response()->json(['status'=>true,'message'=>'Organizational Unit updated successfully']);
            }else{
                return response()->json(['status'=>false,'message'=>'Something went wrong']);
            }
            
    }

    public function deleteOrgUnit(Request $request)
    {        
        $organizationUnit = OrganizationUnits::findOrFail($request->id);
        if ($organizationUnit) {
            $organizationUnit->delete();
            return redirect()->route('org_units.index')->with('message', 'Organizational Unit deleted successfully');
        }
    }


    
}
