<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Event Management System</title>
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
            <!-- Success Container -->
            <div id="success-container" class="success-container" style="display: none;">
                <div class="success-icon">✓</div>
                <div class="success-title">Registration Successful! 🎉</div>
                <div class="success-message">
                    Thank you for registering! Your registration has been submitted successfully.
                    <br><br>
                    <strong>What happens next?</strong><br>
                    Your registration is pending approval. You'll receive a confirmation email once it's approved.
                </div>

                <div class="success-details">
                    <p><strong>Registration ID:</strong> <span id="reg-id" style="font-family: monospace; font-weight: bold;"></span></p>
                    <p><strong>Event:</strong> <span id="event-name"></span></p>
                    <p><strong>Status:</strong> <span style="color: #ffc107;">Pending Approval</span></p>
                </div>

                <div class="alert alert-info">
                    <div class="alert-icon">ℹ</div>
                    <div class="alert-content">
                        <div class="alert-title">Important</div>
                        <div>
                            Please check your email for further instructions. Make sure to add our email address to your contacts to avoid missing important updates.
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <a href="index.php" class="btn btn-primary btn-lg" style="flex: 1; text-decoration: none;">
                        View More Events
                    </a>
                    <a href="event-details.php?id=" id="event-link" class="btn btn-secondary btn-lg" style="flex: 1; text-decoration: none;">
                        View This Event
                    </a>
                </div>
            </div>

            <!-- Error Container -->
            <div id="error-container" class="success-container" style="display: none;">
                <div style="font-size: 80px; color: #dc3545; margin-bottom: 20px; animation: shake 0.5s;">❌</div>
                <div class="success-title" style="color: #dc3545;">Registration Failed</div>
                <div class="success-message" id="error-message"></div>
                <a href="index.php" class="btn btn-primary btn-lg btn-block" style="text-decoration: none; margin-top: 20px;">
                    Back to Events
                </a>
            </div>

            <!-- Loading Container -->
            <div id="loading-container" class="success-container" style="text-align: center;">
                <div class="loading"></div>
                <p class="loading-text" style="margin-top: 10px;">Verifying your registration...</p>
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
        /**
         * Get parameters from URL
         */
        const urlParams = new URLSearchParams(window.location.search);
        const registrationId = urlParams.get('registration_id');
        const eventId = urlParams.get('event_id');

        /**
         * Verify registration
         */
        async function verifyRegistration() {
            if (!registrationId || !eventId) {
                showError('Invalid registration parameters');
                return;
            }

            try {
                // Simulate verification delay
                await new Promise(resolve => setTimeout(resolve, 1000));

                // Get event details to show in success message
                const response = await EventAPI.getEventDetails(eventId);

                if (response.success) {
                    const event = response.data.data.event;
                    showSuccess(registrationId, event);
                } else {
                    showSuccess(registrationId, null);
                }

            } catch (error) {
                console.error('Verification error:', error);
                showError('An error occurred during verification');
            }
        }

        /**
         * Show success message
         */
        function showSuccess(regId, event) {
            document.getElementById('loading-container').style.display = 'none';
            document.getElementById('error-container').style.display = 'none';
            document.getElementById('success-container').style.display = 'block';

            // Set registration ID
            document.getElementById('reg-id').textContent = regId;

            // Set event name
            if (event) {
                document.getElementById('event-name').textContent = event.event_name;
                document.getElementById('event-link').href = `event-details.php?id=${event.event_id}`;
            } else {
                document.getElementById('event-name').textContent = 'Event';
                document.getElementById('event-link').style.display = 'none';
            }

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        /**
         * Show error message
         */
        function showError(message) {
            document.getElementById('loading-container').style.display = 'none';
            document.getElementById('success-container').style.display = 'none';
            document.getElementById('error-container').style.display = 'block';

            document.getElementById('error-message').innerHTML = message || 
                'Your registration could not be verified. Please contact support if this issue persists.';

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Add shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
                20%, 40%, 60%, 80% { transform: translateX(10px); }
            }
        `;
        document.head.appendChild(style);

        // Verify on page load
        verifyRegistration();
    </script>
</body>
</html>
