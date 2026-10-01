@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('membershipfee::membership_fees.membership_fee')]))

    <li><a href="{{ route('admin.membership_fees.index') }}">{{ trans('membershipfee::membership_fees.membership_fees') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('membershipfee::membership_fees.membership_fee')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.membership_fees.store') }}" class="form-horizontal" id="flash-sale-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('membershipFee')) !!}
    </form>
@endsection

@include('membershipfee::admin.membership_fees.partials.shortcuts')


