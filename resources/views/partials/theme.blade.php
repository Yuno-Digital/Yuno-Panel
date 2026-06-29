<meta name="theme-preference" content="{{ auth()->user()->theme ?? 'system' }}">
<script>
    // Apply the user's theme as early as possible to avoid a flash of the wrong
    // colours. Preference order: localStorage (instant, per-device) -> the
    // server-stored value -> the OS setting ("system").
    (function () {
        const meta = document.querySelector('meta[name=theme-preference]');
        const serverPref = meta ? meta.content : 'system';

        function isDark(pref) {
            return pref === 'dark'
                || (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        }

        function apply(pref) {
            document.documentElement.classList.toggle('dark', isDark(pref));
        }

        apply(localStorage.getItem('theme') || serverPref);

        // Allow the profile form to update the theme live.
        window.__setTheme = function (pref) {
            localStorage.setItem('theme', pref);
            apply(pref);
        };

        // React to OS changes while in "system" mode.
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
            const pref = localStorage.getItem('theme') || serverPref;
            if (pref === 'system') apply('system');
        });
    })();
</script>
