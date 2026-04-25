<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details - Event Management System</title>
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

            <!-- Back Button -->
            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Back to Events</a>

            <!-- Event Detail -->
            <div id="event-container" class="event-detail-container">
                <div class="loading-container">
                    <div class="loading"></div>
                    <p class="loading-text">Loading event details...</p>
                </div>
            </div>

            <!-- No Event Message -->
            <div id="no-event-message" style="text-align: center; padding: 60px 20px; display: none;">
                <div style="font-size: 60px; margin-bottom: 20px;">❌</div>
                <h2>Event Not Found</h2>
                <p>The event you're looking for doesn't exist or has been removed.</p>
                <a href="index.php" class="btn btn-primary mt-20">Back to Events</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 Event Management System. All rights reserved.</p>
        <p>Streamline your event management experience</p>
    </footer>

    <script src="assets/js/app.js?v=2"></script>
    <script>
        /**
         * Get event ID from URL
         */
        const urlParams = new URLSearchParams(window.location.search);
        const eventId = urlParams.get('id');

        /**
         * Load event details
         */
        async function loadEventDetails() {
            if (!eventId) {
                document.getElementById('event-container').style.display = 'none';
                document.getElementById('no-event-message').style.display = 'block';
                return;
            }

            const response = await EventAPI.getEventDetails(eventId);

            if (!response.success) {
                UI.showAlert('Failed to load event details', 'danger');
                document.getElementById('event-container').innerHTML = '';
                document.getElementById('no-event-message').style.display = 'block';
                return;
            }

            const event = response.data.data.event;
            const customFields = response.data.data.custom_fields || [];

            if (!event) {
                document.getElementById('event-container').innerHTML = '';
                document.getElementById('no-event-message').style.display = 'block';
                return;
            }

            // Render event details
            renderEventDetails(event, customFields);
        }

        /**
         * Render event details
         */
        function renderEventDetails(event, customFields) {
            const container = document.getElementById('event-container');

            const startDate = new Date(event.start_date_time);
            const endDate = new Date(event.end_date_time);
            const now = new Date();
            const isFuture = startDate > now;

            let html = `
                <div class="event-detail-image">
                    ${event.image_url 
                        ? `<img src="${event.image_url.startsWith('http://') || event.image_url.startsWith('https://') || event.image_url.startsWith('/') ? event.image_url : '../' + event.image_url}" alt="${event.event_name}">` 
                        : `<div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; font-size: 120px;">📅</div>`
                    }
                </div>
                <div class="event-detail-content">
                    <div class="event-detail-header">
                        <div class="event-detail-title">${event.event_name}</div>
                    </div>

                    <div class="event-meta">
                        <div class="meta-item">
                            <div class="meta-icon">📅</div>
                            <div class="meta-content">
                                <h4>Start Date & Time</h4>
                                <p>${UI.formatDate(event.start_date_time)}</p>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">🏁</div>
                            <div class="meta-content">
                                <h4>End Date & Time</h4>
                                <p>${UI.formatDate(event.end_date_time)}</p>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">📍</div>
                            <div class="meta-content">
                                <h4>Location</h4>
                                <p>${event.address || 'To be announced'}</p>
                            </div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-icon">${isFuture ? '✓' : '✗'}</div>
                            <div class="meta-content">
                                <h4>Status</h4>
                                <p>${isFuture ? 'Upcoming' : 'Past Event'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="event-detail-description">
                        <strong>Description:</strong><br>
                        ${event.description || 'No description available'}
                    </div>
            `;

            if (isFuture) {
                html += `
                    <div style="display: flex; gap: 10px;">
                        <a href="register.php?event_id=${event.event_id}" class="btn btn-primary btn-lg" style="flex: 1; text-decoration: none;">
                            Register Now
                        </a>
                    </div>
                `;
            } else {
                html += `
                    <div class="alert alert-info">
                        <div class="alert-icon">ℹ</div>
                        <div class="alert-content">
                            This event has already passed. Registration is no longer available.
                        </div>
                    </div>
                `;
            }

            if (customFields && customFields.length > 0) {
                html += `
                    <div class="custom-fields-container">
                        <div class="custom-fields-title">📋 Additional Registration Fields</div>
                        <p>When registering, you'll be asked to fill in the following information:</p>
                        <ul style="margin-top: 10px;">
                `;

                customFields.forEach(field => {
                    html += `<li>${field.field_name} ${field.required ? '<span style="color: red;">*</span>' : ''}</li>`;
                });

                html += `
                        </ul>
                    </div>
                `;
            }

            html += `</div>`;

            container.innerHTML = html;
        }

        // Load on page load
        loadEventDetails();
    </script>
</body>
</html>
