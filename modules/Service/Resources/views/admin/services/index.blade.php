@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('service::services.services'))

    <li class="active">{{ trans('service::services.services') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'services')
    @slot('name', trans('service::services.service'))

        @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')
                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('footer::footers.table.logo') }}</th>
                <th>{{ trans('service::attributes.name') }}</th>                
                <th>{{ trans('service::attributes.position') }}</th>                
                <th>App Status</th>
                <th>Web Status</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#services-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'logo', orderable: false, searchable: false, width: '10%' },
                { data: 'name', name: 'translations.name', orderable: false, defaultContent: '' },                
                { data: 'position' },                
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'status_web', name: 'is_active_web', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
