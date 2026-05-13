<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if (db_delete($id)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Hindi nabura ang entry.";
    }
}