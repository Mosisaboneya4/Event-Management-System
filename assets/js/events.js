document.addEventListener('DOMContentLoaded', function() {
    const eventSearch = document.getElementById('event-search');
    const categoryFilter = document.getElementById('category-filter');
    const eventCards = document.querySelectorAll('.event-card');

    // Search and filter functionality
    function filterEvents() {
        const searchTerm = eventSearch.value.toLowerCase().trim();
        const selectedCategory = categoryFilter.value.toLowerCase();

        eventCards.forEach(card => {
            const eventName = card.querySelector('h2').textContent.toLowerCase();
            const eventCategory = card.dataset.category.toLowerCase();
            const isSearchMatch = eventName.includes(searchTerm);
            const isCategoryMatch = selectedCategory === '' || eventCategory.includes(selectedCategory);

            if (isSearchMatch && isCategoryMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Add event listeners for search and filter
    eventSearch.addEventListener('input', filterEvents);
    categoryFilter.addEventListener('change', filterEvents);
});
