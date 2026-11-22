const formatMoney = (amount) => amount.toLocaleString('es-MX', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const updateCartTotal = () => {
    let total = 0;
    document.querySelectorAll('.cart-line').forEach((line) => {
        const quantityInput = line.querySelector('[data-cart-quantity]');
        if (!quantityInput || quantityInput.disabled) {
            return;
        }
        const quantity = Number.parseInt(quantityInput.value, 10);
        const unitPrice = Number.parseFloat(line.dataset.unitPrice);
        if (!Number.isFinite(quantity) || !Number.isFinite(unitPrice)) {
            return;
        }
        const subtotal = quantity * unitPrice;
        total += subtotal;
        const lineTotal = line.querySelector('[data-line-total]');
        if (lineTotal) {
            lineTotal.textContent = `$${formatMoney(subtotal)}`;
        }
    });
    const totalDisplay = document.querySelector('[data-cart-total]');
    if (totalDisplay) {
        totalDisplay.textContent = `$${formatMoney(total)}`;
    }
};

document.querySelectorAll('[data-cart-quantity]').forEach((input) => {
    input.addEventListener('input', updateCartTotal);
});
