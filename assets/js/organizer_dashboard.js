document.addEventListener('DOMContentLoaded', function() {
    // Event card hover effects
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.15)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });

    // Quick action buttons hover and click effects
    const quickActionButtons = document.querySelectorAll('.quick-actions .btn');
    quickActionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.opacity = '0.9';
        });

        button.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
        });

        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
        });
    });

    // Notification system (similar to attendee dashboard)
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

    // Example usage - can be triggered by various events
    // showNotification('New event created successfully!', 'success');
});
