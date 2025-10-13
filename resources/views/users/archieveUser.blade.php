@section('title', 'Unarchive User')
@section('sub-title', 'Unarchive User')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="alertMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

<div class="main_cont_outer">
    <br>
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
            <table class="table table-hover" id="archieve_table">
                <thead>
                    <tr>
                        <th scope="col">User Name</th>
                        <th scope="col">Status</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user as $val)
                     <tr>
                        <td>{{ $val->fname }} {{ $val->lname }}</td>
                        <td>
                            @if($val->is_activated == 1)
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                         <td>
                            <i class="bi bi-check-square archieve_user" data-user_id="{{ $val->id }}" style="cursor:pointer;"></i>
                         </td>
                    </tr>
                    @endforeach
                  
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('js_scripts')
<script>
    $(document).ready(function() { 
        $('#archieve_table').dataTable();


$(document).on('click', '.archieve_user', function() {
    var user_id = $(this).data('user_id');
    if (!confirm('Are you sure you want to unarchive this user ?')) {
        return; // Stop execution if user cancels
    }
   

    // Example: send AJAX request
    $.ajax({
        url: '/unarchive-user',
        type: 'POST',
        data: {
            user_id: user_id,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            location.reload();
        },
        error: function(xhr) {
            alert('Error unarchiving user.');
        }
    });
});


        setTimeout(function() {
            $('#alertMessage').fadeOut('slow');
        }, 2000);
    });
</script>

@endsection