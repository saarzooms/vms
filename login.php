<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    // Note: In production use password_verify(). 
    // Using simple check for default setup. The default hash in SQL matches 'admin123'
    if ($user && password_verify($_POST['password'], $user['password'])) { 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center vh-100">
    <div class="container text-center" style="max-width: 400px;">
        <h2 class="mb-4">Visitor System</h2>
        <form method="POST" class="card p-4 shadow-sm">
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" value="admin123" required>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</body>
</html>