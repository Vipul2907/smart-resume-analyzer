@php
    $resumeTemplate = in_array($content['settings']['template'] ?? 'classic', ['classic', 'modern', 'executive'], true)
        ? $content['settings']['template']
        : 'classic';
@endphp
<!doctype html>
<html lang="en" class="resume-template-{{ $resumeTemplate }}"
    style="--resume-accent: {{ $content['settings']['accent_color'] }}; --resume-font: {{ $content['settings']['font_family'] }}, Arial, sans-serif;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resume->name }} · SmartCV Preview</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f5;
            color: #19202a;
            font: 15px/1.55 var(--resume-font);
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            background: #101828;
            color: #fff;
        }

        .toolbar a {
            color: #fff;
        }

        .preview-stage {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            padding: 32px 16px;
        }

        .page {
            width: 210mm;
            max-width: 100%;
            height: 297mm;
            padding: 18mm 16mm;
            flex: 0 0 auto;
            background: #fff;
            box-shadow: 0 8px 35px rgb(0 0 0 / 13%);
        }

        .page-content {
            height: 100%;
            overflow-wrap: anywhere;
        }

        .page-source.one-page-sheet {
            height: 297mm;
            min-height: 0;
            overflow: hidden;
        }

        .page-content> :first-child {
            margin-top: 0;
        }

        .page-source {
            width: 210mm;
            max-width: 100%;
            min-height: 297mm;
            padding: 18mm 16mm;
            flex: 0 0 auto;
            background: #fff;
            box-shadow: 0 8px 35px rgb(0 0 0 / 13%);
        }

        .is-paginated .page-source {
            display: none;
        }

        .accent {
            color: var(--resume-accent);
        }

        h1 {
            margin: 0;
            font-size: 34px;
        }

        h2 {
            margin: 28px 0 0;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--resume-accent);
            font-size: 15px;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .resume-template-classic h1 {
            color: #111827;
            font-size: 32px;
        }

        .resume-template-classic h2 {
            color: #111827;
            border-bottom: 1px solid #64748b;
            letter-spacing: .08em;
        }

        .resume-template-modern h1 {
            color: var(--resume-accent);
            font-size: 39px;
            letter-spacing: -.035em;
        }

        .resume-template-modern header {
            padding: 10px 0 10px 14px;
            border-left: 5px solid var(--resume-accent);
        }

        .resume-template-modern h2 {
            padding: 5px 9px;
            border: 0;
            border-left: 4px solid var(--resume-accent);
            border-radius: 3px;
            background: #f1f5f9;
            color: #172033;
            font-size: 14px;
            letter-spacing: .06em;
            text-transform: none;
        }

        .resume-template-modern .item {
            margin: 10px 0 13px;
        }

        .resume-template-modern .item strong {
            color: #172033;
        }

        .resume-template-executive h1 {
            color: #172033;
            font-family: Georgia, serif;
            font-size: 36px;
            letter-spacing: .055em;
            text-align: center;
            text-transform: uppercase;
        }

        .resume-template-executive header {
            padding-bottom: 12px;
            border-bottom: 2px solid var(--resume-accent);
        }

        .resume-template-executive .meta {
            text-align: center;
        }

        .resume-template-executive h2 {
            color: #26344a;
            border-bottom: 1px solid var(--resume-accent);
            font-family: Georgia, serif;
            font-size: 14px;
            letter-spacing: .16em;
        }

        .resume-template-executive .item strong {
            font-family: Georgia, serif;
        }

        .meta,
        .muted {
            color: #5b6470;
        }

        .meta {
            margin-top: 7px;
        }

        .item {
            margin: 16px 0;
        }

        .item strong {
            display: block;
        }

        ul {
            margin: 7px 0;
            padding-left: 20px;
        }

        @media print {

            .toolbar {
                display: none;
            }

            .is-paginated .page-source {
                display: none;
            }

            body {
                background: #fff;
            }

            .preview-stage {
                display: block;
                padding: 0;
            }

            .page {
                width: 210mm;
                max-width: none;
                height: 297mm;
                margin: 0;
                box-shadow: none;
                break-after: page;
                page-break-after: always;
            }

            .page-source {
                width: 210mm;
                max-width: none;
                min-height: 0;
                padding: 18mm 16mm;
                margin: 0;
                box-shadow: none;
            }

            .page:last-of-type {
                break-after: auto;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>
    @include('components.skip-link')
    <div class="toolbar">
        <a href="{{ route('resumes.builder.edit', $resume) }}">← Back to editor</a><button onclick="window.print()">Print
            / Save as PDF</button>
    </div>
    <main class="preview-stage" data-paginated-document data-page-length="{{ $content['settings']['page_length'] ?? 'one' }}"
        aria-label="A4 resume preview. Each visible paper section is one A4 print page." id="main-content"
        tabindex="-1">
        <section class="page-source" data-page-source>
            <header data-page-block>
                <h1>{{ $content['personal']['name'] }}</h1>
            </header>
            <h2 data-page-block>Contact Information</h2>
            <p class="meta" data-page-block>
                {{ collect([$content['personal']['email'], $content['personal']['phone'], $content['personal']['location'], $content['personal']['website'], $content['personal']['linkedin']])->filter()->implode(' · ') }}
            </p>
            <h2 data-page-block>Professional Summary</h2>
            <p data-page-block>{{ $content['summary'] }}</p>
            <h2 data-page-block>Education</h2>
            @foreach ($content['education'] as $item)
            <div class="item" data-page-block>
                <strong>{{ $item['degree'] ?? '' }}</strong>
                <span class="muted">{{ collect([$item['school'] ?? null, $item['location'] ?? null, $item['start'] ?? null, $item['end'] ?? null])->filter()->implode(' · ') }}</span>
                @if (!empty($item['details']))<p>{{ $item['details'] }}</p>@endif
            </div>
            @endforeach
            @foreach (['experience' => 'Experience', 'projects' => 'Projects', 'certifications' => 'Certifications', 'awards' => 'Awards', 'languages' => 'Languages'] as $key => $title)
            @if (!empty($content[$key]))
            <h2 data-page-block>{{ $title }}</h2>
            @foreach ($content[$key] as $item)
            <div class="item" data-page-block>
                <strong>{{ $item['title'] ?? ($item['degree'] ?? ($item['name'] ?? '')) }}</strong><span
                    class="muted">{{ collect([$item['company'] ?? null, $item['school'] ?? null, $item['issuer'] ?? null, $item['role'] ?? null, $item['location'] ?? null, $item['start'] ?? null, $item['end'] ?? null, $item['date'] ?? null, $item['level'] ?? null])->filter()->implode(' · ') }}</span>
                @if (!empty($item['details']))<p>{{ $item['details'] }}</p>@endif
                @if (!empty($item['link']))<p class="accent">{{ $item['link'] }} </p>@endif


                @if (!empty($item['highlights']))
                <ul>@foreach ($item['highlights'] as $highlight)<li>{{ $highlight }}</li>@endforeach</ul>@endif
            </div>
            @endforeach
            @endif
            @endforeach
            @if ($content['skills'])
            <h2 data-page-block>Skills</h2>
            <p data-page-block>{{ implode(' · ', $content['skills']) }}</p>
            @endif
            @if ($content['interests'])
            <h2 data-page-block>Interests</h2>
            <p data-page-block>{{ implode(' · ', $content['interests']) }}</p>
            @endif
            @foreach ($content['custom_sections'] as $section)
            <h2 data-page-block>{{ $section['title'] }}</h2>
            <p data-page-block>{{ $section['content'] }}</p>
            @endforeach
            @if (!empty($content['declaration_enabled']))
            <h2 data-page-block>Declaration</h2>
            <p data-page-block>I hereby declare that the information provided above is true and correct to the best of my knowledge and belief.</p>
            <p class="item" data-page-block><strong>{{ $content['declaration_name'] }}</strong></p>
            @endif
        </section>
    </main>
    <script>
        (() => {
            const root = document.querySelector('[data-paginated-document]'),
                source = root?.querySelector('[data-page-source]');
            if (!root || !source) return;
            const onePage = root.dataset.pageLength === 'one';
            const newPage = () => {
                const page = document.createElement('article'),
                    content = document.createElement('div');
                page.className = 'page';
                content.className = 'page-content';
                page.append(content);
                root.insertBefore(page, source);
                return content;
            };
            const fits = (page) => page.scrollHeight <= page.clientHeight + 1;
            const fitOnePage = () => {
                source.classList.add('one-page-sheet');
                let content = source.querySelector('.page-content');
                if (!content) {
                    content = document.createElement('div');
                    content.className = 'page-content';
                    while (source.firstChild) content.append(source.firstChild);
                    source.append(content);
                }
                content.style.transform = 'none';
                content.style.width = '100%';
                content.style.height = 'auto';
                content.style.zoom = '1';
                const pageStyle = window.getComputedStyle(source);
                const availableHeight = source.clientHeight - parseFloat(pageStyle.paddingTop) - parseFloat(pageStyle.paddingBottom);
                const contentHeight = content.scrollHeight;
                if (!Number.isFinite(availableHeight) || availableHeight <= 0 || contentHeight <= 0) return;
                const scale = Math.min(1, Math.max(0.000001, (availableHeight - 2) / contentHeight));
                content.style.zoom = String(scale);
            };
            const splitTextBlock = (block, page) => {
                const words = block.textContent.match(/\S+\s*/g) || [];
                let current = page,
                    fragment = block.cloneNode(false);
                current.append(fragment);
                words.forEach((word) => {
                    fragment.append(document.createTextNode(word));
                    if (!fits(current) && fragment.textContent.trim().length > word.trim().length) {
                        fragment.lastChild.remove();
                        current = newPage();
                        fragment = block.cloneNode(false);
                        current.append(fragment);
                        fragment.append(document.createTextNode(word));
                    }
                });
                return current;
            };
            const paginate = () => {
                if (onePage) {
                    root.classList.remove('is-paginated');
                    fitOnePage();
                    return;
                }
                root.classList.remove('is-paginated');
                root.querySelectorAll('.page').forEach((page) => page.remove());
                let page = newPage();
                [...source.children].forEach((originalBlock) => {
                    const block = originalBlock.cloneNode(true);
                    page.append(block);
                    if (fits(page)) return;
                    page.removeChild(block);
                    if (page.children.length) {
                        page = newPage();
                        page.append(block);
                    }
                    if (!fits(page)) {
                        page.removeChild(block);
                        page = splitTextBlock(block, page);
                    }
                });
                root.classList.add('is-paginated');
            };
            paginate();
            window.addEventListener('beforeprint', paginate);
        })();
    </script>
</body>

</html>
