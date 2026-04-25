# User-Facing Pages Documentation

## Overview
This document outlines the user-facing pages and components for the Event Management System. The public interface allows users to browse events, view details, and register for events.

## Pages Structure

```
public/
├── index.php                 # Homepage - Events listing
├── event-details.php         # Single event details page
├── register.php              # Event registration form
├── success.php               # Registration success confirmation
└── assets/
    ├── css/
    │   └── style.css        # Main stylesheet
    └── js/
        └── app.js           # JavaScript client library
```

## Page Descriptions

### 1. Homepage (index.php)
**URL**: `/eve/public/index.php`

**Features**:
- Display upcoming events in a grid layout
- Search functionality for events
- Pagination for multiple events
- Responsive design
- Quick registration buttons

**Components**:
- Event cards with:
  - Event image/placeholder
  - Event name
  - Event date
  - Brief description
  - "View Details" and "Register" buttons

**Functionality**:
- Load events via API (`/api/events.php`)
- Real-time search with debouncing
- Pagination controls
- Loading states and error handling

### 2. Event Details (event-details.php)
**URL**: `/eve/public/event-details.php?id={event_id}`

**Features**:
- Full event information display
- High-resolution event image
- Event metadata (date, time, location, status)
- Event description
- List of custom registration fields required
- Register now button

**Components**:
- Event banner/image
- Event title
- Metadata grid (start date, end date, location, status)
- Full description
- Custom fields preview
- Call-to-action button

**Functionality**:
- Fetch event details via API
- Display custom fields information
- Handle past/future event states
- Show appropriate messages

### 3. Registration Form (register.php)
**URL**: `/eve/public/register.php?event_id={event_id}`

**Features**:
- Multi-section form
- Personal information fields
- Professional information fields
- Dynamic custom fields
- Form validation
- Error messaging

**Form Sections**:

#### Personal Information
- Full Name (required)
- Email Address (required, validated)
- Phone Number (required, validated)

#### Professional Information
- Organization/Company (required)
- Designation/Job Title (optional)
- TSSIA Membership ID (optional)

#### Custom Fields (Dynamic)
- Rendered based on event configuration
- Supports multiple field types
- Validation per field type

**Functionality**:
- Fetch event and custom fields via API
- Client-side form validation
- Field type-specific rendering:
  - Text input
  - Text area
  - Email
  - Phone
  - Number
  - Date
  - Time
  - URL
  - Dropdown
  - Radio buttons
  - Checkboxes
  - File upload
- Submit registration via API
- Handle validation errors
- Redirect to success page on completion

### 4. Success Page (success.php)
**URL**: `/eve/public/success.php?registration_id={id}&event_id={event_id}`

**Features**:
- Confirmation message
- Registration details display
- Registration ID
- Event information
- Next steps information
- Email notification notice
- Call-to-action buttons

**Components**:
- Success icon animation
- Confirmation message
- Registration details box
- Important notice alert
- Action buttons (View More Events, View This Event)

**Functionality**:
- Verify registration details from URL parameters
- Fetch event information
- Display personalized confirmation
- Provide clear next steps

## Styling & Design

### Color Scheme
```css
--primary-color: #007bff (Blue)
--success-color: #28a745 (Green)
--danger-color: #dc3545 (Red)
--warning-color: #ffc107 (Orange)
--info-color: #17a2b8 (Cyan)
--secondary-color: #6c757d (Gray)
```

### Responsive Breakpoints
- Desktop: 1200px+
- Tablet: 768px - 1199px
- Mobile: < 768px

### Key UI Components
1. **Buttons**: Primary, Secondary, Success, Danger variants
2. **Forms**: Input fields, text areas, select boxes
3. **Cards**: Event cards with hover effects
4. **Alerts**: Success, danger, info, warning messages
5. **Loading States**: Spinners and loading text

## JavaScript API Client (app.js)

### EventAPI Class
Provides methods for API communication:

```javascript
// Get events
EventAPI.getEvents(type, page, limit)

// Get event details
EventAPI.getEventDetails(eventId)

// Search events
EventAPI.searchEvents(query, page, limit)

// Register for event
EventAPI.registerEvent(data)

// Login
EventAPI.login(email, password)

// Logout
EventAPI.logout()

// Verify QR code
EventAPI.verifyQR(qrCode, eventId)
```

### UI Helper Class
Provides UI utilities:

```javascript
// Show alerts
UI.showAlert(message, type, container)

// Show loading
UI.showLoading(container)

// Format dates
UI.formatDate(dateString)
UI.formatDateOnly(dateString)

// Button controls
UI.disableButton(button, loading)
UI.enableButton(button, text)

// Form operations
UI.validateForm(form)
UI.getFormData(form)
UI.clearForm(form)

// Modal operations
UI.showModal(modalId)
UI.hideModal(modalId)

// Create UI elements
UI.createEventCard(event)
UI.renderCustomField(field)
```

## Form Validation

### Client-Side Validation
- Required fields
- Email format validation
- Phone number validation (min 10 digits)
- Custom regex patterns

### Field Types Validation
- **Email**: Valid email format
- **Phone**: Numeric, minimum 10 digits
- **URL**: Valid URL format
- **Number**: Numeric value
- **Text**: Minimum/maximum length
- **Custom**: Regex pattern matching

### Error Display
- Inline error messages under fields
- Error highlighting (red border)
- Form-level validation before submission

## Email Notifications

### EmailSender Class
Manages email communications:

```php
$emailer = new EmailSender();

// Send registration confirmation
$emailer->send_registration_confirmation($email, $name, $event, $reg_id);

// Send approval notification
$emailer->send_registration_approved($email, $name, $event, $date, $location, $pass_num);

// Send rejection notification
$emailer->send_registration_rejected($email, $name, $event, $reason);

// Send event reminder
$emailer->send_event_reminder($email, $name, $event, $date, $location, $pass_num);
```

### Email Templates
Located in `templates/email-templates.php`:

1. **Registration Confirmation**
   - Confirmation message
   - Registration details
   - Status: Pending Approval
   - Next steps

2. **Registration Approved**
   - Approval confirmation
   - Ticket number
   - Event details
   - Important reminders

3. **Registration Rejected**
   - Rejection notification
   - Reason (if provided)
   - Contact information

4. **Event Reminder**
   - Event countdown
   - Event details
   - Ticket information
   - Preparation tips

## API Integration

### Event Listing
```
GET /api/events.php?type=upcoming&page=1&limit=10
```
Response:
```json
{
  "status": "success",
  "data": {
    "events": [...],
    "pagination": {
      "total_items": 50,
      "total_pages": 5,
      "current_page": 1,
      "has_prev": false,
      "has_next": true
    }
  }
}
```

### Event Details
```
GET /api/event-details.php?event_id=1
```
Response:
```json
{
  "status": "success",
  "data": {
    "event": {...},
    "custom_fields": [...]
  }
}
```

### Event Registration
```
POST /api/register.php
```
Request Body:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "organization": "ABC Corp",
  "designation": "Manager",
  "event_id": 1,
  "custom_fields": {
    "1": "value1",
    "2": "value2"
  }
}
```

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Optimization

1. **Lazy Loading**: Images loaded on demand
2. **Caching**: Static assets cached
3. **Compression**: CSS and JS minification
4. **Pagination**: Limited records per page
5. **API Optimization**: Only required data fetched

## Accessibility Features

1. **Semantic HTML**: Proper heading hierarchy
2. **ARIA Labels**: For screen readers
3. **Keyboard Navigation**: Tab-friendly forms
4. **Color Contrast**: WCAG compliant
5. **Alt Text**: For images
6. **Form Labels**: Clear labels for inputs

## Error Handling

### User-Facing Errors
- Validation errors: Inline and prominent
- API errors: Alert messages with retry option
- Network errors: Fallback with offline message
- Not found: Helpful redirect options

### Error Recovery
- Form preservation on validation error
- Retry buttons for failed operations
- Clear next steps guidance
- Support contact information

## Security Features

1. **Input Validation**: All user inputs validated
2. **CSRF Protection**: Token-based (backend)
3. **XSS Prevention**: HTML escaping
4. **Email Validation**: Format and domain check
5. **Rate Limiting**: API request throttling

## Mobile Responsiveness

### Mobile Features
- Touch-friendly buttons
- Swipe-able event cards
- Vertical layout optimization
- Mobile keyboard consideration
- Touch-friendly form fields

### Tablet Optimization
- Adjusted grid columns
- Readable font sizes
- Touch-friendly spacing
- Landscape/portrait support

## Testing Checklist

- [ ] Event listing loads correctly
- [ ] Search functionality works
- [ ] Event details display properly
- [ ] Registration form validates correctly
- [ ] Custom fields render properly
- [ ] Form submission succeeds
- [ ] Success page displays confirmation
- [ ] Error messages display clearly
- [ ] Mobile responsiveness verified
- [ ] Email notifications sent
- [ ] Form data persists on errors
- [ ] Navigation works smoothly

## Future Enhancements

1. **Advanced Search Filters**
   - Date range filtering
   - Category filtering
   - Location-based search
   - Price range filtering

2. **User Dashboard**
   - My registrations
   - Registration history
   - Ticket management
   - Profile management

3. **Social Features**
   - Event sharing
   - Social media integration
   - Event reviews/ratings
   - Attendee networking

4. **Enhanced Notifications**
   - SMS reminders
   - Push notifications
   - Calendar integration
   - Ical export

5. **Analytics**
   - Event popularity metrics
   - Registration analytics
   - Attendance tracking
   - User behavior tracking

## Support

For issues or questions:
- Contact: support@eventmanagement.local
- Documentation: /eve/README.md
- Issue Tracking: [GitHub Issues]

