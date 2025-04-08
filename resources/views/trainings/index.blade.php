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

@if(checkAllowedModule('groups','group.store')->isNotEmpty())
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
        <tr>
            <td class="eventName">{{ $event->course?->course_name }}</td>
            <td>{{ $event->student?->fname }} {{ $event->student?->lname }}</td>
            <td>{{ $event->instructor?->fname }} {{ $event->instructor?->lname }}</td>
            <td>{{ $event->resource?->name }}</td>
            <td>{{ date('d-m-y', strtotime($event->event_date)) }}</td>
            <td>{{ date('h:i A', strtotime($event->start_time)) }}</td>
            <td>{{ date('h:i A', strtotime($event->end_time)) }}</td>
            <td>
            @if(checkAllowedModule('training','training.show')->isNotEmpty())
                <a href="{{ route('training.show', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Training Event" style="font-size:18px; cursor: pointer;"><i class="fa fa-eye text-danger me-2"></i></a>
            @endif
            @if(checkAllowedModule('training','training.edit')->isNotEmpty())
                <i class="fa fa-edit edit-event-icon me-2" style="font-size:25px; cursor: pointer;"
                data-event-id="{{ encode_id($event->id) }}"></i>
            @endif
            @if(checkAllowedModule('training','training.delete')->isNotEmpty())
                <i class="fa-solid fa-trash delete-event-icon me-2" style="font-size:25px; cursor: pointer;"
                data-event-id="{{ encode_id($event->id) }}" ></i>
            @endif
            @if(checkAllowedModule('training','training.grading-list')->isNotEmpty())
            <a href="{{ route('training.grading-list', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Grading" style="font-size:18px; cursor: pointer;"><i class="fa fa-list text-danger me-2"></i></a>
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Create Training Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" id="trainingEventForm" method="POST" class="row g-3">
                @csrf

                @if(auth()->user()->is_owner == 1)
                <div class="form-group">
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
                <div class="form-group">
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
                <div class="form-group">
                    <label class="form-label">Select Course<span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" id="select_course">
                        <option value="">Select Course</option>
                        @foreach($courses as $val)
                        <option value="{{ $val->id }}">{{ $val->course_name }}</option>
                        @endforeach
                    </select>
                    <div id="course_id_error" class="text-danger error_e"></div>
                </div>
                <!-- Select Lesson -->
                <div class="form-group">
                    <label class="form-label">Select Lesson<span class="text-danger">*</span></label>
                    <select class="form-select" name="lesson_ids[]" id="select_lesson" multiple>
                        <!-- Options will be populated dynamically -->
                    </select>
                    <div id="lesson_ids_error" class="text-danger error_e"></div>
                </div>
                <!-- Event Date-->
                <div class="form-group">
                    <label class="form-label">Event Date<span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" id="event_date">
                    <div id="event_date_error" class="text-danger error_e"></div>
                </div>
                <!-- Start Date & Time -->
                <div class="form-group">
                    <label class="form-label">Start Time<span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" id="start_time">
                    <div id="start_time_error" class="text-danger error_e"></div>
                </div>

                <!-- End Date & Time -->
                <div class="form-group">
                    <label class="form-label">End Time<span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" id="end_time">
                    <div id="end_time_error" class="text-danger error_e"></div>
                </div>

                <!-- Departure Airfield -->
                <div class="form-group">
                    <label class="form-label">Departure Airfield (4-letter code)<span class="text-danger">*</span></label>
                    <input type="text" name="departure_airfield" class="form-control" maxlength="4">
                    <div id="departure_airfield_error" class="text-danger error_e"></div>
                </div>

                <!-- Destination Airfield -->
                <div class="form-group">
                    <label class="form-label">Destination Airfield (4-letter code)<span class="text-danger">*</span></label>
                    <input type="text" name="destination_airfield" class="form-control" maxlength="4">
                    <div id="destination_airfield_error" class="text-danger error_e"></div>
                </div>                
                <!-- Select Group -->
                <!-- <div class="form-group">
                    <label class="form-label">Select Group<span class="text-danger">*</span></label>
                    <select class="form-select" name="group_id" id="select_group">
                        <option value="">Select Group</option>
                        @foreach($groups as $val)
                        <option value="{{ $val->id }}">{{ $val->name }}</option>
                        @endforeach
                    </select>
                    <div id="group_id_error" class="text-danger error_e"></div>
                </div> -->

                <!-- Select Instructor -->
                <div class="form-group">
                    <label class="form-label">Select Instructor<span class="text-danger">*</span></label>
                    <select class="form-select" name="instructor_id" id="select_instructor">
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $val)
                        <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                        @endforeach
                    </select>
                    <div id="instructor_id_error" class="text-danger error_e"></div>
                </div>

                <!-- Select Resource -->
                <div class="form-group">
                    <label class="form-label">Select Resource<span class="text-danger">*</span></label>
                    <select class="form-select" name="resource_id" id="select_resource">
                        <option value="">Select Resource</option>
                        @foreach($resources as $val)
                        <option value="{{ $val->id }}">{{ $val->class }}</option>
                        @endforeach
                    </select>
                    <div id="resource_id_error" class="text-danger error_e"></div>
                </div>

                <!-- Total Time (Calculated) -->
                <div class="form-group">
                    <label class="form-label">Total Time (hh:mm)<span class="text-danger">*</span></label>
                    <input type="text" name="total_time" class="form-control" id="total_time" readonly>
                    <div id="total_time_error" class="text-danger error_e"></div>
                </div>

                <!-- License Number (Extracted from user profile) -->
                <div class="form-group">
                    <label class="form-label">License Number</label>
                    <input type="text" name="licence_number" class="form-control" id="licence_number" value="{{ auth()->user()->licence_number }}" readonly>
                    <div id="licence_number_error" class="text-danger error_e"></div>
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
    <div class="modal-dialog" role="document">
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
                    <div class="form-group">
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
                <div class="form-group">
                    <label class="form-label">Select Student<span class="text-danger">*</span></label>
                    <select class="form-select" name="student_id" id="edit_select_user">
                        <option value="">Select Student</option>
                        @foreach($students as $val)
                        <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                        @endforeach
                    </select>
                    <div id="group_id_error" class="text-danger error_e"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Select Course<span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" id="edit_select_course">
                        <option value="">Select Course</option>
                        @foreach($courses as $val)
                            <option value="{{ $val->id }}">{{ $val->course_name }}</option>
                        @endforeach
                    </select>
                    <div id="course_id_error_up" class="text-danger error_e"></div>
                </div>
                 <!-- Select Lesson -->
                 <div class="form-group">
                    <label class="form-label">Select Lesson<span class="text-danger">*</span></label>
                    <select class="form-select" name="lesson_ids[]" id="edit_select_lesson" multiple>
                        <!-- Options will be populated dynamically -->
                    </select>
                    <div id="lesson_ids_error_up" class="text-danger error_e"></div>
                </div>
                <!-- Event Date-->
                <div class="form-group">
                    <label class="form-label">Event Date<span class="text-danger">*</span></label>
                    <input type="date" name="event_date" class="form-control" id="edit_event_date">
                    <div id="event_date_error" class="text-danger error_e"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time<span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" id="edit_start_time">
                    <div id="start_time_error_up" class="text-danger error_e"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">End Time<span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" id="edit_end_time">
                    <div id="end_time_error_up" class="text-danger error_e"></div>
                </div>
                <!-- <div class="form-group">
                    <label class="form-label">Select Group<span class="text-danger">*</span></label>
                    <select class="form-select" name="group_id" id="edit_select_group">
                        <option value="">Select Group</option>
                        @foreach($groups as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                        @endforeach
                    </select>
                    <div id="group_id_error_up" class="text-danger error_e"></div>
                </div> -->

                <div class="form-group">
                    <label class="form-label">Departure Airfield</label>
                    <input type="text" name="departure_airfield" class="form-control" id="edit_departure_airfield">
                    <div id="departure_airfield_error_up" class="text-danger error_e"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Destination Airfield</label>
                    <input type="text" name="destination_airfield" class="form-control" id="edit_destination_airfield">
                    <div id="destination_airfield_error_up" class="text-danger error_e"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Select Instructor<span class="text-danger">*</span></label>
                    <select class="form-select" name="instructor_id" id="edit_select_instructor">
                        <option value="">Select Instructor</option>
                        @foreach($instructors as $val)
                            <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                        @endforeach
                    </select>
                    <div id="instructor_id_error_up" class="text-danger error_e"></div>
                </div>

                <!-- New Fields -->
                <div class="form-group">
                    <label class="form-label">Select Resource</label>
                    <select class="form-select" name="resource_id" id="edit_select_resource">
                        <option value="">Select Resource</option>
                        @foreach($resources as $val)
                            <option value="{{ $val->id }}">{{ $val->class }}</option>
                        @endforeach
                    </select>
                    <div id="resource_id_error_up" class="text-danger error_e"></div>
                </div>
                <!-- Total Time (Calculated) -->
                <div class="form-group">
                    <label class="form-label">Total Time (hh:mm)<span class="text-danger">*</span></label>
                    <input type="text" name="total_time" class="form-control" id="edit_total_time" readonly>
                    <div id="total_time_error_up" class="text-danger error_e"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Licence Number</label>
                    <input type="text" name="licence_number" class="form-control" id="edit_licence_number" readonly>
                    <div id="licence_number_error_up" class="text-danger error_e" ></div>
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
$(document).ready(function() {
    $('#groupTable').DataTable();


   // Attach event listeners for both create and edit fields
    $('#start_time, #end_time, #edit_start_time, #edit_end_time').on('change', function () {
        calculateTotalTime($(this).closest('form')); // Pass the form context
    });

    function calculateTotalTime(form) {
        let startInput = form.find('input[name="start_time"], input[name="edit_start_time"]');
        let endInput = form.find('input[name="end_time"], input[name="edit_end_time"]');
        let totalTimeInput = form.find('input[name="total_time"], input[name="edit_total_time"]');

        let start = startInput.val();
        let end = endInput.val();

        if (start && end) {
            let [startHours, startMinutes] = start.split(':').map(Number);
            let [endHours, endMinutes] = end.split(':').map(Number);

            let startTotalMinutes = startHours * 60 + startMinutes;
            let endTotalMinutes = endHours * 60 + endMinutes;

            if (endTotalMinutes > startTotalMinutes) {
                let diffMinutes = endTotalMinutes - startTotalMinutes;
                let hours = Math.floor(diffMinutes / 60);
                let minutes = diffMinutes % 60;

                let formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
                
                totalTimeInput.val(formattedTime);
            } else {
                totalTimeInput.val('00:00'); 
            }
        } else {
            totalTimeInput.val('00:00');
        }
    }


    $(document).on('change', '#select_org_unit, #edit_ou_id', function() {
        var ou_id = $(this).val();
// alert(ou_id);
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
                    $.each(response.instructors, function(index, instructor) {
                        var selected = instructor.id == selectedInstructor ? 'selected' : '';
                        instructorOptions += '<option value="' + instructor.id + '" ' + selected + '>' + instructor.fname + ' ' + instructor.lname + '</option>';
                    });
                }
                instructorDropdown.html(instructorOptions); // Update dropdown

                // Populate Resources
                var resourceOptions = '<option value="">Select Resource</option>';
                if (response.resources && response.resources.length > 0) {
                    $.each(response.resources, function(index, resource) {
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
        var licenceNumberField = isEditModal ? $('#edit_licence_number') : $('#licence_number');
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
                            alert('License number not found!');
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


    // $(document).on('change', '#select_course, #edit_select_course', function() {
    //     var courseId = $(this).val();

    //     // Determine which form is being used
    //     var isEditForm = $(this).attr('id') === 'edit_select_course';

    //     // Select the correct dropdown based on the form
    //     var lessonDropdown = isEditForm ? $('#edit_select_lesson') : $('#select_lesson');

    //     $.ajax({
    //         url: '{{ url("/training/get_course_lessons") }}', // Route to fetch lessons
    //         type: 'GET',
    //         data: { course_id: courseId },
    //         success: function(response) {

    //             // Clear and populate Lessons dropdown
    //             lessonDropdown.empty();

    //             if (response.success && response.lessons.length > 0) {
    //                 lessonDropdown.append('<option value="">Select Lesson</option>'); // Default option
    //                 $.each(response.lessons, function(index, lesson) {
    //                     lessonDropdown.append('<option value="' + lesson.id + '">' + lesson.lesson_title + '</option>');
    //                 });
    //             } else {
    //                 alert('No lessons found for the selected course.');
    //                 lessonDropdown.append('<option value="">Select Lesson</option>'); // Keep default option
    //             }
    //         },
    //         error: function(xhr) {
    //             console.error(xhr.responseText);
    //             alert('Error fetching lessons. Please try again.');
    //         }
    //     });
    // });

    $(document).on('change', '#select_course, #edit_select_course', function() {
        var courseId = $(this).val();
// alert(courseId);
        // Determine if it's the edit form
        var isEditForm = $(this).attr('id') === 'edit_select_course';

        // Select the correct dropdown based on the form
        var lessonDropdown = isEditForm ? $('#edit_select_lesson') : $('#select_lesson');

        $.ajax({
            url: '{{ url("/training/get_course_lessons") }}', // Route to fetch lessons
            type: 'GET',
            data: { course_id: courseId },
            success: function(response) {
                lessonDropdown.empty();

                if (response.success && response.lessons.length > 0) {
                    lessonDropdown.append('<option value="">Select Lesson</option>'); // Default option
                    $.each(response.lessons, function(index, lesson) {
                        lessonDropdown.append('<option value="' + lesson.id + '">' + lesson.lesson_title + '</option>');
                    });

                    // Restore previously selected lessons in edit mode
                    if (isEditForm) {
                        setTimeout(function() {
                            var selectedLessons = lessonDropdown.data('selected-lessons') || []; // Get stored lessons
                            lessonDropdown.val(selectedLessons).trigger('change'); // Select the saved lessons
                        }, 100); // Delay to ensure dropdown is populated
                    }
                } else {
                    alert('No lessons found for the selected course.');
                    lessonDropdown.append('<option value="">Select Lesson</option>'); // Keep default option
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Error fetching lessons. Please try again.');
            }
        });
    });

    $("#createTrainingEvent").on('click', function() {
        $(".error_e").html('');
        $("#trainingEventForm")[0].reset();
        $("#createTrainingEventModal").modal('show');

    })

    $("#submitTrainingEvent").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/training/create") }}',
            type: 'POST',
            data: $("#trainingEventForm").serialize(),
            success: function(response) {
                $('#createTrainingEventModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });

    })

    $(document).on('click', '.edit-event-icon', function() {
        $('.error_e').html('');
        var eventId = $(this).data('event-id');

        $.ajax({
            url: "{{ url('/training/edit') }}",
            type: 'GET',
            data: {
                eventId: eventId
            },
            success: function(response) {
                if (response.success) {
                    $('#edit_select_user').val(response.trainingEvent.student_id);
                    $('#edit_select_course').val(response.trainingEvent.course_id);
                    $('#edit_select_group').val(response.trainingEvent.group_id);
                    $('#edit_event_date').val(response.trainingEvent.event_date);
                    $('#edit_select_instructor').val(response.trainingEvent.instructor_id);
                    $('#edit_select_resource').val(response.trainingEvent.resource_id);
                    $('#edit_departure_airfield').val(response.trainingEvent.departure_airfield);
                    $('#edit_destination_airfield').val(response.trainingEvent.destination_airfield);
                    $('#edit_total_time').val(response.trainingEvent.total_time);
                    $('#edit_licence_number').val(response.trainingEvent.licence_number);

                    // Convert datetime format for input[type="datetime-local"]
                    $('#edit_start_time').val(response.trainingEvent.start_time.replace(" ", "T"));
                    $('#edit_end_time').val(response.trainingEvent.end_time.replace(" ", "T"));

                    $('#edit_event_id').val(response.trainingEvent.id);

                    if (response.trainingEvent.ou_id) {
                        $('#edit_ou_id').val(response.trainingEvent.ou_id);
                    }
                    
                    // Store selected lesson IDs
                    let lessonIds = Array.isArray(response.trainingEvent.lesson_ids)
                    ? response.trainingEvent.lesson_ids
                    : JSON.parse(response.trainingEvent.lesson_ids || "[]");

                    $("#edit_select_lesson").data("selected-lessons", lessonIds);

                    // Store selected values before triggering the change event
                    $("#edit_select_user").data("selected-value", response.trainingEvent.student_id);
                    $("#edit_select_instructor").data("selected-value", response.trainingEvent.instructor_id);
                    $("#edit_select_resource").data("selected-value", response.trainingEvent.resource_id);
                    $("#edit_select_course").data("selected-value", response.trainingEvent.course_id);


                    $('#editTrainingEventModal').modal('show');
                } else {
                    console.error("Error: Invalid response format");
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('Something went wrong! Please try again.');
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
                $('#editTrainingEventForm').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
                })
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

    // $(document).on("change", ".select_org_unit", function () {
    //     var ou_id = $(this).val();
    //     var $select_group, $select_instructor;

    //     // Determine if the event was triggered from the main form or the edit modal
    //     if ($(this).attr("id") === "edit_ou_id") {
    //         $select_group = $("#edit_select_group"); // Edit modal group dropdown
    //         $select_instructor = $("#edit_select_instructor"); // Edit modal instructor dropdown
    //     } else {
    //         $select_group = $("#select_group"); // Main form group dropdown
    //         $select_instructor = $("#select_instructor"); // Main form instructor dropdown
    //     }

    //     $.ajax({
    //         url: "/training/get_ou_groups_and_instructors/",
    //         type: "GET",
    //         data: { 'ou_id': ou_id },
    //         dataType: "json",
    //         success: function (response) {
    //             console.log(response);

    //             // Populate Organization Unit Groups
    //             if (response.orgUnitGroups && Array.isArray(response.orgUnitGroups)) {
    //                 var groupOptions = "<option value=''>Select Group</option>";
    //                 response.orgUnitGroups.forEach(function (value) {
    //                     groupOptions += "<option value='" + value.id + "'>" + value.name + "</option>";
    //                 });
    //                 $select_group.html(groupOptions);
    //             } else {
    //                 console.error("Invalid response format for groups:", response);
    //             }

    //             // Populate Instructors
    //             if (response.ouInstructors && Array.isArray(response.ouInstructors)) {
    //                 var instructorOptions = "<option value=''>Select Instructor</option>";
    //                 response.ouInstructors.forEach(function (value) {
    //                     instructorOptions += "<option value='" + value.id + "'>" + value.fname + " " + value.lname + "</option>";
    //                 });
    //                 $select_instructor.html(instructorOptions);
    //             } else {
    //                 console.error("Invalid response format for instructors:", response);
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             console.error(xhr.responseText);
    //         }
    //     });
    // });

    $(document).on("shown.bs.modal", "#editTrainingEventModal", function(event) {
    var ouId = $("#edit_ou_id").val();
    var userId = $("#edit_select_user").val();
    var courseId = $("#edit_select_course").val();
//  alert(ouId);
// alert(userId);
// alert(courseId);
    if (ouId) {
        $("#edit_ou_id").trigger("change"); // Load students, instructors, resources
    }

    if (userId) {
        $("#edit_select_user").trigger("change"); // Load license number and courses
    }

    // Ensure lessons are loaded based on the selected course
    if (courseId) {
        $("#edit_select_course").trigger("change"); // Load lessons
    }
});


    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);

});
</script>

@endsection

