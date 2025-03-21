@section('title', 'Book Resource')
@section('sub-title', 'Book Resource')
@extends('layout.app')
@section('content')
@if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
<div class="tab-section">
    <div class="container">
        <div class="">
            <!-- <nav>
                <ul id="myTab" role="tablist" class="nav nav-pills with-arrow flex-column flex-sm-row text-center bg-light border-0 rounded-nav mt-3 mb-4">
                    <li class="nav-item flex-sm-fill">
                        <a class="nav-link font-weight-bold active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-all" aria-selected="true">Pending</a>
                    </li>
                    <li class="nav-item flex-sm-fill">
                        <a class="nav-link font-weight-bold" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-Approved" aria-selected="false">Approved</a>
                    </li>
                    <li class="nav-item flex-sm-fill">
                        <a class="nav-link font-weight-bold" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-Rejected" aria-selected="false">Rejected</a>
                    </li>
                </ul>
            </nav> -->
            <div class="tab-content border bg-light" id="nav-tabContent">
                <!-- Pending Payrolls Tab -->
<div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
<form  method="POST" id="booking_table">
    @csrf
    <table class="table table-hover" id="groupTable">
        <thead>
            <tr>
                <th scope="col">Select</th>
                <th scope="col">Resource</th>
                <th scope="col">Image</th>
                <th scope="col">Book Start from</th>
                <th scope="col">Book Start to</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
        @php
           // Convert selected_course to a key-value array with resource_id as the key
            $selectedResources = collect($selected_course)->keyBy('resource_id');
        @endphp
        @foreach ($courseResources as $key => $val)
      
    @php
        $resourceId = $val['resources_id'] ?? null;
        $isChecked = isset($selectedResources[$resourceId]); // Check if resource_id exists in selected_course
        $startDate = $isChecked ? $selectedResources[$resourceId]['start_date'] : '';
        $endDate = $isChecked ? $selectedResources[$resourceId]['end_date'] : '';
        $status = $isChecked ? $selectedResources[$resourceId]['status'] : '';
       
        if ($status === 0) {
            $statusText = 'Pending';
           
        }
        if ($status === 1) {
            $statusText = 'Approved';
        
        }
        if ($status === 2) {
            $statusText = 'Rejected';
           
        }
        if ($status === "") {
            $statusText = 'Not applied';
        
        }
    @endphp

    <tr>
        <td>
            <input type="checkbox" class="resource-checkbox" name="resources[{{ $key }}][id]" 
                value="{{ $resourceId }}" 
                {{ $isChecked ? 'checked' : '' }}>
        </td>
        <td>
            <input type="hidden" name="resources[{{ $key }}][name]" value="{{ $val['resource']['name'] ?? 'N/A' }}">
            <input type="hidden" name="resources[{{ $key }}][courses_id]" value="{{ $val['courses_id'] ?? 'N/A' }}">
            {{ $val['resource']['name'] ?? 'N/A' }}
        </td>
        <td>
            <img src="{{ asset('storage/resource_logo/' . ($val['resource']['resource_logo'] ?? 'No Image')) }}" alt="img" width="70" height="50">
        </td>
        <td>
            <input type="date" name="resources[{{ $key }}][start_date]" class="form-control resource-input"
                value="{{ $startDate }}">
            <span class="text-danger error-message" id="start_date_error_{{ $key }}"></span>
        </td>
        <td>
            <input type="date" name="resources[{{ $key }}][end_date]" class="form-control resource-input"
                value="{{ $endDate }}">
            <span class="text-danger error-message" id="end_date_error_{{ $key }}"></span>
        </td>
        <td>{{ $statusText }}</td>
    </tr>
@endforeach
        </tbody>
    </table>
    <button type="submit" id="booking" class="btn btn-primary">Submit</button>
</form>
                </div>
                <!-- Approved Payrolls Tab -->
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <table class="table table-hover" id="groupTable">
        <thead>
            <tr>
               
                <th scope="col">Resource</th>
                <th scope="col">Image</th>
                <th scope="col">Book Start from</th>
                <th scope="col">Book Start to</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
@foreach ($approved_resources as $val) 

  
    <tr>
    
        <td>{{ $val->name}} </td>
        <td>
        <img src="{{ asset('storage/resource_logo/' . ($val->resource_logo ?? 'No Image')) }}" alt="img" width="70" height="50">
        </td>
        <td>
            <input type="date" name="start_date" class="form-control resource-input"
                value="{{ $val->start_date}}">
        </td>
        <td>
            <input type="date" name="end_date" class="form-control resource-input"
                value="{{ $val->end_date}}">
        </td>
        <td>Approved</td>
    </tr>
@endforeach

        </tbody>
    </table>
                </div>
                <!-- Rejected Payrolls Tab -->
                <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">

    <table class="table table-hover" id="groupTable">
        <thead>
            <tr>
               
                <th scope="col">Resource</th>
                <th scope="col">Image</th>
                <th scope="col">Book Start from</th>
                <th scope="col">Book Start to</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
@foreach ($rejected_resources as $val) 

  
    <tr>
    
        <td>{{ $val->name}} </td>
        <td>
        <img src="{{ asset('storage/resource_logo/' . ($val->resource_logo ?? 'No Image')) }}" alt="img" width="70" height="50">
        </td>
        <td>
            <input type="date" name="start_date" class="form-control resource-input"
                value="{{ $val->start_date}}">
        </td>
        <td>
            <input type="date" name="end_date" class="form-control resource-input"
                value="{{ $val->end_date}}">
        </td>
        <td>Rejected</td>
    </tr>
@endforeach

        </tbody>
    </table>

                </div>
            </div>
        </div>
    </div>
</div>




@endsection

@section('js_scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("#booking").addEventListener("click", function (event) {
        event.preventDefault();

        let valid = true;

        document.querySelectorAll(".resource-checkbox").forEach((checkbox, index) => {
            let row = checkbox.closest("tr");
            let startDateInput = row.querySelector("input[name*='[start_date]']");
            let endDateInput = row.querySelector("input[name*='[end_date]']");
            let startDateError = document.getElementById(`start_date_error_${index}`);
            let endDateError = document.getElementById(`end_date_error_${index}`);

            // Clear previous error messages
            startDateError.innerText = "";
            endDateError.innerText = "";

            if (checkbox.checked) {
                if (!startDateInput.value) {
                    startDateError.innerText = "Start date is required.";
                    valid = false;
                }
                if (!endDateInput.value) {
                    endDateError.innerText = "End date is required.";
                    valid = false;
                }
                if (startDateInput.value && endDateInput.value && startDateInput.value > endDateInput.value) {
                    endDateError.innerText = "End date must be after or equal to start date.";
                    valid = false;
                }
            }
        });

        if (valid) {
            var formData = new FormData($('#booking_table')[0]);

            $.ajax({
                url: "{{ url('booking/store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response.suceess);
                    if(response.suceess)
                        {
                         location.reload();
                        }
                },
                error: function(xhr) {
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function (key, value) {
                        let field = key.split('.').pop(); // Extract field name
                        let index = key.match(/\d+/)[0]; // Extract index number
                        let errorElement = document.getElementById(`${field}_error_${index}`);
                        if (errorElement) {
                            errorElement.innerText = value;
                        }
                    });
                }
            });
        }
    });
});

</script>
@endsection