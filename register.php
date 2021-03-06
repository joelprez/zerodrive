<?php
session_start();
// redirect if user is already logged in
if (isset($_SESSION["id"]) && is_numeric($_SESSION["id"])) {
    header("Location: index.php");
    die();
}
require_once 'inc/config.php';
// CSRF generation
$_SESSION["csrf"] = md5(uniqid(mt_rand(), true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroDrive - Register</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/loader.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script src="assets/js/crypto-js.min.js"></script>
    <script src="assets/js/cryptico.min.js"></script>
</head>
<body class="bg-gray-200 flex flex-col justify-center items-center h-screen">
    <h1 class="text-6xl font-extrabold mb-4"><span class="text-blue-600">Zero</span><span class="text-white">Drive</span></h1>
    <div class="mr-0.5 w-96 rounded overflow-hidden shadow-lg bg-white p-6 text-center">
        <div class="rounded mb-1.5 p-2 text-white bg-sky-400"><a href="login.php">Login now</a></div>
        <div id="message" class="rounded mb-1.5 p-2 text-white hidden"></div>
        <input name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none focus:shadow-outline my-1" placeholder="example@email.com" autocomplete="off">
        <input name="password" type="password" class="shadow appearance-none border rounded text-sm w-full py-2 px-3 focus:outline-none focus:shadow-outline mt-1 mb-1" placeholder="●●●●●●●●●●">
        <meter max="4" id="password-strength" class="mb-1"></meter>
        <input name="password2" type="password" class="shadow appearance-none border rounded w-full text-sm py-2 px-3 focus:outline-none focus:shadow-outline my-1 mb-4" placeholder="●●●●●●●●●●">
        <input id="csrf" type="hidden" value="<?php echo htmlspecialchars($_SESSION["csrf"]); ?>">
        <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_public); ?>"></div>
        <button id="register-btn" class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 hover:border-blue-500 rounded my-1.5 mt-4">Register</button>
    </div>
    <script src="assets/js/functions.js"></script>
    <script src="assets/js/register.js"></script>
</body>
</html>