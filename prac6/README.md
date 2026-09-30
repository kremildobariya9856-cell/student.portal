# Practical 6: Rendering External JSON Data using Fetch API, Search and Filter

## 📌 Problem Definition
Display event lists, student profiles, notices, or FAQs from external JSON files using Fetch API. Implement dynamic rendering, search, filter, sorting, and pagination.

---

## 🎯 Course Outcomes & Learning Objectives
- **CO Mapping**: CO3 (Client-side Data Processing & Filtering), CO4 (Dynamic UI & Modular JavaScript)
- **Learning Outcome**: Students will consume external JSON data using JavaScript Fetch API (`async/await`) and render reusable, paginated frontend views with search and multi-criteria sorting.

---

## 🛠️ Key Technical Features Implemented

1. **Fetch API Engine & Modular Data Loading**:
   - `fetchData(dataType)` using standard `async/await` and Promises.
   - Three independent JSON datasets (`events.json`, `students.json`, `faqs.json`) with **15+ rich records** each.

2. **Array Manipulation & Functional Programming**:
   - `Array.prototype.map()`: Dynamically generates HTML card templates from JSON objects.
   - `Array.prototype.filter()`: Real-time substring search across title, name, description, department, and tags, combined with category selection.
   - `Array.prototype.sort()`: Multi-criteria sorting (A-Z, Z-A, Date, GPA rating).
   - `Array.prototype.slice()`: Page slice extraction for pagination calculation.

3. **Pagination & Page Controls**:
   - Page size selection (6, 9, 12, 15 items per page).
   - Dynamic page number buttons, Next/Prev controls, and page indicator count.

4. **Intermediate Extension (Dependent Dropdowns)**:
   - Dynamic population of **Country $\rightarrow$ State $\rightarrow$ City** selectors based on `locations.json` hierarchy.

5. **Advanced Extension (LocalStorage Caching for Offline Availability)**:
   - Automatically caches fetched JSON responses in `localStorage` under `prac6_cache_${dataType}`.
   - Serves cached payload if the network connection drops or fetch fails, displaying a visual **Offline Cache Status** indicator badge.

---

## 🧪 Evaluation Test Cases Matrix

| Test ID | Feature / Method | Test Scenario | JS Implementation | Status |
|---|---|---|---|---|
| `TC-01` | Fetch API | Load `events.json` feed | `fetch('./events.json')` | PASS |
| `TC-02` | Search Filter | Type `"Hackathon"` | `Array.prototype.filter()` | PASS |
| `TC-03` | Category Filter | Select `"Workshop"` | `Array.prototype.filter()` | PASS |
| `TC-04` | Sorting | Select `"Title (A to Z)"` | `Array.prototype.sort()` | PASS |
| `TC-05` | Pagination | Click Page `2` | `Array.prototype.slice()` | PASS |
| `TC-06` | Page Size | Select `"12 per page"` | `itemsPerPage = 12` | PASS |
| `TC-07` | Dependent Dropdown | Select Country `"India"` | Populates States | PASS |
| `TC-08` | Dependent Dropdown | Select State `"Gujarat"` | Populates Cities | PASS |
| `TC-09` | LocalStorage Cache | Load when offline | `localStorage.getItem()` | PASS |
| `TC-10` | Filter Reset | Click "Clear Filters" | Restore full view | PASS |

---

## 💡 Viva Voce Quick Reference & Q&A

1. **Q: How does the Fetch API work compared to XMLHttpRequest (XHR)?**
   - *A*: `fetch()` returns ES6 Promises, offering a cleaner, more readable `async/await` syntax without callback hell.

2. **Q: Why use `Array.prototype.slice()` for pagination?**
   - *A*: `slice(startIndex, endIndex)` extracts a shallow copy of array items for the current page without mutating the original dataset.

3. **Q: How is offline caching implemented?**
   - *A*: On successful network fetch, we save the JSON payload to `localStorage` with a timestamp. On network error, `localStorage` is read to render the cached data seamlessly.
