<!doctype html>
<html lang="en"
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
        }

        .page-content> :first-child {
            margin-top: 0;
        }

        .page-source {
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

            .toolbar,
            .page-source {
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

            .page:last-child {
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
    <main class="preview-stage" data-paginated-document
        aria-label="A4 resume preview. Each visible paper section is one A4 print page." id="main-content"
        tabindex="-1">
        <section class="page-source" data-page-source>
            <header data-page-block>
                <h1>{{ $content['personal']['name'] }}</h1>
                <p class="meta">
                    {{ collect([$content['personal']['email'], $content['personal']['phone'], $content['personal']['location'], $content['personal']['website'], $content['personal']['linkedin']])->filter()->implode(' · ') }}
                </p>
            </header>
            @if ($content['summary'])
                <h2 data-page-block>Profile</h2>
                <p data-page-block>{{ $content['summary'] }}</p>
            @endif
            @foreach (['experience' => 'Experience', 'education' => 'Education', 'projects' => 'Projects', 'certifications' => 'Certifications', 'awards' => 'Awards', 'languages' => 'Languages'] as $key => $title)
                @if (!empty($content[$key]))
                    <h2 data-page-block>{{ $title }}</h2>
                    @foreach ($content[$key] as $item)
                        <div class="item" data-page-block>
                            <strong>{{ $item['title'] ?? ($item['degree'] ?? ($item['name'] ?? '')) }}</strong><span
                                class="muted">{{ collect([$item['company'] ?? null, $item['school'] ?? null, $item['issuer'] ?? null, $item['role'] ?? null, $item['location'] ?? null, $item['start'] ?? null, $item['end'] ?? null, $item['date'] ?? null, $item['level'] ?? null])->filter()->implode(' · ') }}</span>
                            @if (!empty($item['details']))
                                <p>{{ $item['details'] }}</p>
                                @endif @if (!empty($item['link']))
                                    <p class="accent">{{ $item['link'] }}</p>
                                    @endif @if (!empty($item['highlights']))
                                        <ul>
                                            @foreach ($item['highlights'] as $highlight)
                                                <li>{{ $highlight }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
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
        </section>
    </main>
    <script>
        (() => {
            const root = document.querySelector('[data-paginated-document]'),
                source = root?.querySelector('[data-page-source]');
            if (!root || !source) return;
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
                root.querySelectorAll('.page').forEach((page) => page.remove());
                let page = newPage();
                [...source.children].forEach((block) => {
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
            };
            paginate();
            window.addEventListener('beforeprint', paginate);
        })();
    </script>
</body>

</html>
