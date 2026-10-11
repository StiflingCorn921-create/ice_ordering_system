/**
 * Admin-wide JS.
 */
(function () {
    // Ask before cancelling an order (cancelling returns stock and can't be undone)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-confirm-cancel]');
        if (!btn) return;
        const select = btn.form.querySelector('select[name="status"]');
        if (select && select.value === 'Cancelled' && !confirm('Cancel this order? Stock will be returned and this cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Auto-refresh the orders table (same idea as the old admin_dashboard.php),
    // but skip refreshing while the admin is interacting with a dropdown/input.
    const container = document.querySelector('[data-autorefresh]');
    if (!container) return;

    const interval = parseInt(container.dataset.autorefresh, 10) || 15000;
    let busy = false;
    container.addEventListener('focusin', () => { busy = true; });
    container.addEventListener('focusout', () => { busy = false; });

    setInterval(async () => {
        if (busy || document.hidden) return;
        try {
            const res = await fetch(window.location.href, { credentials: 'same-origin' });
            if (!res.ok || res.redirected) return; // e.g. session expired
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const fresh = doc.querySelector('[data-autorefresh]');
            if (fresh && !busy) container.innerHTML = fresh.innerHTML;
        } catch (err) {
            console.error('Auto-refresh failed:', err);
        }
    }, interval);
})();

