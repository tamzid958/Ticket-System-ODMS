@php
    $state = $getState();
    $lineClamp = $getLineClamp() ?? 3;
@endphp

<div
    x-data="{ expanded: false }"
    class="space-y-1"
>
    <div
        x-bind:style="expanded ? '' : '-webkit-line-clamp: {{ $lineClamp }}; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;'"
        class="text-gray-700 fi-prose"
    >
        {!! $state !!}
    </div>

    <button
        type="button"
        class="text-primary-600 text-sm hover:underline"
        x-on:click="expanded = !expanded"
        x-text="expanded ? 'See less' : 'See more'"
    ></button>
</div>
