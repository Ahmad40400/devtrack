<?php
require_once __DIR__ . '/config.php';

$page_title = 'Features - ' . APP_NAME;
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
        
        .features-header {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            padding: 60px 0 40px;
            color: white;
            text-align: center;
        }
        
        .features-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .features-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .features-content {
            padding: 40px 0;
        }
        
        .feature-block {
            background: white;
            border-radius: 14px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }
        
        .feature-block h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feature-block h2 .feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .feature-block p {
            color: #475569;
            font-size: 0.92rem;
            margin-bottom: 12px;
        }
        
        .feature-block ul {
            color: #475569;
            font-size: 0.92rem;
            padding-left: 20px;
            margin-bottom: 12px;
        }
        
        .feature-block ul li {
            margin-bottom: 8px;
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
            .features-header {
                padding: 40px 0 30px;
            }
            
            .features-header h1 {
                font-size: 1.8rem;
            }
            
            .feature-block {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="features-header">
        <div class="container">
            <h1>Platform Features</h1>
            <p>Discover everything <?php echo APP_NAME; ?> has to offer</p>
        </div>
    </div>

    <!-- Content -->
    <div class="features-content">
        <div class="container">
            
            <div class="row">
                
                <!-- Project Management -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                                <i class="fas fa-folder-open"></i>
                            </span>
                            Project Management
                        </h2>
                        <p>Organize and manage all your development projects in one place.</p>
                        <ul>
                            <li>Create projects with detailed descriptions</li>
                            <li>Track project status (Planning, In Progress, Completed)</li>
                            <li>Add technology tags for easy categorization</li>
                            <li>Link GitHub and demo URLs</li>
                            <li>Upload project files (ZIP) for sharing</li>
                            <li>Make projects public or private</li>
                        </ul>
                    </div>
                </div>

                <!-- Task Tracking -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                                <i class="fas fa-list-check"></i>
                            </span>
                            Task Tracking
                        </h2>
                        <p>Stay on top of your work with powerful task management.</p>
                        <ul>
                            <li>Create tasks with priorities (Low, Medium, High)</li>
                            <li>Set due dates and deadlines</li>
                            <li>Link tasks to specific projects</li>
                            <li>Track task status (Pending, In Progress, Completed)</li>
                            <li>Categorize tasks by type</li>
                            <li>Filter and sort tasks easily</li>
                        </ul>
                    </div>
                </div>

                <!-- Skills Development -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(168,85,247,0.1); color: #a855f7;">
                                <i class="fas fa-code"></i>
                            </span>
                            Skills Development
                        </h2>
                        <p>Track and showcase your technical skills.</p>
                        <ul>
                            <li>Add programming languages and frameworks</li>
                            <li>Set proficiency levels (0-100%)</li>
                            <li>Choose experience level (Beginner to Expert)</li>
                            <li>Organize skills by category</li>
                            <li>Display skills on your portfolio</li>
                        </ul>
                    </div>
                </div>

                <!-- Learning Goals -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                                <i class="fas fa-graduation-cap"></i>
                            </span>
                            Learning Goals
                        </h2>
                        <p>Plan your learning journey with clear goals.</p>
                        <ul>
                            <li>Set learning goals with target dates</li>
                            <li>Track progress with percentage</li>
                            <li>Mark goals as completed</li>
                            <li>Monitor your learning journey</li>
                            <li>Celebrate milestones</li>
                        </ul>
                    </div>
                </div>

                <!-- Analytics Dashboard -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            Analytics Dashboard
                        </h2>
                        <p>Visualize your progress and productivity.</p>
                        <ul>
                            <li>Task completion charts</li>
                            <li>Project progress tracking</li>
                            <li>Skill proficiency overview</li>
                            <li>Productivity score</li>
                            <li>Monthly activity trends</li>
                        </ul>
                    </div>
                </div>

                <!-- GitHub Integration -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(15,23,42,0.1); color: #1e293b;">
                                <i class="fab fa-github"></i>
                            </span>
                            GitHub Integration
                        </h2>
                        <p>Connect your GitHub account and showcase your work.</p>
                        <ul>
                            <li>Link your GitHub profile</li>
                            <li>Display repositories automatically</li>
                            <li>Show followers and following count</li>
                            <li>View recent repos and languages</li>
                            <li>Sync data with one click</li>
                        </ul>
                    </div>
                </div>

                <!-- Public Portfolio -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(236,72,153,0.1); color: #ec4899;">
                                <i class="fas fa-globe"></i>
                            </span>
                            Public Portfolio
                        </h2>
                        <p>Showcase your work to the world.</p>
                        <ul>
                            <li>Personal portfolio page with your projects</li>
                            <li>Shareable URL for recruiters</li>
                            <li>Display skills and learning achievements</li>
                            <li>Showcase GitHub repositories</li>
                            <li>Professional online presence</li>
                        </ul>
                    </div>
                </div>

                <!-- Developer Community -->
                <div class="col-md-6">
                    <div class="feature-block">
                        <h2>
                            <span class="feature-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                                <i class="fas fa-users"></i>
                            </span>
                            Developer Community
                        </h2>
                        <p>Connect and network with other developers.</p>
                        <ul>
                            <li>Browse developer profiles</li>
                            <li>View others' projects and skills</li>
                            <li>Search for developers by name or skill</li>
                            <li>See public portfolios</li>
                            <li>Build your professional network</li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Call to Action -->
            <div class="text-center mt-4">
                <p class="text-muted mb-3">Ready to experience all these features?</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary px-5 py-2" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #6366f1; border: none;">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                    <a href="<?php echo BASE_URL; ?>index.php" class="back-home">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>