@section('title', 'Approval Resource Request')
@section('sub-title', 'Approval Resource Request')
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
                        <table class="table table-hover" id="approvalTable">
                            <thead>
                                <tr>
                                    <th scope="col">Resource</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Book Start from</th>
                                    <th scope="col">Book Start to</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action </th>
                                </tr>
                              
                            </thead>
                            <tbody>
                            @foreach ($pending_approval as $row) 
                            <tr>
                                <td>{{ $row->resource->name }}</td>
                                <td>{{ $row->user->fname }} {{ $row->user->lname }}</td>
                                <td>{{ $row->start_date }}</td>
                                <td>{{ $row->end_date }}</td>
                                <td>
                                    @if($row->status == 0)
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($row->status == 1)
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($row->status == 2)
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->status == 0)
                                        <button class="btn btn-success approve-btn" data-book-id="{{ $row->id }}">Approve</button>
                                        <button class="btn btn-danger reject-btn" data-book-id="{{ $row->id }}">Reject</button>
                                    @endif
                                </td>
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
    $(document).ready(function() {
    $('#approvalTable').DataTable({ 
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

$(document).on("click", ".approve-btn", function() {
    var booking_id = $(this).data("book-id");

    if (!booking_id) {
        alert("Invalid booking ID");
        return;
    }

    var vdata = {
        booking_id: booking_id,
        _token: "{{ csrf_token() }}" // Include CSRF token for Laravel
    };

    $.ajax({
        url: "{{ url('resource/approve/request') }}",
        type: "POST",
        data: vdata,
        success: function(data) {
            if(data.success){
           location.reload(); 
            }
           
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert("An error occurred while processing the request.");
        }
    });
});

// Reject Approval

$(document).on("click", ".reject-btn", function() {
    var booking_id = $(this).data("book-id");

    if (!booking_id) {
        alert("Invalid booking ID");
        return;
    }

    var vdata = {
        booking_id: booking_id,
        _token: "{{ csrf_token() }}" // Include CSRF token for Laravel
    };

    $.ajax({
        url: "{{ url('resource/reject/request') }}",
        type: "POST",
        data: vdata,
        success: function(data) {
            if(data.success){
           location.reload(); 
            }
           
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert("An error occurred while processing the request.");
        }
    });
});

setTimeout(function() {
  $('#successMessage').fadeOut('fast');
}, 1000);

</script>
@endsection