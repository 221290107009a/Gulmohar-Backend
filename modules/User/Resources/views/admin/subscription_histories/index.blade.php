@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title',trans('user::sidebar.subscription_histories'))

    <li class="active">{{ trans('user::sidebar.subscription_histories') }}</li>
@endcomponent

@section('content')
    <div class="box box-primary">
        <div class="box-body index-table" id="subscription_histories-table">
            @component('admin::components.table')
                @slot('thead')
                    <tr>
                        <th>{{ trans('admin::admin.table.id') }}</th>
                        <th>{{ trans('user::users.user_id') }}</th>
                        <th>{{ trans('membershipfee::attributes.membershipfee') }}</th>
                        <th>{{ trans('membershipfee::attributes.name') }}</th>
                        <th>{{ trans('membershipfee::attributes.duration') }}</th>
                        <th>{{ trans('user::attributes.users.start_date') }}</th>
                        <th>{{ trans('user::attributes.users.end_date') }}</th>
                        <th>{{ trans('admin::admin.table.status') }}</th>
                    </tr>
                @endslot
            @endcomponent
        </div>
    </div>
@endsection


@push('scripts')
    <script type="module">
        DataTable.setRoutes('#subscription_histories-table .table', {
            table: '{{ "admin.subscription_histories.table" }}',
            show: '{{ "admin.subscription_histories.show" }}',
        });

        function formatDate(dateString) {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = String(date.getFullYear()).slice();
            return `${day}-${month}-${year}`;
        }

        new DataTable('#subscription_histories-table .table', {
            columns: [
                { data: 'id' },
                { data: 'user_id' },
                { data: 'subscription_fees' },
                { data: 'subscription_fees_name' },
                { data: 'subscription_fees_duration' },
                {
                    data: 'start_date',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                {
                    data: 'end_date',
                    render: function(data) {
                        return formatDate(data);
                    }
                },
                { data: 'status', name:'status' },
            ]
        });
    </script>
@endpush

