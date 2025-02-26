<?php
session_start();
require_once '../../config/database.php';
require_once '../../src/admin_functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$conn = getDatabaseConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process system settings updates
    $settings = [
        'site_name' => $_POST['site_name'] ?? '',
        'email_from' => $_POST['email_from'] ?? '',
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
    ];

    // In a real implementation, you'd save these to a settings table or config file
    // For now, we'll just simulate the process
    $success_message = "Settings updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - EMS Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_system_settings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
    <style>
.sidebar {
    position: fixed;
    left: 0;
    overflow-y: auto;
    z-index: 1000;
}

.admin-main {
    margin-left: 250px;
    flex: 1;
    padding: 24px;
    min-height: 100vh;
    background: #f2f2f7;
}
.system-settings-container {
    max-width: 1000px;
    margin: 0 auto;
}

.settings-card {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    margin: 20px 0;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.settings-section {
    padding: 24px;
    border-bottom: 1px solid #e5e5ea;
}

.settings-section:last-child {
    border-bottom: none;
}

.section-header {
    font-size: 20px;
    color: #1c1c1e;
    margin-bottom: 24px;
    font-weight: 600;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.form-group {
    position: relative;
}

.form-group label {
    display: block;
    font-size: 15px;
    font-weight: 500;
    color: #1c1c1e;
    margin-bottom: 8px;
}

input[type="text"],
input[type="email"] {
    width: 100%;
    padding: 14px 16px;
    border-radius: 12px;
    border: 1.5px solid #e5e5ea;
    background: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    transition: all 0.2s ease;
}

input[type="text"]:focus,
input[type="email"]:focus {
    border-color: #007AFF;
    box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.15);
    outline: none;
}

.checkbox-group {
    background: #f5f5f7;
    padding: 20px;
    border-radius: 16px;
    transition: all 0.2s ease;
}

.checkbox-group:hover {
    background: #f0f0f2;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-weight: 500;
}

input[type="checkbox"] {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    accent-color: #007AFF;
}

.db-info {
    background: #f5f5f7;
    padding: 20px;
    border-radius: 16px;
    margin-top: 16px;
}

.db-info p {
    margin: 8px 0;
    color: #3a3a3c;
    font-size: 15px;
}

.form-actions {
    display: flex;
    gap: 16px;
    padding: 24px;
    background: rgba(255, 255, 255, 0.9);
    border-top: 1px solid #e5e5ea;
    border-radius: 0 0 20px 20px;
}

.btn {
    padding: 14px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #007AFF;
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #0066d6;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f5f5f7;
    color: #3a3a3c;
    border: none;
}

.btn-secondary:hover {
    background: #e5e5ea;
}

.alert {
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    animation: slideIn 0.3s ease;
}

.alert-success {
    background: #e3fbe3;
    color: #1d6f1d;
    border: 1px solid #c3e6c3;
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

</style>
        <?php include 'sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-content">
                <header class="admin-header">
                    <h1>System Settings</h1>
                    <div class="header-actions">
                        <a href="#" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Reset to Defaults
                        </a>
                    </div>
                </header>
                
                <div class="system-settings-container">
                    <?php 
                    if (isset($success_message)) {
                        echo "<div class='alert alert-success'>" . htmlspecialchars($success_message) . "</div>";
                    }
                    ?>

                    <div class="settings-card">
                        <form method="POST" action="" class="settings-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="site_name">Site Name</label>
                                    <input 
                                        type="text" 
                                        id="site_name" 
                                        name="site_name" 
                                        value="Event Management System"
                                        placeholder="Enter site name"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="email_from">System Email</label>
                                    <input 
                                        type="email" 
                                        id="email_from" 
                                        name="email_from" 
                                        value="noreply@ems.local"
                                        placeholder="Enter system email address"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group checkbox-group">
                                    <label>
                                        <input 
                                            type="checkbox" 
                                            name="maintenance_mode" 
                                            id="maintenance_mode"
                                        > 
                                        Maintenance Mode
                                    </label>
                                    <small>When enabled, the site will only be accessible to admins</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <h3>Database Information</h3>
                                    <div class="db-info">
                                        <p><strong>Database Name:</strong> <?php echo DB_NAME; ?></p>
                                        <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Settings
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/admin_system_settings.js"></script>
</body>
</html>
