<?php
session_start();
if (isset($_POST["email"], $_POST["csrf"])) {
    // validate inputs
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && strlen($_POST["csrf"]) === 32) {
        // check if CSRF is valid
        if ($_SESSION["csrf"] === $_POST["csrf"]) {
            // check if email already exists
            require_once '../inc/conn.php';
            // check if email is already registered
            $select_email = $conn->prepare('SELECT email FROM users WHERE email = ?');
            $select_email->bindParam(1, $_POST["email"], PDO::PARAM_STR);
            $select_email->execute();
            $select_email = $select_email->fetch();
            if (empty($select_email)) {
                echo json_encode(["success" => true, "message" => ""]);
            } else {
                echo json_encode(["success" => false, "message" => "E-Mail already exists"]);
            }
        } else {
            // invalid CSRF, block request
            echo json_encode(["success" => false, "message" => "Invalid CSRF"]);
            // TODO rate-limit
            die();
        }
    } else {
        // invalid malformed CSRF, block request
        echo json_encode(["success" => false, "message" => "Invalid CSRF"]);
        // TODO rate-limit
        die();
    }
}
?>