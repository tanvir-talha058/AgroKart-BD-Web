function validateRegistrationForm() {
    let isValid = true;
    document.querySelectorAll('.error-text').forEach(el => el.style.display = 'none');

    const name = document.getElementById('name').value;
    if (name.length < 6) { document.getElementById('name-error').innerText = "Name must be at least 6 characters."; document.getElementById('name-error').style.display = 'block'; isValid = false; }

    const email = document.getElementById('email').value;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { document.getElementById('email-error').innerText = "Invalid email."; document.getElementById('email-error').style.display = 'block'; isValid = false; }

    const phone = document.getElementById('phone').value;
    if (!/^[0-9]{11}$/.test(phone)) { document.getElementById('phone-error').innerText = "Phone must be 11 digits."; document.getElementById('phone-error').style.display = 'block'; isValid = false; }

    const password = document.getElementById('password').value;
    if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(password)) { document.getElementById('password-error').innerText = "Password format is incorrect."; document.getElementById('password-error').style.display = 'block'; isValid = false; } else { document.getElementById('password-error').style.display = 'none'; }

    const confirmPassword = document.getElementById('confirm_password').value;
    if (password !== confirmPassword) { document.getElementById('confirm-password-error').innerText = "Passwords do not match."; document.getElementById('confirm-password-error').style.display = 'block'; isValid = false; }

    return isValid;
}
