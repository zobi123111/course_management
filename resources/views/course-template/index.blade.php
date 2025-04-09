@section('title', 'Course Template')
@section('sub-title', 'Course Template')
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
        <a href="{{ route('course-template.create') }}" class="btn btn-primary create-button" id="createCourseTemplate">Create Course Template</a>
</div>
@endif
<br>

<div class="card pt-4">
    <div class="card-body">
        <table class="table table-hover" id="courseTemplateTable">
        <thead>
            <tr>
                <th scope="col">Course Template Name</th>
                <th scope="col">Description</th>
                <th scope="col">CBTA</th>
                <th scope="col">Manual Time Entry</th>
                @if(checkAllowedModule('course-template','course-template.edit')->isNotEmpty() || checkAllowedModule('course-template','course-template.delete')->isNotEmpty())
                <th scope="col">Action</th>
                @endif
                <!-- @if(checkAllowedModule('groups','group.edit')->isNotEmpty()) 
                <th scope="col">Edit</th>
                @endif
                @if(checkAllowedModule('groups','group.delete')->isNotEmpty())
                <th scope="col">Delete</th>
                @endif -->
            </tr>
        </thead>
        <tbody>
            @foreach($courseTemplate as $val)
            <tr>
                <td class="Template_name">{{ $val->name }}</td>
                <td>{{ $val->description }}</td>
                <td>{!! $val->enable_cbta == 1 ? '<span style="color: green;">✔</span>' : '<span style="color: red;">❌</span>' !!}</td>
                <td>{!! $val->enable_manual_time_entry == 1 ? '<span style="color: green;">✔</span>' : '<span style="color: red;">❌</span>' !!}</td>
                <td>
                @if(checkAllowedModule('course-template','course-template.edit')->isNotEmpty())
                    <i class="fa fa-edit edit-group-icon" style="font-size:25px; cursor: pointer;"
                    data-group-id="{{ encode_id($val->id) }}"></i>
                @endif
                @if(checkAllowedModule('course-template','course-template.delete')->isNotEmpty())
                    <i class="fa-solid fa-trash delete-group-icon" style="font-size:25px; cursor: pointer;"
                    data-group-id="{{ encode_id($val->id) }}" data-group-name="{{ $val->name }}"></i>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        </table>
    </div>
</div>
<!--End of Group Edit-->

<!-- Delete Group Modal -->
<form action="{{ url('group/delete') }}" id="deleteGroupForm" method="POST">
    @csrf
    <div class="modal fade" id="deleteGroup" tabindex="-1" aria-labelledby="deleteGroupLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGroupLabel">Delete Group</h5>
                    <input type="hidden" name="group_id" id="groupId">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Group "<strong><span id="append_name"></span></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="confirmDeleteGroup" class="btn btn-danger delete_group">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js_scripts')