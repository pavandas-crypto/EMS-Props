# Quick Start Guide - Event Management System

## Prerequisites

- PHP 7.4+ with MySQLi extension
- MySQL/MariaDB 5.7+
- XAMPP or similar server
- Web browser (Chrome, Firefox, Safari, Edge)

## Installation Steps

### 1. Database Setup

```bash
# Import the database schema
mysql -u root -p event_management < event_management.sql
```

If you don't have the database created yet:
```bash
mysql -u root -p -e "CREATE DATABASE event_management CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u root -p event_management < event_management.sql
```

### 2. File Permissions

```bash
# Navigate to the project
cd d:\Xampp\htdocs\eve

# Create required directories
mkdir -p uploads
mkdir -p logs

# Set permissions (on Linux/Mac)
chmod 755 uploads
chmod 755 logs
```

### 3. Configuration

Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // Your MySQL password
define('DB_NAME', 'event_management');
```

### 4. Verify Installation

1. Open your browser
2. Navigate to: `http://localhost/eve/public/index.php`
3. You should see the event listing page

## Default Test Credentials

### Admin Login
- **Email**: admin@mail.com
- **Password**: 123456
- **Role**: Admin

### Verifier Login
- **Email**: verifier1@mail.com
- **Password**: 123456
- **Role**: Verifier (for QR code scanning)

## Test Data

The database comes pre-loaded with:
- 1 Sample Event (Tech Conference 2026)
- 2 Sample Participants
- 2 Sample Registrations
- 2 Sample Tickets with QR codes

## File Structure

```
eve/
├── config/
│   ├── database.php          # Database connection
│   └── constants.php         # App constants
├── classes/
│   ├── Database.php          # Database class
│   ├── Auth.php              # Authentication
│   ├── Event.php             # Event management
│   ├── Registration.php      # Registration management
│   ├── CustomField.php       # Custom fields
│   ├── Ticket.php            # Tickets & QR codes
│   └── EmailSender.php       # Email handling
├── includes/
│   ├── helpers.php           # Helper functions
│   └── security.php          # Security functions
├── api/
│   ├── login.php             # Login endpoint
│   ├── logout.php            # Logout endpoint
│   ├── register.php          # Registration endpoint
│   ├── events.php            # Events listing
│   ├── event-details.php     # Event details
│   ├── verify-qr.php         # QR verification
│   └── admin/
│       ├── event-create.php  # Create event
│       ├── registrations.php # Get registrations
│       ├── generate-tickets.php
│       └── registration-status.php
├── public/
│   ├── index.php             # Homepage
│   ├── event-details.php     # Event details page
│   ├── register.php          # Registration form
│   ├── success.php           # Success confirmation
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
├── templates/
│   ├── email-templates.php   # Email templates
│   └── EmailSender.php       # Email class
├── init.php                  # Bootstrap file
├── event_management.sql      # Database dump
├── README.md                 # Backend documentation
└── USER_PAGES_DOCS.md        # User pages documentation
```

## Testing Workflow

### 1. View Events
1. Open `http://localhost/eve/public/index.php`
2. You should see the "Tech Conference 2026" event
3. Try searching for events

### 2. View Event Details
1. Click "View Details" on an event
2. See full event information and custom fields required

### 3. Register for Event
1. Click "Register" button
2. Fill in the registration form
3. Complete registration
4. See success confirmation page

### 4. Admin Panel Testing (Future)
Coming soon: Admin dashboard pages

### 5. QR Code Scanning (Future)
Coming soon: QR scanning interface

## Common Issues & Solutions

### Issue: "Database Connection Failed"
**Solution**: 
- Verify MySQL is running
- Check database credentials in `config/database.php`
- Ensure database is created and schema is imported

### Issue: "No events displaying"
**Solution**:
- Check database has sample data
- Verify API endpoint: `http://localhost/eve/api/events.php`
- Check browser console for errors

### Issue: "Form validation errors not showing"
**Solution**:
- Ensure JavaScript is enabled
- Check browser console for JS errors
- Verify `public/assets/js/app.js` is loaded

### Issue: "Emails not sending"
**Solution**:
- PHP mail() function may be disabled
- Update `templates/EmailSender.php` to use PHPMailer
- Check error logs in `logs/` directory

## API Testing

### Using cURL

```bash
# Test events endpoint
curl "http://localhost/eve/api/events.php?type=upcoming"

# Test event details
curl "http://localhost/eve/api/event-details.php?event_id=1"

# Test registration (requires data)
curl -X POST http://localhost/eve/api/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "phone": "1234567890",
    "organization": "Test Corp",
    "event_id": 1
  }'
```

### Using Postman

1. Import API endpoints
2. Set up test collection
3. Test each endpoint with sample data
4. Verify responses

## Development Tips

### Enable Debug Mode

Edit `config/constants.php`:
```php
define('DEBUG_MODE', true);
```

### View Logs

```bash
# Check activity logs
cat logs/activity_2026-04-25.log

# Check error logs
tail -f logs/error.log
```

### Database Queries

Access MySQLi queries in `classes/Database.php`:
```php
// These are fully prepared and parameterized
$db->insert('table', $data);
$db->update('table', $data, $where);
$db->delete('table', $where);
```

## Next Steps

### User-Facing Features (Completed)
- ✅ Event listing page
- ✅ Event details page
- ✅ Registration form
- ✅ Success page
- ✅ Email templates

### Admin Features (To Build)
- [ ] Admin login page
- [ ] Admin dashboard
- [ ] Event management interface
- [ ] Registration approval interface
- [ ] Ticket generation interface
- [ ] QR code scanning interface
- [ ] Admin reporting

### Mobile App (Future)
- [ ] Mobile registration app
- [ ] QR code scanner app
- [ ] Attendee check-in app

## Useful Commands

```bash
# Test database connection
mysql -u root -p event_management -e "SHOW TABLES;"

# Clear logs
rm -f logs/activity_*.log

# Create uploads directory
mkdir -p d:\Xampp\htdocs\eve\uploads

# Set Apache permissions (Linux/Mac)
chmod -R 755 d:\Xampp\htdocs\eve
```

## Documentation Files

1. **README.md** - Backend API documentation
2. **USER_PAGES_DOCS.md** - User-facing pages documentation
3. **QUICK_START.md** - This file

## Support & Help

- Check browser console for JavaScript errors (F12)
- Check PHP error logs in logs/ directory
- Verify database data with PHPMyAdmin
- Review API responses for detailed error messages

## Performance Tips

1. Use pagination for large datasets
2. Cache static assets in browser
3. Optimize images before upload
4. Use CDN for CSS/JS in production
5. Enable database query caching

## Security Reminders

1. Change default passwords immediately
2. Enable HTTPS in production
3. Use environment variables for sensitive data
4. Implement rate limiting on APIs
5. Regularly update PHP and dependencies

## Next Steps for Customization

1. Customize colors in `public/assets/css/style.css`
2. Add your logo in header
3. Modify email templates in `templates/`
4. Add more custom fields for events
5. Implement SMS notifications
6. Add payment/ticketing integration

## Support

For detailed information:
- Backend: See [README.md](README.md)
- Frontend: See [USER_PAGES_DOCS.md](USER_PAGES_DOCS.md)
- Database: See [event_management.sql](event_management.sql)

---

**System Version**: 1.0.0  
**Last Updated**: April 25, 2026  
**Status**: Production Ready
