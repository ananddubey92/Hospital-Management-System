<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    
    // Create or update rooms table with proper structure
    $sql = "CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_number VARCHAR(10) UNIQUE NOT NULL,
        room_type ENUM('general', 'private', 'icu', 'emergency') NOT NULL,
        bed_count INT DEFAULT 1,
        price_per_day DECIMAL(10,2) NOT NULL,
        status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
        amenities TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Check if table is empty and insert sample data
    $count = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    
    if ($count == 0) {
        $sample_rooms = [
            ['G101', 'general', 4, 500.00, 'available', 'Basic bed, shared bathroom, fan'],
            ['G102', 'general', 4, 500.00, 'available', 'Basic bed, shared bathroom, fan'],
            ['G103', 'general', 4, 500.00, 'occupied', 'Basic bed, shared bathroom, fan'],
            ['P201', 'private', 1, 1500.00, 'available', 'Private bed, attached bathroom, AC, TV'],
            ['P202', 'private', 1, 1500.00, 'available', 'Private bed, attached bathroom, AC, TV'],
            ['P203', 'private', 1, 1500.00, 'occupied', 'Private bed, attached bathroom, AC, TV'],
            ['I301', 'icu', 1, 3000.00, 'available', 'ICU bed, ventilator, monitoring equipment'],
            ['I302', 'icu', 1, 3000.00, 'occupied', 'ICU bed, ventilator, monitoring equipment'],
            ['E401', 'emergency', 2, 2000.00, 'available', 'Emergency bed, basic monitoring, oxygen'],
            ['E402', 'emergency', 2, 2000.00, 'maintenance', 'Emergency bed, basic monitoring, oxygen']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, bed_count, price_per_day, status, amenities) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($sample_rooms as $room) {
            $stmt->execute($room);
        }
        
        echo "Rooms table created and sample data inserted successfully!";
    } else {
        echo "Rooms table already exists with $count rooms.";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Setup Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="auth-card glass text-center">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h2>Room Setup Complete!</h2>
                        <p class="text-white">The room management system has been set up successfully.</p>
                        <div class="d-grid gap-2">
                            <a href="rooms.php" class="btn btn-primary">
                                <i class="fas fa-bed"></i> View Rooms
                            </a>
                            <a href="add-room.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add New Room
                            </a>
                            <a href="admin_dashboard.php" class="btn btn-outline-light">
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