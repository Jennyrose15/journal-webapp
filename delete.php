<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // SQL command para burahin ang entry base sa ID
    $stmt = $conn->prepare("DELETE FROM entries WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        // Kapag nabura na, balik sa index.php
        header("Location: index.php");
        exit();
    } else {
        echo "Hindi nabura ang entry.";
    }
}
?>