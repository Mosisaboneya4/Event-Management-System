document.addEventListener('DOMContentLoaded', function() {
    // Real-time dashboard updates
    function fetchDashboardUpdates() {
        fetch('dashboard_updates.php')
            .then(response => response.json())
            .then(data => {
                // Update dashboard statistics
                updateStatistics(data);
                
                // Update recent events
                updateRecentEvents(data.recent_events);
                
                // Update user roles chart
                updateUserRolesChart(data.user_roles_breakdown);
            })
            .catch(error => {
                console.error('Dashboard update error:', error);
            });
    }

    function updateStatistics(data) {
        const statCards = document.querySelectorAll('.stat-card');
        const statsMap = {
            'Total Users': data.total_users,
            'Total Events': data.total_events,
            'Total Tickets': data.total_tickets,
            'Total Revenue': `$${data.total_revenue.toFixed(2)}`
        };

        statCards.forEach(card => {
            const title = card.querySelector('h3').textContent;
            const valueElement = card.querySelector('p');
            
            if (statsMap[title] !== undefined) {
                valueElement.textContent = statsMap[title];
            }
        });
    }

    function updateRecentEvents(events) {
        const tableBody = document.querySelector('.recent-events tbody');
        tableBody.innerHTML = ''; // Clear existing rows

        events.forEach(event => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${escapeHtml(event.name)}</td>
                <td>${formatDate(event.date)}</td>
                <td>${event.tickets_sold}</td>
                <td>$${event.revenue.toFixed(2)}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    function updateUserRolesChart(rolesData) {
        const ctx = document.getElementById('userRolesChart');
        if (ctx && window.Chart) {
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.data.labels = Object.keys(rolesData);
                existingChart.data.datasets[0].data = Object.values(rolesData);
                existingChart.update();
            }
        }
    }

    // Utility functions
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    // Sidebar navigation highlighting
    function highlightCurrentPage() {
        const currentPath = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar nav ul li a');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href').split('/').pop();
            if (href === currentPath) {
                link.closest('li').classList.add('active');
            } else {
                link.closest('li').classList.remove('active');
            }
        });
    }

    // Periodic dashboard updates (every 5 minutes)
    setInterval(fetchDashboardUpdates, 5 * 60 * 1000);

    // Initial setup
    highlightCurrentPage();
});
