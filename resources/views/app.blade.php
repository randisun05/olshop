<!DOCTYPE html>
@php
    $seo = $page['props']['seo'] ?? [];
    $seoTitle = $seo['title'] ?? config('app.name', 'Toko Online');
    $seoDescription = $seo['description'] ?? null;
    $seoImage = $seo['image'] ?? null;
    $gaId = config('services.analytics.google_analytics_id');
    $metaPixelId = config('services.analytics.meta_pixel_id');
@endphp
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dirender di server (bukan lewat komponen <Head> Vue) supaya crawler
         tanpa JS (WhatsApp/Facebook/Twitter link preview, dst) tetap melihat
         tag ini. Atribut `inertia` menandai tag ini boleh diganti oleh
         komponen <Head> Vue saat navigasi SPA berikutnya, lihat
         resources/js/Components/Seo.vue. --}}
    <title inertia>{{ $seoTitle }}</title>
    @if ($seoDescription)
        <meta name="description" content="{{ $seoDescription }}" inertia>
    @endif
    <meta property="og:type" content="website" inertia>
    <meta property="og:title" content="{{ $seoTitle }}" inertia>
    @if ($seoDescription)
        <meta property="og:description" content="{{ $seoDescription }}" inertia>
    @endif
    @if ($seoImage)
        <meta property="og:image" content="{{ $seoImage }}" inertia>
    @endif
    <meta name="twitter:card" content="summary_large_image" inertia>

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead

    {{-- Opsional, tidak aktif tanpa GOOGLE_ANALYTICS_ID/META_PIXEL_ID di .env
         (pola sama seperti reCAPTCHA & login Google). Ditaruh langsung di
         blade (bukan lewat Vue) karena analitik butuh terpasang di setiap
         page load, bukan data yang berubah per halaman. Skrip sungguhan
         (gtag.js/fbevents.js) baru dimuat lewat window.__initAnalytics(),
         dipanggil otomatis kalau consent cookie sudah tersimpan, atau oleh
         CookieConsentBanner.vue saat pengunjung menekan "Terima" — supaya
         tidak melacak pengunjung sebelum mereka menyetujui. --}}
    @if ($gaId || $metaPixelId)
        <script>
            (function () {
                function hasConsent() {
                    try {
                        return localStorage.getItem('cookie_consent') === 'accepted';
                    } catch (e) {
                        return false;
                    }
                }

                window.__initAnalytics = function () {
                    if (window.__analyticsInitialized) return;
                    window.__analyticsInitialized = true;

                    @if ($gaId)
                        var gaScript = document.createElement('script');
                        gaScript.async = true;
                        gaScript.src = 'https://www.googletagmanager.com/gtag/js?id={{ $gaId }}';
                        document.head.appendChild(gaScript);
                        window.dataLayer = window.dataLayer || [];
                        window.gtag = function () { window.dataLayer.push(arguments); };
                        gtag('js', new Date());
                        gtag('config', '{{ $gaId }}');
                    @endif

                    @if ($metaPixelId)
                        !function(f,b,e,v,n,t,s)
                        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                        n.queue=[];t=b.createElement(e);t.async=!0;
                        t.src=v;s=b.getElementsByTagName(e)[0];
                        s.parentNode.insertBefore(t,s)}(window, document,'script',
                        'https://connect.facebook.net/en_US/fbevents.js');
                        fbq('init', '{{ $metaPixelId }}');
                        fbq('track', 'PageView');
                    @endif
                };

                if (hasConsent()) {
                    window.__initAnalytics();
                }
            })();
        </script>
    @endif
</head>
<body class="antialiased">
    @inertia
</body>
</html>
