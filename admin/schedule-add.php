<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $event_date = sanitizeInput($_POST['event_date'] ?? '');
    $start_time = sanitizeInput($_POST['start_time'] ?? '');
    $end_time = sanitizeInput($_POST['end_time'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $event_type = sanitizeInput($_POST['event_type'] ?? 'event');
    $status = sanitizeInput($_POST['status'] ?? 'scheduled');
    $color = sanitizeInput($_POST['color'] ?? '#3788d8');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($event_date)) {
        $errors[] = 'Event date is required.';
    }
    
    if (empty($errors)) {
        $sql = "INSERT INTO schedule (title, description, event_date, start_time, end_time, location, event_type, status, is_private, color, notes, created_by, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        if ($db->query($sql, [$title, $description, $event_date, $start_time ?: null, $end_time ?: null, $location, $event_type, $status, $is_private, $color, $notes, $_SESSION['user_id']])) {
            header('Location: schedule.php');
            exit();
        } else {
            $errors[] = 'Failed to create event.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add Schedule Event</h1>
                    <a href="schedule.php" class="btn btn-secondary">Back to Schedule</a>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Event Title *</label>
                                        <input type="text" class="form-control" id="title" name="title" required 
                                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="event_date" class="form-label">Date *</label>
                                                <input type="date" class="form-control" id="event_date" name="event_date" required 
                                                       value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="start_time" class="form-label">Start Time</label>
                                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                                       value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="end_time" class="form-label">End Time</label>
                                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                                       value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="event_type" class="form-label">Event Type</label>
                                        <select class="form-select" id="event_type" name="event_type">
                                            <option value="work" <?php echo ($_POST['event_type'] ?? '') === 'work' ? 'selected' : ''; ?>>Work</option>
                                            <option value="meeting" <?php echo ($_POST['event_type'] ?? '') === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                                            <option value="deadline" <?php echo ($_POST['event_type'] ?? '') === 'deadline' ? 'selected' : ''; ?>>Deadline</option>
                                            <option value="shoot" <?php echo ($_POST['event_type'] ?? '') === 'shoot' ? 'selected' : ''; ?>>Photo/Video Shoot</option>
                                            <option value="event" <?php echo ($_POST['event_type'] ?? 'event') === 'event' ? 'selected' : ''; ?>>Event</option>
                                            <option value="reminder" <?php echo ($_POST['event_type'] ?? '') === 'reminder' ? 'selected' : ''; ?>>Reminder</option>
                                            <option value="other" <?php echo ($_POST['event_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="scheduled" <?php echo ($_POST['status'] ?? 'scheduled') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                            <option value="completed" <?php echo ($_POST['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo ($_POST['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="color" class="form-label">Color</label>
                                        <input type="color" class="form-control form-control-color" id="color" name="color" 
                                               value="<?php echo htmlspecialchars($_POST['color'] ?? '#3788d8'); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_private" name="is_private" value="1"
                                                   <?php echo ($_POST['is_private'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_private">
                                                <strong>Private Event</strong>
                                            </label>
                                        </div>
                                        <small class="text-muted">Private events are only visible to admins</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">Create Event</button>
                                <a href="schedule.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
