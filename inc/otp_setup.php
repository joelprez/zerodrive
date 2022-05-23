<?php
require_once 'totp/Totp.php';
$totp = new Totp;
$totp_secret = $user["totp_secret"];

if (isset($_POST["totp_code"]) && !empty($_POST["totp_code"]) && is_numeric(trim($_POST["totp_code"]))) {
    if ($totp->checkCode($totp_secret, $_POST["totp_code"])) {
        // TOTP code correct on setup, change status and redirect to next step
        require_once "conn.php";
        $updateStatus = $conn->prepare("UPDATE users SET totp_enabled = 1, status = 2 WHERE id = ?");
        $updateStatus->bindParam(1, $_SESSION['id']);
        $updateStatus->execute();
        header("Location: index.php");
    } else {
        $error = "TOTP code is invalid, try again";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroDrive - TOTP/Account Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/crypto-js.min.js"></script>
    <script src="assets/js/qrcode.min.js"></script>
</head>
<body class="bg-gray-200 flex flex-col justify-center items-center h-screen">
    <div class="grid grid-cols-2 mb-4">
        <div><h1 class="text-6xl font-extrabold"><span class="text-blue-600">Zero</span><span class="text-white">Drive</span></h1></div>
        <div class="m-auto"><a class="mb-4 bg-gray-300 p-2 rounded" href="logout.php">Logout</a></div>
    </div>
    <div class="mr-0.5 w-2/5 rounded overflow-hidden shadow-lg bg-white p-6 text-left">
        <div class="grid grid-cols-2">
            <div class="mx-auto blur-sm hover:blur-none" id="qrcode"></div>
            <div>
                <h1 class="text-2xl mb-2 font-bold">Welcome to ZeroDrive!</h1>
                <p class="text-xl mb-2">Before you continue with the register:</p>
                <p class="text-xl mb-2"><b>Enable TOTP</b> using an app like <b>Google Authenticator</b>.</p>
                <p class="text-xl mb-2">Then <b>introduce the code</b> in the <b>box below</b>.</p>
                <p class="text-normal">This is enabled by default to protect your account from unauthorized access.</p>
            </div>
        </div>
    </div>
    <form method="POST" class="mt-2 mr-0.5 w-2/5 rounded overflow-hidden shadow-lg bg-white p-6 text-center">
        <input name="totp_code" type="number" class="bg-gray-200 text-gray-600 focus:outline-none focus:shadow-outline p-2 font-bold" placeholder="TOTP CODE: e.g. 701914">
        <button type="submit" class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 hover:border-blue-500 rounded my-1.5 mt-4 ml-4">Continue</button>
    </form>
    <?php
    if (isset($error)) {
        echo '<div class="mt-2 mr-0.5 w-2/5 rounded overflow-hidden shadow-lg bg-red-400 text-white p-6 text-lg text-center">' . htmlspecialchars($error) . '</div>';
    }
    ?>
    <script>
        new QRCode(document.getElementById("qrcode"), "otpauth://totp/ZeroDrive: <?php echo htmlspecialchars($user["email"]); ?>?secret=<?php echo htmlspecialchars($totp_secret); ?>");
    </script>
</body>
</html>