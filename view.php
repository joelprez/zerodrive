<?php
session_start();
if (!isset($_SESSION["id"])) {
    session_destroy();
    header("Location: login.php");
    die();
}

if (isset($_GET["raw"])) {
    require "inc/conn.php";
    $select = $conn->prepare("SELECT * FROM files WHERE id = ? AND owner_id = ?");
    $select->bindParam(1, $_GET["id"], PDO::PARAM_INT);
    $select->bindParam(2, $_SESSION["id"], PDO::PARAM_INT);
    $select->execute();
    $select = $select->fetch();

    echo json_encode(["id" => $select["id"], "name" => $select["name"], "mime" => $select["mime"], "file_aes256cbc_private_key" => $select["file_aes256cbc_private_key"], "file_aes256cbc_iv" => $select["file_aes256cbc_iv"], "data" => file_get_contents("files/" . $select["id"])]);
    die();  
}
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/crypto-js.min.js"></script>
<script src="assets/js/cryptico.min.js"></script>
<script src="assets/js/functions.js"></script>
<script>
    $.get("view.php?raw&id=" + window.location.hash.replace("#",''), function(data) {
        data = JSON.parse(data);

        let aes256PrivateKey = cryptico.decrypt(data.file_aes256cbc_private_key, privateKey).plaintext;
        let aes256Iv = cryptico.decrypt(data.file_aes256cbc_iv, privateKey).plaintext;
        let mime = cryptico.decrypt(data.mime, privateKey).plaintext;

        data = CryptoJS.AES.decrypt(data.data, CryptoJS.enc.Latin1.parse(aes256PrivateKey), {
            iv: CryptoJS.enc.Utf8.parse(aes256Iv), // parse the IV 
            padding: CryptoJS.pad.Pkcs7,
            mode: CryptoJS.mode.CBC
        }).toString(CryptoJS.enc.Latin1);

        const byteNumbers = new Array(data.length);
        for (let i = 0; i < data.length; i++) {
            byteNumbers[i] = data.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);

        const blob = new Blob([byteArray], {type: mime});

        (async function() {
            window.location.href = URL.createObjectURL(await blob);
        })();
    });
</script>