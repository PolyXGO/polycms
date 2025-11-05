<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <title>PolyCMS Admin</title>
    <script>
        // Initialize theme before Vue app loads to prevent flash
        (function() {
            const themeMode = localStorage.getItem('theme_mode') || 'system';
            let isDark = false;

            if (themeMode === 'system') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    isDark = true;
                }
            } else {
                isDark = themeMode === 'dark';
            }

            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/js/admin/main.ts', 'resources/css/app.css'])
</head>
<body class="antialiased">
    <div id="polycms-admin-app"></div>
</body>
</html>
