@section('title', 'Courses')
@section('sub-title', 'Courses')
@extends('layout.app')
@section('content')

<style>
    .course-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }

    .course_card {
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .course_card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 16px rgba(0, 0, 0, 0.2);
    }

    .card-body {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        background-color: #f8f9fa;
    }

    .card-text {
        flex-grow: 1;
    }

    .course-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    /* Button hover effect */
    .card-footer .btn {
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .card-footer .btn:hover {
        background-color: #e2e6ea;
        transform: translateY(-2px);
    }

    .status-label {
        position: absolute;
        top: 10px;
        right: 10px;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.9em;
    }

    .select2-container--open {
        z-index: 9999;
        /* or higher than your modal */
    }

    .modal {
        z-index: 1050;
    }

    .course-card {
        cursor: grab;
        transition: background 0.2s;
    }

    .course-card:active {
        cursor: grabbing;
    }

    /* Prevent transparent dragging effect */
    .ui-sortable-helper {
        opacity: 1 !important;
        background: #fff !important;
        /* box-shadow: 0 2px 6px rgba(0,0,0,0.2); */
    }

    #create_rhs_tag_col .row button i.fa:before {
       color: white !important;
   }
    #edit_rhs_tag_col .row button i.fa:before {
       color: white !important;
   }
</style>

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

<div id="reoderMessage" class="alert alert-success d-none fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
</div>

@if(checkAllowedModule('courses','course.store')->isNotEmpty())
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createCourse" data-toggle="modal"
        data-target="#createCourseModal">Create Course</button>
</div>
@endif
<br>

<div class="card pt-4">
    <div class="card-body">
        <div class="container-fluid">
            @php
            $disableDragDrop = '';
            if (Auth()->user()->is_owner == 1 || auth()->user()->is_admin == 1) {
            $disableDragDrop = 'sortable-courses';
            }
            @endphp

            <div class="row" id="{{ $disableDragDrop }}">
                @forelse($courses as $val)
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                    <div class="course_card course-card" data-id="{{ $val->id }}">
                        <div class="course-image-container" style="position: relative;">
                            @if($val->image)
                            <img src="{{ asset('storage/' . $val->image) }}" class="card-img-top"
                                alt="Course Image">
                            @else
                            <img src="{{ asset('/assets/img/profile-img.jpg') }}" class="card-img-top course-image"
                                alt="Course Image">
                            @endif

                            <span class="status-label"
                                style="position: absolute; top: 10px; right: 10px; background-color: {{ $val->status == 1 ? 'green' : 'red' }}; color: white; padding: 5px 10px; border-radius: 5px;">
                                {{ ($val->status == 1) ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title courseName">{{ $val->course_name}}</h5>

                            <p class="card-text">
                                {{ \Illuminate\Support\Str::words($val->description, 50, '...') }}
                            </p>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            @if(checkAllowedModule('courses', 'course.show')->isNotEmpty())
                            <a href="{{ route('course.show', ['course_id' => encode_id($val->id)]) }}"
                                class="btn btn-light">
                                <i class="fa fa-eye"></i> View Course
                            </a>
                            @endif

                            @if(checkAllowedModule('courses', 'course.edit')->isNotEmpty())
                            <a href="javascript:void(0)" class="btn btn-light edit-course-icon"
                                data-course-id="{{ encode_id($val->id) }}">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            @endif

                            @if(checkAllowedModule('courses', 'course.delete')->isNotEmpty())
                            <a href="javascript:void(0)" class="btn btn-light delete-icon"
                                data-course-id="{{ encode_id($val->id) }}">
                                <i class="fa-solid fa-trash"></i> Delete
                            </a>
                            @endif
                            @if(checkAllowedModule('courses', 'copy_course.index')->isNotEmpty())
                            <a href="javascript:void(0)" class="btn btn-light course-copy-icon"
                                data-course-id="{{ encode_id($val->id) }}">
                                <i class="fa fa-copy"></i> Copy
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <h4 class="text-center">No courses available</h4>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Create Courses-->
<div class="modal fade" id="createCourseModal" tabindex="-1" role="dialog" aria-labelledby="courseModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">Create Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="courses" method="POST" enctype="multipart/form-data" class="row g-3 needs-validation">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example" id="select_org_unit">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Select ATO Number<span class="text-danger">*</span></label>
                        <select class="form-select" name="ato_number" aria-label="Default select example" id="select_ato_number">

                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>
                    </div>
                    @endif
                    @if(auth()->user()->role == 1 && auth()->user()->is_admin==1)
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select ATO Number<span class="text-danger">*</span></label>
                        <select class="form-select" name="ato_number" aria-label="Default select example" id="select_ato_number">

                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>
                    </div>

                    @endif
                    <div class="col-md-6">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <div id="course_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="course_type" class="form-label">Course Type<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="course_type" id="course_type" required>
                            <option value="">Select Course Type</option>
                            <option value="one_event">One Event</option>
                            <option value="multi_lesson">Multiple Lesson</option>
                        </select>
                        <div id="course_type_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-12">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                        <div id="description_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="image" class="form-label">Image<span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="image_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="duration" class="form-label">Course Duration<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="duration_type" id="duration_type">
                            <option value="">Select Duration Type</option>
                            <option value="hours">Hours</option>
                            <option value="events">Events</option>
                        </select>
                        <input type="number" name="duration_value" class="form-control mt-2"
                            placeholder="Enter number of hours/events">
                        <div id="duration_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="enable_groundschool_time" name="enable_groundschool_time">
                            <label class="form-check-label" for="enable_feedback">
                                Enable Groundschool Tracking
                            </label>
                        </div>
                    </div>

                    <!-- Groundschool Time Input -->
                    <div id="groundschool_time_container" class="mt-2" style="display: none;">
                        <!-- <label for="groundschool_hours" class="form-label">Groundschool Hours</label> -->
                        <input type="number" class="form-control" name="groundschool_hours" id="groundschool_hours" placeholder="Enter groundschool hours">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="enable_simulator_time" name="enable_simulator_time">
                            <label class="form-check-label" for="enable_feedback">
                                Enable Simulator Tracking
                            </label>
                        </div>
                    </div>

                    <!-- Simulator Time Input -->
                    <div id="simulator_time_container" class="mt-2" style="display: none;">
                        <!-- <label for="simulator_hours" class="form-label">Simulator Hours</label> -->
                        <input type="number" class="form-control" name="simulator_hours" id="simulator_hours" placeholder="Enter simulator hours">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_custom_time_tracking" id="enable_custom_time_tracking" value="1">
                            <label class="form-check-label" for="enable_custom_time_tracking">
                                Enable Custom Time Tracking
                            </label>
                        </div>
                    </div>
                    <div id="customTimeConfigSection" style="display:none; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <div id="custom_time_tracking_container">
                            <div class="custom-time mb-3">
                                <label for="custom_time_name">Custom Time Type Name</label>
                                <input type="text" name="custom_time[0][name]" id="custom_time_name" class="form-control" placeholder="e.g. Night Flying">
                                <div id="custom_time_0_name_error" class="text-danger error_e"></div>
                                <label for="custom_time_hours">Required Hours</label>
                                <input type="number" name="custom_time[0][hours]" id="custom_time_hours" class="form-control" placeholder="e.g. 4">
                                <div id="custom_time_0_hours_error" class="text-danger error_e"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add_another_time_btn">Add More</button>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_more_mp">
                            <label class="form-check-label" for="enable_more_mp">
                                MP Event
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="course_type" class="form-label">Enable MP Event</label>
                        <select class="form-select" name="enable_mp_lifus" id="enable_mp_lifus">
                            <option value="1">SP Event</option>
                            <option value="2">MP Event</option>
                            <option value="3">SP+MP Event</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_opc" name="enable_opc">
                            <label class="form-check-label" for="enable_opc">
                                OPC Event
                            </label>
                        </div>
                    </div>

                    <div class="form-group opc-aircraft" style="display:none;">
                        <label class="form-label">Aircraft Type</label>
                        <select class="form-select" name="enable_aircraft" id="enable_aircraft">
                            <option value="">Select Aircraft</option>
                            @foreach ($ratings as $rating)
                            @if ($rating->status == 1)
                            <option value="{{ $rating->id }}">
                                {{ $rating->name }}
                            </option>
                            @endif
                            @endforeach

                        </select>
                    </div>
                    <div class="col-md-6 " id="opc_validity_col" style="display:none">
                        <label class="form-label">OPC Validity<span class="text-danger">*</span></label>
                        <select class="form-select" name="opc_validity_months" id="opc_validity_months">
                            <option value="">Select Validity</option>
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}">{{ $i }} Month{{ $i>1?'s':'' }}</option>
                                @endfor
                        </select>
                        <div id="opc_validity_months_error" class="text-danger error_e"></div>
                    </div>

                    <div class="col-md-6 " id="opc_extend_eom_col" style="display:none">
                        <label class="form-label">Validity Extension<span class="text-danger">*</span></label>
                        <select class="form-select" name="opc_extend_eom" id="opc_extend_eom">
                            <option value="">Select Option</option>
                            <option value="1">Extend validity to end of month</option>
                            <option value="0">Do not extend (exact date)</option>
                        </select>
                        <div id="opc_extend_eom_error" class="text-danger error_e"></div>
                    </div>

                    <!-- //------------------------------------------------------------------------------------------>
                    <div class="form-group">
                        <!-- RHS Applicable Checkbox -->
                        <div class="col-md-12 mb-3" >
                            <div class="form-check" id="select_tag_div" style="display:none">
                                <input class="form-check-input" type="checkbox" id="create_is_rhs" name="create_is_rhs" value="1">
                                <label class="form-check-label" for="create_is_rhs">
                                    Qualification
                                </label>
                            </div>
                        </div>
                        <!-- RHS Section -->
                        <div class="row d-none" id="create_rhs_tag_col">
                            <div class="col-md-12">
                                <!-- Action Buttons -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary me-2" id="create_master_tag">
                                        Select Tag
                                    </button>

                                    <button type="button" class="btn btn-secondary" id="create_add_manual_tag">
                                        Add New Tag
                                    </button>
                                </div>
                                <!-- RHS Rows Container -->
                                <div id="create_rhs_rows_container"></div>
                            </div>
                        </div>

                        <!-- MASTER TAG TEMPLATE (SELECT) -->
                        <div class="row create_rhs_row d-none" id="create_rhs_select_row_template">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Select Tag <span class="text-danger">*</span></label>
                                <select class="form-select" name="master_tag_select[]">
                                    <option value="">Select Tag</option>
                                    @foreach ($tags as $val)
                                    <option value="{{ $val->id }}">{{ $val->rhstag }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label">Validity (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control"
                                    name="master_validity[]" min="1" placeholder="Months">
                            </div>

                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-danger create_remove_rhs_row">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>

                        </div>

                        <!-- MANUAL TAG TEMPLATE (INPUT) -->
                        <div class="row rhs_row d-none" id="create_rhs_input_row_template">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Tag <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    name="manual_tag_select[]" placeholder="Enter tag name">
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label">Validity (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control"
                                    name="manual_validity[]" min="1" placeholder="Months">
                            </div>

                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove_rhs_row">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                    <!-- //------------------------------------------------------------------------------------------>


                    <div class="col-md-6">
                        <label for="groups" class="form-label">Assigned Resource<span
                                class="text-danger"></span></label>
                        <select class="form-select resources-select" name="resources[]" multiple="multiple">
                            @foreach($resource as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                        <div id="resources_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="groups" class="form-label">Select Groups<span class="text-danger">*</span></label>
                        <select class="form-select groups-select" name="group_ids[]" multiple="multiple">
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_ids_error" class="text-danger error_e"></div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="enable_feedback" name="enable_feedback">
                            <label class="form-check-label" for="enable_feedback">
                                Enable Training Feedback
                            </label>
                        </div>
                    </div>

                    <div id="feedbackConfigSection" style="display:none; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <label class="form-label">Feedback Questions <span class="text-muted">(Optional)</span></label>
                        <div id="feedback_questions_container">
                            <!-- Question Template -->
                            <div class="feedback-question mb-3">
                                <input type="text" name="feedback_questions[0][question]" class="form-control mb-2" placeholder="Enter question">
                                <div id="feedback_questions_0_question_error" class="text-danger error_e"></div>
                                <select name="feedback_questions[0][answer_type]" class="form-select">
                                    <option value="">Select Answer Type</option>
                                    <option value="yes_no">Yes / No</option>
                                    <option value="rating">Rating (1-5)</option>
                                </select>
                                <div id="feedback_questions_0_answer_type_error" class="text-danger error_e"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add_question_btn">Add Another Question</button>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="enable_instructor_upload" name="enable_instructor_upload">
                            <label class="form-check-label" for="enable_instructor_upload"> Enable Instructor Upload</label>
                        </div>
                    </div>

                    <div id="instructor_documents_container" style="display: none;">
                        <div id="instructor_documents_items">
                            <div class="instructor-documents-item border p-2 mt-2">
                                <div class="form-group">
                                    <label class="form-label">Document Name</label>
                                    <input type="text" name="instructor_documents[0][name]" class="form-control">
                                    <div id="instructor_documents_0_name_error" class="text-danger error_e"></div>
                                </div>
                                <button type="button" class="btn btn-danger remove-documents-container">X</button>
                            </div>
                        </div>
                        <button type="button" id="addDocumentsContainer" class="btn btn-primary mt-2">Add More</button>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="enable_cbta" name="enable_cbta">
                            <label class="form-check-label" for="enable_cbta"> Enable Pilot Competencies </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="instructor" name="instructor_cbta">
                            <label class="form-check-label" for="instructor"> Enable Instructor Competencies </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="examiner" name="examiner_cbta">
                            <label class="form-check-label" for="examiner"> Enable Examiner Competencies </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitCourse" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Courses-->

<!-- Edit Courses -->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourse" class="row g-3 needs-validation">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example"
                            id="edit_select_org_unit">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select ATO Number<span class="text-danger">*</span></label>
                        <select class="form-select" name="ato_number" aria-label="Default select example" id="edit_select_ato_number">

                        </select>
                        <div id="ato_num_error_up" class="text-danger error_e"></div>
                    </div>
                    @endif
                    @if(auth()->user()->role == 1 && auth()->user()->is_admin == 1)
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select ATO Number<span class="text-danger">*</span></label>
                        <select class="form-select" name="ato_number" aria-label="Default select example" id="edit_select_ato_number">

                        </select>
                        <div id="ato_num_error_up" class="text-danger error_e"></div>
                    </div>

                    @endif

                    <div class="col-md-6">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <input type="hidden" name="course_id" class="form-control">
                        <div id="course_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="course_type" class="form-label">Course Type<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="course_type" id="edit_course_type" required>
                            <option value="">Select Course Type</option>
                            <option value="one_event">One Event</option>
                            <option value="multi_lesson">Multiple Lesson</option>
                        </select>
                        <div id="course_type_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-12">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        <div id="description_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="lastname" class="form-label">Image<span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="image_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="duration" class="form-label">Course Duration<span class="text-danger">*</span></label>
                        <select class="form-select" name="duration_type" id="edit_duration_type">
                            <option value="">Select Duration Type</option>
                            <option value="hours">Hours</option>
                            <option value="events">Events</option>
                        </select>
                        <input type="number" name="duration_value" class="form-control mt-2" id="edit_duration_value"
                            placeholder="Enter number of hours/events">
                        <div id="duration_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_enable_groundschool_time" name="enable_groundschool_time">
                            <label class="form-check-label" for="enable_feedback">
                                Enable Groundschool Tracking
                            </label>
                        </div>
                    </div>

                    <!-- Groundschool Time Input -->
                    <div id="edit_groundschool_time_container" class="mt-2" style="display: none;">
                        <!-- <label for="groundschool_hours" class="form-label">Groundschool Hours</label> -->
                        <input type="number" class="form-control" name="groundschool_hours" id="edit_groundschool_hours" placeholder="Enter groundschool hours">
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_enable_simulator_time" name="enable_simulator_time">
                            <label class="form-check-label" for="enable_feedback">
                                Enable Simulator Tracking
                            </label>
                        </div>
                    </div>

                    <!-- Simulator Time Input -->
                    <div id="edit_simulator_time_container" class="mt-2" style="display: none;">
                        <!-- <label for="simulator_hours" class="form-label">Simulator Hours</label> -->
                        <input type="number" class="form-control" name="simulator_hours" id="edit_simulator_hours" placeholder="Enter simulator hours">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_custom_time_tracking" id="edit_enable_custom_time_tracking" value="1">
                            <label class="form-check-label" for="edit_enable_custom_time_tracking">
                                Enable Custom Time Tracking
                            </label>
                        </div>
                    </div>

                    <div id="editCustomTimeConfigSection" style="display:none; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <div id="edit_custom_time_tracking_container">
                            <div class="custom-time mb-3">
                                <label for="custom_time_name">Custom Time Type Name</label>
                                <input type="text" name="custom_time[0][name]" id="custom_time_name" class="form-control" placeholder="e.g. Night Flying">
                                <div id="custom_time_0_name_error_up" class="text-danger error_e"></div>
                                <label for="custom_time_hours">Required Hours</label>
                                <input type="number" name="custom_time[0][hours]" id="custom_time_hours" class="form-control" placeholder="e.g. 4">
                                <div id="custom_time_0_hours_error_up" class="text-danger error_e"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_another_time_btn">Add More</button>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_enable_more_mp">
                            <label class="form-check-label" for="edit_enable_more_mp">
                                MP Event
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="course_type" class="form-label">Enable MP Event</label>
                        <select class="form-select" name="enable_mp_lifus" id="edit_enable_mp_lifus">
                            <option value="1">SP Event</option>
                            <option value="2">MP Event</option>
                            <option value="3">SP+MP Event</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_enable_opc" name="enable_opc">
                            <label class="form-check-label" for="edit_enable_opc">
                                OPC Event
                            </label>
                        </div>
                    </div>


                    <div class="form-group opc-aircraft-edit" style="display:none;">
                        <label class="form-label">Aircraft Type</label>
                        <select class="form-select" name="enable_aircraft" id="edit_enable_aircraft">
                            @foreach ($ratings as $rating)
                            <option value="{{ $rating->id }}">{{ $rating->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 " id="edit_opc_validity_col" style="display:none">
                        <label class="form-label">OPC Validity<span class="text-danger">*</span></label>
                        <select class="form-select" name="opc_validity_months" id="edit_opc_validity_months">
                            <option value="">Select Validity</option>
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}">{{ $i }} Month{{ $i>1?'s':'' }}</option>
                                @endfor
                        </select>
                        <div id="opc_validity_months_error_up" class="text-danger error_e"></div>
                    </div>

                    <div class="col-md-6 " id="edit_opc_extend_eom_col" style="display:none">
                        <label class="form-label">Validity Extension<span class="text-danger">*</span></label>
                        <select class="form-select" name="opc_extend_eom" id="edit_opc_extend_eom">
                            <option value="">Select Option</option>
                            <option value="1">Extend validity to end of month</option>
                            <option value="0">Do not extend (exact date)</option>
                        </select>
                        <div id="opc_extend_eom_error_up" class="text-danger error_e"></div>
                    </div>

                    <!-- //------------------------------------------------------------------------------------------>
                    <div class="form-group">
                        <!-- RHS Applicable Checkbox -->
                        <div class="col-md-12 mb-3">
                            <div class="form-check" id="edit_tag_div">
                                <input class="form-check-input" type="checkbox" id="is_rhs" name="is_rhs" value="1">
                                <label class="form-check-label" for="is_rhs">
                                    Qualification
                                </label>
                            </div>
                        </div>
                        <!-- RHS Section -->
                        <div class="row d-none" id="edit_rhs_tag_col">
                            <div class="col-md-12">
                                <!-- Action Buttons -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary me-2" id="add_master_tag">
                                        Select Tag
                                    </button>

                                    <button type="button" class="btn btn-secondary" id="add_manual_tag">
                                        New Tag
                                    </button>
                                </div>
                                <!-- RHS Rows Container -->
                                <div id="rhs_rows_container"></div>
                            </div>
                        </div>

                        <!-- MASTER TAG TEMPLATE (SELECT) -->
                        <div class="row rhs_row d-none" id="rhs_select_row_template">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Select Tag <span class="text-danger">*</span></label>
                                <select class="form-select" name="master_tag_select[]">
                                    <option value="">Select Tag</option>
                                    @foreach ($tags as $val)
                                    <option value="{{ $val->id }}">{{ $val->rhstag }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label">Validity (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control"
                                    name="master_validity[]" min="1" placeholder="Months">
                            </div>

                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove_rhs_row">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>

                        </div>

                        <!-- MANUAL TAG TEMPLATE (INPUT) -->
                        <div class="row rhs_row d-none" id="rhs_input_row_template">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Tag <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                    name="manual_tag_select[]" placeholder="Enter tag name">
                            </div>

                            <div class="col-md-5 mb-3">
                                <label class="form-label">Validity (Months) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control"
                                    name="manual_validity[]" min="1" placeholder="Months">
                            </div>

                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove_rhs_row">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                    <!-- //------------------------------------------------------------------------------------------>

                    <div class="col-md-6">
                        <label for="groups" class="form-label">Assigned Resource<span
                                class="text-danger"></span></label>
                        <select class="form-select resources-select" name="resources[]" multiple="multiple"
                            id="resources-select">
                            @foreach($resource as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                        <div id="resources_error_up" class="text-danger error_e"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="groups" class="form-label">Select Groups<span class="text-danger"></span></label>
                        <select class="form-select groups-select" name="group_ids[]" multiple="multiple">
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_ids_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_enable_feedback" name="enable_feedback">
                            <label class="form-check-label" for="edit_enable_feedback">
                                Enable Training Feedback
                            </label>
                        </div>
                    </div>

                    <div id="edit_feedbackConfigSection" style="display:none; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-top: 15px;">
                        <label class="form-label">Feedback Questions <span class="text-muted">(Optional)</span></label>

                        <div id="edit_feedback_questions_container">
                            <!-- Question Template -->
                            <div class="feedback-question mb-3">
                                <input type="text" name="feedback_questions[0][question]" class="form-control mb-2" placeholder="Enter question">
                                <div id="feedback_questions_0_question_error_up" class="text-danger error_e"></div>
                                <select name="feedback_questions[0][answer_type]" class="form-select">
                                    <option value="">Select Answer Type</option>
                                    <option value="yes_no">Yes / No</option>
                                    <option value="rating">Rating (1-5)</option>
                                </select>
                                <div id="feedback_questions_0_answer_type_error_up" class="text-danger error_e"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_question_btn">Add Another Question</button>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enable_prerequisites">
                            <label class="form-check-label" for="enable_prerequisites">
                                Enable Prerequisites
                            </label>
                        </div>
                    </div>
                    <div id="prerequisites_container" style="display: none;">
                        <div id="prerequisite_items">
                            <div class="prerequisite-item">
                                <div class="form-group">
                                    <label class="form-label">Prerequisite Detail</label>
                                    <input type="text" class="form-control" name="prerequisite_details[]">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Prerequisite Type</label>
                                    <div>
                                        <input type="radio" name="prerequisite_type[0]" value="number"> Number
                                        <input type="radio" name="prerequisite_type[0]" value="text"> Text
                                        <input type="radio" name="prerequisite_type[0]" value="file"> File
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger remove-prerequisite">X</button>
                            </div>
                        </div>
                        <button type="button" id="addPrerequisite" class="btn btn-primary mt-2">Add More</button>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="edit_enable_instructor_upload" name="enable_instructor_upload">
                            <label class="form-check-label" for="edit_enable_instructor_upload">
                                Enable Instructor Upload
                            </label>
                        </div>
                    </div>

                    <div id="edit_instructor_documents_container" style="display: none;">
                        <div id="edit_instructor_documents_items">
                            <div class="instructor-documents-item border p-2 mt-2">
                                <div class="form-group">
                                    <label class="form-label">Document Name</label>
                                    <input type="text" name="instructor_documents[0][name]" class="form-control">
                                    <div id="instructor_documents_0_name_error_up" class="text-danger error_e"></div>
                                </div>
                                <button type="button" class="btn btn-danger remove-documents-container">X</button>
                            </div>
                        </div>
                        <button type="button" id="editAddDocumentsContainer" class="btn btn-primary mt-2">Add More</button>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="edit_enable_cbta" name="edit_enable_cbta">
                        <label class="form-check-label" for="edit_enable_cbta"> Enable Pilot Competencies </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="edit_instructor" name="edit_instructor_cbta">
                        <label class="form-check-label" for="instructor"> Enable Instructor Competencies </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="edit_examiner" name="edit_examiner_cbta">
                        <label class="form-check-label" for="examiner"> Enable Examiner Competencies </label>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateCourse" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End Edit Courses-->

<!--Courses Delete  Modal -->
<form action="{{ url('course/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteCourse" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="course_id" id="courseId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Course "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary delete_course">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Courses Delete Model -->


<!-- // -------------------------------------------------------- -->
<!-- Select ou Model -->
<div class="modal fade" id="select_ou" tabindex="-1" aria-labelledby="select_ouModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="select_ouModalLabel">Select Organization Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Org Unit <span class="text-danger">*</span></label>
                    <select class="form-select" id="select_ou_copy">
                        <option value="">-- Select Org Unit --</option>
                        @foreach($organizationUnits as $val)
                        <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                        @endforeach
                    </select>
                    <small id="select_ou_copy_error" class="text-danger"></small>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm_copy_course" class="btn btn-primary">
                    Continue
                </button>
            </div>

        </div>
    </div>
</div>


<!-- End Select ou Model -->

<!-- //----------------------------------------------------------------------------------------------- -->
@endsection

@section('js_scripts')
<script>
    $(function() {
        $('#sortable-courses').sortable({
            helper: 'clone',
            cursor: 'grabbing',
            update: function(event, ui) {
                let order = [];
                $('.course-card').each(function(index) {
                    order.push({
                        id: $(this).data('id'),
                        position: index + 1
                    });
                });

                $.ajax({
                    url: '{{ route("courses.reorder") }}',
                    method: 'POST',
                    data: {
                        order: order,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        let $msg = $('#reoderMessage');
                        if ($msg.length) {
                            $msg.removeClass('d-none')
                                .fadeIn()
                                .text('Course order updated successfully!');
                        }

                        setTimeout(function() {
                            $msg.fadeOut();
                        }, 2000);
                    },
                    error: function() {
                        console.error('Error updating order');
                    }
                });
            }
        });

        $('.course-card').css('cursor', 'grab');
    });

    function initializeSelect2() {
        $('.groups-select').select2({
            allowClear: true,
            placeholder: 'Select the Group',
            multiple: true,
            dropdownParent: $('body') // move outside the modal
        });

        $(".resources-select").select2({
            placeholder: 'Select the Resource',
            allowClear: true,
            dropdownParent: $('body') // move outside the modal
        });
    }

    function fetchRatingsByOu(ouId, targetSelect) {
        if (!ouId) return;

        $.ajax({
            url: "/course/get-ratings-by-ou",
            type: "GET",
            data: {
                ou_id: ouId
            },
            dataType: "json",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            success: function(ratings) {

                if (!Array.isArray(ratings)) {
                    console.error("Expected array, got:", ratings);
                    return;
                }

                let html = '<option value="">Select Aircraft</option>';

                ratings.forEach(r => {
                    html += `<option value="${r.id}">${r.name}</option>`;
                });

                $(targetSelect).html(html);
            },
            error: function(xhr) {
                console.error("AJAX error:", xhr.responseText);
            }
        });
    }

    function fetchEditRatingsByOu(ouId, selectedAircraft = null) {
        $.ajax({
            url: "{{ route('course.get-ratings-by-ou') }}",
            type: "GET",
            dataType: "json",
            data: {
                ou_id: ouId
            },
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            success: function(ratings) {
                let options = "<option value=''>Select Aircraft</option>";

                if (Array.isArray(ratings) && ratings.length > 0) {
                    ratings.forEach(r => {
                        options += `<option value="${r.id}">${r.name}</option>`;
                    });
                } else {
                    options += "<option disabled>No aircraft found</option>";
                }

                $("#edit_enable_aircraft").html(options);

                // ✅ Select aircraft AFTER options exist
                if (selectedAircraft) {
                    $("#edit_enable_aircraft")
                        .val(String(selectedAircraft))
                        .trigger("change");
                }
            }
        });
    }

    $(document).ready(function() {
        $('#courseTable').DataTable();
        initializeSelect2();

        $("#createCourse").on('click', function() {
            $(".error_e").html('');
            $("#courses")[0].reset();
            $(".groups-select").val(null).trigger("change");
            // Hide feedback and instructor upload sections if visible
            $("#enable_feedback").prop("checked", false);
            $("#feedbackConfigSection").hide();
            $("#customTimeConfigSection").hide();
            $("#enable_instructor_upload").prop("checked", false);
            $("#instructor_documents_container").hide();
            $("#createCourseModal").modal('show');
            $('#createCourseModal').on('shown.bs.modal', function() {
                initializeSelect2();
            });
            //--------------------------------------------------------------
            var ou_id = "{{ Auth::user()->ou_id }}";
            var $ato_numSelect = $("#select_ato_number");

            $.ajax({
                url: "/group/get_ou_group/",
                type: "GET",
                data: {
                    'ou_id': ou_id
                },
                dataType: "json", // Ensures response is treated as JSON
                success: function(response) {
                    if (response.ato_num && response.ato_num.length > 0) {
                        var ato_number = "<option value=''>Select ATO Number</option>";
                        ato_number += "<option value='generic'>Generic</option>";
                        var hasData = false;

                        $.each(response.ato_num, function(index, value) {
                            if (value.uk_ato_number) {
                                ato_number += "<option value='uk-" + value.uk_ato_number + "'>UK ATO Number - " + value.uk_ato_number + "</option>";
                                hasData = true;
                            }
                            if (value.easa_ato_number) {
                                ato_number += "<option value='easa-" + value.easa_ato_number + "'>EASA ATO Number - " + value.easa_ato_number + "</option>";
                                hasData = true;
                            }
                        });

                        if (!hasData) {
                            ato_number += "<option disabled>No ATO number found</option>";
                        }

                        $ato_numSelect.html(ato_number);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });

            //-----------------------------------------------------------------

        });

        $("#submitCourse").on("click", function(e) {
            e.preventDefault();
            $(".loader").fadeIn();
            $('.error_e').html('');
            var formData = new FormData($('#courses')[0]);

            $.ajax({
                url: '{{ url("/course/create") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $(".loader").fadeOut("slow");
                    $('#createCourseModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        var formattedKey = key.replace(/\./g, '_') + '_error';
                        var errorMsg = '<p>' + value[0] + '</p>';
                        $('#' + formattedKey).html(errorMsg);
                    });
                }
            });
        });

        $('.edit-course-icon').click(function(e) {
            e.preventDefault();
            $('.error_e').html('');
            var courseId = $(this).data('course-id');
            var $ato_numSelect = $("#edit_select_ato_number");

            $.ajax({
                url: "{{ url('/course/edit') }}",
                type: 'GET',
                data: {
                    id: courseId
                },
                success: function(response) {
                    // Populate course data
                    $('input[name="course_name"]').val(response.course.course_name);
                    $('input[name="course_id"]').val(response.course.id);
                    $('#edit_course_type').val(response.course.course_type);
                    $('#edit_description').val(response.course.description);
                    // $('#edit_ou_id').val(response.course.ou_id);
                    $('#edit_duration_type').val(response.course.duration_type);
                    $('#edit_duration_value').val(response.course.duration_value);
                    $('#edit_status').val(response.course.status);
                    $('#edit_select_org_unit').val(response.course.ou_id).trigger('change');

                    // alert(response.course.enable_cbta);
                    if (response.course.enable_cbta == 1) {
                        $('#edit_enable_cbta').prop('checked', true);
                        

                    } else {
                        $('#edit_enable_cbta').prop('checked', false);

                    }
                    if (response.course.instructor_cbta == 1) {
                        $('#edit_instructor').prop('checked', true);

                    } else {
                        $('#edit_instructor').prop('checked', false);

                    }
                    if (response.course.examiner_cbta == 1) {
                        $('#edit_examiner').prop('checked', true);

                    } else {
                        $('#edit_examiner').prop('checked', false);

                    }

                    if (response.course.enable_mp_lifus == 1) {
                        // 🔹 Only SP Event
                        $('#edit_enable_more_mp').prop('checked', false);
                        $('#edit_enable_mp_lifus').val(1);
                        $('#edit_enable_mp_lifus option[value="2"], #edit_enable_mp_lifus option[value="3"]').hide();
                    } else if (response.course.enable_mp_lifus == 2 || response.course.enable_mp_lifus == 3) {
                        // 🔹 MP Event or SP+MP Event
                        $('#edit_enable_more_mp').prop('checked', true);
                        $('#edit_enable_mp_lifus').val(response.course.enable_mp_lifus);
                        $('#edit_enable_mp_lifus option[value="1"]').hide();
                        $('#edit_enable_mp_lifus option[value="2"], #edit_enable_mp_lifus option[value="3"]').show();
                    }

                    if (response.course.opc == 1) {
                        $('#edit_enable_opc').prop('checked', true);
                        toggleOPC('#edit_enable_opc', '.opc-aircraft-edit');

                        fetchEditRatingsByOu(
                            response.course.ou_id,
                            response.course.opc_aircraft
                        ); 
                        $('#edit_opc_validity_col').show();
                        $('#edit_opc_extend_eom_col').show();
                        $('#edit_opc_validity_months').val(response.course.opc_validity);
                         $('#edit_opc_extend_eom').val(response.course.opc_extend);


                    } else {
                        $('#edit_enable_opc').prop('checked', false);
                        toggleOPC('#edit_enable_opc', '.opc-aircraft-edit');
                        $('#edit_opc_validity_col').hide();
                        $('#edit_opc_extend_eom_col').hide();
                    }
                    $('#rhs_rows_container').empty();
                    $('#is_rhs').prop('checked', false);
                    $('#edit_rhs_tag_col').addClass('d-none');

                    if (response.course.user_tag_ratings && response.course.user_tag_ratings.length > 0) {

                        $('#is_rhs').prop('checked', true);
                        $('#edit_rhs_tag_col').removeClass('d-none');

                        response.course.user_tag_ratings.forEach(function(tag) {

                            let $row;

                            if (tag.tag_type === 'master') {

                                // Clone MASTER template
                                $row = $('#rhs_select_row_template').clone()
                                    .removeClass('d-none')
                                    .removeAttr('id');

                                $row.find('select[name="master_tag_select[]"]')
                                    .val(tag.tag_id);

                                $row.find('input[name="master_validity[]"]')
                                    .val(tag.tag_validity);

                            } else if (tag.tag_type === 'manual') {

                                // Clone MANUAL template
                                $row = $('#rhs_input_row_template').clone()
                                    .removeClass('d-none')
                                    .removeAttr('id');

                                $row.find('input[name="manual_tag_select[]"]')
                                    .val(tag.rhs_tag.rhstag);

                                $row.find('input[name="manual_validity[]"]')
                                    .val(tag.tag_validity);
                            }

                            $('#rhs_rows_container').append($row);
                        });
                    }


                    function toggleOPC(checkbox, container) {
                        if ($(checkbox).is(":checked")) {
                            $(container).stop(true, true).slideDown(200);
                        } else {
                            $(container).stop(true, true).slideUp(200);
                            $(container).find("select").val('').trigger('change');
                            $('#edit_opc_validity_col').hide();
                            $('#edit_opc_extend_eom_col').hide();
                            // $('#select_tag_div').hide();
                            $('#edit_rhs_tag_col').addClass('d-none');
                            $('#edit_tag_div').hide();

                        }
                    }

                    $(document).ready(function() {
                        $("#enable_opc").on("change", function() { 
                            toggleOPC(this, ".opc-aircraft");
                        });

                        $("#edit_enable_opc").on("change", function() { 
                            toggleOPC(this, ".opc-aircraft-edit");
                        });

                    });


                    // Populate Resources
                    if (response.resources) {
                        var $resourcesSelect = $('.resources-select');
                        $resourcesSelect.empty();
                        response.resources.forEach(function(resource) {
                            var isSelected = response.courseResources.some(cr => cr
                                .resources_id === resource.id);
                            $resourcesSelect.append(
                                `<option value="${resource.id}" ${isSelected ? 'selected' : ''}>${resource.name}</option>`
                            );
                        });
                        $resourcesSelect.val(response.courseResources.map(cr => cr.resources_id)).trigger('change');
                    }

                    if (response.ato_num && response.ato_num.length > 0) {
                        var ato_number = "<option value=''>Select ATO Number</option>";
                        ato_number += "<option value='generic'>Generic</option>";
                        var hasData = false;

                        $.each(response.ato_num, function(index, value) {
                            if (value.uk_ato_number) {
                                ato_number += "<option value='uk-" + value.uk_ato_number + "'>UK ATO Number - " + value.uk_ato_number + "</option>";
                                hasData = true;
                            }
                            if (value.easa_ato_number) {
                                ato_number += "<option value='easa-" + value.easa_ato_number + "'>EASA ATO Number - " + value.easa_ato_number + "</option>";
                                hasData = true;
                            }
                        });

                        if (!hasData) {
                            ato_number += "<option disabled>No ATO number found</option>";
                        }

                        $ato_numSelect.html(ato_number);
                    }
                    $('#edit_select_ato_number').val(response.course.ato_num);

                    // Fetch and Populate Groups Based on OU
                    $.ajax({
                        url: "{{ url('/group/get_ou_group') }}",
                        type: 'GET',
                        data: {
                            ou_id: response.course.ou_id
                        },
                        dataType: 'json',
                        success: function(groupResponse) {
                            var $groupSelect = $('.groups-select');
                            $groupSelect.empty();

                            if (groupResponse.org_group && Array.isArray(
                                    groupResponse.org_group)) {
                                $groupSelect.append(
                                    `<option value=''>Select Group</option>`);
                                groupResponse.org_group.forEach(function(group) {
                                    $groupSelect.append(
                                        `<option value="${group.id}">${group.name}</option>`
                                    );
                                });

                                // Set selected groups
                                var selectedGroups = response.course.groups.map(g =>
                                    g.id);
                                $groupSelect.val(selectedGroups).trigger('change');
                            }
                        },
                        error: function(xhr) {
                            console.error("Error loading groups:", xhr
                                .responseText);
                        }
                    });

                    // Groundschool Time
                    if (response.course.enable_groundschool_time) {
                        $('#edit_enable_groundschool_time').prop('checked', true);
                        $('#edit_groundschool_time_container').show();
                        $('#edit_groundschool_hours').val(response.course.groundschool_hours);
                    } else {
                        $('#edit_enable_groundschool_time').prop('checked', false);
                        $('#edit_groundschool_time_container').hide();
                        $('#edit_groundschool_hours').val('');
                    }

                    // Simulator Time
                    if (response.course.enable_simulator_time) {
                        $('#edit_enable_simulator_time').prop('checked', true);
                        $('#edit_simulator_time_container').show();
                        $('#edit_simulator_hours').val(response.course.simulator_hours);
                    } else {
                        $('#edit_enable_simulator_time').prop('checked', false);
                        $('#edit_simulator_time_container').hide();
                        $('#edit_simulator_hours').val('');
                    }


                    // Handle Prerequisites
                    if (response.course.enable_prerequisites) {
                        $('#enable_prerequisites').prop('checked', true);
                        $('#prerequisites_container').show();
                    } else {
                        $('#enable_prerequisites').prop('checked', false);
                        $('#prerequisites_container').hide();
                    }

                    $('#prerequisite_items').empty();
                    let prerequisites = response.course.prerequisites;
                    if (prerequisites.length > 0) {
                        prerequisites.forEach((prerequisite, index) => {
                            let prerequisiteHtml = generatePrerequisiteHtml(
                                prerequisite, index);
                            $('#prerequisite_items').append(prerequisiteHtml);
                        });
                    } else {
                        let prerequisiteHtml = generatePrerequisiteHtml({
                            prerequisite_detail: '',
                            prerequisite_type: 'text'
                        }, 0);
                        $('#prerequisite_items').append(prerequisiteHtml);
                    }

                    //Handle Document Container
                    if (response.course.enable_instructor_upload) {
                        $('#edit_enable_instructor_upload').prop('checked', true);
                        $('#edit_instructor_documents_container').show();
                    } else {
                        $('#edit_enable_instructor_upload').prop('checked', false);
                        $('#edit_instructor_documents_container').hide();
                    }

                    $('#edit_instructor_documents_items').empty(); // Clear existing containers
                    let instructor_documents = response.course.documents;

                    if (instructor_documents.length > 0) {
                        instructor_documents.forEach((instructor_documents, index) => {
                            let instructorDocumentHtml = generateDocumentsContainerHtml(
                                instructor_documents, index
                            );

                            $('#edit_instructor_documents_items').append(instructorDocumentHtml);
                        });
                    } else {
                        let instructorDocumentHtml = generateDocumentsContainerHtml({
                            document_name: '',
                            file_path: ''
                        }, 0);
                        $('#edit_instructor_documents_items').append(instructorDocumentHtml);
                    }

                    //Training Feedback
                    const questions = response.course.training_feedback_questions || [];

                    if (questions.length > 0) {
                        $('#edit_enable_feedback').prop('checked', true);
                        $('#edit_feedbackConfigSection').show();
                        $('#edit_feedback_questions_container').empty();

                        questions.forEach((q, i) => {
                            $('#edit_feedback_questions_container').append(`
                            <div class="feedback-question mb-3 position-relative border rounded p-3">
                                <input type="text" name="feedback_questions[${i}][question]" class="form-control mb-2" placeholder="Enter question" value="${q.question}">
                                <select name="feedback_questions[${i}][answer_type]" class="form-select">
                                    <option value="">Select Answer Type</option>
                                    <option value="yes_no" ${q.answer_type === 'yes_no' ? 'selected' : ''}>Yes / No</option>
                                    <option value="rating" ${q.answer_type === 'rating' ? 'selected' : ''}>Rating (1-5)</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-question-btn" title="Remove Question" aria-label="Remove Question">&times;</button>
                            </div>
                        `);
                        });
                    } else {
                        $('#edit_enable_feedback').prop('checked', false);
                        $('#edit_feedbackConfigSection').hide();
                        // $('#edit_feedback_questions_container').empty();
                    }

                    //Course custom times
                    const custom_times = response.course.custom_times || [];
                    if (custom_times.length > 0) {
                        $('#edit_enable_custom_time_tracking').prop('checked', true);
                        $('#editCustomTimeConfigSection').show();
                        $('#edit_custom_time_tracking_container').empty();

                        custom_times.forEach((t, i) => {
                            $('#edit_custom_time_tracking_container').append(`
                            <div class="custom-time mb-3 position-relative border rounded p-3">
                                <label for="custom_time_name">Custom Time Type Name</label>
                                <input type="text" name="custom_time[${i}][name]" id="custom_time_name" value="${t.name}" class="form-control" placeholder="e.g. Night Flying">
                                <div id="custom_time_${i}_name_error_up" class="text-danger error_e"></div>
                                <label for="custom_time_hours">Required Hours</label>
                                <input type="number" name="custom_time[${i}][hours]" id="custom_time_hours" value="${t.hours}"  class="form-control" placeholder="e.g. 4">
                                <div id="custom_time_${i}_hours_error_up" class="text-danger error_e"></div>
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-custom-time-btn" style="transform: translate(18px, -18px);" title="Remove Custom times" aria-label="Remove Custom times">&times;</button>
                            </div>
                        `);
                        });
                    } else {
                        $('#edit_enable_custom_time_tracking').prop('checked', false);
                        $('#editCustomTimeConfigSection').hide();
                        // $('#edit_feedback_questions_container').empty();
                    }
                    $('#editCourseModal').modal('show');

                    $('#editCourseModal').on('shown.bs.modal', function() {
                        $('#editCourseModal .resources-select').select2({
                            dropdownParent: $('#editCourseModal'),
                            width: '100%',
                            allowClear: true,
                            placeholder: 'Select the Resource',
                            multiple: true,
                            dropdownParent: $('body') // move outside the modal
                        });

                        $('#editCourseModal .groups-select').select2({
                            dropdownParent: $('#editCourseModal'),
                            width: '100%',
                            allowClear: true,
                            placeholder: 'Select the Group',
                            multiple: true,
                            dropdownParent: $('body') // move outside the modal
                        });
                    });

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });

        // Enable/Disable feedback on edit modal
        $(document).on('change', '#edit_enable_feedback', function() {
            $('#edit_feedbackConfigSection').toggle(this.checked);
        });

        //Adding extra questions box on edit modal
        $('#edit_add_question_btn').click(function() {
            let index = $('#edit_feedback_questions_container .feedback-question').length;

            let questionHtml = `
            <div class="feedback-question mb-3 position-relative border rounded p-3">
                <input type="text" name="feedback_questions[${index}][question]" class="form-control mb-2" placeholder="Enter question">
                <select name="feedback_questions[${index}][answer_type]" class="form-select">
                    <option value="">Select Answer Type</option>
                    <option value="yes_no">Yes / No</option>
                    <option value="rating">Rating (1-5)</option>
                </select>
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-question-btn" title="Remove Question" aria-label="Remove Question">&times;</button>
            </div>
        `;
            $('#edit_feedback_questions_container').append(questionHtml);
        });

        // Remove a question block
        $('#edit_feedback_questions_container').on('click', '.remove-question-btn', function() {
            $(this).closest('.feedback-question').remove();
        });

        // Enable/Disable custoim time on edit modal
        $(document).on('change', '#edit_enable_custom_time_tracking', function() {
            $('#editCustomTimeConfigSection').toggle(this.checked);
        });

        //Adding extra custome time box on edit modal
        $('#edit_add_another_time_btn').click(function() {
            let index = $('#edit_custom_time_tracking_container .custom-time').length;

            let customTimeHtml = `
            <div class="custom-time mb-3 position-relative border rounded p-3">
                <label for="custom_time_name">Custom Time Type Name</label>
                <input type="text" name="custom_time[${index}][name]" id="custom_time_name"  class="form-control" placeholder="e.g. Night Flying">
                <div id="custom_time_${index}_name_error_up" class="text-danger error_e"></div>
                <label for="custom_time_hours">Required Hours</label>
                <input type="number" name="custom_time[${index}][hours]" id="custom_time_hours"   class="form-control" placeholder="e.g. 4">
                <div id="custom_time_${index}_hours_error_up" class="text-danger error_e"></div>
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-custom-time-btn" style="transform: translate(18px, -18px);" title="Remove Custom times" aria-label="Remove Custom times">&times;</button>
            </div>`;
            $('#edit_custom_time_tracking_container').append(customTimeHtml);
        });

        // Remove a question block
        $('#edit_custom_time_tracking_container').on('click', '.remove-custom-time-btn', function() {
            $(this).closest('.custom-time').remove();
        });

        //Enabling Ground Time
        $('#enable_groundschool_time').change(function() {
            $('#groundschool_time_container').toggle(this.checked);
        });
        //Enabling Simulator Time
        $('#enable_simulator_time').change(function() {
            $('#simulator_time_container').toggle(this.checked);
        });

        //Enabling Edit Ground Time
        $('#edit_enable_groundschool_time').change(function() {
            $('#edit_groundschool_time_container').toggle(this.checked);
        });
        //Enabling Edit Simulator Time
        $('#edit_enable_simulator_time').change(function() {
            $('#edit_simulator_time_container').toggle(this.checked);
        });


        // Update Course functionality
        $('#updateCourse').on('click', function(e) {
            e.preventDefault();
            $(".loader").fadeIn('fast');
            var formData = new FormData($('#editCourse')[0]);
            formData.append('enable_prerequisites', $('#enable_prerequisites').is(':checked') ? 1 : 0);
            $.ajax({
                url: "{{ url('/course/update') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $(".loader").fadeOut("slow");
                    $('#editCourseModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        var formattedKey = key.replace(/\./g, '_') + '_error_up';
                        var errorMsg = '<p>' + value[0] + '</p>';
                        $('#' + formattedKey).html(errorMsg);
                    });
                }
            });
        });

        // Delete Course functionality
        $('.delete-icon').click(function(e) {
            e.preventDefault();
            $('#deleteCourse').modal('show');
            var courseId = $(this).data('course-id');
            // var courseName = $(this).closest('h5').find('.courseName').text();
            var courseName = $(this).closest('.course_card').find('.courseName').text();

            $('#append_name').html(courseName);
            $('#courseId').val(courseId);
        });

        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
        }, 2000);
        // Toggle prerequisites section
        $("#enable_prerequisites").change(function() {
            if ($(this).is(":checked")) {
                $("#prerequisites_container").show();
            } else {
                $("#prerequisites_container").hide();
                // $("#prerequisite_items").empty();
            }
        });


        // Add new prerequisite
        $("#addPrerequisite").click(function() {
            let index = $(".prerequisite-item").length;
            let prerequisiteHTML = `
            <div class="prerequisite-item border p-2 mt-2">
                <div class="form-group">
                    <label class="form-label">Prerequisite Detail</label>
                    <input type="text" class="form-control" name="prerequisite_details[]">
                </div>

                <div class="form-group">
                    <label class="form-label">Prerequisite Type</label>
                    <div>
                      <label for="prerequisite_type_number_${index}">
                            <input type="radio" id="prerequisite_type_number_${index}" name="prerequisite_type[${index}]" value="number"> Number
                        </label>

                        <label for="prerequisite_type_text_${index}">
                            <input type="radio" id="prerequisite_type_text_${index}" name="prerequisite_type[${index}]" value="text"> Text
                        </label>

                        <label for="prerequisite_type_file_${index}">
                            <input type="radio" id="prerequisite_type_file_${index}" name="prerequisite_type[${index}]" value="file"> File
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-danger remove-prerequisite">X</button>
            </div>
        `;
            $("#prerequisite_items").append(prerequisiteHTML);
        });

        // Remove prerequisite
        $(document).on("click", ".remove-prerequisite", function() {
            $(this).closest(".prerequisite-item").remove();
        });

        // Toggle Instructor Documents section
        $("#enable_instructor_upload").change(function() {
            if ($(this).is(":checked")) {
                $("#instructor_documents_container").show();
            } else {
                $("#instructor_documents_container").hide();
                // $("#prerequisite_items").empty();
            }
        });
        // Toggle Instructor Documents section On Editing
        $("#edit_enable_instructor_upload").change(function() {
            if ($(this).is(":checked")) {
                $("#edit_instructor_documents_container").show();
            } else {
                $("#edit_instructor_documents_container").hide();
                // $("#prerequisite_items").empty();
            }
        });

        // Add New Documents Container
        $("#addDocumentsContainer").click(function() {
            let index = $(".instructor-documents-item").length;

            let documentContainerHTML = `
                            <div class="instructor-documents-item border p-2 mt-2">
                                <div class="form-group">
                                    <label class="form-label">Document Name</label>
                                    <input type="text" name="instructor_documents[${index}][name]" id="documents_name_${index}" class="form-control">
                                </div>
                                <button type="button" class="btn btn-danger remove-documents-container">X</button>
                            </div>
        `;
            $("#instructor_documents_items").append(documentContainerHTML);
        });

        // Add New Documents Container while editing
        $("#editAddDocumentsContainer").click(function() {
            // Find the highest existing index first
            let maxIndex = 0;
            $(".instructor-documents-item").each(function() {
                $(this).find('input[name^="instructor_documents"]').each(function() {
                    let match = $(this).attr('name').match(/\[(\d+)\]/);
                    if (match && parseInt(match[1]) > maxIndex) {
                        maxIndex = parseInt(match[1]);
                    }
                });
            });

            //Increment to get the new index
            let newIndex = maxIndex + 1;

            let documentContainerHTML = `
        <div class="instructor-documents-item border p-2 mt-2">
            <div class="form-group">
                <label class="form-label" for="documents_name_${newIndex}">Document Name</label>
                <input type="text" name="instructor_documents[${newIndex}][name]" id="documents_name_${newIndex}" class="form-control">
                <div id="instructor_documents_${newIndex}_name_error_up" class="text-danger error_e"></div>
            </div>

            <button type="button" class="btn btn-danger remove-documents-container">X</button>
        </div>
    `;

            $("#edit_instructor_documents_items").append(documentContainerHTML);
        });



        // Remove Instructor Documents section
        $(document).on("click", ".remove-documents-container", function() {
            $(this).closest(".instructor-documents-item").remove();
        });



        // Enable feedback questions
        let questionIndex = 1;

        $('#enable_feedback').on('change', function() {
            $('#feedbackConfigSection').toggle(this.checked);
        });

        $('#add_question_btn').on('click', function() {
            $('#feedback_questions_container').append(`
            <div class="feedback-question mb-3 border p-3 position-relative rounded">
                <input type="text" name="feedback_questions[${questionIndex}][question]" class="form-control mb-2 question-input" placeholder="Enter question">
                <div id="feedback_questions_${questionIndex}_question_error" class="text-danger error_e"></div>
                <select name="feedback_questions[${questionIndex}][answer_type]" class="form-select answer-type">
                    <option value="">Select Answer Type</option>
                    <option value="yes_no">Yes / No</option>
                    <option value="rating">Rating (1-5)</option>
                </select>
                <div id="feedback_questions_${questionIndex}_answer_type_error" class="text-danger error_e"></div>
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-question-btn">&times;</button>
            </div>
        `);
            questionIndex++;
        });

        // Remove a question block
        $('#feedback_questions_container').on('click', '.remove-question-btn', function() {
            $(this).closest('.feedback-question').remove();
        });

        //Enabling Custom Time

        let customTimeIndex = 1;
        $('#enable_custom_time_tracking').on('change', function() { 
            $('#customTimeConfigSection').toggle(this.checked);
        });

        $('#add_another_time_btn').on('click', function() {
            $('#custom_time_tracking_container').append(`
            <div class="custom-time mb-3 border p-3 position-relative rounded">
                <label for="custom_time_name">Custom Time Type Name</label>
                <input type="text" name="custom_time[${customTimeIndex}][name]" id="custom_time_name" class="form-control" placeholder="e.g. Night Flying">
                <div id="custom_time_${customTimeIndex}_name_error" class="text-danger error_e"></div>
                <label for="custom_time_hours">Required Hours</label>
                <input type="number" name="custom_time[${customTimeIndex}][hours]" id="custom_time_hours" class="form-control" placeholder="e.g. 4">
                <div id="custom_time_${customTimeIndex}_hours_error" class="text-danger error_e"></div>
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-custom-time-btn" style="transform: translate(18px, -18px);">&times;</button>
        </div>
        `);
            customTimeIndex++;
        });

        // Remove Custom time con
        $('#customTimeConfigSection').on('click', '.remove-custom-time-btn', function() {
            $(this).closest('.custom-time').remove();
        });



    });

    function generatePrerequisiteHtml(prerequisite, index) {
        return `
        <div class="prerequisite-item border p-2 mt-2">
            <div class="form-group">
                <label class="form-label">Prerequisite Detail</label>
                <input type="hidden" name="prerequisite_id[]" value="${prerequisite.id ?? '' }">
                <input type="text" class="form-control" name="prerequisite_details[]" value="${prerequisite.prerequisite_detail}">
            </div>

            <div class="form-group">
                <label class="form-label">Prerequisite Type</label>
                <div>
                   <label for="prerequisite_number_${index}">
                        <input type="radio" id="prerequisite_number_${index}" name="prerequisite_type[${index}]" value="number" ${prerequisite.prerequisite_type === 'number' ? 'checked' : ''}> Number
                    </label>

                    <label for="prerequisite_text_${index}">
                        <input type="radio" id="prerequisite_text_${index}" name="prerequisite_type[${index}]" value="text" ${prerequisite.prerequisite_type === 'text' ? 'checked' : ''}> Text
                    </label>

                    <label for="prerequisite_file_${index}">
                        <input type="radio" id="prerequisite_file_${index}" name="prerequisite_type[${index}]" value="file" ${prerequisite.prerequisite_type === 'file' ? 'checked' : ''}> File
                    </label>
                </div>
            </div>

            <button type="button" class="btn btn-danger remove-prerequisite">X</button>
        </div>
    `;
    }


    function generateDocumentsContainerHtml(instructor_documents, index) {

        let documentName = instructor_documents.document_name || '';
        let filePath = instructor_documents.file_path ? `/storage/${instructor_documents.file_path}` : '';
        let existingFilePath = instructor_documents.file_path || '';
        let docRowId = instructor_documents.id || '';

        let uploadedFileLinkHtml = '';
        if (filePath) {
            uploadedFileLinkHtml = `<div class="mt-2">
                                <a href="${filePath}" target="_blank">View Uploaded Document</a>
                            </div>`;
        }

        return `<div class="instructor-documents-item border p-2 mt-2">
                <div class="form-group">
                    <label class="form-label">Document Name</label>
                     <input type="hidden" name="instructor_documents[${index}][id]" value="${docRowId}">
                    <input type="text" name="instructor_documents[${index}][name]" value="${documentName}" id="documents_name_${index}" class="form-control">

                    <div id="instructor_documents_${index}_name_error_up" class="text-danger error_e"></div>
                </div>
                <button type="button" class="btn btn-danger remove-documents-container mt-2">X</button>
            </div>`;
    }




    $(document).on("change", "#select_org_unit", function() {
        var ou_id = $(this).val();
        var $groupSelect = $(".groups-select");
        var $resourceSelect = $(".resources-select");
        var $ato_numSelect = $("#select_ato_number");
        $ato_numSelect.empty().trigger("change");

        if ($("#enable_opc").is(":checked")) {
            fetchRatingsByOu(ou_id, "#enable_aircraft");
        }

        $.ajax({
            url: "/group/get_ou_group/",
            type: "GET",
            data: {
                'ou_id': ou_id
            },
            dataType: "json", // Ensures response is treated as JSON
            success: function(response) {

                if (response.org_group && Array.isArray(response.org_group)) {
                    var options = "<option value=''>Select Group </option>";

                    response.org_group.forEach(function(value) {
                        options += "<option value='" + value.id + "'>" + value.name +
                            "</option>";
                    });
                    $groupSelect.html(options);
                    $groupSelect.trigger("change");
                }
                if (response.org_resource && Array.isArray(response.org_resource)) {

                    var resource = "<option value=''>Select Resource </option>";

                    response.org_resource.forEach(function(value) {
                        resource += "<option value='" + value.id + "'>" + value.name +
                            "</option>";

                    });
                    $resourceSelect.html(resource);
                    $resourceSelect.trigger("change");
                }
                if (response.ato_num && response.ato_num.length > 0) {
                    var ato_number = "<option value=''>Select ATO Number</option>";
                    ato_number += "<option value='generic'>Generic</option>";
                    var hasData = false;

                    $.each(response.ato_num, function(index, value) {
                        if (value.uk_ato_number) {
                            ato_number += "<option value='uk-" + value.uk_ato_number + "'>UK ATO Number - " + value.uk_ato_number + "</option>";
                            hasData = true;
                        }
                        if (value.easa_ato_number) {
                            ato_number += "<option value='easa-" + value.easa_ato_number + "'>EASA ATO Number - " + value.easa_ato_number + "</option>";
                            hasData = true;
                        }
                    });

                    if (!hasData) {
                        ato_number += "<option disabled>No ATO number found</option>";
                    }

                    $ato_numSelect.html(ato_number);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });


    // Edit ou   

    $('#edit_select_org_unit').on('change', function() {
        var ou_id = $(this).val();
        var $groupSelect = $(".groups-select");
        var $resourceSelect = $(".resources-select");
        $groupSelect.empty().append("<option value=''>Select Group</option>").trigger("change");
        var $ato_numSelect = $("#edit_select_ato_number");

        // if ($("#edit_enable_opc").is(":checked")) {
        //     fetchRatingsByOu(ou_id, "#edit_enable_aircraft");
        // }

        if ($('#edit_enable_opc').is(':checked')) {
            fetchEditRatingsByOu($(this).val());
        }

        $.ajax({
            url: "/group/get_ou_group/",
            type: "GET",
            data: {
                'ou_id': ou_id
            },
            dataType: "json", // Ensures response is treated as JSON
            success: function(response) {
                if (response.org_group && Array.isArray(response.org_group)) {
                    var options = "<option value=''>Select Group </option>";
                    response.org_group.forEach(function(value) {
                        options += "<option value='" + value.id + "'>" + value.name +
                            "</option>";
                    });
                    $groupSelect.html(options);
                    $groupSelect.trigger("change");
                }

                if (response.org_resource && Array.isArray(response.org_resource)) {
                    var options = "<option value=''>Select Resource </option>";
                    response.org_resource.forEach(function(value) {
                        options += "<option value='" + value.id + "'>" + value.name +
                            "</option>";
                    });
                    $resourceSelect.html(options);
                    $resourceSelect.trigger("change");
                }

                if (response.ato_num && response.ato_num.length > 0) {
                    var ato_number = "<option value=''>Select ATO Number</option>";
                    var hasData = false;

                    $.each(response.ato_num, function(index, value) {
                        if (value.uk_ato_number) {
                            ato_number += "<option value='uk-" + value.uk_ato_number + "'>UK ATO Number - " + value.uk_ato_number + "</option>";
                            hasData = true;
                        }
                        if (value.easa_ato_number) {
                            ato_number += "<option value='easa-" + value.easa_ato_number + "'>EASA ATO Number - " + value.easa_ato_number + "</option>";
                            hasData = true;
                        }
                    });

                    if (!hasData) {
                        ato_number += "<option disabled>No ATO number found</option>";
                    }

                    $ato_numSelect.html(ato_number);
                } else {
                    console.error("Invalid response format:", response);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $(document).ready(function() {
        // 🔹 Function to toggle MP options
        function toggleMPOpions(checkboxSelector, selectSelector) {
            const checkbox = $(checkboxSelector);
            const select = $(selectSelector);

            function updateVisibility() {

                if (checkbox.is(":checked")) {

                    // Show MP Event + SP+MP Event
                    select.find("option[value='2'], option[value='3']").show();

                    // Hide SP Event
                    select.find("option[value='1']").hide();

                    // If currently selected SP Event → change to MP Event
                    if (select.val() === "1") {
                        select.val("2");
                    }

                } else {
                    // Hide options 2 & 3
                    select.find("option[value='2'], option[value='3']").hide();

                    // Show back SP Event
                    select.find("option[value='1']").show();

                    // Reset selection back to SP Event
                    select.val("1");
                }
            }

            // Run once on page load (for edit mode)
            updateVisibility();

            // Run every time checkbox changes
            checkbox.on("change", updateVisibility);
        }

        // 🔹 Initialize for both Add & Edit sections
        toggleMPOpions("#enable_more_mp", "#enable_mp_lifus");
        toggleMPOpions("#edit_enable_more_mp", "#edit_enable_mp_lifus");
    });

    function toggleOPC(checkbox, container, aircraftSelect, ouSelect) {
        if ($(checkbox).is(":checked")) { 
            $(container).stop(true, true).slideDown();
            let ouId = $(ouSelect).val();
            if (ouId) {
                fetchRatingsByOu(ouId, aircraftSelect);
            }
        } else { 
            $(container).stop(true, true).slideUp();
            $(aircraftSelect).val('').trigger('change');
            $('#opc_validity_col').hide();
            $('#opc_extend_eom_col').hide();
            $('#select_tag_div').hide();
            $('#create_rhs_tag_col').addClass('d-none');
            $('#create_is_rhs').prop('checked', false);
            $('#is_rhs').prop('checked', false); 

        }
    }

    $(document).ready(function() {
        // CREATE
        $("#enable_opc").on("change", function() { 
            toggleOPC(this, ".opc-aircraft", "#enable_aircraft", "#select_org_unit");

        });

        // EDIT
        $("#edit_enable_opc").on("change", function() { 
            toggleOPC(this, ".opc-aircraft-edit", "#edit_enable_aircraft", "#edit_select_org_unit");
        });

    });


    // Click on copy icon

    // $('.course-copy-icon').on('click', function () { 
    //     let course_id = $(this).data("course-id");
    //     if (!course_id) {
    //         alert("Invalid Course ID.");
    //         return;
    //     }

    //     if (!confirm("Are you sure you want to duplicate this Course ?")) {
    //         return;
    //     }
    //       const loggedInOuId = "{{ $ou_id }}";
    //       alert(loggedInOuId);

    //     // store course id temporarily
    //     selectedCourseId = course_id;
    //     // open modal
    //     $('#select_ou').modal('show');
    // });

    // Course Copy
    // $('#confirm_copy_course').on('click', function () { 

    //     let ou_id = $('#select_ou_copy').val();

    //     if (ou_id == '') {
    //         $('#select_ou_copy_error').text('Please select Org Unit');
    //         return;
    //     }

    //     $('#select_ou_copy_error').text('');

    //     $.ajax({
    //         url: "{{ url('copy_course') }}",
    //         type: "POST",
    //         data: {
    //             course_id: selectedCourseId,
    //             ou_id: ou_id,
    //             _token: "{{ csrf_token() }}"
    //         },
    //         beforeSend: function () {
    //             $('#confirm_copy_course').prop('disabled', true).text('Processing...');
    //         },
    //         success: function (response) {

    //             $('#confirm_copy_course').prop('disabled', false).text('Continue');

    //             if (response.status === true || response.status === "true") {
    //                 alert(response.message);
    //                 $('#select_ou').modal('hide');
    //                 window.location.reload();
    //                 return;
    //             }

    //             alert(response.error || 'Something went wrong.');
    //         },
    //         error: function (xhr) {
    //             $('#confirm_copy_course').prop('disabled', false).text('Continue');
    //             alert('Server Error: ' + xhr.status);
    //         }
    //     });
    // });
    const loggedInOuId = "{{ $ou_id ?? '' }}";
    let selectedCourseId = null;

    $('.course-copy-icon').on('click', function() {

        let course_id = $(this).data("course-id");

        if (!course_id) {
            alert("Invalid Course ID.");
            return;
        }

        if (!confirm("Are you sure you want to copy this Course ?")) {
            return;
        }

        selectedCourseId = course_id;

        // ✅ If OU already exists → Direct copy
        if (loggedInOuId !== '') {
            copyCourse(course_id, loggedInOuId);
            return;
        }

        // ❌ OU missing → open modal
        $('#select_ou').modal('show');
    });

    $('#confirm_copy_course').on('click', function() {

        let ou_id = $('#select_ou_copy').val();

        if (ou_id === '') {
            $('#select_ou_copy_error').text('Please select Org Unit');
            return;
        }

        $('#select_ou_copy_error').text('');

        copyCourse(selectedCourseId, ou_id);
    });

    function copyCourse(course_id, ou_id) {

        $.ajax({
            url: "{{ url('copy_course') }}",
            type: "POST",
            data: {
                course_id: course_id,
                ou_id: ou_id,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
                $('#confirm_copy_course')
                    .prop('disabled', true)
                    .text('Processing...');
            },
            success: function(response) {

                $('#confirm_copy_course')
                    .prop('disabled', false)
                    .text('Continue');

                if (response.status === true || response.status === "true") {
                    alert(response.message);
                    $('#select_ou').modal('hide');
                    window.location.reload();
                    return;
                }

                alert(response.error || 'Something went wrong.');
            },
            error: function(xhr) {
                $('#confirm_copy_course')
                    .prop('disabled', false)
                    .text('Continue');

                alert('Server Error: ' + xhr.status);
            }
        });
    }

    // In Edit course

    // Toggle RHS Section
    $('#is_rhs').on('change', function() { 

        if ($(this).is(':checked')) {
            $('#edit_rhs_tag_col').removeClass('d-none');
        } else {
            $('#edit_rhs_tag_col').addClass('d-none');
           // $('#rhs_rows_container').empty();
        }
    });

    // Add Master Tag Row
    $('#add_master_tag').on('click', function() { console.log('add_master_tag');
        editaddRow('#rhs_select_row_template');
    });

    // Add Manual Tag Row
    $('#add_manual_tag').on('click', function() {   console.log('add_manual_tag');
        editaddRow('#rhs_input_row_template');
    });

    // Remove Row
    $(document).on('click', '.remove_rhs_row', function() {
        $(this).closest('.rhs_row').remove();
    });

    function editaddRow(templateId) {  console.log('Template found:', $(templateId).length);
        let row = $(templateId)
            .clone()
            .removeClass('d-none')
            .removeAttr('id');

        $('#rhs_rows_container').append(row);
    }

    // In create course

    $('#create_is_rhs').on('change', function() { 
        if ($(this).is(':checked')) {
            $('#create_rhs_tag_col').removeClass('d-none');
        } else { 
            $('#create_rhs_tag_col').addClass('d-none');
           // $('#create_rhs_tag_col').empty();
          
        }
    });

    // Add Master Tag Row
    $('#create_master_tag').on('click', function() {
        addRow('#create_rhs_select_row_template');
    });

    // Add Manual Tag Row
    $('#create_add_manual_tag').on('click', function() {
        addRow('#create_rhs_input_row_template');
    });

    // Remove Row
    $(document).on('click', '.create_remove_rhs_row', function() {
        $(this).closest('.create_rhs_row').remove();
    });

    function addRow(templateId) {
        let row = $(templateId)
            .clone()
            .removeClass('d-none')
            .removeAttr('id');

        $('#create_rhs_tag_col').append(row);
    }

    $(document).on('change', '#enable_aircraft', function() {
        $('#opc_validity_col').show();
        $('#opc_extend_eom_col').show();
        $('#select_tag_div').show();
    });

    $(document).on('change', '#edit_enable_aircraft', function() { 
        $('#edit_opc_validity_col').show();
        $('#edit_opc_extend_eom_col').show();
        $('#edit_tag_div').show();
    });
</script>

@endsection