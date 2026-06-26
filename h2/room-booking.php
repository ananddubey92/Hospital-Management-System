<?php
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $room_number = trim($_POST['room_number']);
        $room_type = $_POST['room_type'];
        $price_per_day = (float)$_POST['price_per_day'];
        $status = $_POST['status'];
        $availability = $_POST['availability'];
        
        if (empty($room_number) || empty($room_type) || empty($status) || $price_per_day <= 0) {
            throw new Exception('Please fill all required fields with valid values.');
        }
        
        // Check if room exists
        $check = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
        $check->execute([$room_number]);
        if ($check->fetch()) {
            throw new Exception('Room number already exists.');
        }
        
        // Insert room
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, price_per_day, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$room_number, $room_type, $price_per_day, $status]);
        
        $success = 'Room booked successfully!';
        $_POST = array();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-bed"></i> Room Booking</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Room Number *</label>
                                <input type="text" class="form-control" name="room_number" 
                                       value="<?php echo $_POST['room_number'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Room Type *</label>
                                <select class="form-control" name="room_type" required>
                                    <option value="">Select Type</option>
                                    <option value="general" <?php echo ($_POST['room_type'] ?? '') == 'general' ? 'selected' : ''; ?>>General</option>
                                    <option value="private" <?php echo ($_POST['room_type'] ?? '') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    <option value="icu" <?php echo ($_POST['room_type'] ?? '') == 'icu' ? 'selected' : ''; ?>>ICU</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price per Day (₹) *</label>
                                <input type="number" class="form-control" name="price_per_day" 
                                       value="<?php echo $_POST['price_per_day'] ?? ''; ?>" 
                                       step="0.01" min="0" placeholder="e.g., 1500.00" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-control" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="available" <?php echo ($_POST['status'] ?? '') == 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="occupied" <?php echo ($_POST['status'] ?? '') == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                    <option value="maintenance" <?php echo ($_POST['status'] ?? '') == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Availability</label>
                                <select class="form-control" name="availability">
                                    <option value="immediate" <?php echo ($_POST['availability'] ?? '') == 'immediate' ? 'selected' : ''; ?>>Immediate</option>
                                    <option value="scheduled" <?php echo ($_POST['availability'] ?? '') == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Book Room
                                </button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>