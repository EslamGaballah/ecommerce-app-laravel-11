{{-- Runs before the page is drawn, so a dark page never flashes white first. --}}
<script>
    (function () {
        const enabled = @js((bool) Setting::get('dark_mode_enabled'));
        const fallback = @js(Setting::get('default_theme'));
        const root = document.documentElement;
        let chosen = null;

        try { chosen = localStorage.getItem('theme'); } catch (error) {}

        function resolve(theme) {
            if (theme === 'system') {
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            return theme === 'dark' ? 'dark' : 'light';
        }

        root.setAttribute('data-theme', enabled ? resolve(chosen || fallback) : 'light');

        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-theme-toggle]')) return;

            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

            root.setAttribute('data-theme', next);

            try { localStorage.setItem('theme', next); } catch (error) {}
        });
    })();
</script>
