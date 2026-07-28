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
<script>
window.MyOKUcareI18n = {!! json_encode([
    'online' => __('js.online'),
    'offline' => __('js.offline'),
    'no_file' => __('js.no_file'),
    'show_password' => __('js.show_password'),
    'hide_password' => __('js.hide_password'),
    'logging_in' => __('js.logging_in'),
    'live_data' => __('js.live_data'),
    'connection_interrupted' => __('js.connection_interrupted'),
    'dashboard_updated' => __('js.dashboard_updated'),
    'dashboard_update_failed' => __('js.dashboard_update_failed'),
    'no_data' => __('js.no_data'),
    'no_data_copy' => __('js.no_data_copy'),
    'people' => __('js.people'),
    'loading' => __('js.loading'),
    'reload' => __('js.reload'),
    'install_title' => __('js.install_title'),
    'install_copy' => __('js.install_copy'),
    'install' => __('js.install'),
    'close_install' => __('js.close_install'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
</script>
