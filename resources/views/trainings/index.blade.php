@section('title', 'Training Event')
@section('sub-title', 'Training Event')
@extends('layout.app')
@section('content')


@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
@if(session()->has('error'))
<div id="successMessage" class="alert alert-warning fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('error') }}
</div>
@endif

@if(checkAllowedModule('training','training.store')->isNotEmpty())
    <div class="create_btn">
        <button class="btn btn-primary create-button" id="createTrainingEvent" data-toggle="modal"
            data-target="#createTrainingEventModal">Create Training Event</button>
    </div>
@endif
<br>

<div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="trainingEventTable">
        <thead>
            <tr>
                <th scope="col">Event</th>
                <th scope="col">Student</th>
                <th scope="col">Instructor</th>
                <th scope="col">Resource</th>
                <th scope="col">Event Date</th>
                <th scope="col">Start Time</th>
                <th scope="col">End Time</th>
                @if(checkAllowedModule('training','training.show')->isNotEmpty() || checkAllowedModule('training','training.delete')->isNotEmpty() || checkAllowedModule('training','training.delete')->isNotEmpty() || checkAllowedModule('training','training.grading-list')->isNotEmpty())
                <th scope="col">Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($trainingEvents as $event)
                @php
                    $lesson = $event->firstLesson;
                @endphp 
            <tr>
                <td class="eventName">{{ $event->course?->course_name }}</td>
                <td>{{ $event->student?->fname }} {{ $event->student?->lname }}</td>
                <td>{{ $lesson?->instructor?->fname }} {{ $lesson?->instructor?->lname }}</td>
                <td>{{ $lesson?->resource?->name }}</td>
                <td>{{ $lesson?->lesson_date ? date('d-m-y', strtotime($lesson->lesson_date)) : '' }}</td>
                <td>{{ $lesson?->start_time ? date('h:i A', strtotime($lesson->start_time)) : '' }}</td>
                <td>{{ $lesson?->end_time ? date('h:i A', strtotime($lesson->end_time)) : '' }}</td>
                <td>
                @if(get_user_role(auth()->user()->role) == 'administrator')  
                    @if(empty($event->is_locked))
                        @if(checkAllowedModule('training','training.edit')->isNotEmpty()  && !$event->is_graded)
                            <i class="fa fa-edit edit-event-icon me-2" style="font-size:25px; cursor: pointer;"
                            data-event-id="{{ encode_id($event->id) }}"></i>
                        @endif
                        @if(checkAllowedModule('training','training.delete')->isNotEmpty())
                            <i class="fa-solid fa-trash delete-event-icon me-2" style="font-size:25px; cursor: pointer;"
                            data-event-id="{{ encode_id($event->id) }}"></i>
                        @endif
                        @if(checkAllowedModule('training','training.show')->isNotEmpty())
                            <a href="{{ route('training.show', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Training Event" style="font-size:18px; cursor: pointer;">
                            <i class="fa fa-eye text-danger me-2"></i>
                            </a>            
                        @endif
                        @if($event->can_end_course)
                            {{-- Active “End Course” button/icon --}}
                            <button
                                class="btn btn-sm btn-flag-checkered end-course-btn"
                                data-event-id="{{ encode_id($event->id) }}"
                                title="End Course/Event"
                            >
                                <i class="fa fa-flag-checkered text-primary"></i>
                            </button>
                        @endif
                    @else
                        {{-- This event is already locked/ended --}}
                        <span class="badge bg-secondary" data-bs-toggle="tooltip"
                            title="This course has been ended and is locked from editing">
                            <i class="bi bi-lock-fill me-1"></i>Ended
                        </span>
                    @endif
                @elseif(get_user_role(auth()->user()->role) == 'instructor')   
                   @if($event->is_locked == 0)
                        @if(checkAllowedModule('training','training.grading-list')->isNotEmpty())
                            <a href="{{ route('training.grading-list', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Grading" style="font-size:18px; cursor: pointer;">
                            <i class="fa fa-list text-danger me-2"></i>
                            </a>
                        @endif
                    @endif
                @else                   
                    @if(checkAllowedModule('training','training.grading-list')->isNotEmpty())
                        <a href="{{ route('training.grading-list', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Grading" style="font-size:18px; cursor: pointer;">
                        <i class="fa fa-list text-danger me-2"></i>
                        </a>
                    @endif    
                @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>


<!-- Create Training Event-->
<div class="modal fade" id="createTrainingEventModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Create Training Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" id="trainingEventForm" method="POST" class="row g-3">
                @csrf

                @if(auth()->user()->is_owner == 1)
                <div class="col-md-6">
                    <label class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                    <select class="form-select" name="ou_id" id="select_org_unit">
                        <option value="">Select Org Unit</option>
                        @foreach($organizationUnits as $val)
                        <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                        @endforeach
                    </select>
                    <div id="ou_id_error" class="text-danger error_e"></div>
                </div>
                @endif
                <!-- Select User -->
                <div class="col-md-6">
                    <label class="form-label">Select Student<span class="text-danger">*</span></label>
                    <select class="form-select" name="student_id" id="select_user">
                        <option value="">Select Student</option>
                        @foreach($students as $val)
                        <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                        @endforeach
                    </select>
                    <div id="student_id_error" class="text-danger error_e"></div>
                </div>
                <!-- Select Course -->
                <div class="col-md-6">
                    <label class="form-label">Select Course<span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" id="select_course">
                        <option value="">Select Course</option>
                        @foreach($courses as $val)
                        <option value="{{ $val->id }}">{{ $val->course_name }}</option>
                        @endforeach
                    </select>
                    <div id="course_id_error" class="text-danger error_e"></div>
                </div>
                <!-- Event Date-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Course Start Date<span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" id="event_date">
                        <div id="event_date_error" class="text-danger error_e"></div>
                    </div>
                </div>
                <div id="lessonDetailsContainer" class="lesson-box mt-3"></div> 
                <!-- Total Time (Calculated) -->
                <div class="col-md-6">
                    <label class="form-label">Total Time (hh:mm)<span class="text-danger">*</span></label>
                    <input type="text" name="total_time" class="form-control" id="total_time" readonly>
                    <div id="total_time_error" class="text-danger error_e"></div>
                </div>

                <!-- License Number (Extracted from user profile) -->
                <div class="col-md-6">
                    <label class="form-label">Student License Number</label>    
                    <input type="text" name="std_licence_number" class="form-control" id="std_licence_number" value="">
                    <div id="std_licence_number_error" class="text-danger error_e"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="submitTrainingEvent" class="btn btn-primary sbt_btn">Save</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
<!--End of Training Event-->

<!-- Edit Training Event-->
<div class="modal fade" id="editTrainingEventModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Edit Training Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" id="editTrainingEventForm" method="POST" class="row g-3">
                @csrf
                <input type="hidden" name="event_id" id="edit_event_id">
                @if(auth()->user()->is_owner == 1)
                    <div class="col-md-6">
                        <label class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select select_org_unit" name="ou_id" id="edit_ou_id">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                                <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error_up" class="text-danger error_e"></div>
                    </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">Select Student<span class="text-danger">*</span></label>
                    <select class="form-select" name="student_id" id="edit_select_user">
                        <option value="">Select Student</option>
                        @foreach($students as $val)
                        <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                        @endforeach
                    </select>
                    <div id="student_id_error_up" class="text-danger error_e"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Select Course<span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" id="edit_select_course">
                        <option value="">Select Course</option>
                        @foreach($courses as $val)
                            <option value="{{ $val->id }}">{{ $val->course_name }}</option>
                        @endforeach
                    </select>
                    <div id="course_id_error_up" class="text-danger error_e"></div>
                </div>
                <!-- Event Date-->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Course Start Date<span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" id="edit_event_date">
                        <div id="event_date_error_up" class="text-danger error_e"></div>
                    </div>
                </div>
                <div id="editLessonDetailsContainer" class="mt-3"></div>
                <!-- Total Time (Calculated) -->
                <div class="col-md-6">
                    <label class="form-label">Total Time (hh:mm)<span class="text-danger">*</span></label>
                    <input type="text" name="total_time" class="form-control" id="edit_total_time" readonly>
                    <div id="total_time_error_up" class="text-danger error_e"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Student Licence Number</label>
                    <input type="text" name="std_licence_number" class="form-control" id="edit_std_licence_number" readonly>
                    <div id="std_licence_number_error_up" class="text-danger error_e" ></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateTrainingEvent" class="btn btn-primary sbt_btn">Update</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>
<!--End of Edit Training Event-->

<!-- Delete Group Modal -->
<form action="{{ url('training/delete') }}" id="deleteTrainingEventForm" method="POST">
    @csrf
    <div class="modal fade" id="deleteTrainingEvent" tabindex="-1" aria-labelledby="deleteEventLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTrainingEventLabel">Delete Training Event</h5>
                    <input type="hidden" name="event_id" id="eventId">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Training Event "<strong><span id="append_name"></span></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="confirmDeleteTrainingEvent" class="btn btn-danger delete_group">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js_scripts')

<script>
var instructorsdata;
instructorsdata = @json($instructors);
var resourcesdata;
resourcesdata = @json($resources);

const currentUser = {
    id: {{ auth()->user()->id }},
    role: "{{ get_user_role(auth()->user()->role) }}"
};

// Delegate change event to dynamically added instructor selects
$(document).on('change', 'select[name^="lesson_data"][name$="[instructor_id]"]', function () {
    let instructorId = $(this).val();
    let lessonBox = $(this).closest('.lesson-box');
    let licenseInput = lessonBox.find('input[name^="lesson_data"][name$="[instructor_license_number]"]');

    if (instructorId) {
        $.ajax({
            url: "{{ url('/training/get_instructor_license_no') }}/" + instructorId,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    licenseInput.val(response.instructor_licence_number || '');
                    if (!response.instructor_licence_number) {
                        alert("Instructor license number not found.");
                    }
                } else {
                    licenseInput.val('');
                    alert("Instructor not found.");
                }
            },
            error: function () {
                licenseInput.val('');
                console.error("Failed to fetch license number");
                alert("An error occurred while fetching the license number.");
            }
        });
    } else {
        licenseInput.val('');
    }
});

function initializeSelect2() {
    $('.select_lesson').select2({
        allowClear: true,
        placeholder: 'Select Lessons',
        multiple: true,
        dropdownParent: $('.modal:visible'),
    });

}

$(document).ready(function() {
    $('#groupTable').DataTable();
    initializeSelect2();
    
    $("#createTrainingEvent").on('click', function() {
        $(".error_e").html('');
        $("#trainingEventForm")[0].reset();
        $('#total_time').val('');
        $('#lessonDetailsContainer').empty();
        $("#createTrainingEventModal").modal('show');
        $('#createTrainingEventModal').on('shown.bs.modal', function() {
            initializeSelect2();
        });
    })

    // On create screen
    $(document).on('change', '.lesson-start-time, .lesson-end-time', function () {
        calculateTotalTime('#total_time');
    });

    // On edit screen
    $(document).on('change', '.lesson-start-time, .lesson-end-time', function () {
        calculateTotalTime('#edit_total_time');
    });

    function calculateTotalTime(outputSelector = '#total_time') {
        let totalMinutes = 0;

        $('.lesson-box').each(function () {
            let lessonId = $(this).data('lesson-id');
            let start = $(`input[name="lesson_data[${lessonId}][start_time]"]`).val();
            let end = $(`input[name="lesson_data[${lessonId}][end_time]"]`).val();

            if (start && end) {
                let startTime = moment(start, "HH:mm");
                let endTime = moment(end, "HH:mm");

                if (endTime.isBefore(startTime)) {
                    endTime.add(1, 'day');
                }

                totalMinutes += endTime.diff(startTime, 'minutes');
            }
        });

        let hours = Math.floor(totalMinutes / 60);
        let minutes = totalMinutes % 60;
        let totalFormatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

        $(outputSelector).val(totalFormatted);
    }

    $(document).on('change', '#select_org_unit, #edit_ou_id', function() {      
        var ou_id = $(this).val();
        // Determine which modal is being used
        var isEditModal = $(this).attr('id') === 'edit_ou_id';

        // Select correct dropdowns based on the modal
        var studentDropdown = isEditModal ? $('#edit_select_user') : $('#select_user');
        var instructorDropdown = isEditModal ? $('#edit_select_instructor') : $('#select_instructor');
        var resourceDropdown = isEditModal ? $('#edit_select_resource') : $('#select_resource');

        $.ajax({
            url: "{{ url('/training/get_ou_students_instructors_resources') }}/" + ou_id, // Append ou_id in URL
            type: "GET",
            dataType: "json",
            success: function (response) {
                // console.log(response.resources);
                // Store selected values before clearing
                var selectedStudent = studentDropdown.data("selected-value") || [];
                var selectedInstructor = instructorDropdown.data("selected-value") || [];
                var selectedResource = resourceDropdown.data("selected-value") || [];

                // Populate Students
                var studentOptions = '<option value="">Select Student</option>';
                if (response.students && response.students.length > 0) {
                    $.each(response.students, function(index, student) {
                        var selected = student.id == selectedStudent ? 'selected' : '';
                        studentOptions += '<option value="' + student.id + '" ' + selected + '>' + student.fname + ' ' + student.lname + '</option>';
                    });
                }
                studentDropdown.html(studentOptions); // Update dropdown

                // Populate Instructors
                var instructorOptions = '<option value="">Select Instructor</option>';
                if (response.instructors && response.instructors.length > 0) {
                    instructorsdata = response.instructors;
                    $.each(instructorsdata, function(index, instructor) {
                        var selected = instructor.id == selectedInstructor ? 'selected' : '';
                        instructorOptions += '<option value="' + instructor.id + '" ' + selected + '>' + instructor.fname + ' ' + instructor.lname + '</option>';
                    });
                }
                instructorDropdown.html(instructorOptions); // Update dropdown

                // Populate Resources
                var resourceOptions = '<option value="">Select Resource</option>';
                if (response.resources && response.resources.length > 0) {
                    resourcesdata = response.resources;
                    $.each(resourcesdata, function(index, resource) {
                        var selected = resource.id == selectedResource ? 'selected' : '';
                        resourceOptions += '<option value="' + resource.id + '" ' + selected + '>' + resource.name + '</option>';
                    });
                }
                resourceDropdown.html(resourceOptions); // Update dropdown
        },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Error fetching data. Please try again.');
            }
        });
    });

    $(document).on('change', '#select_user, #edit_select_user', function() {
        var userId = $(this).val();
        // alert(userId);
        // Determine which modal is being used
        var isEditModal = $(this).attr('id') === 'edit_select_user';

        // Select the correct fields based on the modal
        var ouDropdown = isEditModal ? $('#edit_ou_id') : $('#select_org_unit');
        var licenceNumberField = isEditModal ? $('#edit_std_licence_number') : $('#std_licence_number');
        var courseDropdown = isEditModal ? $('#edit_select_course') : $('#select_course');

        // Get the selected Organization Unit ID (OU ID)
        var ou_id = ouDropdown.length ? ouDropdown.val() : '{{ auth()->user()->ou_id }}';
        if (userId) {
            $.ajax({
                url: "{{ url('/training/get_licence_number_and_courses') }}/" + userId + '/' + ou_id,
                type: "GET",
                success: function(response) {
                    if (response.success) {
                        // Update license number if available
                        if (response.licence_number) {
                            licenceNumberField.val(response.licence_number);
                        } else {
                            alert('Student License number not found!');
                            licenceNumberField.val('');
                        }

                        // Store the previously selected course (if available)
                        var selectedCourseId = courseDropdown.data("selected-value") || '';

                        // Update courses dropdown
                        var courseOptions = '<option value="">Select Course</option>'; // Default option
                        if (response.courses && response.courses.length > 0) {
                            $.each(response.courses, function(index, course) {
                                var selected = course.id == selectedCourseId ? 'selected' : '';
                                courseOptions += '<option value="' + course.id + '" ' + selected + '>' + course.course_name + '</option>';
                            });
                        } else {
                            alert('No courses found!'); // Notify user
                        }
                        courseDropdown.html(courseOptions); // Update dropdown
                    } else {
                        licenceNumberField.val('');
                        alert('License number not found!');
                        courseDropdown.html('<option value="">Select Course</option>'); // Clear courses
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            licenceNumberField.val('');
            courseDropdown.html('<option value="">Select Course</option>'); // Reset dropdown
        }
    });

    $(document).on('change', '#select_course, #edit_select_course', function () {
        var courseId = $(this).val();
        var isEditForm = $(this).attr('id') === 'edit_select_course';
        var lessonContainer = isEditForm ? $('#editLessonDetailsContainer') : $('#lessonDetailsContainer');

        // For edit mode, map saved lessons by lesson_id for quick lookup
        var lessonPrefillMap = {};
        if (isEditForm && typeof existingEventLessons !== 'undefined') {
            existingEventLessons.forEach(lesson => {
                lessonPrefillMap[lesson.lesson_id] = {
                    instructor_id: lesson.instructor_id || '',
                    resource_id: lesson.resource_id || '',
                    lesson_date: lesson.lesson_date || '',
                    start_time: lesson.start_time || '',
                    end_time: lesson.end_time || '',
                    departure_airfield: lesson.departure_airfield || '',
                    destination_airfield: lesson.destination_airfield || '',
                    instructor_license_number: lesson.instructor_license_number || ''
                };
            });
        }

        $.ajax({
            url: '{{ url("/training/get_course_lessons") }}',
            type: 'GET',
            data: { course_id: courseId },
            success: function (response) {
                lessonContainer.empty(); // Clear existing lesson boxes

                if (response.success && response.lessons.length > 0) {
                    let lessons = response.lessons;
                    resourcesdata = response.resources; 
                        response.lessons.forEach(function (lesson, idx) {
                            let prefillData = isEditForm && lessonPrefillMap[lesson.id] ? lessonPrefillMap[lesson.id] : {};
                            renderLessonBox(lesson, lessonContainer, prefillData, idx);  // ✅ index passed
                        });
                } else {
                    alert('No lessons found for the selected course.');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error fetching lessons. Please try again.');
            }
        });
    });

    $("#submitTrainingEvent").on("click", function(e) { 
        e.preventDefault();
        $.ajax({
            url: '{{ url("/training/create") }}',
            type: 'POST',
            data: $("#trainingEventForm").serialize(),
            success: function(response) {
                if(response.success){
                    $('#createTrainingEventModal').modal('hide');
                    location.reload();
                }else{
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                // $.each(validationErrors, function(key, value) {
                //     var msg = '<p>' + value + '<p>';
                //     $('#' + key + '_error').html(msg);
                // })
                // Clear old errors
                $('.error_e').html('');
                $.each(validationErrors, function(key, value) {
                    var formattedKey = key.replace(/\./g, '_') + '_error';
                    var errorMsg = '<p>' + value[0] + '</p>';
                    $('#' + formattedKey).html(errorMsg);
                });
            }
        });

    })

    $(document).on('click', '.edit-event-icon', function () {
        $('.error_e').html('');
        var eventId = $(this).data('event-id');
        $.ajax({
            url: "{{ url('/training/edit') }}",
            type: 'GET',
            data: { eventId: eventId },
            success: async function (response) {
                if (response.success) {
                    const event = response.trainingEvent;

                    // Store values temporarily
                    const selectedOU = event.ou_id;
                    const selectedStudent = event.student_id;
                    const selectedInstructor = event.instructor_id;
                    const selectedResource = event.resource_id;
                    const selectedCourse = event.course_id;

                    // Set static values
                    $('#edit_event_id').val(event.id);
                    $('#edit_std_licence_number').val(event.std_licence_number);
                    $('#edit_event_date').val(event.event_date);
                    $('#edit_total_time').val(moment(event.total_time, 'HH:mm:ss').format('HH:mm'));

                    // Set OU and wait for dependent dropdowns
                    $('#edit_ou_id').val(selectedOU).trigger('change');
                    await new Promise(resolve => setTimeout(resolve, 500));

                    // Set dropdown values
                    $('#edit_select_user').val(selectedStudent).data("selected-value", selectedStudent);
                    $('#edit_select_instructor').val(selectedInstructor).data("selected-value", selectedInstructor);
                    $('#edit_select_resource').val(selectedResource).data("selected-value", selectedResource);

                    await new Promise(resolve => setTimeout(resolve, 500));
                    $('#edit_select_course').val(selectedCourse).data("selected-value", selectedCourse);

                    // ✅ Global map of existing lessons for prefill
                    window.existingEventLessons = (event.event_lessons || []).map(l => ({
                        lesson_id: l.lesson_id,
                        instructor_id: l.instructor_id || '',
                        resource_id: l.resource_id || '',
                        lesson_date: l.lesson_date || '',
                        start_time: l.start_time || '',
                        end_time: l.end_time || '',
                        departure_airfield: l.departure_airfield || '',
                        destination_airfield: l.destination_airfield || '',
                        instructor_license_number: l.instructor_license_number || ''
                    }));

                    // ✅ Trigger the course change (will call renderLessonBox with prefill)
                    $('#edit_select_course').trigger('change');

                    $('#editTrainingEventModal').modal('show');
                } else {
                    console.error("Error: Invalid response format");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Something went wrong! Please try again.');
            }
        });
    });


    $('#edit_select_lesson').on('change', function () {
        const selectedLessonIds = $(this).val() || []; // Get current selected values (array)
        const allLessonBoxes = $('#editLessonDetailsContainer .lesson-box');

        // 1. Remove lesson boxes that are no longer selected
        allLessonBoxes.each(function () {
            const lessonId = $(this).data('lesson-id').toString();
            if (!selectedLessonIds.includes(lessonId)) {
                $(this).remove();
            }
        });

        // 2. Add boxes for newly selected lessons
        selectedLessonIds.forEach(function (lessonId) {
            // If already present, skip
            if ($(`#editLessonDetailsContainer .lesson-box[data-lesson-id="${lessonId}"]`).length === 0) {
                // Optional: You can get lesson title and other defaults via AJAX or preloaded data
                const lessonTitle = $('#edit_select_lesson option[value="' + lessonId + '"]').text().trim();

                const instructorOptions = @json($instructors).map(i =>
                    `<option value="${i.id}">${i.fname} ${i.lname}</option>`
                ).join('');

                const resourceOptions = @json($resources).map(r =>
                    `<option value="${r.id}">${r.name}</option>`
                ).join('');

                const lessonBox = `
                    <div class="col-12 mb-3 border rounded p-3 lesson-box" data-lesson-id="${lessonId}">
                        <input type="hidden" name="lesson_data[${lessonId}][lesson_id]" value="${lessonId}">
                        <h6 class="fw-bold mb-3">Lesson: ${lessonTitle}</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Instructor<span class="text-danger">*</span></label>
                                <select class="form-select" name="lesson_data[${lessonId}][instructor_id]">
                                    <option value="">Select Instructor</option>
                                    ${instructorOptions}
                                </select>
                                <div id="lesson_data_${lessonId}_instructor_id_error" class="text-danger error_e"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Resource<span class="text-danger">*</span></label>
                                <select class="form-select" name="lesson_data[${lessonId}][resource_id]">
                                    <option value="">Select Resource</option>
                                    ${resourceOptions}
                                </select>
                                <div id="lesson_data_${lessonId}_resource_id_error" class="text-danger error_e"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lesson Date<span class="text-danger">*</span></label>
                                <input type="date" name="lesson_data[${lessonId}][lesson_date]" class="form-control">
                                <div id="lesson_data_${lessonId}_lesson_date_error" class="text-danger error_e"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Start Time<span class="text-danger">*</span></label>
                                <input type="time" name="lesson_data[${lessonId}][start_time]" class="form-control lesson-start-time" data-lesson-id="${lessonId}">
                                <div id="lesson_data_${lessonId}_start_time_error" class="text-danger error_e"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Time<span class="text-danger">*</span></label>
                                <input type="time" name="lesson_data[${lessonId}][end_time]" class="form-control lesson-end-time" data-lesson-id="${lessonId}">
                                <div id="lesson_data_${lessonId}_end_time_error" class="text-danger error_e"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Departure Airfield (4-letter code)<span class="text-danger">*</span></label>
                                <input type="text" name="lesson_data[${lessonId}][departure_airfield]" class="form-control" maxlength="4">
                                <div id="lesson_data_${lessonId}_departure_airfield_error" class="text-danger error_e"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Destination Airfield (4-letter code)<span class="text-danger">*</span></label>
                                <input type="text" name="lesson_data[${lessonId}][destination_airfield]" class="form-control" maxlength="4">
                                <div id="lesson_data_${lessonId}_destination_airfield_error" class="text-danger error_e"></div>
                            </div>  
                            <div class="col-md-6">
                                <label class="form-label">Instructor License Number</label>
                                <input type="text" name="lesson_data[${lessonId}][instructor_license_number]" class="form-control" id="instructor_license_number" value="" readonly>
                                <div id="lesson_data_${lessonId}_instructor_license_number_error" class="text-danger error_e"></div>
                            </div>
                        </div>
                    </div>
                `;

                $('#editLessonDetailsContainer').append(lessonBox);
            }
        });
    });


    $('#updateTrainingEvent').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ url('/training/update') }}",
            type: "POST",
            data: $("#editTrainingEventForm").serialize(),
            success: function(response) {
                if(response.success){
                    $('#editTrainingEventForm').modal('hide');
                    location.reload();
                }else{
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                // Clear old errors
                $('.error_e').html('');
                $.each(validationErrors, function(key, value) {
                    var formattedKey = key.replace(/\./g, '_') + '_error_up';
                    var errorMsg = '<p>' + value[0] + '</p>';
                    $('#' + formattedKey).html(errorMsg);
                });
            }
        })
    })

    // Delete Event
    $(document).on('click', '.delete-event-icon', function() {
        $('#deleteTrainingEvent').modal('show');
        var eventId = $(this).data('event-id');
        var eventName = $(this).closest('tr').find('.eventName').text();
        $('#append_name').html(eventName);
        $('#eventId').val(eventId);      
    });

    //Unlock the training event grading for editing
    $(document).on('click', '.unlock-event-icon', function () {
        let eventId = $(this).data('event-id');

        if (confirm('Are you sure you want to unlock this training event?')) {
            $.ajax({
                url: '/grading/unlock/' + eventId,
                type: 'POST',
                data: {"_token": "{{ csrf_token() }}"},
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Unlock failed:', xhr.responseText);
                }
            });
        }
    });


    $('#editTrainingEventModal').on('shown.bs.modal', async function () {
        initializeSelect2();
        $('#edit_ou_id').trigger('change');

        await new Promise(resolve => setTimeout(resolve, 300));
        $('#edit_select_user').trigger('change');

        await new Promise(resolve => setTimeout(resolve, 300));
        $('#edit_select_course').trigger('change');
    });


    let lessonIndex = 0;

    function renderLessonBox(lesson, container, prefillData = {}, index = null) {
        const currentIndex = index !== null ? index : lessonIndex++; 
        let lessonId = lesson.id;
        let lessonTitle = lesson.lesson_title;

        // Use prefilled data if available, otherwise fallback to empty strings
        let {
            instructor_id = '',
            resource_id = '',
            lesson_date = '',
            start_time = '',
            end_time = '',
            departure_airfield = '',
            destination_airfield = '',
            instructor_license_number = ''
        } = prefillData;

        let isCurrentUserInstructor = currentUser.role === 'instructor';
        let instructorOptions = instructorsdata.map(i => {
            let selected = '';
            let disabled = '';

            if (isCurrentUserInstructor && i.id == currentUser.id) {
                selected = 'selected';
            } else if (isCurrentUserInstructor) {
                disabled = 'disabled';
            } else if (i.id == instructor_id) {
                selected = 'selected';
            }

            return `<option value="${i.id}" ${selected} ${disabled}>${i.fname} ${i.lname}</option>`;
        }).join('');


        let resourceOptions = resourcesdata.map(r =>
            `<option value="${r.id}" ${r.id == resource_id ? 'selected' : ''}>${r.name}</option>`
        ).join('');

        let lessonBox = `
            <div class="col-12 mb-3 border rounded p-3 lesson-box" data-lesson-id="${currentIndex}">
                <input type="hidden" name="lesson_data[${currentIndex}][lesson_id]" value="${lessonId}">
                <h6 class="fw-bold mb-3">Lesson: ${lessonTitle}</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Instructor<span class="text-danger">*</span></label>
                        <select class="form-select" name="lesson_data[${currentIndex}][instructor_id]" id="lesson_data_${currentIndex}_instructor_listbox"
                                ${isCurrentUserInstructor ? 'disabled' : ''}>
                            <option value="">Select Instructor</option>
                            ${instructorOptions}
                        </select>
                        ${isCurrentUserInstructor ? `<input type="hidden" name="lesson_data[${currentIndex}][instructor_id]" value="${currentUser.id}">` : ''}
                        <div id="lesson_data_${currentIndex}_instructor_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Resource<span class="text-danger">*</span></label>
                        <select class="form-select" name="lesson_data[${currentIndex}][resource_id]">
                            <option value="">Select Resource</option>
                            ${resourceOptions}
                        </select>
                        <div id="lesson_data_${currentIndex}_resource_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Lesson Date<span class="text-danger">*</span></label>
                        <input type="date" name="lesson_data[${currentIndex}][lesson_date]" class="form-control" value="${lesson_date}">
                        <div id="lesson_data_${currentIndex}_lesson_date_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Time<span class="text-danger">*</span></label>
                        <input type="time" name="lesson_data[${currentIndex}][start_time]" class="form-control lesson-start-time" data-lesson-id="${currentIndex}" value="${start_time}">
                        <div id="lesson_data_${currentIndex}_start_time_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Time<span class="text-danger">*</span></label>
                        <input type="time" name="lesson_data[${currentIndex}][end_time]" class="form-control lesson-end-time" data-lesson-id="${currentIndex}" value="${end_time}">
                        <div id="lesson_data_${currentIndex}_end_time_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Departure Airfield (4-letter code)<span class="text-danger">*</span></label>
                        <input type="text" name="lesson_data[${currentIndex}][departure_airfield]" class="form-control" maxlength="4" value="${departure_airfield}">
                        <div id="lesson_data_${currentIndex}_departure_airfield_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Destination Airfield (4-letter code)<span class="text-danger">*</span></label>
                        <input type="text" name="lesson_data[${currentIndex}][destination_airfield]" class="form-control" maxlength="4" value="${destination_airfield}">
                        <div id="lesson_data_${currentIndex}_destination_airfield_error" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instructor License Number</label>
                        <input type="text" name="lesson_data[${currentIndex}][instructor_license_number]" class="form-control" id="instructor_license_number" value="${instructor_license_number}" readonly>
                        <div id="lesson_data_${currentIndex}_instructor_license_number_error" class="text-danger error_e"></div>
                    </div>
                </div>
            </div>
        `;

        container.append(lessonBox);

        // Trigger license number fetch if current user is instructor
        if (isCurrentUserInstructor) {
            const $currentBox = container.find(`.lesson-box[data-lesson-id="${currentIndex}"]`);
            const $licenseInput = $currentBox.find(`input[name="lesson_data[${currentIndex}][instructor_license_number]"]`);

            $.ajax({
                url: `/training/get_instructor_license_no/${currentUser.id}`,
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        $licenseInput.val(response.instructor_licence_number || '');
                        if (!response.instructor_licence_number) {
                            alert("Instructor license number not found.");
                        }
                    } else {
                        $licenseInput.val('');
                        alert("Instructor not found.");
                    }
                },
                error: function () {
                    $licenseInput.val('');
                    console.error("Failed to fetch license number");
                    alert("An error occurred while fetching the license number.");
                }
            });
        }
    }
    
    $(document).on('click', '.end-course-btn', function () {
        let eventId = $(this).data('event-id');

        if (confirm('Are you sure you want to end this course? Once ended, it will be locked for further editing.')) {
            $.ajax({
                url: '/training/end-course/' + eventId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    alert(response.success);
                    location.reload(); // Reload to reflect the locked status
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Something went wrong.');
                }
            });
        }
    });



    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);

});
</script>

@endsection

