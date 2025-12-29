<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=todo-app;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS `user` (
        id INT AUTO_INCREMENT NOT NULL,
        email VARCHAR(180) NOT NULL,
        roles JSON NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_verified TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY(id),
        UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' ENGINE = InnoDB");

    echo "User table created or already exists\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
