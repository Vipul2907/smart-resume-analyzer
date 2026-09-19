<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $letter->title }} · SmartCV</title>
  <style>
    @page { size: A4; margin: 22mm 20mm; }
    * { box-sizing: border-box; }
    body { margin: 0; background: #e5e7eb; color: #172033; font-family: Arial, sans-serif; }
    .toolbar { padding: 16px; text-align: center; background: #101827; }
    .toolbar button, .toolbar a { display: inline-block; margin: 0 4px; padding: 10px 14px; border-radius: 8px; border: 0; background: #fff; color: #101827; font-weight: bold; text-decoration: none; cursor: pointer; }
    .preview-stage { width: 210mm; max-width: calc(100% - 32px); min-height: 297mm; margin: 24px auto; background: repeating-linear-gradient(to bottom, #fff 0 calc(297mm - 18px), #dce1e8 calc(297mm - 18px) 297mm); box-shadow: 0 10px 40px rgba(0,0,0,.18); }
    .page { width: 100%; min-height: 297mm; padding: 22mm 20mm; }
    .meta { color: #556070; font-size: 14px; line-height: 1.7; }
    .subject { margin: 32px 0 24px; font-weight: bold; }
    p { white-space: pre-wrap; font-size: 16px; line-height: 1.75; }
    @media print {
      .toolbar { display: none; }
      body { background: #fff; }
      .preview-stage { width: auto; max-width: none; min-height: 0; margin: 0; background: none; box-shadow: none; }
      .page { width: auto; min-height: 0; padding: 0; }
      .meta, .subject { break-inside: avoid-page; page-break-inside: avoid; }
      .subject { break-after: avoid-page; page-break-after: avoid; }
    }
  </style>
</head>
<body>
  <div class="toolbar"><a href="{{ route('cover-letters.edit', $letter) }}">Back to editor</a><a href="{{ route('cover-letters.download.docx', $letter) }}">Download DOCX</a><button onclick="window.print()">Print / Save PDF</button></div>
  <main class="preview-stage" aria-label="A4 cover letter preview. Each visible paper section is one A4 print page.">
    <article class="page">
      <div class="meta">{{ auth()->user()->name }}<br>{{ auth()->user()->email }}<br>{{ now()->format('F j, Y') }}</div>
      @if($letter->recipient_name || $letter->company_name)
        <p class="meta">{{ $letter->recipient_name }}@if($letter->recipient_name && $letter->company_name)<br>@endif{{ $letter->company_name }}</p>
      @endif
      @if($letter->subject)
        <p class="subject">Subject: {{ $letter->subject }}</p>
      @endif
      <p>{{ $letter->opening }}</p>
      <p>{{ $letter->body }}</p>
      <p>{{ $letter->closing }}</p>
      <p>{{ $letter->signature_name ?: auth()->user()->name }}</p>
    </article>
  </main>
</body>
</html>
