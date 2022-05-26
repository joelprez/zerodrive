<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["filename"], $_POST["mime"], $_POST["aes_private_key"], $_POST["aes_iv"], $_POST["file"])) {
    require "../inc/conn.php";
    $insert = $conn->prepare("INSERT INTO files (name, path, mime, file_aes256cbc_private_key, file_aes256cbc_iv, owner_id) VALUES (?, NULL, ?, ?, ?, ?)");
    $insert->bindParam(1, $_POST["filename"], PDO::PARAM_STR);
    $insert->bindParam(2, $_POST["mime"], PDO::PARAM_STR);
    $insert->bindParam(3, $_POST["aes_private_key"], PDO::PARAM_STR);
    $insert->bindParam(4, $_POST["aes_iv"], PDO::PARAM_STR);
    $insert->bindParam(5, $_SESSION["id"]);
    $insert->execute();
    
    $select = $conn->prepare("SELECT id FROM files WHERE file_aes256cbc_private_key = ?");
    $select->bindParam(1, $_POST["aes_private_key"], PDO::PARAM_STR);
    $select->execute();
    $select = $select->fetch();

    file_put_contents("../files/" . $select["id"], $_POST["file"]);
    echo $select["id"];
}
?>