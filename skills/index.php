<?php
require_once '../config.php';
requireLogin();

$page_title = 'Skills';
$userId = $_SESSION['user_id'];

// Get user skills
$userSkills = getUserSkills($userId);

// Get all skills for adding
$allSkills = fetchAll("SELECT * FROM skills ORDER BY name");

$error = '';
$success = '';

// Handle skill addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $skillId = $_POST['skill_id'] ?? 0;
        $proficiency = $_POST['proficiency'] ?? 0;
        $experience_level = $_POST['experience_level'] ?? 'beginner';
        
        if (!$skillId) {
            $error = 'Please select a skill.';
        } else {
            // Check if skill already exists for user
            $existing = fetchOne("SELECT * FROM user_skills WHERE user_id = ? AND skill_id = ?", [$userId, $skillId]);
            
            if ($existing) {
                // Update existing
                update(
                    "UPDATE user_skills SET proficiency = ?, experience_level = ? WHERE id = ?",
                    [$proficiency, $experience_level, $existing['id']]
                );
                $success = 'Skill updated successfully!';
            } else {
                // Add new
                insert(
                    "INSERT INTO user_skills (user_id, skill_id, proficiency, experience_level) VALUES (?, ?, ?, ?)",
                    [$userId, $skillId, $proficiency, $experience_level]
                );
                $success = 'Skill added successfully!';
            }
            
            logActivity($userId, 'skill_added', "Added/updated skill");
            header('Location: ' . BASE_URL . 'skills/');
            exit();
        }
    }
}

// Handle skill removal
if (isset($_GET['remove']) && $_GET['remove']) {
    $skillId = $_GET['remove'];
    delete("DELETE FROM user_skills WHERE user_id = ? AND skill_id = ?", [$userId, $skillId]);
    logActivity($userId, 'skill_removed', "Removed skill");
    $_SESSION['success'] = 'Skill removed successfully!';
    header('Location: ' . BASE_URL . 'skills/');
    exit();
}

include_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <h4 class="mb-4">Your Skills</h4>
        
        <?php if (empty($userSkills)): ?>
            <div class="text-center py-5">
                <i class="fas fa-code fa-4x text-muted mb-3"></i>
                <h4>No Skills Added</h4>
                <p class="text-muted">Add your skills to showcase your expertise.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($userSkills as $skill): ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1">
                                            <?php if ($skill['icon']): ?>
                                                <i class="<?php echo $skill['icon']; ?> me-2"></i>
                                            <?php endif; ?>
                                            <?php echo sanitizeOutput($skill['name']); ?>
                                        </h5>
                                        <small class="text-muted"><?php echo sanitizeOutput($skill['category'] ?? 'General'); ?></small>
                                    </div>
                                    <div>
                                        <span class="badge bg-<?php echo getExperienceBadge($skill['experience_level']); ?>">
                                            <?php echo $skill['experience_level']; ?>
                                        </span>
                                        <a href="?remove=<?php echo $skill['id']; ?>" class="btn btn-sm btn-danger ms-2" 
                                           onclick="return confirm('Remove this skill?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Proficiency</span>
                                        <span><?php echo $skill['proficiency']; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-<?php echo getProgressColor($skill['proficiency']); ?>" 
                                             style="width: <?php echo $skill['proficiency']; ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Add New Skill</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Skill</label>
                        <select name="skill_id" class="form-select" required>
                            <option value="">Choose a skill...</option>
                            <?php 
                                $userSkillIds = array_column($userSkills, 'id');
                                foreach ($allSkills as $skill): 
                                    $isAdded = in_array($skill['id'], $userSkillIds);
                            ?>
                                <option value="<?php echo $skill['id']; ?>" <?php echo $isAdded ? 'disabled' : ''; ?>>
                                    <?php echo sanitizeOutput($skill['name']); ?>
                                    <?php if ($skill['category']): ?>
                                        (<?php echo sanitizeOutput($skill['category']); ?>)
                                    <?php endif; ?>
                                    <?php echo $isAdded ? ' - Already added' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Proficiency (%)</label>
                        <input type="range" name="proficiency" class="form-range" min="0" max="100" value="50" 
                               oninput="document.getElementById('proficiencyValue').textContent = this.value + '%'">
                        <div class="text-center">
                            <span id="proficiencyValue">50%</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Experience Level</label>
                        <select name="experience_level" class="form-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_skill" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-2"></i>Add Skill
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>