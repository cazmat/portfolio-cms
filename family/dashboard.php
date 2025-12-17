<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();

// Only Family members can access this dashboard
if (!isFamily()) {
    if (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$db = new Database();

// Get user info
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);

// Get upcoming schedule events
$upcomingEvents = $db->fetchAll(
    "SELECT * FROM schedule WHERE event_date >= CURDATE() AND status = 'scheduled' ORDER BY event_date ASC, start_time ASC LIMIT 10"
);

// Get recent past events
$recentPastEvents = $db->fetchAll(
    "SELECT * FROM schedule WHERE event_date < CURDATE() AND status != 'cancelled' ORDER BY event_date DESC LIMIT 5"
);

// Get stats
$totalUpcoming = $db->fetchOne("SELECT COUNT(*) as count FROM schedule WHERE event_date >= CURDATE() AND status = 'scheduled'")['count'];
$thisMonth = $db->fetchOne("SELECT COUNT(*) as count FROM schedule WHERE DATE_FORMAT(event_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') AND status = 'scheduled'")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/family-header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($user['first_name'] ?: $user['username']); ?>! 👋</h1>
                <p class="lead">Stay updated with the work schedule.</p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Events</h5>
                        <h2 class="mb-0"><?php echo $totalUpcoming; ?></h2>
                        <small>Scheduled ahead</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">This Month</h5>
                        <h2 class="mb-0"><?php echo $thisMonth; ?></h2>
                        <small>Events scheduled</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Schedule Events -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">📅 Upcoming Schedule</h5>
                        <a href="schedule.php" class="btn btn-sm btn-info">View Full Schedule</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingEvents)): ?>
                            <p class="text-muted">No upcoming events scheduled.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                <p class="mb-1 text-primary">
                                                    <strong>
                                                        📆 <?php echo date('l, F j, Y', strtotime($event['event_date'])); ?>
                                                        <?php if ($event['start_time']): ?>
                                                            at <?php echo date('g:i A', strtotime($event['start_time'])); ?>
                                                        <?php endif; ?>
                                                    </strong>
                                                </p>
                                                <?php if ($event['location']): ?>
                                                    <p class="mb-1"><small>📍 <?php echo htmlspecialchars($event['location']); ?></small></p>
                                                <?php endif; ?>
                                                <?php if ($event['description']): ?>
                                                    <p class="mb-0 text-muted"><small><?php echo htmlspecialchars($event['description']); ?></small></p>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-secondary ms-2"><?php echo ucfirst($event['event_type']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Past Events -->
        <?php if (!empty($recentPastEvents)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentPastEvents as $event): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                            <p class="mb-0 text-muted">
                                                <small><?php echo date('F j, Y', strtotime($event['event_date'])); ?></small>
                                            </p>
                                        </div>
                                        <?php if ($event['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Portfolio CMS. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
