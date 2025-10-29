<?php
// Start the session to access user login data.
session_start();

// --- DYNAMIC HOMEPAGE LINK LOGIC ---
// Determine the correct homepage URL based on the user's role.
$home_page_url = '/index'; // Default for unregistered visitors
if (isset($_SESSION['usertype'])) {
    if ($_SESSION['usertype'] === 'recipeowner') {
        $home_page_url = '/recipeownerl'; // Homepage for recipe owners
    } elseif ($_SESSION['usertype'] === 'user') {
        $home_page_url = '/userl'; // Or a specific user dashboard, e.g., 'user_dashboard.php'
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Culinary Compass</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <style>
        :root {
            --primary-color: #83a87d; /* Soft green */
            --primary-color-dark: #6a8d65;
            --secondary-color: #f2eee8; /* Creamy background */
            --text-color: #4a4a4a;
            --heading-color: #2d2d2d;
            --border-color: #e0dcd5;
            --light-color: #ffffff;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --font-body: 'Poppins', sans-serif;
            --font-display: 'Lora', serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            background-color: var(--secondary-color);
            color: var(--text-color);
            line-height: 1.7;
        }
        
        /* --- Top Bar for Back Button --- */
        .top-bar {
            background-color: var(--primary-color-dark);
            padding: 0.5rem 1.5rem;
        }
        .top-bar .container { padding-top: 0; padding-bottom: 0; }
        .back-link {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: opacity 0.3s ease;
        }
        .back-link:hover { opacity: 0.8; }
        .back-link .fa { margin-right: 0.5rem; }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 4rem 1.5rem;
        }

        /* --- Header Section --- */
        .page-header { text-align: center; margin-bottom: 3rem; }
        .page-header h1 { font-family: var(--font-display); font-size: 3rem; color: var(--heading-color); margin-bottom: 0.5rem; }
        .page-header p { font-size: 1.1rem; color: var(--text-color); max-width: 600px; margin: 0 auto; }

        /* --- Main Content Layout --- */
        .contact-layout {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 3rem;
            background-color: var(--light-color);
            border-radius: 16px;
            box-shadow: 0 10px 40px var(--shadow-color);
            overflow: hidden;
        }
        
        /* --- Contact Form Section (Left Side) --- */
        .contact-form-container { padding: 3rem; }
        .contact-form-container h2 { font-family: var(--font-display); font-size: 2rem; margin-bottom: 2rem; color: var(--heading-color); }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { display: block; font-weight: 500; margin-bottom: 0.5rem; }
        .form-input, .form-textarea {
            width: 100%; padding: 0.8rem 1rem; font-size: 1rem; font-family: var(--font-body);
            border: 1px solid var(--border-color); border-radius: 8px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-input:focus, .form-textarea:focus {
            outline: none; border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(131, 168, 125, 0.2);
        }
        .form-textarea { resize: vertical; min-height: 140px; }
        .submit-btn {
            width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600;
            color: var(--light-color); background-color: var(--primary-color);
            border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s ease;
        }
        .submit-btn:hover { background-color: var(--primary-color-dark); }
        
        /* --- Contact Info Section (Right Side) --- */
        .contact-info-container { background-color: #faf8f5; padding: 3rem; }
        .contact-info-item { display: flex; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; }
        .contact-info-icon { font-size: 1.5rem; color: var(--primary-color); margin-top: 5px; }
        .contact-info-content h3 { font-family: var(--font-display); font-size: 1.25rem; color: var(--heading-color); margin-bottom: 0.25rem; }
        .contact-info-content p, .contact-info-content a { color: var(--text-color); text-decoration: none; }
        .contact-info-content a:hover { color: var(--primary-color); }
        
        /* --- Embedded Map --- */
        .map-container { width: 100%; height: 250px; border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color); }
        .map-container iframe { width: 100%; height: 100%; border: 0; }

        /* --- Responsive Design --- */
        @media (max-width: 992px) {
            .contact-layout { grid-template-columns: 1fr; }
            .contact-info-container { border-top: 1px solid var(--border-color); }
        }
        @media (max-width: 768px) {
            .container { padding: 2rem 1rem; }
            .contact-form-container, .contact-info-container { padding: 2rem; }
            .page-header h1 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <!-- ======================= TOP BAR (DYNAMIC BACK BUTTON) ======================= -->
    <div class="top-bar">
        <div class="container">
            <a href="<?= htmlspecialchars($home_page_url) ?>" class="back-link">
                <i class="fa fa-arrow-left"></i> Back to Homepage
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Page Header -->
        <header class="page-header">
            <h1>Get in Touch</h1>
            <p>We'd love to hear from you! Whether you have a question, feedback, or just want to say hello, please don't hesitate to reach out.</p>
        </header>

        <main class="contact-layout">
            
            <!-- Left Side: Contact Form -->
            <section class="contact-form-container">
                <h2>Send Us a Message</h2>
                <form action="submit_contact_form.php" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" class="form-textarea" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </section>

            <!-- Right Side: Contact Info & Map -->
            <aside class="contact-info-container">
                <div class="contact-info-item">
                    <i class="fa fa-map-marker contact-info-icon"></i>
                    <div class="contact-info-content">
                        <h3>Our Location</h3>
                        <p>123 Recipe Lane, Culinary District<br>Nairobi, Kenya</p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fa fa-phone contact-info-icon"></i>
                    <div class="contact-info-content">
                        <h3>Phone</h3>
                        <p><a href="tel:+254700000000">+254 700 000 000</a></p>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fa fa-envelope contact-info-icon"></i>
                    <div class="contact-info-content">
                        <h3>Email</h3>
                        <p><a href="mailto:hello@culinarycompass.com">hello@culinarycompass.com</a></p>
                    </div>
                </div>
                
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d255282.3585372439!2d36.70730438753204!3d-1.302860261358327!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1172d84d49a7%3A0xf7cf0254b297924c!2sNairobi!5e0!3m2!1sen!2ske!4v1667895352815!5m2!1sen!2ske" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-downgrade"></iframe>
                </div>
            </aside>

        </main>
    </div>

</body>
</html>

