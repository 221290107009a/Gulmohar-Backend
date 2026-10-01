<div class="row">
    <div class="col-md-8">
        {{ Form::text('name', trans('service::attributes.name'), $errors, $service, ['required' => true]) }}
        {{ Form::text('link_url', trans('service::attributes.link_url'), $errors, $service, ['required' => true]) }}
        {{ Form::text('webpage_url', trans('service::attributes.webpage_url'), $errors, $service, ['required' => false]) }}
        {{ Form::text('gradient_colors', trans('service::attributes.gradient_colors'), $errors, $service, ['required' => true]) }}
        {{ Form::number('position', trans('service::attributes.position'), $errors, $service, ['required' => false]) }}
        {{ Form::wysiwyg('description', trans('service::attributes.description'), $errors, $service) }}
        {{ Form::checkbox('is_active', 'Active Status App', trans('service::services.form.enable_the_service'), $errors, $service) }}
        {{ Form::checkbox('is_active_web', 'Active Status Web', 'Enable the service for web', $errors, $service) }}
    </div>
</div>
