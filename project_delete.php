<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

requireLogin();

$project_id = $_GET['id'] ?? null;
$user_id = getCurrentUserId();

if (!$project_id) {
    redirect('projects.php', 'Invalid project ID.', 'danger');
}

$db = Database::getInstance();

// Verify project ownership
$project = $db->fetchOne(
    "SELECT id FROM projects WHERE id = ? AND responsible_person_id = ?",
    [$project_id, $user_id]
);

if (!$project) {
    redirect('projects.php', 'Project not found or access denied.', 'danger');
}

try {
    // Delete project (cascade will handle tasks and project_contacts)
    $db->query("DELETE FROM projects WHERE id = ?", [$project_id]);
    
    redirect('projects.php', 'Project deleted successfully.', 'success');
} catch (Exception $e) {
    error_log("Project deletion error: " . $e->getMessage());
    redirect('projects.php', 'Failed to delete project.', 'danger');
}
?>