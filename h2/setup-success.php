<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complete - Hospital Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <meta http-equiv="refresh" content="5;url=<?php echo $_GET['redirect'] ?? 'services.php'; ?>">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-success text-center p-4" role="alert">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="alert-heading">Setup Complete!</h4>
                    <p class="mb-3"><?php echo $_GET['message'] ?? 'Medical services setup completed successfully!'; ?></p>
                    <p class="small text-muted">Redirecting to dashboard in 5 seconds...</p>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="<?php echo $_GET['redirect'] ?? 'services.php'; ?>" class="btn btn-success">
                            <i class="fas fa-stethoscope"></i> Go to Dashboard
                        </a>
                        <a href="admin_dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Back to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>