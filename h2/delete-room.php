<?php
require_once 'config/database.php';

$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($room_id <= 0) {
    header('Location: rooms.php?error=invalid_id');
    exit();
}

try {
    $pdo = getConnection();
    
    // Check if room exists
    $stmt = $pdo->prepare("SELECT room_number FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        header('Location: rooms.php?error=room_not_found');
        exit();
    }
    
    // Check if room has any bookings
    $booking_check = $pdo->prepare("SELECT COUNT(*) FROM room_bookings WHERE room_id = ?");
    $booking_check->execute([$room_id]);
    $booking_count = $booking_check->fetchColumn();
    
    if ($booking_count > 0) {
        header('Location: rooms.php?error=room_has_bookings');
        exit();
    }
    
    // Delete the room
    $delete_stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $delete_stmt->execute([$room_id]);
    
    header('Location: rooms.php?success=room_deleted');
    exit();
    
} catch (PDOException $e) {
    header('Location: rooms.php?error=database_error');
    exit();
}
?>