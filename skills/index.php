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

<!-- Page Title -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color: #1e293b;">Skills</h1>
        <p class="text-muted small mb-0 mt-1">Showcase your expertise and track proficiency.</p>
    </div>
    <span class="badge bg-light text-dark border px-3 py-2 fw-normal">
        <i class="fas fa-code me-2" style="color: #6366f1;"></i><?php echo count($userSkills); ?> Skills
    </span>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <?php 
        $totalSkills = count($userSkills);
        $expertCount = count(array_filter($userSkills, fn($s) => $s['experience_level'] === 'expert'));
        $advancedCount = count(array_filter($userSkills, fn($s) => $s['experience_level'] === 'advanced'));
        $avgProficiency = $totalSkills > 0 ? round(array_sum(array_column($userSkills, 'proficiency')) / $totalSkills) : 0;
    ?>
    
    <!-- Total Skills -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(99,102,241,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-code" style="color: #6366f1; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Total</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $totalSkills; ?></h3>
                <p class="text-muted small mb-0 mt-1">Skills Tracked</p>
            </div>
        </div>
    </div>

    <!-- Expert -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(16,185,129,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-crown" style="color: #10b981; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Expert</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $expertCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">Expert Level</p>
            </div>
        </div>
    </div>

    <!-- Advanced -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(245,158,11,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-star" style="color: #f59e0b; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Advanced</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $advancedCount; ?></h3>
                <p class="text-muted small mb-0 mt-1">Advanced Level</p>
            </div>
        </div>
    </div>

    <!-- Avg Proficiency -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded-circle p-2" style="background: rgba(59,130,246,0.1); width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line" style="color: #3b82f6; font-size: 0.9rem;"></i>
                    </div>
                    <span class="badge bg-light text-muted fw-normal small">Avg</span>
                </div>
                <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.8rem;"><?php echo $avgProficiency; ?>%</h3>
                <p class="text-muted small mb-0 mt-1">Avg Proficiency</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Left Side: Your Skills -->
    <div class="col-md-8">
        <?php if (empty($userSkills)): ?>
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-body text-center py-5">
                    <div style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fas fa-code text-muted" style="font-size: 1.8rem; opacity: 0.5;"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="color: #1e293b;">No Skills Added</h5>
                    <p class="text-muted small mb-4">Add your skills to showcase your expertise to other developers.</p>
                    <a href="#addSkillForm" class="btn btn-primary px-4 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
                        <i class="fas fa-plus me-2"></i>Add Your First Skill
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($userSkills as $skill): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.95rem;">
                                            <?php if ($skill['icon']): ?>
                                                <i class="<?php echo $skill['icon']; ?> me-2" style="color: #6366f1;"></i>
                                            <?php endif; ?>
                                            <?php echo sanitizeOutput($skill['name']); ?>
                                        </h6>
                                        <?php if ($skill['category']): ?>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?php echo sanitizeOutput($skill['category']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php
                                            $expColors = [
                                                'beginner' => ['bg' => 'rgba(148,163,184,0.1)', 'text' => '#64748b'],
                                                'intermediate' => ['bg' => 'rgba(59,130,246,0.1)', 'text' => '#3b82f6'],
                                                'advanced' => ['bg' => 'rgba(245,158,11,0.1)', 'text' => '#b45309'],
                                                'expert' => ['bg' => 'rgba(16,185,129,0.1)', 'text' => '#047857']
                                            ];
                                            $ec = $expColors[$skill['experience_level']] ?? ['bg' => 'rgba(148,163,184,0.1)', 'text' => '#64748b'];
                                        ?>
                                        <span class="badge fw-normal px-2 py-1" style="background: <?php echo $ec['bg']; ?>; color: <?php echo $ec['text']; ?>; font-size: 0.65rem; border-radius: 6px;">
                                            <?php echo $skill['experience_level']; ?>
                                        </span>
                                        <button onclick="removeSkill(<?php echo $skill['id']; ?>)" 
                                                class="btn btn-sm btn-danger" 
                                                title="Remove Skill"
                                                style="border-radius: 6px; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; background: #ef4444; border: none;">
                                            <i class="fas fa-times" style="font-size: 0.6rem;"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted" style="font-size: 0.7rem;">Proficiency</span>
                                        <span style="font-size: 0.7rem; font-weight: 600; color: #1e293b;"><?php echo $skill['proficiency']; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 10px; background: #f1f5f9;">
                                        <div class="progress-bar" 
                                             style="width: <?php echo $skill['proficiency']; ?>%; border-radius: 10px; background: linear-gradient(90deg, #6366f1, #8b5cf6);">
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

    <!-- Right Side: Add New Skill Form -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 14px; position: sticky; top: 80px;">
            <div class="card-header bg-transparent border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0" style="color: #1e293b;">
                    <i class="fas fa-plus-circle me-2" style="color: #6366f1;"></i>Add New Skill
                </h6>
                <small class="text-muted">Expand your skill set</small>
            </div>
            <div class="card-body px-4 pb-4 pt-2" id="addSkillForm">
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 px-3" style="border-radius: 8px; font-size: 0.8rem;"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem; color: #334155;">Select Skill</label>
                        <select name="skill_id" class="form-select" required style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
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
                        <label class="form-label fw-semibold" style="font-size: 0.8rem; color: #334155;">Proficiency</label>
                        <input type="range" name="proficiency" class="form-range" min="0" max="100" value="50" 
                               oninput="document.getElementById('proficiencyValue').textContent = this.value + '%'">
                        <div class="text-center mt-1">
                            <span id="proficiencyValue" class="badge bg-light text-dark border fw-normal px-3 py-1" style="font-size: 0.85rem; border-radius: 8px;">
                                50%
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.8rem; color: #334155;">Experience Level</label>
                        <select name="experience_level" class="form-select" style="border-radius: 10px; font-size: 0.85rem; border: 1px solid #e2e8f0; padding: 10px 12px;">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_skill" class="btn btn-primary w-100 py-2" style="border-radius: 10px; font-weight: 500; font-size: 0.85rem; background: #6366f1; border: none;">
                        <i class="fas fa-plus me-2"></i>Add Skill
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function removeSkill(id) {
    if (confirm('Are you sure you want to remove this skill?')) {
        window.location.href = '<?php echo BASE_URL; ?>skills/?remove=' + id;
    }
}
</script>

<style>
/* Range Slider Customization */
.form-range::-webkit-slider-thumb {
    background: #6366f1;
    width: 16px;
    height: 16px;
    margin-top: -6px;
    box-shadow: 0 2px 6px rgba(99,102,241,0.3);
}

.form-range::-webkit-slider-runnable-track {
    height: 6px;
    border-radius: 10px;
    background: #f1f5f9;
}

.form-range::-moz-range-thumb {
    background: #6366f1;
    width: 16px;
    height: 16px;
    border: none;
    box-shadow: 0 2px 6px rgba(99,102,241,0.3);
}

.form-range::-moz-range-track {
    height: 6px;
    border-radius: 10px;
    background: #f1f5f9;
}

/* Sticky Form */
@media (max-width: 767px) {
    .sticky-top {
        position: relative !important;
        top: auto !important;
    }
}
</style>

<?php include_once '../includes/footer.php'; ?>
