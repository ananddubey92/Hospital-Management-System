<?php
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_number = trim($_POST['room_number']);
    $room_type = $_POST['room_type'];
    $bed_count = (int)$_POST['bed_count'];
    $price_per_day = (float)$_POST['price_per_day'];
    $amenities = trim($_POST['amenities']);
    $status = $_POST['status'];
    
    if (empty($room_number) || empty($room_type) || $bed_count <= 0 || $price_per_day <= 0) {
        $error = 'Please fill all required fields with valid values.';
    } else {
        try {
            $pdo = getConnection();
            
            // Check if room number already exists
            $check = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $check->execute([$room_number]);
            
            if ($check->fetch()) {
                $error = 'Room number already exists. Please use a different room number.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, bed_count, price_per_day, amenities, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$room_number, $room_type, $bed_count, $price_per_day, $amenities, $status]);
                
                $message = 'Room added successfully!';
                // Clear form data
                $_POST = array();
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room - Hospital Management</title>
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
                        <div class="text-center mb-4">
                            <h2><i class="fas fa-bed"></i> Add New Room</h2>
                            <p class="text-white">Enter room details to add to the system</p>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="room_number" class="form-label text-white">Room Number *</label>
                                    <input type="text" class="form-control" id="room_number" name="room_number" 
                                           value="<?php echo isset($_POST['room_number']) ? htmlspecialchars($_POST['room_number']) : ''; ?>" 
                                           placeholder="e.g., G101, P201" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="room_type" class="form-label text-white">Room Type *</label>
                                    <select class="form-control" id="room_type" name="room_type" required>
                                        <option value="">Select Room Type</option>
                                        <option value="general" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == 'general') ? 'selected' : ''; ?>>General</option>
                                        <option value="private" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == 'private') ? 'selected' : ''; ?>>Private</option>
                                        <option value="icu" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == 'icu') ? 'selected' : ''; ?>>ICU</option>
                                        <option value="emergency" <?php echo (isset($_POST['room_type']) && $_POST['room_type'] == 'emergency') ? 'selected' : ''; ?>>Emergency</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bed_count" class="form-label text-white">Number of Beds *</label>
                                    <input type="number" class="form-control" id="bed_count" name="bed_count" 
                                           value="<?php echo isset($_POST['bed_count']) ? $_POST['bed_count'] : '1'; ?>" 
                                           min="1" max="10" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price_per_day" class="form-label text-white">Price per Day (₹) *</label>
                                    <input type="number" class="form-control" id="price_per_day" name="price_per_day" 
                                           value="<?php echo isset($_POST['price_per_day']) ? $_POST['price_per_day'] : ''; ?>" 
                                           step="0.01" min="0" placeholder="e.g., 1500.00" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label text-white">Room Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="occupied" <?php echo (isset($_POST['status']) && $_POST['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                                    <option value="maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'maintenance') ? 'selected' : ''; ?>>Under Maintenance</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="amenities" class="form-label text-white">Amenities</label>
                                <textarea class="form-control" id="amenities" name="amenities" rows="3" 
                                          placeholder="e.g., AC, TV, Private bathroom, WiFi"><?php echo isset($_POST['amenities']) ? htmlspecialchars($_POST['amenities']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Room
                                </button>
                                <a href="rooms.php" class="btn btn-secondary">
                                    <i class="fas fa-list"></i> View All Rooms
                                </a>
                                <a href="admin_dashboard.php" class="btn btn-outline-light">
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