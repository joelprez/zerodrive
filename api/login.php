<?php
session_start();

if (isset($_POST["email"], $_POST["password"], $_POST["csrf"])) {
    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && ctype_alnum($_POST["password"]) && ctype_alnum($_POST["csrf"]) && strlen($_POST["password"]) === 128 && strlen($_POST["csrf"]) === 32 && $_POST["csrf"] === $_SESSION["csrf"]) {
        require_once '../inc/conn.php';
        $select = $conn->prepare("SELECT id, password, totp_enabled, totp_secret, rsa_public FROM users WHERE email = ?");
        $select->bindParam(1, $_POST["email"], PDO::PARAM_STR);
        $select->execute();
        $select = $select->fetch();
        if (!empty($select)) {
            if (password_verify($_POST["password"], $select["password"])) {
                require_once '../inc/totp/Totp.php';
                $totp = new Totp;
                if ($select["totp_enabled"] === 1) {
                    if (isset($_POST["totp_code"]) && !empty($_POST["totp_code"]) && is_numeric(trim($_POST["totp_code"]))) {
                        if ($totp->checkCode($select["totp_secret"], trim($_POST["totp_code"]))) {
                            $_SESSION["id"] = $select["id"];
                            echo json_encode(["success" => true, "type" => "success", "rsa_public" => $select["rsa_public"], "message" => ""]);
                        } else {
                            echo json_encode(["success" => false, "type" => "failed", "message" => "TOTP code is invalid"]);
                        }
                    } else {
                        echo json_encode(["success" => false, "type" => "totp_required", "message" => "TOTP code is required"]);
                    }
                } else {
                    $_SESSION["id"] = $select["id"];
                    echo json_encode(["success" => true, "rsa_public" => null, "message" => ""]);
                }
            } else {
                echo json_encode(["success" => false, "type" => "failed_login", "message" => "Invalid password"]);
            }
        } else {
            echo json_encode(["success" => false, "type" => "failed_login", "message" => "Invalid email or password"]);
        }
    } else {
        echo json_encode(["success" => false, "type" => "failed_login", "message" => "Invalid email or password"]);
    }
}
?>