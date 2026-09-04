<?php
require_once '../config.php';
requireLogin();

// Only AJAX requests
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
}

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$command = $_POST['ai_command'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';

// Verify CSRF
if (!verifyCSRFToken($csrf_token)) {
    echo json_encode(['success' => false, 'message' => '❌ Invalid security token.']);
    exit();
}

if (empty($command)) {
    echo json_encode(['success' => false, 'message' => '❌ Please type a command.']);
    exit();
}

$originalCommand = trim($command);
$command = strtolower(trim($command));
$response = ['success' => false, 'message' => '', 'reload' => false, 'action' => ''];

// ============================================
// ULTIMATE HELPER FUNCTIONS
// ============================================

// Multi-language translation map
function translateCommand($text) {
    $translations = [
        // English keywords
        'create project' => 'create project', 'new project' => 'create project', 'make project' => 'create project',
        'add project' => 'create project', 'project banao' => 'create project', 'project banay' => 'create project',
        'project bnao' => 'create project',
        
        'add task' => 'add task', 'new task' => 'add task', 'create task' => 'add task',
        'task add' => 'add task', 'task banao' => 'add task', 'task banay' => 'add task',
        'kaam add' => 'add task', 'kaam banao' => 'add task',
        
        'add skill' => 'add skill', 'new skill' => 'add skill', 'learn skill' => 'add skill',
        'skill add' => 'add skill', 'skill banao' => 'add skill', 'seekho' => 'add skill',
        
        'create goal' => 'create goal', 'new goal' => 'create goal', 'add goal' => 'create goal',
        'goal banao' => 'create goal', 'learning goal' => 'create goal',
        
        'show stats' => 'show stats', 'my stats' => 'show stats', 'stats' => 'show stats',
        'report' => 'show stats', 'summary' => 'show stats', 'mery stats' => 'show stats',
        
        'show projects' => 'show projects', 'my projects' => 'show projects', 'list projects' => 'show projects',
        'projects dikhao' => 'show projects', 'projects' => 'show projects',
        
        'show tasks' => 'show tasks', 'my tasks' => 'show tasks', 'list tasks' => 'show tasks',
        'tasks dikhao' => 'show tasks', 'tasks' => 'show tasks',
        
        'show skills' => 'show skills', 'my skills' => 'show skills', 'list skills' => 'show skills',
        'skills dikhao' => 'show skills', 'skills' => 'show skills',
        
        'show goals' => 'show goals', 'my goals' => 'show goals', 'show learning' => 'show goals',
        'goals dikhao' => 'show goals',
        
        'mark task complete' => 'mark task complete', 'complete task' => 'mark task complete',
        'task complete' => 'mark task complete', 'task done' => 'mark task complete',
        'task mukammal' => 'mark task complete', 'kaam complete' => 'mark task complete',
        'kaam ho gaya' => 'mark task complete', 'mark task completed' => 'mark task complete',
        'mark task done' => 'mark task complete',
        
        'mark task in progress' => 'mark task in progress', 'start task' => 'mark task in progress',
        'task shuru' => 'mark task in progress', 'kaam shuru' => 'mark task in progress',
        'task progress' => 'mark task in progress', 'mark task started' => 'mark task in progress',
        
        'delete task' => 'delete task', 'remove task' => 'delete task',
        'task delete' => 'delete task', 'task hatao' => 'delete task', 'kaam hatao' => 'delete task',
        
        'delete project' => 'delete project', 'remove project' => 'delete project',
        'project delete' => 'delete project', 'project hatao' => 'delete project',
        
        'complete project' => 'complete project', 'finish project' => 'complete project',
        'project complete' => 'complete project', 'project mukammal' => 'complete project',
        'mark project complete' => 'complete project', 'mark project completed' => 'complete project',
        
        'update skill' => 'update skill', 'improve skill' => 'update skill',
        'skill update' => 'update skill',
        
        'update goal' => 'update goal', 'progress goal' => 'update goal',
        'goal update' => 'update goal',
        
        'delete goal' => 'delete goal', 'remove goal' => 'delete goal',
        'goal delete' => 'delete goal', 'goal hatao' => 'delete goal',
        'complete goal' => 'complete goal', 'finish goal' => 'complete goal',
        'goal complete' => 'complete goal', 'goal mukammal' => 'complete goal',
        'mark goal complete' => 'complete goal', 'mark goal completed' => 'complete goal',
        
        'hello' => 'hello', 'hi' => 'hello', 'hey' => 'hello', 'salam' => 'hello',
        'assalam' => 'hello', 'good morning' => 'hello', 'good afternoon' => 'hello',
        'good evening' => 'hello', 'salam alaikum' => 'hello',
        
        'thank' => 'thanks', 'thanks' => 'thanks', 'shukriya' => 'thanks',
        'help' => 'help', 'commands' => 'help', 'what can you do' => 'help',
        'who are you' => 'who are you', 'what are you' => 'who are you',
        'your name' => 'who are you',
    ];
    
    $lowerText = strtolower(trim($text));
    
    // Check exact match
    if (isset($translations[$lowerText])) {
        return $translations[$lowerText];
    }
    
    // Check contains
    foreach ($translations as $key => $value) {
        if (strpos($lowerText, $key) !== false) {
            return $value;
        }
    }
    
    return $lowerText;
}

// Extract date from text (supports multiple languages)
function extractDate($text) {
    // English months
    $months = [
        'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
        'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
        'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        // Urdu months
        'جنوری' => 1, 'فروری' => 2, 'مارچ' => 3, 'اپریل' => 4,
        'مئی' => 5, 'جون' => 6, 'جولائی' => 7, 'اگست' => 8,
        'ستمبر' => 9, 'اکتوبر' => 10, 'نومبر' => 11, 'دسمبر' => 12,
    ];
    
    // Check for "12 december" or "12th december"
    if (preg_match('/(\d{1,2})(?:st|nd|rd|th)?\s+([a-z]+|[^\s]+)/i', $text, $matches)) {
        $day = (int)$matches[1];
        $monthName = strtolower($matches[2]);
        
        if (isset($months[$monthName])) {
            return date('Y-m-d', mktime(0, 0, 0, $months[$monthName], $day, date('Y')));
        }
    }
    
    // Check for DD/MM/YYYY
    if (preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $text, $matches)) {
        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        if ($year < 100) $year += 2000;
        return date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
    }
    
    // Relative dates
    if (strpos($text, 'tomorrow') !== false || strpos($text, 'kal') !== false || strpos($text, 'کل') !== false) {
        return date('Y-m-d', strtotime('+1 day'));
    }
    if (strpos($text, 'next week') !== false || strpos($text, 'agle hafte') !== false) {
        return date('Y-m-d', strtotime('+7 days'));
    }
    if (strpos($text, 'today') !== false || strpos($text, 'aaj') !== false || strpos($text, 'آج') !== false) {
        return date('Y-m-d');
    }
    
    // X days/weeks/months from now
    if (preg_match('/(\d+)\s+(day|days|din|دن)/', $text, $matches)) {
        return date('Y-m-d', strtotime('+' . $matches[1] . ' days'));
    }
    if (preg_match('/(\d+)\s+(week|weeks|hafte|ہفتے)/', $text, $matches)) {
        return date('Y-m-d', strtotime('+' . $matches[1] . ' weeks'));
    }
    if (preg_match('/(\d+)\s+(month|months|mahina|مہینہ)/', $text, $matches)) {
        return date('Y-m-d', strtotime('+' . $matches[1] . ' months'));
    }
    
    return null;
}

// Extract priority from text (multi-language)
function extractPriority($text) {
    if (strpos($text, 'high') !== false || strpos($text, 'urgent') !== false || strpos($text, 'important') !== false ||
        strpos($text, 'zarruri') !== false || strpos($text, 'bohat') !== false || strpos($text, 'ضروری') !== false) {
        return 'high';
    }
    if (strpos($text, 'low') !== false || strpos($text, 'minor') !== false || strpos($text, 'kam') !== false) {
        return 'low';
    }
    return 'medium';
}

// Extract proficiency from text
function extractProficiency($text) {
    if (preg_match('/(\d{1,3})\s*%/', $text, $matches)) {
        return min(100, max(0, (int)$matches[1]));
    }
    if (strpos($text, 'beginner') !== false || strpos($text, 'shuru') !== false) return 25;
    if (strpos($text, 'intermediate') !== false || strpos($text, 'medium') !== false) return 50;
    if (strpos($text, 'advanced') !== false || strpos($text, 'good') !== false || strpos($text, 'acha') !== false) return 75;
    if (strpos($text, 'expert') !== false || strpos($text, 'master') !== false) return 100;
    return 50;
}

// Extract experience level
function extractExperience($text) {
    if (strpos($text, 'expert') !== false || strpos($text, 'master') !== false) return 'expert';
    if (strpos($text, 'advanced') !== false) return 'advanced';
    if (strpos($text, 'intermediate') !== false) return 'intermediate';
    return 'beginner';
}

// Extract item name from text (ULTIMATE VERSION)
function extractItemName($text, $keywords) {
    // Pattern: "named X" or "name X" or "X naam"
    if (preg_match('/named\s+(.+?)(?=\s+(with|by|due|deadline|date|high|low|medium|urgent|priority|important|category|for|to|at|in|on|complete|completed|done|delete|remove|update|progress|%|and|as|the)\s|$)/i', $text, $matches)) {
        return trim($matches[1]);
    }
    
    if (preg_match('/name\s+(.+?)(?=\s+(with|by|due|deadline|date|high|low|medium|urgent|priority|important|category|for|to|at|in|on|complete|completed|done|delete|remove|update|progress|%|and|as|the)\s|$)/i', $text, $matches)) {
        return trim($matches[1]);
    }
    
    if (preg_match('/naam\s+(.+?)(?=\s+(with|by|due|deadline|date|high|low|medium|urgent|priority|important|category|for|to|at|in|on|complete|completed|done|delete|remove|update|progress|%|and|as|the)\s|$)/i', $text, $matches)) {
        return trim($matches[1]);
    }
    
    // Pattern: "task X" or "project X" or "skill X" or "goal X"
    foreach ($keywords as $keyword) {
        if (preg_match('/' . $keyword . '\s+(.+?)(?=\s+(with|by|due|deadline|date|high|low|medium|urgent|priority|important|category|for|to|at|in|on|complete|completed|done|delete|remove|update|progress|%|and|named|name|as|the)\s|$)/i', $text, $matches)) {
            return trim($matches[1]);
        }
    }
    
    // Fallback: Remove action words and return remaining
    $cleanText = preg_replace('/\s*(mark|complete|completed|done|delete|remove|update|progress|task|project|skill|goal|as|the|in|on|at|with|to|for|by|my|this|that)\s*$/i', '', trim($text));
    $cleanText = preg_replace('/\s*(mark|complete|completed|done|delete|remove|update|progress|task|project|skill|goal|as|the|in|on|at|with|to|for|by|my|this|that)\s+/i', ' ', $cleanText);
    
    if (!empty($cleanText) && strlen($cleanText) > 2) {
        return trim($cleanText);
    }
    
    return null;
}

// Find task by name (fuzzy)
function findTaskByName($taskName, $userId) {
    if (empty($taskName)) return null;
    
    $task = fetchOne("SELECT * FROM tasks WHERE user_id = ? AND LOWER(title) = ? ORDER BY created_at DESC LIMIT 1", 
        [$userId, strtolower($taskName)]);
    
    if (!$task) {
        $task = fetchOne("SELECT * FROM tasks WHERE user_id = ? AND LOWER(title) LIKE ? ORDER BY created_at DESC LIMIT 1", 
            [$userId, '%' . strtolower($taskName) . '%']);
    }
    
    return $task;
}

// Find project by name (fuzzy)
function findProjectByName($projectName, $userId) {
    if (empty($projectName)) return null;
    
    $project = fetchOne("SELECT * FROM projects WHERE user_id = ? AND LOWER(title) = ? ORDER BY created_at DESC LIMIT 1", 
        [$userId, strtolower($projectName)]);
    
    if (!$project) {
        $project = fetchOne("SELECT * FROM projects WHERE user_id = ? AND LOWER(title) LIKE ? ORDER BY created_at DESC LIMIT 1", 
            [$userId, '%' . strtolower($projectName) . '%']);
    }
    
    return $project;
}

// Find skill by name (fuzzy)
function findSkillByName($skillName, $userId) {
    if (empty($skillName)) return null;
    
    $skill = fetchOne("SELECT us.*, s.name FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ? AND LOWER(s.name) = ?", 
        [$userId, strtolower($skillName)]);
    
    if (!$skill) {
        $skill = fetchOne("SELECT us.*, s.name FROM user_skills us JOIN skills s ON us.skill_id = s.id WHERE us.user_id = ? AND LOWER(s.name) LIKE ?", 
            [$userId, '%' . strtolower($skillName) . '%']);
    }
    
    return $skill;
}

// Find goal by name (fuzzy)
function findGoalByName($goalName, $userId) {
    if (empty($goalName)) return null;
    
    $goal = fetchOne("SELECT * FROM learning_goals WHERE user_id = ? AND LOWER(title) = ? ORDER BY created_at DESC LIMIT 1", 
        [$userId, strtolower($goalName)]);
    
    if (!$goal) {
        $goal = fetchOne("SELECT * FROM learning_goals WHERE user_id = ? AND LOWER(title) LIKE ? ORDER BY created_at DESC LIMIT 1", 
            [$userId, '%' . strtolower($goalName) . '%']);
    }
    
    return $goal;
}

// ============================================
// MAIN AI LOGIC - ULTIMATE VERSION
// ============================================

// First, translate command
$translatedCommand = translateCommand($originalCommand);

// 1. MARK TASK COMPLETE (ULTIMATE FIX)
if (preg_match('/mark\s+task\s+named\s+(.+?)\s+(as\s+)?(complete|completed|done|finished|mukammal|ho\s+gaya)/i', $originalCommand) || 
    preg_match('/mark\s+task\s+(.+?)\s+(as\s+)?(complete|completed|done|finished|mukammal|ho\s+gaya)/i', $originalCommand) ||
    strpos($translatedCommand, 'mark task complete') !== false || 
    strpos($translatedCommand, 'mark task completed') !== false ||
    strpos($translatedCommand, 'complete task') !== false || 
    strpos($translatedCommand, 'task complete') !== false || 
    strpos($translatedCommand, 'task done') !== false ||
    strpos($translatedCommand, 'task mukammal') !== false || 
    strpos($translatedCommand, 'kaam complete') !== false || 
    strpos($translatedCommand, 'kaam ho gaya') !== false) {
    
    // Extract task name
    $taskName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal|ho\s+gaya)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/name\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal|ho\s+gaya)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/naam\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal|ho\s+gaya)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/task\s+named\s+(.+?)\s+(complete|completed|done|finished|mukammal)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/task\s+(.+?)\s+(complete|completed|done|finished|mukammal)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/mark\s+task\s+(.+?)\s+(complete|completed|done|finished)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } else {
        $taskName = extractItemName($originalCommand, ['task', 'kaam']);
    }
    
    $task = findTaskByName($taskName, $userId);
    
    if (!$task) {
        $response['message'] = '❌ Task "<strong>' . $taskName . '</strong>" not found. Try: <strong>show tasks</strong> to see your tasks.';
    } else {
        update("UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE id = ?", [$task['id']]);
        logActivity($userId, 'task_completed', "Completed task: {$task['title']} (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'task_completed';
        $response['message'] = '✅ <strong>Task marked as completed!</strong><br>📋 Task: <strong>' . $task['title'] . '</strong><br>📊 Status: ✅ Completed';
    }
}

// 2. MARK TASK IN PROGRESS
elseif (preg_match('/mark\s+task\s+named\s+(.+?)\s+(as\s+)?(in\s+progress|started|start|pending|shuru)/i', $originalCommand) || 
        preg_match('/mark\s+task\s+(.+?)\s+(as\s+)?(in\s+progress|started|start|pending|shuru)/i', $originalCommand) ||
        strpos($translatedCommand, 'mark task in progress') !== false || 
        strpos($translatedCommand, 'start task') !== false || 
        strpos($translatedCommand, 'task shuru') !== false || 
        strpos($translatedCommand, 'kaam shuru') !== false ||
        strpos($translatedCommand, 'task progress') !== false || 
        strpos($translatedCommand, 'mark task started') !== false) {
    
    $taskName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(as\s+)?(in\s+progress|started|start|pending|shuru)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/name\s+(.+?)(?=\s+(as\s+)?(in\s+progress|started|start|pending|shuru)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/naam\s+(.+?)(?=\s+(as\s+)?(in\s+progress|started|start|pending|shuru)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/task\s+(.+?)\s+(in\s+progress|started|start|shuru)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/mark\s+task\s+(.+?)\s+(in\s+progress|started|start)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } else {
        $taskName = extractItemName($originalCommand, ['task', 'kaam']);
    }
    
    $task = findTaskByName($taskName, $userId);
    
    if (!$task) {
        $response['message'] = '❌ Task "<strong>' . $taskName . '</strong>" not found.';
    } else {
        $newStatus = 'in-progress';
        update("UPDATE tasks SET status = ? WHERE id = ?", [$newStatus, $task['id']]);
        logActivity($userId, 'task_updated', "Updated task: {$task['title']} to {$newStatus} (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'task_updated';
        $response['message'] = '✅ <strong>Task updated!</strong><br>📋 Task: <strong>' . $task['title'] . '</strong><br>📊 Status: ' . ucfirst($newStatus);
    }
}

// 3. COMPLETE PROJECT (ULTIMATE FIX)
elseif (preg_match('/mark\s+project\s+named\s+(.+?)\s+(as\s+)?(complete|completed|done|finished|mukammal)/i', $originalCommand) || 
        preg_match('/mark\s+project\s+(.+?)\s+(as\s+)?(complete|completed|done|finished|mukammal)/i', $originalCommand) ||
        preg_match('/complete\s+project\s+named\s+(.+?)$/i', $originalCommand) ||
        strpos($translatedCommand, 'complete project') !== false || 
        strpos($translatedCommand, 'finish project') !== false || 
        strpos($translatedCommand, 'project complete') !== false || 
        strpos($translatedCommand, 'project mukammal') !== false ||
        strpos($translatedCommand, 'mark project complete') !== false || 
        strpos($translatedCommand, 'mark project completed') !== false) {
    
    $projectName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal)\s|$)/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } elseif (preg_match('/name\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal)\s|$)/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } elseif (preg_match('/complete\s+project\s+(.+?)$/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } elseif (preg_match('/finish\s+project\s+(.+?)$/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } elseif (preg_match('/mark\s+project\s+(.+?)\s+(complete|completed|done|finished)/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } else {
        $projectName = extractItemName($originalCommand, ['project']);
    }
    
    $project = findProjectByName($projectName, $userId);
    
    if (!$project) {
        $response['message'] = '❌ Project "<strong>' . $projectName . '</strong>" not found.';
    } else {
        update("UPDATE projects SET status = 'completed', completed_at = NOW() WHERE id = ?", [$project['id']]);
        logActivity($userId, 'project_completed', "Completed project: {$project['title']} (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'project_completed';
        $response['message'] = '✅ <strong>Project completed!</strong><br>📁 Project: <strong>' . $project['title'] . '</strong> marked as completed. 🎉';
    }
}

// 4. COMPLETE GOAL (ULTIMATE FIX)
elseif (preg_match('/mark\s+goal\s+named\s+(.+?)\s+(as\s+)?(complete|completed|done|finished|mukammal)/i', $originalCommand) || 
        preg_match('/mark\s+goal\s+(.+?)\s+(as\s+)?(complete|completed|done|finished|mukammal)/i', $originalCommand) ||
        preg_match('/complete\s+goal\s+named\s+(.+?)$/i', $originalCommand) ||
        strpos($translatedCommand, 'complete goal') !== false || 
        strpos($translatedCommand, 'finish goal') !== false || 
        strpos($translatedCommand, 'goal complete') !== false || 
        strpos($translatedCommand, 'goal mukammal') !== false ||
        strpos($translatedCommand, 'mark goal complete') !== false || 
        strpos($translatedCommand, 'mark goal completed') !== false) {
    
    $goalName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal)\s|$)/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
    } elseif (preg_match('/name\s+(.+?)(?=\s+(as\s+)?(complete|completed|done|finished|mukammal)\s|$)/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
    } elseif (preg_match('/complete\s+goal\s+(.+?)$/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
    } elseif (preg_match('/finish\s+goal\s+(.+?)$/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
    } elseif (preg_match('/mark\s+goal\s+(.+?)\s+(complete|completed|done|finished)/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
    } else {
        $goalName = extractItemName($originalCommand, ['goal']);
    }
    
    $goal = findGoalByName($goalName, $userId);
    
    if (!$goal) {
        $response['message'] = '❌ Goal "<strong>' . $goalName . '</strong>" not found.';
    } else {
        update("UPDATE learning_goals SET status = 'completed', progress = 100, completed_at = NOW() WHERE id = ?", [$goal['id']]);
        logActivity($userId, 'learning_goal_completed', "Completed goal: {$goal['title']} (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'goal_completed';
        $response['message'] = '✅ <strong>Goal completed!</strong><br>📚 Goal: <strong>' . $goal['title'] . '</strong> marked as completed. 🎉';
    }
}

// 5. DELETE TASK
elseif (strpos($translatedCommand, 'delete task') !== false || strpos($translatedCommand, 'remove task') !== false || strpos($translatedCommand, 'task hatao') !== false || strpos($translatedCommand, 'kaam hatao') !== false) {
    $taskName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(delete|remove|hatao)\s|$)/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/delete\s+task\s+(.+?)$/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } elseif (preg_match('/remove\s+task\s+(.+?)$/i', $originalCommand, $matches)) {
        $taskName = trim($matches[1]);
    } else {
        $taskName = extractItemName($originalCommand, ['task', 'kaam']);
    }
    
    $task = findTaskByName($taskName, $userId);
    
    if (!$task) {
        $response['message'] = '❌ Task "<strong>' . $taskName . '</strong>" not found.';
    } else {
        delete("DELETE FROM tasks WHERE id = ?", [$task['id']]);
        logActivity($userId, 'task_deleted', "Deleted task: {$task['title']} (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'task_deleted';
        $response['message'] = '✅ <strong>Task deleted!</strong><br>📋 Task: <strong>' . $task['title'] . '</strong> removed.';
    }
}

// 6. DELETE PROJECT
elseif (strpos($translatedCommand, 'delete project') !== false || strpos($translatedCommand, 'remove project') !== false || strpos($translatedCommand, 'project hatao') !== false) {
    $projectName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(delete|remove|hatao)\s|$)/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } elseif (preg_match('/delete\s+project\s+(.+?)$/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } elseif (preg_match('/remove\s+project\s+(.+?)$/i', $originalCommand, $matches)) {
        $projectName = trim($matches[1]);
    } else {
        $projectName = extractItemName($originalCommand, ['project']);
    }
    
    $project = findProjectByName($projectName, $userId);
    
    if (!$project) {
        $response['message'] = '❌ Project "<strong>' . $projectName . '</strong>" not found.';
    } else {
        delete("DELETE FROM tasks WHERE project_id = ?", [$project['id']]);
        delete("DELETE FROM projects WHERE id = ?", [$project['id']]);
        logActivity($userId, 'project_deleted', "Deleted project: {$project['title']} (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'project_deleted';
        $response['message'] = '✅ <strong>Project deleted!</strong><br>📁 Project: <strong>' . $project['title'] . '</strong> removed.';
    }
}

// 7. CREATE PROJECT
elseif (strpos($translatedCommand, 'create project') !== false) {
    $title = trim(str_replace(['create project', 'new project', 'make project', 'add project'], '', $originalCommand));
    $deadline = extractDate($originalCommand);
    $priority = extractPriority($originalCommand);
    $description = '';
    $status = 'planning';
    
    if (strpos($command, 'in progress') !== false || strpos($command, 'started') !== false || strpos($command, 'shuru') !== false) {
        $status = 'in-progress';
    } elseif (strpos($command, 'complete') !== false || strpos($command, 'done') !== false || strpos($command, 'mukammal') !== false) {
        $status = 'completed';
    }
    
    $title = preg_replace('/\s*(with|by|due|deadline|date|high|low|medium|urgent|priority|important|for|ko|se|tak|mein)\s*.*$/i', '', $title);
    $title = trim($title);
    
    if (preg_match('/about\s+(.+)/i', $originalCommand, $matches)) {
        $description = trim($matches[1]);
        $title = str_replace($matches[0], '', $title);
        $title = trim($title);
    }
    
    if (empty($title)) {
        $response['message'] = '❌ Please specify a project name. Example: <strong>create project MyApp by december</strong>';
    } else {
        $existing = fetchOne("SELECT id FROM projects WHERE user_id = ? AND LOWER(title) = ?", [$userId, strtolower($title)]);
        
        if ($existing) {
            $response['message'] = 'ℹ️ Project "<strong>' . $title . '</strong>" already exists. Try: <strong>show projects</strong>';
        } else {
            $projectId = insert(
                "INSERT INTO projects (user_id, title, description, status, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)",
                [$userId, $title, $description, $status, date('Y-m-d'), $deadline]
            );
            
            logActivity($userId, 'project_created', "Created project: $title (via AI)");
            
            $response['success'] = true;
            $response['reload'] = true;
            $response['action'] = 'project_created';
            $response['message'] = '✅ <strong>Project created!</strong><br>' .
                '📁 Project: <strong>' . $title . '</strong><br>' .
                '📋 Status: ' . ucfirst(str_replace('-', ' ', $status)) . '<br>' .
                ($deadline ? '📅 Deadline: ' . date('M d, Y', strtotime($deadline)) . '<br>' : '') .
                ($description ? '📝 Description: ' . $description . '<br>' : '');
        }
    }
}

// 8. ADD TASK
elseif (strpos($translatedCommand, 'add task') !== false) {
    $taskTitle = trim(str_replace(['add task', 'new task', 'create task', 'add todo', 'task add', 'kaam add'], '', $originalCommand));
    $dueDate = extractDate($originalCommand);
    $priority = extractPriority($originalCommand);
    $status = 'pending';
    $category = '';
    
    if (strpos($command, 'in progress') !== false || strpos($command, 'started') !== false || strpos($command, 'shuru') !== false) {
        $status = 'in-progress';
    } elseif (strpos($command, 'complete') !== false || strpos($command, 'done') !== false || strpos($command, 'finished') !== false || strpos($command, 'mukammal') !== false) {
        $status = 'completed';
    }
    
    if (preg_match('/category\s+(.+?)(?=\s+(with|by|due|high|low|medium|priority|for)\s|$)/i', $command, $matches)) {
        $category = trim($matches[1]);
    }
    
    $taskTitle = preg_replace('/\s*(with|by|due|deadline|date|high|low|medium|urgent|priority|important|category|for|ko|se|tak)\s*.*$/i', '', $taskTitle);
    $taskTitle = trim($taskTitle);
    
    if (empty($taskTitle)) {
        $response['message'] = '❌ Please specify a task title. Example: <strong>add task Fix bug with high priority</strong>';
    } else {
        $projectId = null;
        $project = fetchOne("SELECT id FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$userId]);
        if ($project) $projectId = $project['id'];
        
        insert(
            "INSERT INTO tasks (user_id, project_id, title, status, priority, category, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $projectId, $taskTitle, $status, $priority, $category, $dueDate]
        );
        
        logActivity($userId, 'task_created', "Created task: $taskTitle (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'task_created';
        $response['message'] = '✅ <strong>Task created!</strong><br>' .
            '📋 Task: <strong>' . $taskTitle . '</strong><br>' .
            '📌 Priority: ' . ucfirst($priority) . '<br>' .
            '📊 Status: ' . ucfirst($status) . '<br>' .
            ($dueDate ? '📅 Due: ' . date('M d, Y', strtotime($dueDate)) . '<br>' : '') .
            ($category ? '🏷️ Category: ' . $category . '<br>' : '');
    }
}

// 9. ADD SKILL
elseif (strpos($translatedCommand, 'add skill') !== false || strpos($translatedCommand, 'seekho') !== false) {
    $skillName = trim(str_replace(['add skill', 'new skill', 'learn skill', 'skill add', 'skill banao', 'seekho'], '', $originalCommand));
    $proficiency = extractProficiency($originalCommand);
    $experience = extractExperience($originalCommand);
    
    $skillName = preg_replace('/\s*(with|at|level|proficiency|beginner|intermediate|advanced|expert|%|for|ko|se)\s*.*$/i', '', $skillName);
    $skillName = trim($skillName);
    
    if (empty($skillName)) {
        $response['message'] = '❌ Please specify a skill name. Example: <strong>add skill Python with 80%</strong>';
    } else {
        $skillName = ucfirst($skillName);
        
        $skill = fetchOne("SELECT id FROM skills WHERE LOWER(name) = ?", [strtolower($skillName)]);
        $skillId = $skill ? $skill['id'] : insert("INSERT INTO skills (name) VALUES (?)", [$skillName]);
        
        $existing = fetchOne("SELECT id FROM user_skills WHERE user_id = ? AND skill_id = ?", [$userId, $skillId]);
        
        if ($existing) {
            update("UPDATE user_skills SET proficiency = ?, experience_level = ? WHERE id = ?", [$proficiency, $experience, $existing['id']]);
            $response['message'] = '✅ <strong>Skill updated!</strong><br>🏷️ Skill: <strong>' . $skillName . '</strong><br>📊 Proficiency: ' . $proficiency . '%';
        } else {
            insert("INSERT INTO user_skills (user_id, skill_id, proficiency, experience_level) VALUES (?, ?, ?, ?)", [$userId, $skillId, $proficiency, $experience]);
            logActivity($userId, 'skill_added', "Added skill: $skillName with $proficiency% (via AI)");
            $response['message'] = '✅ <strong>Skill added!</strong><br>🏷️ Skill: <strong>' . $skillName . '</strong><br>📊 Proficiency: ' . $proficiency . '%<br>🎓 Level: ' . ucfirst($experience);
        }
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'skill_added';
    }
}

// 10. CREATE GOAL
elseif (strpos($translatedCommand, 'create goal') !== false || strpos($translatedCommand, 'goal banao') !== false) {
    $goalTitle = trim(str_replace(['create goal', 'new goal', 'add goal', 'learning goal', 'goal banao'], '', $originalCommand));
    $progress = 0;
    $status = 'not-started';
    $targetDate = extractDate($originalCommand);
    
    if (preg_match('/(\d{1,3})\s*%/', $command, $matches)) {
        $progress = min(100, max(0, (int)$matches[1]));
        if ($progress > 0) $status = 'in-progress';
    }
    
    if (strpos($command, 'completed') !== false || strpos($command, 'done') !== false || strpos($command, 'mukammal') !== false) {
        $status = 'completed';
        $progress = 100;
    } elseif (strpos($command, 'in progress') !== false || strpos($command, 'started') !== false || strpos($command, 'shuru') !== false) {
        $status = 'in-progress';
    }
    
    $goalTitle = preg_replace('/\s*(with|by|due|deadline|date|progress|%|completed|in progress|for|ko|se|tak)\s*.*$/i', '', $goalTitle);
    $goalTitle = trim($goalTitle);
    
    if (empty($goalTitle)) {
        $response['message'] = '❌ Please specify a goal title. Example: <strong>create goal Learn React with 20%</strong>';
    } else {
        insert(
            "INSERT INTO learning_goals (user_id, title, progress, status, start_date, target_date, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $goalTitle, $progress, $status, date('Y-m-d'), $targetDate, ($status == 'completed' ? date('Y-m-d H:i:s') : null)]
        );
        
        logActivity($userId, 'learning_goal_created', "Created goal: $goalTitle with $progress% (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'goal_created';
        $response['message'] = '✅ <strong>Goal created!</strong><br>' .
            '📚 Goal: <strong>' . $goalTitle . '</strong><br>' .
            '📊 Progress: ' . $progress . '%<br>' .
            '📋 Status: ' . ucfirst(str_replace('-', ' ', $status)) . '<br>' .
            ($targetDate ? '📅 Target: ' . date('M d, Y', strtotime($targetDate)) . '<br>' : '');
    }
}

// 11. UPDATE SKILL
elseif (strpos($translatedCommand, 'update skill') !== false || strpos($translatedCommand, 'improve skill') !== false || strpos($translatedCommand, 'skill update') !== false) {
    $skillName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(to|with)\s+(\d{1,3})\s*%?)/i', $originalCommand, $matches)) {
        $skillName = trim($matches[1]);
        $newProficiency = min(100, max(0, (int)$matches[3]));
    } elseif (preg_match('/update\s+skill\s+(.+?)\s+(to|with)\s+(\d{1,3})\s*%?/i', $originalCommand, $matches)) {
        $skillName = trim($matches[1]);
        $newProficiency = min(100, max(0, (int)$matches[3]));
    } else {
        $skillName = extractItemName($originalCommand, ['skill']);
        $newProficiency = extractProficiency($originalCommand);
    }
    
    $skill = findSkillByName($skillName, $userId);
    
    if (!$skill) {
        $response['message'] = '❌ Skill "<strong>' . $skillName . '</strong>" not found. Try: <strong>add skill ' . $skillName . '</strong>';
    } else {
        update("UPDATE user_skills SET proficiency = ?, experience_level = ? WHERE id = ?", [$newProficiency, extractExperience($originalCommand), $skill['id']]);
        logActivity($userId, 'skill_updated', "Updated skill: {$skill['name']} to {$newProficiency}% (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'skill_updated';
        $response['message'] = '✅ <strong>Skill updated!</strong><br>🏷️ Skill: <strong>' . $skill['name'] . '</strong><br>📊 Proficiency: ' . $newProficiency . '%';
    }
}

// 12. UPDATE GOAL
elseif (strpos($translatedCommand, 'update goal') !== false || strpos($translatedCommand, 'progress goal') !== false || strpos($translatedCommand, 'goal update') !== false) {
    $goalName = '';
    
    if (preg_match('/named\s+(.+?)(?=\s+(to|with)\s+(\d{1,3})\s*%?)/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
        $newProgress = min(100, max(0, (int)$matches[3]));
    } elseif (preg_match('/update\s+goal\s+(.+?)\s+(to|with)\s+(\d{1,3})\s*%?/i', $originalCommand, $matches)) {
        $goalName = trim($matches[1]);
        $newProgress = min(100, max(0, (int)$matches[3]));
    } else {
        $goalName = extractItemName($originalCommand, ['goal']);
        $newProgress = extractProficiency($originalCommand);
    }
    
    $goal = findGoalByName($goalName, $userId);
    
    if (!$goal) {
        $response['message'] = '❌ Goal "<strong>' . $goalName . '</strong>" not found.';
    } else {
        $status = $newProgress >= 100 ? 'completed' : 'in-progress';
        update("UPDATE learning_goals SET progress = ?, status = ? WHERE id = ?", [$newProgress, $status, $goal['id']]);
        
        if ($status == 'completed') {
            update("UPDATE learning_goals SET completed_at = NOW() WHERE id = ?", [$goal['id']]);
        }
        
        logActivity($userId, 'learning_goal_updated', "Updated goal: {$goal['title']} to {$newProgress}% (via AI)");
        
        $response['success'] = true;
        $response['reload'] = true;
        $response['action'] = 'goal_updated';
        $response['message'] = '✅ <strong>Goal updated!</strong><br>📚 Goal: <strong>' . $goal['title'] . '</strong><br>📊 Progress: ' . $newProgress . '%<br>📋 Status: ' . ucfirst($status);
    }
}

// 13. SHOW STATS
elseif (strpos($translatedCommand, 'show stats') !== false || strpos($translatedCommand, 'report') !== false) {
    $totalProjects = getProjectsCount($userId);
    $completedProjects = getProjectsCount($userId, 'completed');
    $inProgressProjects = getProjectsCount($userId, 'in-progress');
    
    $totalTasks = getTasksCount($userId);
    $completedTasks = getTasksCount($userId, 'completed');
    $pendingTasks = getTasksCount($userId, 'pending');
    $inProgressTasks = getTasksCount($userId, 'in-progress');
    $highPriorityTasks = fetchColumn("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND priority = 'high' AND status != 'completed'", [$userId]);
    
    $skills = getUserSkills($userId);
    $skillCount = count($skills);
    $avgProficiency = $skillCount > 0 ? round(array_sum(array_column($skills, 'proficiency')) / $skillCount) : 0;
    
    $learningGoals = getLearningGoalsCount($userId);
    $completedGoals = getLearningGoalsCount($userId, 'completed');
    $inProgressGoals = getLearningGoalsCount($userId, 'in-progress');
    
    $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
    
    $response['success'] = true;
    $response['message'] = '📊 <strong>Your Stats:</strong><br><br>' .
        '<strong>📁 Projects:</strong> ' . $totalProjects . '<br>' .
        '&nbsp;&nbsp;✅ Completed: ' . $completedProjects . '<br>' .
        '&nbsp;&nbsp;🔄 In Progress: ' . $inProgressProjects . '<br><br>' .
        '<strong>📋 Tasks:</strong> ' . $totalTasks . '<br>' .
        '&nbsp;&nbsp;✅ Completed: ' . $completedTasks . '<br>' .
        '&nbsp;&nbsp;🔄 In Progress: ' . $inProgressTasks . '<br>' .
        '&nbsp;&nbsp;⏳ Pending: ' . $pendingTasks . '<br>' .
        '&nbsp;&nbsp;🔥 High Priority: ' . $highPriorityTasks . '<br><br>' .
        '<strong>🏷️ Skills:</strong> ' . $skillCount . ' (Avg: ' . $avgProficiency . '%)<br><br>' .
        '<strong>📚 Goals:</strong> ' . $learningGoals . '<br>' .
        '&nbsp;&nbsp;✅ Completed: ' . $completedGoals . '<br>' .
        '&nbsp;&nbsp;🔄 In Progress: ' . $inProgressGoals . '<br><br>' .
        '<strong>🎯 Productivity:</strong> ' . $completionRate . '%';
}

// 14. SHOW PROJECTS
elseif (strpos($translatedCommand, 'show projects') !== false || strpos($translatedCommand, 'projects dikhao') !== false) {
    $projects = fetchAll("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    
    if (empty($projects)) {
        $response['message'] = '📁 You don\'t have any projects yet. Try: <strong>create project MyApp</strong>';
    } else {
        $msg = '📁 <strong>Your Projects (' . count($projects) . '):</strong><br><br>';
        foreach ($projects as $project) {
            $taskCount = fetchColumn("SELECT COUNT(*) FROM tasks WHERE project_id = ?", [$project['id']]);
            $statusColor = ['planning' => '#f59e0b', 'in-progress' => '#6366f1', 'completed' => '#10b981', 'on-hold' => '#94a3b8', 'cancelled' => '#ef4444'];
            $color = $statusColor[$project['status']] ?? '#64748b';
            
            $msg .= '• <strong>' . $project['title'] . '</strong> - <span style="color: ' . $color . ';">' . str_replace('-', ' ', $project['status']) . '</span>';
            if ($taskCount > 0) $msg .= ' (' . $taskCount . ' tasks)';
            if ($project['end_date']) $msg .= ' - 📅 ' . date('M d', strtotime($project['end_date']));
            $msg .= '<br>';
        }
        $response['success'] = true;
        $response['message'] = $msg;
    }
}

// 15. SHOW TASKS
elseif (strpos($translatedCommand, 'show tasks') !== false || strpos($translatedCommand, 'tasks dikhao') !== false) {
    $tasks = fetchAll("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    
    if (empty($tasks)) {
        $response['message'] = '📋 You don\'t have any tasks yet. Try: <strong>add task Fix bug</strong>';
    } else {
        $msg = '📋 <strong>Your Tasks (' . count($tasks) . '):</strong><br><br>';
        foreach ($tasks as $task) {
            $priorityColor = ['low' => '#10b981', 'medium' => '#f59e0b', 'high' => '#ef4444'];
            $statusColor = ['pending' => '#f59e0b', 'in-progress' => '#6366f1', 'completed' => '#10b981'];
            $pColor = $priorityColor[$task['priority']] ?? '#64748b';
            $sColor = $statusColor[$task['status']] ?? '#64748b';
            
            $msg .= '• <strong>' . $task['title'] . '</strong><br>';
            $msg .= '&nbsp;&nbsp;📌 Priority: <span style="color: ' . $pColor . ';">' . ucfirst($task['priority']) . '</span><br>';
            $msg .= '&nbsp;&nbsp;📊 Status: <span style="color: ' . $sColor . ';">' . ucfirst($task['status']) . '</span><br>';
            if ($task['due_date'] && $task['due_date'] != '0000-00-00') $msg .= '&nbsp;&nbsp;📅 Due: ' . date('M d, Y', strtotime($task['due_date'])) . '<br>';
            $msg .= '<br>';
        }
        $response['success'] = true;
        $response['message'] = $msg;
    }
}

// 16. SHOW SKILLS
elseif (strpos($translatedCommand, 'show skills') !== false || strpos($translatedCommand, 'skills dikhao') !== false) {
    $skills = getUserSkills($userId);
    
    if (empty($skills)) {
        $response['message'] = '🏷️ You don\'t have any skills yet. Try: <strong>add skill PHP</strong>';
    } else {
        $msg = '🏷️ <strong>Your Skills (' . count($skills) . '):</strong><br><br>';
        foreach ($skills as $skill) {
            $msg .= '• <strong>' . $skill['name'] . '</strong> - ' . $skill['proficiency'] . '% (' . $skill['experience_level'] . ')<br>';
        }
        $response['success'] = true;
        $response['message'] = $msg;
    }
}

// 17. SHOW GOALS
elseif (strpos($translatedCommand, 'show goals') !== false || strpos($translatedCommand, 'goals dikhao') !== false) {
    $goals = fetchAll("SELECT * FROM learning_goals WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    
    if (empty($goals)) {
        $response['message'] = '📚 You don\'t have any learning goals yet. Try: <strong>create goal Learn React</strong>';
    } else {
        $msg = '📚 <strong>Your Learning Goals (' . count($goals) . '):</strong><br><br>';
        foreach ($goals as $goal) {
            $statusColor = ['not-started' => '#94a3b8', 'in-progress' => '#6366f1', 'completed' => '#10b981', 'paused' => '#f59e0b'];
            $color = $statusColor[$goal['status']] ?? '#64748b';
            
            $msg .= '• <strong>' . $goal['title'] . '</strong> - ' . $goal['progress'] . '% - <span style="color: ' . $color . ';">' . str_replace('-', ' ', $goal['status']) . '</span><br>';
        }
        $response['success'] = true;
        $response['message'] = $msg;
    }
}

// 18. GREETINGS
elseif (strpos($translatedCommand, 'hello') !== false) {
    $time = date('H');
    $greeting = 'Hello';
    if ($time < 12) $greeting = 'Good Morning';
    elseif ($time < 17) $greeting = 'Good Afternoon';
    else $greeting = 'Good Evening';
    
    $response['success'] = true;
    $response['message'] = '👋 <strong>' . $greeting . '!</strong> How can I help you today?<br><br>' .
        '📁 <strong>create project MyApp by 12 december</strong><br>' .
        '✅ <strong>mark task named Fix bug complete</strong><br>' .
        '🏷️ <strong>add skill Python with 80%</strong><br>' .
        '📚 <strong>create goal Learn React with 20%</strong><br>' .
        '📊 <strong>show my stats</strong>';
}

// 19. THANKS
elseif (strpos($translatedCommand, 'thanks') !== false || strpos($translatedCommand, 'thank') !== false || strpos($translatedCommand, 'shukriya') !== false) {
    $response['success'] = true;
    $response['message'] = '🙌 <strong>You\'re welcome!</strong> Happy to help! Is there anything else you need?';
}

// 20. WHO ARE YOU
elseif (strpos($translatedCommand, 'who are you') !== false || strpos($translatedCommand, 'what are you') !== false) {
    $response['success'] = true;
    $response['message'] = '🤖 <strong>I\'m DevTrack AI Assistant!</strong><br><br>' .
        'I can help you manage projects, tasks, skills, and goals.<br>' .
        'I understand multiple languages!<br><br>' .
        'Try: <strong>mark task named Fix bug complete</strong>';
}

// 21. HELP
elseif (strpos($translatedCommand, 'help') !== false || strpos($translatedCommand, 'commands') !== false) {
    $response['success'] = true;
    $response['message'] = '🤖 <strong>I can help you with:</strong><br><br>' .
        '<strong>📁 Projects:</strong><br>' .
        '&nbsp;&nbsp;• create project [name] by [date]<br>' .
        '&nbsp;&nbsp;• mark project named [name] completed<br>' .
        '&nbsp;&nbsp;• delete project named [name]<br>' .
        '&nbsp;&nbsp;• show projects<br><br>' .
        '<strong>📋 Tasks:</strong><br>' .
        '&nbsp;&nbsp;• add task [title] with [priority] by [date]<br>' .
        '&nbsp;&nbsp;• mark task named [title] completed<br>' .
        '&nbsp;&nbsp;• mark task named [title] in progress<br>' .
        '&nbsp;&nbsp;• delete task named [title]<br>' .
        '&nbsp;&nbsp;• show tasks<br><br>' .
        '<strong>🏷️ Skills:</strong><br>' .
        '&nbsp;&nbsp;• add skill [name] with [X]%<br>' .
        '&nbsp;&nbsp;• update skill named [name] to [X]%<br>' .
        '&nbsp;&nbsp;• show skills<br><br>' .
        '<strong>📚 Goals:</strong><br>' .
        '&nbsp;&nbsp;• create goal [title] with [X]%<br>' .
        '&nbsp;&nbsp;• update goal named [title] to [X]%<br>' .
        '&nbsp;&nbsp;• mark goal named [title] completed<br>' .
        '&nbsp;&nbsp;• show goals<br><br>' .
        '<strong>📊 General:</strong><br>' .
        '&nbsp;&nbsp;• show my stats<br>' .
        '&nbsp;&nbsp;• hello<br>' .
        '&nbsp;&nbsp;• help';
}

// 22. DEFAULT / FALLBACK
else {
    $response['message'] = '❌ <strong>Sorry, I didn\'t understand that.</strong><br><br>' .
        'Try these examples:<br><br>' .
        '📁 <strong>create project Ecommerce by 15 january</strong><br>' .
        '✅ <strong>mark task named Fix bug completed</strong><br>' .
        '🏷️ <strong>add skill Python with 80%</strong><br>' .
        '📚 <strong>create goal Learn React with 20%</strong><br>' .
        '📊 <strong>show my stats</strong><br><br>' .
        'Type <strong>help</strong> to see all commands.';
}

echo json_encode($response);
?>