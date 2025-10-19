@props([
    'route' => '',
    'url' => '',
    'title' => '',
    'icon' => '',
    'active' => false,
    'id' => '',
    'activeRoute' => '',
])

@php
    $link = $route ? route($route) : $url;
    $isActive = $active ?: $route && request()->routeIs($activeRoute);
@endphp

<div class="menu-item">
    <a class="menu-link {{ $isActive ? 'active' : '' }}" href="{{ $link }}" {{ $id ? 'id=' . $id : '' }}
        {{ $attributes }}>
        @if ($icon)
            <span class="menu-icon">
                <i class="{{ $icon }}">
                    @if (str_contains($icon, 'ki-duotone'))
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    @endif
                </i>
            </span>
        @endif
        <span class="menu-title">{{ $title }}</span>
    </a>
</div>
