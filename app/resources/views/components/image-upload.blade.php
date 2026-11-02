@props([
    'name',
    'label',
    'src' => null,
    'variant' => 'photo',
    'hint' => 'JPG, JPEG, PNG ou WebP — máx. 5 MB',
])

@php
    $imgClass = $variant === 'cover'
        ? 'h-20 w-full max-w-xs rounded object-cover'
        : 'h-20 w-20 rounded-full object-cover ring-2 ring-canvas-line';
    $previewId = $name.'-preview';
@endphp

<div class="space-y-2">
    <x-input-label :for="$name" :value="$label" />
    <img
        id="{{ $previewId }}"
        @if ($src) src="{{ $src }}" @else hidden @endif
        alt=""
        class="{{ $imgClass }}"
    >
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="file"
        accept=".jpg,.jpeg,.JPG,.JPEG,.png,.webp,image/jpeg,image/jpg,image/pjpeg,image/png,image/webp"
        data-preview-id="{{ $previewId }}"
        class="mt-1 block w-full text-sm text-ink-muted"
        onchange="var img=document.getElementById(this.getAttribute('data-preview-id')); var file=this.files[0]; if(!file){return;} if(file.size>5*1024*1024){alert('A imagem deve ter no máximo 5 MB.'); this.value=''; return;} if(img){img.src=URL.createObjectURL(file); img.hidden=false;}"
    >
    <p class="text-xs text-ink-muted">{{ $hint }}</p>
    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
