/**
 * CHARUSAT Student Hub - Practical 5 JavaScript
 * Frontend Validation, Password Strength Meter, Canvas CAPTCHA, & Accessibility
 */

document.addEventListener("DOMContentLoaded", () => {
    // Form Elements
    const form = document.getElementById("studentRegisterForm");
    const nameInput = document.getElementById("reg-name");
    const emailInput = document.getElementById("reg-email");
    const phoneInput = document.getElementById("reg-phone");
    const courseSelect = document.getElementById("reg-course");
    const passwordInput = document.getElementById("reg-password");
    const confirmPasswordInput = document.getElementById("reg-confirm-password");
    const termsCheckbox = document.getElementById("reg-terms");
    const captchaInput = document.getElementById("reg-captcha-input");

    // Password Toggle & Meter
    const togglePasswordBtn = document.getElementById("togglePasswordBtn");
    const toggleConfirmBtn = document.getElementById("toggleConfirmBtn");
    const strengthBarFill = document.getElementById("strengthBarFill");
    const strengthText = document.getElementById("strengthText");

    // CAPTCHA Elements
    const captchaCanvas = document.getElementById("captchaCanvas");
    const refreshCaptchaBtn = document.getElementById("btnRefreshCaptcha");
    let generatedCaptcha = "";

    // Notification Banners
    const alertBanner = document.getElementById("alertBanner");
    const summaryCard = document.getElementById("summaryCard");
    const summaryDetails = document.getElementById("summaryDetails");

    // Regular Expression Definitions
    const REGEX_PATTERNS = {
        name: /^[A-Za-z\s]{3,50}$/,
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        phone: /^[6-9]\d{9}$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/
    };

    // =========================================================================
    // 1. CANVAS CAPTCHA GENERATOR (Advanced Extension)
    // =========================================================================
    function generateCaptcha() {
        if (!captchaCanvas) return;
        const ctx = captchaCanvas.getContext("2d");
        const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
        generatedCaptcha = "";

        // Reset canvas
        ctx.clearRect(0, 0, captchaCanvas.width, captchaCanvas.height);

        // Background noise lines
        ctx.fillStyle = "#f1f5f9";
        ctx.fillRect(0, 0, captchaCanvas.width, captchaCanvas.height);

        for (let i = 0; i < 6; i++) {
            ctx.strokeStyle = `rgba(${Math.floor(Math.random() * 200)}, ${Math.floor(Math.random() * 200)}, ${Math.floor(Math.random() * 200)}, 0.4)`;
            ctx.beginPath();
            ctx.moveTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.lineTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.stroke();
        }

        // Generate 6 random characters
        for (let i = 0; i < 6; i++) {
            const char = chars.charAt(Math.floor(Math.random() * chars.length));
            generatedCaptcha += char;
        }

        // Draw distorted text
        ctx.font = "bold 24px Arial";
        ctx.textBaseline = "middle";

        for (let i = 0; i < generatedCaptcha.length; i++) {
            const char = generatedCaptcha[i];
            const x = 20 + i * 22;
            const y = 20 + Math.random() * 6 - 3;
            const angle = (Math.random() - 0.5) * 0.4;

            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.fillStyle = "#003366";
            ctx.fillText(char, 0, 0);
            ctx.restore();
        }

        // Add random dots noise
        for (let i = 0; i < 30; i++) {
            ctx.fillStyle = `rgba(0, 51, 102, ${Math.random()})`;
            ctx.beginPath();
            ctx.arc(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height, 1.5, 0, Math.PI * 2);
            ctx.fill();
        }

        captchaInput.value = "";
        clearFieldError(captchaInput);
    }

    if (refreshCaptchaBtn) {
        refreshCaptchaBtn.addEventListener("click", (e) => {
            e.preventDefault();
            generateCaptcha();
        });
    }

    // Initialize CAPTCHA on load
    generateCaptcha();

    // =========================================================================
    // 2. PASSWORD STRENGTH METER LOGIC
    // =========================================================================
    function checkPasswordStrength(password) {
        let score = 0;
        if (!password) return { score: 0, text: "", class: "" };

        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;

        if (score <= 2) {
            return { score: 1, text: "Weak (Requires upper, lower, number, special char)", class: "strength-weak text-weak" };
        } else if (score === 3 || score === 4) {
            return { score: 2, text: "Medium (Almost there)", class: "strength-medium text-medium" };
        } else {
            return { score: 3, text: "Strong Password ✓", class: "strength-strong text-strong" };
        }
    }

    function updatePasswordMeter() {
        const val = passwordInput.value;
        const result = checkPasswordStrength(val);

        strengthBarFill.className = "strength-bar-fill " + (result.class.split(" ")[0] || "");
        strengthText.className = "strength-text " + (result.class.split(" ")[1] || "");
        strengthText.textContent = val ? result.text : "";
    }

    if (passwordInput) {
        passwordInput.addEventListener("input", () => {
            updatePasswordMeter();
            validatePassword();
            if (confirmPasswordInput.value) {
                validateConfirmPassword();
            }
        });
    }

    // Password Toggle Visibility
    function setupPasswordToggle(btn, inputField) {
        if (!btn || !inputField) return;
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            const type = inputField.getAttribute("type") === "password" ? "text" : "password";
            inputField.setAttribute("type", type);
            btn.textContent = type === "password" ? "Show" : "Hide";
            btn.setAttribute("aria-label", type === "password" ? "Show password text" : "Hide password text");
        });
    }

    setupPasswordToggle(togglePasswordBtn, passwordInput);
    setupPasswordToggle(toggleConfirmBtn, confirmPasswordInput);

    // =========================================================================
    // 3. FIELD VALIDATION HELPERS (Inline Accessibility & UX)
    // =========================================================================
    function setFieldError(input, message) {
        const formGroup = input.closest(".form-group");
        if (!formGroup) return;

        formGroup.classList.remove("is-valid");
        formGroup.classList.add("is-invalid");

        input.setAttribute("aria-invalid", "true");

        let errorEl = formGroup.querySelector(".error-message");
        if (errorEl) {
            errorEl.textContent = "⚠️ " + message;
            errorEl.style.display = "flex";
        }
    }

    function setFieldSuccess(input) {
        const formGroup = input.closest(".form-group");
        if (!formGroup) return;

        formGroup.classList.remove("is-invalid");
        formGroup.classList.add("is-valid");

        input.setAttribute("aria-invalid", "false");

        let errorEl = formGroup.querySelector(".error-message");
        if (errorEl) {
            errorEl.style.display = "none";
        }
    }

    function clearFieldError(input) {
        const formGroup = input.closest(".form-group");
        if (!formGroup) return;

        formGroup.classList.remove("is-invalid", "is-valid");
        input.setAttribute("aria-invalid", "false");

        let errorEl = formGroup.querySelector(".error-message");
        if (errorEl) {
            errorEl.style.display = "none";
        }
    }

    // Individual Field Validators
    function validateName() {
        const val = nameInput.value.trim();
        if (!val) {
            setFieldError(nameInput, "Full Name is required.");
            return false;
        }
        if (!REGEX_PATTERNS.name.test(val)) {
            setFieldError(nameInput, "Name must contain only alphabets and spaces (3-50 characters).");
            return false;
        }
        setFieldSuccess(nameInput);
        return true;
    }

    function validateEmail() {
        const val = emailInput.value.trim();
        if (!val) {
            setFieldError(emailInput, "Email address is required.");
            return false;
        }
        if (!REGEX_PATTERNS.email.test(val)) {
            setFieldError(emailInput, "Please enter a valid email format (e.g., student@charusat.edu.in).");
            return false;
        }
        setFieldSuccess(emailInput);
        return true;
    }

    function validatePhone() {
        const val = phoneInput.value.trim();
        if (!val) {
            setFieldError(phoneInput, "Mobile number is required.");
            return false;
        }
        if (!REGEX_PATTERNS.phone.test(val)) {
            setFieldError(phoneInput, "Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.");
            return false;
        }
        setFieldSuccess(phoneInput);
        return true;
    }

    function validateCourse() {
        const val = courseSelect.value;
        if (!val) {
            setFieldError(courseSelect, "Please select your enrolled course.");
            return false;
        }
        setFieldSuccess(courseSelect);
        return true;
    }

    function validateYear() {
        const selectedYear = document.querySelector('input[name="year"]:checked');
        const yearGroup = document.getElementById("yearGroup");
        const errorEl = yearGroup.querySelector(".error-message");

        if (!selectedYear) {
            yearGroup.classList.add("is-invalid");
            yearGroup.classList.remove("is-valid");
            if (errorEl) {
                errorEl.textContent = "⚠️ Please select your current academic year.";
                errorEl.style.display = "flex";
            }
            return false;
        } else {
            yearGroup.classList.remove("is-invalid");
            yearGroup.classList.add("is-valid");
            if (errorEl) errorEl.style.display = "none";
            return true;
        }
    }

    function validateGender() {
        const selectedGender = document.querySelector('input[name="gender"]:checked');
        const genderGroup = document.getElementById("genderGroup");
        const errorEl = genderGroup.querySelector(".error-message");

        if (!selectedGender) {
            genderGroup.classList.add("is-invalid");
            genderGroup.classList.remove("is-valid");
            if (errorEl) {
                errorEl.textContent = "⚠️ Please select your gender.";
                errorEl.style.display = "flex";
            }
            return false;
        } else {
            genderGroup.classList.remove("is-invalid");
            genderGroup.classList.add("is-valid");
            if (errorEl) errorEl.style.display = "none";
            return true;
        }
    }

    function validatePassword() {
        const val = passwordInput.value;
        if (!val) {
            setFieldError(passwordInput, "Password is required.");
            return false;
        }
        if (!REGEX_PATTERNS.password.test(val)) {
            setFieldError(passwordInput, "Password must be at least 8 chars with upper, lower, number, and special character.");
            return false;
        }
        setFieldSuccess(passwordInput);
        return true;
    }

    function validateConfirmPassword() {
        const passVal = passwordInput.value;
        const confirmVal = confirmPasswordInput.value;

        if (!confirmVal) {
            setFieldError(confirmPasswordInput, "Please confirm your password.");
            return false;
        }
        if (confirmVal !== passVal) {
            setFieldError(confirmPasswordInput, "Passwords do not match.");
            return false;
        }
        setFieldSuccess(confirmPasswordInput);
        return true;
    }

    function validateTerms() {
        const termsGroup = document.getElementById("termsGroup");
        const errorEl = termsGroup.querySelector(".error-message");

        if (!termsCheckbox.checked) {
            termsGroup.classList.add("is-invalid");
            termsGroup.classList.remove("is-valid");
            if (errorEl) {
                errorEl.textContent = "⚠️ You must accept the terms and conditions to proceed.";
                errorEl.style.display = "flex";
            }
            return false;
        } else {
            termsGroup.classList.remove("is-invalid");
            termsGroup.classList.add("is-valid");
            if (errorEl) errorEl.style.display = "none";
            return true;
        }
    }

    function validateCaptcha() {
        const userVal = captchaInput.value.trim();
        if (!userVal) {
            setFieldError(captchaInput, "Please enter the CAPTCHA text.");
            return false;
        }
        if (userVal.toLowerCase() !== generatedCaptcha.toLowerCase()) {
            setFieldError(captchaInput, "Incorrect CAPTCHA text. Please try again.");
            return false;
        }
        setFieldSuccess(captchaInput);
        return true;
    }

    // =========================================================================
    // 4. REAL-TIME EVENT ATTACHMENT (Intermediate Extension)
    // =========================================================================
    nameInput.addEventListener("input", validateName);
    nameInput.addEventListener("blur", validateName);

    emailInput.addEventListener("input", validateEmail);
    emailInput.addEventListener("blur", validateEmail);

    phoneInput.addEventListener("input", validatePhone);
    phoneInput.addEventListener("blur", validatePhone);

    courseSelect.addEventListener("change", validateCourse);

    document.querySelectorAll('input[name="year"]').forEach(radio => {
        radio.addEventListener("change", validateYear);
    });

    document.querySelectorAll('input[name="gender"]').forEach(radio => {
        radio.addEventListener("change", validateGender);
    });

    confirmPasswordInput.addEventListener("input", validateConfirmPassword);
    confirmPasswordInput.addEventListener("blur", validateConfirmPassword);

    termsCheckbox.addEventListener("change", validateTerms);
    captchaInput.addEventListener("input", validateCaptcha);

    // =========================================================================
    // 5. FORM SUBMISSION HANDLER
    // =========================================================================
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        // Perform validation across all fields
        const isNameValid = validateName();
        const isEmailValid = validateEmail();
        const isPhoneValid = validatePhone();
        const isCourseValid = validateCourse();
        const isYearValid = validateYear();
        const isGenderValid = validateGender();
        const isPasswordValid = validatePassword();
        const isConfirmValid = validateConfirmPassword();
        const isTermsValid = validateTerms();
        const isCaptchaValid = validateCaptcha();

        const isFormValid = isNameValid && isEmailValid && isPhoneValid && isCourseValid &&
            isYearValid && isGenderValid && isPasswordValid && isConfirmValid &&
            isTermsValid && isCaptchaValid;

        if (isFormValid) {
            // Display Success Notification
            alertBanner.className = "alert-banner alert-success";
            alertBanner.innerHTML = "<strong>✅ Success!</strong> Student Registration completed successfully. Verification link sent to email.";
            alertBanner.style.display = "block";
            alertBanner.scrollIntoView({ behavior: "smooth", block: "center" });

            // Extract Values for Summary Card
            const selectedYear = document.querySelector('input[name="year"]:checked').value;
            const selectedGender = document.querySelector('input[name="gender"]:checked').value;

            summaryDetails.innerHTML = `
                <div class="summary-item">
                    <strong>Student Name:</strong>
                    <span>${escapeHTML(nameInput.value.trim())}</span>
                </div>
                <div class="summary-item">
                    <strong>Email Address:</strong>
                    <span>${escapeHTML(emailInput.value.trim())}</span>
                </div>
                <div class="summary-item">
                    <strong>Mobile Number:</strong>
                    <span>+91 ${escapeHTML(phoneInput.value.trim())}</span>
                </div>
                <div class="summary-item">
                    <strong>Enrolled Course:</strong>
                    <span>${escapeHTML(courseSelect.options[courseSelect.selectedIndex].text)}</span>
                </div>
                <div class="summary-item">
                    <strong>Academic Year:</strong>
                    <span>${escapeHTML(selectedYear)}</span>
                </div>
                <div class="summary-item">
                    <strong>Gender:</strong>
                    <span>${escapeHTML(selectedGender)}</span>
                </div>
                <div class="summary-item">
                    <strong>Registration Status:</strong>
                    <span style="color:#059669; font-weight:bold;">Verified & Active</span>
                </div>
            `;
            summaryCard.style.display = "block";

            // Generate fresh captcha for security
            generateCaptcha();

        } else {
            // Display Error Notification
            alertBanner.className = "alert-banner alert-danger";
            alertBanner.innerHTML = "<strong>❌ Submission Failed!</strong> Please review and correct the errors highlighted in red below.";
            alertBanner.style.display = "block";
            summaryCard.style.display = "none";

            // Focus on first invalid input element
            const firstInvalid = form.querySelector(".is-invalid input, .is-invalid select");
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        }
    });

    // Reset handler
    form.addEventListener("reset", () => {
        setTimeout(() => {
            document.querySelectorAll(".form-group, #yearGroup, #genderGroup, #termsGroup").forEach(group => {
                group.classList.remove("is-invalid", "is-valid");
                const err = group.querySelector(".error-message");
                if (err) err.style.display = "none";
            });
            alertBanner.style.display = "none";
            summaryCard.style.display = "none";
            strengthBarFill.className = "strength-bar-fill";
            strengthText.textContent = "";
            generateCaptcha();
        }, 10);
    });

    // Utility: HTML Escaping
    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g,
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }
});
