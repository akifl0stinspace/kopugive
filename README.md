# KopuGive - MRSM Kota Putra Donation System

A web-based donation management system designed for MRSM Kota Putra to streamline and centralize donation campaigns, replacing manual WhatsApp and Excel-based tracking.

![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)
![License](https://img.shields.io/badge/License-MRSM_KP-green)

---

## 📋 Project Information

**Project Title:** KopuGive: A Web-Based Donation System for MRSM Kota Putra

**Developer:** Wan Nur Syahira binti Che Wan Abd Aziz

**Institution:** Universiti Teknologi MARA (UiTM)

**Program:** Bachelor of Information Systems (Hons.) Information Systems Engineering

**Year:** June 2025

**Managed By:** MUAFAKAT Committee, MRSM Kota Putra

---

## 🎯 Project Overview

### Background

MRSM Kota Putra currently manages donations manually through:
- WhatsApp groups for communication
- Excel spreadsheets for record-keeping
- Manual receipt verification
- Scattered donation information

This leads to:
- ❌ Slow and repetitive processes
- ❌ Human errors and data loss
- ❌ Communication overload
- ❌ No centralized tracking
- ❌ Poor transparency for donors

### Solution: KopuGive

A centralized web-based platform that:
- ✅ Automates donation record-keeping
- ✅ Provides real-time campaign progress tracking
- ✅ Streamlines receipt management
- ✅ Improves transparency and accessibility
- ✅ Reduces administrative workload

---

## ✨ Key Features

### For Administrators (MUAFAKAT Committee)

1. **Campaign Management**
   - Create and manage donation campaigns
   - Set target amounts and deadlines
   - Track progress in real-time
   - Publish campaign updates

2. **Donation Verification**
   - Review submitted donations
   - Verify receipts
   - Approve or reject donations
   - Update campaign totals automatically

3. **Donor Management**
   - View all registered donors
   - Track donation history
   - Generate donor reports

4. **Reports & Analytics**
   - Dashboard with statistics
   - Monthly donation trends
   - Top campaigns and donors
   - Visual charts and graphs
   - Export capabilities

### For Donors

1. **Browse Campaigns**
   - View active donation campaigns
   - See detailed descriptions and goals
   - Track real-time progress
   - Read campaign updates

2. **Easy Donations**
   - Online donation form
   - Multiple payment methods
   - Upload receipts (JPG, PNG, PDF)
   - Add personal messages
   - Option for anonymous donations

3. **Donation Tracking**
   - View donation history
   - Check verification status
   - Download receipts
   - Track contribution impact

4. **User-Friendly Interface**
   - Responsive design (mobile & desktop)
   - Intuitive navigation
   - Modern, clean UI
   - Fast and accessible

---

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Custom styling
- **Bootstrap 5.3** - Responsive UI framework
- **JavaScript** - Interactive elements
- **Font Awesome 6.4** - Icons
- **Chart.js 4.4** - Data visualization

### Backend
- **PHP 8.x** - Server-side logic
- **MySQL 8.0** - Database management
- **PDO** - Database access layer

### Development Tools
- **XAMPP** - Local development environment
- **VS Code** - Code editor
- **phpMyAdmin** - Database management GUI

### Security
- Password hashing (bcrypt)
- PDO prepared statements (SQL injection prevention)
- Input sanitization (XSS prevention)
- Session-based authentication
- File upload validation

---

## 📁 Project Structure

```
kopugive/
├── admin/                  # Admin panel
│   ├── dashboard.php      # Admin dashboard
│   ├── campaigns.php      # Campaign management
│   ├── campaign_add.php   # Create campaign
│   ├── donations.php      # Donation verification
│   ├── reports.php        # Reports & analytics
│   └── includes/          # Shared admin components
├── auth/                   # Authentication
│   ├── login.php          # Login page
│   ├── register.php       # Registration page
│   └── logout.php         # Logout handler
├── config/                 # Configuration files
│   ├── config.php         # General settings
│   └── database.php       # Database connection
├── database/               # Database files
│   ├── schema.sql         # Database structure
│   └── seed.sql           # Sample data
├── donor/                  # Donor interface
│   ├── dashboard.php      # Donor dashboard
│   ├── campaigns.php      # Browse campaigns
│   ├── campaign_view.php  # Campaign details + donate
│   └── my_donations.php   # Donation history
├── includes/               # Helper functions
│   └── functions.php      # Utility functions
├── payment/                # Payment gateway
│   └── process_payment.php # Payment processing (placeholder)
├── uploads/                # User uploads
│   ├── campaigns/         # Campaign banners
│   └── receipts/          # Donation receipts
├── logs/                   # System logs
│   └── php_errors.log
├── index.php              # Public homepage
├── README.md              # This file
├── INSTALLATION.md        # Installation guide
├── SETUP_TUTORIAL.md      # Beginner's setup tutorial
└── TECH_STACK.md          # Technical documentation
```

---

## 🚀 Quick Start

### Prerequisites
- XAMPP (with PHP 8.x and MySQL 8.0)
- Web browser (Chrome, Firefox, Edge, Safari)
- Text editor (VS Code recommended)

### Installation (Quick)

1. **Install XAMPP** and start Apache + MySQL

2. **Create database:**
   - Open http://localhost/phpmyadmin
   - Create database named `kopugive`
   - Import `database/schema.sql`
   - Import `database/seed.sql` (optional)

3. **Copy project files:**
   ```
   Copy kopugive folder to C:\xampp\htdocs\
   ```

4. **Access the system:**
   ```
   http://localhost/kopugive/
   ```

### Default Login Credentials

**Admin:**
- Email: `admin@mrsmkp.edu.my`
- Password: `admin123`

**Donor (Demo):**
- Email: `ahmad@example.com`
- Password: `admin123`

---

## 📖 Documentation

For detailed setup instructions, please refer to:

- **[SETUP_TUTORIAL.md](SETUP_TUTORIAL.md)** - Complete beginner-friendly guide
  - How to download and install XAMPP
  - How to set up MySQL database
  - Step-by-step project setup
  - Troubleshooting common issues

- **[INSTALLATION.md](INSTALLATION.md)** - Technical installation reference
  - System requirements
  - Configuration details
  - Security recommendations

- **[TECH_STACK.md](TECH_STACK.md)** - Technology documentation
  - Detailed tech stack explanation
  - Architecture and design patterns
  - Database schema
  - Future enhancements

---

## 👥 User Roles

### Administrator (MUAFAKAT Committee)
- Full system access
- Campaign management
- Donation verification
- User management
- Reports and analytics
- System settings

### Donor
- Browse campaigns
- Make donations
- Upload receipts
- View donation history
- Track campaign progress
- Manage profile

---

## 🔐 Security Features

- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Secure file upload validation
- ✅ Activity logging
- ✅ HTTPS ready

---

## 📊 Database Schema

### Main Tables

1. **users** - User accounts (admins and donors)
2. **campaigns** - Donation campaigns
3. **donations** - Donation records
4. **campaign_updates** - Campaign announcements
5. **activity_logs** - System activity tracking
6. **settings** - System configuration

For complete schema, see `database/schema.sql`

---

## 🎨 Screenshots

### Public Homepage
- Campaign showcase
- Real-time statistics
- Responsive design

### Admin Dashboard
- Overview statistics
- Recent donations
- Top campaigns
- Quick actions

### Donor Interface
- User-friendly donation form
- Campaign progress tracking
- Donation history
- Receipt management

---

## 🔄 Development Methodology

**SDLC Model:** Waterfall

**Completed Phases:**
1. ✅ Requirements Gathering & Analysis
2. ✅ System Design
3. ✅ Implementation

**Future Phases:**
4. Testing
5. Deployment
6. Maintenance

---

## 🚀 Future Enhancements

### Technical Improvements
- [ ] Real payment gateway integration (FPX, Stripe)
- [ ] Email notifications (PHPMailer)
- [ ] SMS notifications
- [ ] Two-factor authentication
- [ ] API development for mobile app
- [ ] Advanced analytics dashboard
- [ ] Export to PDF/Excel
- [ ] Automated receipts generation

### Features
- [ ] Recurring donations
- [ ] Campaign categories and tags
- [ ] Social media integration
- [ ] Donor leaderboards
- [ ] Campaign milestones
- [ ] Multi-language support
- [ ] Dark mode

---

## 🐛 Known Limitations

1. **Payment Gateway:** Currently placeholder/demo mode
   - Manual receipt upload required
   - No automated payment processing
   - Requires integration with actual payment provider

2. **Email Notifications:** Not implemented
   - No automated email confirmations
   - Requires SMTP configuration

3. **Mobile App:** Web-only
   - Responsive web design only
   - No native mobile application

---

## 🤝 Contributing

This is a Final Year Project for MRSM Kota Putra. For any suggestions or improvements:

1. Contact the MUAFAKAT committee
2. Submit feedback through proper channels
3. Follow institutional guidelines

---

## 📄 License

© 2025 MRSM Kota Putra. All rights reserved.

This project is developed for internal use by MRSM Kota Putra and managed by the MUAFAKAT committee.

---

## 👏 Acknowledgments

- **MUAFAKAT Committee** - For project requirements and support
- **MRSM Kota Putra** - For the opportunity to develop this system
- **UiTM** - For academic guidance and resources
- **Project Supervisor** - For mentorship and feedback
- **Open Source Community** - For libraries and frameworks

---

## 📞 Contact

**Developer:** Wan Nur Syahira binti Che Wan Abd Aziz

**Institution:** Universiti Teknologi MARA (UiTM)

**Program:** Bachelor of Information Systems (Hons.) Information Systems Engineering

**For Support:** Contact MUAFAKAT Committee, MRSM Kota Putra

---

## 🌟 Project Goals Achievement

| Goal | Status |
|------|--------|
| Centralized donation management | ✅ Achieved |
| Automated record-keeping | ✅ Achieved |
| Real-time progress tracking | ✅ Achieved |
| Receipt management system | ✅ Achieved |
| Admin dashboard & reports | ✅ Achieved |
| User-friendly donor interface | ✅ Achieved |
| Mobile responsive design | ✅ Achieved |
| Secure authentication | ✅ Achieved |

---

**Made with ❤️ for MRSM Kota Putra**

*Connecting hearts, building futures together through transparent and convenient donations.*
