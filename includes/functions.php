<?php
// =============================================
// Helper Functions
// =============================================

// Get user by ID
function getUserById($id) {
    return fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
}

// Get user by username
function getUserByUsername($username) {
    return fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
}

// Get user by email
function getUserByEmail($email) {
    return fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
}

// Get user skills
function getUserSkills($userId) {
    return fetchAll("
        SELECT s.*, us.proficiency, us.experience_level 
        FROM user_skills us 
        JOIN skills s ON us.skill_id = s.id 
        WHERE us.user_id = ? 
        ORDER BY us.proficiency DESC
    ", [$userId]);
}

// Get user projects count
function getProjectsCount($userId, $status = null) {
    $sql = "SELECT COUNT(*) FROM projects WHERE user_id = ?";
    $params = [$userId];
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    return fetchColumn($sql, $params);
}

// Get user tasks count
function getTasksCount($userId, $status = null) {
    $sql = "SELECT COUNT(*) FROM tasks WHERE user_id = ?";
    $params = [$userId];
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    return fetchColumn($sql, $params);
}

// Get user learning goals count
function getLearningGoalsCount($userId, $status = null) {
    $sql = "SELECT COUNT(*) FROM learning_goals WHERE user_id = ?";
    $params = [$userId];
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    return fetchColumn($sql, $params);
}

// Get recent activities
function getRecentActivities($userId, $limit = 10) {
    return fetchAll("
        SELECT * FROM activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ?
    ", [$userId, $limit]);
}

// Log activity
function logActivity($userId, $action, $details = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    insert(
        "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
        [$userId, $action, $details, $ip, $agent]
    );
}

// Create notification
function createNotification($userId, $type, $title, $message, $link = null) {
    insert(
        "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)",
        [$userId, $type, $title, $message, $link]
    );
}

// Get unread notifications count
function getUnreadNotificationsCount($userId) {
    return fetchColumn("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    if (!$date) return 'N/A';
    return date($format, strtotime($date));
}

// Time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    if ($diff < 2592000) return floor($diff / 604800) . 'w ago';
    return date('M d, Y', $time);
}

// Get status badge class
function getStatusBadge($status, $type = 'project') {
    $map = [
        'project' => [
            'planning' => 'secondary',
            'in-progress' => 'primary',
            'completed' => 'success',
            'on-hold' => 'warning',
            'cancelled' => 'danger'
        ],
        'task' => [
            'pending' => 'warning',
            'in-progress' => 'primary',
            'completed' => 'success'
        ],
        'learning' => [
            'not-started' => 'secondary',
            'in-progress' => 'primary',
            'completed' => 'success',
            'paused' => 'warning'
        ]
    ];
    
    $classes = $map[$type] ?? $map['project'];
    return $classes[$status] ?? 'secondary';
}

// Get priority badge class
function getPriorityBadge($priority) {
    $map = [
        'low' => 'success',
        'medium' => 'warning',
        'high' => 'danger'
    ];
    return $map[$priority] ?? 'secondary';
}

// Get experience level badge class
function getExperienceBadge($level) {
    $map = [
        'beginner' => 'secondary',
        'intermediate' => 'primary',
        'advanced' => 'warning',
        'expert' => 'success'
    ];
    return $map[$level] ?? 'secondary';
}

// Get progress color
function getProgressColor($progress) {
    if ($progress >= 80) return 'success';
    if ($progress >= 50) return 'info';
    if ($progress >= 25) return 'warning';
    return 'danger';
}

// Generate pagination
function paginate($totalItems, $currentPage, $itemsPerPage = 10, $url = '?') {
    $totalPages = ceil($totalItems / $itemsPerPage);
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . ($currentPage - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Pages
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . 'page=' . ($currentPage + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

// Get total users count
function getTotalUsers() {
    return fetchColumn("SELECT COUNT(*) FROM users");
}

// Get total projects
function getTotalProjects() {
    return fetchColumn("SELECT COUNT(*) FROM projects");
}

// Get total tasks
function getTotalTasks() {
    return fetchColumn("SELECT COUNT(*) FROM tasks");
}

// Get completed tasks
function getCompletedTasks() {
    return fetchColumn("SELECT COUNT(*) FROM tasks WHERE status = 'completed'");
}

// Get pending tasks
function getPendingTasks() {
    return fetchColumn("SELECT COUNT(*) FROM tasks WHERE status != 'completed'");
}
?>