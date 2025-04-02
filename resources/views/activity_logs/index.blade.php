@extends('layout.app')
@section('title', 'Activity Logs')
@section('sub-title', 'Activity Logs')

@section('content')
<div class="card pt-4">
    <div class="card-body">
        <div class="table-responsive">
                <table id="activityLogsTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Log Type</th>
                            <th>User Name</th>
                            <th>Description</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
        </div>
    </div>
</div>
@endsection

@section('js_scripts')
    <script>
        $(document).ready(function() {
            $('#activityLogsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `{{ route('logs.data') }}`,
                    type: 'GET',
                    data: function(d) {
                        d.draw = d.draw || 1;
                    }
                },
                columns: [
                    { data: 'log_type' },
                    { data: 'user_name' },
                    { data: 'description' },
                    { data: 'created_at' }
                ],
                columnDefs: [
                    {
                        targets: 3,
                        render: function(data) {
                            return new Date(data).toLocaleString();
                        }
                    }
                ]
            });
        });
    </script>
@endsection
