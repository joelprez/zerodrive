<?php
session_start();
if (!isset($_SESSION["id"])) {
    session_destroy();
    header("Location: ../login.php");
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZeroDrive - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/crypto-js.min.js"></script>
    <script src="../assets/js/cryptico.min.js"></script>
    <script src="../assets/js/functions.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/mui.min.css">
    <link rel="stylesheet" href="css/fontawesome-6.0.all.min.css">
</head>
<body class="bg-gray-200">
    <div class="text-center p-4 bg-indigo-400 p-4">
        <a><h1 class="text-6xl font-extrabold mb-4"><span class="text-blue-600">Zero</span><span class="text-white">Drive</span></h1></a>
    </div>
    <!-- share modal -->
    <div id="share-modal" tabindex="-1" class="hidden flex justify-center items-center bg-black/10	overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-4xl h-full md:h-auto">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <div class="flex justify-between items-center p-5 rounded-t border-b dark:border-gray-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-white">
                        Share Files
                    </h3>
                    <button type="button" class="share-close-btn text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="large-modal">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>  
                    </button>
                </div>
                <div class="p-6 space-y-6">
                    <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                        Before sending the files we need the email of the recipient:
                    </p>
                    <input id="email-share-input" type="text" class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-purple-500" placeholder="example@example.com">
                    <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                        You are about to share the following files as clones, sensitive information will never be sent to the server and everything will be re-encrypted on the client side using the recipient RSA Public Key
                    </p>
                    <ul id="share-file-list" class="text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></ul>
                </div>
                <div class="flex items-center p-6 space-x-2 rounded-b border-t border-gray-200 dark:border-gray-600">
                    <button id="share-accept-btn" data-modal-toggle="large-modal" type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">I accept</button>
                    <button data-modal-toggle="large-modal" type="button" class="share-close-btn text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Decline</button>
                </div>
            </div>
        </div>
    </div>
    <div class="menu bg-white p-4 text-center flex justify-center">
        <!-- <div class="rounded-full bg-gray-300 cursor-pointer p-4 w-14 text-center text-xl">+</div> -->
        <label class="rounded-full bg-blue-600 cursor-pointer p-4 w-14 text-center text-xl">
            <i class="text-white fa-solid fa-plus"></i>
            <input id="file" class="hidden" multiple="" type="file"/>
        </label>

        <label id="share-btn" class="rounded-full bg-purple-600 cursor-pointer p-4 w-14 text-center text-xl text-white ml-2">
            <i class="fa-solid fa-share-nodes"></i>
        </label>

        <label id="delete-btn" class="rounded-full bg-red-600 cursor-pointer p-4 w-14 text-center text-xl text-white ml-2">
            <i class="fa-solid fa-trash"></i>
        </label>
    </div>
    <!-- end share modal -->
    <!-- delete modal -->
    <div id="delete-modal" tabindex="-1" class="hidden flex justify-center items-center bg-black/10	overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 w-full md:inset-0 h-modal md:h-full">
        <div class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <!-- Heroicon name: outline/exclamation -->
                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete files</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">Are you sure you want to delete these files? The files will be permanently removed. This action cannot be undone.</p>
                </div>
                <ul id="delete-file-list" class="text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></ul>
                </div>
            </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button id="delete-accept-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Delete</button>
            <button id="cancel-delete" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
            </div>
        </div>
        </div>
    </div>
    <div id="files" class="p-4 gap-2.5 flex flex-wrap"></div>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/files.js"></script>
</body>
</html>