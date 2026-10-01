<div class="row">
    <div class="col-md-8">
        {{ Form::textarea('translatable[app_online_store_message]', trans('storefront::attributes.app_online_store_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::checkbox('app_online_store_message_enabled', trans('storefront::attributes.status'), trans('storefront::storefront.form.enable_app_online_store_message_section'), $errors, $settings) }}
    </div>
</div>
