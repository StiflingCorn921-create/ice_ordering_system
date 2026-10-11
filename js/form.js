/**
 * Order form: live total, delivery/pickup toggle, AI ice calculator.
 * (The total here is only a preview; the server recalculates the real price.)
 */
(function () {
    const qtyInputs = document.querySelectorAll('.qty-input');
    const fulfillment = document.getElementById('fulfillment_type');
    const addressGroup = document.getElementById('address-group');
    const addressInput = document.getElementById('delivery_address');
    const elSubtotal = document.getElementById('display_subtotal');
    const elFee = document.getElementById('display_fee');
    const elTotal = document.getElementById('display_total');

    function isDelivery() {
        return !fulfillment || fulfillment.value === 'Delivery';
    }

    function recalc() {
        let subtotal = 0;
        qtyInputs.forEach((input) => {
            const qty = Math.max(0, parseInt(input.value, 10) || 0);
            subtotal += qty * parseFloat(input.dataset.price || 0);
        });
        const fee = isDelivery() && subtotal > 0 ? parseFloat(fulfillment.dataset.deliveryFee || 0) : 0;

        elSubtotal.textContent = App.peso(subtotal);
        elFee.textContent = App.peso(fee);
        elTotal.textContent = App.peso(subtotal + fee);
    }

    function toggleAddress() {
        const show = isDelivery();
        addressGroup.style.display = show ? '' : 'none';
        addressInput.required = show;
    }

    qtyInputs.forEach((input) => input.addEventListener('input', recalc));
    if (fulfillment) {
        fulfillment.addEventListener('change', () => { toggleAddress(); recalc(); });
    }

    // Prevent double submits (double orders) on slow connections
    const form = document.getElementById('order-form');
    if (form) {
        form.addEventListener('submit', () => {
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Submitting…';
        });
    }

    toggleAddress();
    recalc();

    App.chat({ form: '#chat-form', input: '#chat-input', log: '#chat-log', mode: 'customer' });
})();

