document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to event cards
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.boxShadow = '0 6px 12px rgba(0, 0, 0, 0.15)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });

    // Quick action buttons hover effect
    const quickActionButtons = document.querySelectorAll('.quick-actions .btn');
    quickActionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.opacity = '0.9';
        });

        button.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
        });
    });

    // Optional: Add a simple notification system
    function showNotification(message, type = 'info') {
        const notificationContainer = document.createElement('div');
        notificationContainer.classList.add('notification', `notification-${type}`);
        notificationContainer.textContent = message;
        
        document.body.appendChild(notificationContainer);
        
        setTimeout(() => {
            notificationContainer.classList.add('show');
        }, 10);

        setTimeout(() => {
            notificationContainer.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notificationContainer);
            }, 500);
        }, 3000);
    }

    // Example usage (can be triggered by other events)
    // showNotification('Welcome to your dashboard!', 'success');
});
