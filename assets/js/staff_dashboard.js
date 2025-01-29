document.addEventListener('DOMContentLoaded', function() {
    // Event card hover and interaction effects
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

    // Check-in card hover effects
    const checkInCards = document.querySelectorAll('.check-in-card');
    checkInCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#e9ecef';
            this.style.transform = 'scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '#f1f3f5';
            this.style.transform = 'scale(1)';
        });
    });

    // Quick action buttons interactivity
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

    // Notification system
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

    // Real-time check-in status update (simulated)
    function updateCheckInStatus() {
        const checkInStats = document.querySelectorAll('.ticket-stats');
        checkInStats.forEach(stat => {
            const [checkedIn, total] = stat.textContent.match(/\d+/g);
            const percentage = (checkedIn / total * 100).toFixed(1);
            
            if (percentage > 50) {
                stat.style.backgroundColor = '#d4edda';
                stat.style.color = '#28a745';
            } else if (percentage > 25) {
                stat.style.backgroundColor = '#fff3cd';
                stat.style.color = '#ffc107';
            } else {
                stat.style.backgroundColor = '#f8d7da';
                stat.style.color = '#dc3545';
            }
        });
    }

    // Initial check-in status update
    updateCheckInStatus();

    // Optional: Periodic status update (simulated)
    // setInterval(updateCheckInStatus, 30000);
});
