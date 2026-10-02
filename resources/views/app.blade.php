@php($locale = $page['props']['locale'] ?? $page['props']['brief']['locale'] ?? str_replace('_', '-', app()->getLocale()))
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
        <link rel="icon" href="/icon-512.png" type="image/png" sizes="512x512">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <meta property="og:image" content="{{ url('/icon-512.png') }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ isset($meta['title']) ? $meta['title'].' - ' : '' }}{{ config('app.name', 'Laravel') }}</title>
            @isset($meta)
                <meta property="og:title" content="{{ $meta['title'] }}">
                <meta property="og:site_name" content="{{ config('app.name') }}">
                @if ($meta['description'])
                    <meta name="description" content="{{ $meta['description'] }}">
                    <meta property="og:description" content="{{ $meta['description'] }}">
                @endif
                @unless ($meta['indexable'])
                    <meta name="robots" content="noindex, nofollow">
                @endunless
            @endisset
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
