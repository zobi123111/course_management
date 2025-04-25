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
</style>



@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

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
            <div class="row">
                @forelse($courses as $val)
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                    <div class="course_card course-card">
                        <div class="course-image-container" style="position: relative;">
                            @if($val->image)
                            <img src="{{ asset('storage/' . $val->image) }}" class="card-img-top course-image"
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">Create Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="courses" method="POST" enctype="multipart/form-data"
                    class="row g-3 needs-validation">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example"
                            id="select_org_unit">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <div id="course_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="course_type" class="form-label">Course Type<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="course_type" id="course_type" required>
                            <option value="">Select Course Type</option>
                            <option value="one_event">One Event</option>
                            <option value="multi_lesson">Multiple Lesson</option>
                        </select>
                        <div id="course_type_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                        <div id="description_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="image" class="form-label">Image<span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="image_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
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
                        <label for="groups" class="form-label">Assigned Resource<span
                                class="text-danger"></span></label>
                        <select class="form-select resources-select" name="resources[]" multiple="multiple">
                            @foreach($resource as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                        <div id="resources_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
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

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitCourse" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Courses-->

<!-- Edit Courses -->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourse" class="row g-3 needs-validation">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
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
                    @endif
                    <div class="form-group">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <input type="hidden" name="course_id" class="form-control">
                        <div id="course_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="course_type" class="form-label">Course Type<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="course_type" id="edit_course_type" required>
                            <option value="">Select Course Type</option>
                            <option value="one_event">One Event</option>
                            <option value="multi_lesson">Multiple Lesson</option>
                        </select>
                        <div id="course_type_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        <div id="description_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Image<span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="image_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="duration" class="form-label">Course Duration<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="duration_type" id="edit_duration_type">
                            <option value="">Select Duration Type</option>
                            <option value="hours">Hours</option>
                            <option value="events">Events</option>
                        </select>
                        <input type="number" name="duration_value" class="form-control mt-2" id="edit_duration_value"
                            placeholder="Enter number of hours/events">
                        <div id="duration_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
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

                    <div class="form-group">
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
                            <div class="feedback-question mb-3" id="">
                                
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_question_btn">Add Another Question</button>
                    </div>
      

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="enable_prerequisites"> Enable Prerequisites
                        </label>
                    </div>
                    <div id="prerequisites_container" style="display: none;" >
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

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateCourse" class="btn btn-primary sbt_btn">Update</button>
                    </div>
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
@endsection

@section('js_scripts')

<script>
function initializeSelect2() {
    $('.groups-select').select2({
        allowClear: true,
        placeholder: 'Select the Group',
        multiple: true,
        dropdownParent: $('body') // move outside the modal
    });

    $(".resources-select").select2({
        maximumSelectionLength: 3,
        placeholder: 'Select the Resource',
        allowClear: true,
        dropdownParent: $('body') // move outside the modal
    });
}


$(document).ready(function() {
    $('#courseTable').DataTable();
    initializeSelect2();
    $("#createCourse").on('click', function() {
        $(".error_e").html('');
        $("#courses")[0].reset();
        $(".groups-select").val(null).trigger("change");
        $("#createCourseModal").modal('show');
        $('#createCourseModal').on('shown.bs.modal', function() {
            initializeSelect2();
        });
    });

    $("#submitCourse").on("click", function(e) {
        e.preventDefault();
        $('.error_e').html('');
        var formData = new FormData($('#courses')[0]);

        $.ajax({
            url: '{{ url("/course/create") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#createCourseModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
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
                $('#edit_select_org_unit').val(response.course.ou_id);

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
                    $resourcesSelect.val(response.courseResources.map(cr => cr
                        .resources_id)).trigger('change');
                }

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
                    $('#edit_feedback_questions_container').empty();
                }
                $('#editCourseModal').modal('show');

                $('#editCourseModal').on('shown.bs.modal', function() {
                    $('#editCourseModal .resources-select').select2({
                        dropdownParent: $('#editCourseModal'),
                        width: '100%',
                        allowClear: true,
                        placeholder: 'Select the Group',
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
    $('#edit_enable_feedback').on('change', function () {
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
    $('#edit_feedback_questions_container').on('click', '.remove-question-btn', function () {
        $(this).closest('.feedback-question').remove();
    });



    // Update Course functionality
    $('#updateCourse').on('click', function(e) {
        e.preventDefault();
        var formData = new FormData($('#editCourse')[0]);
        formData.append('enable_prerequisites', $('#enable_prerequisites').is(':checked') ? 1 : 0);
        $.ajax({
            url: "{{ url('/course/update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#editCourseModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
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
    $("#enable_prerequisites").change(function () {
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


    // Enable feedback questions
    let questionIndex = 1;

    $('#enable_feedback').on('change', function () {
        $('#feedbackConfigSection').toggle(this.checked);
    });

    $('#add_question_btn').on('click', function () {
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
    $('#feedback_questions_container').on('click', '.remove-question-btn', function () {
        $(this).closest('.feedback-question').remove();
    });

   

});

function generatePrerequisiteHtml(prerequisite, index) {
    return `
        <div class="prerequisite-item border p-2 mt-2">
            <div class="form-group">
                <label class="form-label">Prerequisite Detail</label>
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

$(document).on("change", "#select_org_unit", function() {
    var ou_id = $(this).val();
    var $groupSelect = $(".groups-select");
    var $resourceSelect = $(".resources-select");


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

                    console.log(resource)
                });
                $resourceSelect.html(resource);
                $resourceSelect.trigger("change");
            } else {
                console.error("Invalid response format:", response);
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
            } else {
                console.error("Invalid response format:", response);
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
});
</script>

@endsection