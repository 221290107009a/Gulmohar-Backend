@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title',trans('user::sidebar.reffered_users_list'))

    <li class="active">{{ trans('user::sidebar.reffered_users_list') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body index-table" id="referral_usages-table">
            @component('admin::components.table')
                @slot('thead')
                    <tr>
                        <th>{{ trans('admin::admin.table.id') }}</th>                        
                        <th>User Id</th>
                        <th>Referral Type</th>
                        <th>Referred By</th>
                        <th>{{ trans('user::attributes.users.full_name') }}</th>
                        <th>{{ trans('user::attributes.users.email') }}</th>
                        <th>{{ trans('user::attributes.users.dob') }}</th>
                        <th>{{ trans('user::attributes.users.gender') }}</th>
                        <th data-sort>{{ trans('admin::admin.table.created') }}</th>
                    </tr>
                @endslot
            @endcomponent
        </div>
    </div>
@endsection


@push('scripts')
    <script type="module">
        DataTable.setRoutes('#referral_usages-table .table', {
            table: '{{ "admin.reffered_users_list.table" }}',
            show: '{{ "admin.reffered_users_list.show" }}',
        });
        new DataTable('#referral_usages-table .table', {
            columns: [
                { data: 'id', width: '5%' },
                { data: 'user_id', width: '5%' },
                { data: 'referral_share_type', width: '10%' },
                { data: 'referred_by' },
                { data: 'referred_fullname' },
                { data: 'email' },
                { data: 'dob' },
                { data: 'gender', name: 'referredUser.gender', render: function(data, type, row) {
                    if (data) {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                    return '';
                }},
                { data: 'created', name: 'created_at' },
            ]
        });
    </script>
@endpush

