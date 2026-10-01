<div class="row">
    <div class="col-md-8">
        <div class="box-content clearfix">
            <h4 class="section-title">{{ trans('setting::settings.form.web_maintenance_settings') }}</h4>
            {{ Form::text('web_maintenance_start_time', trans('setting::attributes.web_maintenance_start_time'), $errors, $settings, ['class' => 'datetime-picker', 'data-default-date' => setting('web_maintenance_start_time'), 'data-time' => true]) }}
            {{ Form::text('web_maintenance_end_time', trans('setting::attributes.web_maintenance_end_time'), $errors, $settings, ['class' => 'datetime-picker', 'data-default-date' => setting('web_maintenance_end_time'), 'data-time' => true]) }}
            {{ Form::textarea('web_maintenance_text', trans('setting::attributes.web_maintenance_text'), $errors, $settings) }}
            {{ Form::checkbox('web_maintenance_status', trans('setting::attributes.web_maintenance_status'), trans('setting::settings.form.web_maintenance_status'), $errors, $settings) }}
        </div>

        <div class="box-content clearfix">
            <h4 class="section-title">{{ trans('setting::settings.form.app_maintenance_settings') }}</h4>
            {{ Form::text('app_maintenance_start_time', trans('setting::attributes.app_maintenance_start_time'), $errors, $settings, ['class' => 'datetime-picker', 'data-default-date' => setting('app_maintenance_start_time'), 'data-time' => true]) }}
            {{ Form::text('app_maintenance_end_time', trans('setting::attributes.app_maintenance_end_time'), $errors, $settings, ['class' => 'datetime-picker', 'data-default-date' => setting('app_maintenance_end_time'), 'data-time' => true]) }}
            {{ Form::textarea('app_maintenance_text', trans('setting::attributes.app_maintenance_text'), $errors, $settings) }}
            {{ Form::checkbox('app_maintenance_status', trans('setting::attributes.app_maintenance_status'), trans('setting::settings.form.app_maintenance_status'), $errors, $settings) }}
        </div>
    </div>
</div>
