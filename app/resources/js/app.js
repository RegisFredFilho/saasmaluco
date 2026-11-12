import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('discoveryInfinite', ({ nextUrl = null } = {}) => ({
        nextUrl,
        loading: false,

        async loadMore() {
            if (!this.nextUrl || this.loading) {
                return;
            }

            this.loading = true;

            try {
                const url = new URL(this.nextUrl, window.location.origin);
                url.searchParams.set('fragment', '1');

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error('Falha ao carregar mais resultados');
                }

                const html = await response.text();
                if (html.trim() !== '') {
                    this.$refs.list.insertAdjacentHTML('beforeend', html);
                }

                const headerNext = response.headers.get('X-Next-Page-Url');
                this.nextUrl = headerNext && headerNext !== '' ? headerNext : null;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
    }));
});

Alpine.start();
