<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=todo-app;charset=utf8mb4','root','');
    $stmt = $pdo->query("SHOW TABLES LIKE 'doctrine%'");
    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo implode("\n", $rows) . "\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
