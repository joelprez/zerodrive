<?php
session_start();

if (isset($_POST["rsa_public"])) {
    require '../inc/conn.php';
    $update = $conn->prepare("UPDATE users SET rsa_public = ?, status = 1 WHERE id = ?");
    $update->bindParam(1, $_POST["rsa_public"], PDO::PARAM_STR);
    $update->bindParam(2, $_SESSION["id"], PDO::PARAM_INT);
    $update->execute();
}
?>