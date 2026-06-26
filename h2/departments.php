<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Create departments table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dept_id VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        head_of_department VARCHAR(100),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add status column if it doesn't exist (for existing tables)
    try {
        $pdo->exec("ALTER TABLE departments ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    } catch(PDOException $e) {
        // Column already exists, ignore error
    }
    
    // Handle search
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $query = "SELECT * FROM departments WHERE name LIKE ? OR head_of_department LIKE ? ORDER BY name";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%"]);
    $departments = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $departments = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; width: 100vw; overflow-x: hidden;">
    <div style="width: 100vw; min-height: 100vh; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div style="width: 100%; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border-radius: 15px; padding: 2rem;">
                        <h2><i class="fas fa-building"></i> Department Management</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Department added successfully!</div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; align-items: center;">
                            <a href="add-department.php" class="btn btn-primary" style="flex: 1; min-width: 200px; max-width: 300px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-plus"></i> Add New Department
                            </a>
                            <form method="GET" style="display: flex; gap: 0.5rem; flex: 2; min-width: 250px;">
                                <input type="text" class="form-control" name="search" placeholder="Search departments..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; border-radius: 25px; padding: 12px 20px;">
                                <button type="submit" class="btn btn-outline-light" style="border-radius: 25px; padding: 12px 20px; min-width: 60px;">
                                    <i class="fas fa-search"></i>
                                </button>
                                <?php if ($search): ?>
                                    <a href="departments.php" class="btn btn-secondary" style="border-radius: 25px; padding: 12px 20px;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <?php if (count($departments) > 0): ?>
                        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                            <table class="table table-striped" style="width: 100%; min-width: 800px; background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; margin-bottom: 0;">
                                <thead style="background: linear-gradient(45deg, #00bcd4, #2196f3); color: white;">
                                    <tr>
                                        <th style="padding: 1rem; border: none;">Dept ID</th>
                                        <th style="padding: 1rem; border: none;">Department Name</th>
                                        <th style="padding: 1rem; border: none;">Head of Department</th>
                                        <th style="padding: 1rem; border: none;">Status</th>
                                        <th style="padding: 1rem; border: none;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td style="padding: 1rem; vertical-align: middle;"><strong><?php echo htmlspecialchars($dept['dept_id']); ?></strong></td>
                                        <td style="padding: 1rem; vertical-align: middle;"><?php echo htmlspecialchars($dept['name']); ?></td>
                                        <td style="padding: 1rem; vertical-align: middle;"><?php echo htmlspecialchars($dept['head_of_department'] ?? 'Not Assigned'); ?></td>
                                        <td style="padding: 1rem; vertical-align: middle;">
                                            <span class="badge bg-<?php echo ($dept['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>" style="padding: 0.5rem 1rem; border-radius: 20px;">
                                                <?php echo ucfirst($dept['status'] ?? 'active'); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; vertical-align: middle;">
                                            <div class="btn-group btn-group-sm" style="display: flex; gap: 0.25rem;">
                                                <a href="edit-department.php?id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-warning" title="Edit Department" style="border-radius: 6px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete-department.php?id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete Department" style="border-radius: 6px;">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-white">
                            <p>No departments found.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; justify-content: center; margin-top: 2rem;">
                            <a href="admin_dashboard.php" class="btn btn-secondary" style="border-radius: 25px; padding: 12px 20px; min-width: 200px;">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
        </div>
    </div>

    <style>
        body { margin: 0; padding: 0; width: 100vw; overflow-x: hidden; }
        
        @media (max-width: 768px) {
            div[style*="padding: 2rem"] { padding: 1rem !important; }
            div[style*="flex-wrap: wrap"] { flex-direction: column; gap: 0.5rem !important; }
            .table { font-size: 0.85rem; min-width: 700px !important; }
            .table th, .table td { padding: 0.5rem !important; white-space: nowrap; }
            h2 { font-size: 1.75rem; }
            .btn { width: 100%; margin-bottom: 0.5rem; }
            form[style*="flex: 2"] { flex: 1 !important; }
        }
        
        @media (max-width: 576px) {
            div[style*="padding: 1rem"] { padding: 0.75rem !important; }
            .table { font-size: 0.75rem; min-width: 600px !important; }
            .btn-group { flex-direction: column; width: 100%; }
            .btn-group .btn { margin-bottom: 0.25rem; border-radius: 6px !important; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</body>
</html>