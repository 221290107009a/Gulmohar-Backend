@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title',trans('user::members.member'))

    <li class="active">{{ trans('user::members.member') }}</li>
@endcomponent
    
@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'member')
    @slot('name',"Member")

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')

            <th>{{ trans('admin::admin.table.id') }}</th>
            <th>{{ trans('user::attributes.users.full_name') }}</th>
            <th>{{ trans('user::attributes.users.email') }}</th>
            <th>{{ trans('user::attributes.users.dob') }}</th>
            <th>{{ trans('user::attributes.users.gender') }}</th>
            <th>{{ trans('user::attributes.users.is_subscribe') }}</th>
            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#member-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'fullname', name: 'fullname' },
                { data: 'email' },
                { data: 'dob', name: 'dob', render: function(data, type, row) {
                    if (data) {
                        const date = new Date(data);
                        const day = ("0" + date.getDate()).slice(-2);
                        const month = ("0" + (date.getMonth() + 1)).slice(-2);
                        const year = date.getFullYear();
                        return `${day}-${month}-${year}`;
                    }
                    return '';
                }},
                { data: 'gender', name: 'gender', render: function(data, type, row) {
                    if (data) {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                    return '';
                }},
                { data: 'is_subscribe', name: 'is_subscribe', render: function(data, type, row) {
                    if (data == 1) {
                        return '<span class="badge badge-success">Subscribed</span>';
                    } else {
                        return '<span class="badge badge-warning">Unsubscribed</span>';
                    }
                }},
                { data: 'created', name: 'created_at' },
            ]
        });
    </script>
@endpush

