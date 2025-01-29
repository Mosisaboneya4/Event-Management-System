document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('otpVerificationForm');
    const resultDiv = document.getElementById('verificationResult');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form values
        const ticketCode = document.getElementById('ticket_code').value.trim();
        const otp = document.getElementById('otp').value.trim();

        // Basic client-side validation
        if (!ticketCode || !otp) {
            showResult('Please enter both ticket code and OTP', 'error');
            return;
        }

        // Send AJAX request
        fetch('verify_ticket_otp.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `ticket_code=${encodeURIComponent(ticketCode)}&otp=${encodeURIComponent(otp)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showResult(data.message, 'success');
                // Optional: Redirect or update UI after successful verification
                setTimeout(() => {
                    // Could redirect to event details or reset form
                    form.reset();
                }, 2000);
            } else {
                showResult(data.message, 'error');
            }
        })
        .catch(error => {
            showResult('Network error. Please try again.', 'error');
            console.error('Error:', error);
        });
    });

    function showResult(message, type) {
        resultDiv.textContent = message;
        resultDiv.className = `result-message ${type}`;
    }
});
