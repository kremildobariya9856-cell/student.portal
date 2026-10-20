<?php
require_once __DIR__ . '/auth.php';

// Enforce admin-only access
requireAdmin();

$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';
$adminRole = $_SESSION['user_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management (Fetch API) | CHARUSAT Student Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Header -->
    <header>
        <div class="header-brand">
            <img src="CHARUSAT.jpg" alt="CHARUSAT University Banner" class="banner-img">
            <h1>CHARUSAT Student Hub</h1>
        </div>
        <?= renderRoleBasedNav('admin-events.php') ?>
    </header>

    <nav aria-label="Breadcrumb">
        <ol>
            <li><a href="admin-dashboard.php">Admin Panel</a></li>
            <li aria-current="page">Event Management (Fetch API)</li>
        </ol>
    </nav>

    <!-- Sidebar -->
    <?= renderRoleBasedSidebar('admin-events.php') ?>

    <!-- Main Content -->
    <main id="main-content">
        <h2>Event Management Module (AJAX / Fetch API)</h2>
        <p>Asynchronous CRUD operations powered by PHP JSON API and JavaScript Fetch API without page reload.</p>

        <!-- Active Admin Status Card -->
        <div class="user-status-card">
            <div>
                <strong>Active Admin:</strong> <?php echo htmlspecialchars($adminName); ?>
                <span class="badge badge-admin"><?php echo htmlspecialchars($adminRole); ?></span>
                <br>
                <small style="color:#555;">Email: <?php echo htmlspecialchars($adminEmail); ?> | API Mode: <strong>JSON / Fetch API (No Reload)</strong></small>
            </div>
            <div>
                <a href="#add-event-section" class="btn-success btn-sm" style="text-decoration:none; padding:8px 14px; font-weight:bold; border-radius:4px;">+ Add New Event</a>
                <a href="logout.php" class="btn-danger">Sign Out</a>
            </div>
        </div>

        <!-- Real-Time Notification Toast Container -->
        <div id="toastContainer" aria-live="polite"></div>

        <!-- SEARCH AND FILTER MODULE (ASYNCHRONOUS) -->
        <section class="search-filter-card">
            <h3 style="margin-bottom:12px; font-size:1.15rem; color:#003366;">Asynchronous Search & Filter</h3>
            <form id="filterForm" onsubmit="event.preventDefault(); fetchEvents();">
                <div class="search-filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
                    <div class="filter-group">
                        <label for="search">Keyword Search</label>
                        <input type="search" id="search" name="search" placeholder="Search by title or venue..." oninput="debounceFetch()">
                    </div>

                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" onchange="fetchEvents()">
                            <option value="">All Categories</option>
                            <option value="Technical">Technical</option>
                            <option value="Sports">Sports</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Academic">Academic</option>
                            <option value="Career">Career</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">Event Status</label>
                        <select id="status" name="status" onchange="fetchEvents()">
                            <option value="">All Statuses</option>
                            <option value="Upcoming">Upcoming</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="button" class="btn-primary" onclick="fetchEvents()">Filter</button>
                        <button type="button" class="btn-secondary" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </form>
        </section>

        <!-- VIEW / EVENTS LIST TABLE -->
        <section aria-labelledby="event-list-heading">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <h3 id="event-list-heading" style="margin:0;">Campus Events List</h3>
                <span id="eventCount" style="font-size:14px; color:#555;">Loading count...</span>
            </div>

            <!-- Loading Spinner State -->
            <div id="loadingIndicator" class="ajax-loading" style="display:none;">
                <div class="spinner"></div>
                <span>Fetching events asynchronously from PHP JSON API...</span>
            </div>

            <!-- Events Table -->
            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Poster</th>
                        <th scope="col">Title & Category</th>
                        <th scope="col">Date & Time</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Capacity</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsTbody">
                    <tr>
                        <td colspan="8" style="text-align:center; padding:25px; color:#666;">
                            <div class="spinner" style="margin-bottom:8px;"></div><br>
                            Loading events via Fetch API...
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ADD NEW EVENT FORM (ASYNCHRONOUS VIA FETCH) -->
        <section id="add-event-section" class="crud-form-card">
            <h3>Add New Event (Asynchronous POST)</h3>
            <p>Submit event details with poster image. Database is updated without page reload.</p>
            <form id="addEventForm" onsubmit="submitAddEvent(event)" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="crud-form-grid">
                    <div class="field">
                        <label for="add_title">Event Title *</label>
                        <input type="text" id="add_title" name="title" placeholder="e.g. National Hackathon 2026" required>
                    </div>

                    <div class="field">
                        <label for="add_category">Category *</label>
                        <select id="add_category" name="category" required>
                            <option value="Technical" selected>Technical</option>
                            <option value="Sports">Sports</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Academic">Academic</option>
                            <option value="Career">Career</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_date">Event Date *</label>
                        <input type="date" id="add_date" name="event_date" required>
                    </div>

                    <div class="field">
                        <label for="add_time">Event Time *</label>
                        <input type="text" id="add_time" name="event_time" placeholder="e.g. 10:00 AM" required>
                    </div>

                    <div class="field">
                        <label for="add_venue">Venue / Location *</label>
                        <input type="text" id="add_venue" name="venue" placeholder="e.g. Seminar Hall 1" required>
                    </div>

                    <div class="field">
                        <label for="add_capacity">Seating Capacity</label>
                        <input type="number" id="add_capacity" name="capacity" min="1" value="100" required>
                    </div>

                    <div class="field">
                        <label for="add_status">Event Status *</label>
                        <select id="add_status" name="status" required>
                            <option value="Upcoming" selected>Upcoming</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="add_poster">Event Poster Image <small style="font-weight:normal; color:#666;">(Max 2MB: JPG, PNG, WEBP)</small></label>
                        <input type="file" id="add_poster" name="poster" accept=".jpg,.jpeg,.png,.webp,.gif">
                    </div>

                    <div class="field" style="grid-column: 1 / -1;">
                        <label for="add_desc">Event Description</label>
                        <textarea id="add_desc" name="description" rows="3" placeholder="Provide schedule, event details, and rules..."></textarea>
                    </div>
                </div>

                <div style="margin-top:15px; display:flex; align-items:center; gap:12px;">
                    <button type="submit" id="addSubmitBtn" class="btn-success">Publish & Save Event (AJAX)</button>
                    <span id="addLoadingSpan" style="display:none; color:#003366; font-size:14px;">
                        <span class="spinner" style="width:14px; height:14px; vertical-align:middle;"></span> Saving event...
                    </span>
                </div>
            </form>
        </section>

    </main>

    <!-- EDIT EVENT MODAL (ASYNCHRONOUS UPDATE) -->
    <div id="editModal" class="modal-backdrop" style="display:none;" onclick="handleModalBackdropClick(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 id="editModalHeading">Edit Event</h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editEventForm" onsubmit="submitEditEvent(event)" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id" name="event_id" value="">

                <div class="crud-form-grid">
                    <div class="field">
                        <label for="edit_title">Event Title *</label>
                        <input type="text" id="edit_title" name="title" required>
                    </div>

                    <div class="field">
                        <label for="edit_category">Category *</label>
                        <select id="edit_category" name="category" required>
                            <option value="Technical">Technical</option>
                            <option value="Sports">Sports</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Academic">Academic</option>
                            <option value="Career">Career</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="edit_date">Event Date *</label>
                        <input type="date" id="edit_date" name="event_date" required>
                    </div>

                    <div class="field">
                        <label for="edit_time">Event Time *</label>
                        <input type="text" id="edit_time" name="event_time" required>
                    </div>

                    <div class="field">
                        <label for="edit_venue">Venue / Location *</label>
                        <input type="text" id="edit_venue" name="venue" required>
                    </div>

                    <div class="field">
                        <label for="edit_capacity">Seating Capacity</label>
                        <input type="number" id="edit_capacity" name="capacity" min="1" required>
                    </div>

                    <div class="field">
                        <label for="edit_status">Event Status *</label>
                        <select id="edit_status" name="status" required>
                            <option value="Upcoming">Upcoming</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="edit_poster">Change Poster <small style="font-weight:normal; color:#666;">(Leave blank to keep existing)</small></label>
                        <input type="file" id="edit_poster" name="poster" accept=".jpg,.jpeg,.png,.webp,.gif">
                        <div id="editPosterPreview" class="current-poster-preview" style="display:none; margin-top:8px;"></div>
                    </div>

                    <div class="field" style="grid-column: 1 / -1;">
                        <label for="edit_desc">Event Description</label>
                        <textarea id="edit_desc" name="description" rows="3"></textarea>
                    </div>
                </div>

                <div style="margin-top:16px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" id="editSubmitBtn" class="btn-primary" style="background:#ff9800;">Save Changes (Fetch API)</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <nav aria-label="Footer">
            <ul>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact us</a></li>
                <li><a href="faq.html">FAQ</a></li>
                <li><a href="feedback.html">Feedback</a></li>
            </ul>
        </nav>
        <p>&copy; 2026 CHARUSAT Student Hub. All rights reserved.</p>
    </footer>

    <!-- Asynchronous Fetch API CRUD Logic -->
    <script>
        const API_URL = 'api-events.php';
        let debounceTimer = null;

        // Display animated notification toast
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-msg toast-${type}`;
            toast.innerHTML = `
                <span><strong>${type === 'success' ? 'Success:' : 'Error:'}</strong> ${escapeHtml(message)}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:transparent; border:none; color:inherit; font-weight:bold; cursor:pointer; font-size:16px; margin-left:12px;">&times;</button>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.4s';
                setTimeout(() => toast.remove(), 400);
            }, 4500);
        }

        // Helper to escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // 1. READ / LIST (Asynchronous Fetch API GET)
        async function fetchEvents() {
            const loading = document.getElementById('loadingIndicator');
            const tbody = document.getElementById('eventsTbody');
            const countSpan = document.getElementById('eventCount');

            loading.style.display = 'inline-flex';

            const search = document.getElementById('search').value.trim();
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;

            const params = new URLSearchParams({ action: 'list' });
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (status) params.append('status', status);

            try {
                const response = await fetch(`${API_URL}?${params.toString()}`, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error(`Server returned HTTP ${response.status}`);
                }

                const json = await response.json();
                loading.style.display = 'none';

                if (json.status === 'success') {
                    renderEventsTable(json.data);
                    countSpan.innerHTML = `Showing <strong>${json.data.length}</strong> events ${search || category || status ? '(Filtered)' : ''}`;
                } else {
                    showToast(json.message || 'Failed to fetch events', 'error');
                }
            } catch (err) {
                loading.style.display = 'none';
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; color:#c9302c; padding:20px;">Network or Server Error: ${escapeHtml(err.message)}</td></tr>`;
                showToast('Network error while connecting to PHP JSON API.', 'error');
            }
        }

        // Debounce helper for live keyword searching
        function debounceFetch() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchEvents();
            }, 300);
        }

        // Reset filter inputs and re-fetch list
        function resetFilters() {
            document.getElementById('search').value = '';
            document.getElementById('category').value = '';
            document.getElementById('status').value = '';
            fetchEvents();
        }

        // Render events table rows
        function renderEventsTable(events) {
            const tbody = document.getElementById('eventsTbody');

            if (!events || events.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align:center; padding:30px; color:#777;">
                            No events found matching your criteria.
                            <br><button type="button" class="btn-sm btn-primary" onclick="resetFilters()" style="margin-top:10px;">Reset Filters</button>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            events.forEach(ev => {
                const statusClass = (ev.status || '').toLowerCase();
                const posterHtml = ev.has_poster 
                    ? `<a href="uploads/${encodeURIComponent(ev.poster)}" target="_blank" title="View Full Poster"><img src="uploads/${encodeURIComponent(ev.poster)}" alt="Poster" class="poster-thumb"></a>`
                    : `<div class="poster-no-img">No Poster</div>`;

                html += `
                    <tr id="event-row-${ev.event_id}">
                        <td>${ev.event_id}</td>
                        <td>${posterHtml}</td>
                        <td>
                            <strong>${escapeHtml(ev.title)}</strong><br>
                            <small style="color:#666;">${escapeHtml(ev.category)}</small>
                        </td>
                        <td>
                            ${escapeHtml(ev.event_date)}<br>
                            <small style="color:#555;">${escapeHtml(ev.event_time)}</small>
                        </td>
                        <td>${escapeHtml(ev.venue)}</td>
                        <td>${escapeHtml(ev.capacity)} Seats</td>
                        <td>
                            <span class="badge badge-${statusClass}">
                                ${escapeHtml(ev.status)}
                            </span>
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="btn-sm btn-edit" onclick="openEditModal(${ev.event_id})">Edit</button>
                                <button type="button" class="btn-sm btn-delete" onclick="deleteEvent(${ev.event_id}, '${escapeHtml(ev.title).replace(/'/g, "\\'")}')">Delete</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        // 2. CREATE (Asynchronous Fetch API POST with FormData)
        async function submitAddEvent(e) {
            e.preventDefault();
            const form = document.getElementById('addEventForm');
            const submitBtn = document.getElementById('addSubmitBtn');
            const loadingSpan = document.getElementById('addLoadingSpan');

            submitBtn.disabled = true;
            loadingSpan.style.display = 'inline-block';

            const formData = new FormData(form);

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });

                const json = await res.json();
                submitBtn.disabled = false;
                loadingSpan.style.display = 'none';

                if (res.ok && json.status === 'success') {
                    showToast(json.message, 'success');
                    form.reset();
                    fetchEvents(); // Refresh list asynchronously without full reload!
                } else {
                    showToast(json.message || 'Error creating event', 'error');
                }
            } catch (err) {
                submitBtn.disabled = false;
                loadingSpan.style.display = 'none';
                showToast('Failed to communicate with API: ' + err.message, 'error');
            }
        }

        // 3. EDIT: Open Modal & Fetch Single Event Record
        async function openEditModal(eventId) {
            try {
                const res = await fetch(`${API_URL}?action=get&id=${eventId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();

                if (json.status === 'success') {
                    const ev = json.data;
                    document.getElementById('editModalHeading').innerText = `Edit Event #${ev.event_id}: ${ev.title}`;
                    document.getElementById('edit_id').value = ev.event_id;
                    document.getElementById('edit_title').value = ev.title;
                    document.getElementById('edit_category').value = ev.category;
                    document.getElementById('edit_date').value = ev.event_date;
                    document.getElementById('edit_time').value = ev.event_time;
                    document.getElementById('edit_venue').value = ev.venue;
                    document.getElementById('edit_capacity').value = ev.capacity;
                    document.getElementById('edit_status').value = ev.status;
                    document.getElementById('edit_desc').value = ev.description || '';
                    document.getElementById('edit_poster').value = '';

                    const previewDiv = document.getElementById('editPosterPreview');
                    if (ev.has_poster) {
                        previewDiv.innerHTML = `
                            <img src="uploads/${encodeURIComponent(ev.poster)}" alt="Current Poster" class="poster-thumb">
                            <small style="color:#555;">Current Poster: <code>${escapeHtml(ev.poster)}</code></small>
                        `;
                        previewDiv.style.display = 'flex';
                    } else {
                        previewDiv.style.display = 'none';
                    }

                    document.getElementById('editModal').style.display = 'flex';
                } else {
                    showToast(json.message || 'Error loading event data', 'error');
                }
            } catch (err) {
                showToast('Could not load event: ' + err.message, 'error');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function handleModalBackdropClick(e) {
            if (e.target.id === 'editModal') {
                closeEditModal();
            }
        }

        // 4. UPDATE (Asynchronous Fetch API POST)
        async function submitEditEvent(e) {
            e.preventDefault();
            const form = document.getElementById('editEventForm');
            const submitBtn = document.getElementById('editSubmitBtn');

            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving changes...';

            const formData = new FormData(form);

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });

                const json = await res.json();
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Changes (Fetch API)';

                if (res.ok && json.status === 'success') {
                    showToast(json.message, 'success');
                    closeEditModal();
                    fetchEvents(); // Refresh data without reload!
                } else {
                    showToast(json.message || 'Error updating event', 'error');
                }
            } catch (err) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Changes (Fetch API)';
                showToast('Failed to communicate with API: ' + err.message, 'error');
            }
        }

        // 5. DELETE (Asynchronous Fetch API POST with confirmation)
        async function deleteEvent(eventId, title) {
            if (!confirm(`Are you sure you want to delete event: "${title}"?\n\nThis will permanently delete the event and its uploaded poster file without page reload.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('event_id', eventId);

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });

                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    showToast(json.message, 'success');

                    // Fade out row and refresh
                    const row = document.getElementById(`event-row-${eventId}`);
                    if (row) {
                        row.style.transition = 'opacity 0.4s, background-color 0.4s';
                        row.style.backgroundColor = '#f8d7da';
                        row.style.opacity = '0';
                        setTimeout(() => fetchEvents(), 400);
                    } else {
                        fetchEvents();
                    }
                } else {
                    showToast(json.message || 'Error deleting event', 'error');
                }
            } catch (err) {
                showToast('Failed to delete event: ' + err.message, 'error');
            }
        }

        // Initial Load on Page Ready
        document.addEventListener('DOMContentLoaded', () => {
            fetchEvents();
        });
    </script>

</body>
</html>
