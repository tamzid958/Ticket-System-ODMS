@php
    $state = $getState();
    $lineClamp = $getLineClamp() ?? 3;
@endphp

<div
    x-data="{
        expanded: false,
        clamped: false,
        checkClamped(el, clamp) {
            // First reset styles to measure full height
            el.style.removeProperty('-webkit-line-clamp')
            el.style.removeProperty('display')
            el.style.removeProperty('-webkit-box-orient')
            el.style.removeProperty('overflow')

            const fullHeight = el.scrollHeight

            // Apply clamp
            el.style['-webkit-line-clamp'] = clamp
            el.style.display = '-webkit-box'
            el.style['-webkit-box-orient'] = 'vertical'
            el.style.overflow = 'hidden'

            this.$nextTick(() => {
                const clampedHeight = el.offsetHeight
                this.clamped = fullHeight > clampedHeight
            })
        }
    }"
    x-init="checkClamped($refs.text, {{ $lineClamp }})"
    class="space-y-1"
>
    <div
        x-ref="text"
        x-bind:style="expanded ? '' : '-webkit-line-clamp: {{ $lineClamp }}; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden;'"
        class="text-gray-700 fi-prose"
    >
        {!! $state !!}
    </div>

    <button
        x-show="clamped"
        type="button"
        class="text-primary-600 text-sm hover:underline"
        x-on:click="expanded = !expanded"
        x-text="expanded ? 'See less' : 'See more'"
    ></button>
</div>
