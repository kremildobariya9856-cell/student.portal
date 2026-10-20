var namePattern = /^[a-zA-Z ]{3,50}$/;
var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
var mobilePattern = /^[6-9][0-9]{9}$/;
var passwordPattern = /^(?=.*[a-zA-Z])(?=.*[0-9]).{8,}$/;

var form = document.getElementById("regForm");

function showError(id, msg) {
  document.getElementById(id + "Error").textContent = msg;
  document.getElementById(id).classList.add("invalid");
}

function clearError(id) {
  document.getElementById(id + "Error").textContent = "";
  document.getElementById(id).classList.remove("invalid");
}

function checkName() {
  var value = document.getElementById("fullName").value.trim();
  if (!namePattern.test(value)) {
    showError("fullName", "Enter a valid name (letters only, min 3 characters)");
    return false;
  }
  clearError("fullName");
  return true;
}

function checkEmail() {
  var value = document.getElementById("email").value.trim();
  if (!emailPattern.test(value)) {
    showError("email", "Enter a valid email address");
    return false;
  }
  clearError("email");
  return true;
}

function checkMobile() {
  var value = document.getElementById("mobile").value.trim();
  if (!mobilePattern.test(value)) {
    showError("mobile", "Enter a valid 10 digit mobile number");
    return false;
  }
  clearError("mobile");
  return true;
}

function getStrength(value) {
  var score = 0;
  if (value.length >= 8) score++;
  if (/[A-Z]/.test(value)) score++;
  if (/[0-9]/.test(value)) score++;
  if (/[^A-Za-z0-9]/.test(value)) score++;
  if (score <= 1) return "Weak";
  if (score <= 3) return "Medium";
  return "Strong";
}

function checkPassword() {
  var value = document.getElementById("password").value;
  document.getElementById("strengthText").textContent = value ? "Strength: " + getStrength(value) : "";
  if (!passwordPattern.test(value)) {
    showError("password", "Min 8 characters, letters and numbers required");
    return false;
  }
  clearError("password");
  return true;
}

function checkConfirmPassword() {
  var password = document.getElementById("password").value;
  var confirmPassword = document.getElementById("confirmPassword").value;
  if (password !== confirmPassword || confirmPassword === "") {
    showError("confirmPassword", "Passwords do not match");
    return false;
  }
  clearError("confirmPassword");
  return true;
}

function checkCourse() {
  var value = document.getElementById("course").value;
  if (value === "") {
    showError("course", "Please select a course");
    return false;
  }
  clearError("course");
  return true;
}

function checkYear() {
  var value = document.getElementById("year").value;
  if (value === "") {
    showError("year", "Please select a year");
    return false;
  }
  clearError("year");
  return true;
}

function checkGender() {
  var selected = document.querySelector('input[name="gender"]:checked');
  if (!selected) {
    document.getElementById("genderError").textContent = "Please select a gender";
    return false;
  }
  document.getElementById("genderError").textContent = "";
  return true;
}

function checkTerms() {
  var checked = document.getElementById("terms").checked;
  if (!checked) {
    document.getElementById("termsError").textContent = "You must accept the terms";
    return false;
  }
  document.getElementById("termsError").textContent = "";
  return true;
}

document.getElementById("fullName").addEventListener("input", checkName);
document.getElementById("email").addEventListener("input", checkEmail);
document.getElementById("mobile").addEventListener("input", checkMobile);
document.getElementById("password").addEventListener("input", checkPassword);
document.getElementById("confirmPassword").addEventListener("input", checkConfirmPassword);
document.getElementById("course").addEventListener("change", checkCourse);
document.getElementById("year").addEventListener("change", checkYear);

form.addEventListener("submit", function (e) {
  e.preventDefault();

  var validName = checkName();
  var validEmail = checkEmail();
  var validMobile = checkMobile();
  var validPassword = checkPassword();
  var validConfirm = checkConfirmPassword();
  var validCourse = checkCourse();
  var validYear = checkYear();
  var validGender = checkGender();
  var validTerms = checkTerms();

  var allValid = validName && validEmail && validMobile && validPassword && validConfirm && validCourse && validYear && validGender && validTerms;

  if (allValid) {
    document.getElementById("successMsg").textContent = "Registration successful!";
    form.reset();
    document.getElementById("strengthText").textContent = "";
  } else {
    document.getElementById("successMsg").textContent = "";
  }
});
