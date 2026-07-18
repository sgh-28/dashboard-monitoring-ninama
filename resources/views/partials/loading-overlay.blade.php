<div id="global-loading-overlay" class="global-loading-overlay" aria-live="polite" aria-hidden="true">
    <div class="global-loading-card" role="status">
        <div class="global-loading-spinner" aria-hidden="true"></div>
        <div>
            <p id="global-loading-title" class="global-loading-title">Memproses data</p>
            <p class="global-loading-text">Mohon tunggu, permintaan sedang diproses.</p>
        </div>
    </div>
</div>

<style>
    .global-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(3px);
    }

    .global-loading-overlay.is-visible {
        display: flex;
    }

    .global-loading-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        width: min(100%, 24rem);
        border: 1px solid rgba(96, 165, 250, 0.35);
        border-radius: 0.75rem;
        background: #111827;
        padding: 1.25rem;
        color: #ffffff;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
    }

    .global-loading-spinner {
        width: 3rem;
        height: 3rem;
        flex: 0 0 3rem;
        border-radius: 999px;
        border: 4px solid rgba(148, 163, 184, 0.35);
        border-top-color: #2563eb;
        border-right-color: #10b981;
        animation: global-loading-spin 0.8s linear infinite;
    }

    .global-loading-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .global-loading-text {
        margin: 0.25rem 0 0;
        color: #cbd5e1;
        font-size: 0.875rem;
        line-height: 1.4;
    }

    @keyframes global-loading-spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    (function() {
        const overlay = document.getElementById('global-loading-overlay');
        const title = document.getElementById('global-loading-title');

        if (!overlay || !title) {
            return;
        }

        function showLoading(message) {
            title.textContent = message || 'Memproses data';
            overlay.classList.add('is-visible');
            overlay.setAttribute('aria-hidden', 'false');
        }

        window.showGlobalLoading = showLoading;

        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (event.defaultPrevented) {
                    return;
                }

                const method = (form.getAttribute('method') || 'GET').toUpperCase();

                if (method === 'GET' || form.dataset.loading === 'false') {
                    return;
                }

                const submitter = document.activeElement && document.activeElement.matches('button, input[type="submit"]')
                    ? document.activeElement
                    : form.querySelector('[type="submit"]');
                const message = form.dataset.loadingMessage
                    || submitter?.dataset.loadingMessage
                    || submitter?.textContent?.trim()
                    || 'Memproses data';

                showLoading(message);

                form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(button) {
                    button.disabled = true;
                    button.classList.add('opacity-70', 'cursor-not-allowed');
                });
            });
        });

        document.querySelectorAll('a[data-loading]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                if (event.defaultPrevented || link.target === '_blank' || link.getAttribute('href') === '#') {
                    return;
                }

                showLoading(link.dataset.loadingMessage || link.textContent.trim() || 'Memuat halaman');
            });
        });
    })();
</script>
