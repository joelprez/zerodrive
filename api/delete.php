<?php
session_start();
if (isset($_POST["fileid"], $_SESSION["id"])) {
    require_once "../inc/conn.php";

    $delete = $conn->prepare("DELETE FROM files WHERE id = ? AND owner_id = ?");
    $delete->bindParam(1, $_POST["fileid"], PDO::PARAM_INT);
    $delete->bindParam(2, $_SESSION["id"], PDO::PARAM_INT);
    $delete->execute();

    unlink("../files/" . $_POST["fileid"]);
}
?>