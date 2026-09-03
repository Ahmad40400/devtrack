<?php
require_once '../config.php';
requireLogin();

$page_title = 'Edit Project';

$projectId = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

// Get project
$project = fetchOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$projectId, $userId]);

if (!$project) {
    $_SESSION['error'] = 'Project not found.';
    header('Location: ' . BASE_URL . 'projects/');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $technologies = sanitizeInput($_POST['technologies'] ?? '');
        $github_url = sanitizeInput($_POST['github_url'] ?? '');
        $demo_url = sanitizeInput($_POST['demo_url'] ?? '');
        $status = $_POST['status'] ?? 'planning';
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        $allow_download = isset($_POST['allow_download']) ? 1 : 0;
        
        if (empty($title)) {
            $error = 'Project title is required.';
        } elseif ($github_url && !validateURL($github_url)) {
            $error = 'Invalid GitHub URL.';
        } elseif ($demo_url && !validateURL($demo_url)) {
            $error = 'Invalid Demo URL.';
        } else {
            $image = $project['image'];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $validation = validateFileUpload($_FILES['image']);
                if ($validation['valid']) {
                    // Delete old image
                    if ($image && file_exists(UPLOAD_PATH . 'projects/' . $image)) {
                        unlink(UPLOAD_PATH . 'projects/' . $image);
                    }
                    
                    $image = sanitizeFilename($_FILES['image']['name']);
                    $uploadPath = UPLOAD_PATH . 'projects/' . $image;
                    
                    if (!is_dir(UPLOAD_PATH . 'projects/')) {
                        mkdir(UPLOAD_PATH . 'projects/', 0777, true);
                    }
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        $error = 'Failed to upload image.';
                    }
                } else {
                    $error = $validation['error'];
                }
            }
            
            // Handle image removal
            if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
                if ($image && file_exists(UPLOAD_PATH . 'projects/' . $image)) {
                    unlink(UPLOAD_PATH . 'projects/' . $image);
                }
                $image = null;
            }
            
            // Handle file upload (ZIP)
            $file_path = $project['file_path'];
            $file_name = $project['file_name'];
            $file_size = $project['file_size'];
            
            if (!$error && isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['project_file'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                
                if (strtolower($ext) !== 'zip') {
                    $error = 'Only ZIP files are allowed for project files.';
                } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB
                    $error = 'File size exceeds 50MB limit.';
                } else {
                    // Delete old file if exists
                    if ($file_path && file_exists(UPLOAD_PATH . 'projects/files/' . $file_path)) {
                        unlink(UPLOAD_PATH . 'projects/files/' . $file_path);
                    }
                    
                    $file_name = sanitizeFilename($file['name']);
                    $file_path = time() . '_' . $file_name;
                    $uploadPath = UPLOAD_PATH . 'projects/files/' . $file_path;
                    
                    if (!is_dir(UPLOAD_PATH . 'projects/files/')) {
                        mkdir(UPLOAD_PATH . 'projects/files/', 0777, true);
                    }
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $file_size = $file['size'];
                    } else {
                        $error = 'Failed to upload project file.';
                    }
                }
            }
            
            if (!$error) {
                update(
                    "UPDATE projects SET 
                        title = ?, description = ?, technologies = ?, github_url = ?, 
                        demo_url = ?, status = ?, start_date = ?, end_date = ?, 
                        image = ?, is_public = ?, allow_download = ?,
                        file_path = ?, file_name = ?, file_size = ?
                     WHERE id = ? AND user_id = ?",
                    [$title, $description, $technologies, $github_url, $demo_url, 
                     $status, $start_date, $end_date, $image, $is_public, $allow_download,
                     $file_path, $file_name, $file_size, $projectId, $userId]
                );
                
                logActivity($userId, 'project_updated', "Updated project: $title");
                $_SESSION['success'] = 'Project updated successfully!';
                header('Location: ' . BASE_URL . 'projects/');
                exit();
            }
        }
    }
}

include_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Project</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    
                    <!-- Basic Info -->
                    <div class="mb-3">
                        <label class="form-label">Project Title *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo sanitizeOutput($project['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo sanitizeOutput($project['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Technologies (comma separated)</label>
                        <input type="text" name="technologies" class="form-control" value="<?php echo sanitizeOutput($project['technologies']); ?>" placeholder="PHP, JavaScript, Bootstrap, MySQL">
                    </div>
                    
                    <!-- Links -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github_url" class="form-control" value="<?php echo sanitizeOutput($project['github_url']); ?>" placeholder="https://github.com/username/repo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Live Demo URL</label>
                                <input type="url" name="demo_url" class="form-control" value="<?php echo sanitizeOutput($project['demo_url']); ?>" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status & Image -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="planning" <?php echo $project['status'] == 'planning' ? 'selected' : ''; ?>>Planning</option>
                                    <option value="in-progress" <?php echo $project['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $project['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="on-hold" <?php echo $project['status'] == 'on-hold' ? 'selected' : ''; ?>>On Hold</option>
                                    <option value="cancelled" <?php echo $project['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Image</label>
                                <?php if ($project['image']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo BASE_URL; ?>uploads/projects/<?php echo $project['image']; ?>" 
                                             alt="Current image" style="max-height: 100px;" class="rounded">
                                        <div class="form-check mt-1">
                                            <input type="checkbox" name="remove_image" value="1" class="form-check-input" id="removeImage">
                                            <label class="form-check-label" for="removeImage">Remove current image</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dates -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $project['start_date']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $project['end_date']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Management -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3"><i class="fas fa-file-archive me-2"></i>Project Files</h6>
                        
                        <?php if ($project['file_path']): ?>
                            <div class="bg-light p-3 rounded mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-file-archive fa-2x text-primary me-3"></i>
                                        <strong><?php echo sanitizeOutput($project['file_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo round($project['file_size'] / 1024, 2); ?> KB</small>
                                        <?php if ($project['allow_download']): ?>
                                            <span class="badge bg-success ms-2">Download allowed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-2">Download disabled</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="<?php echo BASE_URL; ?>projects/download.php?project=<?php echo $project['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <button type="button" onclick="removeFile(<?php echo $project['id']; ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No files uploaded for this project.</p>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Upload New File (ZIP only)</label>
                                    <input type="file" name="project_file" class="form-control" accept=".zip">
                                    <small class="text-muted">Maximum size: 50MB. This will replace existing file.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Allow Download</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="allow_download" value="1" class="form-check-input" id="allowDownload" 
                                               <?php echo $project['allow_download'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allowDownload">Allow others to download</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Visibility -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3"><i class="fas fa-eye me-2"></i>Visibility</h6>
                        <div class="form-check">
                            <input type="checkbox" name="is_public" value="1" class="form-check-input" id="isPublic" 
                                   <?php echo $project['is_public'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isPublic">
                                Make project visible to other developers
                            </label>
                            <br>
                            <small class="text-muted">If unchecked, only you can see this project on your profile.</small>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function removeFile(projectId) {
    if (confirm('Are you sure you want to remove the uploaded file? This action cannot be undone.')) {
        window.location.href = '<?php echo BASE_URL; ?>projects/remove-file.php?id=' + projectId;
    }
}
</script>

<?php include_once '../includes/footer.php'; ?>