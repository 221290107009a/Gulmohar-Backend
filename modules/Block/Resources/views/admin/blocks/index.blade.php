@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('block::blocks.blocks'))

    <li class="active">{{ trans('block::blocks.blocks') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'blocks')
    @slot('name', trans('block::blocks.block'))

    @slot('thead')
        <tr>
            @include('admin::partials.table.select_all')

            <th data-sort>{{ trans('admin::admin.table.id') }}</th>            
            <th>{{ trans('block::blocks.table.name') }}</th>
            <th>{{ trans('admin::admin.table.status') }}</th>
            <th data-sort>{{ trans('admin::admin.table.created') }}</th>
        </tr>
    @endslot
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#blocks-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'translations.name', orderable: false, defaultContent: '' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
