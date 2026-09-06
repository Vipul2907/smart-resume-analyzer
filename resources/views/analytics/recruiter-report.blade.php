<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Professional profile · SmartCV</title>
  <style>body{margin:0;background:#f7f8fc;color:#182033;font-family:Arial,sans-serif}main{max-width:850px;margin:auto;background:#fff;padding:50px}h1{font-size:34px;margin:0}h2{margin:30px 0 12px;font-size:18px}.muted{color:#65708a}.tag{display:inline-block;margin:0 8px 8px 0;padding:7px 11px;border-radius:99px;background:#eef0ff;color:#4b3ac7;font-size:13px}.project{padding:16px 0;border-bottom:1px solid #e5e8ef}.score{float:right;padding:8px 12px;border-radius:8px;background:#edfdf7;color:#087a54;font-weight:700}@media print{body{background:#fff}main{padding:0}}</style>
</head>
<body><main>
  <p class="muted">SMARTCV · PROFESSIONAL PROFILE</p><span class="score">{{ $latestScore !== null ? 'Resume score '.$latestScore.'/100' : 'Resume ready' }}</span><h1>{{ auth()->user()->name }}</h1>
  <p class="muted">{{ $profile?->headline ?: auth()->user()->target_role ?: 'Professional candidate' }}{{ $profile?->location ? ' · '.$profile->location : '' }}</p>
  @if($profile?->about)
    <section><h2>Profile</h2><p>{!! nl2br(e($profile->about)) !!}</p></section>
  @endif
  <section><h2>Core skills</h2>
    @forelse($skills as $skill)
      <span class="tag">{{ $skill->name }}{{ $skill->proficiency !== null ? ' · '.$skill->proficiency.'%' : '' }}</span>
    @empty
      <p class="muted">Skills have not been published in this report.</p>
    @endforelse
  </section>
  <section><h2>Selected projects</h2>
    @forelse($projects as $project)
      <div class="project"><strong>{{ $project->title }}</strong>
        @if($project->tagline)<span class="muted"> — {{ $project->tagline }}</span>@endif
        @if($project->description)<p class="muted">{{ $project->description }}</p>@endif
      </div>
    @empty
      <p class="muted">Projects have not been added yet.</p>
    @endforelse
  </section>
  @if($resume)
    <section><h2>Resume</h2><p class="muted">{{ $resume->name }} · prepared in SmartCV</p></section>
  @endif
  <section><h2>Contact</h2><p>{{ auth()->user()->email }}
    @if($profile?->linkedin_url) · {{ $profile->linkedin_url }}@endif
    @if($profile?->website_url) · {{ $profile->website_url }}@endif
  </p></section>
  <p class="muted" style="margin-top:40px;font-size:12px">Generated privately from SmartCV on {{ $generatedAt->format('M j, Y') }}. Use your browser print dialog to save this profile as a PDF.</p>
</main></body></html>
