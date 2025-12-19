<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

// Handle status updates
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'read') {
        $db->query("UPDATE messages SET status = 'read' WHERE id = ?", [$id]);
    } elseif ($action === 'unread') {
        $db->query("UPDATE messages SET status = 'unread' WHERE id = ?", [$id]);
    } elseif ($action === 'archive') {
        $db->query("UPDATE messages SET status = 'archived' WHERE id = ?", [$id]);
    } elseif ($action === 'delete') {
        $db->query("DELETE FROM messages WHERE id = ?", [$id]);
    } elseif ($action === 'mark_spam') {
        $db->query("UPDATE messages SET spam_status = 'spam' WHERE id = ?", [$id]);
    } elseif ($action === 'mark_clean') {
        $db->query("UPDATE messages SET spam_status = 'clean' WHERE id = ?", [$id]);
    } elseif ($action === 'whitelist') {
        // Get message email
        $message = $db->fetchOne("SELECT email, name FROM messages WHERE id = ?", [$id]);
        if ($message) {
            // Add to whitelist
            $db->query(
                "INSERT IGNORE INTO email_whitelist (email, name, added_by, created_at) VALUES (?, ?, ?, NOW())",
                [$message['email'], $message['name'], $_SESSION['user_id']]
            );
            // Mark message as clean
            $db->query("UPDATE messages SET spam_status = 'clean' WHERE id = ?", [$id]);
        }
    } elseif ($action === 'blacklist') {
        // Get message email
        $message = $db->fetchOne("SELECT email, name FROM messages WHERE id = ?", [$id]);
        if ($message) {
            // Add to blacklist
            $db->query(
                "INSERT IGNORE INTO email_blacklist (email, reason, added_by, created_at) VALUES (?, ?, ?, NOW())",
                [$message['email'], 'Blocked from message spam', $_SESSION['user_id']]
            );
            // Delete the message
            $db->query("DELETE FROM messages WHERE id = ?", [$id]);
        }
    }
    
    header('Location: messages.php');
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM messages";
$params = [];

if ($filter === 'unread') {
    $sql .= " WHERE status = 'unread'";
} elseif ($filter === 'read') {
    $sql .= " WHERE status = 'read'";
} elseif ($filter === 'archived') {
    $sql .= " WHERE status = 'archived'";
} elseif ($filter === 'spam') {
    $sql .= " WHERE spam_status = 'spam'";
} elseif ($filter === 'suspicious') {
    $sql .= " WHERE spam_status = 'suspicious'";
} elseif ($filter === 'clean') {
    $sql .= " WHERE spam_status = 'clean'";
}

$sql .= " ORDER BY created_at DESC";
$messages = $db->fetchAll($sql, $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Messages</h1>
                </div>
                
                <!-- Filter Buttons -->
                <div class="mb-3">
                    <div class="btn-group me-2" role="group">
                        <a href="?filter=all" class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?filter=unread" class="btn btn-outline-warning <?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread</a>
                        <a href="?filter=read" class="btn btn-outline-success <?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
                        <a href="?filter=archived" class="btn btn-outline-secondary <?php echo $filter === 'archived' ? 'active' : ''; ?>">Archived</a>
                    </div>
                    
                    <div class="btn-group" role="group">
                        <a href="?filter=spam" class="btn btn-outline-danger <?php echo $filter === 'spam' ? 'active' : ''; ?>">⚠️ Spam</a>
                        <a href="?filter=suspicious" class="btn btn-outline-warning <?php echo $filter === 'suspicious' ? 'active' : ''; ?>">⚠ Suspicious</a>
                        <a href="?filter=clean" class="btn btn-outline-success <?php echo $filter === 'clean' ? 'active' : ''; ?>">✓ Clean</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <p class="text-muted">No messages found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>From</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Spam</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($messages as $message): ?>
                                            <tr class="<?php echo $message['status'] === 'unread' ? 'table-warning' : ''; ?>">
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $message['status'] === 'unread' ? 'warning' : 
                                                            ($message['status'] === 'read' ? 'success' : 'secondary'); 
                                                    ?>">
                                                        <?php echo ucfirst($message['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                                <td><?php echo htmlspecialchars($message['subject'] ?: 'No subject'); ?></td>
                                                <td>
                                                    <?php if (isset($message['spam_status']) && $message['spam_status'] !== 'clean'): ?>
                                                        <span class="badge bg-<?php echo $message['spam_status'] === 'spam' ? 'danger' : 'warning'; ?>">
                                                            <?php echo $message['spam_status'] === 'spam' ? '⚠️ SPAM' : '⚠ Suspicious'; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">✓ Clean</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatDate($message['created_at']); ?></td>
                                                <td>
                                                    <a href="message-view.php?id=<?php echo $message['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                    
                                                    <?php if (isset($message['spam_status'])): ?>
                                                        <?php if ($message['spam_status'] === 'spam' || $message['spam_status'] === 'suspicious'): ?>
                                                            <a href="?action=mark_clean&id=<?php echo $message['id']; ?>" 
                                                               class="btn btn-sm btn-outline-success" 
                                                               title="Mark as Not Spam">✓ Not Spam</a>
                                                            <a href="?action=whitelist&id=<?php echo $message['id']; ?>" 
                                                               class="btn btn-sm btn-outline-info" 
                                                               title="Add to Whitelist">🛡️ Whitelist</a>
                                                            <a href="?action=blacklist&id=<?php echo $message['id']; ?>" 
                                                               class="btn btn-sm btn-outline-dark" 
                                                               title="Block this email forever"
                                                               onclick="return confirm('Block <?php echo htmlspecialchars($message['email']); ?> permanently? Future messages will be silently ignored.')">🚫 Block</a>
                                                        <?php else: ?>
                                                            <a href="?action=mark_spam&id=<?php echo $message['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger" 
                                                               title="Mark as Spam">⚠️ Mark Spam</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <a href="?action=delete&id=<?php echo $message['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
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
