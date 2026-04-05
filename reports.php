<?php
require_once 'auth.php';

// Strict Role Check: Receptionists are NOT allowed to view reports
if ($current_role === 'receptionist') {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h3 style='color:red;'>Access Denied</h3>
            <p>Your role does not have permission to view reports.</p>
            <a href='index.php'>Back to Dashboard</a>
         </div>");
}

// Default dates: First day of the current month to Today
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$reports = [];

// Fetch visits between the selected dates
$query = "
    SELECT visits.*, visitors.name, visitors.mobile 
    FROM visits 
    JOIN visitors ON visits.visitor_id = visitors.id 
    WHERE DATE(visits.in_time) >= ? AND DATE(visits.in_time) <= ?
    ORDER BY visits.in_time DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$start_date, $end_date]);
$reports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Between Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Hide UI elements when printing the report */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background-color: white; }
            .container { max-width: 100%; width: 100%; margin: 0; }
        }
    </style>
</head>
<body class="container mt-4 mb-5 bg-light">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 no-print">
        <h2>Visit Reports</h2>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card p-4 shadow-sm mb-4 no-print">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
            </div>
        </form>
    </div>

    <div class="card p-4 shadow-sm bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                Report: <span class="text-primary"><?= date('d M Y', strtotime($start_date)) ?></span> to <span class="text-primary"><?= date('d M Y', strtotime($end_date)) ?></span>
            </h4>
            <button onclick="window.print()" class="btn btn-dark no-print">🖨️ Print Report</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Visited To</th>
                        <th>Purpose</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reports as $r): ?>
                    <tr>
                        <td><?= date('Y-m-d', strtotime($r['in_time'])) ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['mobile']) ?></td>
                        <td><?= htmlspecialchars($r['visited_to']) ?></td>
                        <td><?= htmlspecialchars($r['purpose']) ?></td>
                        <td><?= date('h:i A', strtotime($r['in_time'])) ?></td>
                        <td>
                            <?php if ($r['out_time']): ?>
                                <?= date('h:i A', strtotime($r['out_time'])) ?>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark no-print">Still Inside</span>
                                <span class="d-none d-print-block">Inside</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($reports)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No visits found for the selected dates.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>