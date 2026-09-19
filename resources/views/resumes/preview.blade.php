<!doctype html>
<html lang="en" style="--resume-accent: {{ $content['settings']['accent_color'] }}; --resume-font: {{ $content['settings']['font_family'] }}, Arial, sans-serif;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $resume->name }} · SmartCV Preview</title>
  <style>
    @page { size: A4; margin: 18mm 16mm; }
    * { box-sizing: border-box; }
    body { margin: 0; background: #eef1f5; color: #19202a; font: 15px/1.55 var(--resume-font); }
    .toolbar { display: flex; justify-content: space-between; padding: 16px; background: #101828; color: #fff; }
    .toolbar a { color: #fff; }
    .preview-stage { width: 210mm; max-width: calc(100% - 32px); min-height: 297mm; margin: 32px auto; padding: 0; background: repeating-linear-gradient(to bottom, #fff 0 calc(297mm - 18px), #dce1e8 calc(297mm - 18px) 297mm); box-shadow: 0 8px 35px rgb(0 0 0 / 13%); }
    .page { width: 100%; min-height: 297mm; padding: 18mm 16mm; }
    .accent { color: var(--resume-accent); }
    h1 { margin: 0; font-size: 34px; }
    h2 { margin: 28px 0 0; padding-bottom: 6px; border-bottom: 2px solid var(--resume-accent); font-size: 15px; letter-spacing: .12em; text-transform: uppercase; }
    .meta, .muted { color: #5b6470; }
    .meta { margin-top: 7px; }
    .item { margin: 16px 0; }
    .item strong { display: block; }
    ul { margin: 7px 0; padding-left: 20px; }
    @media print {
      .toolbar { display: none; }
      body { background: #fff; }
      .preview-stage { width: auto; max-width: none; min-height: 0; margin: 0; padding: 0; background: none; box-shadow: none; }
      .page { width: auto; min-height: 0; padding: 0; }
      header, section, .item, li { break-inside: avoid-page; page-break-inside: avoid; }
      h2 { break-after: avoid-page; page-break-after: avoid; }
    }
  </style>
</head>
<body>
  <div class="toolbar"><a href="{{ route('resumes.builder.edit', $resume) }}">← Back to editor</a><button onclick="window.print()">Print / Save as PDF</button></div>
  <main class="preview-stage" aria-label="A4 resume preview. Each visible paper section is one A4 print page.">
  <article class="page">
    <header><h1>{{ $content['personal']['name'] }}</h1><p class="meta">{{ collect([$content['personal']['email'], $content['personal']['phone'], $content['personal']['location'], $content['personal']['website'], $content['personal']['linkedin']])->filter()->implode(' · ') }}</p></header>
    @if($content['summary']) <section><h2>Profile</h2><p>{{ $content['summary'] }}</p></section> @endif
    @foreach(['experience' => 'Experience', 'education' => 'Education', 'projects' => 'Projects', 'certifications' => 'Certifications', 'awards' => 'Awards', 'languages' => 'Languages'] as $key => $title)
      @if(!empty($content[$key])) <section><h2>{{ $title }}</h2>@foreach($content[$key] as $item)<div class="item"><strong>{{ $item['title'] ?? $item['degree'] ?? $item['name'] ?? '' }}</strong><span class="muted">{{ collect([$item['company'] ?? null, $item['school'] ?? null, $item['issuer'] ?? null, $item['role'] ?? null, $item['location'] ?? null, $item['start'] ?? null, $item['end'] ?? null, $item['date'] ?? null, $item['level'] ?? null])->filter()->implode(' · ') }}</span>@if(!empty($item['details']))<p>{{ $item['details'] }}</p>@endif @if(!empty($item['link']))<p class="accent">{{ $item['link'] }}</p>@endif @if(!empty($item['highlights']))<ul>@foreach($item['highlights'] as $highlight)<li>{{ $highlight }}</li>@endforeach</ul>@endif</div>@endforeach</section> @endif
    @endforeach
    @if($content['skills']) <section><h2>Skills</h2><p>{{ implode(' · ', $content['skills']) }}</p></section> @endif
    @if($content['interests']) <section><h2>Interests</h2><p>{{ implode(' · ', $content['interests']) }}</p></section> @endif
    @foreach($content['custom_sections'] as $section)<section><h2>{{ $section['title'] }}</h2><p>{{ $section['content'] }}</p></section>@endforeach
  </article>
  </main>
</body>
</html>
