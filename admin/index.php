<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

  require("../includes/class.auth.php");
  require("../includes/class.display.php");


requireLogin();
requireAdmin();

  $db = new Database();
  $auth = new Auth;
  $display = new Display;
  
  $display->load_settings();

// Get statistics
$projectCount = $db->fetchOne("SELECT COUNT(*) as count FROM projects")['count'];
$publishedCount = $db->fetchOne("SELECT COUNT(*) as count FROM projects WHERE status = 'published'")['count'];
$messageCount = $db->fetchOne("SELECT COUNT(*) as count FROM messages WHERE status = 'unread'")['count'];

// Get recent projects
$recentProjects = $db->fetchAll(
    "SELECT id, title, status, created_at FROM projects ORDER BY created_at DESC LIMIT 5"
);

// Get recent messages
$recentMessages = $db->fetchAll(
    "SELECT id, name, email, subject, status, created_at FROM messages ORDER BY created_at DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Projects</h5>
                                <h2 class="mb-0"><?php echo $projectCount; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Published Projects</h5>
                                <h2 class="mb-0"><?php echo $publishedCount; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Projects -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Projects</h5>
                        <a href="projects.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentProjects)): ?>
                            <p class="text-muted">No projects yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentProjects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $project['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($project['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($project['created_at']); ?></td>
                                                <td>
                                                    <a href="project-edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
<?php
  
  //
  $display->page_html .= "<div class='row'><div class='col col-md-8'>Main Column</div><div class='col col-md-4'>Dashboard Sidebar";
  
  // Messages card
  $recentUnread = $db->fetchAll("SELECT id, name, email, subject FROM messages WHERE status='unread' AND spam_status != 'spam' ORDER BY created_at DESC LIMIT 5");
  $unreadClean = $db->fetchOne("SELECT COUNT(*) as count FROM messages WHERE status = 'unread' AND spam_status != 'spam'")['count'];
  $unreadSpam = $db->fetchOne("SELECT COUNT(*) as count FROM messages WHERE status = 'unread' AND spam_status='spam'")['count'];
  $display->page_html .= "<div class='card'><div class='card-header text-center'>Messages</div><div class='card-body row'><div class='col text-center'>";
  $display->page_html .= "<span class='badge text-bg-warning'>${unreadClean} Unread</span></div><div class='col text-center'>";
  $display->page_html .= "<span class='badge text-bg-danger'>${unreadSpam} Unread Spam</span></div></div>";
  if(empty($recentUnread)) {
    // Maybe add a "No messages" message?
  } else {
    $display->page_html .= "<ul class='list-group list-group-flush'>";
    foreach($recentUnread as $message) {
      $display->page_html .= "<li class='list-group-item'><div class='row'><div class='col'><div>${message['name']} ";
      $display->page_html .= "<span class='badge text-bg-dark'>${message['email']}</span></div><div>${message['subject']}";
      $display->page_html .= "</div></div><div class='col col-md-2 text-end'><a href='message-view.php?id=${message['id']}'><span class='badge text-bg-primary'>View</span></a></div></div><div></div></li>";
    }
    $display->page_html .= "</ul>";
  }
  $display->page_html .= "<div class='card-footer text-end'><a href='messages.php' class='card-footer-link'>View All</a></div></div>";
  
  
  $display->page_html .= "</div></div>";
  //
  
  $display->page_html .= "</main></div>";
  $display->output(false);
?>
