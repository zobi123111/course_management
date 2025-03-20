<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\OrganizationUnits; 
use App\Models\Resource;
use App\Models\User;
use App\Models\Courses;
use App\Models\CourseResources;
use App\Models\BookedResource;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
Use DB;

class ResourceController extends Controller
{
    public function resource_list(Request $request)
    {
        if ($request->ajax()) {
            $query = Resource::query();

            // Get total records count before applying filters
            $totalRecords = $query->count();

            // Ordering
            $orderColumn = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'asc');
            $columns = ['registration', 'type', 'class', 'note'];

            if ($orderColumn !== null && isset($columns[$orderColumn])) {
                $column = $columns[$orderColumn];
                $query->orderBy($column, $orderDirection);
            }

            // Pagination
            $filteredRecords = $query->count(); // Update after filtering
            $resources = $query->offset($request->input('start'))
                ->limit($request->input('length'))
                ->get();
               
            // Data transformation
            $data = $resources->map(function ($unit) {
               
                return [
                    'name' => $unit->name,
                    'registration' => $unit->registration,
                    'type' => $unit->type,
                    'class' => $unit->class,
                    'note' => $unit->note,
                  'edit' => '<i class="fa fa-edit edit-resource-icon" data-resource-id="' . encode_id($unit->id) .'"></i>',

                  'delete' => '<i class="fa-solid fa-trash delete-icon" data-resource-id="' . encode_id($unit->id). '"></i>',

                  
                ];
            });

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } else {
            return view('resource.index');
        }
    }

    public function edit(Request $request)
    {
        $resourcedata = $request->filled('resourceId') ? Resource::find(decode_id($request->resourceId)) : null;
      //  dd($resourcedata);
        $user = $request->filled('userId') ? User::find(decode_id($request->userId)) : null;
       
        
             // Handle missing or not found errors
             if ($request->filled('resourceId') && !$resourcedata) {
                return response()->json(['error' => 'Organizational Unit not found'], 404);
            }
            if ($request->filled('userId') && !$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            if (!$request->filled('resourceId') && !$request->filled('userId')) {
                return response()->json(['error' => 'At least one of orgId or userId is required'], 400);
            }

            return response()->json([
                'resourcedata' => $resourcedata,
                'user' => $user
            ]);
        
    }

    public function save(Request $request)
    {
    //    dd($request->all());
        $validated =   request()->validate([
            'name' => 'required',
            'registration' => 'required',
            'type' => 'required',
            'class' => 'required',
            'note' => 'required',
            'Hours_from_RTS' => 'required',
            'Date_from_RTS' => 'required',
            'Date_for_maintenance' => 'required',
            'Hours_Remaining' => 'required',
            'resource_logo' => 'required',
        ],
        [
            'name.required'            => 'The Name field is required.',
            'registration.required'    => 'The Registration field is required.',
            'type.required'            => 'The Type field is required.',
            'class.required'           => 'The Class field is required.',
            'note.required'            => 'The Note field is required.',
            'Hours_from_RTS.required'  => 'The Hours from RTS field is required.',
            'Date_from_RTS.required'   => 'The Date from RTS field is required.',
            'Date_for_maintenance.required' => 'The Date for maintenance field is required.',
            'Hours_Remaining.required'  => 'The Hours Remaining field is required.',
            'resource_logo.required'  => 'The Resource_logo field is required.'
        ]);

        if($validated)
        {
            $logo_name = [];
            if ($request->hasFile('resource_logo')) {
                $file = $request->file('resource_logo');
                $fileName = $file->getClientOriginalName();
                $filePath = $file->storeAs('resource_logo', $fileName, 'public');
                $logo_name[] = $fileName;
            }
           $resource_data = array(
            'name'         => $request->name, 
            "registration"  =>  $request->registration,
            "type"  =>  $request->type,
            "class"  =>  $request->class,
            "note"  =>  $request->note,
            "hours_from_rts"  =>  $request->Hours_from_RTS,
            "date_from_rts"  =>  $request->Date_from_RTS,
            "date_for_maintenance"  =>  $request->Date_for_maintenance,
            "hours_remaining"  =>  $request->Hours_Remaining,
            "resource_logo"  =>  $logo_name[0] ?? null

           );
           $save_resource = Resource::create($resource_data);
           if($save_resource)
           {
            Session::flash('message', 'Resource created successfully');
            return response()->json(['success' => 'success']);
           }
        }
    }

    public function update(Request $request)
    {
        $validated =   request()->validate([
            'edit_name' => 'required',
            'edit_registration' => 'required',
            'edit_type' => 'required',
            'edit_class' => 'required',
            'edit_note' => 'required',
            'edit_Hours_from_RTS' => 'required',
            'edit_Date_from_RTS' => 'required',
            'edit_Date_for_maintenance' => 'required',
            'edit_Hours_Remaining' => 'required',
            //'resource_logo' => 'required',
        ],
        [
            'edit_name.required'            => 'The Name field is required.',
            'edit_registration.required'    => 'The Registration field is required.',
            'edit_type.required'            => 'The Type field is required.',
            'edit_class.required'           => 'The Class field is required.',
            'edit_note.required'            => 'The Note field is required.',
            'edit_Hours_from_RTS.required'  => 'The Hours from RTS field is required.',
            'edit_Date_from_RTS.required'   => 'The Date from RTS field is required.',
            'edit_Date_for_maintenance.required' => 'The Date for maintenance field is required.',
            'edit_Hours_Remaining.required'  => 'The Hours Remaining field is required.',
           // 'resource_logo.required'  => 'The Resource_logo field is required.'
        ]);

        $logo_name = [];
            if ($request->hasFile('edit_organization_logo')) {
            $file = $request->file('edit_organization_logo');
            $fileName = $file->getClientOriginalName(); // Get the original file name
            $filePath = $file->storeAs('resource_logo', $fileName, 'public');
            
            // If you want to store only the filename instead of the path:
            $storedFileName = basename($filePath);
            $logo_name[] = ($fileName);
    }

    $resource_data = array(
        "name"  =>  $request->edit_name,
        "registration"  =>  $request->edit_registration,
        "type"  =>  $request->edit_type,
        "class"  =>  $request->edit_class,
        "note"  =>  $request->edit_note,
        "hours_from_rts"  =>  $request->edit_Hours_from_RTS,
        "date_from_rts"  =>  $request->edit_Date_from_RTS,
        "date_for_maintenance"  =>  $request->edit_Date_for_maintenance,
        "hours_remaining"  =>  $request->edit_Hours_Remaining,
        "resource_logo"  => $logo_name[0] ?? $request->existing_resourse_logo

       );

       $resource = Resource::find($request->resourse_id); 
        if ($resource) {
            $resource->update($resource_data);
            Session::flash('message', 'Resource Updated successfully');
            return response()->json(['success' => 'success']);
        }
    }

    
    public function delete(Request $request)
    {        
        $resource = $request->filled('resource_id')? Resource::findOrFail(decode_id($request->resource_id)): null;
       
        if ($resource) {
            $resource->delete();
            return redirect()->route('resource.index')->with('message', 'Resource  deleted successfully');
        }
    }

    public function getcourseResource(Request $request)
    {
        $course_id = $request->courseid;
        
    }


    public function bookresource(Request $request, $course_id)
    {
        $course_id = decode_id($course_id);

        $courseResources = CourseResources::where('courses_id', $course_id)
                            ->with('resource')
                            ->get();
        $userId = Auth::user()->id;
   
        $selected_course     = BookedResource::where('course_id', $course_id)->with('resource')->where('user_id', $userId)->get();  

        $pending_resources   = BookedResource::where('course_id', $course_id)->with('resource')->where('user_id', $userId)->where('status', 0)->get(); 

     
        $approved_resources = DB::table('resources')
                            ->join('booked_resources', 'resources.id', '=', 'booked_resources.resource_id')
                            ->where('booked_resources.user_id', $userId)
                            ->where('booked_resources.course_id', $course_id)
                             ->where('booked_resources.status', 1)
                            ->select('resources.*', 'booked_resources.*') // Select all columns from both tables
                            ->distinct()
                            ->get()
                            ->toArray();
      
        $rejected_resources = DB::table('resources')
                            ->join('booked_resources', 'resources.id', '=', 'booked_resources.resource_id')
                            ->where('booked_resources.user_id', $userId)
                            ->where('booked_resources.course_id', $course_id)
                            ->where('booked_resources.status', 2)
                            ->select('resources.*', 'booked_resources.*') // Select all columns from both tables
                            ->distinct()
                            ->get()
                            ->toArray();
                           // dd($rejected_resources);
    
      

        return view('booking.index', compact('courseResources','pending_resources', 'selected_course', 'approved_resources', 'rejected_resources'));
    }

 public function store(Request $request)
{
    $checkedResources = collect($request->input('resources', []))->filter(function ($resource) {
        return isset($resource['id']); // Only validate checked resources
    });

    $rules = [];
    foreach ($checkedResources as $key => $resource) {
        $rules["resources.$key.start_date"] = 'required|date';
        $rules["resources.$key.end_date"] = 'required|date|after_or_equal:resources.'.$key.'.start_date';
    }

    $messages = [
        'resources.*.start_date.required' => 'Start date is required.',
        'resources.*.end_date.required' => 'End date is required.',
        'resources.*.end_date.after_or_equal' => 'End date must be after or equal to start date.',
    ];

    $request->validate($rules, $messages);

    // Save bookings if not duplicate
    $userId = Auth::user()->id;
    foreach ($checkedResources as $row) {
        $exists = BookedResource::where('user_id', $userId)
            ->where('course_id', $row['courses_id'])
            ->where('resource_id', $row['id'])
            ->exists();

        if (!$exists) {
            BookedResource::create([
                "user_id" => $userId,
                "course_id" => $row['courses_id'],
                "resource_id" => $row['id'],
                "start_date" => $row['start_date'],
                "end_date" => $row['end_date'],
                "status" => 0,
            ]);
        }
    }
    Session::flash('message','Resource Booked successfully.');
    return response()->json(['suceess' => 'suceess' ]);
}

    

}