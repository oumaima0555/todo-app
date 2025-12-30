<?php

// Creates a sample user with email and hashed password if it doesn't exist
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=todo-app;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = 'user@example.com';
    $plain = 'password123';

    $stmt = $pdo->prepare('SELECT id FROM `user` WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo "User $email already exists\n";
        exit(0);
    }

    $hash = password_hash($plain, PASSWORD_BCRYPT);
    $roles = json_encode(['ROLE_USER']);

    $insert = $pdo->prepare('INSERT INTO `user` (email, roles, password, is_verified) VALUES (:email, :roles, :password, 1)');
    $insert->execute(['email' => $email, 'roles' => $roles, 'password' => $hash]);

    echo "Created user $email with password $plain\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
