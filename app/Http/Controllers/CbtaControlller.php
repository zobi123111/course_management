<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\CbtaGrading;
use App\Models\OrganizationUnits;
use Session;
use Illuminate\Validation\Rule;

class CbtaControlller extends Controller
{
    public function index()
    {
     if(auth()->user()->is_owner == 1){
        $instructor =  CbtaGrading::with('organization_unit')->where('competency_type', 'instructor')->get();
         $examiner =  CbtaGrading::with('organization_unit')->where('competency_type', 'examiner')->get();

     }else{
      $instructor =  CbtaGrading::with('organization_unit')->where('competency_type', 'instructor')->where('ou_id', auth()->user()->ou_id)->get();
      $examiner =  CbtaGrading::with('organization_unit')->where('competency_type', 'examiner')->where('ou_id', auth()->user()->ou_id)->get();
     }
      $organizationUnits  = OrganizationUnits::all();
      return view('CBTA.show', compact('instructor', 'examiner','organizationUnits'));
    }



public function save(Request $request)
{
    $request->validate([
        'competency' => [
            'required',
            Rule::unique('cbta_gradings')->whereNull('deleted_at')
        ],
        'short_name' => [
            'required',
            Rule::unique('cbta_gradings')->whereNull('deleted_at')
        ],
        'competency_type' => 'required'
    ]);

    CbtaGrading::create([
        'competency'      => $request->competency,
        'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->organization_unit : auth()->user()->ou_id,
        'short_name'      => $request->short_name,
        'competency_type' => $request->competency_type 
    ]);

    Session::flash('message', 'CBTA competency added successfully');
    return response()->json(['success' => 'CBTA competency added successfully']);
}


    public function edit(Request $request)
    {
      $cbta = CbtaGrading::where('id', $request->CbtaID)->get();
      return response()->json(['success' => 'true', 'cbta' => $cbta]);
    }

    public function update(Request $request)
    {
          $this->validate(request(), [
            'edit_competency' => 'required',
             'edit_short_name' => 'required',
            'edit_competency_type' => 'required'
            ], [], 
            [
                'edit_competency' => 'Competency field',
                'edit_short_name' => 'Short Name field ',
            ]);
    
           CbtaGrading::where('id', $request->cbta_id)->update([
                    'competency'      => $request->edit_competency,
                    'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->organization_unit : auth()->user()->ou_id,
                    'short_name'      => $request->edit_short_name,
                    'competency_type' => $request->edit_competency_type
                ]);

           Session::flash('message', 'CBTA competency updated successfully');
          return response()->json(['success' => 'CBTA competency updated successfully']);
    }

    public function delete(Request $request)
    {
       CbtaGrading::where('id', $request->CbtaID)->delete();
       return response()->json(['success' => 'CBTA competency deleted successfully']);
    }


}
