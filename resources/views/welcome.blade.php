<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmartCV helps you build a stronger resume, improve ATS readiness, and prepare for the next step in your career.">
    <title>SmartCV - Build your next career move</title>
    <style>
        :root { color-scheme: dark; --ink: #070b18; --panel: rgba(16, 25, 48, .78); --line: rgba(255,255,255,.10); --muted: #98a5c2; --white: #f8fafc; --violet: #7c6cff; --cyan: #40d8ff; }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; min-width: 320px; color: var(--white); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--ink); }
        a { color: inherit; text-decoration: none; }
        .page { min-height: 100vh; overflow: hidden; background: radial-gradient(circle at 12% 5%, rgba(91, 81, 225, .27), transparent 28rem), radial-gradient(circle at 88% 30%, rgba(21, 174, 230, .16), transparent 26rem), #070b18; }
        .shell { width: min(1160px, calc(100% - 40px)); margin: 0 auto; }
        nav { height: 84px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .brand { display: inline-flex; align-items: center; gap: 11px; font-weight: 800; font-size: 20px; letter-spacing: -.04em; }
        .brand-mark { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, var(--violet), var(--cyan)); box-shadow: 0 10px 30px rgba(102, 92, 255, .35); color: white; font-size: 20px; }
        .nav-links, .nav-actions { display: flex; align-items: center; gap: 26px; }
        .nav-actions form { margin: 0; }
        .nav-links a { color: #b7c1d7; font-size: 14px; transition: color .2s; }
        .nav-links a:hover { color: white; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 19px; border: 1px solid transparent; border-radius: 12px; font-weight: 750; font-size: 14px; transition: transform .2s, box-shadow .2s, background .2s; cursor: pointer; }
        .button:hover { transform: translateY(-2px); }
        .button-quiet { color: #d8def0; background: rgba(255,255,255,.04); border-color: var(--line); }
        .button-quiet:hover { background: rgba(255,255,255,.09); }
        .button-main { background: linear-gradient(135deg, #7567ff, #46cfff); box-shadow: 0 12px 26px rgba(70, 163, 255, .25); color: white; }
        .button-main:hover { box-shadow: 0 16px 32px rgba(70, 163, 255, .38); }
        .signed-in { color: #91a0bd; font-size: 13px; white-space: nowrap; }
        .hero { position: relative; padding: 64px 0 56px; text-align: center; }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; color: #c6c5ff; border: 1px solid rgba(137,130,255,.30); border-radius: 999px; background: rgba(111, 99, 255, .11); font-weight: 700; font-size: 12px; letter-spacing: .10em; text-transform: uppercase; }
        .eyebrow i { width: 7px; height: 7px; border-radius: 50%; background: #55e8ca; box-shadow: 0 0 12px #55e8ca; }
        h1 { max-width: 960px; margin: 22px auto 18px; font-size: clamp(42px, 5.5vw, 70px); line-height: 1.04; letter-spacing: -.065em; }
        h1 span { color: transparent; background: linear-gradient(100deg, #a89cff, #4ed6ff); -webkit-background-clip: text; background-clip: text; }
        .hero-copy { max-width: 630px; margin: 0 auto; color: var(--muted); font-size: clamp(17px, 2vw, 19px); line-height: 1.65; }
        .hero-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-top: 34px; }
        .hero-actions .button { min-width: 172px; }
        .fine-print { margin-top: 17px; color: #7885a2; font-size: 13px; }
        .dashboard { position: relative; max-width: 1030px; margin: 0 auto; padding: 18px; border: 1px solid rgba(171, 188, 255, .17); border-radius: 24px; background: linear-gradient(135deg, rgba(32, 43, 77, .90), rgba(10, 17, 36, .80)); box-shadow: 0 32px 80px rgba(0,0,0,.45), inset 0 1px rgba(255,255,255,.08); text-align: left; }
        .dashboard-top { display: flex; align-items: center; justify-content: space-between; padding: 5px 5px 18px; color: #aeb9d2; font-size: 13px; }
        .dots { display: flex; gap: 6px; }.dots span { width: 8px; height: 8px; border-radius: 50%; background: #33405e; }.dots span:first-child { background: #ff6d84; }.dots span:nth-child(2) { background: #ffc968; }.dots span:last-child { background: #55dbb7; }
        .dashboard-grid { display: grid; grid-template-columns: 190px 1fr; min-height: 300px; border: 1px solid var(--line); border-radius: 15px; overflow: hidden; background: rgba(3, 8, 21, .55); }
        .mini-side { padding: 18px 13px; border-right: 1px solid var(--line); }.mini-label { color: #71809f; margin: 16px 8px 8px; font-size: 10px; font-weight: 800; letter-spacing: .12em; }.mini-item { display: block; padding: 9px; border-radius: 8px; color: #9cabc7; font-size: 12px; }.mini-item.active { color: white; background: linear-gradient(90deg, rgba(112,98,255,.24), rgba(74,203,255,.08)); }
        .mini-main { padding: 24px; }.mini-main h2 { margin: 0; font-size: 22px; letter-spacing: -.04em; }.mini-main p { margin: 7px 0 21px; color: #8290ad; font-size: 13px; }.metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }.metric { min-height: 104px; padding: 15px; border: 1px solid var(--line); border-radius: 12px; background: rgba(255,255,255,.025); }.metric small { color: #8290ad; font-size: 11px; }.metric strong { display: block; margin-top: 13px; font-size: 27px; letter-spacing: -.05em; }.metric strong.good { color: #63e3c1; }.progress { height: 7px; margin-top: 13px; border-radius: 20px; background: #202b44; overflow: hidden; }.progress b { display: block; width: 82%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #7567ff, #4ed6ff); }.mini-card { margin-top: 13px; padding: 16px; border: 1px solid var(--line); border-radius: 12px; }.mini-card b { font-size: 13px; }.mini-card span { display: block; margin-top: 8px; color: #8391ae; font-size: 12px; }
        .features { padding: 110px 0; }.section-heading { max-width: 670px; margin: 0 auto 42px; text-align: center; }.section-heading h2 { margin: 12px 0; font-size: clamp(30px, 4vw, 45px); letter-spacing: -.055em; }.section-heading p { margin: 0; color: var(--muted); line-height: 1.65; }.feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }.feature { padding: 25px; border: 1px solid var(--line); border-radius: 18px; background: rgba(21, 31, 58, .58); transition: border-color .2s, transform .2s, background .2s; }.feature:hover { transform: translateY(-4px); border-color: rgba(124,108,255,.52); background: rgba(29, 41, 76, .78); }.feature-icon { display: grid; place-items: center; width: 43px; height: 43px; margin-bottom: 19px; border-radius: 13px; background: linear-gradient(135deg, rgba(126,111,255,.28), rgba(64,216,255,.16)); color: #a79fff; font-size: 21px; }.feature h3 { margin: 0 0 9px; font-size: 17px; letter-spacing: -.03em; }.feature p { margin: 0; color: #91a0bd; font-size: 14px; line-height: 1.6; }
        .how { padding: 34px 0 80px; }.how-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }.how-step { position: relative; padding: 26px; border: 1px solid #e5e9f1; border-radius: 20px; background: #fff; box-shadow: 0 9px 25px rgba(31,41,55,.045); }.step-number { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 10px; color: #5147b7; background: #f1f0ff; font-size: 13px; font-weight: 800; }.how-step h3 { margin: 18px 0 8px; color: #17223a; font-size: 18px; }.how-step p { margin: 0; color: #667085; font-size: 14px; line-height: 1.65; }.faq { padding: 10px 0 90px; }.faq-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }.faq details { padding: 18px; border: 1px solid #e5e9f1; border-radius: 14px; background: #fff; }.faq summary { cursor: pointer; color: #17223a; font-size: 15px; font-weight: 750; }.faq details p { margin: 12px 0 0; color: #667085; font-size: 14px; line-height: 1.6; }
        .cta { padding: 0 0 90px; }.cta-box { padding: 54px 30px; border: 1px solid rgba(125,110,255,.34); border-radius: 24px; background: radial-gradient(circle at 50% 0%, rgba(105,90,255,.29), transparent 55%), rgba(16,24,49,.7); text-align: center; }.cta-box h2 { margin: 0; font-size: clamp(28px, 4vw, 43px); letter-spacing: -.055em; }.cta-box p { max-width: 520px; margin: 14px auto 27px; color: #a1aed0; line-height: 1.6; }
        footer { display: flex; justify-content: space-between; gap: 20px; padding: 27px 0 35px; border-top: 1px solid var(--line); color: #7e8ba6; font-size: 13px; }.footer-links { display: flex; gap: 18px; }.footer-links a:hover { color: white; }
        @media (max-width: 720px) { .shell { width: min(100% - 28px, 1160px); } nav { height: 72px; }.nav-links, .signed-in { display: none; }.nav-actions { gap: 8px; }.nav-actions .button { min-height: 40px; padding: 0 12px; }.hero { padding: 54px 0 44px; }.dashboard { padding: 10px; }.dashboard-grid { grid-template-columns: 1fr; }.mini-side { display: none; }.mini-main { padding: 17px; }.metric-grid { grid-template-columns: 1fr 1fr; }.metric:last-child { grid-column: span 2; }.feature-grid, .how-grid, .faq-grid { grid-template-columns: 1fr; }.features { padding: 76px 0; } footer { flex-direction: column; }.cta { padding-bottom: 65px; } }

        /* Step 17: the public SmartCV experience uses the same bright, calm system as the workspace. */
        :root { color-scheme: light; --ink: #f7f8fc; --panel: #fff; --line: #e6eaf0; --muted: #667085; --white: #17223a; --violet: #635bff; --cyan: #12b8d4; }
        body { color: #17223a; background: #f7f8fc; }
        .page { background: radial-gradient(circle at 7% 5%, rgba(99,91,255,.13), transparent 28rem), radial-gradient(circle at 90% 20%, rgba(18,184,212,.10), transparent 25rem), #f7f8fc; }
        .brand { color: #17223a; }.brand-mark { box-shadow: 0 12px 24px rgba(99,91,255,.24); }
        .nav-links a { color: #667085; font-weight: 650; }.nav-links a:hover { color: #332d87; }
        .button-quiet { color: #344054; background: #fff; border-color: #dbe1eb; }.button-quiet:hover { background: #f4f5ff; border-color: #bab7ff; }
        .button-main { background: linear-gradient(135deg,#635bff,#5b62ef 52%,#12b8d4); box-shadow: 0 12px 26px rgba(99,91,255,.22); }
        .signed-in { color: #667085; }.eyebrow { color: #5147b7; border-color: #d8d6ff; background: #f3f2ff; }.eyebrow i { background: #12b8a6; box-shadow: 0 0 10px rgba(18,184,166,.55); }
        h1 { color: #17223a; } h1 span { background-image: linear-gradient(100deg,#635bff,#0aa8c4); }
        .hero-copy, .fine-print { color: #667085; }
        .dashboard { border-color: #e0e5ef; background: rgba(255,255,255,.88); box-shadow: 0 30px 70px rgba(34,46,75,.12), inset 0 1px rgba(255,255,255,.85); }
        .dashboard-top { color: #667085; }.dots span { background: #d3dae6; }.dashboard-grid { border-color: #e4e8f0; background: #fbfcff; }
        .mini-side { border-color: #e5e9f1; }.mini-label { color: #8a94a6; }.mini-item { color: #667085; }.mini-item.active { color: #3e3694; background: linear-gradient(90deg, rgba(99,91,255,.13), rgba(18,184,212,.07)); }
        .mini-main h2 { color: #17223a; }.mini-main p, .metric small, .mini-card span { color: #667085; }.metric { border-color: #e4e8f0; background: #fff; }.metric strong.good { color: #138a63; }.progress { background: #e6eaf0; }.progress b { background: linear-gradient(90deg,#635bff,#12b8d4); }.mini-card { border-color: #e4e8f0; background: #fff; }.mini-card b { color: #17223a; }
        .section-heading h2, .feature h3, .cta-box h2 { color: #17223a; }.section-heading p, .feature p, .cta-box p { color: #667085; }.feature { border-color: #e5e9f1; background: #fff; box-shadow: 0 8px 24px rgba(31,41,55,.045); }.feature:hover { border-color: #bbb7ff; background: #fff; box-shadow: 0 16px 35px rgba(70,63,156,.11); }.feature-icon { color: #635bff; background: linear-gradient(135deg,#f0efff,#e8faff); }.cta-box { border-color: #dcd9ff; background: radial-gradient(circle at 50% 0%, rgba(99,91,255,.14), transparent 58%), #fff; box-shadow: 0 16px 40px rgba(40,48,74,.07); } footer { border-color: #e4e8f0; color: #667085; }.footer-links a:hover { color: #5147b7; }
    </style>
</head>
<body>
<div class="page">
    <div class="shell">
        <nav aria-label="Main navigation">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">S</span>SmartCV</a>
            <div class="nav-links">
                <a href="#tools">Career tools</a>
                <a href="#how-it-works">How it works</a>
                <a href="{{ route('help') }}">Help center</a>
            </div>
            <div class="nav-actions">
                @auth
                    <span class="signed-in">Signed in as {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button button-quiet" type="submit">Sign out</button>
                    </form>
                    <a class="button button-main" href="{{ route('dashboard') }}">Open workspace</a>
                @else
                    <a class="button button-quiet" href="{{ route('login') }}">Log in</a>
                    <a class="button button-main" href="{{ route('register') }}">Create account</a>
                @endauth
            </div>
        </nav>

        <main>
            <section class="hero" id="how-it-works">
                <div class="eyebrow"><i></i> Your free AI career workspace</div>
                <h1>Build a stronger career, <span>one focused action at a time.</span></h1>
                <p class="hero-copy">SmartCV connects your resume, live opportunities, interview practice, and career plan in one private workspace built to help you move forward.</p>
                <div class="hero-actions">
                    @auth
                        <a class="button button-main" href="{{ route('dashboard') }}">Open my workspace</a>
                    @else
                        <a class="button button-main" href="{{ route('register') }}">Create your free account</a>
                        <a class="button button-quiet" href="{{ route('login') }}">I already have an account</a>
                    @endauth
                </div>
                <p class="fine-print">Free forever. No credit card. Built for your next opportunity.</p>
            </section>

            <section class="dashboard" aria-label="SmartCV workspace preview">
                <div class="dashboard-top"><div class="dots"><span></span><span></span><span></span></div><span>SMARTCV WORKSPACE</span><span>Today</span></div>
                <div class="dashboard-grid">
                    <aside class="mini-side"><b style="font-size:14px">Workspace</b><div class="mini-label">CAREER</div><span class="mini-item active">Overview</span><span class="mini-item">My resumes</span><span class="mini-item">ATS analysis</span><span class="mini-item">Interview prep</span><div class="mini-label">GROW</div><span class="mini-item">Skill plan</span><span class="mini-item">Career insights</span></aside>
                    <div class="mini-main"><h2>Your career workspace</h2><p>One focused place for your next best career move.</p><div class="metric-grid"><div class="metric"><small>Resume readiness</small><strong class="good">82%</strong><div class="progress"><b></b></div></div><div class="metric"><small>ATS match score</small><strong>74</strong><div class="progress"><b style="width:74%"></b></div></div><div class="metric"><small>Interview practice</small><strong>06</strong><div class="progress"><b style="width:60%"></b></div></div></div><div class="mini-card"><b>Recommended next action</b><span>Tailor your resume summary to the role you want next.</span></div></div>
                </div>
            </section>

            <section class="how" id="how-it-works">
                <div class="section-heading"><div class="eyebrow">A calmer way to grow</div><h2>Three steps from ambition to action.</h2><p>Every tool is connected, so you always know what to do next.</p></div>
                <div class="how-grid">
                    <article class="how-step"><span class="step-number">01</span><h3>Build your foundation</h3><p>Create or upload a resume, add your real skills, projects, and career direction.</p></article>
                    <article class="how-step"><span class="step-number">02</span><h3>Improve for the role</h3><p>Compare your resume to an opportunity, strengthen ATS readiness, and practise answers.</p></article>
                    <article class="how-step"><span class="step-number">03</span><h3>Apply with clarity</h3><p>Save jobs, manage follow-ups, and use one simple dashboard to keep momentum.</p></article>
                </div>
            </section>

            <section class="features" id="tools">
                <div class="section-heading"><div class="eyebrow">Everything in one place</div><h2>A stronger career system, not just a resume checker.</h2><p>Use focused tools that help you present your experience clearly and keep your job search moving.</p></div>
                <div class="feature-grid">
                    <article class="feature"><div class="feature-icon">▤</div><h3>Resume analysis</h3><p>Review the strength, clarity, and structure of your resume before you send it.</p></article>
                    <article class="feature"><div class="feature-icon">◎</div><h3>ATS readiness</h3><p>Find important keywords and improve how your resume matches a target role.</p></article>
                    <article class="feature"><div class="feature-icon">◈</div><h3>Interview preparation</h3><p>Practice thoughtful answers and prepare examples that show your best work.</p></article>
                    <article class="feature"><div class="feature-icon">↗</div><h3>Job tracker</h3><p>Keep applications, follow-ups, interviews, and next actions organised.</p></article>
                    <article class="feature"><div class="feature-icon">✦</div><h3>Skill planning</h3><p>See which skills can make the biggest difference for the path you want.</p></article>
                    <article class="feature"><div class="feature-icon">◌</div><h3>Career insights</h3><p>Use one calm workspace to track progress and make better career decisions.</p></article>
                </div>
            </section>

            <section class="faq"><div class="section-heading"><div class="eyebrow">Questions, answered</div><h2>Designed to stay simple.</h2></div><div class="faq-grid"><details><summary>Is SmartCV really free?</summary><p>Yes. SmartCV is designed as a free career workspace with no payment wall.</p></details><details><summary>Is my resume private?</summary><p>Uploaded files and saved career data are tied to your signed-in account and stored privately.</p></details><details><summary>Can I use more than one resume?</summary><p>Yes. Keep targeted resume versions for different roles, then analyse the one you choose.</p></details><details><summary>What can SmartCV help me do?</summary><p>Build resumes, compare them to jobs, practise interviews, track applications, grow skills, and share a portfolio.</p></details></div></section>

            <section class="cta"><div class="cta-box"><h2>Start with the career you want next.</h2><p>Create a free SmartCV account and build a clearer, more confident path forward.</p>@auth<a class="button button-main" href="{{ route('dashboard') }}">Open my workspace</a>@else<a class="button button-main" href="{{ route('register') }}">Create free account</a>@endauth</div></section>
        </main>

        <footer><span>© {{ date('Y') }} SmartCV. Free career tools for everyone.</span><div class="footer-links"><a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('terms') }}">Terms</a><a href="{{ route('help') }}">Help</a></div></footer>
    </div>
</div>
</body>
</html>
