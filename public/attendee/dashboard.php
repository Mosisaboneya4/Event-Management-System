<?php
session_start();
require_once '../../config/database.php';

// Ensure only attendees can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'attendee') {
    header("Location: ../index.php");
    exit();
}

$conn = getDatabaseConnection();
$attendee_id = $_SESSION['user_id'];

// Fetch upcoming events the user has tickets for
$upcoming_events_query = "
    SELECT e.event_id, e.name as event_name, e.date as start_date, e.location as venue, e.description, 
           t.ticket_code, t.created_at as purchase_date
    FROM events e
    JOIN tickets t ON e.event_id = t.event_id
    WHERE 
        t.attendee_id = ? 
        AND e.date > NOW() 
        AND e.status = 'published'
    ORDER BY e.date
    LIMIT 5
";
$upcoming_stmt = $conn->prepare($upcoming_events_query);
$upcoming_stmt->bind_param("i", $_SESSION['user_id']);
$upcoming_stmt->execute();
$upcoming_events = $upcoming_stmt->get_result();

// Fetch past events the user has attended
$past_events_query = "
    SELECT e.event_id, e.name as event_name, e.date as start_date, e.location as venue, 
           t.ticket_code, t.check_in_time
    FROM events e
    JOIN tickets t ON e.event_id = t.event_id
    WHERE 
        t.attendee_id = ? 
        AND e.date < NOW() 
        AND t.check_in_time IS NOT NULL
        AND e.status = 'published'
    ORDER BY e.date DESC
    LIMIT 5
";
$past_stmt = $conn->prepare($past_events_query);
$past_stmt->bind_param("i", $_SESSION['user_id']);
$past_stmt->execute();
$past_events = $past_stmt->get_result();

// Fetch total number of tickets and events
$stats_query = "
    SELECT 
        COUNT(DISTINCT ticket_id) as total_tickets,
        COUNT(DISTINCT event_id) as total_events_attended
    FROM tickets
    WHERE attendee_id = ? AND check_in_time IS NOT NULL
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $_SESSION['user_id']);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
// Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    $otp_query = "SELECT otp FROM attendee_photos WHERE attendee_id = ? AND status = 'approved' ORDER BY upload_date DESC LIMIT 1";
    $otp_stmt = $conn->prepare($otp_query);
    $otp_stmt->bind_param("i", $attendee_id);
    $otp_stmt->execute();
    $otp_result = $otp_stmt->get_result()->fetch_assoc();

    if ($otp_result && $otp_result['otp'] == $entered_otp) {
        echo "<script>alert('OTP verified successfully!');</script>";
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}

// Fetch user's events and stats
$stats_query = "SELECT COUNT(DISTINCT ticket_id) as total_tickets, COUNT(DISTINCT event_id) as total_events_attended 
                FROM tickets WHERE attendee_id = ? AND check_in_time IS NOT NULL";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $attendee_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendee Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="user-info">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Attendee Dashboard</p>
            </div>
            <div class="dashboard-actions">
                <a href="my_tickets.php" class="action-btn">
                    <i class="icon-ticket"></i>
                    My Tickets
                </a>
                <a href="profile.php" class="action-btn">
                    <i class="icon-user"></i>
                    Edit Profile
                </a>
            </div>
            <div class="header-actions">
                <a href="../logout.php" class="btn btn-logout">Logout</a>
            </div>
        </header>
        <div class="dashboard-notifications">
    <?php
    // Fetch latest OTP notification
    $notification_query = "
        SELECT ap.otp, ap.status, ap.upload_date 
        FROM attendee_photos ap
        WHERE ap.attendee_id = ? 
        AND ap.status = 'approved' 
        AND ap.otp IS NOT NULL
        ORDER BY ap.upload_date DESC 
        LIMIT 1";
        
    $notif_stmt = $conn->prepare($notification_query);
    $notif_stmt->bind_param("i", $_SESSION['user_id']);
    $notif_stmt->execute();
    $notification = $notif_stmt->get_result()->fetch_assoc();
    if ($notification) {
        echo '<div class="notification-box">';
        echo '<button class="close-btn" onclick="this.parentElement.style.display=\'none\';">&times;</button>';
        echo '<div class="notification-header">';
        echo '<i class="fas fa-bell"></i>';
        echo '<h3>New OTP Received!</h3>';
        echo '</div>';
        echo '<div class="notification-content">';
        echo '<p>Your OTP code is: <strong>' . htmlspecialchars($notification['otp']) . '</strong></p>';
        echo '<p>Received on: ' . date('F d, Y h:i A', strtotime($notification['upload_date'])) . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    
    ?>
</div>

<style>
    .notification-box {
    position: relative;  /* Add this */
    background: #fff;
    border-left: 4px solid #4CAF50;
    border-radius: 8px;
    padding: 20px;
    margin: 10px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: slideIn 0.5s ease-out;
}

.close-btn {
    position: absolute;
    right: 15px;
    top: 15px;
    background: #ff4444;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: white;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
    z-index: 1;  /* Add this */
}

.close-btn:hover {
    background: #ff0000;
}

.dashboard-notifications {
    margin: 20px 0;
}

.notification-box {
    background: #fff;
    border-left: 4px solid #4CAF50;
    border-radius: 8px;
    padding: 20px;
    margin: 10px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: slideIn 0.5s ease-out;
}

.notification-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.notification-header i {
    color: #4CAF50;
    font-size: 1.2em;
}

.notification-content {
    padding: 10px 0;
}

.notification-content strong {
    color: #4CAF50;
    font-size: 1.2em;
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>


        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Tickets</h3>
                <p><?php echo htmlspecialchars($stats['total_tickets']); ?></p>
            </div>
            <div class="stat-card">
                <h3>Events Attended</h3>
                <p><?php echo htmlspecialchars($stats['total_events_attended']); ?></p>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="upcoming-events">
                <h2>Upcoming Events</h2>
                <?php if ($upcoming_events->num_rows > 0): ?>
                    <div class="event-list">
                        <?php while($event = $upcoming_events->fetch_assoc()): ?>
                            <div class="event-card">
                                <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <p><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($event['start_date'])); ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                                <p><strong>Ticket Code:</strong> <?php echo htmlspecialchars($event['ticket_code']); ?></p>
                                <a href="event_details.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-view">View Details</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No upcoming events found.</p>
                <?php endif; ?>
            </div>

            <div class="past-events">
                <h2>Past Events</h2>
                <?php if ($past_events->num_rows > 0): ?>
                    <div class="event-list">
                        <?php while($event = $past_events->fetch_assoc()): ?>
                            <div class="event-card">
                                <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                <p><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($event['start_date'])); ?></p>
                                <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
                                <p><strong>Checked In:</strong> <?php echo date('F d, Y h:i A', strtotime($event['check_in_time'])); ?></p>
                                <a href="event_details.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-view">View Details</a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No past events found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="events.php" class="btn btn-browse">Browse Events</a>
                <a href="my_tickets.php" class="btn btn-tickets">My Tickets</a>
                <a href="profile.php" class="btn btn-edit-profile">Edit Profile</a>
            </div>
        </div>
</div>

<style>
.otp-verification-section {
    max-width: 400px;
    margin: 2rem auto;
    padding: 1rem;
}

.otp-card {
    background: linear-gradient(145deg, #ffffff, #f5f5f5);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.otp-header {
    text-align: center;
    margin-bottom: 2rem;
}

.otp-header i {
    font-size: 2.5rem;
    color: #4CAF50;
    margin-bottom: 1rem;
}

.otp-header h2 {
    color: #333;
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.subtitle {
    color: #666;
    font-size: 0.9rem;
}

.otp-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.otp-input-group {
    position: relative;
}
.otp-input-group input {
    width: 80%;
    margin: 0 auto;
    padding: 0.8rem;
    font-size: 1.1rem;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    background: white;
    transition: all 0.3s ease;
    text-align: center;
    letter-spacing: 4px;
    display: block;
}
.otp-input-group input:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.1);
}

.verify-button {
    background: linear-gradient(45deg, #4CAF50, #45a049);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 12px;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: transform 0.2s ease;
}

.verify-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.verify-button i {
    transition: transform 0.2s ease;
}

.verify-button:hover i {
    transform: translateX(5px);
}
</style>


        <div class="photo-sharing-section">
            <h2>Payment screen shots</h2>
            <div class="upload-container">
                <form action="upload_photo.php" method="POST" enctype="multipart/form-data" class="modern-upload-form">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon" id="uploadIcon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="preview-container" id="previewContainer">
                            <img id="photoPreview" src="" alt="Preview">
                        </div>
                        <label for="photoInput" class="custom-file-upload">
                            Select Photo
                        </label>
                        <input type="file" id="photoInput" name="attendee_photo" accept="image/*" onchange="previewImage(this)">
                    </div>
                    
                    <div class="photo-details-input">
                        <div class="input-group">
                            <label for="photoTitle">Title</label>
                            <input type="text" id="photoTitle" name="photo_title" placeholder="Give your photo a title">
                        </div>
                        
                        <div class="input-group">
                            <label for="photoDescription">Description</label>
                            <textarea id="photoDescription" name="photo_description" 
                                placeholder="Share the story behind this moment..."></textarea>
                        </div>

                        <div class="upload-actions">
                            <button type="submit" class="btn btn-upload">Share Photo</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <style>
        .stats-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin: 10px;
            text-align: center;
        }
            .photo-sharing-section {
                background: #ffffff;
                border-radius: 20px;
                padding: 3rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                margin: 2.5rem 0;
            }

            .photo-sharing-section h2 {
                font-size: 1.8rem;
                color: #1a1a1a;
                margin-bottom: 2rem;
                font-weight: 700;
                text-align: center;
            }

            .modern-upload-form {
                max-width: 700px;
                margin: 0 auto;
                background: #fafafa;
                padding: 2.5rem;
                border-radius: 16px;
            }

            .upload-area {
                background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
                border-radius: 16px;
                padding: 4rem 2rem;
                text-align: center;
                border: 3px dashed #6366f1;
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .upload-area:hover {
                border-color: #4f46e5;
                transform: translateY(-2px);
            }

            .upload-icon {
                font-size: 4rem;
                background: linear-gradient(45deg, #6366f1, #4f46e5);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                margin-bottom: 2rem;
            }

            .custom-file-upload {
                background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
                color: white;
                padding: 1.2rem 3rem;
                border-radius: 12px;
                font-weight: 600;
                font-size: 1.1rem;
                cursor: pointer;
                display: inline-block;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
            }

            .custom-file-upload:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            }

            .photo-details-input {
                margin-top: 3rem;
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }

            .input-group label {
                font-weight: 600;
                color: #374151;
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
                display: block;
            }

            .input-group input,
            .input-group textarea {
                width: 100%;
                padding: 1.2rem;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                background: #ffffff;
            }

            .input-group input:focus,
            .input-group textarea:focus {
                border-color: #6366f1;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
                outline: none;
            }

            .input-group textarea {
                min-height: 150px;
                resize: vertical;
            }

            .upload-actions {
                margin-top: 3rem;
                text-align: center;
            }

            .btn-upload {
                background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
                color: white;
                padding: 1.2rem 4rem;
                border-radius: 12px;
                border: none;
                font-size: 1.2rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
            }

            .btn-upload:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
            }

            input[type="file"] {
                display: none;
            }

            .preview-container {
                display: none;
                width: 100%;
                max-width: 400px;
                margin: 0 auto 2rem auto;
                border-radius: 12px;
                overflow: hidden;
            }

            .preview-container img {
                width: 100%;
                height: auto;
                display: block;
                object-fit: cover;
            }

            .preview-active .upload-icon {
                display: none;
            }

            .preview-active .preview-container {
                display: block;
            }
        </style>

        <script>
            function previewImage(input) {
                const preview = document.getElementById('photoPreview');
                const uploadArea = document.getElementById('uploadArea');
                const previewContainer = document.getElementById('previewContainer');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        uploadArea.classList.add('preview-active');
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
    </div>
    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>