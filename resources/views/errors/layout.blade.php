<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') - Move2AD</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
    {{-- Self-contained on purpose: an error page must not depend on the app's build. --}}
    <style>
        :root { --brand: oklch(0.5 0.1 195); --sand: oklch(0.96 0.02 85); --fg: #171717; --muted: #737373; --bg: #fff; --border: #ececec; }
        @media (prefers-color-scheme: dark) {
            :root { --brand: oklch(0.74 0.11 195); --sand: oklch(0.22 0.015 85); --fg: #fafafa; --muted: #a3a3a3; --bg: #0a0a0a; --border: #262626; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; flex-direction: column; background: var(--bg); color: var(--fg);
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        header { border-bottom: 1px solid var(--border); }
        nav, .wrap { max-width: 64rem; margin: 0 auto; padding: 0 1rem; }
        nav { height: 3.5rem; display: flex; align-items: center; }
        .logo { display: flex; align-items: center; gap: .5rem; font-weight: 600; color: inherit; text-decoration: none; }
        .mark { display: flex; width: 1.75rem; height: 1.75rem; align-items: center; justify-content: center; border-radius: .375rem;
            background: var(--brand); color: #fff; font-size: .75rem; font-weight: 700; }
        main { flex: 1; background: var(--sand); }
        .wrap { padding-top: 4rem; padding-bottom: 4rem; }
        .code { color: var(--brand); font-weight: 600; margin: 0; }
        h1 { font-size: clamp(2rem, 6vw, 3rem); line-height: 1.1; letter-spacing: -0.03em; margin: .5rem 0 0; }
        p.lead { color: var(--muted); font-size: 1.125rem; max-width: 36rem; margin: 1rem 0 0; }
        .ar { margin-top: 1.5rem; }
        .actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .btn { display: inline-block; border-radius: .5rem; padding: .625rem 1rem; font-size: .875rem; font-weight: 500; text-decoration: none;
            border: 1px solid var(--border); background: var(--bg); color: var(--fg); }
        .btn.primary { background: var(--brand); border-color: var(--brand); color: #fff; }
        .btn:focus-visible { outline: 3px solid var(--brand); outline-offset: 2px; }
    </style>
</head>
<body>
    <header><nav><a class="logo" href="{{ url('/') }}"><span class="mark">M2</span>Move2AD</a></nav></header>
    <main>
        <div class="wrap">
            <p class="code">@yield('code')</p>
            <h1>@yield('title')</h1>
            <p class="lead">@yield('message')</p>
            <p class="lead ar" lang="ar" dir="rtl">@yield('message_ar')</p>
            <div class="actions">
                <a class="btn primary" href="{{ url('/') }}">Get your Abu Dhabi brief</a>
                <a class="btn" href="{{ url('/check') }}">Check a job offer</a>
            </div>
        </div>
    </main>
</body>
</html>
