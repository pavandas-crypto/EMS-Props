/**
 * API Client for Event Management System
 */

const API_BASE_URL = '/eve/api';

class EventAPI {
    /**
     * Make API request
     */
    static async request(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
            const result = await response.json();

            return {
                success: response.ok,
                status: response.status,
                data: result
            };
        } catch (error) {
            console.error('API Error:', error);
            return {
                success: false,
                status: 500,
                data: { status: 'error', message: 'Network error occurred' }
            };
        }
    }

    /**
     * Get all events
     */
    static async getEvents(type = 'upcoming', page = 1, limit = 10) {
        return this.request(`/events.php?type=${type}&page=${page}&limit=${limit}`);
    }

    /**
     * Get event details
     */
    static async getEventDetails(eventId) {
        return this.request(`/event-details.php?event_id=${eventId}`);
    }

    /**
     * Search events
     */
    static async searchEvents(query, page = 1, limit = 10) {
        return this.request(`/events.php?type=search&q=${encodeURIComponent(query)}&page=${page}&limit=${limit}`);
    }

    /**
     * Register for event
     */
    static async registerEvent(data) {
        return this.request('/register.php', 'POST', data);
    }

    /**
     * Login
     */
    static async login(email, password) {
        return this.request('/login.php', 'POST', { email, password });
    }

    /**
     * Logout
     */
    static async logout() {
        return this.request('/logout.php', 'POST');
    }

    /**
     * Verify QR code
     */
    static async verifyQR(qrCode, eventId) {
        return this.request('/verify-qr.php', 'POST', { qr_code: qrCode, event_id: eventId });
    }
}

/**
 * UI Helper Functions
 */
const UI = {
    /**
     * Show alert
     */
    showAlert(message, type = 'info', container = 'alert-container') {
        const alertDiv = document.getElementById(container);
        if (!alertDiv) return;

        const alertHTML = `
            <div class="alert alert-${type}">
                <div class="alert-icon">
                    ${type === 'success' ? '✓' : type === 'danger' ? '✕' : 'ℹ'}
                </div>
                <div class="alert-content">
                    <div class="alert-title">${type === 'success' ? 'Success' : type === 'danger' ? 'Error' : 'Info'}</div>
                    <div>${message}</div>
                </div>
            </div>
        `;

        alertDiv.innerHTML = alertHTML;
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        if (type === 'success') {
            setTimeout(() => alertDiv.innerHTML = '', 5000);
        }
    },

    /**
     * Show loading
     */
    showLoading(container) {
        const element = document.querySelector(container);
        if (!element) return;
        element.innerHTML = '<div class="loading-container"><div class="loading"></div><p class="loading-text">Loading...</p></div>';
    },

    /**
     * Format date
     */
    formatDate(dateString) {
        const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    },

    /**
     * Format date only
     */
    formatDateOnly(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('en-US', options);
    },

    /**
     * Disable button
     */
    disableButton(button, loading = true) {
        button.disabled = true;
        if (loading) {
            button.innerHTML = '<span class="loading"></span> Processing...';
        }
    },

    /**
     * Enable button
     */
    enableButton(button, text) {
        button.disabled = false;
        button.innerHTML = text;
    },

    /**
     * Validate form
     */
    validateForm(form) {
        const errors = {};
        const formData = new FormData(form);

        for (let [name, value] of formData.entries()) {
            const field = form.querySelector(`[name="${name}"]`);
            const formGroup = field.closest('.form-group');

            // Remove existing error
            formGroup.classList.remove('error');

            // Required validation
            if (field.hasAttribute('required') && !value.trim()) {
                errors[name] = `${field.placeholder || name} is required`;
                formGroup.classList.add('error');
                formGroup.querySelector('.form-error').textContent = errors[name];
                continue;
            }

            // Email validation
            if (field.type === 'email' && value && !this.isValidEmail(value)) {
                errors[name] = 'Invalid email address';
                formGroup.classList.add('error');
                formGroup.querySelector('.form-error').textContent = errors[name];
                continue;
            }

            // Phone validation
            if (field.type === 'tel' && value && !this.isValidPhone(value)) {
                errors[name] = 'Invalid phone number';
                formGroup.classList.add('error');
                formGroup.querySelector('.form-error').textContent = errors[name];
                continue;
            }
        }

        return Object.keys(errors).length === 0;
    },

    /**
     * Validate email
     */
    isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    /**
     * Validate phone
     */
    isValidPhone(phone) {
        const regex = /^\d{10,}$/;
        return regex.test(phone.replace(/[^\d]/g, ''));
    },

    /**
     * Get form data
     */
    getFormData(form) {
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            const match = key.match(/^([^[\]]+)\[([^\]]+)\]$/);
            if (match) {
                const root = match[1];
                const nestedKey = match[2];
                if (!data[root]) {
                    data[root] = {};
                }
                data[root][nestedKey] = value;
            } else {
                data[key] = value;
            }
        }

        return data;
    },

    /**
     * Clear form
     */
    clearForm(form) {
        form.reset();
        form.querySelectorAll('.form-group').forEach(group => {
            group.classList.remove('error');
        });
    },

    /**
     * Show modal
     */
    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
        }
    },

    /**
     * Hide modal
     */
    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
        }
    },

    /**
     * Create event card
     */
    createEventCard(event) {
        return `
            <div class="event-card">
                <div class="event-image">
                    ${event.image_url 
                        ? `<img src="${event.image_url}" alt="${event.event_name}">` 
                        : `<div class="event-image-placeholder">📅</div>`
                    }
                </div>
                <div class="event-content">
                    <div class="event-name">${event.event_name}</div>
                    <div class="event-date">
                        <span>📅</span>
                        <span>${this.formatDateOnly(event.start_date_time)}</span>
                    </div>
                    <div class="event-description">${event.description || 'No description available'}</div>
                    <div class="event-footer">
                        <a href="event-details.php?id=${event.event_id}" class="btn btn-primary" style="flex: 1;">View Details</a>
                        <a href="register.php?event_id=${event.event_id}" class="btn btn-success" style="flex: 1;">Register</a>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Render custom field
     */
    renderCustomField(field) {
        let html = `
            <div class="form-group ${field.required ? 'required' : ''}">
                <label for="field-${field.custom_id}">${field.field_name}</label>
        `;

        const attrs = {
            id: `field-${field.custom_id}`,
            name: `custom_fields[${field.custom_id}]`,
            placeholder: field.placeholder || ''
        };

        const attrString = Object.entries(attrs)
            .map(([k, v]) => `${k}="${v}"`)
            .join(' ');

        switch (field.field_type) {
            case 'textarea':
                html += `<textarea ${attrString} ${field.required ? 'required' : ''}></textarea>`;
                break;

            case 'email':
                html += `<input type="email" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            case 'phone':
                html += `<input type="tel" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            case 'number':
                html += `<input type="number" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            case 'date':
                html += `<input type="date" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            case 'time':
                html += `<input type="time" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            case 'url':
                html += `<input type="url" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            case 'dropdown':
                html += `<select ${attrString} ${field.required ? 'required' : ''}>
                    <option value="">-- Select --</option>`;
                if (field.options) {
                    field.options.forEach(option => {
                        html += `<option value="${option}">${option}</option>`;
                    });
                }
                html += `</select>`;
                break;

            case 'radio':
                if (field.options) {
                    field.options.forEach(option => {
                        html += `<div style="margin: 10px 0;">
                            <label style="display: inline-flex; align-items: center; margin: 0;">
                                <input type="radio" name="custom_fields[${field.custom_id}]" value="${option}" ${field.required ? 'required' : ''} />
                                <span style="margin-left: 8px;">${option}</span>
                            </label>
                        </div>`;
                    });
                }
                break;

            case 'checkbox':
                html += `<input type="checkbox" ${attrString} />`;
                break;

            case 'file':
                html += `<input type="file" ${attrString} ${field.required ? 'required' : ''} />`;
                break;

            default:
                html += `<input type="text" ${attrString} ${field.required ? 'required' : ''} />`;
        }

        html += `
                <div class="form-error"></div>
            </div>
        `;

        return html;
    }
};

/**
 * Ready state listener
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add any global event listeners here if needed
    console.log('Event Management System loaded');
});
