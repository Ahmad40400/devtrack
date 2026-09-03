<?php
require_once __DIR__ . '/config.php';

$page_title = 'Privacy Policy - ' . APP_NAME;
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
            background: linear-gradient(135deg, #10b981, #059669);
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
            background: rgba(16, 185, 129, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #10b981;
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
            background: #10b981;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-top: 20px;
        }
        
        .back-home:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
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
            <h1>Privacy Policy</h1>
            <p>Your privacy is important to us. Learn how we protect your data.</p>
        </div>
    </div>

    <!-- Content -->
    <div class="legal-content">
        <div class="legal-container">
            
            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-shield-alt"></i></span>
                    1. Introduction
                </h2>
                <p>
                    This Privacy Policy explains how <?php echo APP_NAME; ?> ("we", "us", "our") collects, uses, and protects your personal information 
                    when you use our platform. By using <?php echo APP_NAME; ?>, you consent to the practices described in this policy.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-database"></i></span>
                    2. Information We Collect
                </h2>
                <p>We collect the following types of information:</p>
                <ul>
                    <li><strong>Account Information:</strong> Username, email address, password (hashed), full name</li>
                    <li><strong>Profile Information:</strong> Bio, avatar, social media links, GitHub username</li>
                    <li><strong>Content:</strong> Projects, tasks, skills, learning goals you create</li>
                    <li><strong>Usage Data:</strong> Pages visited, features used, activity logs</li>
                    <li><strong>Technical Data:</strong> IP address, browser type, device information</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-cog"></i></span>
                    3. How We Use Your Information
                </h2>
                <p>We use your information to:</p>
                <ul>
                    <li>Provide and maintain the platform</li>
                    <li>Create and manage your account</li>
                    <li>Personalize your experience</li>
                    <li>Display your content on your profile/portfolio</li>
                    <li>Send you notifications about platform updates</li>
                    <li>Analyze usage to improve our services</li>
                    <li>Protect against fraud and unauthorized access</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-share-alt"></i></span>
                    4. Information Sharing
                </h2>
                <p>We do NOT sell your personal information. We may share your information:</p>
                <ul>
                    <li>With other users (your public profile and projects)</li>
                    <li>With service providers who help us operate the platform</li>
                    <li>When required by law or legal process</li>
                    <li>With your consent</li>
                </ul>
                <p>
                    Public content (projects, profile) is visible to other users. You can make projects private by unchecking 
                    the "Make project visible" option when creating a project.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-cookie"></i></span>
                    5. Cookies
                </h2>
                <p>
                    We use cookies to maintain your session and remember your preferences. Cookies are small files stored on your device. 
                    You can disable cookies in your browser settings, but some features may not work properly.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-lock"></i></span>
                    6. Data Security
                </h2>
                <p>
                    We take data security seriously. We use industry-standard measures to protect your data:
                </p>
                <ul>
                    <li>Passwords are hashed using secure algorithms</li>
                    <li>SSL encryption for data transmission</li>
                    <li>Regular security audits</li>
                    <li>Access controls to limit data access</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-user-edit"></i></span>
                    7. Your Rights
                </h2>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal data</li>
                    <li>Update or correct your data</li>
                    <li>Request deletion of your account</li>
                    <li>Export your data</li>
                    <li>Withdraw consent at any time</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-trash-alt"></i></span>
                    8. Data Retention
                </h2>
                <p>
                    We retain your data as long as your account is active. If you delete your account, we permanently delete all associated data 
                    from our systems within 30 days.
                </p>
            </div>

            <div class="legal-section">
                <h2>
                    <span class="section-icon"><i class="fas fa-envelope"></i></span>
                    9. Contact Us
                </h2>
                <p>
                    If you have any questions about this Privacy Policy, please contact us at:
                </p>
                <p>
                    <strong>Email:</strong> <a href="mailto:privacy@devtrack.com" style="color: #10b981;">privacy@devtrack.com</a><br>
                    <strong>Website:</strong> <a href="<?php echo BASE_URL; ?>" style="color: #10b981;"><?php echo APP_NAME; ?></a>
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