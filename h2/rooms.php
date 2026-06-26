<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    
    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $type_filter = isset($_GET['type']) ? $_GET['type'] : '';
    
    // Build query with filters
    $query = "SELECT * FROM rooms WHERE 1=1";
    $params = [];
    
    if (!empty($status_filter)) {
        $query .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($type_filter)) {
        $query .= " AND room_type = ?";
        $params[] = $type_filter;
    }
    
    $query .= " ORDER BY room_number";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
    
    // Get counts for statistics
    $total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    $available_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetchColumn();
    $occupied_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'occupied'")->fetchColumn();
    $maintenance_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'maintenance'")->fetchColumn();
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; width: 100vw; overflow-x: hidden;">
    <div class="w-100" style="min-height: 100vh; padding: 1rem;">
        <div class="w-100">
            <div class="w-100" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border-radius: 15px; padding: 2rem;">
                        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap">
                            <div class="mb-3 mb-md-0">
                                <h2><i class="fas fa-bed"></i> Room Management</h2>
                                <p class="text-white mb-0">Manage hospital rooms and availability</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="add-room.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">Add New Room</span><span class="d-sm-none">Add Room</span>
                                </a>
                                <a href="admin_dashboard.php" class="btn btn-outline-light">
                                    <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Dashboard</span>
                                </a>
                            </div>
                        </div>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php
                                switch ($_GET['success']) {
                                    case 'room_deleted':
                                        echo 'Room deleted successfully!';
                                        break;
                                    default:
                                        echo 'Operation completed successfully!';
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php
                                switch ($_GET['error']) {
                                    case 'invalid_id':
                                        echo 'Invalid room ID provided.';
                                        break;
                                    case 'room_not_found':
                                        echo 'Room not found.';
                                        break;
                                    case 'room_has_bookings':
                                        echo 'Cannot delete room with existing bookings.';
                                        break;
                                    case 'database_error':
                                        echo 'Database error occurred.';
                                        break;
                                    default:
                                        echo 'An error occurred.';
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Statistics Cards -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; width: 100%;">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center p-3">
                                    <h3 class="mb-2"><?php echo $total_rooms; ?></h3>
                                    <p class="mb-0 small">Total Rooms</p>
                                </div>
                            </div>
                            <div class="card bg-success text-white">
                                <div class="card-body text-center p-3">
                                    <h3 class="mb-2"><?php echo $available_rooms; ?></h3>
                                    <p class="mb-0 small">Available</p>
                                </div>
                            </div>
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center p-3">
                                    <h3 class="mb-2"><?php echo $occupied_rooms; ?></h3>
                                    <p class="mb-0 small">Occupied</p>
                                </div>
                            </div>
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center p-3">
                                    <h3 class="mb-2"><?php echo $maintenance_rooms; ?></h3>
                                    <p class="mb-0 small">Maintenance</p>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="card mb-4" style="width: 100%;">
                            <div class="card-body">
                                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; width: 100%;">
                                    <div>
                                        <label for="status" class="form-label">Filter by Status</label>
                                        <select class="form-control" id="status" name="status" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #00bcd4; color: white; border-radius: 12px; padding: 12px 15px; box-shadow: 0 4px 15px rgba(0, 188, 212, 0.3);">
                                            <option value="" style="background: #2c3e50; color: #00bcd4; font-weight: bold;">All Status</option>
                                            <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?> style="background: #27ae60; color: white;">Available</option>
                                            <option value="occupied" <?php echo $status_filter == 'occupied' ? 'selected' : ''; ?> style="background: #f39c12; color: white;">Occupied</option>
                                            <option value="maintenance" <?php echo $status_filter == 'maintenance' ? 'selected' : ''; ?> style="background: #e74c3c; color: white;">Maintenance</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="type" class="form-label">Filter by Type</label>
                                        <select class="form-control" id="type" name="type" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #9c27b0; color: white; border-radius: 12px; padding: 12px 15px; box-shadow: 0 4px 15px rgba(156, 39, 176, 0.3);">
                                            <option value="" style="background: #2c3e50; color: #9c27b0; font-weight: bold;">All Types</option>
                                            <option value="general" <?php echo $type_filter == 'general' ? 'selected' : ''; ?> style="background: #3498db; color: white;">General</option>
                                            <option value="private" <?php echo $type_filter == 'private' ? 'selected' : ''; ?> style="background: #8e44ad; color: white;">Private</option>
                                            <option value="icu" <?php echo $type_filter == 'icu' ? 'selected' : ''; ?> style="background: #e67e22; color: white;">ICU</option>
                                            <option value="emergency" <?php echo $type_filter == 'emergency' ? 'selected' : ''; ?> style="background: #c0392b; color: white;">Emergency</option>
                                        </select>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: end;">
                                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="rooms.php" class="btn btn-secondary" style="flex: 1;">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Rooms Table -->
                        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table class="table table-striped" style="width: 100%; min-width: 800px;">
                                <thead>
                                    <tr>
                                        <th>Room Number</th>
                                        <th>Type</th>
                                        <th>Beds</th>
                                        <th>Price/Day</th>
                                        <th>Status</th>
                                        <th>Amenities</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rooms)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <i class="fas fa-bed fa-3x mb-3 text-muted"></i>
                                                <p class="text-muted">No rooms found. <a href="add-room.php" class="text-primary">Add the first room</a></p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($rooms as $room): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($room['room_number']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo ucfirst(htmlspecialchars($room['room_type'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-bed"></i> <?php echo $room['bed_count']; ?>
                                                </td>
                                                <td>
                                                    <strong>₹<?php echo number_format($room['price_per_day'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($room['status']) {
                                                        case 'available':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'occupied':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'maintenance':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($room['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($room['amenities'], 0, 50)) . (strlen($room['amenities']) > 50 ? '...' : ''); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo date('M d, Y', strtotime($room['created_at'])); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary btn-sm" onclick="editRoom(<?php echo $room['id']; ?>)" title="Edit Room">
                                                            <i class="fas fa-edit"></i><span class="d-none d-lg-inline ms-1">Edit</span>
                                                        </button>
                                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>')" title="Delete Room">
                                                            <i class="fas fa-trash"></i><span class="d-none d-lg-inline ms-1">Delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editRoom(roomId) {
            // For now, redirect to add-room.php with edit parameter
            window.location.href = 'edit-room.php?id=' + roomId;
        }

        function deleteRoom(roomId, roomNumber) {
            if (confirm('Are you sure you want to delete room ' + roomNumber + '?')) {
                window.location.href = 'delete-room.php?id=' + roomId;
            }
        }
    </script>
</body>
</html>