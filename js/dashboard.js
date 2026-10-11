/**
 * Used on two "dashboard" pages:
 *  - public/dashboard.php (order tracking): auto-refresh while the order is still in progress
 *  - admin/index.php: business assistant chat
 */
(function () {
    // Public tracking page
    const result = document.getElementById('order-result');
    if (result && !['Completed', 'Cancelled'].includes(result.dataset.status)) {
        setTimeout(() => window.location.reload(), 30000);
    }

    // Admin dashboard assistant
    App.chat({ form: '#admin-chat-form', input: '#admin-chat-input', log: '#admin-chat-log', mode: 'admin' });
})();

