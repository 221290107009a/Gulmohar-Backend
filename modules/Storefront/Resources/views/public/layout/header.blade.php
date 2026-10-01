<div class="hero-nav-wrap">
    <div class="container">
        <div class="hero-nav">
            <a href="{{ route('home') }}">
                <img class="hero-logo" src="{{ $logo }}" alt="Sangho Logo">
            </a>
             <div class="nav-right">
                <div class="menu-switcher">
                    <i class="fa fa-bars menu-icon" onclick="toggleMenuDropdown()"></i>
                    <div class="menu-dropdown" id="menuDropdown">
                        <a href="{{ route('home') }}" class="menu-option">Home</a>
                        <a href="{{ $footerMenuOne->filter(fn($item) => stripos($item->name, 'about') !== false)->first()?->url() ?? url('pages/about-us') }}" class="menu-option">About Us</a>
                        <a href="{{ route('blog_posts.index') }}" class="menu-option">Blogs</a>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>
