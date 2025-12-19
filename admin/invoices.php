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
    $db->query("DELETE FROM invoices WHERE id = ?", [$id]);
    header('Location: invoices.php?msg=deleted');
    exit();
}

// Handle status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'mark_sent') {
        $db->query("UPDATE invoices SET status = 'sent', updated_at = NOW() WHERE id = ?", [$id]);
    } elseif ($action === 'mark_paid') {
        $db->query("UPDATE invoices SET status = 'paid', payment_date = CURDATE(), updated_at = NOW() WHERE id = ?", [$id]);
    }
    
    header('Location: invoices.php');
    exit();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT i.*, u.username as client_name, u.first_name, u.last_name, u.company, p.title as project_title
        FROM invoices i
        INNER JOIN users u ON i.client_id = u.id
        LEFT JOIN projects p ON i.project_id = p.id";

if ($filter === 'draft') {
    $sql .= " WHERE i.status = 'draft'";
} elseif ($filter === 'sent') {
    $sql .= " WHERE i.status = 'sent'";
} elseif ($filter === 'paid') {
    $sql .= " WHERE i.status = 'paid'";
} elseif ($filter === 'overdue') {
    $sql .= " WHERE i.status = 'sent' AND i.due_date < CURDATE()";
}

$sql .= " ORDER BY i.created_at DESC";
$invoices = $db->fetchAll($sql);

// Calculate totals
$totalUnpaid = 0;
$totalPaid = 0;
foreach ($invoices as $invoice) {
    if ($invoice['status'] === 'paid') {
        $totalPaid += $invoice['total'];
    } elseif ($invoice['status'] !== 'cancelled') {
        $totalUnpaid += $invoice['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">📄 Invoices</h1>
                    <a href="invoice-create.php" class="btn btn-primary">Create Invoice</a>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php if ($_GET['msg'] === 'deleted'): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            Invoice deleted successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <h5 class="card-title">Unpaid</h5>
                                <h2 class="mb-0">$<?php echo number_format($totalUnpaid, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Paid</h5>
                                <h2 class="mb-0">$<?php echo number_format($totalPaid, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Buttons -->
                <div class="mb-3">
                    <div class="btn-group" role="group">
                        <a href="?filter=all" class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                        <a href="?filter=draft" class="btn btn-outline-secondary <?php echo $filter === 'draft' ? 'active' : ''; ?>">Draft</a>
                        <a href="?filter=sent" class="btn btn-outline-info <?php echo $filter === 'sent' ? 'active' : ''; ?>">Sent</a>
                        <a href="?filter=paid" class="btn btn-outline-success <?php echo $filter === 'paid' ? 'active' : ''; ?>">Paid</a>
                        <a href="?filter=overdue" class="btn btn-outline-danger <?php echo $filter === 'overdue' ? 'active' : ''; ?>">Overdue</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($invoices)): ?>
                            <p class="text-muted">No invoices found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Client</th>
                                            <th>Project</th>
                                            <th>Date</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                                                <td>
                                                    <?php 
                                                    $clientName = trim($invoice['first_name'] . ' ' . $invoice['last_name']);
                                                    echo htmlspecialchars($clientName ?: $invoice['client_name']); 
                                                    ?>
                                                    <?php if ($invoice['company']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($invoice['company']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($invoice['project_title'] ?: '-'); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($invoice['invoice_date'])); ?></td>
                                                <td>
                                                    <?php echo date('M j, Y', strtotime($invoice['due_date'])); ?>
                                                    <?php if ($invoice['status'] === 'sent' && strtotime($invoice['due_date']) < time()): ?>
                                                        <br><span class="badge bg-danger">Overdue</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong>$<?php echo number_format($invoice['total'], 2); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $invoice['status'] === 'paid' ? 'success' : 
                                                            ($invoice['status'] === 'sent' ? 'info' : 
                                                            ($invoice['status'] === 'cancelled' ? 'danger' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($invoice['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="invoice-view.php?id=<?php echo $invoice['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                    
                                                    <?php if ($invoice['status'] === 'draft'): ?>
                                                        <a href="invoice-edit.php?id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-sm btn-outline-secondary">Edit</a>
                                                        <a href="?action=mark_sent&id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-sm btn-outline-info">Send</a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($invoice['status'] === 'sent'): ?>
                                                        <a href="?action=mark_paid&id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success">Mark Paid</a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="?delete=<?php echo $invoice['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Delete this invoice?')">Delete</a>
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
