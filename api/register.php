<?php
session_start();

if (isset($_POST["email"], $_POST["password"], $_POST["g_recaptcha_response"], $_POST["csrf"])) {
    // include database
    require_once '../inc/conn.php';
    require_once '../inc/config.php';

    // filter user input
    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && ctype_alnum($_POST["password"]) && ctype_alnum($_POST["csrf"]) && strlen($_POST["password"]) === 128 && strlen($_POST["csrf"]) === 32 && $_POST["csrf"] === $_SESSION["csrf"] && !empty($_POST["g_recaptcha_response"])) {
        // send recaptcha info
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?" . http_build_query([
            'secret' => $recaptcha_secret,
            'response' => $_POST['g_recaptcha_response'],
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ]));
        // check if recaptcha is correct
        if (json_decode($response)->success) {
            // check if email is already registered
            $select_email = $conn->prepare('SELECT email FROM users WHERE email = ?');
            $select_email->bindParam(1, $_POST["email"], PDO::PARAM_STR);
            $select_email->execute();
            $select_email = $select_email->fetch();
            if (empty($select_email)) {
                // save user into database
                $insert_user = $conn->prepare('INSERT INTO users (email, password, totp_secret) VALUES (?, ?, ?)');
                $insert_user->bindParam(1, $_POST["email"], PDO::PARAM_STR);
                $options = ["cost" => $passwordhash_bcrypt_cost];
                $password = password_hash($_POST["password"], PASSWORD_BCRYPT, $options);
                $insert_user->bindParam(2, $password, PDO::PARAM_STR);
                require_once '../inc/totp/Totp.php';
                $totp = new Totp;
                $totp_private = $totp->generateSecret();
                $insert_user->bindParam(3, $totp_private, PDO::PARAM_STR);
                $insert_user->execute();
                echo json_encode(["success" => true, "message" => ""]);
            } else {
                // email already registered
                echo json_encode(["success" => false, "message" => "Email already registered"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid captcha"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    }
}
?>