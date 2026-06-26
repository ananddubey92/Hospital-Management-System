<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("INSERT INTO departments (dept_id, name, description, head_of_department, status) VALUES (?, ?, ?, ?, 'active')");
        
        if ($stmt->execute([$_POST['dept_id'], $_POST['name'], $_POST['description'], $_POST['head_of_department']])) {
            header('Location: departments.php?success=1');
            exit();
        } else {
            $error = "Failed to add department.";
        }
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department</title>
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
                        <h2><i class="fas fa-plus-circle"></i> Add New Department</h2>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-white">Department ID</label>
                                <input type="text" class="form-control" name="dept_id" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Department Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Head of Department</label>
                                <input type="text" class="form-control" name="head_of_department" required>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Add Department
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