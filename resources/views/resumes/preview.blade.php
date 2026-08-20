<!doctype html>
<html lang="en" style="--resume-accent: {{ $content['settings']['accent_color'] }}; --resume-font: {{ $content['settings']['font_family'] }}, Arial, sans-serif;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $resume->name }} · SmartCV Preview</title>
  <style>
    body { margin: 0; background: #eef1f5; color: #19202a; font: 15px/1.55 var(--resume-font); }
    .toolbar { display: flex; justify-content: space-between; padding: 16px; background: #101828; color: #fff; }
    .toolbar a { color: #fff; }
    .page { box-sizing: border-box; max-width: 850px; min-height: 1100px; margin: 32px auto; padding: 58px 62px; background: #fff; box-shadow: 0 8px 35px rgb(0 0 0 / 13%); }
    .accent { color: var(--resume-accent); }
    h1 { margin: 0; font-size: 34px; }
    h2 { margin-top: 28px; padding-bottom: 6px; border-bottom: 2px solid var(--resume-accent); font-size: 15px; letter-spacing: .12em; text-transform: uppercase; }
    .meta, .muted { color: #5b6470; }
    .meta { margin-top: 7px; }
    .item { margin: 16px 0; }
    .item strong { display: block; }
    ul { margin: 7px 0; padding-left: 20px; }
    @media print { .toolbar { display: none; } body { background: #fff; } .page { max-width: none; min-height: 0; margin: 0; padding: 20mm; box-shadow: none; } }
  </style>
</head>
<body>
  <div class="toolbar"><a href="{{ route('resumes.builder.edit', $resume) }}">← Back to editor</a><button onclick="window.print()">Print / Save as PDF</button></div>
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
</body>
</html>
