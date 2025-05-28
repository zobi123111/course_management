<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\OrganizationUnits; 
use App\Models\Resource;
use App\Models\User;
use App\Models\Courses;
use App\Models\CourseResources;
use App\Models\BookedResource;
use App\Models\ResourceDocument;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
Use DB;

class ResourceController extends Controller
{
    public function resource_list(Request $request)
    {
        $organizationUnits = OrganizationUnits::all();
        $ou_id = auth()->user()->ou_id;

        if ($request->ajax()) {
            if (auth()->user()->is_owner == 1) {
                $query = Resource::with('orgUnit');
            } else {
                $query = Resource::with('orgUnit')->where('ou_id', $ou_id);
            }

            // Store unfiltered count before search
            $totalRecords = $query->count();

            // Apply search filter
            $searchValue = $request->input('search.value');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('registration', 'like', '%' . $searchValue . '%')
                        ->orWhere('type', 'like', '%' . $searchValue . '%')
                        ->orWhere('class', 'like', '%' . $searchValue . '%')
                        ->orWhere('note', 'like', '%' . $searchValue . '%');
                });
            }

            // Count after filtering
            $filteredRecords = $query->count();

            // Ordering
            $orderColumn = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'asc');
            $columns = ['registration', 'type', 'class', 'note'];

            if ($orderColumn !== null && isset($columns[$orderColumn])) {
                $column = $columns[$orderColumn];
                $query->orderBy($column, $orderDirection);
            }

            // Pagination
            $resources = $query->offset($request->input('start'))
                ->limit($request->input('length'))
                ->get();

            // Format data
            $data = $resources->map(function ($unit) {
                $row = [
                    'name' => $unit->name,
                    'registration' => $unit->registration,
                    'type' => $unit->type,
                    'class' => $unit->class,
                    'note' => $unit->note,
                    'edit' => '<i class="fa fa-edit edit-resource-icon" data-resource-id="' . encode_id($unit->id) . '"></i>',
                    'delete' => '<i class="fa-solid fa-trash delete-icon" data-resource-id="' . encode_id($unit->id) . '"></i>',
                ];

                if (auth()->user()->is_owner == 1) {
                    $row['OU'] = $unit->orgUnit->org_unit_name;
                }

                return $row;
            });

            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        } else {
            return view('resource.index', compact('organizationUnits'));
        }
    }

    public function edit(Request $request)
    {
        $resourcedata = $resourcedata = $request->filled('resourceId') ? Resource::with('documents')->find(decode_id($request->resourceId)) : null;
        //dd($resourcedata->documents);
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
        //dd($request->all());
        $validated = $request->validate([
                'name' => 'required',
                'resource_documents.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10240 KB = 10 MB
            ], [
                'name.required' => 'The Name field is required.',
                'resource_documents.*.file.mimes' => 'Only JPG, JPEG, PNG, and PDF files are allowed.',
                'resource_documents.*.file.max' => 'Each file must not exceed 10MB in size.',
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
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : (auth()->user()->ou_id ?? null),
            'name'         => $request->name, 
            "registration"  =>  $request->registration,
            "type"  =>  $request->type,
            "classroom"  =>  $request->classroom,
            "class"  =>  $request->class,
            "other"  =>  $request->other,
            "note"  =>  $request->note,
            "hours_from_rts"  =>  $request->Hours_from_RTS,
            "date_from_rts"  =>  $request->Date_from_RTS,
            "date_for_maintenance"  =>  $request->Date_for_maintenance,
            "hours_remaining"  =>  $request->Hours_Remaining,
            "resource_logo"  =>  $logo_name[0] ?? null,
            'enable_doc_upload' => $request->has('enable_doc_upload') ? 1 : 0,
           );
           $save_resource = Resource::create($resource_data);

            // Save uploaded documents if enabled
            if ($request->has('enable_doc_upload') && $request->has('resource_documents')) {
                foreach ($request->resource_documents as $index => $doc) {
                    if (isset($doc['file']) && $request->file("resource_documents.$index.file")->isValid()) {
                        $uploadedFile = $request->file("resource_documents.$index.file");
                        $originalName = $uploadedFile->getClientOriginalName();
                        $extension = $uploadedFile->getClientOriginalExtension();
                        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

                        // Generate a unique file name
                        $timestamp = now()->format('YmdHis');
                        $uniqueFileName = $baseName . '_' . $timestamp . '.' . $extension;

                        // Store the file
                        $storedPath = $uploadedFile->storeAs('resource_documents', $uniqueFileName, 'public');

                        ResourceDocument::create([
                            'resource_id' => $save_resource->id,
                            'name' => $doc['name'] ?? 'Untitled',
                            'file_path' => $storedPath,
                        ]);
                    }
                }
            }

           if($save_resource)
           {
            Session::flash('message', 'Resource created successfully');
            return response()->json(['success' => 'success']);
           }
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
                'edit_name' => 'required',
                'resource_documents.*.file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10240 KB = 10 MB
            ], [
                'edit_name.required' => 'The Name field is required.',
                'resource_documents.*.file.mimes' => 'Only JPG, JPEG, PNG, and PDF files are allowed.',
                'resource_documents.*.file.max' => 'Each file must not exceed 10MB in size.',
            ]);

        if($validated)
        {    
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
                // 'ou_id'         => $request->edit_ou_id ?? null, 
                'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->edit_ou_id : (auth()->user()->ou_id ?? null),
                "name"  =>  $request->edit_name,
                "registration"  =>  $request->edit_registration,
                "type"  =>  $request->edit_type,
                "classroom"  =>  $request->edit_classroom,
                "class"  =>  $request->edit_class,
                "other"  =>  $request->edit_other,
                "note"  =>  $request->edit_note,
                "hours_from_rts"  =>  $request->edit_Hours_from_RTS,
                "date_from_rts"  =>  $request->edit_Date_from_RTS,
                "date_for_maintenance"  =>  $request->edit_Date_for_maintenance,
                "hours_remaining"  =>  $request->edit_Hours_Remaining,
                "resource_logo"  => $logo_name[0] ?? $request->existing_resourse_logo,
                'enable_doc_upload' => $request->has('enable_doc_upload') ? 1 : 0
            );

            $resource = Resource::find($request->resourse_id); 
            if ($resource) {
                $resource->update($resource_data);

                // Handle Resource Documents
                if ($request->filled('resource_documents')) {

                    $submittedIds = collect($request->resource_documents)->pluck('row_id')->filter()->toArray();
                    $existingIds = ResourceDocument::where('resource_id', $resource->id)->pluck('id')->toArray();
                    $toDelete = array_diff($existingIds, $submittedIds);

                    // Delete missing documents and their files
                    foreach ($toDelete as $docId) {
                        $doc = ResourceDocument::find($docId);
                        if ($doc && Storage::disk('public')->exists($doc->file_path)) {
                            Storage::disk('public')->delete($doc->file_path);
                        }
                        $doc?->delete();
                    }

                    foreach ($request->resource_documents as $doc) {
                        $filePath = null;

                        // Check if a new file is uploaded
                        if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
                            // If an existing file path is provided, delete the old file
                            if (!empty($doc['existing_file_path']) && Storage::disk('public')->exists($doc['existing_file_path'])) {
                                Storage::disk('public')->delete($doc['existing_file_path']);
                            }

                            // Rename file to avoid overwriting
                            $uploadedFile = $doc['file'];
                            $originalName = $uploadedFile->getClientOriginalName();
                            $extension = $uploadedFile->getClientOriginalExtension();
                            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                            $uniqueFileName = $baseName . '_' . now()->format('YmdHis') . '.' . $extension;
                            // Store the new file with its original name
                            $filePath = $uploadedFile->storeAs('resource_documents', $uniqueFileName, 'public');
                        } else {
                            // If no new file is uploaded, retain the existing file path
                            $filePath = $doc['existing_file_path'] ?? null;
                        }

                        // Create or update the document record
                       if (!empty($doc['row_id'])) {
                            ResourceDocument::where('id', $doc['row_id'])->update([
                                'resource_id' => $resource->id,
                                'name' => $doc['name'] ?? '',
                                'file_path' => $filePath,
                            ]);
                        } else {
                            ResourceDocument::create([
                                'resource_id' => $resource->id,
                                'name' => $doc['name'] ?? '',
                                'file_path' => $filePath,
                            ]);
                        }
                    }
                }

                Session::flash('message', 'Resource Updated successfully');
                return response()->json(['success' => 'success']);
            }
        }
    }

    
    public function delete(Request $request)
    {
        $resource = $request->filled('resource_id') ? Resource::findOrFail(decode_id($request->resource_id)) : null;

        if ($resource) {
            // Get all related documents
            $documents = ResourceDocument::where('resource_id', $resource->id)->get();

            foreach ($documents as $doc) {
                // Delete the file from storage if it exists
                if (!empty($doc->file_path) && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }

                // Delete the document record
                $doc->delete();
            }

            // Delete the resource itself
            $resource->delete();

            return redirect()->route('resource.index')->with('message', 'Resource and associated documents deleted successfully');
        }

        return redirect()->route('resource.index')->with('message', 'Resource not found');
    }

    public function getcourseResource(Request $request)
    {
        $course_id = $request->courseid;
        
    }


    public function bookresource(Request $request, $course_id)
    {
        $course_id = decode_id($course_id);
       //dd($course_id);
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
    $ou_id = Auth::user()->ou_id;
 
    foreach ($checkedResources as $row) {
        $exists = BookedResource::where('user_id', $userId)
            ->where('course_id', $row['courses_id'])
            ->where('resource_id', $row['id'])
            ->exists();

        if (!$exists) {
            BookedResource::create([
                "user_id" => $userId,
                "ou_id" => $ou_id,
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

public function resource_approval()
{
    $ou_id    = Auth::user()->ou_id;
    $is_admin = Auth::user()->is_admin;

    
    if($is_admin== 1){
        $pending_approval = BookedResource::with('resource:id,name', 'user:id,fname,lname')->where('ou_id', $ou_id)->get();
        return view('booking.approval', compact('pending_approval'));
    }else{
        $pending_approval = [];
        return view('booking.approval', compact('pending_approval'));
    }
    
   
}

public function approve_request(Request $request)
{
   $booking_id = $request->booking_id;

   $approve = array(
     "status" => 1
   );

   $approve_request = BookedResource::where('id', $booking_id)->update($approve);
   if($approve_request)
   {
    Session::flash('message','Request Approved Successfully');
    return response()->json(['success' => 'success' ]);
   }

}

public function reject_request(Request $request)
{
    $booking_id = $request->booking_id;

    $approve = array(
      "status" => 2
    );
 
    $approve_request = BookedResource::where('id', $booking_id)->update($approve);
    if($approve_request)
    {
     Session::flash('message','Request Rejected Successfully');
     return response()->json(['success' => 'success' ]);
    }
}

    

}