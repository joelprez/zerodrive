let email = document.querySelector('[name="email"]');
let password = document.querySelector('[name="password"]');
let meter = document.getElementById('password-strength');
let password2 = document.querySelector(['[name="password2"]']);
let registerBtn = document.getElementById("register-btn");

// email check regex
let validEmail = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/;

// hide and show input checks and meter
email.addEventListener('input', function() {
    if (email.value !== "") {
        email.classList.add("input-check-meter");
    } else {
        email.classList.remove("input-check-meter");
        email.classList.remove("input-check-metter-success");
    }
    
    if (email.value.match(validEmail)) {
        email.classList.add("input-check-meter-success");
    } else {
        email.classList.remove("input-check-meter-success");
    }
});

password.addEventListener('input', function() {
    if (password.value !== "") {
        password.classList.remove("mb-1");
        meter.style.display = "block";
    } else {
        password.classList.add("mb-1");
        meter.style.display = "none";
    }

    meter.value = zxcvbn(password.value).score;
})

password2.addEventListener('input', function() {
    if (password2.value !== "") {
        password2.classList.add("input-check-meter");
    } else {
        password2.classList.remove("input-check-meter");
    }

    if (password.value === password2.value && password2.value != "" && meter.value === 4) {
        password2.classList.add("input-check-meter-success");
    } else {
        password2.classList.remove("input-check-meter-success");

    }
});

registerBtn.onclick = function() {

    $("#toast").fadeIn("fast", function() { $(this).delay(1000).fadeOut("slow"); });
}