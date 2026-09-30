# Practical 5: Registration Form with Frontend Validation and User-Friendly Error Handling

## 📌 Problem Definition
Create a student registration form with HTML5 input types and JavaScript validation for name, email, mobile number, password, confirm password, course, year, gender, and terms acceptance. Use Regular Expression for validations.

---

## 🎯 Course Outcomes & Learning Objectives
- **CO Mapping**: CO1 (Web Form Design & Structure), CO3 (Client-side Scripting & RegEx Validation)
- **Learning Outcome**: Students will design accessible forms with robust client-side validation, instant UX error feedback, password strength evaluation, and security CAPTCHA verification.

---

## 🛠️ Key Technical Features Implemented

1. **HTML5 & Accessible Structure**:
   - Semantic HTML elements (`<header>`, `<main>`, `<section>`, `<fieldset>`, `<legend>`, `<footer>`).
   - Accessible ARIA attributes (`aria-invalid`, `aria-describedby`, `aria-live="polite"`, `role="alert"`).
   - Keyboard accessible navigation and high contrast design matching **CHARUSAT Student Hub** (`#003366` Navy Blue & `#ff9800` Accent Orange).

2. **Regular Expression (RegEx) Validation Engine**:
   - **Full Name**: `/^[A-Za-z\s]{3,50}$/` (Alphabetical letters and spaces, 3-50 characters).
   - **Email Address**: `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/` (Valid RFC email structure).
   - **Mobile Number**: `/^[6-9]\d{9}$/` (10-digit Indian phone number starting with 6, 7, 8, or 9).
   - **Password Security**: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/` (Min 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 special character).

3. **Password Strength Meter & UX Helpers**:
   - Real-time password strength score calculation based on character set diversity.
   - Dynamic colored bar transition (Red for Weak, Yellow for Medium, Green for Strong).
   - Password Show/Hide toggle button for password & confirm password fields.

4. **Real-time Event Validation (Intermediate Extension)**:
   - Attached `input`, `change`, and `blur` event listeners to give instant visual feedback before form submission.

5. **Custom HTML5 Canvas CAPTCHA (Advanced Extension)**:
   - Dynamic 6-character random alphanumeric CAPTCHA drawn on HTML5 `<canvas>`.
   - Rotated characters, random background lines, and dot noise to prevent bot scraping.
   - One-click refresh button and case-insensitive user verification.

6. **Submission Confirmation Summary**:
   - Form submission prevents default page reloads and displays a complete registration details card upon validation success.

---

## 🧪 Validation Test Cases Matrix

| Test ID | Input Field | Test Scenario | RegEx / Logic | Expected Output | Status |
|---|---|---|---|---|---|
| `TC-01` | Full Name | `"John123"` | `/^[A-Za-z\s]{3,50}$/` | Error: Alphabets only | PASS |
| `TC-02` | Full Name | `"Rahul Sharma"` | `/^[A-Za-z\s]{3,50}$/` | Field Validated Green | PASS |
| `TC-03` | Email | `"student@com"` | `/^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/` | Error: Invalid Email Format | PASS |
| `TC-04` | Email | `"rahul.ce24@charusat.edu.in"` | Standard RegEx | Field Validated Green | PASS |
| `TC-05` | Phone | `"5432109876"` | `/^[6-9]\d{9}$/` | Error: Must start with 6-9 | PASS |
| `TC-06` | Phone | `"9876543210"` | `/^[6-9]\d{9}$/` | Field Validated Green | PASS |
| `TC-07` | Password | `"abc123"` | Complex RegEx | Meter Red / Weak | PASS |
| `TC-08` | Password | `"Charusat@2026"` | Complex RegEx | Meter Green / Strong | PASS |
| `TC-09` | Confirm Pass | Mismatched string | `confirm === password` | Error: Passwords do not match | PASS |
| `TC-10` | CAPTCHA | Wrong text | Canvas Verification | Error: Incorrect CAPTCHA | PASS |
| `TC-11` | Form Submit | All inputs valid | Form Handler | Summary Card Shown | PASS |

---

## 💡 Viva Voce Quick Reference & Q&A

1. **Q: Why is client-side validation essential even if server-side validation exists?**
   - *A*: Client-side validation provides instantaneous user feedback without network latency, reducing server load and bandwidth usage.

2. **Q: How does `aria-describedby` improve accessibility?**
   - *A*: It programmatically associates input fields with their helper text and error message containers, enabling screen readers to speak validation error messages when focused.

3. **Q: What is the benefit of using Regular Expressions in form validation?**
   - *A*: Regular expressions allow precise pattern matching for strings like emails, phone numbers, and password complexity rules in a single concise line of code.

4. **Q: How is the HTML5 Canvas CAPTCHA generated?**
   - *A*: We use the 2D rendering context (`getContext('2d')`) to draw text characters with random rotations, translations, background noise lines, and arc dots.
