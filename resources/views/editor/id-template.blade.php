<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Layout - {{ $template->name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="{{ \Filament\Support\Facades\FilamentAsset::getStyleHref('editor', package: 'cskiller/filament-id-generator') }}">
    <script type="module" src="{{ \Filament\Support\Facades\FilamentAsset::getScriptSrc('editor', package: 'cskiller/filament-id-generator') }}"></script>
</head>
<body>
<div id="editor-app"></div>

<script>
    window.__EDITOR_BOOTSTRAP__ = @json($bootstrap);
</script>
</body>
</html>
