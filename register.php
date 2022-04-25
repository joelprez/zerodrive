<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroDrive - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-200 flex flex-col justify-center items-center h-screen">
    <h1 class="text-6xl font-extrabold mb-4"><span class="text-blue-600">Zero</span><span class="text-white">Drive</span></h1>
    <div class="mr-0.5 w-96 rounded overflow-hidden shadow-lg bg-white p-6 text-center">
        <input name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none focus:shadow-outline my-1" placeholder="example@email.com">
        <input name="password" type="password" class="shadow appearance-none border rounded text-sm w-full py-2 px-3 focus:outline-none focus:shadow-outline mt-1 mb-1" placeholder="●●●●●●●●●●">
        <meter max="4" id="password-strength" class="mb-1"></meter>
        <input name="password2" type="password" class="shadow appearance-none border rounded w-full text-sm py-2 px-3 focus:outline-none focus:shadow-outline my-1 mb-4" placeholder="●●●●●●●●●●">
        <button id="register-btn" class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 hover:border-blue-500 rounded my-1.5" disabled>Register</button>
    </div>
    
    <div id="toast" style="display: none;" class="flex items-center w-full max-w-xs p-4 text-white bg-red-400 rounded absolute top-5 right-5" role="alert">
        <div class="text-sm font-normal">EXAMPLE TEXT</div>
    </div>
    <script defer src="assets/js/register.js"></script>
</body>
</html>