@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('seller::sellers.sellers'))

    <li class="active">{{ trans('seller::sellers.sellers') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'sellers')
    @slot('name', trans('seller::sellers.seller'))

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')

            <th data-sort>{{ trans('admin::admin.table.id') }}</th>            
            <th>{{ trans('seller::sellers.table.shop_name') }}</th>
            <th>{{ trans('seller::sellers.table.owner_name') }}</th>
            <th>{{ trans('seller::sellers.table.email') }}</th>
            <th>{{ trans('seller::sellers.table.phone') }}</th>
            <th>{{ trans('seller::sellers.table.category') }}</th>
            <th>{{ trans('seller::sellers.table.status') }}</th>
            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#sellers-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'shop_name', name: 'translations.shop_name', orderable: false, defaultContent: '' },
                { data: 'owner_name', name: 'translations.owner_name', orderable: false, defaultContent: '' },
                { data: 'email', name: 'email', orderable: true },
                { data: 'phone', name: 'phone', orderable: true },
                { data: 'category', name: 'translations.category', orderable: false, defaultContent: '' },
                { 
                    data: 'current_status',
                    name: 'current_status',
                    searchable: true,
                    render: function(data) {
                        let statusClass = {
                            'pending': 'warning',
                            'in_review': 'info',
                            'approved': 'success',
                            'rejected': 'danger'
                        };
                        return `<span class="badge badge-${statusClass[data]}">${data.replace('_', ' ').toUpperCase()}</span>`;
                    }
                },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush