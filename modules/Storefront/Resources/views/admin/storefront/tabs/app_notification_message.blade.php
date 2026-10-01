<div class="row">
    <div class="col-md-8">
        {{ Form::text('translatable[shared_quote_notification_title]', trans('storefront::attributes.shared_quote_notification_title'), $errors, $settings) }}
        {{ Form::textarea('translatable[shared_quote_notification_description]', trans('storefront::attributes.shared_quote_notification_description'), $errors, $settings, ['rows' => 5]) }}
    </div>
</div>
