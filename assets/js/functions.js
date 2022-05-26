let validEmail = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/;
let salt = "$$$ZeroDrive";
let bits = 2048;

function hash_password(password) {
    let hashed_password = password + salt;
    for (let i = 0; i < 20000; i++) {
        hashed_password = CryptoJS.SHA512(password).toString(CryptoJS.enc.Hex);
    }
    return hashed_password;
}

function filename_to_mime(filename) {
    let mimes = {
        pdf: "application/pdf",
        jpg: "image/jpeg",
        png: "image/png",
        zip: "application/zip",
        mp3: "audio/mpeg",
        txt: "text/plain"
    };

    let selectedMime = mimes[filename.split('.').pop()];
    
    if (selectedMime !== undefined) {
        return selectedMime;
    } else {
        return "application/octet-stream";
    }
}

function randomString(length) {
    let result = "";
    let characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

let currentUser = localStorage.getItem("current_user");
let myKeys = JSON.parse(localStorage.getItem("keys"))[currentUser];
var publicKey = myKeys.rsa_public;
var privateKey = cryptico.privateKeyFromString(myKeys.rsa_private);