document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const closeBtn = document.querySelector('.close');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');

    // Open modal for adding user
    addUserBtn.addEventListener('click', function() {
        modalTitle.textContent = 'Add New User';
        userForm.reset();
        document.getElementById('password').required = true;
        modal.style.display = 'block';
    });

    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Edit user buttons
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const row = this.closest('tr');
            
            // Populate form with existing data
            document.getElementById('userId').value = userId;
            document.getElementById('username').value = row.children[1].textContent;
            document.getElementById('email').value = row.children[2].textContent;
            document.getElementById('role').value = row.children[3].textContent.toLowerCase();
            document.getElementById('status').value = row.children[4].textContent.toLowerCase();
            
            // Update modal
            modalTitle.textContent = 'Edit User';
            document.getElementById('password').required = false;
            modal.style.display = 'block';
        });
    });

    // Delete user buttons
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('user_management.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete_user&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') {
                        // Remove row from table
                        this.closest('tr').remove();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the user');
                });
            }
        });
    });

    // Form submission
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(userForm);
        const action = document.getElementById('userId').value ? 'update_user' : 'create_user';
        formData.append('action', action);

        fetch('user_management.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                modal.style.display = 'none';
                location.reload(); // Refresh page to show updated data
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the user');
        });
    });
});
