<?php
    require_once 'config.php';
    $query = "select visits.*, visitors.name, visitors.mobile from visits join visitors on visits.visitor_id = visitors.id";
    $visits = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <div class="d-flex justify-content-between mb-4 border-bottom pb-2">
    <h2>Dashboard</h2>
    <div>
        <a href="users.php" class="btn btn-dark">Manage Users</a>
        <a href="reports.php" class="btn btn-warning">View Reports</a>
        <a href="visitors.php" class="btn btn-success">Manage Visitors</a>
        <a href="add-visit.php" class="btn btn-primary">Add Visit Entry</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
    </div>
    <h4>All Visitor History</h4>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Mobile</th>
                <th>Purpose</th>
                <th>Visited To</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Action</th> 
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visits as $v) { ?>
                <tr>
                    <td><?=htmlspecialchars($v['name'])?></td>
                    <td><?=htmlspecialchars($v['mobile'])?></td>
                    <td><?=htmlspecialchars($v['purpose'])?></td>
                    <td><?=htmlspecialchars($v['visited_to'])?></td>
                    <td><?=date('d-m-Y H:i', strtotime($v['in_time']))?></td>
                    <td><?=$v['out_time'] ? date('d-m-Y H:i', strtotime($v['out_time'])): '<span class="badge bg-warning">Inside</span>'?></td>
                    <td><a href="?mark_out=<?= $v['id']?>" class="btn btn-sm btn-danger">Mark out</a></td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</body>
</html>