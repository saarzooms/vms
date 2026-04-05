<?php
session_start();
  // Simple .env parser
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    $env = parse_ini_file($env_path);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'visitor_db';
$user = $_ENV['DB_USER'] ?? '1234';
$pass = $_ENV['DB_PASS'] ?? '';

    try{
        $pdo =  new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);

    }catch(PDOException $e){
        die("Database connection faield:".$e->getMessage());
    }
?>