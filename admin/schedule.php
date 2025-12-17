<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM schedule WHERE id = ?", [$id]);
    header('Location: schedule.php?msg=deleted');
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'upcoming';
$month = $_GET['month'] ?? date('Y-m');

$sql = "SELECT s.*, u.username as creator_name 
        FROM schedule s 
        LEFT JOIN users u ON s.created_by = u.id";

if ($filter === 'upcoming') {
    $sql .= " WHERE s.event_date >= CURDATE() AND s.status = 'scheduled'";
} elseif ($filter === 'past') {
    $sql .= " WHERE s.event_date < CURDATE()";
} elseif ($filter === 'completed') {
    $sql .= " WHERE s.status = 'completed'";
} elseif ($filter === 'cancelled') {
    $sql .= " WHERE s.status = 'cancelled'";
} elseif ($filter === 'month') {
    $sql .= " WHERE DATE_FORMAT(s.event_date, '%Y-%m') = ?";
}

$sql .= " ORDER BY s.event_date ASC, s.start_time ASC";

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
    <title>Work Schedule - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Work Schedule</h1>
                    <a href="schedule-add.php" class="btn btn-primary">Add Event</a>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php if ($_GET['msg'] === 'deleted'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            Event deleted successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Filter Buttons -->
                <div class="mb-3">
                    <div class="btn-group me-2" role="group">
                        <a href="?filter=upcoming" class="btn btn-outline-primary <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                        <a href="?filter=past" class="btn btn-outline-secondary <?php echo $filter === 'past' ? 'active' : ''; ?>">Past</a>
                        <a href="?filter=completed" class="btn btn-outline-success <?php echo $filter === 'completed' ? 'active' : ''; ?>">Completed</a>
                        <a href="?filter=cancelled" class="btn btn-outline-danger <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
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
                            <div class="alert alert-info">No events found for the selected filter.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card event-card event-<?php echo $event['event_type']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                                <?php if ($event['is_private']): ?>
                                                    <span class="badge bg-dark">🔒 Private</span>
                                                <?php endif; ?>
                                            </h5>
                                            <span class="badge bg-<?php echo $event['status'] === 'completed' ? 'success' : ($event['status'] === 'cancelled' ? 'danger' : 'primary'); ?>">
                                                <?php echo ucfirst($event['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="text-muted small mb-2">
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
                                        
                                        <span class="badge bg-secondary"><?php echo ucfirst($event['event_type']); ?></span>
                                        
                                        <?php if ($event['description']): ?>
                                            <p class="card-text mt-2 small"><?php echo htmlspecialchars(substr($event['description'], 0, 100)); ?><?php echo strlen($event['description']) > 100 ? '...' : ''; ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3">
                                            <a href="schedule-edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
