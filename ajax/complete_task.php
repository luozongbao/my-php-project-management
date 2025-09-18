<?php
/**
 * AJAX endpoint for quick task completion
 */

require_once '../config.php';
require_once '../includes/Database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$task_id = $_POST['task_id'] ?? null;

if (!$task_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Task ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $user_id = getCurrentUserId();
    
    // Verify user has permission to complete this task
    $task = $db->fetchOne(
        "SELECT t.*, p.responsible_person_id as project_owner
         FROM tasks t
         JOIN projects p ON t.project_id = p.id
         WHERE t.id = ? AND (p.responsible_person_id = ? OR t.responsible_person_id = ?)",
        [$task_id, $user_id, $user_id]
    );
    
    if (!$task) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Task not found or access denied']);
        exit;
    }
    
    if ($task['status'] === 'completed') {
        echo json_encode(['success' => false, 'message' => 'Task is already completed']);
        exit;
    }
    
    // Update task to completed status
    $completion_date = date('Y-m-d H:i:s');
    $db->execute(
        "UPDATE tasks SET 
         status = 'completed',
         completion_percentage = 100,
         completion_date = ?,
         updated_at = NOW()
         WHERE id = ?",
        [$completion_date, $task_id]
    );
    
    // Get updated task data
    $updated_task = $db->fetchOne(
        "SELECT id, name, status, completion_percentage, completion_date
         FROM tasks WHERE id = ?",
        [$task_id]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Task marked as completed successfully',
        'task' => $updated_task
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error completing task: ' . $e->getMessage()]);
}
?>