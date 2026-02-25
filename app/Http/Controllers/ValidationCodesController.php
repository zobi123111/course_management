<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CbtaGrading;
use App\Models\LicenceValidationType;
use App\Models\OrganizationUnits;
use Session;
use Illuminate\Validation\Rule;

class ValidationCodesController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        $ou_id = decode_id($request->ou_id);

        if(auth()->user()->is_owner == 1){
            $validation_type =  LicenceValidationType::with('OrganizationUnit')->where('ou_id', $ou_id)->orderBy('id', 'desc')->get();
        }else{
            $validation_type =  LicenceValidationType::with('OrganizationUnit')->where('ou_id', auth()->user()->ou_id)->orderBy('id', 'desc')->get();
        }
     
        $organizationUnits  = OrganizationUnits::all();

        return view('licence_validation_type.show', compact('validation_type', 'organizationUnits'));
    }

    public function save(Request $request)
    {

        // dd($request->all());
        $this->validate(request(), [
            'code' => 'required|unique:licence_validation_types,code',
            'country_name' => 'required',
            'aircraft_prefix' => 'required',
        ], [], 
        [
            'code' => 'Code field',
            'country_name' => 'Country Name field',
            'aircraft_prefix' => 'Aircraft Prefix field',
        ]);

        LicenceValidationType::create([
            'code'      => $request->code,
            'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
            'country_name'      => $request->country_name,
            'aircraft_prefix' => $request->aircraft_prefix,
            'enabled' => $request->enabled
        ]);

        Session::flash('message', 'Licence validation type added successfully');
        return response()->json(['success' => 'Licence validation type added successfully']);
    }


    public function edit(Request $request)
    {
        $validation_type = LicenceValidationType::where('id', $request->id)->first();
        $organizationUnits  = OrganizationUnits::all();
        return response()->json(['success' => 'true', 'validation_type' => $validation_type, 'organizationUnits' => $organizationUnits]);
    }

    public function update(Request $request)
    {
        $this->validate(request(), [
            'code' => 'required|unique:licence_validation_types,code,'.$request->id,
            'country_name' => 'required',
            'aircraft_prefix' => 'required',
        ], [], 
        [
            'code' => 'Code field',
            'country_name' => 'Country Name field',
            'aircraft_prefix' => 'Aircraft Prefix field',
        ]);
    
           LicenceValidationType::where('id', $request->id)->update([
                    'code'      => $request->code,
                    'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
                    'country_name'      => $request->country_name,
                    'aircraft_prefix' => $request->aircraft_prefix,
                    'enabled' => $request->enabled
                ]);

           Session::flash('message', 'Licence validation type updated successfully');
          return response()->json(['success' => 'Licence validation type updated successfully']);
    }

    public function delete(Request $request)
    {
       LicenceValidationType::where('id', $request->id)->delete();
       return response()->json(['success' => 'Licence validation type deleted successfully']);
    }
}
