document.addEventListener('DOMContentLoaded', function() {
    // Event listeners for edit and delete buttons
    const editButtons = document.querySelectorAll('.btn-edit');
    const deleteButtons = document.querySelectorAll('.btn-delete');

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            // Placeholder for edit functionality
            console.log('Edit event:', eventId);
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            if (confirm('Are you sure you want to delete this event?')) {
                // Placeholder for delete functionality
                console.log('Delete event:', eventId);
            }
        });
    });
});
