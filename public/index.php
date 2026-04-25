<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events - Event Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">
                <span>📅</span>
                <span>Event Manager</span>
            </a>
            <nav>
                <a href="index.php">Events</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="page-wrapper">
        <div class="container">
            <!-- Alert Container -->
            <div id="alert-container"></div>

            <!-- Page Title -->
            <div class="page-title">
                <h1>🎉 Upcoming Events</h1>
                <p>Discover and register for amazing events</p>
            </div>

            <!-- Search Box -->
            <div style="margin-bottom: 40px; text-align: center;">
                <form id="search-form" style="max-width: 600px; margin: 0 auto;">
                    <div style="display: flex; gap: 10px;">
                        <input 
                            type="text" 
                            id="search-query" 
                            placeholder="Search events..." 
                            style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px;"
                        >
                        <button type="submit" class="btn btn-primary">Search</button>
                        <button type="reset" class="btn btn-secondary">Clear</button>
                    </div>
                </form>
            </div>

            <!-- Events Grid -->
            <div id="events-container" class="events-grid">
                <div class="loading-container">
                    <div class="loading"></div>
                    <p class="loading-text">Loading events...</p>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination-container" style="text-align: center; margin-top: 40px; display: none;">
                <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
                    <button id="prev-btn" class="btn btn-secondary btn-sm">← Previous</button>
                    <span id="page-info" style="padding: 10px 20px; background: #f0f0f0; border-radius: 6px;">Page 1</span>
                    <button id="next-btn" class="btn btn-secondary btn-sm">Next →</button>
                </div>
            </div>

            <!-- No Events Message -->
            <div id="no-events-message" style="text-align: center; padding: 60px 20px; display: none;">
                <div style="font-size: 60px; margin-bottom: 20px;">📭</div>
                <h2>No Events Found</h2>
                <p>There are no events available at the moment. Please check back later.</p>
                <a href="index.php" class="btn btn-primary mt-20">View All Events</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Event Management System. All rights reserved.</p>
        <p>Streamline your event management experience</p>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        let currentPage = 1;
        let searchMode = false;
        let searchQuery = '';
        const itemsPerPage = 10;

        /**
         * Load events
         */
        async function loadEvents(page = 1) {
            const container = document.getElementById('events-container');
            UI.showLoading('#events-container');

            let response;
            if (searchMode) {
                response = await EventAPI.searchEvents(searchQuery, page, itemsPerPage);
            } else {
                response = await EventAPI.getEvents('upcoming', page, itemsPerPage);
            }

            if (!response.success) {
                UI.showAlert('Failed to load events', 'danger');
                container.innerHTML = '';
                return;
            }

            const { events, pagination } = response.data.data;

            if (!events || events.length === 0) {
                container.innerHTML = '';
                document.getElementById('no-events-message').style.display = 'block';
                document.getElementById('pagination-container').style.display = 'none';
                return;
            }

            document.getElementById('no-events-message').style.display = 'none';

            // Render events
            container.innerHTML = events.map(event => UI.createEventCard(event)).join('');

            // Update pagination
            currentPage = page;
            updatePagination(pagination);
        }

        /**
         * Update pagination
         */
        function updatePagination(pagination) {
            const paginationContainer = document.getElementById('pagination-container');
            
            if (pagination.total_pages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'block';
            document.getElementById('page-info').textContent = 
                `Page ${pagination.current_page} of ${pagination.total_pages}`;

            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');

            prevBtn.disabled = !pagination.has_prev;
            nextBtn.disabled = !pagination.has_next;

            prevBtn.onclick = () => loadEvents(pagination.current_page - 1);
            nextBtn.onclick = () => loadEvents(pagination.current_page + 1);
        }

        /**
         * Search form handler
         */
        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const query = document.getElementById('search-query').value.trim();
            
            if (query.length < 2) {
                UI.showAlert('Search query must be at least 2 characters', 'info');
                return;
            }

            searchMode = true;
            searchQuery = query;
            loadEvents(1);
        });

        // Reset search on clear button
        document.getElementById('search-form').addEventListener('reset', function() {
            searchMode = false;
            searchQuery = '';
            loadEvents(1);
        });

        // Load events on page load
        loadEvents(1);
    </script>
</body>
</html>
