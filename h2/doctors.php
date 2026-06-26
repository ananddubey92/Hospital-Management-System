<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Handle search
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $query = "SELECT d.*, dept.name as department_name FROM doctors d 
              LEFT JOIN departments dept ON d.department_id = dept.id 
              WHERE d.name LIKE ? OR d.specialization LIKE ? 
              ORDER BY d.name";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$search%", "%$search%"]);
    $doctors = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $doctors = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="auth-card glass">
                        <h2><i class="fas fa-user-md"></i> Doctors Management</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <a href="add-doctor.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Doctor
                                </a>
                            </div>
                            <div class="col-md-6">
                                <form method="GET" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Search doctors..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-outline-light">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <?php if (count($doctors) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Doctor ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Specialization</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($doctors as $doctor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($doctor['doc_id']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['department_name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $doctor['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($doctor['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-white">
                            <p>No doctors found.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="admin_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>