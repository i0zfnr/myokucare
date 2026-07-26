<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('icons/favicon-64.png') }}">
<link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
<meta name="application-name" content="MyOKUcare">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="MyOKUcare">
<meta name="mobile-web-app-capable" content="yes">
<meta name="guideline-version" content="{{ config('app.guideline_version', '1') }}">
<meta name="guideline-completed" content="{{ auth()->user()?->has_completed_guideline ? '1' : '0' }}">
<meta name="guideline-authenticated" content="{{ auth()->check() ? '1' : '0' }}">
<meta name="guideline-track-url" content="{{ route('guideline.track') }}">
