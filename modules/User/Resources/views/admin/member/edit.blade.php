@extends('admin::layout')
<?php
$arrUser = $user->toArray();
?>
@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => "member"]))
    @slot('subtitle', $arrUser['fullname'])

    <li><a href="{{ route('admin.member.index') }}">Member</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => "member"]) }}</li>
@endcomponent

@section('content')
    {{--
    @if(!empty($user->subscriptionHistories($user->id)->toArray()))
    <div class="box box-primary">
        <div class="box-body index-table" id="subscription_histories-table">
            <div class="row">
                <div class="col-md-12">
                    <div class="member clearfix">
                        <div class="table-responsive">
                            <h4 class="p-b-10">{{ trans('user::subscription_histories.details') }}</h4>
                            <table class="table">
                                <tr>
                                    <td width='30%'>{{ trans('user::attributes.users.is_subscribe') }}</td>
                                    <td class="{{ $user->is_subscribe == 1 ? 'text-success' : 'text-danger' }}">{{ $user->is_subscribe == 1 ? 'Subscribed' : 'Unsubscribed' }}</td>
                                </tr>
                                <tr>
                                    <td width='30%'>{{ trans('membershipfee::attributes.membershipfee') }}</td>
                                    <td>{{ $user->subscriptionDetail($user->id)->subscription_fees }}</td>
                                </tr>
                                <tr>
                                    <td width='30%'>{{ trans('membershipfee::attributes.name') }}</td>
                                    <td>{{ $user->subscriptionDetail($user->id)->subscription_fees_name }}</td>
                                </tr>
                                <tr>
                                    <td width='30%'>{{ trans('membershipfee::attributes.duration') }}</td>
                                    <td>{{ $user->subscriptionDetail($user->id)->subscription_fees_duration }}</td>
                                </tr>
                                <tr>
                                    <td width='30%'>{{ trans('user::attributes.users.start_date') }}</td>
                                    @if($user->is_subscribe == 1)
                                    <td>{{ date('d M Y, g:i A', strtotime($user->subscriptionDetail($user->id)->start_date)) }}</td>
                                    @else
                                    <td></td>
                                    @endif
                                </tr>
                                <tr>
                                    <td width='30%'>{{ trans('user::attributes.users.end_date') }}</td>
                                    @if($user->is_subscribe == 1)
                                    <td>{{ date('d M Y, g:i A', strtotime($user->subscriptionDetail($user->id)->end_date)) }}</td>
                                    @else
                                    <td></td>
                                    @endif
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($user->subscriptionHistories($user->id)->toArray()))
    <div class="box box-primary">
        <div class="box-body index-table" id="subscription_histories-table">
            <div class="row">
                <div class="col-md-12">
                    <div class="member clearfix">
                        <div class="table-responsive">
                            <h4 class="p-b-10">{{ trans('user::subscription_histories.subscription_history') }}</h4>
                            <table class="table">
                                <thead>
                                    <tr role="row">
                                        <th>{{ trans('admin::admin.table.id') }}</th>
                                        <th>{{ trans('membershipfee::attributes.membershipfee') }}</th>
                                        <th>{{ trans('membershipfee::attributes.name') }}</th>
                                        <th>{{ trans('membershipfee::attributes.duration') }}</th>
                                        <th>{{ trans('user::attributes.users.start_date') }}</th>
                                        <th>{{ trans('user::attributes.users.end_date') }}</th>
                                    </tr>
                                </thead>
                                @foreach ($user->subscriptionHistories($user->id) as $history )
                                    <tr>
                                        <td> {{ $history->id }}</td>
                                        <td> {{ $history->subscription_fees }}</td>
                                        <td> {{ $history->subscription_fees_name }}</td>
                                        <td> {{ $history->subscription_fees_duration }}</td>
                                        <td>{{ $history->start_date }} </td>
                                        <td>{{ $history->end_date }} </td>
                                    </tr>
                                @endforeach                                
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    --}}
    <form method="POST" action="{{ route('admin.member.update', $user) }}" class="form-horizontal" id="members_regi" enctype="multipart/form-data" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}
        
        {!! $tabs->render(compact('user')) !!}
    </form>
@endsection

@include('user::admin.users.partials.shortcuts')

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
        'modules/User/Resources/assets/admin/sass/main.scss',
        'modules/User/Resources/assets/admin/js/main.js'
    ])
@endpush
