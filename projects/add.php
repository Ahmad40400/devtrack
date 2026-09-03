<?php
require_once '../config.php';
requireLogin();

$page_title = 'Add Project';
$userId = $_SESSION['user_id'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
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
            // Handle image upload
            $image = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $validation = validateFileUpload($_FILES['image']);
                if ($validation['valid']) {
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
            
            // Handle file upload (ZIP)
            $file_path = null;
            $file_name = null;
            $file_size = null;
            
            if (!$error && isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['project_file'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                
                if (strtolower($ext) !== 'zip') {
                    $error = 'Only ZIP files are allowed for project files.';
                } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB
                    $error = 'File size exceeds 50MB limit.';
                } else {
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
                $projectId = insert(
                    "INSERT INTO projects (user_id, title, description, technologies, github_url, demo_url, 
                        status, start_date, end_date, image, is_public, allow_download, 
                        file_path, file_name, file_size) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$userId, $title, $description, $technologies, $github_url, $demo_url, 
                     $status, $start_date, $end_date, $image, $is_public, $allow_download,
                     $file_path, $file_name, $file_size]
                );
                
                logActivity($userId, 'project_created', "Created project: $title");
                $_SESSION['success'] = 'Project created successfully!';
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
                <h4 class="card-title mb-0">Create New Project</h4>
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
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Technologies (comma separated)</label>
                        <input type="text" name="technologies" class="form-control" placeholder="PHP, JavaScript, Bootstrap, MySQL">
                    </div>
                    
                    <!-- Links -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">GitHub URL</label>
                                <input type="url" name="github_url" class="form-control" placeholder="https://github.com/username/repo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Live Demo URL</label>
                                <input type="url" name="demo_url" class="form-control" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status & Image -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="planning">Planning</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="on-hold">On Hold</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dates -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <!-- NEW: File Upload Section -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3"><i class="fas fa-file-archive me-2"></i>Project Files (ZIP)</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Upload Project Files (ZIP only)</label>
                                    <input type="file" name="project_file" class="form-control" accept=".zip">
                                    <small class="text-muted">Maximum size: 50MB. Only ZIP files allowed.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Allow Download</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" name="allow_download" value="1" class="form-check-input" id="allowDownload" checked>
                                        <label class="form-check-label" for="allowDownload">Allow others to download</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- NEW: Visibility Section -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3"><i class="fas fa-eye me-2"></i>Visibility</h6>
                        <div class="form-check">
                            <input type="checkbox" name="is_public" value="1" class="form-check-input" id="isPublic" checked>
                            <label class="form-check-label" for="isPublic">
                                Make project visible to other developers
                            </label>
                            <br>
                            <small class="text-muted">If unchecked, only you can see this project.</small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo BASE_URL; ?>projects/" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>