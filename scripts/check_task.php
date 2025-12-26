<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=todo-app;charset=utf8mb4', 'root', '');
    $stmt = $pdo->query('SELECT COUNT(*) AS cnt FROM task');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "task table row count: " . ($row['cnt'] ?? '0') . "\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}
