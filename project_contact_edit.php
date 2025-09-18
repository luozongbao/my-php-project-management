<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

requireLogin();

$db = Database::getInstance();
$user_id = getCurrentUserId();
$project_id = $_GET['project_id'] ?? null;
$contact_id = $_GET['contact_id'] ?? null;

// Validate project access
if (!$project_id) {
    redirect('projects.php');
}

$project = $db->fetchOne(
    "SELECT * FROM projects WHERE id = ? AND responsible_person_id = ?",
    [$project_id, $user_id]
);

if (!$project) {
    redirect('projects.php');
}

$is_editing = $contact_id !== null;
$contact = null;

// If editing, get the existing contact
if ($is_editing) {
    $contact = $db->fetchOne("SELECT * FROM contacts WHERE id = ?", [$contact_id]);
    if (!$contact) {
        redirect("project_contacts.php?project_id=$project_id");
    }
    
    // Check if contact is associated with this project
    $project_contact = $db->fetchOne(
        "SELECT * FROM project_contacts WHERE project_id = ? AND contact_id = ?",
        [$project_id, $contact_id]
    );
    
    if (!$project_contact) {
        redirect("project_contacts.php?project_id=$project_id");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $wechat = trim($_POST['wechat'] ?? '');
    $line_id = trim($_POST['line_id'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Contact name is required and cannot be empty.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Contact name must be 100 characters or less.";
    }
    
    if (!empty($description) && strlen($description) > 1000) {
        $errors[] = "Description must be 1000 characters or less.";
    }
    
    if (!empty($email)) {
        if (!isValidEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        } elseif (strlen($email) > 255) {
            $errors[] = "Email must be 255 characters or less.";
        }
    }
    
    // Validate field lengths
    $string_fields = [
        'phone' => 20,
        'mobile' => 20,
        'company' => 100,
        'position' => 100,
        'address' => 500,
        'wechat' => 50,
        'line_id' => 50,
        'facebook' => 100,
        'linkedin' => 100
    ];
    
    foreach ($string_fields as $field => $max_length) {
        if (!empty($$field) && strlen($$field) > $max_length) {
            $field_name = ucfirst(str_replace('_', ' ', $field));
            $errors[] = "$field_name must be $max_length characters or less.";
        }
    }
    
    if (empty($errors)) {
        try {
            if ($is_editing) {
                // Update existing contact
                $db->execute(
                    "UPDATE contacts SET 
                     name = ?, description = ?, email = ?, phone = ?, mobile = ?,
                     company = ?, position = ?, address = ?, wechat = ?, line_id = ?,
                     facebook = ?, linkedin = ?, updated_at = NOW()
                     WHERE id = ?",
                    [
                        $name, $description, $email, $phone, $mobile,
                        $company, $position, $address, $wechat, $line_id,
                        $facebook, $linkedin, $contact_id
                    ]
                );
                
                redirect("project_contacts.php?project_id=$project_id", 'Contact updated successfully!', 'success');
            } else {
                // Create new contact
                $db->execute(
                    "INSERT INTO contacts (
                        name, description, email, phone, mobile, company, position, address,
                        wechat, line_id, facebook, linkedin, created_at, updated_at
                     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $name, $description, $email, $phone, $mobile,
                        $company, $position, $address, $wechat, $line_id,
                        $facebook, $linkedin
                    ]
                );
                
                $new_contact_id = $db->lastInsertId();
                
                // Associate contact with project
                $db->execute(
                    "INSERT INTO project_contacts (project_id, contact_id, created_at) VALUES (?, ?, NOW())",
                    [$project_id, $new_contact_id]
                );
                
                redirect("project_contacts.php?project_id=$project_id", 'Contact created and added to project successfully!', 'success');
            }
        } catch (Exception $e) {
            $errors[] = "Error saving contact: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <div class="page-title">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span>/</span>
            <a href="projects.php">Projects</a>
            <span>/</span>
            <a href="project_detail.php?id=<?= $project_id ?>"><?= e($project['name']) ?></a>
            <span>/</span>
            <span><?= $is_editing ? 'Edit Contact' : 'Add Contact' ?></span>
        </div>
        <h1>
            <i class="fas fa-<?= $is_editing ? 'user-edit' : 'user-plus' ?>"></i>
            <?= $is_editing ? 'Edit Contact' : 'Add Contact to Project' ?>
        </h1>
        <p><?= $is_editing ? 'Update contact information' : 'Add a new contact to this project' ?></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fas fa-info-circle"></i>
                Contact Information
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <!-- Basic Information -->
                <div class="form-section">
                    <h4>Basic Information</h4>
                    
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" 
                               value="<?= e($_POST['name'] ?? $contact['name'] ?? '') ?>" 
                               required maxlength="100" 
                               placeholder="Enter contact name">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  maxlength="1000" rows="3" 
                                  placeholder="Brief description or notes about this contact"><?= e($_POST['description'] ?? $contact['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" id="company" name="company" 
                                   value="<?= e($_POST['company'] ?? $contact['company'] ?? '') ?>" 
                                   maxlength="100" 
                                   placeholder="Company name">
                        </div>
                        
                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" id="position" name="position" 
                                   value="<?= e($_POST['position'] ?? $contact['position'] ?? '') ?>" 
                                   maxlength="100" 
                                   placeholder="Job title or position">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="form-section">
                    <h4>Contact Details</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= e($_POST['email'] ?? $contact['email'] ?? '') ?>" 
                                   maxlength="255" 
                                   placeholder="email@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?= e($_POST['phone'] ?? $contact['phone'] ?? '') ?>" 
                                   maxlength="20" 
                                   placeholder="Office or home phone">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <input type="tel" id="mobile" name="mobile" 
                                   value="<?= e($_POST['mobile'] ?? $contact['mobile'] ?? '') ?>" 
                                   maxlength="20" 
                                   placeholder="Mobile phone number">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" 
                                   value="<?= e($_POST['address'] ?? $contact['address'] ?? '') ?>" 
                                   maxlength="500" 
                                   placeholder="Physical address">
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="form-section">
                    <h4>Social Media & Messaging</h4>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="wechat">
                                <i class="fab fa-weixin"></i>
                                WeChat
                            </label>
                            <input type="text" id="wechat" name="wechat" 
                                   value="<?= e($_POST['wechat'] ?? $contact['wechat'] ?? '') ?>" 
                                   maxlength="50" 
                                   placeholder="WeChat ID">
                        </div>
                        
                        <div class="form-group">
                            <label for="line_id">
                                <i class="fab fa-line"></i>
                                LINE ID
                            </label>
                            <input type="text" id="line_id" name="line_id" 
                                   value="<?= e($_POST['line_id'] ?? $contact['line_id'] ?? '') ?>" 
                                   maxlength="50" 
                                   placeholder="LINE ID">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="facebook">
                                <i class="fab fa-facebook"></i>
                                Facebook
                            </label>
                            <input type="text" id="facebook" name="facebook" 
                                   value="<?= e($_POST['facebook'] ?? $contact['facebook'] ?? '') ?>" 
                                   maxlength="100" 
                                   placeholder="Facebook profile or username">
                        </div>
                        
                        <div class="form-group">
                            <label for="linkedin">
                                <i class="fab fa-linkedin"></i>
                                LinkedIn
                            </label>
                            <input type="text" id="linkedin" name="linkedin" 
                                   value="<?= e($_POST['linkedin'] ?? $contact['linkedin'] ?? '') ?>" 
                                   maxlength="100" 
                                   placeholder="LinkedIn profile URL or username">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?= $is_editing ? 'Update Contact' : 'Add Contact' ?>
                    </button>
                    <a href="project_contacts.php?project_id=<?= $project_id ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h4 {
    margin: 0 0 20px 0;
    color: #495057;
    font-weight: 600;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>