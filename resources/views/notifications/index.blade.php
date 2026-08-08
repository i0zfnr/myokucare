@extends('layout', ['title' => __('notifications.page_title')])

@section('content')
<div class="page-heading notification-heading">
    <div>
        <p class="eyebrow">MyOKUcare</p>
        <h2>{{ __('notifications.page_title') }}</h2>
        <p>{{ __('notifications.page_intro') }}</p>
    </div>
    @if(auth()->user()->unreadNotifications()->exists())
        <form method="post" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn" type="submit">{{ __('notifications.mark_all_read') }}</button>
        </form>
    @endif
</div>

<section class="notification-list" aria-label="{{ __('notifications.page_title') }}">
    @forelse($notifications as $notification)
        @php
            $data = $notification->data;
            $parameters = $data['parameters'] ?? [];
            foreach ($parameters as $key => $value) {
                if (str_ends_with($key, '_key') && is_string($value)) {
                    $parameters[str($key)->beforeLast('_key')->toString()] = __($value);
                    unset($parameters[$key]);
                }
            }
        @endphp
        <article class="notification-card {{ $notification->read_at ? '' : 'unread' }}">
            <span class="notification-indicator" aria-hidden="true"></span>
            <div>
                <div class="notification-card-head">
                    <strong>{{ __($data['title_key'] ?? 'notifications.default_title', $parameters) }}</strong>
                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                </div>
                <p>{{ __($data['message_key'] ?? 'notifications.default_message', $parameters) }}</p>
            </div>
            <a class="btn btn-primary" href="{{ route('notifications.read', $notification) }}">{{ __('notifications.view') }}</a>
        </article>
    @empty
        <div class="panel-empty"><strong>{{ __('notifications.empty_title') }}</strong><p>{{ __('notifications.empty_message') }}</p></div>
    @endforelse
</section>

{{ $notifications->links('components.pagination') }}
@endsection
