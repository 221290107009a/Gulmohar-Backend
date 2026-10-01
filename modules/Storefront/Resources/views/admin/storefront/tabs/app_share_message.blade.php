<div class="row">
    <div class="col-md-8">
        {{ Form::textarea('translatable[app_share_message]', trans('storefront::attributes.app_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[quote_share_message]', trans('storefront::attributes.quote_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[community_share_message]', trans('storefront::attributes.community_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[community_post_share_message]', trans('storefront::attributes.community_post_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[community_card_share_message]', trans('storefront::attributes.community_card_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[program_share_message]', trans('storefront::attributes.program_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[program_qr_share_message]', trans('storefront::attributes.program_qr_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[program_registration_post_message]', trans('storefront::attributes.program_registration_post_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[business_share_message]', trans('storefront::attributes.business_share_message'), $errors, $settings, ['rows' => 5]) }}
        {{ Form::textarea('translatable[political_template_message]', trans('storefront::attributes.political_template_message'), $errors, $settings, ['rows' => 5]) }}
    </div>
</div>
