@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.edit', ['resource' => trans('emailtemplate::emailtemplates.emailtemplate')]))
    @slot('subtitle', $newsLetter->title)

    <li><a href="{{ route('admin.emailtemplates.index') }}">{{ trans('emailtemplate::emailtemplates.emailtemplates') }}</a></li>
    <li class="active">{{ trans('admin::resource.edit', ['resource' => trans('emailtemplate::emailtemplates.emailtemplate')]) }}</li>
@endcomponent

@section('content')
    <form method="POST" action="{{ route('admin.emailtemplates.update', $newsLetter) }}" class="form-horizontal" id="emailtemplate-edit-form" novalidate>
        {{ csrf_field() }}
        {{ method_field('put') }}
        
    </form>
@endsection