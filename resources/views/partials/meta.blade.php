@php
    $metaTitle = $__env->yieldContent('title') 
        ? $__env->yieldContent('title') . ' - ' . setting('site_name', 'SBRS Portal')
        : setting('meta_title', setting('site_name', 'SBRS Portal') . ' - University of Maiduguri');
    $metaDesc = setting('meta_description', 'Official portal of the School of Basic and Remedial Studies, University of Maiduguri.');
    $metaKeywords = setting('meta_keywords', '');
    $metaAuthor = setting('meta_author', '');
    $ogSiteName = setting('og_site_name', setting('site_name', 'SBRS Portal'));
    $ogImage = setting_image('og_image') ?? (setting_image('main_logo') ?? url('/assets/images/favicon.png'));
    $twitterHandle = setting('twitter_handle', '');
    $googleVerification = setting('google_site_verification', '');
    $currentUrl = url()->current();
@endphp

{{-- Basic Meta --}}
<meta name="description" content="{{ $metaDesc }}">
@if($metaKeywords)
<meta name="keywords" content="{{ $metaKeywords }}">
@endif
@if($metaAuthor)
<meta name="author" content="{{ $metaAuthor }}">
@endif
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ $currentUrl }}">

{{-- Open Graph / Facebook / WhatsApp --}}
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $currentUrl }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDesc }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:site_name" content="{{ $ogSiteName }}">
<meta property="og:locale" content="en_NG">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDesc }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@if($twitterHandle)
<meta name="twitter:site" content="{{ $twitterHandle }}">
@endif

{{-- Google Verification --}}
@if($googleVerification)
<meta name="google-site-verification" content="{{ $googleVerification }}">
@endif

{{-- Theme Color --}}
<meta name="theme-color" content="#006633">
<meta name="msapplication-TileColor" content="#006633">
