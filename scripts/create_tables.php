<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=todo-app;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS task (
        id INT AUTO_INCREMENT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description LONGTEXT DEFAULT NULL,
        status TINYINT(1) NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' ENGINE = InnoDB");

    $pdo->exec("CREATE TABLE IF NOT EXISTS messenger_messages (
        id INT AUTO_INCREMENT NOT NULL,
        body LONGTEXT NOT NULL,
        headers LONGTEXT NOT NULL,
        queue_name VARCHAR(190) NOT NULL,
        created_at DATETIME NOT NULL,
        available_at DATETIME NOT NULL,
        delivered_at DATETIME DEFAULT NULL,
        PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci' ENGINE = InnoDB");

    $pdo->exec("CREATE INDEX IF NOT EXISTS IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)");

    echo "Tables created or already exist\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
