<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConnection();
$tests = $pdo->query("SELECT * FROM diagnostic_tests ORDER BY test_date DESC, created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Test Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0dcaf022, #ffffff); }
        .results-header { background: linear-gradient(135deg, #0dcaf0, #0bb5d6); color: white; padding: 30px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="results-header">
        <h1><i class="fas fa-file-medical"></i> Diagnostic Test Results</h1>
        <p>View and Download Test Reports</p>
    </div>

    <div class="container my-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <h4>Total Tests: <?php echo count($tests); ?></h4>
            </div>
            <div class="col-md-6 text-end">
                <a href="diagnostic-services.php" class="btn btn-info">
                    <i class="fas fa-plus"></i> Schedule New Test
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-info">
                    <tr>
                        <th>Test ID</th>
                        <th>Patient Name</th>
                        <th>Test Type</th>
                        <th>Test Date</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($test['test_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($test['patient_name']); ?></td>
                        <td>
                            <i class="fas fa-x-ray text-info"></i>
                            <?php echo htmlspecialchars($test['test_type']); ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($test['test_date'])); ?></td>
                        <td class="text-success">₹<?php echo number_format($test['cost'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $test['status'] === 'Completed' ? 'success' : 
                                    ($test['status'] === 'In Progress' ? 'warning' : 'primary'); 
                            ?>">
                                <?php echo $test['status']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewReport('<?php echo $test['test_id']; ?>')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($test['status'] === 'Completed'): ?>
                            <button class="btn btn-sm btn-success" onclick="downloadReport('<?php echo $test['test_id']; ?>')">
                                <i class="fas fa-download"></i> Download
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="diagnostic-services.php" class="btn btn-info me-2">
                <i class="fas fa-arrow-left"></i> Back to Diagnostic Services
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Home
            </a>
        </div>
    </div>

    <script>
        function viewReport(testId) {
            alert('Viewing report for Test ID: ' + testId);
        }
        
        function downloadReport(testId) {
            alert('Downloading report for Test ID: ' + testId);
        }
    </script>
</body>
</html>