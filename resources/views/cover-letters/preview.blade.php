<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $letter->title }} · SmartCV</title>
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
      background: #e5e7eb;
      color: #172033;
      font-family: Arial, sans-serif;
    }

    .toolbar {
      padding: 16px;
      text-align: center;
      background: #101827;
    }

    .toolbar button,
    .toolbar a {
      display: inline-block;
      margin: 0 4px;
      padding: 10px 14px;
      border-radius: 8px;
      border: 0;
      background: #fff;
      color: #101827;
      font-weight: bold;
      text-decoration: none;
      cursor: pointer;
    }

    .preview-stage {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 18px;
      padding: 24px 16px;
    }

    .page {
      width: 210mm;
      max-width: 100%;
      height: 297mm;
      padding: 22mm 20mm;
      flex: 0 0 auto;
      background: #fff;
      box-shadow: 0 10px 40px rgba(0, 0, 0, .18);
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

    .meta {
      color: #556070;
      font-size: 14px;
      line-height: 1.7;
    }

    .subject {
      margin: 32px 0 24px;
      font-weight: bold;
    }

    p {
      white-space: pre-wrap;
      font-size: 16px;
      line-height: 1.75;
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
    <a href="{{ route('cover-letters.edit', $letter) }}">Back to editor</a><a href="{{ route('cover-letters.download.docx', $letter) }}">Download DOCX</a><button onclick="window.print()">Print / Save PDF</button>
  </div>
  <main class="preview-stage" data-paginated-document aria-label="A4 cover letter preview. Each visible paper section is one A4 print page." id="main-content" tabindex="-1">
    <section class="page-source" data-page-source>
      <div class="meta" data-page-block>{{ auth()->user()->name }}<br>{{ auth()->user()->email }}<br>{{ now()->format('F j, Y') }}</div>
      @if($letter->recipient_name || $letter->company_name)<p class="meta" data-page-block>{{ $letter->recipient_name }}@if($letter->recipient_name && $letter->company_name)<br>@endif{{ $letter->company_name }}</p>@endif
      @if($letter->subject)<p class="subject" data-page-block>Subject: {{ $letter->subject }}</p>@endif
      @if($letter->opening)<p data-page-block>{{ $letter->opening }}</p>@endif
      <p data-page-block>{{ $letter->body }}</p>
      @if($letter->closing)<p data-page-block>{{ $letter->closing }}</p>@endif
      <p data-page-block>{{ $letter->signature_name ?: auth()->user()->name }}</p>
    </section>
  </main>
  <script>
    (() => {
      const root = document.querySelector('[data-paginated-document]');
      const source = root?.querySelector('[data-page-source]');
      if (!root || !source) return;
      const newPage = () => {
        const page = document.createElement('article');
        const content = document.createElement('div');
        page.className = 'page';
        content.className = 'page-content';
        page.append(content);
        root.insertBefore(page, source);
        return content;
      };
      const fits = (page) => page.scrollHeight <= page.clientHeight + 1;
      const splitTextBlock = (block, page) => {
        const words = block.textContent.match(/\S+\s*/g) || [];
        let current = page;
        let fragment = block.cloneNode(false);
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