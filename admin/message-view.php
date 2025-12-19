<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: messages.php');
    exit();
}

// Get message
$message = $db->fetchOne("SELECT * FROM messages WHERE id = ?", [$id]);

if (!$message) {
    header('Location: messages.php');
    exit();
}

// Mark as read if unread
if ($message['status'] === 'unread') {
    $db->query("UPDATE messages SET status = 'read' WHERE id = ?", [$id]);
    $message['status'] = 'read';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">View Message</h1>
                    <a href="messages.php" class="btn btn-secondary">Back to Messages</a>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></h5>
                            <span class="badge bg-<?php 
                                echo $message['status'] === 'unread' ? 'warning' : 
                                    ($message['status'] === 'read' ? 'success' : 'secondary'); 
                            ?>">
                                <?php echo ucfirst($message['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Email:</strong> 
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Date:</strong> <?php echo formatDate($message['created_at']); ?>
                        </div>
                        
                        <?php if (isset($message['spam_status'])): ?>
                        <div class="mb-3">
                            <strong>Spam Status:</strong> 
                            <span class="badge bg-<?php 
                                echo $message['spam_status'] === 'spam' ? 'danger' : 
                                    ($message['spam_status'] === 'suspicious' ? 'warning' : 'success'); 
                            ?>">
                                <?php 
                                    if ($message['spam_status'] === 'spam') {
                                        echo '⚠️ SPAM - Email has sent messages before';
                                    } elseif ($message['spam_status'] === 'suspicious') {
                                        echo '⚠ SUSPICIOUS - Email contains suspicious patterns';
                                    } else {
                                        echo '✓ CLEAN';
                                    }
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <strong>Message:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>" 
                               class="btn btn-primary">Reply via Email</a>
                            
                            <?php if (isset($message['spam_status'])): ?>
                                <?php if ($message['spam_status'] === 'spam' || $message['spam_status'] === 'suspicious'): ?>
                                    <a href="messages.php?action=mark_clean&id=<?php echo $message['id']; ?>" 
                                       class="btn btn-success">✓ Mark as Not Spam</a>
                                    <a href="messages.php?action=whitelist&id=<?php echo $message['id']; ?>" 
                                       class="btn btn-info">🛡️ Add to Whitelist</a>
                                    <a href="messages.php?action=blacklist&id=<?php echo $message['id']; ?>" 
                                       class="btn btn-dark"
                                       onclick="return confirm('Block <?php echo htmlspecialchars($message['email']); ?> permanently? Future messages will be silently ignored and this message will be deleted.')">🚫 Block Email</a>
                                <?php else: ?>
                                    <a href="messages.php?action=mark_spam&id=<?php echo $message['id']; ?>" 
                                       class="btn btn-warning">⚠️ Mark as Spam</a>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($message['status'] !== 'archived'): ?>
                                <a href="messages.php?action=archive&id=<?php echo $message['id']; ?>" 
                                   class="btn btn-secondary">Archive</a>
                            <?php endif; ?>
                            
                            <a href="messages.php?action=delete&id=<?php echo $message['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
