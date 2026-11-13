@props(['type', 'label' => null])

<span {{ $attributes->class(['type-badge', 'type-badge--'.$type]) }}>
    {{ $label ?? config('pokemon.type_labels.'.$type, ucfirst($type)) }}
</span>
