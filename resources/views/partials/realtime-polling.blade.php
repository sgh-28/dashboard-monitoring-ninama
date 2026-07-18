<script>
    (function() {
        const realtimeUrl = @json(route('realtime.version'));
        const intervalMs = 10000;
        const ignoredPaths = ['/create', '/edit', '/submit'];
        let currentVersion = sessionStorage.getItem('ninamaRealtimeVersion');
        let isChecking = false;

        function shouldPauseRealtime() {
            if (document.visibilityState !== 'visible') {
                return true;
            }

            if (ignoredPaths.some(function(path) {
                return window.location.pathname.includes(path);
            })) {
                return true;
            }

            const activeElement = document.activeElement;
            if (!activeElement) {
                return false;
            }

            return ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeElement.tagName);
        }

        async function checkRealtimeVersion() {
            if (isChecking || shouldPauseRealtime()) {
                return;
            }

            isChecking = true;

            try {
                const response = await fetch(realtimeUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                if (!data.version) {
                    return;
                }

                if (!currentVersion) {
                    currentVersion = data.version;
                    sessionStorage.setItem('ninamaRealtimeVersion', data.version);
                    return;
                }

                if (currentVersion !== data.version) {
                    sessionStorage.setItem('ninamaRealtimeVersion', data.version);
                    window.location.reload();
                }
            } catch (error) {
                // Biarkan halaman tetap jalan kalau koneksi polling gagal sesaat.
            } finally {
                isChecking = false;
            }
        }

        setInterval(checkRealtimeVersion, intervalMs);
        checkRealtimeVersion();
    })();
</script>
