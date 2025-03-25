<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get database instance
$db = Database::getInstance();

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get sites count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM sites");
$stmt->execute();
$sitesCount = $stmt->get_result()->fetch_assoc()['total'];

// Get visits count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM visits");
$stmt->execute();
$visitsCount = $stmt->get_result()->fetch_assoc()['total'];

// Get clicks count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM clicks");
$stmt->execute();
$clicksCount = $stmt->get_result()->fetch_assoc()['total'];

// Get bot count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM visits WHERE is_bot = 1");
$stmt->execute();
$botCount = $stmt->get_result()->fetch_assoc()['total'];

// Get anomalies count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM anomalies WHERE is_resolved = 0");
$stmt->execute();
$anomaliesCount = $stmt->get_result()->fetch_assoc()['total'];

// Get recent visits
$stmt = $db->prepare("
    SELECT v.*, s.name as site_name
    FROM visits v
    JOIN sites s ON v.site_id = s.id
    ORDER BY v.visit_time DESC
    LIMIT 10
");
$stmt->execute();
$recentVisits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recent clicks
$stmt = $db->prepare("
    SELECT c.*, v.ip_address, s.name as site_name
    FROM clicks c
    JOIN visits v ON c.visit_id = v.id
    JOIN sites s ON v.site_id = s.id
    ORDER BY c.click_time DESC
    LIMIT 10
");
$stmt->execute();
$recentClicks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recent anomalies
$stmt = $db->prepare("
    SELECT a.*, s.name as site_name
    FROM anomalies a
    JOIN sites s ON a.site_id = s.id
    ORDER BY a.detected_at DESC
    LIMIT 10
");
$stmt->execute();
$recentAnomalies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get conversion rates by type
$stmt = $db->prepare("
    SELECT 
        c.click_type,
        COUNT(*) as total_clicks,
        (SELECT COUNT(*) FROM visits) as total_visits,
        (COUNT(*) / (SELECT COUNT(*) FROM visits)) * 100 as conversion_rate
    FROM clicks c
    GROUP BY c.click_type
");
$stmt->execute();
$conversionRates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Page title
$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Hệ Thống Tracking IP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card-dashboard {
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-dashboard .card-body {
            padding: 1.5rem;
        }
        .card-dashboard .icon {
            font-size: 2.5rem;
            color: #3961AA;
        }
        .card-dashboard .count {
            font-size: 2rem;
            font-weight: 700;
        }
        .card-dashboard .title {
            font-size: 1rem;
            color: #6c757d;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .dropdown-menu {
            right: 0;
            left: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 d-none d-md-block sidebar">
                <div class="text-center mb-4">
                    <h5 class="text-white">Tracking IP</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sites.php">
                            <i class="fas fa-globe"></i> Websites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="visits.php">
                            <i class="fas fa-eye"></i> Visits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clicks.php">
                            <i class="fas fa-mouse-pointer"></i> Clicks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="anomalies.php">
                            <i class="fas fa-exclamation-triangle"></i> Anomalies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fraud.php">
                            <i class="fas fa-ban"></i> Fraud Patterns
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <!-- Top navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="index.php">Dashboard</a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
                
                <!-- Dashboard content -->
                <div class="row">
                    <!-- Sites count -->
                    <div class="col-md-4 col-xl-3">
                        <div class="card card-dashboard bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?php echo $sitesCount; ?></div>
                                        <div class="title">Websites</div>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Visits count -->
                    <div class="col-md-4 col-xl-3">
                        <div class="card card-dashboard bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?php echo $visitsCount; ?></div>
                                        <div class="title">Visits</div>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clicks count -->
                    <div class="col-md-4 col-xl-3">
                        <div class="card card-dashboard bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?php echo $clicksCount; ?></div>
                                        <div class="title">Clicks</div>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-mouse-pointer"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bot count -->
                    <div class="col-md-4 col-xl-3">
                        <div class="card card-dashboard bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="count"><?php echo $botCount; ?></div>
                                        <div class="title">Bots Detected</div>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <!-- Conversion rates chart -->
                    <div class="col-md-6">
                        <div class="card card-dashboard">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Conversion Rates</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="conversionChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Anomalies -->
                    <div class="col-md-6">
                        <div class="card card-dashboard">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Anomalies</h5>
                                <span class="badge badge-danger"><?php echo $anomaliesCount; ?> unresolved</span>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentAnomalies) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Site</th>
                                                <th>Type</th>
                                                <th>Severity</th>
                                                <th>Detected</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentAnomalies as $anomaly): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($anomaly['site_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    switch ($anomaly['anomaly_type']) {
                                                        case 'high_cr':
                                                            echo 'High Conversion';
                                                            break;
                                                        case 'pattern':
                                                            echo 'Pattern';
                                                            break;
                                                        case 'timing':
                                                            echo 'Timing';
                                                            break;
                                                        case 'cluster':
                                                            echo 'Cluster';
                                                            break;
                                                        default:
                                                            echo htmlspecialchars($anomaly['anomaly_type']);
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                    switch ($anomaly['severity']) {
                                                        case 'low':
                                                            echo 'info';
                                                            break;
                                                        case 'medium':
                                                            echo 'warning';
                                                            break;
                                                        case 'high':
                                                            echo 'danger';
                                                            break;
                                                        default:
                                                            echo 'secondary';
                                                    }
                                                    ?>">
                                                        <?php echo ucfirst(htmlspecialchars($anomaly['severity'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($anomaly['detected_at'])); ?></td>
                                                <td>
                                                    <?php if ($anomaly['is_resolved']): ?>
                                                    <span class="badge badge-success">Resolved</span>
                                                    <?php else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted my-4">No anomalies detected yet.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="anomalies.php" class="btn btn-sm btn-outline-primary">View All Anomalies</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <!-- Recent visits -->
                    <div class="col-md-6">
                        <div class="card card-dashboard">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Visits</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentVisits) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>IP</th>
                                                <th>Site</th>
                                                <th>Browser</th>
                                                <th>Location</th>
                                                <th>Time</th>
                                                <th>Bot</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentVisits as $visit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($visit['ip_address']); ?></td>
                                                <td><?php echo htmlspecialchars($visit['site_name']); ?></td>
                                                <td><?php echo htmlspecialchars($visit['browser']); ?></td>
                                                <td><?php echo htmlspecialchars($visit['city'] . ', ' . $visit['country']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($visit['visit_time'])); ?></td>
                                                <td>
                                                    <?php if ($visit['is_bot']): ?>
                                                    <span class="badge badge-danger">Bot</span>
                                                    <?php else: ?>
                                                    <span class="badge badge-success">Human</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted my-4">No visits recorded yet.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="visits.php" class="btn btn-sm btn-outline-primary">View All Visits</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent clicks -->
                    <div class="col-md-6">
                        <div class="card card-dashboard">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Clicks</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentClicks) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>IP</th>
                                                <th>Site</th>
                                                <th>Type</th>
                                                <th>URL</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentClicks as $click): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($click['ip_address']); ?></td>
                                                <td><?php echo htmlspecialchars($click['site_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    switch ($click['click_type']) {
                                                        case 'phone':
                                                            echo '<span class="badge badge-info">Phone</span>';
                                                            break;
                                                        case 'zalo':
                                                            echo '<span class="badge badge-primary">Zalo</span>';
                                                            break;
                                                        case 'messenger':
                                                            echo '<span class="badge badge-success">Messenger</span>';
                                                            break;
                                                        case 'maps':
                                                            echo '<span class="badge badge-warning">Maps</span>';
                                                            break;
                                                        default:
                                                            echo htmlspecialchars($click['click_type']);
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($click['click_url']); ?>" target="_blank" class="text-truncate d-inline-block" style="max-width: 150px;">
                                                        <?php echo htmlspecialchars($click['click_url']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($click['click_time'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted my-4">No clicks recorded yet.</p>
                                <?php endif; ?>
                                <div class="text-center mt-3">
                                    <a href="clicks.php" class="btn btn-sm btn-outline-primary">View All Clicks</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <footer class="mt-5 mb-3">
                    <div class="text-center">
                        <p class="text-muted">&copy; <?php echo date('Y'); ?> IP Tracking System</p>
                    </div>
                </footer>
            </main>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        // Conversion rates chart
        var ctx = document.getElementById('conversionChart').getContext('2d');
        var conversionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    foreach ($conversionRates as $rate) {
                        switch ($rate['click_type']) {
                            case 'phone':
                                echo "'Phone', ";
                                break;
                            case 'zalo':
                                echo "'Zalo', ";
                                break;
                            case 'messenger':
                                echo "'Messenger', ";
                                break;
                            case 'maps':
                                echo "'Maps', ";
                                break;
                            default:
                                echo "'" . $rate['click_type'] . "', ";
                        }
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Conversion Rate (%)',
                    data: [
                        <?php 
                        foreach ($conversionRates as $rate) {
                            echo round($rate['conversion_rate'], 2) . ', ';
                        }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(255, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel + '%';
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
