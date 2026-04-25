<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration - Event Management System</title>
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

            <!-- Registration Form -->
            <div class="registration-container">
                <div id="loading-message" style="text-align: center; display: none;">
                    <div class="loading"></div>
                    <p class="loading-text" style="margin-top: 10px;">Loading event details...</p>
                </div>

                <div id="form-container" style="display: none;">
                    <h2 id="event-title" style="margin-bottom: 10px; color: #333;"></h2>
                    <p id="event-date" style="color: #666; margin-bottom: 30px;"></p>

                    <form id="registration-form">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">👤 Personal Information</div>

                            <div class="form-group required">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" placeholder="John Doe" required>
                                <div class="form-error"></div>
                            </div>

                            <div class="form-group required">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="john@example.com" required>
                                <div class="form-error"></div>
                            </div>

                            <div class="form-group required">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="1234567890" required>
                                <div class="form-error"></div>
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="form-section">
                            <div class="form-section-title">💼 Professional Information</div>

                            <div class="form-group required">
                                <label for="organization">Organization / Company</label>
                                <input type="text" id="organization" name="organization" placeholder="ABC Corporation" required>
                                <div class="form-error"></div>
                            </div>

                            <div class="form-group">
                                <label for="designation">Designation / Job Title</label>
                                <input type="text" id="designation" name="designation" placeholder="Manager">
                                <div class="form-error"></div>
                            </div>

                            <div class="form-group">
                                <label for="tssia_membership_id">TSSIA Membership ID (if applicable)</label>
                                <input type="text" id="tssia_membership_id" name="tssia_membership_id" placeholder="TSSIA123">
                                <div class="form-error"></div>
                            </div>
                        </div>

                        <!-- Custom Fields Section -->
                        <div id="custom-fields-section" class="form-section" style="display: none;">
                            <div class="form-section-title">📋 Additional Information</div>
                            <div id="custom-fields-container"></div>
                        </div>

                        <!-- Terms -->
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="terms" name="terms" required>
                                <span>I agree to the terms and conditions and privacy policy</span>
                            </label>
                            <div class="form-error"></div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-btn">
                            Complete Registration
                        </button>
                    </form>
                </div>

                <div id="no-event-message" style="text-align: center; display: none;">
                    <div style="font-size: 60px; margin-bottom: 20px;">❌</div>
                    <h2>Event Not Found</h2>
                    <p>The event you're trying to register for doesn't exist or has been removed.</p>
                    <a href="index.php" class="btn btn-primary mt-20">Back to Events</a>
                </div>
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
        const eventId = urlParams.get('event_id');

        /**
         * Load event and prepare form
         */
        async function loadEventForm() {
            const loadingMsg = document.getElementById('loading-message');
            const formContainer = document.getElementById('form-container');
            const noEventMsg = document.getElementById('no-event-message');

            loadingMsg.style.display = 'block';

            if (!eventId) {
                loadingMsg.style.display = 'none';
                noEventMsg.style.display = 'block';
                return;
            }

            const response = await EventAPI.getEventDetails(eventId);

            if (!response.success) {
                UI.showAlert('Failed to load event details', 'danger');
                loadingMsg.style.display = 'none';
                noEventMsg.style.display = 'block';
                return;
            }

            const event = response.data.data.event;
            const customFields = response.data.data.custom_fields || [];

            if (!event) {
                loadingMsg.style.display = 'none';
                noEventMsg.style.display = 'block';
                return;
            }

            // Set event title and date
            document.getElementById('event-title').textContent = event.event_name;
            document.getElementById('event-date').textContent = 
                `📅 ${UI.formatDate(event.start_date_time)}`;

            // Set form event ID
            const form = document.getElementById('registration-form');
            const eventIdInput = document.createElement('input');
            eventIdInput.type = 'hidden';
            eventIdInput.name = 'event_id';
            eventIdInput.value = eventId;
            form.appendChild(eventIdInput);

            // Render custom fields
            if (customFields.length > 0) {
                const customFieldsSection = document.getElementById('custom-fields-section');
                const customFieldsContainer = document.getElementById('custom-fields-container');
                
                customFieldsSection.style.display = 'block';
                customFieldsContainer.innerHTML = customFields
                    .map(field => UI.renderCustomField(field))
                    .join('');
            }

            loadingMsg.style.display = 'none';
            formContainer.style.display = 'block';
        }

        /**
         * Handle form submission
         */
        document.getElementById('registration-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate form
            if (!UI.validateForm(this)) {
                UI.showAlert('Please fix the errors in the form', 'danger');
                return;
            }

            // Validate terms
            if (!document.getElementById('terms').checked) {
                UI.showAlert('You must agree to the terms and conditions', 'danger');
                return;
            }

            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            UI.disableButton(submitBtn);

            // Get form data
            const formData = UI.getFormData(this);

            // Submit registration
            const response = await EventAPI.registerEvent(formData);

            if (response.success) {
                const registrationId = response.data.data.registration_id;
                
                // Redirect to success page
                window.location.href = `success.php?registration_id=${registrationId}&event_id=${eventId}`;
            } else {
                const errorMessage = response.data?.message || response.data?.data?.message || 'Registration failed';
                UI.showAlert(errorMessage, 'danger');
                UI.enableButton(submitBtn, originalText);
            }
        });

        // Load on page load
        loadEventForm();
    </script>
</body>
</html>
