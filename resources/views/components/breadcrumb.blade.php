@props(['items' => []])

@if (count($items))
    <nav aria-label="Ruta de navegación" class="mb-3">
        <ol class="breadcrumb breadcrumb-pk mb-0">
            @foreach ($items as $item)
                @if (! $loop->last && ! empty($item['url']))
                    <li class="breadcrumb-item">
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item active" @if ($loop->last) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
