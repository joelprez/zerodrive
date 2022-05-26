let message = document.getElementById("message");
let email = document.querySelector('[name="email"]');
let password = document.querySelector('[name="password"]');
let loginBtn = document.getElementById("login-btn");
let passwordHash = document.getElementById("password-hash");
let oldPassword = "";

let totp = false;

loginBtn.onclick = function() {
    message.classList.remove("hidden");
    if (email.value.match(validEmail)) {
        if (password.value !== "") {            
            if (oldPassword !== password.value) {
                message.classList.remove("bg-red-600");
                message.classList.remove("bg-blue-400");
                message.classList.add("bg-gray-400");
                message.innerHTML = 'Logging in <div class="lds-facebook"><div></div><div></div><div></div></div>';
                
                passwordHash.value = hash_password(password.value);
                oldPassword = password.value;
            }

            setTimeout(function() {
                // message.classList.remove("bg-red-600");
                // message.classList.remove("bg-blue-400");
                // send data to API
                values = {
                    type: "login",
                    email: email.value,
                    password: passwordHash.value,
                    csrf: csrf.value
                }

                if (totp) {
                    values.totp_code = $("#totp_code").val();
                }

                $.post("api/auth.php", values, function(result) {
                    let output = JSON.parse(result);
                    if (output.success) {
                        loginBtn.disabled = true;
                        message.classList.remove("bg-red-600");
                        message.classList.remove("bg-gray-400");
                        message.classList.add("bg-green-600");
                        message.classList.remove("bg-blue-400");
                        message.innerText = "Logged in, redirecting in 3 seconds";
                        
                        // check localStorage database
                        if (localStorage.getItem("keys") !== null) {
                            var userKeys = JSON.parse(localStorage.getItem("keys"));
                        } else {
                            var userKeys = new Object;
                        }

                        if (userKeys[email.value] == null) {
                            // email is not saved, add keys
                            userKeys[email.value] = new Object;
                            let tmpPasswdPBKDF2 = CryptoJS.PBKDF2(password.value, salt, { keySize: 512 / 16, iterations: 20000 }).toString()

                            if (output.rsa_public !== null) {
                                userKeys[email.value].rsa_public = output.rsa_public;
                                userKeys[email.value].rsa_private = cryptico.privateKeyString(cryptico.generateRSAKey(tmpPasswdPBKDF2, bits));
                            } else {
                                userKeys[email.value].password = tmpPasswdPBKDF2;
                                userKeys[email.value].rsa_public = "";
                                userKeys[email.value].rsa_private = "";
                            }
                        }

                        localStorage.setItem("keys", JSON.stringify(userKeys));
                        localStorage.setItem("current_user", email.value)


                        setTimeout(function() {
                            window.location.href = "index.php";
                        }, 3000);
                    } else if (output.type === "totp_required") {
                        // show TOTP required, add third input for TOTP code
                        message.classList.remove("bg-red-600");
                        message.classList.remove("bg-gray-400");
                        message.classList.add("bg-blue-400");
                        message.innerText = output.message;
                        $("#inputs").append($('<input id="totp_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-sm text-gray-700 focus:outline-none focus:shadow-outline my-1" placeholder="TOTP code: e.g 810300" autocomplete="off">'));
                        email.style.display = "none";
                        password.style.display = "none";
                        totp = true;
                    } else {
                        message.classList.remove("bg-green-600");
                        message.classList.remove("bg-gray-400");
                        message.classList.remove("bg-blue-400");
                        message.classList.add("bg-red-600");
                        message.innerText = output.message;
                    }
                });
            }, 100);
        } else {
            message.classList.remove("bg-gray-400");
            message.classList.remove("bg-green-600");
            message.classList.add("bg-red-600");
            message.innerText = "Password can't be blank";
        }
    } else {
        message.classList.remove("bg-gray-400");
        message.classList.remove("bg-green-600");
        message.classList.add("bg-red-600");
        message.innerText = "Invalid email address";
    }
}