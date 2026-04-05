<?php
    require_once 'config.php';
    $visitor = null;
    if(isset($_GET['search_mobile'])){
        $stmt = $pdo->prepare('select * from visitors where mobile = ?');
        $stmt->execute([$_GET['search_mobile']]);
        $visitor  = $stmt->fetch();
    }
    if($_SERVER['REQUEST_METHOD']=="POST"){
        $stmt = $pdo->prepare("insert into visits (visitor_id, purpose, visited_to) values (?,?,?)");
        $stmt->execute([$_POST['visitor_id'], $_POST['purpose'], $_POST['visited_to']]);
        header("Location:index.php");
        exit;
    }
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
    <h2>Add Visit Entry</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
   <form method="GET" class="d-flex mb-4">
        <input type="text" name="search_mobile" class="form-control me-2" placeholder="Search Visitor by Mobile..." required>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>  
    <?php
        if($visitor):?>
        <div class="card p-4">
            <h4>Visitor: <?= htmlspecialchars($visitor['name'])?></h4>
            <form  method="post">
                <input type="hidden" name="visitor_id" value="<?=$visitor['id']?>">
                <div class="mb-3">
                    <label for="purpose">Purpose of visit</label>
                    <input type="text" name="purpose" id="purpose" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="visited_to">Visited to</label>
                    <input type="text" name="visited_to" id="visited_to" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Check-In</button>
            </form>
        </div>
    <?php endif;?>
</body>
</html>