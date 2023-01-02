"use strict";
const _ = (selector, base = document) => {
	let elements = base.querySelectorAll(selector);
	return (elements.length == 1) ? elements[0] : elements;
}
let passwordInput = _('#password-input input[type="password"]');
let capslockOn = _('#capslock-on');
capslockOn.style.display = "none";
let passwordStrength = _('#password-strength');
let poor = _('#password-strength #poor');
let weak = _('#password-strength #weak');
let strong = _('#password-strength #strong');
let passwordInfo = _('#password-info');

let poorRegExp = /[a-z]/;
let weakRegExp = /(?=.*?[0-9])/;
let strongRegExp = /(?=.*?[#?!@$%^&*-;,])/;
let whitespaceRegExp = /^$|\s+/;

/**
 * Eventhandler for the password input field
 */
passwordInput.addEventListener( 'input', e => {

  let passwordValue = passwordInput.value;
  let passwordLength = passwordValue.length;
  let poorPassword = passwordValue.match(poorRegExp);
  let weakPassword = passwordValue.match(weakRegExp);
  let strongPassword = passwordValue.match(strongRegExp);
  let whitespace = passwordValue.match(whitespaceRegExp);

  if (passwordValue != "") {
    passwordStrength.style.display = "block";
    passwordStrength.style.display = "flex";
    passwordInfo.style.display = "block";
    passwordInfo.style.color = "black";
    if (whitespace) {
      passwordInfo.textContent = "whitespaces are not allowed";
    } else {
      poorPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
      weakPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
      strongPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword);
    }

  } else {
    passwordStrength.style.display = "none";
    passwordInfo.style.display = "none";

  }
});

/**
 * 
 * @param {int} passwordLength 
 * @param {regexp} poorPassword 
 * @param {regexp} weakPassword 
 * @param {regexp} strongPassword 
 */
function poorPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword) {
  if (passwordLength <= 3 && (poorPassword || weakPassword || strongPassword)) {
    poor.classList.add("active");
    passwordInfo.style.display = "block";
    passwordInfo.style.color = "red";
    passwordInfo.textContent = "Password ist sehr schwach";

  }
}
function weakPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword) {
  if (passwordLength >= 4 && poorPassword && (weakPassword || strongPassword)) {
    weak.classList.add("active");
    passwordInfo.textContent = "Password ist schwach";
    passwordInfo.style.color = "orange";

  } else {
    weak.classList.remove("active");

  }
}
function strongPasswordStrength(passwordLength, poorPassword, weakPassword, strongPassword) {
  if (passwordLength >= 6 && (poorPassword && weakPassword) && strongPassword) {
    poor.classList.add("active");
    weak.classList.add("active");
    strong.classList.add("active");
    passwordInfo.textContent = "Password ist stark";
    passwordInfo.style.color = "green";
  } else {
    strong.classList.remove("active");

  }
}
let showToggle = _('#password-input #show-toggle');
showToggle.onclick = function () {
  togglePassword()
}
function togglePassword() {
  if (passwordInput.type == "password") {
    passwordInput.type = "text";
    showToggle.textContent = "🔒";
    showToggle.style.color = "green";
  } else {
    passwordInput.type = "password";
    showToggle.textContent = "🔓";
    showToggle.style.color = "red";
  }
}

passwordInput.addEventListener('keyup', event=> {
  if( event.getModifierState("CapsLock") ) {
    capslockOn.style.display = "block";
  } else {
    capslockOn.style.display = "none";
  }
})