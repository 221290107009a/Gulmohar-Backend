<div class="row">
    <div class="col-md-8">
        {{ Form::text('storefront_facebook_link', trans('storefront::attributes.storefront_facebook_link'), $errors, $settings) }}
        {{ Form::text('storefront_twitter_link', trans('storefront::attributes.storefront_twitter_link'), $errors, $settings) }}
        {{ Form::text('storefront_instagram_link', trans('storefront::attributes.storefront_instagram_link'), $errors, $settings) }}
        {{ Form::text('storefront_youtube_link', trans('storefront::attributes.storefront_youtube_link'), $errors, $settings) }}
        {{ Form::text('storefront_linkedin_link', trans('storefront::attributes.storefront_linkedin_link'), $errors, $settings) }}
        {{ Form::text('storefront_pinterest_link', trans('storefront::attributes.storefront_pinterest_link'), $errors, $settings) }}
        {{ Form::text('storefront_playstore_link', trans('storefront::attributes.storefront_playstore_link'), $errors, $settings) }}
    </div>
</div>
