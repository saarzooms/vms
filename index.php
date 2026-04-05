<?php
require_once 'auth.php';

// Mark OUT logic
if (isset($_GET['mark_out']) && canMarkOut()) {
    $stmt = $pdo->prepare("UPDATE visits SET out_time = NOW() WHERE id = ?");
    $stmt->execute([$_GET['mark_out']]);
    header("Location: index.php");
    exit;
}

// Data fetching based on role
$query = "SELECT visits.*, visitors.name, visitors.mobile FROM visits JOIN visitors ON visits.visitor_id = visitors.id";
if ($current_role === 'receptionist') {
    $query .= " WHERE DATE(in_time) = CURDATE()"; // Today's Desk only
}
$query .= " ORDER BY in_time DESC";
$visits = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="container mt-4">
    <div class="d-flex justify-content-between mb-4 border-bottom pb-2">
        <h2>Dashboard - <?= strtoupper($current_role) ?></h2>
        <div>
            <?php if($current_role === 'admin'): ?>
            <a href="users.php" class="btn btn-dark">Manage Users</a>
        <?php endif; ?>
            <?php if($current_role === 'admin' || $current_role === 'manager'): ?>
            <a href="reports.php" class="btn btn-warning">View Reports</a>
        <?php endif; ?>
            <?php if(canRegisterVisitor()): ?>
                <a href="visitors.php" class="btn btn-success">Manage Visitors</a>
                <a href="add_visit.php" class="btn btn-primary">Add Visit Entry</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <h4><?= $current_role === 'receptionist' ? "Today's Desk" : "All Visitor History" ?></h4>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Name</th><th>Mobile</th><th>Purpose</th><th>Visited To</th><th>In Time</th><th>Out Time</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($visits as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['name']) ?></td>
                <td><?= htmlspecialchars($v['mobile']) ?></td>
                <td><?= htmlspecialchars($v['purpose']) ?></td>
                <td><?= htmlspecialchars($v['visited_to']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($v['in_time'])) ?></td>
                <td><?= $v['out_time'] ? date('Y-m-d H:i', strtotime($v['out_time'])) : '<span class="badge bg-warning">Inside</span>' ?></td>
                <td>
                    <?php if(!$v['out_time'] && canMarkOut()): ?>
                        <a href="?mark_out=<?= $v['id'] ?>" class="btn btn-sm btn-danger">Mark OUT</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>