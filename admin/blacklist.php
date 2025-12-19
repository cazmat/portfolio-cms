<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$message = '';
$messageType = '';

// Handle delete (unblock)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->query("DELETE FROM email_blacklist WHERE id = ?", [$id])) {
        $message = 'Email removed from blacklist (unblocked).';
        $messageType = 'success';
    }
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $reason = sanitizeInput($_POST['reason'] ?? '');
    
    if (empty($email)) {
        $message = 'Email address is required.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $sql = "INSERT INTO email_blacklist (email, reason, added_by, created_at) VALUES (?, ?, ?, NOW())";
        if ($db->query($sql, [$email, $reason, $_SESSION['user_id']])) {
            $message = 'Email added to blacklist successfully! Future messages will be silently ignored.';
            $messageType = 'success';
            $_POST = [];
        } else {
            $message = 'Email already exists in blacklist or error occurred.';
            $messageType = 'error';
        }
    }
}

// Get all blacklisted emails
$blacklist = $db->fetchAll(
    "SELECT b.*, u.username as added_by_username 
     FROM email_blacklist b 
     LEFT JOIN users u ON b.added_by = u.id 
     ORDER BY b.created_at DESC"
);

// Count blocked messages (messages that would have been blocked)
$totalBlocked = 0;
foreach ($blacklist as $item) {
    $count = $db->fetchOne(
        "SELECT COUNT(*) as count FROM messages WHERE email = ?",
        [$item['email']]
    );
    $totalBlocked += $count['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Blacklist - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">🚫 Email Blacklist</h1>
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
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">Block Email Address</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" required 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                        <small class="text-muted">This email will be silently blocked from contacting you</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="reason" class="form-label">Reason (Optional)</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3"><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
                                        <small class="text-muted">Why are you blocking this email?</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-danger">🚫 Block Email</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">About Blacklist</h5>
                            </div>
                            <div class="card-body">
                                <h6>What is the blacklist?</h6>
                                <p>The blacklist contains email addresses that are <strong>permanently blocked</strong>. Messages from these emails are silently ignored - they appear successful to the sender but never reach you.</p>
                                
                                <h6 class="mt-3">When to use it:</h6>
                                <ul>
                                    <li>Persistent spammers</li>
                                    <li>Abusive contacts</li>
                                    <li>Repeat offenders</li>
                                    <li>Confirmed spam bots</li>
                                </ul>
                                
                                <h6 class="mt-3">How it works:</h6>
                                <ol>
                                    <li>Sender submits contact form</li>
                                    <li>System checks blacklist</li>
                                    <li>If blocked: Shows success, saves nothing</li>
                                    <li>Sender thinks it worked ✓</li>
                                </ol>
                                
                                <h6 class="mt-3">Stats:</h6>
                                <p class="mb-0">
                                    <strong><?php echo count($blacklist); ?></strong> blocked email(s)<br>
                                    <strong><?php echo $totalBlocked; ?></strong> messages in history from now-blocked senders
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Blocked Emails (<?php echo count($blacklist); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($blacklist)): ?>
                            <p class="text-muted">No blocked emails yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Reason</th>
                                            <th>Blocked By</th>
                                            <th>Date Blocked</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($blacklist as $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['email']); ?></strong>
                                                    <br><span class="badge bg-danger">🚫 Blocked</span>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($item['reason'] ?: 'No reason provided', 0, 100)); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['added_by_username'] ?: 'Unknown'); ?></td>
                                                <td><?php echo formatDate($item['created_at']); ?></td>
                                                <td>
                                                    <a href="?delete=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-outline-success"
                                                       onclick="return confirm('Unblock <?php echo htmlspecialchars($item['email']); ?>? They will be able to contact you again.')">
                                                        ✓ Unblock
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
