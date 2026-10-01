@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('admin::resource.create', ['resource' => "Member"]))

    <li><a href="{{ route('admin.member.index') }}">member</a></li>
    <li class="active">{{ trans('admin::resource.create', ['resource' => "member"]) }}</li>
@endcomponent
@section('content')
    <form method="POST" action="{{ route('admin.member.store') }}" class="form-horizontal" id="user-create-form" novalidate>
        {{ csrf_field() }}

        {!! $tabs->render(compact('user')) !!}
    </form>
@endsection

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
        'modules/User/Resources/assets/admin/sass/main.scss',
        'modules/User/Resources/assets/admin/js/main.js'
    ])
@endpush

