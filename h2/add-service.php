<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("INSERT INTO services (service_name, description, price, duration, category, status) VALUES (?, ?, ?, ?, ?, 'active')");
        
        if ($stmt->execute([$_POST['service_name'], $_POST['description'], $_POST['price'], $_POST['duration'], $_POST['category']])) {
            header('Location: admin_dashboard.php?success=service_added');
            exit();
        } else {
            $error = "Failed to add service.";
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
    <title>Add Service</title>
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
                        <h2><i class="fas fa-plus-circle"></i> Add New Service</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label text-white">Service Name</label>
                                <input type="text" class="form-control" name="service_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Price</label>
                                <input type="number" step="0.01" class="form-control" name="price" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Duration</label>
                                <input type="text" class="form-control" name="duration" placeholder="e.g., 30 minutes">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-white">Category</label>
                                <select class="form-control" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="lab">Lab</option>
                                    <option value="scan">Scan</option>
                                    <option value="procedure">Procedure</option>
                                    <option value="therapy">Therapy</option>
                                </select>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Add Service
                                </button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">
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