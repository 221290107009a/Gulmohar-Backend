@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'Feedbacks')

    <li class="active">Feedbacks</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body index-table" id="feedback-table">
            @component('admin::components.table')
                @slot('thead')
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>User Name</th>
                        <th>Subject</th>
                        <th>App Version</th>
                        <th>OS Version</th>
                        <th>Status</th>
                        <th data-sort>Created</th>
                    </tr>
                @endslot
            @endcomponent
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        DataTable.setRoutes('#feedback-table .table', {
            table: '{{ "admin.feedback.table" }}',
            show: '{{ "admin.feedback.show" }}',
        });

        new DataTable('#feedback-table .table', {
            columns: [
                { data: 'id', width: '5%' },
                { data: 'user_id', width: '10%' },
                { data: 'user.fullname', defaultContent: 'N/A' },
                { data: 'subject' },
                { data: 'app_version', defaultContent: 'N/A' },
                { data: 'os_version', defaultContent: 'N/A' },
                { data: 'status', name: 'status', orderable: false, searchable: false, width: '10%' },
                { data: 'created', name: 'created_at' },
            ]
        });
    </script>
@endpush
