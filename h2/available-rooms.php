<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    
    // Get only available rooms
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available' ORDER BY room_type, room_number");
    $stmt->execute();
    $available_rooms = $stmt->fetchAll();
    
    // Get room statistics
    $stats = [
        'general' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_type = 'general' AND status = 'available'")->fetchColumn(),
        'private' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_type = 'private' AND status = 'available'")->fetchColumn(),
        'icu' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_type = 'icu' AND status = 'available'")->fetchColumn(),
        'emergency' => $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_type = 'emergency' AND status = 'available'")->fetchColumn()
    ];
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark header fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt"></i> MediCare Plus
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="available-rooms.php">Available Rooms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="departments.php">Departments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div style="margin-top: 100px;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="text-center mb-5">
                        <h1 class="text-white"><i class="fas fa-bed"></i> Available Rooms</h1>
                        <p class="text-white">Find the perfect room for your stay</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Room Type Statistics -->
                    <div class="row mb-5">
                        <div class="col-md-3">
                            <div class="stat-card green">
                                <i class="fas fa-bed"></i>
                                <h3><?php echo $stats['general']; ?></h3>
                                <p>General Rooms</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card blue">
                                <i class="fas fa-door-closed"></i>
                                <h3><?php echo $stats['private']; ?></h3>
                                <p>Private Rooms</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card red">
                                <i class="fas fa-heartbeat"></i>
                                <h3><?php echo $stats['icu']; ?></h3>
                                <p>ICU Rooms</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card orange">
                                <i class="fas fa-ambulance"></i>
                                <h3><?php echo $stats['emergency']; ?></h3>
                                <p>Emergency Rooms</p>
                            </div>
                        </div>
                    </div>

                    <!-- Available Rooms Grid -->
                    <div class="row">
                        <?php if (empty($available_rooms)): ?>
                            <div class="col-12">
                                <div class="card glass text-center">
                                    <div class="card-body">
                                        <i class="fas fa-bed fa-4x text-muted mb-3"></i>
                                        <h4 class="text-white">No Available Rooms</h4>
                                        <p class="text-white">All rooms are currently occupied. Please check back later.</p>
                                        <a href="index.php" class="btn btn-primary">Back to Home</a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($available_rooms as $room): ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card glass-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title">
                                                    <i class="fas fa-bed text-primary"></i> 
                                                    Room <?php echo htmlspecialchars($room['room_number']); ?>
                                                </h5>
                                                <span class="badge bg-success">Available</span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <span class="badge bg-info mb-2">
                                                    <?php echo ucfirst(htmlspecialchars($room['room_type'])); ?>
                                                </span>
                                                <p class="text-muted mb-1">
                                                    <i class="fas fa-bed"></i> <?php echo $room['bed_count']; ?> 
                                                    <?php echo $room['bed_count'] == 1 ? 'Bed' : 'Beds'; ?>
                                                </p>
                                            </div>

                                            <div class="mb-3">
                                                <h4 class="text-success">₹<?php echo number_format($room['price_per_day'], 2); ?></h4>
                                                <small class="text-muted">per day</small>
                                            </div>

                                            <?php if (!empty($room['amenities'])): ?>
                                                <div class="mb-3">
                                                    <h6>Amenities:</h6>
                                                    <p class="text-muted small"><?php echo htmlspecialchars($room['amenities']); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <div class="d-grid">
                                                <a href="book-room.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">
                                                    <i class="fas fa-calendar-plus"></i> Book Room
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card glass text-center">
                                <div class="card-body">
                                    <h5 class="text-white mb-3">Need Help Booking a Room?</h5>
                                    <p class="text-white">Contact our reception desk for assistance</p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <i class="fas fa-phone fa-2x text-primary mb-2"></i>
                                            <p class="text-white">+91 98765 43210</p>
                                        </div>
                                        <div class="col-md-4">
                                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                            <p class="text-white">rooms@medicareplus.com</p>
                                        </div>
                                        <div class="col-md-4">
                                            <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                            <p class="text-white">24/7 Available</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>