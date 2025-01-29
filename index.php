<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hero-content {
            flex: 1;
            padding-right: 2rem;
        }

        .hero-image {
            flex: 1;
            text-align: center;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .subheading {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: rgba(255,255,255,0.8);
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #ffffff;
            color: #667eea;
        }

        .btn-secondary {
            background-color: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 17px rgba(0,0,0,0.2);
        }

        .features {
            background-color: rgba(255,255,255,0.1);
            padding: 3rem 0;
            margin-top: 2rem;
        }

        .features-container {
            display: flex;
            justify-content: space-between;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature {
            flex: 1;
            text-align: center;
            padding: 1rem;
        }

        .feature i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .feature h3 {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                text-align: center;
            }

            .hero-content {
                padding-right: 0;
                margin-bottom: 2rem;
            }

            .cta-buttons {
                justify-content: center;
            }

            .features-container {
                flex-direction: column;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="hero-content">
            <h1>Event Management Simplified</h1>
            <p class="subheading">Streamline your event planning, ticketing, and management with our comprehensive platform.</p>
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">Get Started</a>
                <a href="#features" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Event Management">
        </div>
    </div>

    <div class="features" id="features">
        <div class="features-container">
            <div class="feature">
                <i class="fas fa-calendar-alt"></i>
                <h3>Event Creation</h3>
                <p>Easily create and manage events with our intuitive interface.</p>
            </div>
            <div class="feature">
                <i class="fas fa-ticket-alt"></i>
                <h3>Ticket Management</h3>
                <p>Sell, track, and validate tickets seamlessly.</p>
            </div>
            <div class="feature">
                <i class="fas fa-users"></i>
                <h3>Attendee Tracking</h3>
                <p>Monitor and manage event attendees with real-time insights.</p>
            </div>
        </div>
    </div>
</body>
</html>
