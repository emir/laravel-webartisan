<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Webartisan &mdash; Laravel Terminal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery.terminal/2.45.2/css/jquery.terminal.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/webartisan/webartisan.css') }}" rel="stylesheet">
</head>
<body data-theme="{{ $theme ?? 'dark' }}">
    <div id="webartisan-header">
        <div class="header-left">
            <span class="header-dot red"></span>
            <span class="header-dot yellow"></span>
            <span class="header-dot green"></span>
            <span class="header-title">Webartisan</span>
        </div>
        <div class="header-right">
            <span class="header-env">{{ app()->environment() }}</span>
            <span class="header-version">v{{ $version ?? '2.0.0' }}</span>
        </div>
    </div>
    <div id="webartisan"></div>

    <script>
        window.WebartisanConfig = {
            runUrl: "{{ route('webartisan.run') }}",
            commandsUrl: "{{ route('webartisan.commands') }}",
            csrfToken: "{{ csrf_token() }}",
            exitUrl: "{{ url('/') }}",
            version: "{{ $version ?? '2.0.0' }}",
            environment: "{{ app()->environment() }}"
        };
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.terminal/2.45.2/js/jquery.terminal.min.js"></script>
    <script src="{{ asset('vendor/webartisan/app.js') }}"></script>
</body>
</html>
