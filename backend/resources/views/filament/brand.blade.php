@php
    $setting = \App\Models\Setting::query()->first();
    $logo = $setting?->getFirstMediaUrl('logo_admin');
    $name = $setting?->brand_name ?: config('app.name');
@endphp

<div class="site-brand">
    @if ($logo)
        <img src="{{ $logo }}" alt="{{ $name }}" style="height: 2rem; width: auto;">
    @else
        <span class="site-brand__mark" aria-hidden="true">&lt;/&gt;</span>
        <span class="site-brand__name">{{ $name }}</span>
    @endif
</div>
