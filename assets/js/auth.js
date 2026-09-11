'use strict';
const password = document.querySelector('#password');
const meter = document.querySelector('#password-meter');
const strength = document.querySelector('#password-strength');
const locationFields = document.querySelector('.location-fields');

function hidePassword(input, button) {
    input.type = 'password';
    button.setAttribute('aria-pressed', 'false');
    button.setAttribute('aria-label', 'Hold to show password');
}

function showPassword(input, button) {
    input.type = 'text';
    button.setAttribute('aria-pressed', 'true');
    button.setAttribute('aria-label', 'Release to hide password');
}

document.querySelectorAll('input[type="password"]').forEach((input) => {
    const control = document.createElement('span');
    control.className = 'password-control';
    input.parentNode.insertBefore(control, input);
    control.appendChild(input);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'password-eye';
    button.setAttribute('aria-label', 'Hold to show password');
    button.setAttribute('aria-pressed', 'false');
    button.innerHTML = '<span aria-hidden="true">&#128065;</span>';
    control.appendChild(button);

    button.addEventListener('pointerdown', (event) => {
        event.preventDefault();
        button.setPointerCapture?.(event.pointerId);
        showPassword(input, button);
    });
    ['pointerup', 'pointercancel', 'lostpointercapture', 'pointerleave'].forEach((name) => {
        button.addEventListener(name, () => hidePassword(input, button));
    });
    button.addEventListener('keydown', (event) => {
        if (event.key === ' ' || event.key === 'Enter') {
            event.preventDefault();
            showPassword(input, button);
        }
    });
    button.addEventListener('keyup', (event) => {
        if (event.key === ' ' || event.key === 'Enter') hidePassword(input, button);
    });
    button.addEventListener('blur', () => hidePassword(input, button));
    button.addEventListener('click', (event) => event.preventDefault());
});
function assessPassword() {
    const value = password.value;
    const score = [value.length >= 8, /[a-z]/.test(value), /[A-Z]/.test(value), /\d/.test(value)].filter(Boolean).length;
    meter.value = score;
    strength.textContent = score === 4 ? 'Strong password.' : 'Use 8+ characters, uppercase, lowercase and a number.';
}
function updateLocations() {
    const selected = document.querySelector('[name="location_mode"]:checked');
    locationFields.hidden = !selected || selected.value !== 'custom';
}
password?.addEventListener('input', assessPassword);
document.querySelectorAll('[name="location_mode"]').forEach((radio) => radio.addEventListener('change', updateLocations));
updateLocations();
