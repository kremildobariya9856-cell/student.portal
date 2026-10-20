// Events page: loads event data from a JSON file and lets the user
// search, filter by category, sort and page through the results.

var eventsPerPage = 4;
var allEvents = [];
var filteredEvents = [];
var currentPage = 1;

var eventListEl = document.getElementById("eventList");
var searchInput = document.getElementById("eventSearch");
var categorySelect = document.getElementById("categoryFilter");
var sortSelect = document.getElementById("sortEvents");
var paginationEl = document.getElementById("pagination");
var statusEl = document.getElementById("eventStatus");

function loadEvents() {
  statusEl.textContent = "Loading events...";
  eventListEl.innerHTML = "";

  fetch("events-data.json")
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Could not load events (status " + response.status + ")");
      }
      return response.json();
    })
    .then(function (data) {
      allEvents = data;
      statusEl.textContent = "";
      applyFilters();
    })
    .catch(function (error) {
      statusEl.textContent = "Sorry, events could not be loaded. Please try again later.";
      console.error(error);
    });
}

function applyFilters() {
  var searchTerm = searchInput.value.trim().toLowerCase();
  var selectedCategory = categorySelect.value;

  filteredEvents = allEvents.filter(function (item) {
    var matchesSearch =
      item.title.toLowerCase().indexOf(searchTerm) !== -1 ||
      item.venue.toLowerCase().indexOf(searchTerm) !== -1;
    var matchesCategory = selectedCategory === "all" || item.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  sortEvents();
  currentPage = 1;
  renderEvents();
}

function sortEvents() {
  var sortBy = sortSelect.value;

  if (sortBy === "date-asc") {
    filteredEvents.sort(function (a, b) {
      return new Date(a.date) - new Date(b.date);
    });
  } else if (sortBy === "date-desc") {
    filteredEvents.sort(function (a, b) {
      return new Date(b.date) - new Date(a.date);
    });
  } else if (sortBy === "title-asc") {
    filteredEvents.sort(function (a, b) {
      return a.title.localeCompare(b.title);
    });
  }
}

function formatDate(dateString) {
  var options = { day: "numeric", month: "short", year: "numeric" };
  return new Date(dateString).toLocaleDateString("en-GB", options);
}

function renderEvents() {
  eventListEl.innerHTML = "";

  if (filteredEvents.length === 0) {
    statusEl.textContent = "No events match your search.";
    paginationEl.innerHTML = "";
    return;
  }

  statusEl.textContent = "";

  var startIndex = (currentPage - 1) * eventsPerPage;
  var pageEvents = filteredEvents.slice(startIndex, startIndex + eventsPerPage);

  pageEvents.forEach(function (item) {
    var card = document.createElement("article");
    card.className = "event-card";
    card.innerHTML =
      "<h4>" + item.title + "</h4>" +
      "<p class='event-meta'>" + formatDate(item.date) + " &middot; " + item.category + " &middot; " + item.venue + "</p>" +
      "<p>" + item.description + "</p>";
    eventListEl.appendChild(card);
  });

  renderPagination();
}

function renderPagination() {
  paginationEl.innerHTML = "";
  var totalPages = Math.ceil(filteredEvents.length / eventsPerPage);

  if (totalPages <= 1) {
    return;
  }

  for (var i = 1; i <= totalPages; i++) {
    var pageButton = document.createElement("button");
    pageButton.type = "button";
    pageButton.textContent = i;
    if (i === currentPage) {
      pageButton.disabled = true;
      pageButton.className = "active-page";
    }
    pageButton.addEventListener("click", makePageClickHandler(i));
    paginationEl.appendChild(pageButton);
  }
}

function makePageClickHandler(pageNumber) {
  return function () {
    currentPage = pageNumber;
    renderEvents();
  };
}

searchInput.addEventListener("input", applyFilters);
categorySelect.addEventListener("change", applyFilters);
sortSelect.addEventListener("change", applyFilters);

loadEvents();
