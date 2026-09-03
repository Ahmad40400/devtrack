<?php
require_once __DIR__ . '/config.php';
http_response_code(404);
$page_title = 'Page Not Found - ' . APP_NAME;
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
            max-width: 550px;
            padding: 20px;
        }
        
        /* Floating Astronaut - Smaller */
        .astronaut-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .astronaut {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
            animation: float 3s ease-in-out infinite;
            position: relative;
        }
        
        .astronaut i {
            font-size: 2.2rem;
            color: white;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }
        
        /* Stars - Smaller */
        .star {
            position: absolute;
            font-size: 0.7rem;
            color: #f59e0b;
            animation: twinkle 2s ease-in-out infinite;
        }
        
        .star-1 { top: -8px; left: -15px; animation-delay: 0s; }
        .star-2 { top: 15px; right: -20px; animation-delay: 0.5s; }
        .star-3 { bottom: -8px; left: 8px; animation-delay: 1s; }
        .star-4 { top: -12px; right: 8px; animation-delay: 1.5s; }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }
        
        /* Error Code - Smaller */
        .error-code {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
            letter-spacing: -0.05em;
        }
        
        .error-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #1e293b;
        }
        
        .error-message {
            color: #64748b;
            font-size: 0.85rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .error-message .emoji {
            font-size: 1rem;
        }
        
        /* Code Snippet - Compact */
        .code-snippet {
            background: #1e293b;
            border-radius: 10px;
            padding: 12px 16px;
            text-align: left;
            margin: 15px auto;
            max-width: 350px;
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            color: #e2e8f0;
            position: relative;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .code-snippet::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 22px;
            background: #334155;
            border-radius: 10px 10px 0 0;
        }
        
        .code-snippet .dots {
            position: absolute;
            top: 7px;
            left: 10px;
            display: flex;
            gap: 4px;
        }
        
        .code-snippet .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }
        
        .code-snippet .dot-red { background: #ef4444; }
        .code-snippet .dot-yellow { background: #f59e0b; }
        .code-snippet .dot-green { background: #10b981; }
        
        .code-snippet code {
            display: block;
            margin-top: 15px;
            line-height: 1.6;
        }
        
        .code-snippet .code-blue { color: #60a5fa; }
        .code-snippet .code-green { color: #34d399; }
        .code-snippet .code-orange { color: #fbbf24; }
        .code-snippet .code-gray { color: #94a3b8; }
        
        /* Action Buttons */
        .error-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .btn-home {
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
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
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
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
        
        /* Fun Fact - Compact */
        .fun-fact {
            padding: 10px 15px;
            background: #f0f0ff;
            border-radius: 8px;
            font-size: 0.72rem;
            color: #6366f1;
            display: inline-block;
            line-height: 1.5;
            max-width: 380px;
        }
        
        .fun-fact i {
            margin-right: 5px;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 20px 0;
            }
            
            .error-container {
                padding: 10px;
            }
            
            .error-code {
                font-size: 2.8rem;
            }
            
            .astronaut {
                width: 65px;
                height: 65px;
            }
            
            .astronaut i {
                font-size: 1.8rem;
            }
            
            .error-title {
                font-size: 1.1rem;
            }
            
            .error-message {
                font-size: 0.8rem;
            }
            
            .code-snippet {
                max-width: 100%;
                font-size: 0.65rem;
                padding: 10px 12px;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-home, .btn-back {
                width: 100%;
                max-width: 220px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="error-container">
        
        <!-- Floating Astronaut -->
        <div class="astronaut-wrapper">
            <div class="astronaut">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <span class="star star-1"><i class="fas fa-star"></i></span>
            <span class="star star-2"><i class="fas fa-star"></i></span>
            <span class="star star-3"><i class="fas fa-star"></i></span>
            <span class="star star-4"><i class="fas fa-star"></i></span>
        </div>
        
        <!-- Error Code -->
        <div class="error-code">404</div>
        
        <h1 class="error-title">Houston, We Have a Problem!</h1>
        
        <p class="error-message">
            <span class="emoji">🛸</span> The page you're looking for has drifted into space!<br>
            It might have been moved, deleted, or never existed.
        </p>
        
        <!-- Fun Code Snippet -->
        <div class="code-snippet">
            <div class="dots">
                <span class="dot dot-red"></span>
                <span class="dot dot-yellow"></span>
                <span class="dot dot-green"></span>
            </div>
            <code>
                <span class="code-gray">// Error 404</span><br>
                <span class="code-blue">if</span> (page.<span class="code-orange">exists</span>) {<br>
                &nbsp;&nbsp;&nbsp;&nbsp;show(<span class="code-green">"Content"</span>);<br>
                } <span class="code-blue">else</span> {<br>
                &nbsp;&nbsp;&nbsp;&nbsp;show(<span class="code-orange">"404 Not Found"</span>);<br>
                &nbsp;&nbsp;&nbsp;&nbsp;<span class="code-gray">// Oops! 🚀</span><br>
                }
            </code>
        </div>
        
        <!-- Action Buttons -->
        <div class="error-actions">
            <a href="<?php echo BASE_URL; ?>index.php" class="btn-home">
                <i class="fas fa-home"></i>
                Take Me Home
            </a>
            <button onclick="history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </button>
        </div>
        
        <!-- Fun Fact -->
        <div class="fun-fact">
            <i class="fas fa-lightbulb"></i>
            <strong>Did you know?</strong> Room 404 was where the first web server was located!
        </div>
    </div>

</body>
</html>
