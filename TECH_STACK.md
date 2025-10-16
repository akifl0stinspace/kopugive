# KopuGive - Technical Stack Documentation

## Overview

KopuGive is a web-based donation management system built for MRSM Kota Putra using a traditional LAMP/WAMP stack architecture.

---

## Technology Stack

### Frontend Technologies

#### 1. **HTML5**
- Semantic markup for better accessibility
- Form validation
- Modern HTML5 elements

#### 2. **CSS3**
- Custom styling
- Responsive design
- CSS Grid and Flexbox layouts
- Animations and transitions

#### 3. **Bootstrap 5.3.0**
- **Purpose**: Responsive UI framework
- **Features Used**:
  - Grid system for responsive layouts
  - Pre-built components (cards, modals, alerts)
  - Form styling and validation
  - Navigation components
  - Utility classes
- **CDN**: https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/

#### 4. **Font Awesome 6.4.0**
- **Purpose**: Icon library
- **Features**: Over 7000+ icons for UI elements
- **CDN**: https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/

#### 5. **JavaScript (Vanilla)**
- Client-side form validation
- Dynamic UI interactions
- AJAX requests (future enhancement)

#### 6. **Chart.js 4.4.0**
- **Purpose**: Data visualization
- **Usage**: 
  - Monthly donation trends (Line chart)
  - Campaign status distribution (Doughnut chart)
  - Analytics dashboards
- **CDN**: https://cdn.jsdelivr.net/npm/chart.js@4.4.0/

---

### Backend Technologies

#### 1. **PHP 8.x**
- **Purpose**: Server-side scripting language
- **Features Used**:
  - Object-oriented programming
  - PDO for database access
  - Session management
  - File upload handling
  - Security functions (password hashing, input sanitization)

**Key PHP Features:**
```php
- Password hashing (password_hash, password_verify)
- PDO prepared statements (SQL injection prevention)
- Session management (authentication)
- File system operations
- Error handling and logging
```

#### 2. **MySQL 8.0**
- **Purpose**: Relational database management system
- **Database Name**: `kopugive`
- **Charset**: `utf8mb4_unicode_ci` (supports emojis and international characters)

**Database Schema:**
- `users` - User accounts (admins and donors)
- `campaigns` - Donation campaigns
- `donations` - Donation records
- `campaign_updates` - Campaign announcements
- `activity_logs` - System activity tracking
- `settings` - System configuration

---

### Development Environment

#### 1. **XAMPP**
- **Version**: Latest (PHP 8.x compatible)
- **Components**:
  - Apache HTTP Server (Web server)
  - MySQL Database Server
  - PHP Interpreter
  - phpMyAdmin (Database management)

#### 2. **Visual Studio Code**
- **Purpose**: Code editor
- **Recommended Extensions**:
  - PHP Intelephense
  - MySQL
  - HTML CSS Support
  - Prettier - Code formatter
  - GitLens (if using Git)

---

## Architecture & Design Patterns

### 1. **MVC-Inspired Structure**
```
kopugive/
├── config/          # Configuration files
├── includes/        # Helper functions and utilities
├── admin/           # Admin panel (Controllers + Views)
├── donor/           # Donor interface (Controllers + Views)
├── auth/            # Authentication (Controllers + Views)
├── database/        # Database schemas and seeds
├── uploads/         # User-uploaded files
└── assets/          # Static assets (future)
```

### 2. **Design Patterns Used**

**Singleton Pattern**
- Database connection management
- One instance per request

**Session Management**
- User authentication state
- Flash messages
- CSRF protection ready

**Repository Pattern (Implicit)**
- Database queries organized by entity
- Reusable query functions

---

## Security Features

### 1. **Authentication & Authorization**
```php
- Password hashing (bcrypt via password_hash)
- Session-based authentication
- Role-based access control (Admin/Donor)
- Protected routes
```

### 2. **Input Validation & Sanitization**
```php
- sanitize() function for XSS prevention
- PDO prepared statements (SQL injection prevention)
- File upload validation
- Email validation
- CSRF tokens (recommended for forms)
```

### 3. **File Upload Security**
```php
- File type validation
- File size limits (5MB)
- Unique filename generation
- Secure storage location
- MIME type checking
```

### 4. **Session Security**
```php
- HttpOnly cookies
- Session timeout (1 hour)
- Secure session configuration
```

---

## Key Features Implementation

### 1. **Campaign Management**
- CRUD operations for campaigns
- Status management (draft, active, completed, closed)
- Target vs raised amount tracking
- Campaign updates/announcements
- Banner image uploads

### 2. **Donation Processing**
- Multi-step donation form
- Receipt upload capability
- Payment method selection
- Anonymous donation option
- Donation verification workflow
- Real-time progress tracking

### 3. **User Management**
- User registration and login
- Profile management
- Role-based dashboards
- Activity logging

### 4. **Reporting & Analytics**
- Dashboard statistics
- Monthly donation trends
- Top campaigns and donors
- Exportable reports
- Visual charts and graphs

### 5. **Receipt Management**
- Upload donation receipts (JPG, PNG, PDF)
- View and verify receipts
- Secure storage
- File validation

---

## Payment Gateway Integration (Placeholder)

### Current Implementation
- **Status**: Placeholder/Mock implementation
- **Location**: `payment/process_payment.php`

### Recommended Payment Providers (Malaysia)

1. **FPX (Financial Process Exchange)**
   - Malaysian online banking payment gateway
   - Widely used in Malaysia
   - Supports all major Malaysian banks

2. **iPay88**
   - Malaysian payment gateway
   - Supports FPX, credit cards, e-wallets
   - Website: https://www.ipay88.com.my/

3. **eGHL (eGHLPay)**
   - Established Malaysian payment provider
   - Multiple payment methods
   - Website: https://www.eghl.com/

4. **Stripe**
   - International payment processor
   - Credit/debit card payments
   - Easy integration
   - Website: https://stripe.com/

### Integration Steps (Future)
1. Register merchant account
2. Obtain API credentials
3. Install SDK/library
4. Implement payment flow
5. Handle callbacks and webhooks
6. Test in sandbox mode
7. Deploy to production

---

## Database Design

### Key Tables

**users**
- Stores admin and donor accounts
- Password hashed with bcrypt
- Role-based access (enum: 'admin', 'donor')

**campaigns**
- Campaign information
- Target and current amounts
- Status tracking
- Date ranges

**donations**
- Links donors to campaigns
- Amount and payment details
- Verification workflow
- Receipt storage path

**activity_logs**
- Audit trail
- User actions tracking
- IP and user agent logging

---

## File Structure

```
kopugive/
│
├── config/
│   ├── config.php           # General configuration
│   └── database.php         # Database connection
│
├── includes/
│   └── functions.php        # Helper functions
│
├── auth/
│   ├── login.php           # Login page
│   ├── register.php        # Registration page
│   └── logout.php          # Logout handler
│
├── admin/
│   ├── dashboard.php       # Admin dashboard
│   ├── campaigns.php       # Campaign management
│   ├── campaign_add.php    # Create campaign
│   ├── donations.php       # Donation verification
│   ├── reports.php         # Reports & analytics
│   └── includes/
│       ├── admin_styles.php    # Shared styles
│       └── admin_sidebar.php   # Sidebar component
│
├── donor/
│   ├── dashboard.php       # Donor dashboard
│   ├── campaigns.php       # Browse campaigns
│   ├── campaign_view.php   # Campaign details + donate
│   └── my_donations.php    # Donation history
│
├── payment/
│   └── process_payment.php # Payment gateway (placeholder)
│
├── database/
│   ├── schema.sql          # Database structure
│   └── seed.sql            # Sample data
│
├── uploads/
│   ├── campaigns/          # Campaign banners
│   └── receipts/           # Donation receipts
│
├── logs/
│   └── php_errors.log      # Error logs
│
├── index.php               # Public homepage
├── INSTALLATION.md         # Installation guide
├── TECH_STACK.md          # This file
└── README.md              # Project overview
```

---

## Browser Compatibility

- **Chrome**: ✅ Latest
- **Firefox**: ✅ Latest
- **Safari**: ✅ Latest
- **Edge**: ✅ Latest
- **Mobile**: ✅ Responsive design supports iOS and Android

---

## Performance Considerations

### Database
- Indexed columns for faster queries
- Efficient JOIN operations
- Query optimization

### Frontend
- CDN for libraries (fast loading)
- Minimal custom CSS
- Lazy loading for images (future)

### Caching
- Browser caching for static assets
- Database query caching (future)

---

## Future Enhancements

### Technical Improvements
1. **Implement AJAX**
   - Asynchronous form submissions
   - Real-time notifications
   - Live search and filtering

2. **Add Composer**
   - Dependency management
   - PHPMailer for emails
   - Payment SDK integration

3. **API Development**
   - RESTful API endpoints
   - Mobile app support
   - Third-party integrations

4. **Advanced Security**
   - Two-factor authentication
   - CSRF tokens on all forms
   - Rate limiting
   - Security headers

5. **Performance**
   - Redis caching
   - Image optimization
   - Minification of CSS/JS
   - Database query caching

6. **Testing**
   - Unit tests (PHPUnit)
   - Integration tests
   - Browser testing (Selenium)

---

## Development Methodology

**SDLC**: Waterfall Model

**Phases Completed:**
1. ✅ Requirements Gathering & Analysis
2. ✅ System Design
3. ✅ Implementation

**Pending (as per FYP scope):**
4. Testing
5. Deployment
6. Maintenance

---

## Resources & Documentation

### Official Documentation
- PHP: https://www.php.net/docs.php
- MySQL: https://dev.mysql.com/doc/
- Bootstrap: https://getbootstrap.com/docs/5.3/
- Chart.js: https://www.chartjs.org/docs/

### Learning Resources
- W3Schools: https://www.w3schools.com/
- MDN Web Docs: https://developer.mozilla.org/
- PHP The Right Way: https://phptherightway.com/

---

## Project Information

**Project Name**: KopuGive
**Full Title**: A Web-Based Donation System for MRSM Kota Putra
**Developer**: Wan Nur Syahira binti Che Wan Abd Aziz
**Institution**: Universiti Teknologi MARA (UiTM)
**Program**: Bachelor of Information Systems (Hons.) Information Systems Engineering
**Year**: 2025

**Supervisor**: [Insert Supervisor Name]
**Committee**: MUAFAKAT (MRSM Kota Putra)

---

## License & Credits

© 2025 MRSM Kota Putra. All rights reserved.

**Third-Party Libraries:**
- Bootstrap (MIT License)
- Font Awesome (Font Awesome Free License)
- Chart.js (MIT License)

