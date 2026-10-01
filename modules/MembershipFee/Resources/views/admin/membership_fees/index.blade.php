@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('membershipfee::membership_fees.membership_fees'))

    <li class="active">{{ trans('membershipfee::membership_fees.membership_fees') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'membership_fees')
    @slot('name', trans('membershipfee::membership_fees.membership_fee'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('membershipfee::attributes.name') }}</th>
                <th>{{ trans('membershipfee::attributes.duration') }}</th> 
                <th>{{ trans('membershipfee::membership_fees.membership_fee') }}</th> 
                <th>{{ trans('admin::admin.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#membership_fees-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'translations.name',orderable: false, },
                { data: 'duration', name: 'duration', searchable: false },
                { data: 'membershipfee', name: 'membershipfee', searchable: false },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
