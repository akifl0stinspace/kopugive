<style>
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }
    .sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin: 0.2rem 0;
    }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
        background: rgba(255,255,255,0.2);
        color: white;
    }
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    /* Report Page Enhancements */
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    /* Print Styles */
    @media print {
        .sidebar, .btn, .no-print {
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
    
    /* Filter Section Styling */
    .form-select, .form-control {
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    /* Table Enhancements */
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
    
    /* Progress Bar Animation */
    .progress-bar {
        transition: width 1s ease;
    }
</style>

