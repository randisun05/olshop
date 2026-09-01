<!DOCTYPE html>
@php
    $seo = $page['props']['seo'] ?? [];
    $seoTitle = $seo['title'] ?? config('app.name', 'Toko Online');
    $seoDescription = $seo['description'] ?? null;
    $seoImage = $seo['image'] ?? null;
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
</head>
<body class="antialiased">
    @inertia
</body>
</html>
