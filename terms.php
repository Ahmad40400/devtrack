<?php
require_once __DIR__ . '/config.php';

$page_title = 'Terms & Conditions - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeOutput($page_title); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.7;
        }
        
        .legal-header {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            padding: 60px 0 40px;
            color: white;
            text-align: center;
        }
        
        .legal-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .legal-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .legal-content {
            padding: 40px 0;
        }
        
        .legal-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .legal-section {
            background: white;
            border-radius: 14px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        
        .legal-section h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .legal-section h2 .section-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #6366f1;
            flex-shrink: 0;
        }
        
        .legal-section p {
            color: #475569;
            font-size: 0.92rem;
            margin-bottom: 12px;
        }
        
        .legal-section ul {
            color: #475569;
            font-size: 0.92rem;
            padding-left: 20px;
            margin-bottom: 12px;
        }
        
        .legal-section ul li {
            margin-bottom: 8px;
        }
        
        .legal-section .updated-date {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: #6366f1;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-top: 20px;
        }
        
        .back-home:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }
        
        @media (max-width: 768px) {
            .legal-header {
                padding: 40px 0 30px;
            }
            
            .legal-header h1 {
                font-size: 1.8rem;
            }
            
            .legal-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="legal-header">
        <div class="container">
            <h1>Terms & Conditions</h1>
            <p>Please read these terms carefully before using <?php echo APP_NAME; ?></p>
        </div>
    </div>

    <!-- Content -->
    <div class="legal-content">
        <div class="legal-container">
            
            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-file-contract"></i></span>
                    1. Acceptance of Terms
                </h2>
                <p>
                    By accessing and using <?php echo APP_NAME; ?>, you accept and agree to be bound by these Terms and Conditions. 
                    If you do not agree to these terms, please do not use this platform.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-user"></i></span>
                    2. Account Registration
                </h2>
                <p>To use <?php echo APP_NAME; ?>, you must create an account. You agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information during registration</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                    <li>Be responsible for all activities that occur under your account</li>
                    <li>Not use another user's account without their permission</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-project-diagram"></i></span>
                    3. User Content
                </h2>
                <p>
                    You retain all rights to the content you post on <?php echo APP_NAME; ?> (projects, tasks, skills, learning goals, etc.).
                    By posting content, you grant us a non-exclusive, worldwide, royalty-free license to:
                </p>
                <ul>
                    <li>Display your content on the platform</li>
                    <li>Make your content available to other users (if public)</li>
                    <li>Use your content to improve the platform</li>
                </ul>
                <p>You agree not to post content that:</p>
                <ul>
                    <li>Is illegal, harmful, or offensive</li>
                    <li>Infringes on intellectual property rights</li>
                    <li>Contains viruses or malicious code</li>
                    <li>Violates any applicable laws</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-shield-alt"></i></span>
                    4. Privacy & Data Protection
                </h2>
                <p>
                    We are committed to protecting your privacy. Your personal information is handled in accordance with our 
                    <a href="<?php echo BASE_URL; ?>privacy.php" style="color: #6366f1;">Privacy Policy</a>. 
                    By using <?php echo APP_NAME; ?>, you consent to our data practices.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-ban"></i></span>
                    5. Prohibited Activities
                </h2>
                <p>You agree not to engage in any of the following activities:</p>
                <ul>
                    <li>Attempting to gain unauthorized access to the platform</li>
                    <li>Interfering with the normal operation of the platform</li>
                    <li>Uploading malicious code or files</li>
                    <li>Using the platform for any illegal purpose</li>
                    <li>Impersonating another user or entity</li>
                    <li>Spamming or sending unsolicited messages</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-copyright"></i></span>
                    6. Intellectual Property
                </h2>
                <p>
                    The <?php echo APP_NAME; ?> platform, including its design, logo, and features, is owned by us and protected by intellectual property laws. 
                    You may not copy, modify, or distribute any part of the platform without our express written consent.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-exclamation-circle"></i></span>
                    7. Disclaimer of Warranties
                </h2>
                <p>
                    <?php echo APP_NAME; ?> is provided "as is" without warranties of any kind, either express or implied. 
                    We do not guarantee that the platform will be uninterrupted, error-free, or completely secure.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-user-slash"></i></span>
                    8. Account Termination
                </h2>
                <p>
                    We reserve the right to suspend or terminate your account at any time if you violate these terms. 
                    You may also delete your account at any time from your profile settings.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-sync-alt"></i></span>
                    9. Changes to Terms
                </h2>
                <p>
                    We may update these terms from time to time. Continued use of the platform after changes constitutes acceptance of the new terms. 
                    We will notify you of significant changes via email or platform notifications.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-envelope"></i></span>
                    10. Contact Us
                </h2>
                <p>
                    If you have any questions about these Terms & Conditions, please contact us at:
                </p>
                <p>
                    <strong>Email:</strong> <a href="mailto:support@devtrack.com" style="color: #6366f1;">support@devtrack.com</a><br>
                    <strong>Website:</strong> <a href="<?php echo BASE_URL; ?>" style="color: #6366f1;"><?php echo APP_NAME; ?></a>
                </p>
                <div class="updated-date">
                    <i class="fas fa-calendar-alt me-1"></i> Last Updated: <?php echo date('F d, Y'); ?>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>index.php" class="back-home">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>

        </div>
    </div>

</body>
</html>