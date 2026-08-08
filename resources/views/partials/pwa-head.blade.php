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
<meta name="push-authenticated" content="{{ auth()->check() ? '1' : '0' }}">
@auth
<meta name="push-config-url" content="{{ route('push.config') }}">
<meta name="push-subscribe-url" content="{{ route('push.subscriptions.store') }}">
<meta name="push-unsubscribe-url" content="{{ route('push.subscriptions.destroy') }}">
@endauth
<script>
window.MyOKUcareI18n = {!! json_encode([
    'online' => __('js.online'),
    'offline' => __('js.offline'),
    'online_required' => __('js.online_required'),
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
    'push_title' => __('push.prompt_title'),
    'push_copy' => __('push.prompt_copy'),
    'push_enable' => __('push.enable'),
    'push_disable' => __('push.disable'),
    'push_not_now' => __('push.not_now'),
    'push_enabled' => __('push.enabled'),
    'push_disabled' => __('push.disabled'),
    'push_denied' => __('push.denied'),
    'push_unavailable' => __('push.unavailable'),
    'push_failed' => __('push.failed'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
</script>
