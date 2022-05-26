<?php
session_start();

require '../inc/conn.php';

$select = $conn->prepare("SELECT * FROM files WHERE owner_id = ?");
$select->bindParam(1, $_SESSION["id"], PDO::PARAM_STR);
$select->execute();
$select = $select->fetchAll();

$files = [];

foreach ($select as $file) {
    $files[] = [$file['id'], 1, $file['name'], $file['mime']];
}

echo json_encode($files);
?>