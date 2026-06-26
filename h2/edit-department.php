<?php
require_once __DIR__ . '/config/database.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

try {
    $pdo = getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ?, head_of_department = ?, status = ? WHERE id = ?");
        
        if ($stmt->execute([$_POST['name'], $_POST['description'], $_POST['head_of_department'], $_POST['status'], $id])) {
            $success = "Department updated successfully!";
        } else {
            $error = "Failed to update department.";
        }
    }
    
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$id]);
    $department = $stmt->fetch();
    
    if (!$department) {
        header('Location: departments.php');
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="auth-card glass">
                        <h2><i class="fas fa-edit"></i> Edit Department</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-white">Department ID</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($department['dept_id']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Department Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($department['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Description</label>
                                <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($department['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Head of Department</label>
                                <input type="text" class="form-control" name="head_of_department" value="<?php echo htmlspecialchars($department['head_of_department']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="active" <?php echo $department['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $department['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Update Department
                                </button>
                                <a href="departments.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>