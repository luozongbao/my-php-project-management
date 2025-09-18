<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

requireLogin();

$db = Database::getInstance();
$user_id = getCurrentUserId();
$project_id = $_GET['id'] ?? null;
$errors = [];

// Handle form submission for project creation/update
if ($_POST) {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $expected_completion_date = $_POST['expected_completion_date'] ?? null;
    $completion_date = $_POST['completion_date'] ?? null;
    $status = $_POST['status'] ?? 'not_started';
    
    // Convert dates to UTC and handle empty values
    if ($expected_completion_date && trim($expected_completion_date) !== '') {
        $expected_completion_date = toUTC($expected_completion_date . ' 00:00:00');
    } else {
        $expected_completion_date = null;
    }
    if ($completion_date && trim($completion_date) !== '') {
        $completion_date = toUTC($completion_date . ' 00:00:00');
    } else {
        $completion_date = null;
    }
    
    // Validation
    if (empty($name)) $errors[] = "Project name is required";
    
    if (empty($errors)) {
        try {
            if ($project_id) {
                // Update existing project
                $db->execute(
                    "UPDATE projects SET name = ?, description = ?, expected_completion_date = ?, 
                     completion_date = ?, status = ?, updated_at = NOW() WHERE id = ? AND responsible_person_id = ?",
                    [$name, $description, $expected_completion_date, $completion_date, $status, $project_id, $user_id]
                );
                redirect('project_detail.php?id=' . $project_id, 'Project updated successfully!', 'success');
            } else {
                // Create new project
                $db->execute(
                    "INSERT INTO projects (name, description, responsible_person_id, expected_completion_date, 
                     completion_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$name, $description, $user_id, $expected_completion_date, $completion_date, $status]
                );
                $new_project_id = $db->lastInsertId();
                redirect('project_detail.php?id=' . $new_project_id, 'Project created successfully!', 'success');
            }
        } catch (Exception $e) {
            $errors[] = "Failed to save project. Please try again.";
        }
    }
}

// Load existing project data
$project = null;
if ($project_id) {
    $project = $db->fetchOne(
        "SELECT * FROM projects WHERE id = ? AND responsible_person_id = ?",
        [$project_id, $user_id]
    );
    
    if (!$project) {
        redirect('projects.php', 'Project not found.', 'danger');
    }
}

$title = $project ? "Edit Project" : "New Project";
$show_nav = true;
?>

<?php include 'includes/header.php'; ?>

<div class="page-container">
    <div class="page-header">
        <div class="page-title">
            <h1>
                <i class="fas fa-<?= $project ? 'edit' : 'plus' ?>"></i>
                <?= $project ? 'Edit Project' : 'New Project' ?>
            </h1>
            <p><?= $project ? 'Update your project details' : 'Create a new project to get started' ?></p>
        </div>
        <div class="page-actions">
            <a href="<?= $project ? 'project_detail.php?id=' . $project['id'] : 'projects.php' ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <?= $project ? 'Back to Project' : 'Back to Projects' ?>
            </a>
        </div>
    </div>

    <div class="form-container">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div><?= e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-project-diagram"></i>
                                Project Name *
                            </label>
                            <input type="text" id="name" name="name" 
                                   value="<?= e($project['name'] ?? $_POST['name'] ?? '') ?>" 
                                   required maxlength="255">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-flag"></i>
                                Status
                            </label>
                            <select id="status" name="status" required>
                                <option value="not_started" <?= ($project['status'] ?? $_POST['status'] ?? 'not_started') == 'not_started' ? 'selected' : '' ?>>
                                    Not Started
                                </option>
                                <option value="in_progress" <?= ($project['status'] ?? $_POST['status'] ?? '') == 'in_progress' ? 'selected' : '' ?>>
                                    In Progress
                                </option>
                                <option value="completed" <?= ($project['status'] ?? $_POST['status'] ?? '') == 'completed' ? 'selected' : '' ?>>
                                    Completed
                                </option>
                                <option value="on_hold" <?= ($project['status'] ?? $_POST['status'] ?? '') == 'on_hold' ? 'selected' : '' ?>>
                                    On Hold
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left"></i>
                            Project Description
                        </label>
                        <textarea id="description" name="description" rows="4" 
                                  placeholder="Describe the project goals, scope, and requirements..."><?= e($project['description'] ?? $_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="expected_completion_date">
                                <i class="fas fa-calendar"></i>
                                Expected Completion Date
                            </label>
                            <input type="date" id="expected_completion_date" name="expected_completion_date"
                                   value="<?= formatDateForInput($project['expected_completion_date'] ?? $_POST['expected_completion_date'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="completion_date">
                                <i class="fas fa-calendar-check"></i>
                                Actual Completion Date
                            </label>
                            <input type="date" id="completion_date" name="completion_date"
                                   value="<?= formatDateForInput($project['completion_date'] ?? $_POST['completion_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?= $project ? 'Update Project' : 'Create Project' ?>
                        </button>
                        
                        <a href="<?= $project ? 'project_detail.php?id=' . $project['id'] : 'projects.php' ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        
                        <?php if ($project): ?>
                            <button type="button" class="btn btn-danger" onclick="deleteProject()" style="margin-left: auto;">
                                <i class="fas fa-trash"></i>
                                Delete Project
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.page-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.form-container {
    margin-top: 0;
}

.form-actions {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}

.form-actions .btn {
    min-width: 140px;
}

.form-group label i {
    margin-right: 8px;
    color: #666;
    width: 16px;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions .btn {
        width: 100%;
        margin-left: 0 !important;
    }
}
</style>

<?php if ($project): ?>
<script>
function deleteProject() {
    if (confirm('Are you sure you want to delete this project?\n\nThis will also delete all associated tasks and cannot be undone.')) {
        window.location.href = 'project_delete.php?id=<?= $project['id'] ?>';
    }
}

// Auto-update completion date when status changes to completed
document.getElementById('status').addEventListener('change', function() {
    const completionDateField = document.getElementById('completion_date');
    
    if (this.value === 'completed' && !completionDateField.value) {
        // Set to today's date
        const today = new Date().toISOString().split('T')[0];
        completionDateField.value = today;
    } else if (this.value !== 'completed') {
        // Clear completion date if status is not completed
        completionDateField.value = '';
    }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>