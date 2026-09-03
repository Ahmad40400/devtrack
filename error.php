<?php
require_once 'config.php';
$page_title = 'Error';
$error_message = $_GET['message'] ?? 'An unexpected error occurred.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 text-center">
                <i class="fas fa-exclamation-triangle text-warning fa-5x mb-4"></i>
                <h2 class="mb-4">Oops! Something went wrong</h2>
                <p class="text-muted mb-4"><?php echo sanitizeOutput($error_message); ?></p>
                <a href="javascript:history.back()" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </a>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>