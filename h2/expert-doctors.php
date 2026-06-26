<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getConnection();
    
    // Get expert doctors (experience >= 5 years and rating >= 4.0)
    $query = "SELECT d.*, dept.name as department_name FROM doctors d 
              LEFT JOIN departments dept ON d.department_id = dept.id 
              WHERE d.experience >= 5 AND d.rating >= 4.0 AND d.status = 'active'
              ORDER BY d.rating DESC, d.experience DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $expert_doctors = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $expert_doctors = [];
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expert Doctors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; width: 100vw; overflow-x: hidden; }
        
        @media (max-width: 768px) {
            div[style*="padding: 2rem"] { padding: 1rem !important; }
            .table { font-size: 0.85rem; min-width: 700px !important; }
            .table th, .table td { padding: 0.5rem 0.25rem; white-space: nowrap; }
            .badge { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
            h2 { font-size: 1.75rem; }
        }
        
        @media (max-width: 576px) {
            div[style*="padding: 1rem"] { padding: 0.5rem !important; }
            .table { font-size: 0.75rem; min-width: 600px !important; }
            .table th, .table td { padding: 0.375rem 0.25rem; }
            .btn { width: 100%; margin-bottom: 0.5rem; }
            div[style*="flex-wrap: wrap"] { flex-direction: column; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div style="width: 100vw; min-height: 100vh; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div style="width: 100%; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border-radius: 15px; padding: 2rem;">
                        <h2><i class="fas fa-star"></i> Expert Doctors</h2>
                        <p class="text-center text-white mb-4">
                            Our most experienced and highly-rated doctors (5+ years experience, 4+ rating)
                        </p>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <?php if (count($expert_doctors) > 0): ?>
                        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                            <table class="table table-striped" style="width: 100%; min-width: 900px; background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; margin-bottom: 0;">
                                <thead>
                                    <tr>
                                        <th>Doctor</th>
                                        <th>Department</th>
                                        <th>Specialization</th>
                                        <th>Experience</th>
                                        <th>Rating</th>
                                        <th>Consultation Fee</th>
                                        <th>Contact</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expert_doctors as $doctor): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($doctor['name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($doctor['qualification']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($doctor['department_name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                        <td><?php echo $doctor['experience']; ?> years</td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-star"></i> <?php echo $doctor['rating']; ?>
                                            </span>
                                        </td>
                                        <td>₹<?php echo number_format($doctor['consultation_fee'], 2); ?></td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars($doctor['phone']); ?><br>
                                                <?php echo htmlspecialchars($doctor['email']); ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-white">
                            <p>No expert doctors found.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
                            <a href="schedule-appointment.php" class="btn btn-primary" style="flex: 1; max-width: 200px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-calendar-plus"></i> Book Appointment
                            </a>
                            <a href="index.php" class="btn btn-secondary" style="flex: 1; max-width: 200px; border-radius: 25px; padding: 12px 20px;">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
        </div>
    </div>
</body>
</html>