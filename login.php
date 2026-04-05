<?php
require_once 'config.php';
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $stmt = $pdo->prepare("select * from users where username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if($user && password_verify($_POST['password'], $user['password'])){
        $_SESSION['user_id']=$user['id'];
        $_SESSION['username']=$user['username'];
        $_SESSION['role']=$user['role'];
        header("Location:index.php");
        exit;
    }else{
        $error = "Invalid credentials";
    }
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
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container text-center">
        <h2 class="mb-4">Visitor System</h2>
        <form method="post" class="card p-4 shadow-sm">
            <?php if(isset($error))echo "<div class ='alert alert-danger'>$error</div>";?>
            <input type="text" name="username" id="username" class="form-control mb-3" required placeholder="Enter username">
            <input type="password" name="password" id="password" class="form-control mb-3" required placeholder="Enter password">
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>