<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: loginpage.php");
    exit();
}

include "Procedures.php";
$proc = new Procedures();
$conn = $proc->getConnection();

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Get user initials for avatar
$name_parts = explode(' ', $_SESSION['name']);
$initials = strtoupper(substr($name_parts[0], 0, 1));
if (isset($name_parts[1])) {
    $initials .= strtoupper(substr($name_parts[1], 0, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <div class="sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="sidebar-logo-small">V</div>
            <div class="sidebar-title">VIRMS</div>
        </div>

        <!-- User Info -->
        <div class="sidebar-user">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div class="user-info">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                <p class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'User'); ?></p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="impound.php" class="sidebar-item <?php echo $current_page == 'impound.php' ? 'active' : ''; ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 20V10"></path>
                    <path d="M12 20V4"></path>
                    <path d="M6 20v-6"></path>
                </svg>
                <span>Impound Vehicle</span>
            </a>

            <a href="records.php" class="sidebar-item <?php echo $current_page == 'records.php' ? 'active' : ''; ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>View Records</span>
                <?php
                // Optional: Show count of impounded vehicles
                $count_sql = "SELECT COUNT(*) as total FROM impounded_vehicles WHERE status='impounded'";
                $count = $conn->query($count_sql)->fetch_assoc()['total'];
                if ($count > 0):
                ?>
                <span class="sidebar-badge"><?php echo $count; ?></span>
                <?php endif; ?>
            </a>

            <a href="release.php" class="sidebar-item <?php echo $current_page == 'release.php' ? 'active' : ''; ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <span>Release Vehicle</span>
            </a>

            <a href="owners.php" class="sidebar-item <?php echo $current_page == 'owners.php' ? 'active' : ''; ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Manage Owners</span>
            </a>

            <a href="auditlog.php" class="sidebar-item <?php echo $current_page == 'auditlog.php' ? 'active' : ''; ?>">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span>Audit Log</span>
            </a>

            <!-- Logout -->
            <a href="logout.php" class="sidebar-item logout">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Log Out</span>
            </a>
        </nav>
    </div>
</body>
</html>