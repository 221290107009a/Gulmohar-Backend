@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', 'User Report')

    <li class="active">User Report</li>
@endcomponent

@section('content')
    <style>
        .report-result .table {
            table-layout: auto;
            width: 100%;
            background: #fff;
        }
        .report-result .table th, .report-result .table td {
            vertical-align: middle !important;
            padding: 12px 15px !important;
            font-size: 13px;
            height: auto !important;
            word-wrap: break-word;
            word-break: normal;
        }
        .col-name, .col-address, .col-vihar { 
            white-space: normal !important; 
        }
        .col-id, .col-email, .col-state, .col-status, .col-date { 
            white-space: nowrap !important; 
        }
        .status-pill {
            padding: 4px 12px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            text-align: center;
        }
        .status-subscribed {
            background: #bff2e8ff;
            color: #47e05eff;
        }
        .status-unsubscribed {
            background: #fff7e6;
            color: #d48806;
        }

        .col-id a, .col-vihar a {
            color: #3c8dbc;
            font-weight: 600;
            text-decoration: underline;
        }
        .col-id a:hover, .col-vihar a:hover {
            color: #23527c;
        }
        .col-id { width: 1% !important; min-width: 40px; white-space: nowrap !important; text-align: left; }
        .col-name { min-width: 180px; }
        .col-email { min-width: 240px; }
        .col-address { min-width: 250px; }
        .col-vihar { min-width: 220px; }
        .col-state { min-width: 120px; }
        .col-status { min-width: 100px; }
        .col-date { min-width: 140px; }
    </style>

    <div class="report-wrapper">
        <div class="row">
            {{-- Filter Section --}}
            <div class="col-md-12">
                <div class="filter-report box" style="margin-bottom: 30px;">
                    <div class="box-header">
                        <h5 class="tab-content-title">Filters</h5>
                    </div>

                    <div class="box-body">
                        <form method="GET" action="{{ route('admin.user_reports.index') }}" id="user-report-filter-form">
                            {{-- Row 1 --}}
                            <div class="row" style="margin-bottom: 15px;">
                                <div class="col-md-3">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="customer_type">Customer Type</label>
                                        <select name="customer_type" id="customer_type" class="form-control custom-select-black">
                                            <option value="" disabled {{ !request('customer_type') ? 'selected' : '' }}>Select Customer</option>
                                            <option value="all" {{ request('customer_type') === 'all' ? 'selected' : '' }}>All Customers</option>
                                            <option value="active" {{ request('customer_type') === 'active' ? 'selected' : '' }}>Active Customers Only</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                     <div class="form-group" style="margin-bottom: 0;">
                                        <label for="from">{{ trans('report::admin.filters.date_start') }}</label>
                                        <input type="text" name="from" class="form-control datetime-picker" id="from" value="{{ request('from') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                     <div class="form-group" style="margin-bottom: 0;">
                                        <label for="to">{{ trans('report::admin.filters.date_end') }}</label>
                                        <input type="text" name="to" class="form-control datetime-picker" id="to" value="{{ request('to') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="state">State</label>
                                        <select name="state" id="state" class="form-control custom-select-black">
                                            <option value="" disabled {{ !request('state') ? 'selected' : '' }}>Select State</option>
                                            <option value="all" {{ request('state') === 'all' ? 'selected' : '' }}>All States</option>
                                            @foreach($states as $state)
                                                <option value="{{ $state }}" {{ request('state') === $state ? 'selected' : '' }}>
                                                    {{ $state }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Row 2 --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="address">Address Status</label>
                                        <select name="address" id="address" class="form-control custom-select-black">
                                            <option value="">Select Address</option>
                                            <option value="all" {{ request('address') === 'all' ? 'selected' : '' }}>All Address</option>
                                            <option value="added" {{ request('address') === 'added' ? 'selected' : '' }}>Address Added</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="buddha_vihar">Buddha Vihar Status</label>
                                        <select name="buddha_vihar" id="buddha_vihar" class="form-control custom-select-black">
                                            <option value="">Select Buddha Vihar</option>
                                            <option value="all" {{ request('buddha_vihar') === 'all' ? 'selected' : '' }}>All Buddha Vihar</option>
                                            <option value="added" {{ request('buddha_vihar') === 'added' ? 'selected' : '' }}>Buddha Vihar Added</option>
                                        </select>
                                    </div>  
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group" style="margin-top: 25px; margin-bottom: 0;">
                                        <div class="row">
                                            <div class="col-md-7" style="padding-right: 5px;">
                                                <button type="button" class="btn btn-default btn-block" id="manual-filter-btn">
                                                    Filter
                                                </button>
                                            </div>
                                            <div class="col-md-5" style="padding-left: 5px;">
                                                <button type="button" class="btn btn-default btn-block" id="reset-filter-btn">
                                                    Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Report Index/List (Conditionally Shown) --}}
            @if(isset($showReport) && $showReport)
                <div class="col-md-12">
                    <div class="report-result box">
                        <div class="box-header">
                            <h5>
                                User Registration List
                                @if(request('customer_type') === 'active')
                                    (Active Only)
                                @endif
                                @if(request('state') && request('state') !== 'all')
                                    - State: {{ request('state') }}
                                @endif
                                @if(request('buddha_vihar') && request('buddha_vihar') !== 'all')
                                    - Buddha Vihar: {{ request('buddha_vihar') }}
                                @endif
                            </h5>
                        </div>

                        <div class="box-body">
                            <div class="table-responsive anchor-table">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="col-id">ID</th>
                                            <th class="col-name">Name</th>
                                            <th class="col-email">Email</th>
                                            <th class="col-address">Address</th>
                                            <th class="col-vihar">Buddha Vihar</th>
                                            <th class="col-state">State</th>
                                            <th class="col-status">Subscribe</th>
                                            <th class="col-date">Registered Date</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($report as $user)
                                            <tr>
                                                <td class="col-id">
                                                    <a href="{{ route('admin.member.edit', $user->id) }}" target="_blank">
                                                        {{ $user->id }}
                                                    </a>
                                                </td>
                                                <td class="col-name" title="{{ $user->fullname ?: (trim($user->first_name . ' ' . $user->last_name) ?: 'N/A') }}">
                                                    {{ $user->fullname ?: (trim($user->first_name . ' ' . $user->last_name) ?: 'N/A') }}
                                                </td>
                                                <td class="col-email">
                                                    {{ $user->email }}
                                                </td>
                                                <td class="col-address" title="{{ $user->address ?? 'N/A' }}">
                                                    {{ $user->address ?? 'N/A' }}
                                                </td>
                                                <td class="col-vihar" title="{{ $user->buddha_vihar_names ?? 'N/A' }}">
                                                    @if($user->buddha_vihar_names)
                                                        @php
                                                            $vihar_names = explode(', ', $user->buddha_vihar_names);
                                                            $vihar_ids = explode(',', $user->buddha_vihar_ids);
                                                        @endphp
                                                        @foreach($vihar_names as $index => $vihar_name)
                                                            @if(isset($vihar_ids[$index]))
                                                                <div style="display: flex; gap: 5px; margin-bottom: 8px; align-items: flex-start;">
                                                                    @if(count($vihar_names) > 1)
                                                                        <span style="flex-shrink: 0; line-height: 1.6;">{{ $index + 1 }}.</span>
                                                                    @endif
                                                                    <a href="{{ route('admin.buddha_vihars.edit', $vihar_ids[$index]) }}" target="_blank" style="display: inline-block; line-height: 1.6;">
                                                                        {{ $vihar_name }}
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        {{ $user->buddha_vihar_name ?? 'N/A' }}
                                                    @endif
                                                </td>
                                                <td class="col-state">{{ $user->state ?? 'N/A' }}</td>
                                                <td class="col-status text-center">
                                                    @if($user->is_subscribe)
                                                        <span class="status-pill status-subscribed">Subscribed</span>
                                                    @else
                                                        <span class="status-pill status-unsubscribed">Unsubscribed</span>
                                                    @endif
                                                </td>
                                                <td class="col-date">{{ \Illuminate\Support\Carbon::parse($user->created_at)->toFormattedDateString() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="empty" colspan="8">No customers found matching these criteria!</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Grand Total Section --}}
                            <div class="report-summary" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-top: 2px solid #eee;">
                                <div class="row">
                                    <div class="col-md-12 text-right">
                                        <h4 style="margin: 0; font-size: 16px;">
                                            <strong>Total Users: </strong>
                                            <span class="text-primary" style="font-size: 18px;">{{ $grandTotal ?? 0 }}</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="pull-right" style="margin-top: 15px;">
                                {!! $report->links('pagination::bootstrap-4') !!}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header">
                            <h5>User Report List</h5>
                        </div>
                        <div class="box-body text-center" style="padding: 50px;">
                            <p class="text-muted">Please select filters from above and click Filter to see the report.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Report/Resources/assets/admin/sass/main.scss',
        'modules/Report/Resources/assets/admin/js/main.js'
    ])

    <script type="module">
        $(document).ready(function() {
            $('.filter-report').css('margin-bottom', '25px');


            setTimeout(() => {
                const fromEl = document.querySelector('#from');
                const toEl = document.querySelector('#to');
                
                if (fromEl && toEl && fromEl._flatpickr && toEl._flatpickr) {
                    const fromInstance = fromEl._flatpickr;
                    const toInstance = toEl._flatpickr;

                    fromInstance.set('onChange', (selectedDates) => {
                        if (selectedDates.length > 0) {
                            let nextDay = new Date(selectedDates[0]);
                            nextDay.setDate(nextDay.getDate() + 1);
                            toInstance.set('minDate', nextDay);
                            if (toInstance.selectedDates.length === 0 || toInstance.selectedDates[0] < nextDay) {
                                toInstance.setDate(nextDay);
                            }
                        } else {
                            toInstance.set('minDate', null);
                        }
                    });

                    toInstance.set('onChange', (selectedDates) => {
                        if (selectedDates.length > 0) {
                            let prevDay = new Date(selectedDates[0]);
                            prevDay.setDate(prevDay.getDate() - 1);
                            fromInstance.set('maxDate', prevDay);
                        } else {
                            fromInstance.set('maxDate', null);
                        }
                    });

                    @if(request('from'))
                        fromInstance.setDate("{{ request('from') }}");
                    @endif

                    @if(request('to'))
                        toInstance.setDate("{{ request('to') }}");
                    @endif

                    if (window.location.search === "" || window.location.search === "?") {
                        fromInstance.clear();
                        toInstance.clear();
                    }
                }
            }, 500);

            $('#manual-filter-btn').on('click', function() {
                const form = $('#user-report-filter-form');
                const baseUrl = "{{ route('admin.user_reports.index') }}";
                const params = form.find(':input').filter(function() {
                    return this.value !== '' && this.name !== '';
                }).serialize();
                
                window.location.href = baseUrl + '?' + params;
            });

            $('#reset-filter-btn').on('click', function() {
                const form = $('#user-report-filter-form');
                form.find('input').val('');
                form.find('select').each(function() {
                    $(this).prop('selectedIndex', 0);
                });
                window.location.href = "{{ route('admin.user_reports.index') }}";
            });
        });
    </script>
@endpush
