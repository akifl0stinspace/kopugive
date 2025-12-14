<style>
    /* ============================================
       KopuGive - Minimalist Maroon & Gold Theme
       ============================================ */
    
    :root {
        /* Primary Colors - Maroon & Gold */
        --maroon-primary: #800020;
        --maroon-dark: #5c0016;
        --maroon-light: #a6002b;
        --gold-primary: #D4AF37;
        --gold-dark: #B8960A;
        --gold-light: #F4E5C2;
        
        /* Neutral Colors */
        --white: #FFFFFF;
        --light-gray: #F8F9FA;
        --medium-gray: #6C757D;
        --dark-gray: #343A40;
        --border-gray: #DEE2E6;
        
        /* Minimalist Palette */
        --bg-primary: #FFFFFF;
        --bg-secondary: #F8F9FA;
        --text-primary: #2C3E50;
        --text-secondary: #6C757D;
        
        /* Shadows - Subtle */
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 4px 12px rgba(0, 0, 0, 0.12);
    }
    
    /* ============================================
       Global Styles
       ============================================ */
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-primary);
        background-color: var(--bg-secondary);
    }
    
    /* ============================================
       Navigation & Sidebar
       ============================================ */
    
    .navbar-custom {
        background: var(--white);
        box-shadow: var(--shadow-sm);
        border-bottom: 2px solid var(--maroon-primary);
    }
    
    .navbar-custom .navbar-brand {
        color: var(--maroon-primary) !important;
        font-weight: 700;
        font-size: 1.5rem;
    }
    
    .navbar-custom .nav-link {
        color: var(--text-primary) !important;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 0.5rem 1rem !important;
        border-radius: 6px;
        margin: 0 0.2rem;
    }
    
    .navbar-custom .nav-link:hover,
    .navbar-custom .nav-link.active {
        background: var(--gold-light);
        color: var(--maroon-primary) !important;
    }
    
    .navbar-custom .dropdown-menu {
        border: 1px solid var(--border-gray);
        box-shadow: var(--shadow-md);
    }
    
    /* Admin Sidebar */
    .sidebar {
        min-height: 100vh;
        background: var(--white);
        box-shadow: var(--shadow-sm);
        border-right: 2px solid var(--maroon-primary);
    }
    
    .sidebar .nav-link {
        color: var(--text-primary);
        padding: 1rem 1.5rem;
        border-radius: 6px;
        margin: 0.3rem 0.5rem;
        transition: all 0.3s ease;
        font-weight: 500;
        border-left: 3px solid transparent;
    }
    
    .sidebar .nav-link:hover {
        background: var(--gold-light);
        color: var(--maroon-primary);
        border-left-color: var(--gold-primary);
    }
    
    .sidebar .nav-link.active {
        background: var(--light-gray);
        color: var(--maroon-primary);
        border-left-color: var(--maroon-primary);
        font-weight: 600;
    }
    
    .sidebar .nav-link i {
        width: 20px;
        margin-right: 10px;
        color: var(--maroon-primary);
    }
    
    /* ============================================
       Buttons
       ============================================ */
    
    .btn-primary {
        background: var(--maroon-primary);
        border-color: var(--maroon-primary);
        color: var(--white);
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: none;
    }
    
    .btn-primary:hover,
    .btn-primary:focus {
        background: var(--maroon-dark);
        border-color: var(--maroon-dark);
        box-shadow: var(--shadow-sm);
    }
    
    .btn-outline-primary {
        border-color: var(--maroon-primary);
        color: var(--maroon-primary);
        font-weight: 500;
        transition: all 0.3s ease;
        background: transparent;
    }
    
    .btn-outline-primary:hover,
    .btn-outline-primary:focus {
        background: var(--maroon-primary);
        border-color: var(--maroon-primary);
        color: var(--white);
    }
    
    .btn-secondary {
        background: var(--white);
        border: 1px solid var(--border-gray);
        color: var(--text-primary);
        font-weight: 500;
    }
    
    .btn-secondary:hover {
        background: var(--light-gray);
        border-color: var(--gold-primary);
    }
    
    /* ============================================
       Cards
       ============================================ */
    
    .card {
        border: 1px solid var(--border-gray);
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s ease;
        background: var(--white);
    }
    
    .card:hover {
        box-shadow: var(--shadow-md);
    }
    
    .card-header {
        background: var(--white);
        color: var(--maroon-primary);
        border-bottom: 2px solid var(--gold-primary);
        border-radius: 8px 8px 0 0 !important;
        font-weight: 600;
        padding: 1rem 1.5rem;
    }
    
    /* Stat Cards */
    .stat-card {
        border-left: 4px solid var(--maroon-primary);
        transition: all 0.3s ease;
        background: var(--white);
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-left-color: var(--gold-primary);
    }
    
    .stat-card .text-primary {
        color: var(--maroon-primary) !important;
    }
    
    /* Campaign Cards */
    .campaign-card {
        transition: all 0.3s ease;
        border: 1px solid var(--border-gray);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        background: var(--white);
    }
    
    .campaign-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
        border-color: var(--gold-primary);
    }
    
    .campaign-image {
        height: 200px;
        object-fit: cover;
        background: var(--light-gray);
    }
    
    /* ============================================
       Badges
       ============================================ */
    
    .badge.bg-primary {
        background: var(--maroon-primary) !important;
        font-weight: 500;
    }
    
    .badge.bg-secondary {
        background: var(--light-gray) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-gray);
        font-weight: 500;
    }
    
    .badge.bg-info {
        background: var(--gold-light) !important;
        color: var(--maroon-primary) !important;
        border: 1px solid var(--gold-primary);
        font-weight: 500;
    }
    
    /* ============================================
       Progress Bars
       ============================================ */
    
    .progress {
        background-color: var(--light-gray);
        border-radius: 10px;
        overflow: hidden;
        height: 8px;
    }
    
    .progress-bar {
        background: var(--maroon-primary);
        transition: width 1s ease;
    }
    
    .progress-bar.bg-success {
        background: var(--maroon-primary) !important;
    }
    
    .progress-large {
        height: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    /* ============================================
       Forms
       ============================================ */
    
    .form-control:focus,
    .form-select:focus {
        border-color: var(--maroon-primary);
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 32, 0.15);
    }
    
    .input-group-text {
        background: var(--white);
        border-color: var(--border-gray);
    }
    
    /* ============================================
       Tables
       ============================================ */
    
    .table-hover tbody tr:hover {
        background-color: var(--gold-light);
    }
    
    .table thead {
        background: var(--light-gray);
        color: var(--text-primary);
        font-weight: 600;
        border-bottom: 2px solid var(--maroon-primary);
    }
    
    /* ============================================
       Alerts
       ============================================ */
    
    .alert-primary {
        background-color: rgba(128, 0, 32, 0.08);
        border-color: var(--maroon-primary);
        border-left: 4px solid var(--maroon-primary);
        color: var(--maroon-dark);
    }
    
    .alert-info {
        background-color: var(--gold-light);
        border-color: var(--gold-primary);
        border-left: 4px solid var(--gold-primary);
        color: var(--text-primary);
    }
    
    /* ============================================
       Welcome Banner
       ============================================ */
    
    .welcome-banner {
        background: var(--white);
        color: var(--text-primary);
        border-radius: 8px;
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        border-left: 4px solid var(--maroon-primary);
        border-top: 2px solid var(--gold-primary);
    }
    
    .welcome-banner h3 {
        color: var(--maroon-primary);
    }
    
    /* ============================================
       Campaign Banner
       ============================================ */
    
    .campaign-banner {
        height: 400px;
        background: var(--light-gray);
        object-fit: cover;
    }
    
    /* ============================================
       Text Colors
       ============================================ */
    
    .text-primary {
        color: var(--maroon-primary) !important;
    }
    
    .text-gold {
        color: var(--gold-primary) !important;
    }
    
    .bg-primary {
        background: var(--maroon-primary) !important;
    }
    
    .bg-gold {
        background: var(--gold-light) !important;
    }
    
    /* ============================================
       Links
       ============================================ */
    
    a {
        color: var(--maroon-primary);
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    a:hover {
        color: var(--maroon-dark);
    }
    
    /* ============================================
       Pagination
       ============================================ */
    
    .pagination .page-link {
        color: var(--maroon-primary);
        border-color: var(--border-gray);
        background: var(--white);
    }
    
    .pagination .page-link:hover {
        background-color: var(--gold-light);
        border-color: var(--gold-primary);
        color: var(--maroon-primary);
    }
    
    .pagination .page-item.active .page-link {
        background-color: var(--maroon-primary);
        border-color: var(--maroon-primary);
    }
    
    /* ============================================
       Dropdown Menu
       ============================================ */
    
    .dropdown-item:hover,
    .dropdown-item:focus {
        background-color: var(--gold-light);
        color: var(--maroon-primary);
    }
    
    /* ============================================
       Accent Elements
       ============================================ */
    
    .border-primary {
        border-color: var(--maroon-primary) !important;
    }
    
    .border-gold {
        border-color: var(--gold-primary) !important;
    }
    
    /* Gold accent line for sections */
    .section-divider {
        border-top: 2px solid var(--gold-primary);
        margin: 2rem 0;
    }
    
    /* ============================================
       Print Styles
       ============================================ */
    
    @media print {
        .sidebar,
        .navbar,
        .btn,
        .no-print {
            display: none !important;
        }
        
        main {
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        .card {
            page-break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
        
        .badge {
            border: 1px solid #000;
        }
    }
    
    /* ============================================
       Responsive Design
       ============================================ */
    
    @media (max-width: 768px) {
        .navbar-custom .navbar-brand {
            font-size: 1.2rem;
        }
        
        .campaign-card {
            margin-bottom: 1rem;
        }
        
        .stat-card {
            margin-bottom: 1rem;
        }
    }
    
    /* ============================================
       Subtle Animations
       ============================================ */
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in {
        animation: fadeIn 0.4s ease-in;
    }
    
    /* ============================================
       Custom Scrollbar (Webkit browsers)
       ============================================ */
    
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--light-gray);
    }
    
    ::-webkit-scrollbar-thumb {
        background: var(--medium-gray);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: var(--maroon-primary);
    }
</style>
