<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
    @foreach ($covers as $cover)
        <button
            type="button"
            class="group relative overflow-hidden rounded-xl border border-canvas-line bg-canvas-elev text-left"
            :class="isSelected(@js($cover['image'])) ? 'ring-2 ring-accent' : ''"
            @click="toggle(@js($cover['image']))"
        >
            <img src="{{ $cover['image'] }}" alt="{{ $cover['title'] }}" class="aspect-square w-full object-cover transition group-hover:scale-105" loading="lazy">
            <span class="absolute inset-x-0 bottom-0 bg-black/60 px-2 py-1 text-xs text-white truncate">{{ $cover['title'] }}</span>
        </button>
    @endforeach
</div>
