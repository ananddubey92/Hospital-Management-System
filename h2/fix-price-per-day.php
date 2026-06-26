<?php
require_once 'config/database.php';

try {
    $pdo = getConnection();
    
    echo "<h3>Fixing price_per_day field issues...</h3>";
    
    // Check if there are any rooms with NULL or 0 price_per_day
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms WHERE price_per_day IS NULL OR price_per_day = 0");
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "<p>Found {$result['count']} rooms with missing price_per_day values.</p>";
        
        // Update rooms with default prices based on room type
        $updates = [
            "UPDATE rooms SET price_per_day = 500.00 WHERE room_type = 'general' AND (price_per_day IS NULL OR price_per_day = 0)",
            "UPDATE rooms SET price_per_day = 1500.00 WHERE room_type = 'private' AND (price_per_day IS NULL OR price_per_day = 0)",
            "UPDATE rooms SET price_per_day = 3000.00 WHERE room_type = 'icu' AND (price_per_day IS NULL OR price_per_day = 0)",
            "UPDATE rooms SET price_per_day = 2000.00 WHERE room_type = 'emergency' AND (price_per_day IS NULL OR price_per_day = 0)"
        ];
        
        foreach ($updates as $sql) {
            $pdo->exec($sql);
        }
        
        echo "<p style='color: green;'>✓ Updated rooms with default price_per_day values.</p>";
    } else {
        echo "<p style='color: green;'>✓ All rooms already have valid price_per_day values.</p>";
    }
    
    // Ensure the column is NOT NULL (it should already be from the schema)
    try {
        $pdo->exec("ALTER TABLE rooms MODIFY COLUMN price_per_day DECIMAL(10,2) NOT NULL");
        echo "<p style='color: green;'>✓ Ensured price_per_day column is NOT NULL.</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already') !== false) {
            echo "<p style='color: blue;'>ℹ Column price_per_day is already NOT NULL.</p>";
        } else {
            throw $e;
        }
    }
    
    echo "<h4>Summary:</h4>";
    echo "<p>The SQLSTATE[HY000]: 1364 error has been fixed by:</p>";
    echo "<ul>";
    echo "<li>✓ Updated room-booking.php to include price_per_day in INSERT queries</li>";
    echo "<li>✓ Added price_per_day input field to the booking form</li>";
    echo "<li>✓ Set default values for existing rooms missing price_per_day</li>";
    echo "<li>✓ Ensured database column is properly configured as NOT NULL</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Price Per Day Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h3, h4 { color: #333; }
        p { margin: 10px 0; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <div style="margin-top: 20px;">
        <a href="rooms.php" style="background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">View Rooms</a>
        <a href="add-room.php" style="background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;">Add Room</a>
        <a href="admin_dashboard.php" style="background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;">Dashboard</a>
    </div>
</body>
</html>