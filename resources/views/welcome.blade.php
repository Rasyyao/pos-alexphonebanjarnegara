{{-- Landing page — Alex Phone Banjarnegara --}}
<!DOCTYPE html>
<html lang="id" data-theme="navy" data-theme-mode="light" data-fonts="helvetica" data-cards="flat" data-hero="editorial">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alex Phone Banjarnegara — Solusi Gadget Paling Update</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    <style>
        :root {
            --bg: #FFFFFF;
            --bg-soft: #F4F6FB;
            --bg-elev: #FFFFFF;
            --ink: #0A2540;
            --ink-soft: #3D5374;
            --ink-mute: #7A8AA8;
            --line: #E4E9F2;
            --brand: #0A2540;
            --accent: #2563EB;
            --accent-ink: #FFFFFF;
            --success: #10806B;
            --warn: #C2410C;
            --radius-card: 20px;
            --radius-svc: 16px;
            --radius-pill: 999px;
            --font-ui: "Helvetica Neue", Helvetica, system-ui, -apple-system, sans-serif;
            --font-mono: "JetBrains Mono", ui-monospace, monospace;
            --page-max: 1480px;
            --gutter: clamp(16px, 1.8vw, 28px);
            --section-y: clamp(64px, 8vw, 120px);
        }

        html[data-theme="indigo"] {
            --accent: #4F46E5;
        }

        html[data-theme="cool-slate"] {
            --accent: #334155;
        }

        html[data-theme="warm"] {
            --accent: #C2410C;
        }

        html[data-theme-mode="dark"] {
            --bg: #0A1428;
            --bg-soft: #0F1B33;
            --bg-elev: #162547;
            --ink: #F4F6FB;
            --ink-soft: #A8B6D1;
            --ink-mute: #6E7EA0;
            --line: #1E2D4E;
            --brand: #FFFFFF;
            --accent-ink: #0A1428;
        }

        html[data-theme-mode="dark"][data-theme="navy"] {
            --accent: #5B8DEF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            background: var(--bg);
        }

        body {
            font-family: var(--font-ui);
            color: var(--ink);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
            line-height: 1.5;
            font-size: 16px;
            transition: background 220ms ease, color 220ms ease;
            position: relative;
            overflow-x: clip;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 900px;
            pointer-events: none;
            z-index: 0;
            background-image: radial-gradient(circle at 1px 1px, color-mix(in oklab, var(--ink) 6%, transparent) 1px, transparent 0);
            background-size: 32px 32px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.2) 50%, transparent 100%);
            -webkit-mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.2) 50%, transparent 100%);
        }

        main,
        nav.top,
        footer {
            position: relative;
            z-index: 1;
        }

        p {
            text-wrap: pretty;
        }

        h1,
        h2,
        h3,
        h4 {
            text-wrap: balance;
            letter-spacing: -0.02em;
            line-height: 1.15;
            font-weight: 600;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img,
        svg {
            display: block;
            max-width: 100%;
        }

        html {
            scroll-behavior: smooth;
        }

        :focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 3px;
            border-radius: 4px;
        }

        .wrap {
            max-width: var(--page-max);
            margin: 0 auto;
            padding-left: var(--gutter);
            padding-right: var(--gutter);
        }

        section {
            padding-top: var(--section-y);
        }

        .band {
            background: var(--bg-soft);
            padding-top: var(--section-y);
            padding-bottom: var(--section-y);
            transition: background 220ms ease;
        }

        .eyebrow {
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--ink-mute);
        }

        nav.top {
            position: sticky;
            top: 16px;
            z-index: 50;
            margin: 16px auto 0;
            width: min(calc(100% - 32px), var(--page-max));
            border-radius: 999px;
            background: color-mix(in oklab, var(--bg), transparent 8%);
            backdrop-filter: saturate(160%) blur(20px);
            border: 1px solid var(--line);
            box-shadow: 0 12px 32px -18px color-mix(in oklab, var(--ink), transparent 70%);
        }

        nav.top .wrap {
            max-width: none;
            padding: 0 clamp(14px, 1.5vw, 22px) 0 clamp(18px, 2vw, 28px);
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0;
        }

        .logo img {
            height: 40px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        nav.top ul {
            list-style: none;
            display: flex;
            gap: 4px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        nav.top ul a {
            font-size: 14px;
            color: var(--ink-soft);
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 8px;
            transition: color 180ms, background 180ms;
            position: relative;
        }

        nav.top ul a:hover {
            color: var(--ink);
            background: color-mix(in oklab, var(--ink), transparent 94%);
        }

        nav.top ul a.active {
            color: var(--ink);
        }

        @media (max-width: 760px) {
            nav.top ul {
                display: none;
            }
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: var(--bg-elev);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 180ms;
            color: var(--ink-soft);
            font-family: inherit;
        }

        .btn-icon:hover {
            background: color-mix(in oklab, var(--ink), transparent 94%);
            color: var(--ink);
            border-color: var(--ink);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 48px;
            padding: 0 22px;
            border-radius: var(--radius-pill);
            font-size: 14px;
            font-weight: 500;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 200ms ease;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--accent-ink);
        }

        .btn-primary:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            border-color: var(--ink);
            color: var(--ink);
        }

        .btn-secondary:hover {
            background: var(--ink);
            color: var(--bg);
        }

        .btn-wa {
            background: var(--success);
            color: #fff;
            border-color: transparent;
        }

        .btn-wa::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
        }

        .btn-wa:hover {
            filter: brightness(1.08);
        }

        .btn-sm {
            height: 36px;
            padding: 0 14px;
            font-size: 13px;
        }

        .hero {
            padding-top: clamp(32px, 4vw, 56px);
            padding-bottom: clamp(32px, 4vw, 56px);
        }

        .hero .wrap {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
            gap: 56px;
            align-items: center;
        }

        .hero h1 {
            font-size: clamp(40px, 5.4vw, 76px);
            line-height: 1.0;
            letter-spacing: -0.035em;
            font-weight: 600;
            margin-top: 20px;
        }

        .hero h1 em {
            font-style: normal;
            color: var(--accent);
        }

        .hero .quote {
            margin-top: 24px;
            font-size: clamp(16px, 1.2vw, 18px);
            color: var(--ink-soft);
            max-width: 38ch;
        }

        .hero .quote::before {
            content: "\201C";
            margin-right: 2px;
            color: var(--ink-mute);
        }

        .hero .quote::after {
            content: "\201D";
            margin-left: 2px;
            color: var(--ink-mute);
        }

        .hero-ctas {
            display: flex;
            gap: 10px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .hero-meta {
            margin-top: 48px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .hero-meta div {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--ink-mute);
        }

        .hero-meta strong {
            display: block;
            font-family: var(--font-ui);
            font-size: 20px;
            color: var(--ink);
            font-weight: 600;
            letter-spacing: -0.01em;
            margin-bottom: 4px;
        }

        .hero-art {
            border-radius: 28px;
            overflow: hidden;
            width: 100%;
            justify-self: center;
            background: #0d0d0d;
            display: flex;
            align-items: center;
            justify-content: center;
            max-height: 480px;
        }

        .hero-art img {
            max-width: 100%;
            max-height: 480px;
            width: auto;
            height: auto;
            display: block;
        }

        @media (max-width: 880px) {
            .hero .wrap {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-art {
                max-width: 520px;
                max-height: 340px;
                margin: 0 auto;
            }

            .hero-art img {
                max-height: 340px;
            }
        }

        @media (max-width: 540px) {
            .hero-art {
                max-width: 100%;
                max-height: 260px;
            }

            .hero-art img {
                max-height: 260px;
            }
        }

        .trust {
            margin-top: var(--section-y);
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            padding: 28px 0;
        }

        .trust .wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            flex-wrap: wrap;
        }

        .trust .label {
            font-family: var(--font-mono);
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-mute);
        }

        .trust ul {
            list-style: none;
            display: flex;
            gap: clamp(20px, 4vw, 56px);
            flex-wrap: wrap;
        }

        .trust li {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: -0.01em;
            color: var(--ink);
            opacity: 0.7;
        }

        .sec-head {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: end;
            margin-bottom: 56px;
            position: relative;
            padding-top: 24px;
        }

        .sec-head::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 24px;
            height: 1px;
            background: var(--ink);
        }

        .sec-head::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 1px;
            height: 24px;
            background: var(--ink);
        }

        .sec-head h2 {
            font-size: clamp(36px, 4.5vw, 64px);
            letter-spacing: -0.025em;
            max-width: 16ch;
        }

        .sec-head p {
            font-size: 17px;
            color: var(--ink-soft);
            max-width: 42ch;
        }

        .sec-head .meta-eyebrow {
            display: block;
            margin-bottom: 16px;
        }

        @media (max-width: 760px) {
            .sec-head {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        @media (max-width: 880px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        .svc {
            background: var(--bg-elev);
            border-radius: var(--radius-svc);
            padding: 40px 32px 36px;
            position: relative;
            transition: all 220ms ease;
            border: 1px solid transparent;
        }

        .svc-num {
            font-family: var(--font-mono);
            font-size: 13px;
            font-weight: 500;
            color: var(--ink-mute);
            margin-bottom: 96px;
        }

        .svc h3 {
            font-size: 26px;
            margin-bottom: 16px;
            letter-spacing: -0.015em;
        }

        .svc p {
            font-size: 15px;
            color: var(--ink-soft);
        }

        .svc .arr {
            position: absolute;
            right: 28px;
            top: 36px;
            opacity: 0;
            transform: translateX(-6px);
            transition: all 220ms ease;
        }

        .svc:hover .arr {
            opacity: 1;
            transform: translateX(0);
        }

        .svc:hover {
            transform: translateY(-2px);
            border-color: var(--line);
        }

        .promo {
            padding-top: var(--section-y);
        }

        .promo-card {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 32px;
            align-items: center;
            padding: clamp(28px, 4vw, 44px) clamp(28px, 4vw, 48px);
            border-radius: 24px;
            background: var(--bg-soft);
            border: 1px solid var(--line);
        }

        .promo-body {
            position: relative;
            z-index: 2;
        }

        .promo-body h3 {
            font-size: clamp(22px, 2.4vw, 30px);
            letter-spacing: -0.02em;
            line-height: 1.2;
            max-width: 32ch;
        }

        .promo-body p {
            margin-top: 8px;
            color: var(--ink-soft);
            font-size: 15px;
            max-width: 44ch;
        }

        .promo-cta {
            position: relative;
            z-index: 2;
        }

        .promo-deco {
            position: absolute;
            right: -80px;
            top: -40px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, color-mix(in oklab, var(--accent), transparent 78%) 0%, transparent 65%);
            filter: blur(20px);
            z-index: 1;
            pointer-events: none;
        }

        @media (max-width: 760px) {
            .promo-card {
                grid-template-columns: 1fr;
            }
        }

        .cat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 1100px) {
            .cat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 540px) {
            .cat-grid {
                grid-template-columns: 1fr;
            }
        }

        .cat {
            background: var(--bg-elev);
            border-radius: var(--radius-card);
            overflow: hidden;
            transition: all 220ms ease;
            border: 1px solid var(--line);
            display: flex;
            flex-direction: column;
        }

        .cat:hover {
            transform: translateY(-3px);
            border-color: var(--ink);
        }

        .cat-img {
            aspect-ratio: 1/1;
            background: var(--bg-soft);
            position: relative;
            display: grid;
            place-items: center;
            border-bottom: 1px solid var(--line);
        }

        .cat-img::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(135deg, transparent 0 16px, color-mix(in oklab, var(--ink) 3%, transparent) 16px 17px);
        }

        .cat-device {
            width: 38%;
            aspect-ratio: 9/19.5;
            border-radius: 24px;
            background: linear-gradient(160deg, #2a3f66 0%, #0a1428 100%);
            border: 4px solid #0a1428;
            position: relative;
            z-index: 1;
            box-shadow: 0 18px 36px -16px color-mix(in oklab, var(--ink), transparent 70%);
        }

        .cat-device::before {
            content: "";
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 44px;
            height: 12px;
            border-radius: 999px;
            background: #0a1428;
        }

        .cat-device--round {
            width: 32%;
            aspect-ratio: 1;
            border-radius: 50%;
        }

        .cat-device--round::before {
            display: none;
        }

        .ph-cap {
            position: absolute;
            top: 14px;
            left: 14px;
            font-family: var(--font-mono);
            font-size: 10px;
            color: var(--ink-mute);
            background: var(--bg-elev);
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            z-index: 2;
        }

        .cat-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .cat-brand {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-mute);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .cat h3 {
            font-size: 20px;
            letter-spacing: -0.015em;
            font-weight: 600;
        }

        .cat p {
            font-size: 14px;
            color: var(--ink-soft);
            margin-top: 2px;
        }

        .testimonials {
            margin-top: var(--section-y);
        }

        .t-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 1024px) {
            .t-grid {
                grid-template-columns: 1fr;
            }
        }

        .t-card {
            background: var(--bg-elev);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: all 220ms ease;
        }

        .t-card:hover {
            transform: translateY(-2px);
            border-color: var(--ink);
        }

        .t-card--feature {
            background: var(--ink);
            color: #F4F6FB;
            border-color: var(--ink);
        }

        .t-card--feature .t-quote,
        .t-card--feature .t-name {
            color: #fff;
        }

        .t-card--feature .t-meta {
            color: color-mix(in oklab, #F4F6FB, transparent 40%);
        }

        .t-card--feature .t-avatar {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.2);
        }

        .t-card--feature .t-stars {
            color: #fff;
        }

        .t-card--feature .t-author {
            border-top-color: rgba(255, 255, 255, 0.12);
        }

        .t-stars {
            font-family: var(--font-mono);
            font-size: 14px;
            letter-spacing: 0.1em;
            color: var(--accent);
        }

        .t-quote {
            font-size: 17px;
            color: var(--ink);
            line-height: 1.5;
            flex: 1;
        }

        .t-author {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }

        .t-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-soft);
            border: 1px solid var(--line);
            display: grid;
            place-items: center;
            font-weight: 600;
            font-size: 14px;
            color: var(--ink);
        }

        .t-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
        }

        .t-meta {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--ink-mute);
            letter-spacing: 0.06em;
            margin-top: 2px;
        }

        .cta-final-wrap {
            padding-top: var(--section-y);
        }

        .cta-final {
            position: relative;
            overflow: hidden;
            padding: clamp(56px, 8vw, 96px) clamp(28px, 5vw, 80px);
            text-align: center;
            border-radius: 28px;
            background: linear-gradient(180deg, color-mix(in oklab, var(--ink) 96%, transparent) 0%, var(--ink) 100%);
            color: #F4F6FB;
            border: 1px solid color-mix(in oklab, var(--ink), white 8%);
            box-shadow: 0 24px 60px -24px color-mix(in oklab, var(--ink), transparent 50%);
        }

        .cta-final .eyebrow {
            display: block;
            margin-bottom: 24px;
            color: color-mix(in oklab, #F4F6FB, transparent 50%);
            position: relative;
            z-index: 2;
        }

        .cta-final h2 {
            font-size: clamp(36px, 4.5vw, 60px);
            letter-spacing: -0.03em;
            line-height: 1.05;
            max-width: 20ch;
            margin: 0 auto;
            color: #fff;
            position: relative;
            z-index: 2;
        }

        .cta-final p {
            color: color-mix(in oklab, #F4F6FB, transparent 30%);
            font-size: 16px;
            max-width: 44ch;
            margin: 20px auto 0;
            position: relative;
            z-index: 2;
        }

        .cta-final .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 36px;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .cta-final .cta-secondary {
            background: transparent;
            border-color: color-mix(in oklab, #F4F6FB, transparent 70%);
            color: #fff;
        }

        .cta-final .cta-secondary:hover {
            background: #fff;
            color: var(--ink);
            border-color: #fff;
        }

        .cta-deco {
            position: absolute;
            inset: 0;
            background: radial-gradient(60% 60% at 100% 0%, color-mix(in oklab, var(--accent), transparent 50%) 0%, transparent 70%), radial-gradient(50% 60% at 0% 100%, color-mix(in oklab, var(--accent), transparent 75%) 0%, transparent 70%);
            z-index: 1;
            pointer-events: none;
        }

        .cta-deco::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 1px 1px, color-mix(in oklab, #fff 18%, transparent) 1px, transparent 0);
            background-size: 28px 28px;
            opacity: 0.5;
            mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
        }

        footer {
            margin-top: var(--section-y);
            padding: 72px 0 32px;
            border-top: 1px solid var(--line);
            background: var(--bg-soft);
        }

        .foot-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 56px;
        }

        @media (max-width: 880px) {
            .foot-grid {
                grid-template-columns: 1fr 1fr;
                gap: 32px;
            }
        }

        @media (max-width: 540px) {
            .foot-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }
        }

        .foot-brand .logo {
            margin-bottom: 20px;
        }

        .foot-brand p {
            color: var(--ink-soft);
            font-size: 14px;
            max-width: 32ch;
        }

        .foot-col h4 {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-mute);
            margin-bottom: 16px;
        }

        .foot-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .foot-col li {
            font-size: 14px;
            color: var(--ink);
        }

        .foot-col li.muted {
            color: var(--ink-soft);
            font-size: 13px;
        }

        .foot-col a:hover {
            color: var(--accent);
        }

        .foot-bot {
            border-top: 1px solid var(--line);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--ink-mute);
            letter-spacing: 0.04em;
            flex-wrap: wrap;
            gap: 12px;
        }

        .foot-tagline {
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .reveal {
            opacity: 0;
            transform: translateY(12px);
            transition: opacity 500ms ease-out, transform 500ms ease-out;
        }

        .reveal.in {
            opacity: 1;
            transform: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
    </style>
</head>

<body>

    <nav class="top" id="main-nav">
        <div class="wrap">
            <a href="#beranda" class="logo" aria-label="Alex Phone Banjarnegara">
                <img src="/assets/logoalex.webp" alt="Alex Phone Banjarnegara">
            </a>
            <ul role="menubar" id="nav-links">
                <li><a href="#layanan">Layanan</a></li>
                <li><a href="#katalog">Katalog</a></li>
                <li><a href="#testimoni">Testimoni</a></li>
            </ul>
            <div class="nav-actions">
                {{-- <button class="btn-icon" id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle dark mode">
        <svg id="icon-moon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg id="icon-sun" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </button> --}}
                <a class="btn btn-wa btn-sm" href="https://wa.me/+6289674886141">Chat Admin</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-sm"
                        style="height:36px;padding:0 14px;font-size:13px;background:var(--bg-elev);border:1px solid var(--line);color:var(--ink);">Dashboard
                        →</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm"
                        style="height:36px;padding:0 14px;font-size:13px;background:transparent;border:1px solid var(--line);color:var(--ink);">Login</a>
                @endauth
            </div>
        </div>
    </nav>

    <main>

        <section class="hero" id="beranda">
            <div class="wrap">
                <div class="hero-text reveal">
                    <h1>Solusi gadget<br />paling <em>update</em>,<br />harga bersahabat.</h1>
                    <p class="quote">Harga murah, pelayanan ramah. Sedia HP baru &amp; bekas, servis kilat, dan
                        aksesoris original — semua di satu konter.</p>
                    <div class="hero-ctas">
                        <a class="btn btn-primary" href="#katalog">Lihat Katalog</a>
                        <a class="btn btn-secondary" href="https://shopee.co.id/alexrizaldi">Shopee Toko →</a>
                    </div>
                    <div class="hero-meta">
                        <div><strong>1.200+</strong>Unit terjual</div>
                        <div><strong>4.9 / 5.0</strong>Rating pelanggan</div>
                        <div><strong>30 hari</strong>Garansi servis</div>
                    </div>
                </div>
                <div class="hero-art reveal">
                    <img src="/assets/alex-phone.webp" alt="Alex Phone Banjarnegara">
                </div>
            </div>
            <div class="trust">
                <div class="wrap">
                    <span class="label">Sedia unit original dari</span>
                    <ul>
                        <li>Apple</li>
                        <li>Samsung</li>
                        <li>Xiaomi</li>
                        <li>Oppo</li>
                        <li>Vivo</li>
                        <li>Realme</li>
                    </ul>
                </div>
            </div>
        </section>

        <section id="layanan" class="band">
            <div class="wrap">
                <div class="sec-head reveal">
                    <div>
                        <span class="eyebrow meta-eyebrow">— Layanan</span>
                        <h2>Tiga hal yang kami kerjakan dengan baik.</h2>
                    </div>
                    <p>Konter kecil, tapi lengkap. Dari unit baru bergaransi resmi sampai servis LCD retak, semuanya
                        ditangani langsung di tempat oleh teknisi yang sudah kenal mesinnya luar-dalam.</p>
                </div>
                <div class="services-grid">
                    <article class="svc reveal">
                        <div class="svc-num">01 / Penjualan</div>
                        <h3>HP Baru &amp; Bekas</h3>
                        <p>Sedia tukar tambah smartphone impianmu dengan jaminan harga terbaik, unit original, dan
                            bergaransi resmi distributor.</p>
                        <span class="arr">→</span>
                    </article>
                    <article class="svc reveal">
                        <div class="svc-num">02 / Servis</div>
                        <h3>Servis Kilat</h3>
                        <p>LCD retak, baterai drop, atau mati total? Ditangani teknisi berpengalaman di tempat. Banyak
                            kasus selesai di hari yang sama.</p>
                        <span class="arr">→</span>
                    </article>
                    <article class="svc reveal">
                        <div class="svc-num">03 / Aksesoris</div>
                        <h3>Aksesoris Lengkap</h3>
                        <p>Casing estetik, tempered glass premium, charger original, sampai TWS kekinian — harga
                            pelajar, kualitas tidak murahan.</p>
                        <span class="arr">→</span>
                    </article>
                </div>
            </div>
        </section>

        <section class="promo" id="promo" aria-labelledby="promo-title">
            <div class="wrap">
                <div class="promo-card reveal">
                    <div class="promo-deco" aria-hidden="true"></div>
                    <div class="promo-body">
                        <h3 id="promo-title">Beli HP minggu ini, gratis paket aksesoris premium.</h3>
                        <p>Berlaku untuk semua tipe — baru maupun second mulus. Stok terbatas.</p>
                    </div>
                    <a class="btn btn-primary promo-cta"
                        href="https://wa.me/+6289674886141?text=Halo%20Alex%20Phone%2C%20saya%20mau%20klaim%20promo%20minggu%20ini">Klaim
                        via WhatsApp →</a>
                </div>
            </div>
        </section>

        <section id="katalog" class="catalog">
            <div class="wrap">
                <div class="sec-head reveal">
                    <div>
                        <span class="eyebrow meta-eyebrow">— Yang kami jual</span>
                        <h2>Empat kategori, satu konter.</h2>
                    </div>
                    <p>Mulai dari iPhone series sampai TWS pelajar — semua tersedia di Alex Phone. Tanya admin via
                        WhatsApp untuk konfirmasi unit ready dan harga terbaru.</p>
                </div>
                <div class="cat-grid">
                    <article class="cat reveal">
                        <div class="cat-img"><span class="ph-cap">iphone-series.png</span>
                            <div class="ph-device cat-device"></div>
                        </div>
                        <div class="cat-body">
                            <div class="cat-brand">Apple</div>
                            <h3>iPhone Series</h3>
                            <p>Sedia baru &amp; second mulus.</p>
                        </div>
                    </article>
                    <article class="cat reveal">
                        <div class="cat-img"><span class="ph-cap">galaxy-series.png</span>
                            <div class="ph-device cat-device"></div>
                        </div>
                        <div class="cat-body">
                            <div class="cat-brand">Samsung</div>
                            <h3>Galaxy A &amp; S Series</h3>
                            <p>Garansi resmi SEIN.</p>
                        </div>
                    </article>
                    <article class="cat reveal">
                        <div class="cat-img"><span class="ph-cap">midrange-series.png</span>
                            <div class="ph-device cat-device"></div>
                        </div>
                        <div class="cat-body">
                            <div class="cat-brand">Xiaomi · Vivo · Oppo</div>
                            <h3>Mid-Range King</h3>
                            <p>Spek gahar, harga pelajar.</p>
                        </div>
                    </article>
                    <article class="cat reveal">
                        <div class="cat-img"><span class="ph-cap">tws-audio.png</span>
                            <div class="ph-device cat-device cat-device--round"></div>
                        </div>
                        <div class="cat-body">
                            <div class="cat-brand">Gadget Kit</div>
                            <h3>TWS &amp; Audio Premium</h3>
                            <p>Bass mantap, awet pol.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="testimoni" class="testimonials band">
            <div class="wrap">
                <div class="sec-head reveal">
                    <div><span class="eyebrow meta-eyebrow">— Testimoni</span>
                        <h2>Kata warga<br />Banjarnegara.</h2>
                    </div>
                    <p>Tiga hal yang sering muncul di review: ramah, jujur, dan cepat. Beberapa di antaranya kami pinjam
                        dari WhatsApp pelanggan.</p>
                </div>
                <div class="t-grid">
                    <article class="t-card reveal">
                        <div class="t-stars">★★★★★</div>
                        <p class="t-quote">Beli HP second di sini kondisinya mulus banget kaya baru, dapet bonus case
                            lagi. Pelayanannya ramah pol — ditanya-tanya tetep senyum.</p>
                        <div class="t-author">
                            <div class="t-avatar">R</div>
                            <div>
                                <div class="t-name">Ridwan</div>
                                <div class="t-meta">Alun-alun Banjarnegara</div>
                            </div>
                        </div>
                    </article>
                    <article class="t-card t-card--feature reveal">
                        <div class="t-stars">★★★★★</div>
                        <p class="t-quote">Servis LCD retak di sini pengerjaannya cepet dan rapi, harganya jauh lebih
                            murah dibanding tempat lain. Recommended pol.</p>
                        <div class="t-author">
                            <div class="t-avatar">S</div>
                            <div>
                                <div class="t-name">Siska</div>
                                <div class="t-meta">Wangon</div>
                            </div>
                        </div>
                    </article>
                    <article class="t-card reveal">
                        <div class="t-stars">★★★★★</div>
                        <p class="t-quote">Tukar tambah dapet harga paling oke se-Banjarnegara. Admin sabar banget
                            bantu pilih yang sesuai budget. Pulang langsung happy.</p>
                        <div class="t-author">
                            <div class="t-avatar">A</div>
                            <div>
                                <div class="t-name">Adi</div>
                                <div class="t-meta">Purwareja Klampok</div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta-final-wrap">
            <div class="wrap">
                <div class="cta-final reveal">
                    <div class="cta-deco" aria-hidden="true"></div>
                    <span class="eyebrow">— Mampir ke konter atau chat dulu</span>
                    <h2>Bingung mau pilih yang mana? Tanya dulu, gratis.</h2>
                    <p>Tim admin standby setiap hari 09:00–21:00 WIB. Tinggal chat WA, sebutkan budget &amp; kebutuhan,
                        kami bantu cari unit yang paling cocok.</p>
                    <div class="actions">
                        <a class="btn btn-wa" href="https://wa.me/+6289674886141">Chat Admin WhatsApp</a>
                        <a class="btn btn-secondary cta-secondary" href="https://shopee.co.id/alexrizaldi">Lihat di
                            Shopee →</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer id="lokasi">
        <div class="wrap">
            <div class="foot-grid">
                <div class="foot-col foot-brand">
                    <a href="#beranda" class="logo" aria-label="Alex Phone Banjarnegara">
                        <img src="/assets/logoalex.webp" alt="Alex Phone Banjarnegara">
                    </a>
                    <p>Pusat penjualan dan perbaikan smartphone terlengkap di Banjarnegara.</p>
                </div>
                <div class="foot-col">
                    <h4>Jam Operasional</h4>
                    <ul>
                        <li>Senin – Minggu</li>
                        <li>09:00 – 21:00 WIB</li>
                        <li class="muted">Hari libur tetap buka</li>
                    </ul>
                </div>
                <div class="foot-col">
                    <h4>Alamat</h4>
                    <ul>
                        <li>Jl. Raya Banjarnegara</li>
                        <li class="muted">Banjarnegara, Jawa Tengah</li>
                    </ul>
                </div>
                <div class="foot-col">
                    <h4>Sosial</h4>
                    <ul>
                        <li><a href="https://wa.me/+6289674886141">WhatsApp</a></li>
                        <li><a href="https://shopee.co.id/alexrizaldi">Shopee</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>
            <div class="foot-bot">
                <span>© 2026 Alex Phone Banjarnegara.</span>
                <span class="foot-tagline">Harga murah · Pelayanan ramah</span>
            </div>
        </div>
    </footer>

    <script>
        // Scroll reveal
        const revealIO = new IntersectionObserver((entries) => {
            for (const e of entries) {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    revealIO.unobserve(e.target);
                }
            }
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -40px 0px'
        });
        document.querySelectorAll('.reveal').forEach(el => revealIO.observe(el));

        // Active nav link on scroll
        const sections = document.querySelectorAll('section[id], footer[id]');
        const navLinks = document.querySelectorAll('#nav-links a');
        const navIO = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    navLinks.forEach(a => a.classList.remove('active'));
                    const link = document.querySelector('#nav-links a[href="#' + e.target.id + '"]');
                    if (link) link.classList.add('active');
                }
            });
        }, {
            threshold: 0.3
        });
        sections.forEach(s => navIO.observe(s));

        // Dark/light mode toggle
        const html = document.documentElement;
        const toggleBtn = document.getElementById('theme-toggle');
        const iconMoon = document.getElementById('icon-moon');
        const iconSun = document.getElementById('icon-sun');

        function updateIcons() {
            const isDark = html.getAttribute('data-theme-mode') === 'dark';
            iconMoon.style.display = isDark ? 'none' : 'block';
            iconSun.style.display = isDark ? 'block' : 'none';
        }
        updateIcons();
        toggleBtn.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme-mode') === 'dark';
            html.setAttribute('data-theme-mode', isDark ? 'light' : 'dark');
            updateIcons();
        });
    </script>
</body>

</html>
