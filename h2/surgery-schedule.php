<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConnection();

// Get all surgeries with filters
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

$query = "SELECT * FROM surgeries WHERE 1=1";
$params = [];

if ($status_filter) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $query .= " AND surgery_date = ?";
    $params[] = $date_filter;
}

$query .= " ORDER BY surgery_date ASC, surgery_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$surgeries = $stmt->fetchAll();

// Get statistics
$total_surgeries = $pdo->query("SELECT COUNT(*) FROM surgeries")->fetchColumn();
$scheduled = $pdo->query("SELECT COUNT(*) FROM surgeries WHERE status = 'Scheduled'")->fetchColumn();
$in_progress = $pdo->query("SELECT COUNT(*) FROM surgeries WHERE status = 'In Progress'")->fetchColumn();
$completed = $pdo->query("SELECT COUNT(*) FROM surgeries WHERE status = 'Completed'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surgery Schedule - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; width: 100vw; overflow-x: hidden;">
    <div style="width: 100vw; min-height: 100vh; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div style="width: 100%; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border-radius: 15px; padding: 2rem;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="color: white; margin: 0;"><i class="fas fa-procedures"></i> Surgery Schedule</h2>
                    <p style="color: rgba(255,255,255,0.8); margin: 0;">View and manage all scheduled surgeries</p>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="surgery-management.php" class="btn btn-success" style="border-radius: 25px;">
                        <i class="fas fa-plus"></i> Schedule Surgery
                    </a>
                    <a href="admin_dashboard.php" class="btn btn-outline-light" style="border-radius: 25px;">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center p-3">
                        <h3><?php echo $total_surgeries; ?></h3>
                        <p class="mb-0 small">Total Surgeries</p>
                    </div>
                </div>
                <div class="card bg-info text-white">
                    <div class="card-body text-center p-3">
                        <h3><?php echo $scheduled; ?></h3>
                        <p class="mb-0 small">Scheduled</p>
                    </div>
                </div>
                <div class="card bg-warning text-white">
                    <div class="card-body text-center p-3">
                        <h3><?php echo $in_progress; ?></h3>
                        <p class="mb-0 small">In Progress</p>
                    </div>
                </div>
                <div class="card bg-success text-white">
                    <div class="card-body text-center p-3">
                        <h3><?php echo $completed; ?></h3>
                        <p class="mb-0 small">Completed</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4" style="width: 100%;">
                <div class="card-body">
                    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div>
                            <label class="form-label">Filter by Status</label>
                            <select class="form-control" name="status" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #00bcd4; color: white; border-radius: 12px; padding: 12px 15px; box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);">
                                <option value="" style="background: #2c3e50; color: #00bcd4; font-weight: bold;">All Status</option>
                                <option value="Scheduled" <?php echo $status_filter == 'Scheduled' ? 'selected' : ''; ?> style="background: #3498db; color: white;">Scheduled</option>
                                <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?> style="background: #f39c12; color: white;">In Progress</option>
                                <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?> style="background: #27ae60; color: white;">Completed</option>
                                <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?> style="background: #e74c3c; color: white;">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Filter by Date</label>
                            <input type="date" class="form-control" name="date" value="<?php echo $date_filter; ?>" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #9c27b0; color: white; border-radius: 12px; padding: 12px 15px; box-shadow: 0 4px 15px rgba(156, 39, 176, 0.3);">
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: end;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="surgery-schedule.php" class="btn btn-secondary" style="flex: 1;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Surgery Schedule Table -->
            <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 15px;">
                <table class="table table-striped" style="width: 100%; min-width: 1000px; background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; margin-bottom: 0;">
                    <thead>
                        <tr style="background: linear-gradient(45deg, #00bcd4, #2196f3); color: white;">
                            <th>Surgery ID</th>
                            <th>Patient</th>
                            <th>Surgery Type</th>
                            <th>Surgeon</th>
                            <th>Date & Time</th>
                            <th>Operation Theater</th>
                            <th>Duration</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($surgeries)): ?>
                            <tr>
                                <td colspan="10" class="text-center p-4">
                                    <i class="fas fa-procedures fa-3x mb-3 text-muted"></i>
                                    <p class="text-muted">No surgeries found. <a href="surgery-management.php" class="text-primary">Schedule the first surgery</a></p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($surgeries as $surgery): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($surgery['surgery_id']); ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($surgery['patient_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($surgery['patient_phone']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($surgery['surgery_type']); ?></td>
                                    <td><?php echo htmlspecialchars($surgery['surgeon_name']); ?></td>
                                    <td>
                                        <strong><?php echo date('M d, Y', strtotime($surgery['surgery_date'])); ?></strong><br>
                                        <small><?php echo date('H:i A', strtotime($surgery['surgery_time'])); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($surgery['operation_theater']); ?></td>
                                    <td><?php echo htmlspecialchars($surgery['estimated_duration']); ?></td>
                                    <td><strong>₹<?php echo number_format($surgery['cost'], 2); ?></strong></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($surgery['status']) {
                                            case 'Scheduled': $status_class = 'bg-primary'; break;
                                            case 'In Progress': $status_class = 'bg-warning'; break;
                                            case 'Completed': $status_class = 'bg-success'; break;
                                            case 'Cancelled': $status_class = 'bg-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($surgery['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info btn-sm" onclick="viewSurgery(<?php echo $surgery['id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-primary btn-sm" onclick="updateStatus(<?php echo $surgery['id']; ?>)" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewSurgery(id) {
            alert('Surgery details view - ID: ' + id);
        }

        function updateStatus(id) {
            const newStatus = prompt('Enter new status (Scheduled/In Progress/Completed/Cancelled):');
            if (newStatus) {
                // Here you would typically make an AJAX call to update the status
                alert('Status update functionality would be implemented here for surgery ID: ' + id);
            }
        }
    </script>

    <style>
        @media (max-width: 768px) {
            div[style*="padding: 2rem"] { padding: 1rem !important; }
            .table { font-size: 0.85rem; min-width: 800px !important; }
            .table th, .table td { padding: 0.5rem 0.25rem; white-space: nowrap; }
            .btn-group { flex-direction: column; width: 100%; }
            .btn-group .btn { margin-bottom: 0.25rem; border-radius: 6px !important; }
        }
        
        @media (max-width: 576px) {
            div[style*="padding: 1rem"] { padding: 0.5rem !important; }
            .table { font-size: 0.75rem; min-width: 700px !important; }
            .card-body h3 { font-size: 1.5rem; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</body>
</html>