<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            margin: 0;
            size: {{ $widthMm }}mm {{ $heightMm }}mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
        }

        .page {
            width: {{ $widthMm }}mm;
            height: {{ $heightMm }}mm;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .page img {
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
@foreach ($pages as $page)
    <div class="page">
        <img src="{{ $page['data_uri'] }}" alt="Generated ID page">
    </div>
@endforeach
</body>
</html>
