<?php
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: login.php");
    exit;
}

$current_role = $_SESSION['role'] ?? null;

// Permission Checkers based on the image provided
function canRegisterVisitor() {
    global $current_role;
    return in_array($current_role, ['admin', 'receptionist']);
}

function canEditOrDelete() {
    global $current_role;
    return $current_role === 'admin';
}

function canMarkOut() {
    global $current_role;
    return in_array($current_role, ['admin', 'receptionist']);
}

function requirePermission($condition) {
    if (!$condition) {
        die("<h3 style='color:red;'>Access Denied. Your role does not have permission for this action.</h3><a href='index.php'>Go Back</a>");
    }
}
?>