<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sg-btn-primary']) }}>
    {{ $slot }}
</button>
