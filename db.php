<?php

date_default_timezone_set('Asia/Manila');

try {
    $conn = new PDO("sqlite:" . __DIR__ . "/journal.db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $conn->exec("CREATE TABLE IF NOT EXISTS entries (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        title      TEXT     NOT NULL,
        content    TEXT     NOT NULL,
        mood       TEXT     NOT NULL DEFAULT 'Happy',
        created_at DATETIME DEFAULT (datetime('now', 'localtime'))
    )");

} catch (PDOException $e) {
    die("Hindi makakonekta: " . $e->getMessage());
}
?>