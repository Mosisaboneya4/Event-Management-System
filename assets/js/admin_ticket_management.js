document.addEventListener('DOMContentLoaded', function() {
    // Placeholder for ticket management interactions
    const ticketsTable = document.querySelector('.tickets-table');

    // Optional: Add sorting functionality
    const tableHeaders = ticketsTable.querySelectorAll('thead th');
    tableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const columnIndex = Array.from(tableHeaders).indexOf(this);
            sortTable(columnIndex);
        });
    });

    function sortTable(columnIndex) {
        const table = document.querySelector('.tickets-table');
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        
        rows.sort((a, b) => {
            const cellA = a.querySelectorAll('td')[columnIndex].textContent;
            const cellB = b.querySelectorAll('td')[columnIndex].textContent;
            return cellA.localeCompare(cellB);
        });

        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    }
});
