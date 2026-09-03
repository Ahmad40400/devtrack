<?php
require_once '../config.php';
requireLogin();

$page_title = 'Edit Profile';
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $full_name = sanitizeInput($_POST['full_name'] ?? '');
        $bio = sanitizeInput($_POST['bio'] ?? '');
        $github_username = sanitizeInput($_POST['github_username'] ?? '');
        $website = sanitizeInput($_POST['website'] ?? '');
        $linkedin = sanitizeInput($_POST['linkedin'] ?? '');
        $twitter = sanitizeInput($_POST['twitter'] ?? '');
        
        // Handle avatar upload
        $avatar = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $validation = validateFileUpload($_FILES['avatar']);
            if ($validation['valid']) {
                // Delete old avatar
                if ($avatar && $avatar != 'default-avatar.png' && file_exists(UPLOAD_PATH . 'profile/' . $avatar)) {
                    unlink(UPLOAD_PATH . 'profile/' . $avatar);
                }
                
                $avatar = sanitizeFilename($_FILES['avatar']['name']);
                $uploadPath = UPLOAD_PATH . 'profile/' . $avatar;
                
                if (!is_dir(UPLOAD_PATH . 'profile/')) {
                    mkdir(UPLOAD_PATH . 'profile/', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                    // Update session
                    $_SESSION['avatar'] = $avatar;
                } else {
                    $error = 'Failed to upload avatar.';
                }
            } else {
                $error = $validation['error'];
            }
        }
        
        if (!$error) {
            update(
                "UPDATE users SET 
                    full_name = ?, bio = ?, github_username = ?, 
                    website = ?, linkedin = ?, twitter = ?, avatar = ?
                 WHERE id = ?",
                [$full_name, $bio, $github_username, $website, $linkedin, $twitter, $avatar, $userId]
            );
            
            // Update session
            $_SESSION['full_name'] = $full_name;
            
            logActivity($userId, 'profile_updated', 'Updated profile');
            $_SESSION['success'] = 'Profile updated successfully!';
            header('Location: ' . BASE_URL . 'profile/');
            exit();
        }
    }
}

include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Profile</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3 text-center">
                        <img src="<?php echo BASE_URL; ?>uploads/profile/<?php echo $user['avatar'] ?? 'default-avatar.png'; ?>" 
                             alt="Profile Photo" class="rounded-circle img-fluid mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                        <div>
                            <label class="form-label">Change Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo sanitizeOutput($user['full_name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo sanitizeOutput($user['username']); ?>" disabled>
                                <small class="text-muted">Username cannot be changed.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3"><?php echo sanitizeOutput($user['bio']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">GitHub Username</label>
                        <input type="text" name="github_username" class="form-control" value="<?php echo sanitizeOutput($user['github_username']); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" value="<?php echo sanitizeOutput($user['website']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">LinkedIn</label>
                                <input type="url" name="linkedin" class="form-control" value="<?php echo sanitizeOutput($user['linkedin']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Twitter</label>
                        <input type="url" name="twitter" class="form-control" value="<?php echo sanitizeOutput($user['twitter']); ?>">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo BASE_URL; ?>profile/" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>