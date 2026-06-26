<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $pdo = getConnection();
    
    // Create emergencies table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS emergencies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_name VARCHAR(100) NOT NULL,
        contact_number VARCHAR(15) NOT NULL,
        emergency_type VARCHAR(50) NOT NULL,
        priority_level ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
        status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Get filter parameter
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    
    // Build query based on filter
    switch($filter) {
        case 'high-priority':
            $query = "SELECT * FROM emergencies WHERE priority_level IN ('High', 'Critical') ORDER BY created_at DESC";
            break;
        case 'recent':
            $query = "SELECT * FROM emergencies WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC";
            break;
        default:
            $query = "SELECT * FROM emergencies ORDER BY created_at DESC";
    }
    
    $emergencies = $pdo->query($query)->fetchAll();
} catch(PDOException $e) {
    $emergencies = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Services - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; width: 100vw; overflow-x: hidden; }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .status-critical { background: linear-gradient(45deg, #dc3545, #c82333); color: white; }
        .status-high { background: linear-gradient(45deg, #fd7e14, #e55a00); color: white; }
        .status-medium { background: linear-gradient(45deg, #ffc107, #e0a800); color: white; }
        .status-low { background: linear-gradient(45deg, #28a745, #1e7e34); color: white; }
        .status-pending { background: linear-gradient(45deg, #6c757d, #545b62); color: white; }
        .status-completed { background: linear-gradient(45deg, #28a745, #1e7e34); color: white; }
        
        @media (max-width: 768px) {
            div[style*="padding: 2rem"] { padding: 1rem !important; }
            .table { font-size: 0.85rem; min-width: 700px !important; }
            .table th, .table td { padding: 0.5rem 0.25rem; white-space: nowrap; }
            .status-badge { font-size: 0.75rem; padding: 0.35rem 0.7rem; }
            h2 { font-size: 1.75rem; }
            div[style*="flex-wrap: wrap"] a { min-width: 150px !important; }
        }
        
        @media (max-width: 576px) {
            div[style*="padding: 1rem"] { padding: 0.5rem !important; }
            .table { font-size: 0.75rem; min-width: 600px !important; }
            .table th, .table td { padding: 0.375rem 0.25rem; }
            .status-badge { font-size: 0.7rem; padding: 0.25rem 0.5rem; }
            .btn { width: 100%; margin-bottom: 0.5rem; font-size: 0.9rem; }
            div[style*="flex-wrap: wrap"] { flex-direction: column; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div style="width: 100vw; min-height: 100vh; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div style="width: 100%; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border-radius: 15px; padding: 2rem;">
                        <h2><i class="fas fa-ambulance"></i> Emergency Services</h2>
                        <p class="text-center text-white mb-4">
                            See all active emergency cases and their status in real time.
                        </p>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap;">
                            <a href="emergency-services.php?filter=all" class="btn btn-primary" style="flex: 1; min-width: 200px; max-width: 300px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-list"></i> <span class="d-none d-sm-inline">View All Emergency Cases</span><span class="d-sm-none">All Cases</span>
                            </a>
                            <a href="emergency-services.php?filter=high-priority" class="btn btn-danger" style="flex: 1; min-width: 200px; max-width: 300px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-exclamation-triangle"></i> <span class="d-none d-sm-inline">View High-Priority Cases</span><span class="d-sm-none">High Priority</span>
                            </a>
                            <a href="emergency-services.php?filter=recent" class="btn btn-warning" style="flex: 1; min-width: 200px; max-width: 300px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-clock"></i> <span class="d-none d-sm-inline">View Recently Added Cases</span><span class="d-sm-none">Recent</span>
                            </a>
                        </div>

                        <?php if (count($emergencies) > 0): ?>
                        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                            <table class="table table-striped" style="width: 100%; min-width: 800px; background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Contact</th>
                                        <th>Emergency Type</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($emergencies as $emergency): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($emergency['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($emergency['contact_number']); ?></td>
                                        <td><?php echo htmlspecialchars($emergency['emergency_type']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($emergency['priority_level']); ?>">
                                                <?php echo $emergency['priority_level']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($emergency['status']); ?>">
                                                <?php echo $emergency['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($emergency['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-white">
                            <p>No emergency cases found for the selected filter.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
                            <a href="schedule-emergency.php" class="btn btn-danger" style="flex: 1; max-width: 250px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-plus"></i> Add Emergency Case
                            </a>
                            <a href="index.php" class="btn btn-secondary" style="flex: 1; max-width: 250px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>