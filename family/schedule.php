<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();

// Only Admin and Family can view schedule
if (!isAdmin() && !isFamily()) {
    header('Location: dashboard.php');
    exit();
}

$db = new Database();

// Get filter
$filter = $_GET['filter'] ?? 'upcoming';
$month = $_GET['month'] ?? date('Y-m');

$sql = "SELECT * FROM schedule";

if ($filter === 'upcoming') {
    $sql .= " WHERE event_date >= CURDATE() AND status = 'scheduled'";
} elseif ($filter === 'past') {
    $sql .= " WHERE event_date < CURDATE()";
} elseif ($filter === 'month') {
    $sql .= " WHERE DATE_FORMAT(event_date, '%Y-%m') = ?";
}

// Hide private events from family members
if (isFamily()) {
    $sql .= ($filter ? " AND" : " WHERE") . " is_private = 0";
}

$sql .= " ORDER BY event_date ASC, start_time ASC";

if ($filter === 'month') {
    $events = $db->fetchAll($sql, [$month]);
} else {
    $events = $db->fetchAll($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Schedule - Family Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .event-card { border-left: 4px solid #3788d8; }
        .event-work { border-left-color: #6f42c1; }
        .event-meeting { border-left-color: #3788d8; }
        .event-deadline { border-left-color: #dc3545; }
        .event-shoot { border-left-color: #28a745; }
        .event-event { border-left-color: #ffc107; }
        .event-reminder { border-left-color: #17a2b8; }
        .event-other { border-left-color: #6c757d; }
    </style>
</head>
<body>
    <?php include 'includes/family-header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Work Schedule</h1>
            </div>
        </div>
        
        <!-- Filter Buttons -->
        <div class="mb-4">
            <div class="btn-group me-2" role="group">
                <a href="?filter=upcoming" class="btn btn-outline-primary <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                <a href="?filter=past" class="btn btn-outline-secondary <?php echo $filter === 'past' ? 'active' : ''; ?>">Past Events</a>
            </div>
            
            <form method="GET" class="d-inline">
                <input type="hidden" name="filter" value="month">
                <input type="month" name="month" class="form-control d-inline-block" style="width: auto;" 
                       value="<?php echo $month; ?>" onchange="this.form.submit()">
            </form>
        </div>
        
        <div class="row">
            <?php if (empty($events)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No events scheduled for the selected period.</div>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card event-card event-<?php echo $event['event_type']; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
                                    <?php if ($event['status'] === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php elseif ($event['status'] === 'cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-primary mb-2">
                                    <strong><?php echo date('l, F j, Y', strtotime($event['event_date'])); ?></strong>
                                </p>
                                
                                <?php if ($event['start_time']): ?>
                                    <p class="mb-2">
                                        <small>
                                            🕐 <?php echo date('g:i A', strtotime($event['start_time'])); ?>
                                            <?php if ($event['end_time']): ?>
                                                - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($event['location']): ?>
                                    <p class="mb-2"><small>📍 <?php echo htmlspecialchars($event['location']); ?></small></p>
                                <?php endif; ?>
                                
                                <span class="badge bg-secondary mb-2"><?php echo ucfirst(str_replace('_', ' ', $event['event_type'])); ?></span>
                                
                                <?php if ($event['description']): ?>
                                    <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if ($event['notes'] && isAdmin()): ?>
                                    <div class="alert alert-light mt-2 p-2">
                                        <small><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($event['notes'])); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Portfolio CMS. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
