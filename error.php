<?php
require_once __DIR__ . '/config.php';

$error_code = $_GET['code'] ?? '500';
$error_message = $_GET['message'] ?? 'Something went wrong.';
$page_title = 'Error ' . $error_code . ' - ' . APP_NAME;

// Map common error codes to messages
$error_messages = [
    '403' => 'You don\'t have permission to access this page.',
    '404' => 'The page you\'re looking for doesn\'t exist.',
    '500' => 'Something went wrong on our end. Please try again.',
    '503' => 'Service is temporarily unavailable. Please try again later.',
];

if (isset($error_messages[$error_code])) {
    $error_message = $error_messages[$error_code];
}
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: #1e293b;
        }
        
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px 20px;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            letter-spacing: -0.05em;
        }
        
        .error-icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .error-icon i {
            font-size: 2rem;
            color: #ef4444;
        }
        
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1e293b;
        }
        
        .error-message {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-home {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: #6366f1;
            border: none;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-home:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }
        
        .btn-back {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            border: 1.5px solid #e2e8f0;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }
        
        .refresh-btn {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            border: 1.5px solid #10b981;
            color: #10b981;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .refresh-btn:hover {
            background: rgba(16, 185, 129, 0.1);
            transform: translateY(-2px);
        }
        
        .support-box {
            margin-top: 30px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.6;
        }
        
        .support-box i {
            color: #6366f1;
            margin-right: 6px;
        }
    </style>
</head>
<body>

    <div class="error-container">
        <div class="error-code"><?php echo sanitizeOutput($error_code); ?></div>
        
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h1 class="error-title">Oops! Something Went Wrong</h1>
        
        <p class="error-message">
            <?php echo sanitizeOutput($error_message); ?>
        </p>
        
        <div class="error-actions">
            <a href="<?php echo BASE_URL; ?>index.php" class="btn-home">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
            <button onclick="location.reload()" class="refresh-btn">
                <i class="fas fa-sync"></i>
                Refresh
            </button>
            <button onclick="history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </button>
        </div>
        
        <div class="support-box">
            <i class="fas fa-headset"></i>
            <strong>Need help?</strong> If the problem persists, please contact support at 
            <a href="mailto:support@devtrack.com" style="color: #6366f1; text-decoration: none;">support@devtrack.com</a>
        </div>
    </div>

</body>
</html>
