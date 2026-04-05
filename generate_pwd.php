<?php
    $password = "password123";

    $hashedPassword = password_hash($password,PASSWORD_DEFAULT);

    echo "Password".$password.'<br/>';
    echo "hash".$hashedPassword.'<br/>';
?>