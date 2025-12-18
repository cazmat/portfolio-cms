<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$message = '';
$messageType = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->query("DELETE FROM email_whitelist WHERE id = ?", [$id])) {
        $message = 'Email removed from whitelist.';
        $messageType = 'success';
    }
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $name = sanitizeInput($_POST['name'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    if (empty($email)) {
        $message = 'Email address is required.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $sql = "INSERT INTO email_whitelist (email, name, notes, added_by, created_at) VALUES (?, ?, ?, ?, NOW())";
        if ($db->query($sql, [$email, $name, $notes, $_SESSION['user_id']])) {
            $message = 'Email added to whitelist successfully!';
            $messageType = 'success';
            $_POST = [];
        } else {
            $message = 'Email already exists in whitelist or error occurred.';
            $messageType = 'error';
        }
    }
}

// Get all whitelisted emails
$whitelist = $db->fetchAll(
    "SELECT w.*, u.username as added_by_username 
     FROM email_whitelist w 
     LEFT JOIN users u ON w.added_by = u.id 
     ORDER BY w.created_at DESC"
);

// Count messages from each whitelisted email
foreach ($whitelist as &$item) {
    $count = $db->fetchOne(
        "SELECT COUNT(*) as count FROM messages WHERE email = ?",
        [$item['email']]
    );
    $item['message_count'] = $count['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Whitelist - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">🛡️ Email Whitelist</h1>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Add Email to Whitelist</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                        <small class="text-muted">This email will never be marked as spam</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name (Optional)</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                                        <small class="text-muted">Why is this email trusted?</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Add to Whitelist</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">About Whitelist</h5>
                            </div>
                            <div class="card-body">
                                <h6>What is the whitelist?</h6>
                                <p>The whitelist contains trusted email addresses that will <strong>never</strong> be marked as spam, even if they send multiple messages.</p>
                                
                                <h6 class="mt-3">When to use it:</h6>
                                <ul>
                                    <li>Regular clients who contact you often</li>
                                    <li>Business partners</li>
                                    <li>Known contacts</li>
                                    <li>Legitimate follow-up inquiries</li>
                                </ul>
                                
                                <h6 class="mt-3">Stats:</h6>
                                <p class="mb-0">
                                    <strong><?php echo count($whitelist); ?></strong> whitelisted email(s)<br>
                                    <strong><?php echo array_sum(array_column($whitelist, 'message_count')); ?></strong> total messages from whitelisted senders
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Whitelisted Emails (<?php echo count($whitelist); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($whitelist)): ?>
                            <p class="text-muted">No emails in whitelist yet. Add trusted senders above.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Name</th>
                                            <th>Messages</th>
                                            <th>Notes</th>
                                            <th>Added By</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($whitelist as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['email']); ?></strong>
                                                    <br><span class="badge bg-success">✓ Whitelisted</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['name'] ?: '-'); ?></td>
                                                <td>
                                                    <?php if ($item['message_count'] > 0): ?>
                                                        <a href="messages.php?search=<?php echo urlencode($item['email']); ?>">
                                                            <?php echo $item['message_count']; ?> message(s)
                                                        </a>
                                                    <?php else: ?>
                                                        0 messages
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($item['notes'] ?: 'No notes', 0, 50)); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['added_by_username'] ?: 'Unknown'); ?></td>
                                                <td><?php echo formatDate($item['created_at']); ?></td>
                                                <td>
                                                    <a href="?delete=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Remove <?php echo htmlspecialchars($item['email']); ?> from whitelist?')">
                                                        Remove
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
