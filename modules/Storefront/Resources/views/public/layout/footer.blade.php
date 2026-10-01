<footer class="sangho-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-18 mb-4">
                <div class="footer-about">
                    <a href="{{ route('home') }}">
                        <img class="footer-logo" src="{{ $logo }}" alt="Sangho Logo">
                    </a>
                    <p class="footer-desc">
                        Sangho is the all-in-one digital platform for the Bahujan community, bringing together culture, knowledge, business, and digital tools.
                    </p>
                    <div class="social-links">
                        @if (setting('storefront_facebook_link'))
                            <a href="{{ setting('storefront_facebook_link') }}" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if (setting('storefront_twitter_link'))
                            <a href="{{ setting('storefront_twitter_link') }}" target="_blank"><i class="fab fa-x-twitter"></i></a>
                        @endif
                        @if (setting('storefront_instagram_link'))
                            <a href="{{ setting('storefront_instagram_link') }}" target="_blank"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if (setting('storefront_youtube_link'))
                            <a href="{{ setting('storefront_youtube_link') }}" target="_blank"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if (setting('storefront_linkedin_link'))
                            <a href="{{ setting('storefront_linkedin_link') }}" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        @endif
                        @if (setting('storefront_pinterest_link'))
                            <a href="{{ setting('storefront_pinterest_link') }}" target="_blank"><i class="fab fa-pinterest-p"></i></a>
                        @endif
                        @if (setting('storefront_playstore_link'))
                            <a href="{{ setting('storefront_playstore_link') }}" target="_blank"><i class="fab fa-google-play"></i></a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-md-9 mb-6">
                <div class="footer-links">
                    <h4 class="footer-title">Features</h4>
                    <ul class="mb-4">
                        @if ($footerMenuTwo->isNotEmpty())
                            @foreach ($footerMenuTwo as $menuItem)
                                <li><a href="{{ $menuItem->url() }}" target="{{ $menuItem->target }}">{{ $menuItem->name }}</a></li>
                            @endforeach
                        @endif
                        <li><a href="{{ route('blog_posts.index') }}">Blogs</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-5 col-md-9 mb-6">
                <div class="footer-links">
                    <h4 class="footer-title">Connect</h4>
                    <ul class="mb-4">
                        @if ($footerMenuOne->isNotEmpty())
                            @foreach ($footerMenuOne as $menuItem)
                                <li><a href="{{ $menuItem->url() }}" target="{{ $menuItem->target }}">{{ $menuItem->name }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="footer-actions footer-actions-connect">
                        <a href="https://play.google.com/store/apps/details?id=sangho.app&pcampaignid=web_share" class="btn-google-plays google-play-footer">
                            <img src="{{ asset('build/assets/google_play.svg') }}" alt="Google Play">
                            <span class="google-play-text">
                                <span class="google-play-text-normal">Download on</span>
                                <span class="google-play-text-bold">Google Play</span>
                            </span>
                        </a>                        
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>{!! setting('storefront_copyright_text') !!} <a href="{{ setting('storefront_footer_designed_by_url') }}" target="_blank">{{ setting('storefront_footer_designed_by_text') }}</a></p>
        </div>
    </div>
</footer>
