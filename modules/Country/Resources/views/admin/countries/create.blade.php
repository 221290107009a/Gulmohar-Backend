@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => trans('country::countries.country')]))

    <li><a href="{{ route('admin.countries.index') }}">{{ trans('country::countries.countries') }}</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => trans('country::countries.countries')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.countries.store') }}" class="form-horizontal" id="country-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('country')) !!}
    </form>
@endsection

@include('country::admin.countries.partials.shortcuts')
