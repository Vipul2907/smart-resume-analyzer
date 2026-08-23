<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $letter->title }} · SmartCV</title>
  <style>body{margin:0;background:#e5e7eb;color:#172033;font-family:Arial,sans-serif}.toolbar{padding:16px;text-align:center;background:#101827}.toolbar button,.toolbar a{display:inline-block;margin:0 4px;padding:10px 14px;border-radius:8px;border:0;background:#fff;color:#101827;font-weight:bold;text-decoration:none;cursor:pointer}.page{box-sizing:border-box;width:min(210mm,calc(100% - 32px));min-height:297mm;margin:24px auto;padding:28mm 25mm;background:white;box-shadow:0 10px 40px rgba(0,0,0,.18)}.meta{color:#556070;font-size:14px;line-height:1.7}.subject{margin:32px 0 24px;font-weight:bold}p{white-space:pre-wrap;font-size:16px;line-height:1.75}@media print{.toolbar{display:none}.page{width:auto;min-height:0;margin:0;box-shadow:none;padding:0}body{background:white}}</style>
</head>
<body>
  <div class="toolbar"><a href="{{ route('cover-letters.edit', $letter) }}">Back to editor</a><a href="{{ route('cover-letters.download.docx', $letter) }}">Download DOCX</a><button onclick="window.print()">Print / Save PDF</button></div>
  <article class="page"><div class="meta">{{ auth()->user()->name }}<br>{{ auth()->user()->email }}<br>{{ now()->format('F j, Y') }}</div>@if($letter->subject)<p class="subject">Subject: {{ $letter->subject }}</p>@endif<p>{{ $letter->opening }}</p><p>{{ $letter->body }}</p><p>{{ $letter->closing }}</p><p>{{ $letter->signature_name ?: auth()->user()->name }}</p></article>
</body>
</html>
