function share(filename, email, privateKey) {
    // get user rsa public key from email
    $.post("../api/share.php", {
        email: email,
        fileid: filename
    }, function(result) {
        let file = JSON.parse(result);
        
        // decrypt file data
        let fileFilename = cryptico.decrypt(file.filename, privateKey).plaintext;
        let fileMime = cryptico.decrypt(file.mime, privateKey).plaintext;
        let filePrivateKey = cryptico.decrypt(file.file_aes256cbc_private_key, privateKey).plaintext;
        let fileIv = cryptico.decrypt(file.file_aes256cbc_iv, privateKey).plaintext;
        
        // re-encrypt file data using destination user RSA Public Key
        fileFilename = cryptico.encrypt(fileFilename, file.rsa_public).cipher;
        fileMime = cryptico.encrypt(fileMime, file.rsa_public).cipher;
        filePrivateKey = cryptico.encrypt(filePrivateKey, file.rsa_public).cipher;
        fileIv = cryptico.encrypt(fileIv, file.rsa_public).cipher;

        // POST data to the server
        $.post("../api/share.php", {
            email: email,
            fileid: filename,
            filename: fileFilename,
            mime: fileMime,
            private_key: filePrivateKey,
            iv: fileIv
        }, function(result) {
            if (JSON.parse(result).success) {
                return true;
            } else {
                return false;
            }
        });
    });
}

function deleteFile(filename) {
    $("#" + filename).remove();
    $.post("../api/delete.php", {
        fileid: filename
    });
}

// share modal open and close functions
function closeShareModal() {
    $("#share-modal").addClass("hidden");
    $("#email-share-input").val("");
    $("#share-file-list").html("");
}

$("#share-btn").click(function() {
    $("#share-modal").removeClass("hidden");
    $shareFileList = $("#share-file-list");
    for (let i = 0; i < selectedFiles.length; i++) {
        let filename = selectedFiles[i].innerText;
        $shareFileList.append('<li class="w-full px-4 py-2 border-b border-gray-200 dark:border-gray-600">' + filename + '</li>');
    }

    $("#share-accept-btn").click(function() {
        $email = $("#email-share-input").val();
        for (let i = 0; i < selectedFiles.length; i++) {
            share(selectedFiles[i].id, $email, privateKey);
        }
        closeShareModal();
    })

    $(".share-close-btn").click(function() {
        closeShareModal()
    });
});

// delete modal open and close functions
function closeDeleteModal() {
    $("#delete-modal").addClass("hidden");
    $("#delete-file-list").html("");
}

$("#delete-btn").click(function() {
    $("#delete-modal").removeClass("hidden");
    $deleteFileList = $("#delete-file-list");
    for (let i = 0; i < selectedFiles.length; i++) {
        let filename = selectedFiles[i].innerText;
        $deleteFileList.append('<li class="w-full px-4 py-2 border-b border-gray-200 dark:border-gray-600">' + filename + '</li>');
    }

    $("#delete-accept-btn").click(function() {
        for (let i = 0; i < selectedFiles.length; i++) {
            deleteFile(selectedFiles[i].id);
        }
        closeDeleteModal();
    });

    $("#cancel-delete").click(function() {
        closeDeleteModal();
    });
});

document.getElementById("file").onchange = function(evt) {
    if(!window.FileReader) return; // Browser is not compatible            
    var reader = new FileReader();
    reader.onload = function(evt) {
        let filenamePlaintext = document.querySelector("#file").value.split(/(\\|\/)/g).pop();
        
        let aesPrivateKey = randomString(32);
        let aesPrivateKeyEncrypted = cryptico.encrypt(aesPrivateKey, publicKey).cipher;

        let aesIv = randomString(16);
        let aesIvEncrypted = cryptico.encrypt(aesIv, publicKey).cipher;

        let filenameEncrypted = cryptico.encrypt(filenamePlaintext, publicKey).cipher;
        let mimeEncrypted = cryptico.encrypt(filename_to_mime(filenamePlaintext), publicKey).cipher;

        let cipher = CryptoJS.AES.encrypt(CryptoJS.enc.Latin1.parse(evt.target.result), CryptoJS.enc.Utf8.parse(aesPrivateKey), {
            iv: CryptoJS.enc.Utf8.parse(aesIv), // parse the IV 
            padding: CryptoJS.pad.Pkcs7,
            mode: CryptoJS.mode.CBC
        }).toString();

        $.post("../api/upload.php", {
            filename: filenameEncrypted,
            mime: mimeEncrypted,
            aes_private_key: aesPrivateKeyEncrypted,
            aes_iv: aesIvEncrypted,
            file: cipher
        }, function(result) {
            loadFiles();
        });
    };
    reader.readAsBinaryString(evt.target.files[0]);
};

function inArray(needle, haystack) {
    var count = haystack.length;
    for (var i = 0; i < count; i++) {
        if (haystack[i] === needle) {
            return true;
        }
    }
    return false;
}

function getHTTP(url) {
    const xhr = new XMLHttpRequest()
    xhr.open("GET", url, false);
    xhr.send()
    if (xhr.status === 200) {
        data = JSON.parse(xhr.responseText);
        return data;
    } else {
        error(2);
        return false;
    }
}

function deselectFiles() {
    selectedFiles.forEach(function(archivo) {
        archivo.classList.remove("active");
    });
    selectedFiles = [];
}

function selectFolder(id) {
    // borrar todos las selecciones si no se pulsa ctrl
    if (!window.event.ctrlKey) {
        deselectFiles();
    }

    // deseleccionar archivo
    if (inArray(divArchivo[id], selectedFiles)) {
        let selectedFiles_tmp = [];
        selectedFiles.forEach(function(archivo) {
            if (archivo !== divArchivo[id]) {
                selectedFiles_tmp.push(archivo);
            } else {
                archivo.classList.remove("active");
            }
        });
        selectedFiles = selectedFiles_tmp;
    } else {
        // añadir archivo a seleccion
        divArchivo[id].classList.add("active");
        selectedFiles.push(divArchivo[id]);
    }
}

function navigateFolder(id) {
    window.open("../view.php#" + id, '_blank').focus();
}

const filesContainer = document.getElementById("files");
var divArchivo = [];
var selectedFiles = [];

window.onload = loadFiles;

function loadFiles() {
    filesContainer.innerHTML = "";
    var fileStructure = getHTTP("/zerodrive/dashboard/api.php");
    // const fileStructure = [
    //     //["id", 0/1 (carpeta/archivo), "Nombre", "color"]
    //     // Azul #3f51b5
    //     // Verde #00BB2D
    //     // Naranja #f44611
    //     // Rojo #FF0000
    //     // Lila #C8A2C8
    //     ["facturas", 0, "Facturas", "#3f51b5"],
    //     ["it", 0, "Informatica", "#00bb2d"],
    //     ["pdfs", 0, "PDFs", "#f44611"],
    //     ["contratos", 0, "Contratos", "#ff0000"]
    // ]; 

    fileStructure.forEach(function(archivo) {
        //id como i
        let i = archivo[0];
        divArchivo[i] = document.createElement("div");
        divArchivo[i].setAttribute("id", i);
        divArchivo[i].className = "archivo hover:max-w-none";
        filesContainer.appendChild(divArchivo[i]);
        
        divArchivo[i].onclick = function() {
            if (window.event.ctrlKey) {
                selectFolder(i);
            } else if (inArray(divArchivo[i], selectedFiles)) {
                // segundo click despues de estar seleccionado
                navigateFolder(i);
            } else {
                selectFolder(i);
            }
        }

        // doble click
        divArchivo[i].addEventListener('dblclick', function() {
            navigateFolder(i);
        });
        
        let iconoFolder = document.createElement("i");

            switch (cryptico.decrypt(archivo[3], privateKey).plaintext) {
                // logos ficheros
                case "application/pdf":
                    iconoFolder.classList = "fas fa-file-pdf";
                    iconoFolder.style.color = "#f40f02";
                    break;
                case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                    iconoFolder.classList = "fas fa-file-excel";
                    iconoFolder.style.color = "#1d6f42";
                    break;
                case "application/zip":
                    iconoFolder.classList = "fa-solid fa-file-zipper";
                    iconoFolder.style.color = "#e8e802";
                    break;
                case "audio/mpeg":
                    iconoFolder.classList = "fa-solid fa-file-audio";
                    iconoFolder.style.color = "#8c8cff";
                    break;
                default:
                    iconoFolder.classList = "fas fa-file";
                    iconoFolder.style.color = "#3f51b5";
                    break;
            }
            divArchivo[i].appendChild(iconoFolder);
            let nombre = document.createElement("span");
            nombre.innerText = cryptico.decrypt(archivo[2], privateKey).plaintext;
            nombre.classList = "hover:overflow-visible";
            divArchivo[i].appendChild(nombre);
    });
}