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
            <div class="tab-content border bg-light" id="nav-tabContent">
                <!-- Pending Payrolls Tab -->
                <div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <form method="POST" id="booking_table">
                        @csrf
                        <table class="table table-hover" id="bookingRequest_table">
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
                                $isChecked = isset($selectedResources[$resourceId]); 
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
                                    <td><input type="checkbox" class="resource-checkbox" name="resources[{{ $key }}][id]" value="{{      $resourceId }}" {{ $isChecked ? 'checked' : '' }}>
                                    </td>
                                    <td><input type="hidden" name="resources[{{ $key }}][name]" value="{{ $val['resource']['name'] ?? 'N/A' }}">
                                        <input type="hidden" name="resources[{{ $key }}][courses_id]"
                                            value="{{ $val['courses_id'] ?? 'N/A' }}"> {{ $val['resource']['name'] ?? 'N/A' }}
                                    </td>
                                    <td><img src="{{ asset('storage/resource_logo/' . ($val['resource']['resource_logo'] ?? 'No Image')) }}"
                                            alt="img" width="70" height="50">
                                    </td>
                                    <td>
                                        <input type="date" name="resources[{{ $key }}][start_date]"
                                            class="form-control resource-input" value="{{ $startDate }}">
                                        <span class="text-danger error-message" id="start_date_error_{{ $key }}"></span>
                                    </td>
                                    <td>
                                        <input type="date" name="resources[{{ $key }}][end_date]"
                                            class="form-control resource-input" value="{{ $endDate }}">
                                        <span class="text-danger error-message" id="end_date_error_{{ $key }}"></span>
                                    </td>
                                    <td>{{ $statusText }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($courseResources) > 0)
                            <button type="submit" id="booking" class="btn btn-primary">Submit</button>
                        @endif

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_scripts')
<script>
        $(document).ready(function() {
    $('#bookingRequest_table').DataTable({ 
      "columnDefs": [
        { "targets": [1], "orderable": false } // Set index 1 (Action column) as non-sortable
      ],
      searching: true,
      language: {
        emptyTable: "No records found"
      },
      "drawCallback": function() { 
        $('[data-toggle="tooltip"]').tooltip();
      }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelector("#booking").addEventListener("click", function(event) {
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
                if (startDateInput.value && endDateInput.value && startDateInput.value >
                    endDateInput.value) {
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
                    if (response.suceess) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        let field = key.split('.').pop(); // Extract field name
                        let index = key.match(/\d+/)[0]; // Extract index number
                        let errorElement = document.getElementById(
                            `${field}_error_${index}`);
                        if (errorElement) {
                            errorElement.innerText = value;
                        }
                    });
                }
            });
        }
    });
});

setTimeout(function() {
  $('#successMessage').fadeOut('fast');
}, 1000);

</script>
@endsection