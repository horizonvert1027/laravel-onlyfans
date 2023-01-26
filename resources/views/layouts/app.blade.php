<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
 <meta name="viewport" user-scalable="no" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
 <meta name="google-site-verification" content="ZuCQ74CqfRHDxXq83-wNxwR0Jk44TF3-a_ySsAmO-1c" />
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="index, follow">
  <meta property="og:image" content="https://oprivado.com/public/images/main.png">
  <meta name="description" content="@yield('description_custom')@if(!Request::route()->named('seo') && !Request::route()->named('profile')){{trans('seo.description')}}@endif">
  <meta name="keywords" content="@yield('keywords_custom'){{ trans('seo.keywords') }}" />
  <meta name="theme-color" content="{{ auth()->check() && auth()->user()->dark_mode == 'on' ? '#303030' : $settings->color_default }}">
  <title>{{ auth()->check() && User::notificationsCount() ? '('.User::notificationsCount().') ' : '' }}@section('title')@show {{$settings->title.''.__('')}}</title>
  <!-- Favicon -->
  <link href="{{ url('public/img', $settings->favicon) }}" rel="icon">

  @include('includes.css_general')

  @if ($settings->status_pwa)
    @laravelPWA
  @endif

  @yield('css')

 @if($settings->google_analytics != '')
  {!! $settings->google_analytics !!}
  @endif

</head>

<style>
 .custom-control-label {
    font-size: 13px;
}

.align-bottom {
    vertical-align: middle!important;
}

.close-live, .exit-live, .live-options {
    font-size: 18px!important;
}

.live-views {
    padding: 5px 7px!important;
    font-weight: 500!important;
    font-size: 12px!important;
}

.live {
    padding: 5px 7px!important;
    font-weight: 500!important;
    font-size: 12px!important;
}

.imagem1 {
    background-image: url(https://oprivado.com/public/images/iconweb5.png);
    background-size: contain;
    color: #fff;
    justify-content: center;
    flex-direction: column;
    background-repeat: no-repeat;
}

.imagem2 {
    background-image: url(https://oprivado.com/public/images/logoweb.png);
    background-size: contain;
    color: #fff;
    justify-content: center;
    flex-direction: column;
    background-repeat: no-repeat;
}

.imagem3 {
    background-image: url(https://oprivado.com/public/images/logoicone.png);
    background-size: contain;
    color: #fff;
    justify-content: center;
    flex-direction: column;
    background-repeat: no-repeat;
}

@media (min-width: 1286px){
.bundles-about__img {
    flex: 0 0 50%;
    max-width: 50%;
}}

.bundles-about__img2 {
    width: 100%;
    height:100%;
}

.bundles-about__img {
    align-items: center;
    justify-content: center;
    min-height: 400px;
}

.bundles-about__content h4 {
    font-size: 32px;
    font-weight: 500;
    line-height: 1.06;
    margin-bottom: 37px;
}

@media (min-width: 1286px){
.bundles-about__content {flex: 0 0 50%;
    max-width: 50%;
    padding: 50px!important;
}}

.bundles-about__content {
    padding: 24px;
    font-size: 16px;
    line-height: 1.25;
    background: #fff;
    padding: 35px;
}

@media (min-width: 1286px){
.bundles-about__wrapper {
    display: flex;
}}

.bundles-about__wrapper {
    min-height: 400px;
}

.wrapper-msg-inbox {
    border-top: 1px solid #dee2e6;
}

.modal-content {
    border-radius: 5px!important;
    border: none!important;
}

h5.media-heading.mb-2.text-truncate {
    font-size: 14px!important;
}

a.dropdown-item.border-top.py-2.text-center {
    font-size: 12px;
}

@media (max-width: 991px){
.navbar .navbar-nav .nav-item {
    margin-bottom: 0px;
}}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: #fff!important;
    border-top: 1px solid #ededed!important;
    border-bottom: 1px solid #ededed!important;
}

h6.media-heading.mb-0.text-truncate {
    font-size: 13px!important;
}

.font-weight-bold {
    font-weight: 600!important;
}
.pagination {
    flex-wrap: wrap;
}

.triggerEmoji {
    display: none!important;
}

.link-footer, .ico-social {
    font-size: 12px;
}

.btn:hover {
    box-shadow:none!important;
}

.sweet-alert[data-has-cancel-button=false] button {
    background-color: #fe754f!important;
}

.coluna {display: flex!important; -ms-flex-wrap: wrap!important; flex-wrap: nowrap!important; justify-content: center!important;} 

.dividir {
    height: 0;
    margin: 2px;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}

.page-link {
    padding: 7px!important;
    height: 28px!important;
    min-width: 28px!important;
    font-size: 12px!important;
}

.stories.carousel .story>.item-link>.info .name {
    font-weight: 400!!important;
    font-size: 10px!important;
}

.pagination {
    margin-top: 40px!important;
}

@media (max-width: 991px){
.notify {
    right: 13px !important;
}}

.notify {
    right: 4px;
    padding: 1px 5px!important;
    background: #e41e3f!important;
    font-weight: 600!important;
    border-bottom: 0px solid #DADADA!important;
    font-size: 10px!important;
}

.item-add-story > .info > .name {
    font-size: 10px!important;
}

.sweet-alert .sa-icon {
    display: none!important;
}

.sweet-alert h2 {
    color: #575757;
    font-size: 17px!important;
    font-weight: 600!important;
    margin: 4px 0!important;
}

.sweet-alert p {
    color: #5f5f5f;
    font-size: 14px!important;
    font-weight: 400!important;
}

.sweet-alert button {
    font-size: 12px!important;
    font-weight: 400!important;
    border-radius: 4px!important;
    padding: 9px 12px!important;
    margin: 14px 5px 0 5px!important;
}

.f-size-25 {
    font-size: 21px!important;
}

.btn-category:hover, .active-category {
    border-color: #a1a1a1  !important;
}

.dropdown-item {
    padding: 0.3rem 10px!important;
}

.sweet-alert {
    background-color: #fff;
    font-family: 'Geomanist', Arial, sans-serif !important;
    padding: 15px;
}

.dropdown-menu {
    font-size: 14px !important;
    line-height: normal !important;
    padding: 2px!important;
    border: 1px solid #dbdbdb;
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
}

.category-filter {
    font-weight: 600!important;
    font-size: 15px!important;
    padding-left: 0px;
}

.alert-warning {
    color: #1f2d3d!important;
    background: #fafafa!important;
    border-color: #5f5f5f!important;
}

.icon-chevron-right:before {
    display:none!important;
}

.ico-no-result {
    color: #5f5f5f!important;
    border: 2px solid #5f5f5f!important;
    font-size: 40px!important;
    width: 130px!important;
    height: 130px!important;
    line-height: 130px!important;
}

.lead {
    font-size: 13px!important;
    font-weight: 400!important;
}

.h5, h5 {
    font-size: 15px!important;
}

.texto {
    font-size: 15px!important;
}

.btn-success {
    background-color: #fe754f!important;
    border-color: #fe754f!important;
}

a.social-share i {
    color: #fe754f!important;
    font-size: 22px!important;
}

.alert-primary {
    border-color: #fe754f!important;
    background-color: #fe754f!important;
}

.table thead th {
    color: #645f5f!important;
    font-size: 14px!important;
    font-weight: 600!important;
}

.custom-select {
    font-size: 12px;
}
 
.icon--dashboard {
    display: none!important;
}

.h4, h4 {
    font-size: 18px;
    font-weight: 600!important;
}

.subscriptionDiscount {
    font-size: 10px!important;
    padding: 2px 11px!important;
}

.h2, h2 {
    font-size: 24px!important;
    font-weight: 600!important;
}

.py-5 {
    padding-bottom: 20px!important;
    padding-top: 14px!important;
}

.containerLikeComment {
    font-size: 12px!important;
}

.bloco-right {
    float: left;
}

.badge {
    font-size: 10px;
    font-weight: 600;
    line-height: 10px;
}

.input-group-text {
    font-size: 14px;
    padding: 7px!important;
}

.form-control {
    height: 38px;
    line-height: 16px;
}

.popout {
    padding: 6px 10px!important;
    font-size: 13px!important;
}

.share-btn-user {
    color: #fe794f!important;
}

.select2-container .select2-selection--multiple {
    min-height: 38px!important;
    border: 1px solid #cad1d7;
}

.input-group-alternative {
    transition: none;
    box-shadow: none;
    border: 1px solid #dbdbdb;
    border-radius: 3px;
}

.fundo {
    background: #fafafa;
}

.shadow-custom {
    box-shadow: none!important;
    border-bottom: 1px solid #dbdbdb!important;
}

.borda {
    border: 1px solid #dbdbdb!important;
}

body {
    background: #fafafa;
}

.search-bar {
    border-radius: 4px!important;
    padding: 11px 14px !important;
    height: 37px !important;
    border: 1px solid #dbdbdb !important;
    font-size: 13px!important;
}

.button-search {
    top: 3px!important;
}

h5,h6 {
    font-weight: 600!important;
}

.form-control {
    font-size: 14px;
}

.button-white-sm {
    padding: 3px 12px;
    border-radius: 4px;
    font-size: 11px;
}

.rounded-pill {
    border-radius: 4px!important;
}

.nav-profile {
    border-bottom: 1px solid rgba(159, 163, 177, 0.07);
}

.nav-profile li.active {
    border-bottom: 1px solid #fe754f !important;
}

.btn {
    font-size: 13px;
    padding: 8px 15px;
}

.user-offline-profile::before {
    border: 2px solid #fff!important;
    height: 10%!important;
    width: 10%!important;
    right: 21px!important;
}

.user-online-profile::before {
    border: 2px solid #fff!important;
    height: 10%!important;
    width: 10%!important;
    right: 21px!important;
}

b, strong {
    font-weight: 600!important;
}

.img-user {
    border: 3px solid #FFF;
}

.w-60 {
    width: 60%!important;
}

.menu-left-home li > a {
    font-size: 16px!important;
    color: #1c1e21!important;
}

.menu-left-home li > a:hover, .menu-left-home li > a.active {
    background-color: #c3c3c300;
    color: #1c1e21;
    font-weight: 600;
}

h1,h2,h3,h4,h5, body {
   font-family: 'Geomanist', Arial, sans-serif !important;
}

body {
   font-size: 14px!important;
}

.rounded-large {
    border-radius: 6px!important;
}

.shadow-large {
    box-shadow: none!important;
}

.p-nav {
    padding: 8px!important;
}

@font-face {font-display: swap;font-family: "Geomanist"; font-style: "400"; font-display: block; font-weight: 400; src: url("/public/webfonts/Geomanist-Regular.woff2") format('woff2'), url("/public/webfonts/Geomanist-Regular.woff") format('woff'), url("/public/webfonts/Geomanist-Regular.ttf") format('truetype');}

@font-face {font-display: swap;font-family: "Geomanist"; font-style: "500"; font-display: block; font-weight: 500; src: url("/public/webfonts/Geomanist-Medium.woff2") format('woff2'), url("/public/webfonts/Geomanist-Medium.woff") format('woff'), url("/public/webfonts/Geomanist-Medium.ttf") format('truetype');}

@font-face {font-display: swap;font-family: "Geomanist"; font-style: "600"; font-display: block; font-weight: 600; src: url("/public/webfonts/Geomanist-Book.woff2") format('woff2'), url("/public/webfonts/Geomanist-Book.woff") format('woff'), url("/public/webfonts/Geomanist-Book.ttf") format('truetype');}

@font-face {font-display: swap;font-family: "Geomanist"; font-style: "700"; font-display: block; font-weight: 700; src: url("/public/webfonts/Geomanist-Bold.woff2") format('woff2'), url("/public/webfonts/Geomanist-Bold.woff") format('woff'), url("/public/webfonts/Geomanist-Bold.ttf") format('truetype');}
</style>

<body>
  @if ($settings->disable_banner_cookies == 'off')
  <div class="btn-block text-center showBanner padding-top-10 pb-3 display-none">
    <i class="fa fa-cookie-bite"></i> {{trans('general.cookies_text')}}
    @if ($settings->link_cookies != '')
      <a href="{{$settings->link_cookies}}" class="mr-2 text-white link-border" target="_blank">{{ trans('general.cookies_policy') }}</a>
    @endif
    <button class="btn btn-sm btn-primary" id="close-banner">{{trans('general.go_it')}}
    </button>
  </div>
@endif

  <div id="mobileMenuOverlay" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"></div>

  @auth
    @if (! request()->is('messages/*') && ! request()->is('live/*'))
    @include('includes.menu-mobile')
  @endif
  @endauth

  <div class="popout popout-error font-default"></div>

@if (auth()->guest() && request()->path() == '/' && $settings->home_style == 0
    || auth()->guest() && request()->path() != '/' && $settings->home_style == 0
    || auth()->guest() && request()->path() != '/' && $settings->home_style == 1
    || auth()->check()
    )
  @include('includes.navbar')
  @endif

  <main @if (request()->is('messages/*') || request()->is('live/*')) class="h-100" @endif role="main">
    @yield('content')

    @if (auth()->guest() && ! request()->route()->named('profile')
          || auth()->check()
          && request()->path() != '/'
          && ! request()->is('my/bookmarks')
          && ! request()->is('my/likes')
          && ! request()->is('my/purchases')
          && ! request()->is('explore')
          && ! request()->route()->named('profile')
          && ! request()->is('messages')
          && ! request()->is('messages/*')
          && ! request()->is('live/*')
          )

          @if (auth()->guest() && request()->path() == '/' && $settings->home_style == 0
                || auth()->guest() && request()->path() != '/' && $settings->home_style == 0
                || auth()->guest() && request()->path() != '/' && $settings->home_style == 1
                || auth()->check()
                  )

                  @if (auth()->guest() && $settings->who_can_see_content == 'users')
                    <div class="text-center py-3 px-3">
                      @include('includes.footer-tiny')
                    </div>
                  @else
                    @include('includes.footer')
                  @endif

          @endif

  @endif

  @guest

  @if (request()->is('/')
      && $settings->home_style == 0
      || request()->is('creators')
      || request()->is('creators/*')
      || request()->is('category/*')
      || request()->is('p/*')
      || request()->is('blog')
      || request()->is('blog/post/*')
      || request()->is('shop')
      || request()->is('shop/product/*')
      || request()->route()->named('profile')
      )

      @include('includes.modal-login')

    @endif
  @endguest

  @auth

    @if ($settings->disable_tips == 'off')
     @include('includes.modal-tip')
   @endif

    @include('includes.modal-payperview')

    @if ($settings->live_streaming_status == 'on')
      @include('includes.modal-live-stream')
    @endif
    
  @endauth

  @guest
    @include('includes.modal-2fa')
  @endguest
</main>

  @include('includes.javascript_general')

  @yield('javascript')

@auth
  <div id="bodyContainer"></div>
@endauth
</body>
</html>
