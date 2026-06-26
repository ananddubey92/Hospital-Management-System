<?php
require_once __DIR__ . '/config/database.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        header('Location: departments.php?deleted=1');
    } else {
        header('Location: departments.php?error=1');
    }
} catch(PDOException $e) {
    header('Location: departments.php?error=1');
}
exit();
?>