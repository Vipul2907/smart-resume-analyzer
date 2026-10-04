<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Make your next career move with resume tools, job matching, interview practice, and one focused workspace.">
    <meta name="theme-color" content="#f7f8fc">
    <title>SmartCV — Make your next move clearer</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #17223a;
            --muted: #66738a;
            --line: #e7eaf1;
            --violet: #6257df;
            --cyan: #1dacc1;
            --paper: #fff;
            --canvas: #f7f8fc;
            --green: #19856a
        }

        * {
            box-sizing: border-box
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 85px
        }

        body {
            margin: 0;
            min-width: 320px;
            color: var(--ink);
            background: var(--canvas);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased
        }

        a {
            color: inherit;
            text-decoration: none
        }

        a:focus-visible,
        summary:focus-visible {
            outline: 3px solid #756cf3;
            outline-offset: 4px
        }

        .page {
            overflow: hidden;
            background: radial-gradient(ellipse at 50% -20%, rgba(191, 187, 255, .32), transparent 39rem), var(--canvas)
        }

        .shell {
            width: min(1140px, calc(100% - 44px));
            margin: auto
        }

        .nav {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
            border-bottom: 1px solid #e7e9f0
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 19px;
            font-weight: 850;
            letter-spacing: -.05em
        }

        .brand-mark {
            display: grid;
            width: 35px;
            height: 35px;
            place-items: center;
            border-radius: 11px;
            color: white;
            background: linear-gradient(140deg, #786dec, #30b9ca);
            box-shadow: 0 7px 18px #7165dc45
        }

        .brand-mark svg {
            width: 20px
        }

        .links,
        .actions {
            display: flex;
            align-items: center;
            gap: 23px
        }

        .links a {
            color: #69758a;
            font-size: 12px;
            font-weight: 650
        }

        .links a:hover,
        .footer-links a:hover {
            color: var(--violet)
        }

        .actions {
            gap: 9px
        }

        .actions form {
            margin: 0
        }

        .signed {
            max-width: 160px;
            overflow: hidden;
            color: var(--muted);
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .btn {
            min-height: 43px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-size: 12px;
            font-weight: 750;
            transition: .2s;
            cursor: pointer
        }

        .btn:hover {
            transform: translateY(-2px)
        }

        .primary {
            color: #fff;
            background: linear-gradient(120deg, #6559e5, #5046cc);
            box-shadow: 0 9px 21px #5f53dc38
        }

        .primary:hover {
            box-shadow: 0 13px 26px #5f53dc55
        }

        .secondary {
            color: #39455b;
            background: #fff;
            border-color: #e0e4ed
        }

        .secondary:hover {
            border-color: #c6c2fa
        }

        .btn svg {
            width: 15px;
            height: 15px
        }

        .hero {
            padding: 56px 0 28px
        }

        .hero-grid {
            display: grid;
            grid-template-columns: .88fr 1.12fr;
            align-items: center;
            gap: 40px
        }

        .copy {
            position: relative;
            z-index: 2;
            padding: 15px 0 22px
        }

        .kicker,
        .label {
            color: #574dc6;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .13em;
            text-transform: uppercase
        }

        .kicker {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .spark {
            display: grid;
            width: 24px;
            height: 24px;
            place-items: center;
            border: 1px solid #e1defe;
            border-radius: 8px;
            background: #f0efff
        }

        .spark svg {
            width: 14px
        }

        h1 {
            max-width: 540px;
            margin: 19px 0 15px;
            font-size: clamp(43px, 5.5vw, 68px);
            line-height: .99;
            letter-spacing: -.075em
        }

        h1 span {
            color: var(--violet)
        }

        .intro {
            max-width: 465px;
            margin: 0;
            color: #68758b;
            font-size: 15px;
            line-height: 1.75
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px
        }

        .reassure {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 16px 0 0;
            color: #78849a;
            font-size: 10px
        }

        .reassure svg {
            width: 15px;
            color: var(--green);
            flex: none
        }

        .visual {
            position: relative;
            padding: 19px 0 20px 16px
        }

        .glow {
            position: absolute;
            inset: 12% 0 5% 9%;
            border-radius: 50%;
            background: #9889f435;
            filter: blur(60px)
        }

        .window {
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e5ed;
            border-radius: 18px;
            background: white;
            box-shadow: 0 32px 78px #29305121, 0 4px 15px #2930510c;
            transform: perspective(1200px) rotateY(-2deg)
        }

        .window-head {
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            border-bottom: 1px solid #edf0f5;
            color: #8791a2;
            font-size: 9px
        }

        .window-brand {
            color: #46516a;
            font-weight: 850
        }

        .window-brand i {
            display: inline-grid;
            width: 18px;
            height: 18px;
            place-items: center;
            margin-right: 5px;
            border-radius: 6px;
            color: #fff;
            background: linear-gradient(140deg, #786dec, #30b9ca);
            font-style: normal
        }

        .dots {
            display: flex;
            gap: 4px
        }

        .dots i {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #d9deea
        }

        .mock {
            display: grid;
            grid-template-columns: 128px 1fr;
            min-height: 315px
        }

        .side {
            padding: 16px 9px;
            border-right: 1px solid #edf0f5;
            background: #fcfcfe
        }

        .person {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 3px 18px
        }

        .avatar {
            display: grid;
            width: 26px;
            height: 26px;
            place-items: center;
            border-radius: 8px;
            color: #584dc5;
            background: #efeeff;
            font-size: 9px;
            font-weight: 800
        }

        .person b,
        .person small {
            display: block
        }

        .person b {
            font-size: 8px
        }

        .person small {
            margin-top: 2px;
            color: #99a2b1;
            font-size: 7px
        }

        .side-label {
            margin: 15px 6px 6px;
            color: #a1a8b6;
            font-size: 7px;
            font-weight: 850;
            letter-spacing: .12em
        }

        .side-link {
            display: block;
            padding: 7px 6px;
            border-radius: 6px;
            color: #748097;
            font-size: 8px
        }

        .side-link.active {
            color: #554ac3;
            background: #f0efff
        }

        .mock-main {
            padding: 21px 19px
        }

        .mock-title {
            display: flex;
            justify-content: space-between;
            gap: 10px
        }

        .mock-main h2 {
            margin: 0;
            font-size: 15px;
            letter-spacing: -.04em
        }

        .mock-main p {
            margin: 5px 0 14px;
            color: #8791a2;
            font-size: 8px
        }

        .sample {
            align-self: start;
            padding: 5px 7px;
            border: 1px solid #eceaff;
            border-radius: 6px;
            color: #6258c8;
            background: #f7f6ff;
            font-size: 7px;
            font-weight: 850;
            white-space: nowrap
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 7px
        }

        .stat {
            padding: 9px;
            border: 1px solid #eceef4;
            border-radius: 9px
        }

        .stat span {
            color: #8b95a6;
            font-size: 7px
        }

        .stat b {
            display: block;
            margin-top: 7px;
            color: #37435b;
            font-size: 16px
        }

        .stat b.purple {
            color: #6458d5
        }

        .match {
            margin-top: 9px;
            padding: 12px;
            border: 1px solid #e9eaf2;
            border-radius: 10px
        }

        .match-top {
            display: flex;
            justify-content: space-between;
            gap: 7px;
            color: #46516a;
            font-size: 8px;
            font-weight: 750
        }

        .match-top small {
            color: #8993a4;
            font-size: 7px;
            font-weight: 500
        }

        .match-body {
            display: grid;
            grid-template-columns: 57px 1fr;
            align-items: center;
            gap: 10px;
            margin-top: 11px
        }

        .ring {
            position: relative;
            display: grid;
            width: 54px;
            height: 54px;
            place-items: center;
            border-radius: 50%;
            background: conic-gradient(#6b5ee3 0 74%, #eeedf8 74%)
        }

        .ring:before {
            position: absolute;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            content: ""
        }

        .ring b {
            position: relative;
            font-size: 13px
        }

        .match-copy b {
            font-size: 8px
        }

        .match-copy p {
            margin: 4px 0 6px;
            font-size: 7px;
            line-height: 1.5
        }

        .pills {
            display: flex;
            flex-wrap: wrap;
            gap: 4px
        }

        .pills span {
            padding: 4px 5px;
            border-radius: 5px;
            color: #33765f;
            background: #edf8f3;
            font-size: 7px;
            font-weight: 700
        }

        .pills span:last-child {
            color: #987039;
            background: #fff6e7
        }

        .mock-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
            margin-top: 8px
        }

        .mock-bottom div {
            padding: 9px;
            border: 1px solid #eceef4;
            border-radius: 8px
        }

        .mock-bottom small {
            color: #939bab;
            font-size: 6px
        }

        .mock-bottom b {
            display: block;
            margin-top: 5px;
            font-size: 8px
        }

        .float {
            position: absolute;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border: 1px solid #ebeaf2;
            border-radius: 12px;
            background: #ffffffed;
            box-shadow: 0 10px 28px #3039591c;
            backdrop-filter: blur(8px)
        }

        .float.top {
            top: 2px;
            right: -8px
        }

        .float.bottom {
            bottom: 0;
            left: -4px
        }

        .float-icon {
            display: grid;
            width: 26px;
            height: 26px;
            place-items: center;
            border-radius: 8px;
            color: #218567;
            background: #eaf7f0
        }

        .float-icon svg {
            width: 14px
        }

        .float b,
        .float small {
            display: block
        }

        .float b {
            font-size: 8px
        }

        .float small {
            margin-top: 2px;
            color: #8d97a8;
            font-size: 7px
        }

        .tool-strip {
            display: grid;
            grid-template-columns: 1.2fr repeat(4, 1fr);
            align-items: center;
            gap: 10px;
            margin-top: 22px;
            padding: 15px 18px;
            border: 1px solid #e8eaf1;
            border-radius: 14px;
            background: #ffffffbd
        }

        .strip-intro {
            color: #818ca0;
            font-size: 9px;
            line-height: 1.5
        }

        .strip-item {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            color: #536078;
            font-size: 9px;
            font-weight: 720;
            white-space: nowrap
        }

        .strip-item svg {
            width: 15px;
            color: #675be0
        }

        .section {
            padding: 86px 0
        }

        .heading {
            max-width: 650px;
            margin: 0 auto 34px;
            text-align: center
        }

        .heading .label {
            font-size: 9px
        }

        .heading h2 {
            margin: 11px 0;
            font-size: clamp(29px, 4vw, 43px);
            line-height: 1.08;
            letter-spacing: -.065em
        }

        .heading p {
            max-width: 540px;
            margin: 0 auto;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7
        }

        .tool-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px
        }

        .tool {
            grid-column: span 2;
            min-height: 185px;
            padding: 21px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--paper);
            box-shadow: 0 6px 20px #29355108;
            transition: .2s
        }

        .tool:hover {
            transform: translateY(-3px);
            border-color: #d4d0fb;
            box-shadow: 0 15px 30px #29355112
        }

        .tool.wide {
            grid-column: span 3;
            min-height: 160px
        }

        .tool-icon {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 1px solid #e7e5ff;
            border-radius: 11px;
            color: #5d53d0;
            background: linear-gradient(140deg, #f1f0ff, #effaff)
        }

        .tool-icon svg {
            width: 18px
        }

        .tool h3 {
            margin: 15px 0 6px;
            font-size: 14px;
            letter-spacing: -.03em
        }

        .tool p {
            max-width: 290px;
            margin: 0;
            color: #748097;
            font-size: 11px;
            line-height: 1.65
        }

        .tool-num {
            float: right;
            color: #b3b9c5;
            font-size: 8px;
            font-weight: 850;
            letter-spacing: .1em
        }

        .journey {
            position: relative;
            padding: 74px 0;
            color: #fff;
            background: #191e36
        }

        .journey:before {
            position: absolute;
            top: -120px;
            right: -70px;
            width: 370px;
            height: 370px;
            border-radius: 50%;
            background: #796bed21;
            filter: blur(70px);
            content: "";
            pointer-events: none
        }

        .journey .heading {
            position: relative
        }

        .journey .heading .label {
            color: #b8b1ff
        }

        .journey .heading h2 {
            color: #fff
        }

        .journey .heading p {
            color: #b3bbce
        }

        .steps {
            position: relative;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px
        }

        .step {
            position: relative;
            min-height: 185px;
            padding: 21px;
            border: 1px solid #ffffff1c;
            border-radius: 16px;
            background: #ffffff09
        }

        .step-num {
            display: flex;
            justify-content: space-between;
            color: #bbb4ff;
            font-size: 9px;
            font-weight: 850;
            letter-spacing: .1em
        }

        .step-num i {
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border: 1px solid #ffffff26;
            border-radius: 9px;
            font-style: normal
        }

        .step h3 {
            margin: 21px 0 7px;
            font-size: 15px
        }

        .step p {
            margin: 0;
            color: #b2bacd;
            font-size: 11px;
            line-height: 1.7
        }

        .connector {
            position: absolute;
            top: 34px;
            right: -14px;
            width: 27px;
            height: 1px;
            background: #bdb6ff55
        }

        .privacy {
            display: grid;
            grid-template-columns: .8fr 1.2fr;
            align-items: center;
            gap: 35px;
            margin: 75px 0;
            padding: 33px 38px;
            border: 1px solid #e7e9f1;
            border-radius: 21px;
            background: linear-gradient(115deg, #f0efff, #f5fbff 72%, #effaf5)
        }

        .privacy h2 {
            margin: 0;
            font-size: clamp(25px, 3vw, 34px);
            line-height: 1.1;
            letter-spacing: -.06em
        }

        .privacy h2 span {
            color: var(--violet)
        }

        .privacy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px
        }

        .privacy-point {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #536078;
            font-size: 10px;
            line-height: 1.55
        }

        .privacy-point svg {
            width: 15px;
            flex: none;
            color: #23876b
        }

        .privacy-point a {
            color: #5147bd;
            text-decoration: underline;
            text-underline-offset: 2px
        }

        .faq {
            padding: 3px 0 77px
        }

        .faq-list {
            max-width: 750px;
            margin: auto;
            border-top: 1px solid #e3e6ee
        }

        .faq-list details {
            border-bottom: 1px solid #e3e6ee
        }

        .faq-list summary {
            padding: 18px 34px 18px 2px;
            color: #37435b;
            font-size: 12px;
            font-weight: 720;
            cursor: pointer;
            list-style: none
        }

        .faq-list summary::-webkit-details-marker {
            display: none
        }

        .faq-list summary:after {
            float: right;
            color: var(--violet);
            content: "+";
            font-size: 18px;
            font-weight: 500
        }

        .faq-list details[open] summary:after {
            content: "−"
        }

        .faq-list details p {
            max-width: 660px;
            margin: -2px 0 18px;
            color: #748097;
            font-size: 11px;
            line-height: 1.7
        }

        .cta {
            padding-bottom: 75px
        }

        .cta-box {
            position: relative;
            overflow: hidden;
            padding: 49px 24px;
            border: 1px solid #dedcff;
            border-radius: 23px;
            background: radial-gradient(ellipse at 15% 0%, #8173ee22, transparent 45%), radial-gradient(ellipse at 90% 100%, #37b8c51d, transparent 42%), #fff;
            box-shadow: 0 15px 45px #30395710;
            text-align: center
        }

        .cta-box h2 {
            max-width: 650px;
            margin: 10px auto;
            font-size: clamp(32px, 5vw, 49px);
            line-height: 1.04;
            letter-spacing: -.07em
        }

        .cta-box p {
            max-width: 470px;
            margin: 12px auto 21px;
            color: #69758a;
            font-size: 12px;
            line-height: 1.7
        }

        .footer {
            padding: 22px 0 28px;
            border-top: 1px solid #e3e6ee
        }

        .footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            color: #8791a3;
            font-size: 9px
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #3b4760;
            font-size: 11px;
            font-weight: 800
        }

        .footer-brand .brand-mark {
            width: 25px;
            height: 25px;
            border-radius: 8px;
            font-size: 10px
        }

        .footer-links {
            display: flex;
            gap: 16px
        }

        @media(max-width:900px) {
            .hero-grid {
                grid-template-columns: .85fr 1.15fr;
                gap: 23px
            }

            .mock {
                grid-template-columns: 106px 1fr
            }

            .mock-main {
                padding: 17px 13px
            }

            .tool-strip {
                grid-template-columns: repeat(4, 1fr)
            }

            .strip-intro {
                grid-column: 1/-1;
                text-align: center
            }
        }

        @media(max-width:680px) {
            .shell {
                width: calc(100% - 30px);
                max-width: 540px
            }

            .nav {
                min-height: 67px
            }

            .links,
            .signed {
                display: none
            }

            .actions {
                gap: 6px
            }

            .actions .btn {
                min-height: 37px;
                padding-inline: 11px;
                font-size: 10px
            }

            .brand {
                font-size: 17px
            }

            .brand-mark {
                width: 31px;
                height: 31px
            }

            .hero {
                padding: 35px 0 20px
            }

            .hero-grid {
                grid-template-columns: 1fr;
                gap: 17px
            }

            .copy {
                padding: 3px 0 0;
                text-align: center
            }

            .kicker {
                justify-content: center
            }

            h1 {
                margin: 16px auto 13px;
                font-size: clamp(42px, 11vw, 57px)
            }

            .intro {
                margin: auto;
                font-size: 13px
            }

            .hero-actions {
                justify-content: center;
                margin-top: 19px
            }

            .reassure {
                justify-content: center;
                font-size: 9px
            }

            .visual {
                padding: 11px 0 18px
            }

            .window {
                transform: none
            }

            .float.top {
                right: -4px
            }

            .float.bottom {
                left: -5px
            }

            .mock {
                grid-template-columns: 92px 1fr;
                min-height: 275px
            }

            .side {
                padding: 12px 6px
            }

            .side-link {
                padding: 7px 5px;
                font-size: 7px
            }

            .mock-main {
                padding: 15px 10px
            }

            .mock-main h2 {
                font-size: 13px
            }

            .stat {
                padding: 7px 5px
            }

            .stat b {
                font-size: 14px
            }

            .match {
                padding: 9px
            }

            .match-body {
                grid-template-columns: 48px 1fr;
                gap: 7px
            }

            .ring {
                width: 46px;
                height: 46px
            }

            .ring:before {
                width: 36px;
                height: 36px
            }

            .match-copy p {
                font-size: 6px
            }

            .tool-strip {
                grid-template-columns: 1fr 1fr;
                gap: 11px;
                padding: 14px 10px
            }

            .strip-intro {
                grid-column: 1/-1
            }

            .strip-item {
                justify-content: flex-start;
                font-size: 8px
            }

            .section {
                padding: 64px 0
            }

            .heading {
                margin-bottom: 25px
            }

            .heading p {
                font-size: 12px
            }

            .tool-grid {
                grid-template-columns: 1fr 1fr;
                gap: 9px
            }

            .tool,
            .tool.wide {
                grid-column: span 1;
                min-height: 185px;
                padding: 15px
            }

            .tool h3 {
                font-size: 12px
            }

            .tool p {
                font-size: 10px
            }

            .journey {
                padding: 62px 0
            }

            .steps {
                grid-template-columns: 1fr
            }

            .step {
                min-height: auto;
                padding: 18px
            }

            .step h3 {
                margin: 15px 0 6px
            }

            .connector {
                top: auto;
                right: auto;
                bottom: -13px;
                left: 31px;
                width: 1px;
                height: 25px
            }

            .privacy {
                grid-template-columns: 1fr;
                gap: 20px;
                margin: 52px 0;
                padding: 23px 19px
            }

            .privacy-grid {
                gap: 12px 8px
            }

            .privacy-point {
                font-size: 9px
            }

            .faq {
                padding-bottom: 58px
            }

            .faq-list summary {
                font-size: 11px
            }

            .cta {
                padding-bottom: 55px
            }

            .cta-box {
                padding: 38px 18px
            }

            .cta-box p {
                font-size: 11px
            }

            .footer-row {
                align-items: flex-start;
                flex-direction: column
            }

            .footer-links {
                gap: 14px
            }
        }

        @media(max-width:390px) {
            .shell {
                width: calc(100% - 24px)
            }

            .hero-actions .btn {
                width: 100%
            }

            .mock {
                grid-template-columns: 1fr
            }

            .side {
                display: none
            }

            .tool-grid {
                grid-template-columns: 1fr
            }

            .tool,
            .tool.wide {
                min-height: auto
            }

            .tool p {
                max-width: none
            }
        }

        @media(prefers-reduced-motion:reduce) {

            *,
            *:before,
            *:after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important
            }
        }

        /* Roomier type and a light journey section make the page easier to scan. */
        .links a {
            font-size: 14px
        }

        .btn {
            font-size: 14px
        }

        .intro {
            font-size: 17px
        }

        .reassure {
            font-size: 12px
        }

        .window-head {
            height: 48px;
            font-size: 11px
        }

        .mock {
            grid-template-columns: 150px 1fr;
            min-height: 390px
        }

        .side {
            padding: 20px 11px
        }

        .person b {
            font-size: 10px
        }

        .person small {
            font-size: 9px
        }

        .side-label {
            font-size: 9px
        }

        .side-link {
            padding: 9px 7px;
            font-size: 10px
        }

        .mock-main {
            padding: 25px 22px
        }

        .mock-main h2 {
            font-size: 20px
        }

        .mock-main p {
            font-size: 11px
        }

        .sample {
            font-size: 9px
        }

        .stats {
            gap: 9px
        }

        .stat {
            padding: 11px
        }

        .stat span {
            font-size: 10px
        }

        .stat b {
            font-size: 22px
        }

        .match {
            padding: 15px
        }

        .match-top {
            font-size: 11px
        }

        .match-top small {
            font-size: 9px
        }

        .match-body {
            grid-template-columns: 68px 1fr;
            gap: 12px
        }

        .ring {
            width: 64px;
            height: 64px
        }

        .ring:before {
            width: 50px;
            height: 50px
        }

        .ring b {
            font-size: 16px
        }

        .match-copy b {
            font-size: 11px
        }

        .match-copy p {
            font-size: 10px
        }

        .pills span {
            font-size: 9px
        }

        .mock-bottom {
            gap: 9px
        }

        .mock-bottom div {
            padding: 11px
        }

        .mock-bottom small {
            font-size: 8px
        }

        .mock-bottom b {
            font-size: 10px
        }

        .float {
            padding: 11px 14px
        }

        .float-icon {
            width: 32px;
            height: 32px
        }

        .float b {
            font-size: 11px
        }

        .float small {
            font-size: 9px
        }

        .tool-strip {
            padding: 18px 20px
        }

        .strip-intro {
            font-size: 12px
        }

        .strip-item {
            font-size: 12px
        }

        .section {
            padding-block: 96px
        }

        .heading h2 {
            font-size: clamp(32px, 4vw, 47px)
        }

        .heading p {
            font-size: 15px
        }

        .tool {
            min-height: 215px;
            padding: 24px
        }

        .tool.wide {
            min-height: 180px
        }

        .tool-icon {
            width: 43px;
            height: 43px
        }

        .tool h3 {
            font-size: 17px
        }

        .tool p {
            font-size: 14px
        }

        .journey {
            color: var(--ink);
            background: linear-gradient(130deg, #eff0ff, #f6f8ff 55%, #eef9fa)
        }

        .journey:before {
            background: #8c83f421
        }

        .journey .heading .label {
            color: #5a4fd0
        }

        .journey .heading h2 {
            color: var(--ink)
        }

        .journey .heading p {
            color: var(--muted)
        }

        .step {
            border-color: #e2e5ef;
            background: #fff;
            box-shadow: 0 8px 24px #3039580b
        }

        .step-num {
            color: #5e54ce
        }

        .step-num i {
            border-color: #e1defa;
            background: #f5f4ff
        }

        .step h3 {
            color: var(--ink)
        }

        .step p {
            color: #68758a
        }

        .connector {
            background: #b9b3f3
        }

        .privacy-point {
            font-size: 13px
        }

        .faq-list summary {
            font-size: 15px
        }

        .faq-list details p {
            font-size: 14px
        }

        .cta-box p {
            font-size: 15px
        }

        .footer-row {
            font-size: 12px
        }

        .footer-brand {
            font-size: 13px
        }

        @media(max-width:680px) {

            .links a,
            .btn {
                font-size: 13px
            }

            .intro {
                font-size: 15px
            }

            .reassure {
                font-size: 11px
            }

            .mock {
                grid-template-columns: 1fr;
                min-height: 0
            }

            .side {
                display: none
            }

            .mock-main {
                padding: 20px 16px
            }

            .mock-main h2 {
                font-size: 18px
            }

            .mock-main p {
                font-size: 10px
            }

            .stat {
                padding: 10px 8px
            }

            .stat span {
                font-size: 9px
            }

            .stat b {
                font-size: 19px
            }

            .match {
                padding: 12px
            }

            .match-top {
                font-size: 10px
            }

            .match-body {
                grid-template-columns: 58px 1fr;
                gap: 10px
            }

            .ring {
                width: 56px;
                height: 56px
            }

            .ring:before {
                width: 43px;
                height: 43px
            }

            .ring b {
                font-size: 14px
            }

            .match-copy b {
                font-size: 10px
            }

            .match-copy p {
                font-size: 9px
            }

            .pills span {
                font-size: 8px
            }

            .mock-bottom b {
                font-size: 9px
            }

            .tool-strip {
                padding: 15px 12px
            }

            .strip-intro {
                font-size: 11px
            }

            .strip-item {
                font-size: 10px
            }

            .section {
                padding-block: 68px
            }

            .heading h2 {
                font-size: clamp(30px, 8vw, 39px)
            }

            .heading p {
                font-size: 13px
            }

            .tool,
            .tool.wide {
                min-height: 195px;
                padding: 17px
            }

            .tool h3 {
                font-size: 14px
            }

            .tool p {
                font-size: 12px
            }

            .step h3 {
                font-size: 16px
            }

            .step p {
                font-size: 13px
            }

            .privacy-point {
                font-size: 11px
            }

            .faq-list summary {
                font-size: 13px
            }

            .faq-list details p {
                font-size: 12px
            }

            .cta-box p {
                font-size: 13px
            }

            .footer-row {
                font-size: 11px
            }
        }

        @media(max-width:390px) {

            .tool,
            .tool.wide {
                min-height: 0;
                padding: 18px
            }

            .tool h3 {
                font-size: 15px
            }

            .tool p {
                font-size: 13px
            }

            .privacy-grid {
                grid-template-columns: 1fr
            }

            .mock-bottom small {
                font-size: 7px
            }

            .mock-bottom b {
                font-size: 9px
            }
        }

        @media(max-width:680px) {
            .intro {
                font-size: 16px
            }

            .reassure {
                font-size: 12px
            }

            .mock-main p {
                font-size: 12px
            }

            .stat span {
                font-size: 10px
            }

            .match-top {
                font-size: 11px
            }

            .match-top small {
                font-size: 9px
            }

            .match-copy b {
                font-size: 12px
            }

            .match-copy p {
                font-size: 11px
            }

            .pills span {
                font-size: 10px
            }

            .mock-bottom small {
                font-size: 8px
            }

            .mock-bottom b {
                font-size: 10px
            }

            .strip-intro {
                font-size: 12px
            }

            .strip-item {
                font-size: 12px
            }

            .tool,
            .tool.wide {
                min-height: 225px
            }

            .tool h3 {
                font-size: 16px
            }

            .tool p {
                font-size: 14px
            }

            .step h3 {
                font-size: 17px
            }

            .step p {
                font-size: 14px
            }

            .privacy-point {
                font-size: 13px
            }

            .faq-list summary {
                font-size: 15px
            }

            .faq-list details p {
                font-size: 14px
            }

            .cta-box p {
                font-size: 15px
            }

            .footer-row {
                font-size: 12px
            }
        }

        @media(max-width:390px) {

            .tool,
            .tool.wide {
                min-height: 0
            }

            .tool h3 {
                font-size: 17px
            }

            .tool p {
                font-size: 14px
            }
        }

        /* Fluid type and card surfaces stay calm and readable as the viewport narrows. */
        .nav {
            flex-wrap: wrap
        }

        .links a {
            font-size: clamp(.78rem, .9vw, .9rem)
        }

        .btn {
            font-size: clamp(.8rem, .92vw, .9rem)
        }

        h1 {
            font-size: clamp(2.7rem, 5.2vw, 4.3rem)
        }

        .intro {
            font-size: clamp(1rem, 1.18vw, 1.1rem)
        }

        .reassure {
            font-size: clamp(.72rem, .82vw, .8rem)
        }

        .heading h2 {
            font-size: clamp(2rem, 3.45vw, 2.95rem)
        }

        .heading p {
            font-size: clamp(.9rem, 1vw, 1rem)
        }

        .tool h3 {
            font-size: clamp(.98rem, 1.16vw, 1.1rem)
        }

        .tool p {
            font-size: clamp(.84rem, .95vw, .94rem)
        }

        .step h3 {
            font-size: clamp(1rem, 1.2vw, 1.13rem)
        }

        .step p {
            font-size: clamp(.84rem, .95vw, .94rem)
        }

        .privacy-point {
            font-size: clamp(.82rem, .92vw, .92rem)
        }

        .faq-list summary {
            font-size: clamp(.9rem, 1vw, 1rem)
        }

        .faq-list details p,
        .cta-box p {
            font-size: clamp(.86rem, .96vw, .96rem)
        }

        .tool,
        .step,
        .privacy,
        .cta-box,
        .tool-strip {
            border-color: rgba(219, 222, 242, .86);
            background: linear-gradient(145deg, rgba(255, 255, 255, .90), rgba(241, 243, 255, .78));
            box-shadow: 0 12px 34px rgba(70, 72, 124, .07)
        }

        .tool:hover {
            background: linear-gradient(145deg, rgba(255, 255, 255, .98), rgba(237, 239, 255, .9));
            box-shadow: 0 18px 38px rgba(70, 72, 124, .11)
        }

        .window {
            background: linear-gradient(145deg, rgba(255, 255, 255, .97), rgba(246, 247, 255, .96));
            border-color: rgba(215, 219, 239, .95);
            box-shadow: 0 32px 78px rgba(55, 57, 112, .14), 0 4px 15px rgba(42, 48, 85, .06)
        }

        .window-head,
        .side {
            background: rgba(248, 249, 255, .92)
        }

        .mock-main {
            background: linear-gradient(145deg, rgba(255, 255, 255, .94), rgba(249, 249, 255, .94))
        }

        .stat,
        .match,
        .mock-bottom div {
            border-color: #e5e6f2;
            background: linear-gradient(145deg, #fff, #f6f7ff)
        }

        .journey .step {
            border-color: #e1e2f2;
            background: linear-gradient(145deg, rgba(255, 255, 255, .94), rgba(241, 242, 255, .86));
            box-shadow: 0 12px 30px rgba(61, 65, 117, .07)
        }

        .privacy {
            background: linear-gradient(115deg, rgba(237, 235, 255, .94), rgba(238, 249, 255, .90) 58%, rgba(235, 249, 243, .88))
        }

        .cta-box {
            background: radial-gradient(ellipse at 15% 0%, rgba(129, 115, 238, .14), transparent 45%), radial-gradient(ellipse at 90% 100%, rgba(55, 184, 197, .13), transparent 42%), linear-gradient(145deg, #fff, #f5f6ff)
        }

        @media(max-width:1020px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 20px
            }

            .copy {
                text-align: center;
                padding: 8px 0 0
            }

            .copy .kicker,
            .hero-actions,
            .reassure {
                justify-content: center
            }

            .intro {
                margin-inline: auto
            }

            .visual {
                width: min(100%, 780px);
                margin-inline: auto
            }

            .window {
                transform: none
            }

            .links {
                display: none
            }

            .tool-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .tool,
            .tool.wide {
                grid-column: span 1
            }

            .steps {
                grid-template-columns: 1fr
            }

            .step {
                min-height: 0
            }

            .connector {
                top: auto;
                right: auto;
                bottom: -13px;
                left: 31px;
                width: 1px;
                height: 25px
            }

            .privacy {
                grid-template-columns: 1fr;
                gap: 22px
            }
        }

        @media(max-width:680px) {
            .shell {
                width: calc(100% - 30px)
            }

            h1 {
                font-size: clamp(2.55rem, 10.5vw, 3.7rem)
            }

            .intro {
                font-size: 1rem
            }

            .reassure {
                font-size: .78rem
            }

            .visual {
                padding-inline: 0
            }

            .mock {
                grid-template-columns: 1fr
            }

            .side {
                display: none
            }

            .mock-main {
                padding: clamp(1rem, 4vw, 1.4rem)
            }

            .mock-main h2 {
                font-size: clamp(1rem, 4vw, 1.2rem)
            }

            .mock-main p {
                font-size: .78rem
            }

            .stat span {
                font-size: .68rem
            }

            .stat b {
                font-size: 1.2rem
            }

            .match-top {
                font-size: .72rem
            }

            .match-top small {
                font-size: .62rem
            }

            .match-copy b {
                font-size: .75rem
            }

            .match-copy p {
                font-size: .69rem
            }

            .pills span {
                font-size: .62rem
            }

            .mock-bottom small {
                font-size: .58rem
            }

            .mock-bottom b {
                font-size: .68rem
            }

            .float b {
                font-size: .72rem
            }

            .float small {
                font-size: .62rem
            }

            .tool-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .tool,
            .tool.wide {
                min-height: 0;
                padding: clamp(.85rem, 3.5vw, 1.15rem)
            }

            .tool h3 {
                font-size: clamp(.9rem, 3.2vw, 1rem)
            }

            .tool p {
                font-size: clamp(.78rem, 2.8vw, .88rem)
            }

            .step h3 {
                font-size: 1.05rem
            }

            .step p {
                font-size: .88rem
            }

            .privacy-point {
                font-size: .8rem
            }

            .faq-list summary {
                font-size: .9rem
            }

            .faq-list details p {
                font-size: .82rem
            }

            .cta-box p {
                font-size: .9rem
            }
        }

        @media(max-width:500px) {
            .tool-grid {
                grid-template-columns: 1fr
            }

            .tool,
            .tool.wide {
                min-height: 0;
                padding: 1.1rem
            }

            .tool h3 {
                font-size: 1.05rem
            }

            .tool p {
                font-size: .9rem
            }

            .privacy-grid {
                grid-template-columns: 1fr
            }

            .hero-actions .btn {
                width: 100%
            }

            .tool-strip {
                grid-template-columns: 1fr 1fr
            }

            .strip-intro {
                grid-column: 1/-1
            }
        }
    </style>
</head>

<body>
    @include('components.skip-link')
    <div class="page">
        <div class="shell">
            <header>
                <nav class="nav" aria-label="Main navigation">
                    <a class="brand" href="{{ route('home') }}"><span class="brand-mark"
                            aria-hidden="true">✦</span>SmartCV</a>
                    <div class="links"><a href="#tools">Explore tools</a><a href="#journey">How it works</a><a
                            href="{{ route('help') }}">Help center</a></div>
                    <div class="actions">
                        @auth
                            <span class="signed">Welcome, {{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn secondary"
                                    type="submit">Sign out</button></form>
                            <a class="btn primary" href="{{ route('dashboard') }}">Open workspace <svg viewBox="0 0 20 20"
                                    fill="none" aria-hidden="true">
                                    <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg></a>
                        @else
                            <a class="btn secondary" href="{{ route('login') }}">Log in</a><a class="btn primary"
                                href="{{ route('register') }}">Get started</a>
                        @endauth
                    </div>
                </nav>
            </header>

            <main id="main-content" tabindex="-1">
                <section class="hero" aria-labelledby="hero-title">
                    <div class="hero-grid">
                        <div class="copy">
                            <div class="kicker"><span class="spark" aria-hidden="true">✧</span>Your next move, made
                                clearer</div>
                            <h1 id="hero-title">Your experience deserves a <span>stronger story.</span></h1>
                            <p class="intro">Shape your resume, see how it fits a role, and keep your job search
                                moving—all from one focused career workspace.</p>
                            <div class="hero-actions">
                                @auth
                                    <a class="btn primary" href="{{ route('dashboard') }}">Go to my workspace <svg
                                            viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg></a>
                                @else
                                    <a class="btn primary" href="{{ route('register') }}">Build your career workspace <svg
                                            viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg></a><a class="btn secondary" href="{{ route('login') }}">I have an account</a>
                                @endauth
                            </div>
                            <p class="reassure"><svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <circle cx="10" cy="10" r="8" stroke="currentColor"
                                        stroke-width="1.3" />
                                    <path d="m5.5 10 3 3 6-6" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>A practical toolkit for the steps between “I’m ready” and “I got the interview.”
                            </p>
                        </div>
                        <div class="visual" aria-label="Illustration of the SmartCV workspace">
                            <div class="glow" aria-hidden="true"></div>
                            <div class="float top"><span class="float-icon">✓</span><span><b>One step at a
                                        time</b><small>Keep your next move in view</small></span></div>
                            <div class="window">
                                <div class="window-head"><span class="window-brand"><i>✦</i> SMARTCV /
                                        Workspace</span><span class="dots"
                                        aria-hidden="true"><i></i><i></i><i></i></span></div>
                                <div class="mock">
                                    <aside class="side" aria-hidden="true">
                                        <div class="person"><span class="avatar">JD</span><span><b>Jordan
                                                    Davis</b><small>Career workspace</small></span></div>
                                        <div class="side-label">WORKSPACE</div><span class="side-link active">▦ &nbsp;
                                            Overview</span><span class="side-link">▤ &nbsp; Resumes</span><span
                                            class="side-link">⌕ &nbsp; Job match</span><span class="side-link">▣
                                            &nbsp; Applications</span>
                                        <div class="side-label">GROW</div><span class="side-link">◇ &nbsp; Interview
                                            prep</span><span class="side-link">✧ &nbsp; Skills & goals</span>
                                    </aside>
                                    <div class="mock-main">
                                        <div class="mock-title">
                                            <div>
                                                <h2>Your career, in focus</h2>
                                                <p>A sample of tools in your workspace</p>
                                            </div><span class="sample">SAMPLE VIEW</span>
                                        </div>
                                        <div class="stats">
                                            <div class="stat"><span>Resume versions</span><b>03</b></div>
                                            <div class="stat"><span>Applications</span><b class="purple">08</b>
                                            </div>
                                            <div class="stat"><span>Skills tracked</span><b>12</b></div>
                                        </div>
                                        <div class="match">
                                            <div class="match-top">Resume ↔ Product Designer <small>Example
                                                    analysis</small></div>
                                            <div class="match-body">
                                                <div class="ring" aria-label="Illustrative match score 74 percent">
                                                    <b>74%</b>
                                                </div>
                                                <div class="match-copy"><b>Make your experience easier to see</b>
                                                    <p>Compare your resume with a role and review skills that match or
                                                        need more evidence.</p>
                                                    <div class="pills"><span>Product
                                                            strategy</span><span>Research</span><span>+ add
                                                            impact</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mock-bottom">
                                            <div><small>INTERVIEW PRACTICE</small><b>Prepare with focused prompts</b>
                                            </div>
                                            <div><small>NEXT STEP</small><b>Keep an application moving</b></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="float bottom"><span class="float-icon"
                                    style="color:#5f59c9;background:#f0efff">↗</span><span><b>Resume + role
                                        insights</b><small>Understand the match, then choose</small></span></div>
                        </div>
                    </div>
                    <div class="tool-strip"><span class="strip-intro">The pieces of a career search,<br>together in
                            one workspace.</span><span class="strip-item">▤ &nbsp; Resume tools</span><span
                            class="strip-item">⌕ &nbsp; Job matching</span><span class="strip-item">◇ &nbsp; Interview
                            prep</span><span class="strip-item">↗ &nbsp; Career growth</span></div>
                </section>

                <section class="section" id="tools" aria-labelledby="tools-title">
                    <div class="heading"><span class="label">One connected toolkit</span>
                        <h2 id="tools-title">More than a resume.<br>A clearer way forward.</h2>
                        <p>Bring your career materials and your next steps together. Use the tools you need, when you
                            need them.</p>
                    </div>
                    <div class="tool-grid">
                        <article class="tool"><span class="tool-num">01 / BUILD</span><span
                                class="tool-icon">▤</span>
                            <h3>Resume studio</h3>
                            <p>Build, organize, and tailor resume versions for the opportunities you care about.</p>
                        </article>
                        <article class="tool"><span class="tool-num">02 / MATCH</span><span
                                class="tool-icon">⌕</span>
                            <h3>Role matching</h3>
                            <p>Compare your resume with a job description and review relevant strengths and gaps.</p>
                        </article>
                        <article class="tool"><span class="tool-num">03 / PREPARE</span><span
                                class="tool-icon">▱</span>
                            <h3>Interview practice</h3>
                            <p>Prepare with guided prompts, save your answers, and review your practice.</p>
                        </article>
                        <article class="tool wide"><span class="tool-num">04 / ORGANIZE</span><span
                                class="tool-icon">▣</span>
                            <h3>Job search tracker</h3>
                            <p>Keep applications, interviews, and follow-ups together so your next action is easier to
                                find.</p>
                        </article>
                        <article class="tool wide"><span class="tool-num">05 / GROW</span><span
                                class="tool-icon">✧</span>
                            <h3>Skills, goals & insights</h3>
                            <p>Track the skills you are building, set career goals, and reflect on your progress over
                                time.</p>
                        </article>
                    </div>
                </section>
        </div>

        <section class="journey" id="journey" aria-labelledby="journey-title">
            <div class="shell">
                <div class="heading"><span class="label">A path you can actually follow</span>
                    <h2 id="journey-title">Turn “I should apply” into your next action.</h2>
                    <p>No magic promises. Just useful tools to help you prepare, make a plan, and keep going.</p>
                </div>
                <div class="steps">
                    <article class="step">
                        <div class="step-num"><span>STEP 01</span><i>01</i></div>
                        <h3>Bring your experience together</h3>
                        <p>Add or build a resume, save your skills, and choose the direction you want to explore.</p>
                        <span class="connector" aria-hidden="true"></span>
                    </article>
                    <article class="step">
                        <div class="step-num"><span>STEP 02</span><i>02</i></div>
                        <h3>Prepare for a real opportunity</h3>
                        <p>Review how your resume fits a role, improve your materials, and practise interview answers.
                        </p><span class="connector" aria-hidden="true"></span>
                    </article>
                    <article class="step">
                        <div class="step-num"><span>STEP 03</span><i>03</i></div>
                        <h3>Keep your momentum</h3>
                        <p>Track applications and follow-ups, then use your workspace to decide what comes next.</p>
                    </article>
                </div>
            </div>
        </section>

        <div class="shell">
            <section class="privacy" aria-labelledby="privacy-title">
                <h2 id="privacy-title">Your career story.<br><span>Your call.</span></h2>
                <div class="privacy-grid">
                    <div class="privacy-point"><b>✓</b><span>Manage resumes and career details from your signed-in
                            workspace.</span></div>
                    <div class="privacy-point"><b>✓</b><span>Choose whether portfolio projects are private or
                            public.</span></div>
                    <div class="privacy-point"><b>✓</b><span>Control whether AI analysis history is retained in your
                            account.</span></div>
                    <div class="privacy-point"><b>✓</b><span>Read the <a href="{{ route('privacy') }}">privacy
                                details</a> before you get started.</span></div>
                </div>
            </section>

            <section class="faq" aria-labelledby="faq-title">
                <div class="heading"><span class="label">Good questions</span>
                    <h2 id="faq-title">A few things you may be wondering.</h2>
                    <p>Clear answers help you decide if SmartCV is right for your next step.</p>
                </div>
                <div class="faq-list">
                    <details>
                        <summary>What can I do with SmartCV?</summary>
                        <p>You can build and manage resumes, compare one with a job description, practise interviews,
                            track applications, work on skills and goals, and create a portfolio.</p>
                    </details>
                    <details>
                        <summary>Does a match score guarantee an interview?</summary>
                        <p>No. A match score is a guide to help you review how your resume relates to a job description.
                            It cannot predict an employer’s decision.</p>
                    </details>
                    <details>
                        <summary>Who can see the information I add?</summary>
                        <p>Your workspace is connected to your account. Portfolio projects can be set to private or
                            public. See the privacy page for details about stored information and AI analysis.</p>
                    </details>
                    <details>
                        <summary>Can I keep different resume versions?</summary>
                        <p>Yes. You can manage more than one resume version and choose which one to use for a particular
                            opportunity.</p>
                    </details>
                </div>
            </section>
            </main>

            <section class="cta" aria-labelledby="cta-title">
                <div class="cta-box"><span class="label">Your next chapter starts with one step</span>
                    <h2 id="cta-title">Make your next move with a little more clarity.</h2>
                    <p>Get your resume, role preparation, and career to-dos into one place—then take it one step at a
                        time.</p>
                    @auth<a class="btn primary" href="{{ route('dashboard') }}">Open my workspace <svg
                                viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round" />
                        </svg></a>@else<a class="btn primary" href="{{ route('register') }}">Create your workspace
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                <path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round" />
                        </svg></a>@endauth
                </div>
            </section>
            <footer class="footer">
                <div class="footer-row"><a class="footer-brand" href="{{ route('home') }}"><span
                            class="brand-mark">S</span>SmartCV <span style="color:#929bad;font-weight:500">©
                            {{ date('Y') }}</span></a><span>Tools to help you make your next career move.</span>
                    <nav class="footer-links" aria-label="Footer navigation"><a
                            href="{{ route('privacy') }}">Privacy</a><a href="{{ route('terms') }}">Terms</a><a
                            href="{{ route('help') }}">Help center</a></nav>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
