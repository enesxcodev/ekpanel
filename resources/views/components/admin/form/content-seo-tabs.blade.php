@props([
    'id',
    'contentLabel',
    'seoLabel' => 'SEO ayarları',
    'contentIcon' => 'file',
    'seoIcon' => 'search',
    'contentDescription' => null,
    'seoDescription' => null,
])
@php($seoActive = $errors->hasAny(['meta_title', 'meta_keywords', 'meta_description']))
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $seoActive ? '' : 'active' }}" id="{{ $id }}-content-tab" data-bs-toggle="tab" data-bs-target="#{{ $id }}-content" type="button" role="tab" aria-controls="{{ $id }}-content" aria-selected="{{ $seoActive ? 'false' : 'true' }}">
            <span class="admin-tab-icon"><x-admin.icon :name="$contentIcon" /></span>
            <span class="admin-tab-text">
                <span class="admin-tab-title">{{ $contentLabel }}</span>
                @if ($contentDescription)<span class="admin-tab-description">{{ $contentDescription }}</span>@endif
            </span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $seoActive ? 'active' : '' }}" id="{{ $id }}-seo-tab" data-bs-toggle="tab" data-bs-target="#{{ $id }}-seo" type="button" role="tab" aria-controls="{{ $id }}-seo" aria-selected="{{ $seoActive ? 'true' : 'false' }}">
            <span class="admin-tab-icon"><x-admin.icon :name="$seoIcon" /></span>
            <span class="admin-tab-text">
                <span class="admin-tab-title">{{ $seoLabel }}</span>
                @if ($seoDescription)<span class="admin-tab-description">{{ $seoDescription }}</span>@endif
            </span>
        </button>
    </li>
</ul>
<div class="tab-content" data-form-tabs>
    <div class="tab-pane {{ $seoActive ? '' : 'active show' }}" id="{{ $id }}-content" role="tabpanel" aria-labelledby="{{ $id }}-content-tab" tabindex="0">{{ $slot }}</div>
    <div class="tab-pane {{ $seoActive ? 'active show' : '' }}" id="{{ $id }}-seo" role="tabpanel" aria-labelledby="{{ $id }}-seo-tab" tabindex="0">{{ $seo }}</div>
</div>
