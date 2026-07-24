@props(['icon', 'title'])

<article class="feature-card card-lift card-lift-border" aria-labelledby="feature-{{ $icon }}">
    <span class="feature-symbol card-lift-icon" aria-hidden="true"><x-dashboard-icon :name="$icon" /></span>
    <h3 id="feature-{{ $icon }}">{{ $title }}</h3>
    <p>{{ $slot }}</p>
</article>
