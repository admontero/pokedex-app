@props(['title', 'description' => null, 'image' => null])

<div class="empty-state">
    @if ($image)
        <img class="empty-state__image" src="{{ $image }}" alt="" width="200" height="200" loading="lazy" decoding="async">
    @endif

    <h2 class="h4 fw-bold">{{ $title }}</h2>

    @if ($description)
        <p class="text-muted-pk mb-4">{{ $description }}</p>
    @endif

    <div class="d-flex flex-wrap justify-content-center gap-2">
        {{ $slot }}
    </div>
</div>
