<?php
session_start();
if (isset($_POST["email"], $_POST["fileid"], $_POST["filename"], $_POST["mime"], $_POST["private_key"], $_POST["iv"])) {
    require_once "../inc/conn.php";
    $destination_id = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $destination_id->bindParam(1, $_POST["email"]);
    $destination_id->execute();
    $destination_id = $destination_id->fetch()["id"];

    $insert = $conn->prepare("INSERT INTO files (name, mime, file_aes256cbc_private_key, file_aes256cbc_iv, owner_id) VALUES (?, ?, ?, ?, ?)");
    $insert->bindParam(1, $_POST["filename"], PDO::PARAM_STR);
    $insert->bindParam(2, $_POST["mime"], PDO::PARAM_STR);
    $insert->bindParam(3, $_POST["private_key"], PDO::PARAM_STR);
    $insert->bindParam(4, $_POST["iv"], PDO::PARAM_STR);
    $insert->bindParam(5, $destination_id, PDO::PARAM_INT);
    $insert->execute();

    $file_id = $conn->prepare("SELECT id FROM files WHERE name = ? AND file_aes256cbc_private_key = ?");
    $file_id->bindParam(1, $_POST["filename"], PDO::PARAM_STR);
    $file_id->bindParam(2, $_POST["private_key"], PDO::PARAM_STR);
    $file_id->execute();
    $file_id = $file_id->fetch()["id"];

    // clone file
    $data = file_get_contents("../files/" . $_POST["fileid"]);
    file_put_contents("../files/" . $file_id, $data);
    
} else if (isset($_POST["email"], $_POST["fileid"]) && !empty($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    require_once "../inc/conn.php";

    $rsaPublic = $conn->prepare("SELECT rsa_public FROM users WHERE email = ?");
    $rsaPublic->bindParam(1, $_POST["email"], PDO::PARAM_STR);
    $rsaPublic->execute();
    $rsaPublic = $rsaPublic->fetch();
    
    $file = $conn->prepare("SELECT * FROM files WHERE id = ? AND owner_id = ?");
    $file->bindParam(1, $_POST["fileid"], PDO::PARAM_STR);
    $file->bindParam(2, $_SESSION["id"], PDO::PARAM_INT);
    $file->execute();
    $file = $file->fetch();

    echo json_encode(["rsa_public" => $rsaPublic["rsa_public"], "filename" => $file["name"], "mime" => $file["mime"], "file_aes256cbc_private_key" => $file["file_aes256cbc_private_key"], "file_aes256cbc_iv" => $file["file_aes256cbc_iv"]]);
}
?>