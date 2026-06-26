<?php
// Simple test to demonstrate the success flow
header('Location: setup-success.php?message=' . urlencode('Medical services setup completed successfully!') . '&redirect=services.php');
exit();
?>