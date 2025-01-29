document.addEventListener('DOMContentLoaded', function() {
    const ticketQuantitySelect = document.getElementById('ticket_quantity');
    const totalCostSpan = document.getElementById('total-cost');
    const ticketPrice = parseFloat(totalCostSpan.textContent.replace('$', ''));

    // Update total cost when ticket quantity changes
    ticketQuantitySelect.addEventListener('change', function() {
        const quantity = parseInt(this.value);
        const totalCost = (ticketPrice * quantity).toFixed(2);
        totalCostSpan.textContent = `$${totalCost}`;
    });
});
