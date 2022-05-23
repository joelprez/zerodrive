<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroDrive - Key Generation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/crypto-js.min.js"></script>
    <script src="assets/js/cryptico.min.js"></script>
</head>
<body class="bg-gray-200 flex flex-col justify-center items-center h-screen">
    <div class="grid grid-cols-2 mb-4">
            <div><h1 class="text-6xl font-extrabold"><span class="text-blue-600">Zero</span><span class="text-white">Drive</span></h1></div>
            <div class="m-auto"><a class="mb-4 bg-gray-300 p-2 rounded" href="logout.php">Logout</a></div>
        </div>
        <div class="mr-0.5 w-2/5 rounded overflow-hidden shadow-lg bg-white p-6 text-left">
            <div class="grid grid-cols-2">
                <div>
                    <img src="https://cdn.dribbble.com/users/720114/screenshots/2120614/media/39c24d999b984689d6568c961b325ff3.gif">
                </div>
                <div class="ml-6 text-center">
                    <h1 class="text-xl font-bold mb-4">Generating RSA 2048-bit keys</h1>
                    <p class="text-left">Your RSA 2048-bit keys are being generated right now, this process may take a while...</p>
                    <p class="text-left mt-2 mb-6">Once your keys are generated you will be automatically redirected to the Dashboard</p>
                    <div class="flex justify-center">
                        <img width="50px" src="https://c.tenor.com/I6kN-6X7nhAAAAAj/loading-buffering.gif">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        setTimeout(() => {
            let currentUser = localStorage.getItem("current_user");
            let userKeys = JSON.parse(localStorage.getItem("keys"));
            
            let bits = 2048;

            let privateKey = cryptico.generateRSAKey(userKeys[currentUser].password, bits);
            let publicKey = cryptico.publicKeyString(privateKey);

            userKeys[currentUser].rsa_public = publicKey;
            userKeys[currentUser].rsa_private = cryptico.privateKeyString(privateKey);

            delete userKeys[currentUser].password;

            localStorage.setItem("keys", JSON.stringify(userKeys));
            
            $.post("api/saveKey.php", {rsa_public: publicKey});
            location.reload();   
        }, 5000);
    </script>
</body>
</html>