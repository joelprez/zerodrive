<?php
session_start();

if (isset($_SESSION["id"]) && is_numeric($_SESSION["id"])) {
    require_once("inc/conn.php");
    $user = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user->bindParam(1, $_SESSION["id"], PDO::PARAM_INT);
    $user->execute();
    $user = $user->fetch();
    
    switch ($user['status']) {
        case 0:
            require_once 'inc/generate_keys.php';
            break;
        case 1:
            require_once 'inc/otp_setup.php';
            break;
        case 2:
            header("Location: dashboard");
            break;
    }
} else {
    header("Location: login.php");
    die();
}
?>