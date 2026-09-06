<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Career report · SmartCV</title>
  <style>
    :root { color-scheme: light; } body { margin: 0; color: #172033; background: #f7f8fc; font-family: Arial, sans-serif; } main { max-width: 900px; margin: 0 auto; padding: 46px; background: #fff; } h1,h2,p { margin-top: 0; } h1 { font-size: 30px; } h2 { font-size: 17px; margin-bottom: 12px; } .muted { color: #667085; } .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; } .card { border: 1px solid #e6e9f0; border-radius: 12px; padding: 16px; break-inside: avoid; } .number { margin: 8px 0 0; font-size: 27px; font-weight: 700; } table { width: 100%; border-collapse: collapse; margin-top: 12px; } th,td { padding: 10px 0; border-bottom: 1px solid #e6e9f0; text-align: left; font-size: 13px; } .bar { height: 8px; border-radius: 999px; background: #e8ebf4; overflow: hidden; } .bar > div { height: 100%; background: #6d5dfc; } section { margin-top: 30px; } @media print { body { background: #fff; } main { padding: 0; } }
  </style>
</head>
<body><main>
  <p class="muted">SMARTCV · PRIVATE CAREER REPORT</p><h1>{{ auth()->user()->name }}’s career progress</h1><p class="muted">Generated {{ $generatedAt->format('M j, Y, g:i A') }}. This report is based only on data saved in this SmartCV account.</p>
  <section class="grid">@foreach([['Applications', $metrics['applications']], ['Active pipeline', $metrics['active']], ['Response rate', $metrics['response_rate'].'%'], ['Interview score', $metrics['interview_score'] !== null ? $metrics['interview_score'].'/100' : 'No score yet'], ['Skills tracked', $metrics['skills']], ['Goal progress', $metrics['goal_average'] !== null ? $metrics['goal_average'].'%' : 'No goal progress yet'], ['Projects', $metrics['projects']], ['Resume score', $metrics['resume_score'] !== null ? $metrics['resume_score'].'/100' : 'No score yet']] as [$label, $value])<div class="card"><span class="muted">{{ $label }}</span><p class="number">{{ $value }}</p></div>@endforeach</section>
  <section><h2>Application funnel</h2><table><thead><tr><th>Status</th><th>Applications</th></tr></thead><tbody>@foreach($statuses as $status)<tr><td>{{ $status['label'] }}</td><td>{{ $status['count'] }}</td></tr>@endforeach</tbody></table></section>
  <section><h2>Skills</h2>@forelse($topSkills as $skill)<p><strong>{{ $skill['name'] }}</strong> — {{ $skill['proficiency'] }}%{{ $skill['target'] ? ' / '.$skill['target'].'% target' : '' }}</p><progress class="bar" max="100" value="{{ min(100, $skill['proficiency']) }}">{{ $skill['proficiency'] }}</progress>@empty<p class="muted">No skills have been measured yet.</p>@endforelse</section>
  <section><h2>Career goals</h2>@forelse($goals as $goal)<p><strong>{{ $goal->title }}</strong> — {{ $goal->progress ?? 0 }}% complete</p><progress class="bar" max="100" value="{{ min(100, (int) ($goal->progress ?? 0)) }}">{{ $goal->progress ?? 0 }}</progress>@empty<p class="muted">No career goals have been created yet.</p>@endforelse</section>
  <section><h2>Data privacy</h2><p class="muted">This report is private. SmartCV generated it from the owner’s saved job applications, practice sessions, skills, goals, portfolio projects, and completed resume analyses. It does not contain data from other users.</p></section>
</main></body></html>
