<?php
require_once 'config.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

requireLogin();

$db = Database::getInstance();
$user_id = getCurrentUserId();
$project_id = $_GET['project_id'] ?? null;

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

// Handle contact removal from project
if ($_POST['action'] ?? '' === 'remove_contact' && isset($_POST['contact_id'])) {
    $contact_id = $_POST['contact_id'];
    try {
        $db->execute(
            "DELETE FROM project_contacts WHERE project_id = ? AND contact_id = ?",
            [$project_id, $contact_id]
        );
        redirect("project_contacts.php?project_id=$project_id", 'Contact removed from project successfully!', 'success');
    } catch (Exception $e) {
        $error_message = "Error removing contact: " . $e->getMessage();
    }
}

// Get project contacts with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Search functionality
$search = trim($_GET['search'] ?? '');
$search_condition = '';
$search_params = [$project_id];

if (!empty($search)) {
    $search_condition = "AND (c.name LIKE ? OR c.email LIKE ? OR c.company LIKE ? OR c.position LIKE ?)";
    $search_term = "%$search%";
    $search_params = array_merge($search_params, [$search_term, $search_term, $search_term, $search_term]);
}

// Get total count
$total_contacts = $db->fetchOne(
    "SELECT COUNT(*) as count 
     FROM contacts c
     JOIN project_contacts pc ON c.id = pc.contact_id
     WHERE pc.project_id = ? $search_condition",
    $search_params
)['count'] ?? 0;

// Get contacts
$contacts = $db->fetchAll(
    "SELECT c.*, pc.created_at as added_to_project
     FROM contacts c
     JOIN project_contacts pc ON c.id = pc.contact_id
     WHERE pc.project_id = ? $search_condition
     ORDER BY c.name ASC
     LIMIT $per_page OFFSET $offset",
    $search_params
);

// Calculate pagination
$total_pages = ceil($total_contacts / $per_page);

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
            <span>Contacts</span>
        </div>
        <h1>
            <i class="fas fa-users"></i>
            Project Contacts
            <?php if (!empty($search)): ?>
                <span class="search-context">- "<?= e($search) ?>"</span>
            <?php endif; ?>
        </h1>
        <p>Manage contacts associated with <?= e($project['name']) ?></p>
    </div>
    <div class="page-actions">
        <a href="project_contact_edit.php?project_id=<?= $project_id ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Contact
        </a>
        <a href="project_detail.php?id=<?= $project_id ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Project
        </a>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= e($error_message) ?>
    </div>
<?php endif; ?>

<!-- Search and Filter -->
<div class="search-section">
    <div class="container">
        <form method="GET" action="" class="search-form">
            <input type="hidden" name="project_id" value="<?= $project_id ?>">
            <div class="search-input-group">
                <input type="text" name="search" value="<?= e($search) ?>" 
                       placeholder="Search contacts by name, email, company, or position..." 
                       class="search-input">
                <button type="submit" class="btn btn-outline search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="project_contacts.php?project_id=<?= $project_id ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Contact Statistics -->
<div class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= $total_contacts ?></div>
                    <div class="stat-label">Total Contacts</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <?php 
                    $contacts_with_email = $db->fetchOne(
                        "SELECT COUNT(*) as count 
                         FROM contacts c
                         JOIN project_contacts pc ON c.id = pc.contact_id
                         WHERE pc.project_id = ? AND c.email IS NOT NULL AND c.email != ''",
                        [$project_id]
                    )['count'] ?? 0;
                    ?>
                    <div class="stat-value"><?= $contacts_with_email ?></div>
                    <div class="stat-label">With Email</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <?php 
                    $companies_count = $db->fetchOne(
                        "SELECT COUNT(DISTINCT c.company) as count 
                         FROM contacts c
                         JOIN project_contacts pc ON c.id = pc.contact_id
                         WHERE pc.project_id = ? AND c.company IS NOT NULL AND c.company != ''",
                        [$project_id]
                    )['count'] ?? 0;
                    ?>
                    <div class="stat-value"><?= $companies_count ?></div>
                    <div class="stat-label">Companies</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contacts List -->
<div class="container">
    <?php if (empty($contacts)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <?php if (!empty($search)): ?>
                <h3>No Contacts Found</h3>
                <p>No contacts match your search criteria. Try adjusting your search terms.</p>
                <a href="project_contacts.php?project_id=<?= $project_id ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Clear Search
                </a>
            <?php else: ?>
                <h3>No Contacts Yet</h3>
                <p>This project doesn't have any contacts associated with it yet.</p>
                <a href="project_contact_edit.php?project_id=<?= $project_id ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Your First Contact
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="contacts-grid">
            <?php foreach ($contacts as $contact): ?>
                <div class="contact-card">
                    <div class="contact-header">
                        <div class="contact-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="contact-info">
                            <h3>
                                <a href="contact_detail.php?id=<?= $contact['id'] ?>">
                                    <?= e($contact['name']) ?>
                                </a>
                            </h3>
                            <?php if ($contact['company'] || $contact['position']): ?>
                                <p class="contact-job">
                                    <?php if ($contact['position']): ?>
                                        <?= e($contact['position']) ?>
                                    <?php endif; ?>
                                    <?php if ($contact['company'] && $contact['position']): ?>
                                        at 
                                    <?php endif; ?>
                                    <?php if ($contact['company']): ?>
                                        <?= e($contact['company']) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="contact-actions">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline dropdown-toggle" type="button">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="contact_detail.php?id=<?= $contact['id'] ?>" class="dropdown-item">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <a href="project_contact_edit.php?project_id=<?= $project_id ?>&contact_id=<?= $contact['id'] ?>" class="dropdown-item">
                                        <i class="fas fa-edit"></i> Edit Contact
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <button type="button" class="dropdown-item text-danger remove-contact-btn" 
                                            data-contact-id="<?= $contact['id'] ?>" 
                                            data-contact-name="<?= e($contact['name']) ?>">
                                        <i class="fas fa-unlink"></i> Remove from Project
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($contact['description']): ?>
                        <div class="contact-description">
                            <p><?= e(substr($contact['description'], 0, 150)) ?><?= strlen($contact['description']) > 150 ? '...' : '' ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="contact-details">
                        <?php if ($contact['email']): ?>
                            <div class="contact-detail">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($contact['mobile']): ?>
                            <div class="contact-detail">
                                <i class="fas fa-mobile-alt"></i>
                                <a href="tel:<?= e($contact['mobile']) ?>"><?= e($contact['mobile']) ?></a>
                            </div>
                        <?php elseif ($contact['phone']): ?>
                            <div class="contact-detail">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?= e($contact['phone']) ?>"><?= e($contact['phone']) ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="contact-meta">
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus"></i>
                                Added <?= formatDateTime($contact['added_to_project'], 'M j, Y') ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    Showing <?= ($offset + 1) ?> to <?= min($offset + $per_page, $total_contacts) ?> of <?= $total_contacts ?> contacts
                </div>
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?project_id=<?= $project_id ?>&page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?project_id=<?= $project_id ?>&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                           class="page-link <?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?project_id=<?= $project_id ?>&page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="page-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Remove Contact Modal -->
<div id="removeContactModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Remove Contact from Project</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to remove <strong id="contactNameSpan"></strong> from this project?</p>
            <p class="text-muted">This will only remove the association with this project. The contact will still exist and can be added to other projects.</p>
        </div>
        <div class="modal-footer">
            <form method="POST" action="">
                <input type="hidden" name="action" value="remove_contact">
                <input type="hidden" name="contact_id" id="removeContactId">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-danger">Remove from Project</button>
            </form>
        </div>
    </div>
</div>

<style>
.search-context {
    color: #6c757d;
    font-weight: normal;
}

.search-section {
    background: #f8f9fa;
    padding: 20px 0;
    margin-bottom: 30px;
}

.search-form {
    max-width: 600px;
    margin: 0 auto;
}

.search-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
}

.stats-section {
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    max-width: 800px;
    margin: 0 auto;
}

.contacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.contact-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.contact-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.contact-header {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 15px;
}

.contact-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.contact-info {
    flex: 1;
    min-width: 0;
}

.contact-info h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.contact-info h3 a {
    color: #333;
    text-decoration: none;
}

.contact-info h3 a:hover {
    color: var(--primary-color);
}

.contact-job {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.contact-description {
    margin-bottom: 15px;
}

.contact-description p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

.contact-details {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
}

.contact-detail {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 14px;
}

.contact-detail i {
    width: 16px;
    color: #666;
}

.contact-detail a {
    color: #333;
    text-decoration: none;
}

.contact-detail a:hover {
    color: var(--primary-color);
}

.contact-meta {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f8f9fa;
}

.dropdown {
    position: relative;
}

.dropdown-toggle {
    border: none !important;
    padding: 8px !important;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    min-width: 200px;
    z-index: 1000;
}

.dropdown.active .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: block;
    padding: 12px 16px;
    color: #333;
    text-decoration: none;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 14px;
}

.dropdown-item:hover {
    background: #f8f9fa;
}

.dropdown-item.text-danger {
    color: #dc3545;
}

.dropdown-divider {
    height: 1px;
    background: #e9ecef;
    margin: 8px 0;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #666;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contacts-grid {
        grid-template-columns: 1fr;
    }
    
    .search-input-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .contact-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .contact-actions {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown functionality
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns
            document.querySelectorAll('.dropdown.active').forEach(dropdown => {
                if (dropdown !== this.parentElement) {
                    dropdown.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            this.parentElement.classList.toggle('active');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    });
    
    // Remove contact functionality
    const modal = document.getElementById('removeContactModal');
    const contactNameSpan = document.getElementById('contactNameSpan');
    const removeContactId = document.getElementById('removeContactId');
    
    document.querySelectorAll('.remove-contact-btn').forEach(button => {
        button.addEventListener('click', function() {
            const contactId = this.getAttribute('data-contact-id');
            const contactName = this.getAttribute('data-contact-name');
            
            contactNameSpan.textContent = contactName;
            removeContactId.value = contactId;
            modal.style.display = 'block';
        });
    });
    
    // Close modal
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>