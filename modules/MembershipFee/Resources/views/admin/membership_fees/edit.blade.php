@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('membershipfee::membership_fees.membership_fee')]))
    @slot('subtitle', $membershipFee->campaign_name)

    <li><a href="{{ route('admin.membership_fees.index') }}">{{ trans('membershipfee::membership_fees.membership_fees') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('membershipfee::membership_fees.membership_fee')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.membership_fees.update', $membershipFee) }}" class="form-horizontal" id="flash-sale-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('membershipFee')) !!}
    </form>
@endsection

@include('membershipfee::admin.membership_fees.partials.shortcuts')
