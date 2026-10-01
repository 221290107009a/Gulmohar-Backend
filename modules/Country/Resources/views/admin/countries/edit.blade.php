@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('country::countries.country')]))
    @slot('subtitle', $country->title)

    <li><a href="{{ route('admin.countries.index') }}">{{ trans('country::countries.countries') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('country::countries.countries')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.countries.update', $country) }}" class="form-horizontal" id="country-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}

        {!! $tabs->render(compact('country')) !!}
    </form>
@endsection

@include('country::admin.countries.partials.shortcuts')
