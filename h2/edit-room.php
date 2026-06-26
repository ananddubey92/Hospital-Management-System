<?php
require_once 'config/database.php';

$message = '';
$error = '';
$room = null;

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($room_id <= 0) {
    header('Location: rooms.php');
    exit();
}

try {
    $pdo = getConnection();
    
    // Fetch room details
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        header('Location: rooms.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}

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
            // Check if room number already exists (excluding current room)
            $check = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
            $check->execute([$room_number, $room_id]);
            
            if ($check->fetch()) {
                $error = 'Room number already exists. Please use a different room number.';
            } else {
                $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, room_type = ?, bed_count = ?, price_per_day = ?, amenities = ?, status = ? WHERE id = ?");
                $stmt->execute([$room_number, $room_type, $bed_count, $price_per_day, $amenities, $status, $room_id]);
                
                $message = 'Room updated successfully!';
                
                // Refresh room data
                $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
                $stmt->execute([$room_id]);
                $room = $stmt->fetch();
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
    <title>Edit Room - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-12">
                    <div class="auth-card glass w-100">
                        <div class="text-center mb-4">
                            <h2><i class="fas fa-edit"></i> Edit Room</h2>
                            <p class="text-white">Update room details</p>
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

                        <?php if ($room): ?>
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="room_number" class="form-label text-white">Room Number *</label>
                                    <input type="text" class="form-control w-100" id="room_number" name="room_number" 
                                           value="<?php echo htmlspecialchars($room['room_number']); ?>" 
                                           placeholder="e.g., G101, P201" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="room_type" class="form-label text-white">Room Type *</label>
                                    <select class="form-control w-100" id="room_type" name="room_type" required>
                                        <option value="">Select Room Type</option>
                                        <option value="general" <?php echo $room['room_type'] == 'general' ? 'selected' : ''; ?>>General</option>
                                        <option value="private" <?php echo $room['room_type'] == 'private' ? 'selected' : ''; ?>>Private</option>
                                        <option value="icu" <?php echo $room['room_type'] == 'icu' ? 'selected' : ''; ?>>ICU</option>
                                        <option value="emergency" <?php echo $room['room_type'] == 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="bed_count" class="form-label text-white">Number of Beds *</label>
                                    <input type="number" class="form-control w-100" id="bed_count" name="bed_count" 
                                           value="<?php echo $room['bed_count']; ?>" 
                                           min="1" max="10" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="price_per_day" class="form-label text-white">Price per Day (₹) *</label>
                                    <input type="number" class="form-control w-100" id="price_per_day" name="price_per_day" 
                                           value="<?php echo $room['price_per_day']; ?>" 
                                           step="0.01" min="0" placeholder="e.g., 1500.00" required>
                                </div>

                                <div class="col-12">
                                    <label for="status" class="form-label text-white">Room Status *</label>
                                    <select class="form-control w-100" id="status" name="status" required>
                                        <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="occupied" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                        <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="amenities" class="form-label text-white">Amenities</label>
                                    <textarea class="form-control w-100" id="amenities" name="amenities" rows="3" 
                                              placeholder="e.g., AC, TV, Private bathroom, WiFi"><?php echo htmlspecialchars($room['amenities']); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="fas fa-save"></i> Update Room
                                        </button>
                                        <a href="rooms.php" class="btn btn-secondary flex-fill">
                                            <i class="fas fa-list"></i> Back to Rooms
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>