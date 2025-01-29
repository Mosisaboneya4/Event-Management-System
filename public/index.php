<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .error-message {
            background-color: #ffdddd;
            color: #ff0000;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .input-group label {
            position: absolute;
            top: -10px;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 12px;
            color: #666;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            user-select: none;
        }

        .password-toggle:hover {
            color: #333;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .role-select {
            margin-bottom: 20px;
            position: relative;
        }

        .role-select select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            appearance: none;
            background: url('data:image/svg+xml;utf8,<svg fill="%23333" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>') no-repeat right 10px center;
            background-size: 30px auto;
        }

        .role-select label {
            position: absolute;
            top: -10px;
            left: 10px;
            background: white;
            padding: 0 5px;
            font-size: 12px;
            color: #666;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .signup-link {
            margin-top: 20px;
            font-size: 14px;
        }

        .signup-link a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Event Management System</h2>
        
        <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
            <div class="success-message">
                Registration successful! Please login with your credentials.
            </div>
        <?php endif; ?>

        <form id="loginForm" action="../src/auth/login_handler.php" method="POST">
            <div class="input-group role-select">
                <label for="userRole">User Role</label>
                <select id="userRole" name="user_role" required>
                    <option value="">Select User Role</option>
                    <option value="admin">System Admin</option>
                    <option value="attendee">Attendee</option>
                    <option value="organizer">Event Organizer</option>
                    <option value="staff">Event Staff</option>
                </select>
            </div>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle">Show</span>
            </div>

            <div class="forgot-password">
                <a href="#">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn">Login</button>

            <div class="signup-link">
                Don't have an account? <a href="attendee/register.php?start=1">Sign Up</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle URL parameters for error messages
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const username = urlParams.get('username') || '';

            // Populate username if returned from login attempt
            if (username) {
                document.getElementById('username').value = username;
            }

            // Password visibility toggle
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.querySelector('.password-toggle');

            passwordToggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.textContent = 'Hide';
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.textContent = 'Show';
                }
            });

            // Display error messages
            let errorMessage = '';
            switch(error) {
                case 'missing_fields':
                    errorMessage = 'Please fill in all fields.';
                    break;
                case 'incorrect_password':
                    errorMessage = 'Incorrect password. Please try again.';
                    break;
                case 'user_not_found':
                    errorMessage = 'User not found. Check your username and role.';
                    break;
                case 'invalid_role':
                    errorMessage = 'Invalid user role selected.';
                    break;
                case 'system_error':
                    errorMessage = 'A system error occurred. Please try again later.';
                    break;
                case 'registration':
                    if (urlParams.get('registration') === 'success') {
                        const successDiv = document.createElement('div');
                        successDiv.className = 'success-message';
                        successDiv.textContent = 'Registration successful! Please login with your credentials.';
                        const form = document.getElementById('loginForm');
                        form.insertBefore(successDiv, form.firstChild);
                    }
                    break;
            }

            // Display error if exists
            if (errorMessage) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = errorMessage;
                const form = document.getElementById('loginForm');
                form.insertBefore(errorDiv, form.firstChild);
            }
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const role = document.getElementById('userRole').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!role) {
                e.preventDefault();
                alert('Please select a user role');
            }
        });
    </script>
</body>
</html>
