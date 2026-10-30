<button {{ $attributes->merge(['type' => 'submit', 'class' => 'sg-btn-danger']) }}>
    {{ $slot }}
</button>
