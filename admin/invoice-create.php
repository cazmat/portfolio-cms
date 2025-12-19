<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();
$db = new Database();

$errors = [];

// Get clients and projects for dropdowns
$clients = $db->fetchAll("SELECT id, username, first_name, last_name, company, email FROM users WHERE role IN ('client', 'family') ORDER BY username");
$projects = $db->fetchAll("SELECT id, title, client FROM projects ORDER BY title");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = (int)($_POST['client_id'] ?? 0);
    $project_id = !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null;
    $invoice_number = sanitizeInput($_POST['invoice_number'] ?? '');
    $invoice_date = sanitizeInput($_POST['invoice_date'] ?? '');
    $due_date = sanitizeInput($_POST['due_date'] ?? '');
    $tax_rate = floatval($_POST['tax_rate'] ?? 0);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $terms = sanitizeInput($_POST['terms'] ?? '');
    
    // Get line items
    $descriptions = $_POST['description'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $unit_prices = $_POST['unit_price'] ?? [];
    
    // Validation
    if (empty($invoice_number)) $errors[] = 'Invoice number is required.';
    if ($client_id === 0) $errors[] = 'Client is required.';
    if (empty($invoice_date)) $errors[] = 'Invoice date is required.';
    if (empty($due_date)) $errors[] = 'Due date is required.';
    if (empty($descriptions)) $errors[] = 'At least one line item is required.';
    
    if (empty($errors)) {
        // Calculate totals
        $subtotal = 0;
        foreach ($descriptions as $i => $desc) {
            if (!empty($desc)) {
                $qty = floatval($quantities[$i] ?? 0);
                $price = floatval($unit_prices[$i] ?? 0);
                $subtotal += $qty * $price;
            }
        }
        
        $tax_amount = $subtotal * ($tax_rate / 100);
        $total = $subtotal + $tax_amount;
        
        // Insert invoice
        $sql = "INSERT INTO invoices (invoice_number, client_id, project_id, invoice_date, due_date, status, subtotal, tax_rate, tax_amount, total, notes, terms, created_by, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'draft', ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        if ($db->query($sql, [$invoice_number, $client_id, $project_id, $invoice_date, $due_date, $subtotal, $tax_rate, $tax_amount, $total, $notes, $terms, $_SESSION['user_id']])) {
            $invoice_id = $db->lastInsertId();
            
            // Insert line items
            $order = 0;
            foreach ($descriptions as $i => $desc) {
                if (!empty($desc)) {
                    $qty = floatval($quantities[$i] ?? 0);
                    $price = floatval($unit_prices[$i] ?? 0);
                    $amount = $qty * $price;
                    
                    $db->query(
                        "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount, display_order) VALUES (?, ?, ?, ?, ?, ?)",
                        [$invoice_id, $desc, $qty, $price, $amount, $order++]
                    );
                }
            }
            
            header('Location: invoice-view.php?id=' . $invoice_id);
            exit();
        } else {
            $errors[] = 'Failed to create invoice.';
        }
    }
}

// Generate invoice number
$lastInvoice = $db->fetchOne("SELECT invoice_number FROM invoices ORDER BY id DESC LIMIT 1");
if ($lastInvoice) {
    $lastNum = intval(preg_replace('/[^0-9]/', '', $lastInvoice['invoice_number']));
    $newNum = $lastNum + 1;
} else {
    $newNum = 1001;
}
$suggestedNumber = 'INV-' . $newNum;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Create Invoice</h1>
                    <a href="invoices.php" class="btn btn-secondary">Cancel</a>
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
                
                <form method="POST" id="invoiceForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header"><h5 class="mb-0">Invoice Details</h5></div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Invoice Number *</label>
                                        <input type="text" class="form-control" name="invoice_number" required 
                                               value="<?php echo htmlspecialchars($_POST['invoice_number'] ?? $suggestedNumber); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Client *</label>
                                        <select class="form-select" name="client_id" required>
                                            <option value="">Select Client</option>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?php echo $client['id']; ?>" <?php echo (isset($_POST['client_id']) && $_POST['client_id'] == $client['id']) ? 'selected' : ''; ?>>
                                                    <?php 
                                                    $name = trim($client['first_name'] . ' ' . $client['last_name']);
                                                    echo htmlspecialchars($name ?: $client['username']);
                                                    if ($client['company']) echo ' - ' . htmlspecialchars($client['company']);
                                                    ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Project (Optional)</label>
                                        <select class="form-select" name="project_id">
                                            <option value="">No Project</option>
                                            <?php foreach ($projects as $project): ?>
                                                <option value="<?php echo $project['id']; ?>" <?php echo (isset($_POST['project_id']) && $_POST['project_id'] == $project['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($project['title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Invoice Date *</label>
                                            <input type="date" class="form-control" name="invoice_date" required 
                                                   value="<?php echo $_POST['invoice_date'] ?? date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Due Date *</label>
                                            <input type="date" class="form-control" name="due_date" required 
                                                   value="<?php echo $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tax Rate (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="tax_rate" 
                                               value="<?php echo $_POST['tax_rate'] ?? '0.00'; ?>" onchange="calculateTotal()">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header"><h5 class="mb-0">Additional Information</h5></div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="4"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Terms</label>
                                        <textarea class="form-control" name="terms" rows="4"><?php echo htmlspecialchars($_POST['terms'] ?? 'Payment due within 30 days. Thank you for your business!'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Line Items</h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addLineItem()">+ Add Item</button>
                        </div>
                        <div class="card-body">
                            <div id="lineItems">
                                <div class="row mb-2 line-item">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="description[]" placeholder="Description" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="0.01" class="form-control" name="quantity[]" placeholder="Qty" value="1" required onchange="calculateTotal()">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" step="0.01" class="form-control" name="unit_price[]" placeholder="Price" required onchange="calculateTotal()">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" readonly placeholder="Amount">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeLineItem(this)" title="Remove">×</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-8"></div>
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Subtotal:</strong></td>
                                            <td class="text-end" id="subtotal">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tax:</strong></td>
                                            <td class="text-end" id="tax">$0.00</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total:</strong></td>
                                            <td class="text-end"><strong id="total">$0.00</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg">Create Invoice</button>
                    <a href="invoices.php" class="btn btn-secondary btn-lg">Cancel</a>
                </form>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function addLineItem() {
        const template = document.querySelector('.line-item').cloneNode(true);
        template.querySelectorAll('input').forEach(input => input.value = input.type === 'number' ? (input.name === 'quantity[]' ? '1' : '') : '');
        document.getElementById('lineItems').appendChild(template);
        calculateTotal();
    }
    
    function removeLineItem(btn) {
        if (document.querySelectorAll('.line-item').length > 1) {
            btn.closest('.line-item').remove();
            calculateTotal();
        }
    }
    
    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.line-item').forEach(item => {
            const qty = parseFloat(item.querySelector('input[name="quantity[]"]').value) || 0;
            const price = parseFloat(item.querySelector('input[name="unit_price[]"]').value) || 0;
            const amount = qty * price;
            item.querySelector('input[readonly]').value = '$' + amount.toFixed(2);
            subtotal += amount;
        });
        
        const taxRate = parseFloat(document.querySelector('input[name="tax_rate"]').value) || 0;
        const tax = subtotal * (taxRate / 100);
        const total = subtotal + tax;
        
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('tax').textContent = '$' + tax.toFixed(2);
        document.getElementById('total').textContent = '$' + total.toFixed(2);
    }
    
    // Calculate on load
    calculateTotal();
    </script>
</body>
</html>
