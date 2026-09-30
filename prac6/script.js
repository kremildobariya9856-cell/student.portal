/**
 * CHARUSAT Student Hub - Practical 6 JavaScript
 * Async Fetch API, Real-Time Search, Multi-Criteria Filtering, Sorting,
 * Pagination, LocalStorage Caching, and Dependent Dropdowns.
 * Supports both HTTP Server (Fetch API) and direct file:// protocol (Fallback Data).
 */

document.addEventListener("DOMContentLoaded", () => {
    // Fallback static datasets for direct file:// protocol browser opening
    const FALLBACK_DATA = {
        events: [
            { id: 101, title: "SPOURAL 2026 - Annual Sports Fest", category: "Sports", date: "2026-10-15", venue: "CHARUSAT Athletics Ground", organizer: "Kremil Dobariya (System Admin)", attendees: 450, status: "Upcoming", description: "Inter-college athletic meet featuring cricket, football, basketball, badminton, and track events." },
            { id: 102, title: "Cognizance 2026 - National Tech Symposium", category: "Technical", date: "2026-11-02", venue: "DEPSTAR Auditorium", organizer: "Department of Computer Engineering", attendees: 600, status: "Upcoming", description: "National level hackathon, paper presentations, AI code sprints, and robotics competitions." },
            { id: 103, title: "AI & Deep Learning Hands-On Workshop", category: "Workshop", date: "2026-10-08", venue: "Lab 402, CSPIT", organizer: "IEEE Student Branch", attendees: 120, status: "Upcoming", description: "Comprehensive 2-day session covering PyTorch, Transformer architectures, and LLM fine-tuning." },
            { id: 104, title: "Cultural Night & Talent Hunt", category: "Cultural", date: "2026-11-20", venue: "Central Amphitheatre", organizer: "Cultural Club", attendees: 800, status: "Upcoming", description: "Annual music, dance, drama, and fashion showcase celebrating campus diversity." },
            { id: 105, title: "Cyber Security & Ethical Hacking Bootcamp", category: "Workshop", date: "2026-09-12", venue: "Online Webinar", organizer: "CSI Student Chapter", attendees: 250, status: "Completed", description: "Explored network penetration testing, CTF challenges, and OWASP top 10 vulnerabilities." },
            { id: 106, title: "Campus Placement Drive - Tech Mahindra", category: "Placement", date: "2026-10-25", venue: "Training & Placement Cell", organizer: "CHARUSAT T&P Cell", attendees: 300, status: "Upcoming", description: "On-campus recruitment drive for final year B.Tech, MCA, and M.Tech students." },
            { id: 107, title: "Web3 & Blockchain Developer Summit", category: "Technical", date: "2026-12-05", venue: "Seminar Hall 1", organizer: "Developer Student Club", attendees: 180, status: "Upcoming", description: "Introduction to Smart Contracts, Solidity development, and decentralized applications." },
            { id: 108, title: "Blood Donation & Health Checkup Camp", category: "Social Cause", date: "2026-09-05", venue: "Student Activity Centre", organizer: "NSS Wing", attendees: 350, status: "Completed", description: "Voluntary blood donation drive organized in association with Indian Red Cross Society." },
            { id: 109, title: "Cloud Computing & AWS Certification Bootcamp", category: "Workshop", date: "2026-10-18", venue: "Computer Lab 301", organizer: "AWS Academy CHARUSAT", attendees: 140, status: "Upcoming", description: "Hands-on cloud infrastructure provisioning, S3, EC2 deployment, and serverless architectures." },
            { id: 110, title: "Annual Alumni Reconnect & Networking Dinner", category: "Alumni", date: "2026-12-18", venue: "CHARUSAT Guest House Lawn", organizer: "Alumni Association", attendees: 220, status: "Upcoming", description: "Interactive networking event connecting current students with distinguished CHARUSAT alumni." },
            { id: 111, title: "GameDev 24-Hour Game Jam", category: "Technical", date: "2026-11-14", venue: "Game Lab, IIIM", organizer: "Game Development Club", attendees: 95, status: "Upcoming", description: "Create games from scratch using Unity and Unreal Engine within a 24-hour sprint." },
            { id: 112, title: "Design Thinking & UI/UX Workshop", category: "Workshop", date: "2026-08-28", venue: "Design Studio", organizer: "Department of IT", attendees: 110, status: "Completed", description: "Mastering wireframing, Figma prototyping, usability testing, and visual design principles." },
            { id: 113, title: "Inter-Department Chess Championship", category: "Sports", date: "2026-10-10", venue: "Indoor Sports Complex", organizer: "Sports Club", attendees: 64, status: "Upcoming", description: "Rapid and Blitz chess tournament across all university institutes." },
            { id: 114, title: "Entrepreneurship & Startup Pitch Competition", category: "Business", date: "2026-11-28", venue: "EDC Incubator Center", organizer: "Entrepreneurship Development Cell", attendees: 130, status: "Upcoming", description: "Pitch innovative business ideas to angel investors and seed fund mentors." },
            { id: 115, title: "Environmental Sustainability & Tree Plantation", category: "Social Cause", date: "2026-08-15", venue: "Campus Botanical Garden", organizer: "Eco Club", attendees: 210, status: "Completed", description: "Planted 500 saplings across the campus on Independence Day celebration." }
        ],
        students: [
            { id: 201, name: "Kremil Dobariya", email: "25DCE022@charusat.edu.in", department: "Computer Engineering", year: "Student Administrator", gpa: 9.95, skills: ["System Admin", "React", "Node.js", "Python", "Cloud Security"], status: "System Admin" },
            { id: 202, name: "Diya Shah", email: "diya.it24@charusat.edu.in", department: "Information Technology", year: "2nd Year", gpa: 9.15, skills: ["Java", "Spring Boot", "MySQL", "Git"], status: "Active" },
            { id: 203, name: "Rohan Sharma", email: "rohan.ai23@charusat.edu.in", department: "AI & Data Science", year: "3rd Year", gpa: 8.85, skills: ["TensorFlow", "PyTorch", "Python", "OpenCV"], status: "Active" },
            { id: 204, name: "Ananya Joshi", email: "ananya.ce22@charusat.edu.in", department: "Computer Engineering", year: "4th Year", gpa: 9.68, skills: ["Go", "Kubernetes", "AWS", "Microservices"], status: "Graduating" },
            { id: 205, name: "Karan Mehta", email: "karan.mca24@charusat.edu.in", department: "Computer Applications", year: "1st Year", gpa: 8.40, skills: ["PHP", "Laravel", "JavaScript", "HTML/CSS"], status: "Active" },
            { id: 206, name: "Priya Desai", email: "priya.it23@charusat.edu.in", department: "Information Technology", year: "3rd Year", gpa: 9.02, skills: ["Flutter", "Dart", "Firebase", "UI/UX"], status: "Active" },
            { id: 207, name: "Devansh Trivedi", email: "devansh.ec24@charusat.edu.in", department: "Electronics & Comm.", year: "2nd Year", gpa: 8.65, skills: ["Embedded C", "IoT", "Arduino", "Raspberry Pi"], status: "Active" },
            { id: 208, name: "Sneha Parmar", email: "sneha.ce25@charusat.edu.in", department: "Computer Engineering", year: "1st Year", gpa: 8.90, skills: ["C++", "Data Structures", "Algorithms", "HTML5"], status: "Active" },
            { id: 209, name: "Harsh Vora", email: "harsh.ai24@charusat.edu.in", department: "AI & Data Science", year: "2nd Year", gpa: 9.30, skills: ["Scikit-Learn", "Pandas", "R", "SQL"], status: "Active" },
            { id: 210, name: "Meera Bhatt", email: "meera.bca23@charusat.edu.in", department: "Computer Applications", year: "3rd Year", gpa: 8.75, skills: ["Vue.js", "Tailwind CSS", "REST API", "MongoDB"], status: "Active" },
            { id: 211, name: "Vikas Rathod", email: "vikas.ce22@charusat.edu.in", department: "Computer Engineering", year: "4th Year", gpa: 9.55, skills: ["Cyber Security", "Linux", "Ethical Hacking", "Python"], status: "Graduating" },
            { id: 212, name: "Tanvi Solanki", email: "tanvi.it25@charusat.edu.in", department: "Information Technology", year: "1st Year", gpa: 8.20, skills: ["Python", "SQL", "HTML5", "CSS3"], status: "Active" },
            { id: 213, name: "Yash Zaveri", email: "yash.mtech24@charusat.edu.in", department: "Computer Engineering", year: "Post Graduate", gpa: 9.75, skills: ["Natural Language Processing", "BERT", "Deep Learning", "PyTorch"], status: "Research Scholar" },
            { id: 214, name: "Ishita Gandhi", email: "ishita.ai23@charusat.edu.in", department: "AI & Data Science", year: "3rd Year", gpa: 9.10, skills: ["Data Visualization", "Tableau", "Power BI", "Python"], status: "Active" },
            { id: 215, name: "Siddharth Doshi", email: "siddharth.ce23@charusat.edu.in", department: "Computer Engineering", year: "3rd Year", gpa: 8.95, skills: ["Swift", "iOS Development", "CoreData", "Git"], status: "Active" }
        ],
        faqs: [
            { id: 301, question: "How do I register for semester examination backlogs or re-checking?", answer: "Students can apply for examination re-checking or backlog registration via the Student Hub portal under the Academic tab within 7 days of result declaration.", category: "Academics", tags: ["Exam", "Results", "Rechecking", "Backlog"], helpfulCount: 142 },
            { id: 302, question: "What is the minimum attendance requirement to appear in end-semester exams?", answer: "CHARUSAT academic regulations require a minimum of 75% attendance in both lectures and practical sessions for each course.", category: "Academics", tags: ["Attendance", "Regulations", "Eligibility"], helpfulCount: 210 },
            { id: 303, question: "How can I apply for a bonafide certificate or transcript?", answer: "Submit an online application through the Administrative Services section on the portal or visit the Student Section in the Central Administration Building.", category: "Administration", tags: ["Certificate", "Transcript", "Documents"], helpfulCount: 98 },
            { id: 304, question: "What are the timings and access rules for the Central Library?", answer: "The Central Library is open from 8:00 AM to 8:00 PM on weekdays and 9:00 AM to 5:00 PM on Saturdays. Digital library resources are accessible 24/7.", category: "Library", tags: ["Library", "Timings", "Books", "E-resources"], helpfulCount: 175 },
            { id: 305, question: "How do I connect to the CHARUSAT High-Speed Campus Wi-Fi?", answer: "Connect to 'CHARUSAT-STUDENT' network and log in using your University Enrollment Number and portal password. MAC binding is required.", category: "IT Support", tags: ["WiFi", "Internet", "Credentials", "Network"], helpfulCount: 320 },
            { id: 306, question: "Where can I find information about upcoming placement drives?", answer: "Training & Placement opportunities are updated daily on the T&P Cell Notice Board on the portal and broadcasted via student email.", category: "Placements", tags: ["Jobs", "Campus Drive", "T&P", "Recruitment"], helpfulCount: 189 },
            { id: 307, question: "How do I apply for merit-based or NEED-based scholarships?", answer: "Scholarship forms are available at the beginning of each academic year under the Financial Aid section. Submit required income proof before the deadline.", category: "Scholarships", tags: ["Financial Aid", "Fees", "Merit", "Scholarship"], helpfulCount: 165 },
            { id: 308, question: "What hostel facilities are provided and how do I apply for accommodation?", answer: "Separate boys and girls hostels are available on campus with Wi-Fi, mess, and security. Hostel allocation forms are available online during admission.", category: "Hostel", tags: ["Accommodation", "Hostel", "Mess", "Campus Life"], helpfulCount: 130 },
            { id: 309, question: "What steps should I take if I face technical issues during online exam submission?", answer: "Immediately capture a screenshot of the error with timestamp and report to exam.support@charusat.edu.in within 15 minutes of the issue.", category: "IT Support", tags: ["Online Exam", "Technical Support", "Error"], helpfulCount: 85 },
            { id: 3010, question: "How can I join campus clubs like Robotics, Cultural, or IEEE?", answer: "Club orientation drives take place during the first month of the odd semester. Students can also register online through the Events page.", category: "Campus Life", tags: ["Clubs", "Societies", "Activities", "IEEE"], helpfulCount: 115 },
            { id: 3011, question: "What is the procedure for paying tuition fees online?", answer: "Tuition fees can be paid via Net Banking, UPI, or Credit/Debit Cards through the Pay Fees option on the Student Portal. E-receipt is generated instantly.", category: "Administration", tags: ["Fees", "Payment", "Online Fee", "Receipt"], helpfulCount: 240 },
            { id: 3012, question: "Is bus transportation facility available for day scholars from nearby cities?", answer: "Yes, CHARUSAT operates a fleet of AC buses connecting Anand, Nadiad, Vadodara, and Ahmedabad. Bus passes can be renewed per semester.", category: "Transport", tags: ["Bus", "Transport", "Commute", "Pass"], helpfulCount: 150 },
            { id: 3013, question: "How can I submit feedback regarding course faculty or campus infrastructure?", answer: "Anonymous student feedback forms are opened twice a semester under the Feedback tab in your student dashboard.", category: "Feedback", tags: ["Feedback", "Faculty", "Infrastructure", "Evaluation"], helpfulCount: 78 },
            { id: 3014, question: "What emergency medical services are available on campus?", answer: "The CHARUSAT Health Centre provides 24/7 free medical consultation, first aid, and ambulance service for all students and staff.", category: "Health & Safety", tags: ["Health", "Medical", "Emergency", "Ambulance"], helpfulCount: 160 },
            { id: 3015, question: "How can I reset my forgotten student portal password?", answer: "Click on 'Forgot Password' on the login screen, enter your registered email ID, and follow the link sent to your email to set a new password.", category: "IT Support", tags: ["Password", "Reset", "Login", "Account Security"], helpfulCount: 290 }
        ],
        locations: {
            "India": {
                "Gujarat": ["Anand", "Nadiad", "Vadodara", "Ahmedabad", "Surat", "Rajkot"],
                "Maharashtra": ["Mumbai", "Pune", "Nagpur", "Nashik", "Thane"],
                "Karnataka": ["Bengaluru", "Mysuru", "Hubballi", "Mangaluru"],
                "Delhi NCR": ["New Delhi", "Noida", "Gurugram", "Faridabad"]
            },
            "United States": {
                "California": ["San Francisco", "Los Angeles", "San Jose", "San Diego"],
                "New York": ["New York City", "Buffalo", "Albany", "Rochester"],
                "Texas": ["Austin", "Houston", "Dallas", "San Antonio"]
            },
            "United Kingdom": {
                "England": ["London", "Manchester", "Birmingham", "Cambridge", "Oxford"],
                "Scotland": ["Edinburgh", "Glasgow", "Aberdeen"]
            },
            "Canada": {
                "Ontario": ["Toronto", "Ottawa", "Hamilton", "Kitchener"],
                "British Columbia": ["Vancouver", "Victoria", "Surrey"]
            }
        }
    };

    // State Management
    let currentTab = "events"; // 'events' | 'students' | 'faqs' | 'location'
    let rawData = [];
    let filteredData = [];
    let currentPage = 1;
    let itemsPerPage = 6;
    let locationsData = {};

    // DOM Elements
    const cardsGrid = document.getElementById("cardsGrid");
    const faqContainer = document.getElementById("faqContainer");
    const searchInput = document.getElementById("searchInput");
    const categorySelect = document.getElementById("categorySelect");
    const sortSelect = document.getElementById("sortSelect");
    const pageSizeSelect = document.getElementById("pageSizeSelect");
    const btnResetFilters = document.getElementById("btnResetFilters");
    const resultsCountEl = document.getElementById("resultsCount");
    const resultsInfoBar = document.querySelector(".results-info-bar");
    const paginationContainer = document.getElementById("paginationContainer");
    const paginationControls = document.getElementById("paginationControls");
    const cacheStatusText = document.getElementById("cacheStatusText");
    const cacheDot = document.getElementById("cacheDot");
    const toolbarCard = document.getElementById("toolbarCard");
    const locationExtensionCard = document.getElementById("locationExtensionCard");

    // Dependent Dropdown Elements
    const countrySelect = document.getElementById("countrySelect");
    const stateSelect = document.getElementById("stateSelect");
    const citySelect = document.getElementById("citySelect");
    const locationSummaryText = document.getElementById("locationSummaryText");

    // =========================================================================
    // 1. ASYNC FETCH API WITH LOCALSTORAGE CACHING & FALLBACK
    // =========================================================================
    async function loadData(dataType, skipSkeleton = false) {
        if (!skipSkeleton && currentTab !== "location") {
            showSkeletonLoader();
        }
        const cacheKey = `prac6_cache_${dataType}`;

        try {
            // Attempt Fetch API Request
            const response = await fetch(`./${dataType}.json`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            const data = await response.json();

            // Cache data in localStorage
            try {
                localStorage.setItem(cacheKey, JSON.stringify({
                    timestamp: new Date().toISOString(),
                    payload: data
                }));
            } catch (e) {
                console.warn("localStorage quota exceeded or disabled", e);
            }

            updateCacheBadge(true, "Fetch API Live Data");
            return data;

        } catch (error) {
            console.warn(`Fetch API call for ${dataType}.json failed or blocked by CORS file:// protocol. Trying fallback...`, error);

            // Try localStorage Cache first
            const cachedItem = localStorage.getItem(cacheKey);
            if (cachedItem) {
                const parsedCache = JSON.parse(cachedItem);
                const cacheTime = new Date(parsedCache.timestamp).toLocaleTimeString();
                updateCacheBadge(false, `Cached Data (${cacheTime})`);
                return parsedCache.payload;
            }

            // If no localStorage, fallback to embedded static data array
            if (FALLBACK_DATA[dataType]) {
                updateCacheBadge(true, "Data Loaded (Local Protocol)");
                return FALLBACK_DATA[dataType];
            }

            updateCacheBadge(false, "Data Unavailable");
            throw error;
        }
    }

    function updateCacheBadge(isLive, message) {
        if (!cacheStatusText || !cacheDot) return;
        cacheStatusText.textContent = message;
        if (isLive) {
            cacheDot.className = "badge-dot";
        } else {
            cacheDot.className = "badge-dot offline";
        }
    }

    function showSkeletonLoader() {
        if (!cardsGrid || currentTab === "location") return;
        cardsGrid.innerHTML = `
            <div class="skeleton-loader"></div>
            <div class="skeleton-loader"></div>
            <div class="skeleton-loader"></div>
        `;
        if (faqContainer) faqContainer.style.display = "none";
        cardsGrid.style.display = "grid";
    }

    // =========================================================================
    // 2. TAB SWITCHING & DYNAMIC CATEGORY INITIALIZATION
    // =========================================================================
    async function switchTab(tabName) {
        currentTab = tabName;
        currentPage = 1;

        // Update active tab button styles
        document.querySelectorAll(".tab-btn").forEach(btn => {
            if (btn.dataset.tab === tabName) {
                btn.classList.add("active");
                btn.setAttribute("aria-selected", "true");
            } else {
                btn.classList.remove("active");
                btn.setAttribute("aria-selected", "false");
            }
        });

        if (tabName === "location") {
            // Show Dependent Dropdowns View exclusively
            if (toolbarCard) toolbarCard.style.display = "none";
            if (resultsInfoBar) resultsInfoBar.style.display = "none";
            if (cardsGrid) {
                cardsGrid.style.display = "none";
                cardsGrid.innerHTML = "";
            }
            if (faqContainer) faqContainer.style.display = "none";
            if (paginationContainer) paginationContainer.style.display = "none";
            if (locationExtensionCard) locationExtensionCard.style.display = "block";
            await initLocationDropdowns();
            return;
        } else {
            if (toolbarCard) toolbarCard.style.display = "flex";
            if (resultsInfoBar) resultsInfoBar.style.display = "flex";
            if (locationExtensionCard) locationExtensionCard.style.display = "none";
            if (paginationContainer) paginationContainer.style.display = "flex";
        }

        try {
            rawData = await loadData(tabName);
            populateCategoryDropdown(rawData);
            applyFiltersAndRender();
        } catch (err) {
            cardsGrid.innerHTML = `
                <div class="empty-state">
                    <h4>⚠️ Error Loading Data</h4>
                    <p>Unable to load data for ${tabName}. Please check your browser settings.</p>
                </div>
            `;
        }
    }

    function populateCategoryDropdown(data) {
        categorySelect.innerHTML = `<option value="">All Categories</option>`;
        let categories = new Set();

        data.forEach(item => {
            if (item.category) categories.add(item.category);
            if (item.department) categories.add(item.department);
        });

        categories.forEach(cat => {
            const option = document.createElement("option");
            option.value = cat;
            option.textContent = cat;
            categorySelect.appendChild(option);
        });
    }

    // Event listeners for Tab Buttons
    document.querySelectorAll(".tab-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            switchTab(btn.dataset.tab);
        });
    });

    // =========================================================================
    // 3. SEARCH, FILTERING, & SORTING ENGINE (Array Methods: filter & sort)
    // =========================================================================
    function applyFiltersAndRender() {
        if (currentTab === "location") return;

        const query = searchInput.value.trim().toLowerCase();
        const selectedCat = categorySelect.value;
        const sortVal = sortSelect.value;

        // 1. Array.filter() for Search & Category
        filteredData = rawData.filter(item => {
            // Category Filter
            if (selectedCat) {
                const itemCat = item.category || item.department;
                if (itemCat !== selectedCat) return false;
            }

            // Search Query Filter
            if (query) {
                const titleMatch = (item.title || item.name || item.question || "").toLowerCase().includes(query);
                const descMatch = (item.description || item.answer || item.email || "").toLowerCase().includes(query);
                const tagMatch = item.tags ? item.tags.some(t => t.toLowerCase().includes(query)) : false;
                const skillMatch = item.skills ? item.skills.some(s => s.toLowerCase().includes(query)) : false;

                return titleMatch || descMatch || tagMatch || skillMatch;
            }

            return true;
        });

        // 2. Array.sort() for Sorting
        filteredData.sort((a, b) => {
            if (sortVal === "title-asc") {
                const textA = a.title || a.name || a.question;
                const textB = b.title || b.name || b.question;
                return textA.localeCompare(textB);
            }
            if (sortVal === "title-desc") {
                const textA = a.title || a.name || a.question;
                const textB = b.title || b.name || b.question;
                return textB.localeCompare(textA);
            }
            if (sortVal === "date-desc") {
                if (a.date && b.date) return new Date(b.date) - new Date(a.date);
                if (a.gpa && b.gpa) return b.gpa - a.gpa;
                if (a.helpfulCount && b.helpfulCount) return b.helpfulCount - a.helpfulCount;
            }
            if (sortVal === "date-asc") {
                if (a.date && b.date) return new Date(a.date) - new Date(b.date);
                if (a.gpa && b.gpa) return a.gpa - b.gpa;
            }
            return 0;
        });

        // Reset to Page 1 on Filter/Search Change
        renderPaginatedView();
    }

    // Controls Event Handlers
    searchInput.addEventListener("input", () => {
        currentPage = 1;
        applyFiltersAndRender();
    });

    categorySelect.addEventListener("change", () => {
        currentPage = 1;
        applyFiltersAndRender();
    });

    sortSelect.addEventListener("change", applyFiltersAndRender);

    pageSizeSelect.addEventListener("change", (e) => {
        itemsPerPage = parseInt(e.target.value, 10);
        currentPage = 1;
        renderPaginatedView();
    });

    btnResetFilters.addEventListener("click", () => {
        searchInput.value = "";
        categorySelect.value = "";
        sortSelect.value = "default";
        currentPage = 1;
        applyFiltersAndRender();
    });

    // =========================================================================
    // 4. PAGINATION ENGINE & DYNAMIC RENDER (Array.slice & Array.map)
    // =========================================================================
    function renderPaginatedView() {
        if (currentTab === "location") return;

        const totalItems = filteredData.length;
        resultsCountEl.textContent = `Showing ${totalItems} result${totalItems !== 1 ? "s" : ""}`;

        if (totalItems === 0) {
            cardsGrid.style.display = "grid";
            cardsGrid.innerHTML = `
                <div class="empty-state">
                    <h4>🔍 No Matching Records Found</h4>
                    <p>Try adjusting your search keywords or filter criteria.</p>
                </div>
            `;
            if (faqContainer) faqContainer.style.display = "none";
            renderPaginationControls(0);
            return;
        }

        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;

        // Array.slice() for Pagination
        const startIndex = (currentPage - 1) * itemsPerPage;
        const pageData = filteredData.slice(startIndex, startIndex + itemsPerPage);

        // Render based on current dataset type
        if (currentTab === "events") {
            renderEvents(pageData);
        } else if (currentTab === "students") {
            renderStudents(pageData);
        } else if (currentTab === "faqs") {
            renderFAQs(pageData);
        }

        renderPaginationControls(totalPages);
    }

    // View Renderers using Array.map()
    function renderEvents(events) {
        cardsGrid.style.display = "grid";
        if (faqContainer) faqContainer.style.display = "none";

        cardsGrid.innerHTML = events.map(evt => `
            <article class="data-card">
                <div>
                    <div class="card-header">
                        <h4 class="card-title">${escapeHTML(evt.title)}</h4>
                        <span class="card-badge ${evt.status === 'Upcoming' ? 'badge-upcoming' : 'badge-completed'}">
                            ${escapeHTML(evt.status)}
                        </span>
                    </div>
                    <div class="card-body">
                        <p>${escapeHTML(evt.description)}</p>
                        <div class="card-meta">
                            <span>📅 ${escapeHTML(evt.date)}</span>
                            <span>📍 ${escapeHTML(evt.venue)}</span>
                            <span>👥 ${evt.attendees} Attendees</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <span>Organized by: ${escapeHTML(evt.organizer)}</span>
                    <span class="tag-pill">${escapeHTML(evt.category)}</span>
                </div>
            </article>
        `).join("");
    }

    function renderStudents(students) {
        cardsGrid.style.display = "grid";
        if (faqContainer) faqContainer.style.display = "none";

        cardsGrid.innerHTML = students.map(std => `
            <article class="data-card">
                <div>
                    <div class="card-header">
                        <h4 class="card-title">👨‍🎓 ${escapeHTML(std.name)}</h4>
                        <span class="card-badge badge-gpa">GPA ${std.gpa}</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Department:</strong> ${escapeHTML(std.department)}</p>
                        <p><strong>Academic Level:</strong> ${escapeHTML(std.year)}</p>
                        <p><strong>Contact Email:</strong> <a href="mailto:${std.email}" style="color:#003366;">${escapeHTML(std.email)}</a></p>
                        <div class="card-tags" style="margin-top:12px;">
                            ${std.skills.map(s => `<span class="tag-pill">💻 ${escapeHTML(s)}</span>`).join("")}
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <span>ID: #${std.id}</span>
                    <span style="color:#059669; font-weight:bold;">Status: ${escapeHTML(std.status)}</span>
                </div>
            </article>
        `).join("");
    }

    function renderFAQs(faqs) {
        cardsGrid.style.display = "none";
        if (!faqContainer) return;
        faqContainer.style.display = "block";

        faqContainer.innerHTML = faqs.map(faq => `
            <article class="faq-card">
                <div class="faq-question">❓ ${escapeHTML(faq.question)}</div>
                <div class="faq-answer">${escapeHTML(faq.answer)}</div>
                <div class="card-meta" style="margin-top:12px;">
                    <span>📂 Category: <strong>${escapeHTML(faq.category)}</strong></span>
                    <span>👍 Helpful (${faq.helpfulCount})</span>
                </div>
                <div class="card-tags" style="margin-top:8px;">
                    ${faq.tags ? faq.tags.map(t => `<span class="tag-pill">#${escapeHTML(t)}</span>`).join("") : ""}
                </div>
            </article>
        `).join("");
    }

    function renderPaginationControls(totalPages) {
        if (!paginationControls) return;
        paginationControls.innerHTML = "";

        if (totalPages <= 1) return;

        // Previous Page Button
        const prevBtn = document.createElement("button");
        prevBtn.className = "btn-page";
        prevBtn.textContent = "« Prev";
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderPaginatedView();
            }
        });
        paginationControls.appendChild(prevBtn);

        // Page Number Buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement("button");
            pageBtn.className = `btn-page ${i === currentPage ? "active" : ""}`;
            pageBtn.textContent = i;
            pageBtn.addEventListener("click", () => {
                currentPage = i;
                renderPaginatedView();
            });
            paginationControls.appendChild(pageBtn);
        }

        // Next Page Button
        const nextBtn = document.createElement("button");
        nextBtn.className = "btn-page";
        nextBtn.textContent = "Next »";
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPaginatedView();
            }
        });
        paginationControls.appendChild(nextBtn);
    }

    // =========================================================================
    // 5. DEPENDENT DROPDOWNS EXTENSION (Intermediate Extension)
    // =========================================================================
    async function initLocationDropdowns() {
        if (Object.keys(locationsData).length === 0) {
            try {
                locationsData = await loadData("locations", true);
            } catch (e) {
                console.error("Failed to load locations data", e);
                locationsData = FALLBACK_DATA.locations;
            }
        }

        // Populate Country Dropdown
        countrySelect.innerHTML = `<option value="">-- Select Country --</option>`;
        stateSelect.innerHTML = `<option value="">-- Select State / Region --</option>`;
        citySelect.innerHTML = `<option value="">-- Select City --</option>`;
        stateSelect.disabled = true;
        citySelect.disabled = true;

        Object.keys(locationsData).forEach(country => {
            const opt = document.createElement("option");
            opt.value = country;
            opt.textContent = country;
            countrySelect.appendChild(opt);
        });
    }

    if (countrySelect) {
        countrySelect.addEventListener("change", (e) => {
            const country = e.target.value;
            stateSelect.innerHTML = `<option value="">-- Select State / Region --</option>`;
            citySelect.innerHTML = `<option value="">-- Select City --</option>`;
            citySelect.disabled = true;

            if (country && locationsData[country]) {
                stateSelect.disabled = false;
                Object.keys(locationsData[country]).forEach(state => {
                    const opt = document.createElement("option");
                    opt.value = state;
                    opt.textContent = state;
                    stateSelect.appendChild(opt);
                });
            } else {
                stateSelect.disabled = true;
            }
            updateLocationSummary();
        });
    }

    if (stateSelect) {
        stateSelect.addEventListener("change", (e) => {
            const country = countrySelect.value;
            const state = e.target.value;
            citySelect.innerHTML = `<option value="">-- Select City --</option>`;

            if (country && state && locationsData[country][state]) {
                citySelect.disabled = false;
                locationsData[country][state].forEach(city => {
                    const opt = document.createElement("option");
                    opt.value = city;
                    opt.textContent = city;
                    citySelect.appendChild(opt);
                });
            } else {
                citySelect.disabled = true;
            }
            updateLocationSummary();
        });
    }

    if (citySelect) {
        citySelect.addEventListener("change", updateLocationSummary);
    }

    function updateLocationSummary() {
        const country = countrySelect.value;
        const state = stateSelect.value;
        const city = citySelect.value;

        if (country && state && city) {
            locationSummaryText.innerHTML = `<strong>Selected Location:</strong> ${escapeHTML(city)}, ${escapeHTML(state)}, ${escapeHTML(country)} ✓`;
        } else if (country && state) {
            locationSummaryText.innerHTML = `<strong>Selected State:</strong> ${escapeHTML(state)}, ${escapeHTML(country)}`;
        } else if (country) {
            locationSummaryText.innerHTML = `<strong>Selected Country:</strong> ${escapeHTML(country)}`;
        } else {
            locationSummaryText.innerHTML = `<em>Select a country, state, and city from the dropdowns above.</em>`;
        }
    }

    // Utility: HTML Escaping
    function escapeHTML(str) {
        if (!str) return "";
        return String(str).replace(/[&<>'"]/g,
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }

    // Initialize default tab on load
    switchTab("events");
});
