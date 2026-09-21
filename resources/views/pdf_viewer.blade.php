<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        html, body { margin: 0; height: 100%; overflow: hidden; }
        iframe { display: block; width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>
    <iframe src="{{ $pdfSrc }}" title="{{ $title ?? 'Document' }}"></iframe>
</body>
</html>
