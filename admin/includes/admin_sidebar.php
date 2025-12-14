<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-md-block sidebar p-3 position-fixed">
            <div class="text-center mb-4">
                <h4 class="text-primary"><i class="fas fa-hand-holding-heart"></i> KopuGive</h4>
                <small class="text-muted">Admin Panel</small>
            </div>
            
            <hr>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'campaigns.php' || basename($_SERVER['PHP_SELF']) == 'campaign_add.php' || basename($_SERVER['PHP_SELF']) == 'campaign_edit.php' || basename($_SERVER['PHP_SELF']) == 'campaign_view.php' ? 'active' : '' ?>" href="campaigns.php">
                        <i class="fas fa-bullhorn"></i> Campaigns
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'donations.php' ? 'active' : '' ?>" href="donations.php">
                        <i class="fas fa-hand-holding-usd"></i> Donations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'donors.php' ? 'active' : '' ?>" href="donors.php">
                        <i class="fas fa-users"></i> Donors
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' || basename($_SERVER['PHP_SELF']) == 'generate_report.php' ? 'active' : '' ?>" href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
            
            <hr class="mt-auto">
            
            <div class="mt-auto">
                <div class="mb-2 text-muted">
                    <i class="fas fa-user-circle me-2"></i>
                    <small><?= htmlspecialchars($_SESSION['full_name']) ?></small>
                </div>
                <a href="../auth/logout.php" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </nav>
    </div>
</div>
