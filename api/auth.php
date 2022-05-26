<?php
session_start();
if (isset($_POST["csrf"]) && ctype_alnum($_POST["csrf"]) && strlen($_POST["csrf"]) === 32 && $_POST["csrf"] === $_SESSION["csrf"]) {
    if (isset($_POST["type"])) {
        switch($_POST["type"]) {
            case "register":
                if (isset($_POST["email"], $_POST["password"], $_POST["g_recaptcha_response"])) {
                    require_once '../inc/conn.php';
                    require_once '../inc/config.php';
                    // filter user input
                    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && ctype_alnum($_POST["password"]) && strlen($_POST["password"]) === 128 && !empty($_POST["g_recaptcha_response"])) {
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
                break;
            case "login":
            default:
                if (isset($_POST["email"], $_POST["password"])) {
                    if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && ctype_alnum($_POST["password"]) && strlen($_POST["password"]) === 128) {
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
                break;
        }
    }
} else {
    echo json_encode(["success" => false, "type" => "invalid_csrf", "message" => "Invalid CSRF"]);
}
?>