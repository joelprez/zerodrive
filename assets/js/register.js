let message = document.getElementById("message");
let email = document.querySelector('[name="email"]');
let password = document.querySelector('[name="password"]');
let meter = document.getElementById('password-strength');
let password2 = document.querySelector(['[name="password2"]']);
let csrf = document.getElementById("csrf");
let registerBtn = document.getElementById("register-btn");

// check if recaptcha is checked
function isCaptchaChecked() {
    return grecaptcha && grecaptcha.getResponse().length !== 0;
}

// hide and show input checks and meter
email.addEventListener('input', function() {
    if (email.value !== "") {
        email.classList.add("input-check-meter");
    } else {
        email.classList.remove("input-check-meter");
        email.classList.remove("input-check-metter-success");
    }
    
    if (email.value.match(validEmail)) {
        $.post("api/emailCheck.php", {
            email: email.value,
            csrf: csrf.value,
        }, function(result) {
            let output = JSON.parse(result);
            if (output.success) {
                email.classList.add("input-check-meter-success");
                email.valid = true;
            } else {
                email.classList.remove("input-check-meter-success");
                email.valid = false;
            }
        });
    } else {
        email.classList.remove("input-check-meter-success");
        email.valid = false;
    }
});

password.addEventListener("input", function() {
    if (password.value !== "") {
        password.classList.remove("mb-1");
        meter.style.display = "block";
    } else {
        password.classList.add("mb-1");
        meter.style.display = "none";
    }

    meter.value = zxcvbn(password.value).score;
})

password2.addEventListener("input", function() {
    if (password2.value !== "") {
        password2.classList.add("input-check-meter");
    } else {
        password2.classList.remove("input-check-meter");
    }

    if (password.value === password2.value && password2.value != "" && meter.value === 4) {
        password2.classList.add("input-check-meter-success");
        password2.valid = true;
    } else {
        password2.classList.remove("input-check-meter-success");
        password2.valid = true;
    }
});

registerBtn.onclick = function() {
    message.classList.remove("hidden");
    if (email.valid && password2.valid && isCaptchaChecked()) {
        // add hash loading animation
        message.classList.add("bg-gray-400");
        message.innerHTML = 'Hashing your password <div class="lds-facebook"><div></div><div></div><div></div></div>';
        // wait 2.5 seconds
        setTimeout(function() {
            // hash password
            let hashed_password = hash_password(password.value);
            // send data to API
            $.post("api/auth.php", {
                type: "register",
                email: email.value,
                password: hashed_password,
                g_recaptcha_response: document.getElementById("g-recaptcha-response").value,
                csrf: csrf.value
            }, function(result) {
                let output = JSON.parse(result);
                if (output.success) {
                    registerBtn.disabled = true;
                    message.classList.remove("bg-red-600");
                    message.classList.remove("bg-gray-400");
                    message.classList.add("bg-green-600");
                    message.innerText = "Account created, redirecting in 3 seconds";
                    setTimeout(function() {
                        window.location.href = "login.php";
                    }, 3000);
                } else {
                    message.classList.remove("bg-green-600");
                    message.classList.add("bg-red-600");
                    message.innerText = output.message;
                }
            });
        }, 2500);
    } else {
        message.classList.remove("bg-gray-400");
        message.classList.remove("bg-green-600");
        message.classList.add("bg-red-600");
        if (!email.valid) {
            message.innerText = "Email is not valid or it's already registered";
        } else if (!password2.valid) {
            message.innerText = "Password is not valid";
        } else if (!isCaptchaChecked()) {
            message.innerText = "Captcha is missing";
        }
        
    }
}