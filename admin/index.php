<li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="interactions.php">
                            <i class="fas fa-phone-alt"></i> Tương tác
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_site.php">
                            <i class="fas fa-globe"></i> Quản lý Website
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="buttons.php">
                            <i class="fas fa-comment-dots"></i> Tùy chỉnh nút
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bot_settings.php">
                            <i class="fas fa-robot"></i> Cài đặt chống Bot
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="documentation.php">
                            <i class="fas fa-book"></i> Hướng dẫn
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php">
                            <i class="fas fa-question-circle"></i> Hỗ trợ
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main content -->
            <div class="col-md-10 py-3">
                <?php if (count($sites) == 0): ?>
                    <div class="alert alert-info">
                        <h4>Chưa có website nào được thêm</h4>
                        <p>Bạn chưa thêm website nào để theo dõi. Hãy thêm website đầu tiên để bắt đầu tracking.</p>
                        <a href="add_site.php" class="btn btn-primary">Thêm website</a>
                    </div>
                <?php elseif ($current_site_id > 0): ?>
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-0">Dashboard</h2>
                            <p class="text-muted">Thống kê tương tác & truy cập: <?php echo $title_period; ?></p>
                        </div>
                        <div class="d-flex">
                            <select class="form-select me-2" onchange="window.location='index.php?site_id=<?php echo $current_site_id; ?>&period='+this.value">
                                <option value="day" <?php echo $period == 'day' ? 'selected' : ''; ?>>Hôm nay</option>
                                <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>7 ngày qua</option>
                                <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>30 ngày qua</option>
                                <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>365 ngày qua</option>
                            </select>
                            <select class="form-select" onchange="window.location='index.php?site_id='+this.value+'&period=<?php echo $period; ?>'">
                                <?php foreach ($sites as $site): ?>
                                    <option value="<?php echo $site['id']; ?>" <?php echo $current_site_id == $site['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($site['site_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Stats Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card primary mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Tổng lượt truy cập</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_visits']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card success mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Khách truy cập thực</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_unique_visitors']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card warning mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Bot/Automation</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_bot_visits']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card info mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Tỷ lệ thật/tổng</h6>
                                        <h2 class="mt-2 mb-0">
                                            <?php 
                                                $total = $dashboard_data['overview']['total_visits'];
                                                $unique = $dashboard_data['overview']['total_unique_visitors'];
                                                echo ($total > 0) ? round(($unique / $total) * 100) : 0; 
                                            ?>%
                                        </h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-percent"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Interaction Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card danger mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Bấm gọi điện</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_tel']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-phone-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card purple mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Bấm Zalo</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_zalo']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fab fa-rocketchat"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card orange mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Bấm Messenger</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_messenger']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fab fa-facebook-messenger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card primary mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title text-muted mb-0">Bấm xem địa chỉ</h6>
                                        <h2 class="mt-2 mb-0"><?php echo number_format($dashboard_data['overview']['total_address']); ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart and Top Pages -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card dashboard-card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Lượt truy cập theo thời gian</h5>
                                </div>
                                <div class="card-body">
                                    <div class="visits-chart-container">
                                        <canvas id="visitsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card dashboard-card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Top trang được xem</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($dashboard_data['top_pages'])): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                            <p>Chưa có tương tác nào</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Loại</th>
                                                        <th>Giá trị</th>
                                                        <th>IP</th>
                                                        <th>Thiết bị</th>
                                                        <th>Vị trí</th>
                                                        <th>Thời gian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dashboard_data['recent_interactions'] as $interaction): ?>
                                                        <tr>
                                                            <td>
                                                                <?php
                                                                    $iconClass = '';
                                                                    $typeText = '';
                                                                    switch ($interaction['interaction_type']) {
                                                                        case 'tel':
                                                                            $iconClass = 'fas fa-phone-alt text-danger';
                                                                            $typeText = 'Gọi điện';
                                                                            break;
                                                                        case 'zalo':
                                                                            $iconClass = 'fab fa-rocketchat text-primary';
                                                                            $typeText = 'Zalo';
                                                                            break;
                                                                        case 'messenger':
                                                                            $iconClass = 'fab fa-facebook-messenger text-info';
                                                                            $typeText = 'Messenger';
                                                                            break;
                                                                        case 'address':
                                                                            $iconClass = 'fas fa-map-marker-alt text-success';
                                                                            $typeText = 'Địa chỉ';
                                                                            break;
                                                                    }
                                                                ?>
                                                                <span class="<?php echo $iconClass; ?>"></span> <?php echo $typeText; ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($interaction['target_value']); ?></td>
                                                            <td><?php echo htmlspecialchars($interaction['ip']); ?></td>
                                                            <td>
                                                                <?php 
                                                                    $device = $interaction['os'] . ' / ' . $interaction['browser'];
                                                                    echo htmlspecialchars($device); 
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php 
                                                                    $location = [];
                                                                    if (!empty($interaction['city'])) $location[] = $interaction['city'];
                                                                    if (!empty($interaction['country'])) $location[] = $interaction['country'];
                                                                    echo htmlspecialchars(implode(', ', $location));
                                                                ?>
                                                            </td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($interaction['timestamp'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($current_site_id > 0 && !empty($dashboard_data)): ?>
    <script>
        // Biểu đồ lượt truy cập
        var visitsCtx = document.getElementById('visitsChart').getContext('2d');
        var visitsChart = new Chart(visitsCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php
                        foreach ($dashboard_data['chart_data'] as $day) {
                            echo "'" . date('d/m', strtotime($day['date'])) . "',";
                        }
                    ?>
                ],
                datasets: [
                    {
                        label: 'Tất cả truy cập',
                        data: [
                            <?php
                                foreach ($dashboard_data['chart_data'] as $day) {
                                    echo $day['visits'] . ",";
                                }
                            ?>
                        ],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Khách thực',
                        data: [
                            <?php
                                foreach ($dashboard_data['chart_data'] as $day) {
                                    echo $day['unique_visitors'] . ",";
                                }
                            ?>
                        ],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Bot',
                        data: [
                            <?php
                                foreach ($dashboard_data['chart_data'] as $day) {
                                    echo $day['bot_visits'] . ",";
                                }
                            ?>
                        ],
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Biểu đồ hệ điều hành
        var osCtx = document.getElementById('osChart').getContext('2d');
        var osChart = new Chart(osCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php
                        foreach ($dashboard_data['device_stats'] as $os) {
                            echo "'" . $os['device_os'] . "',";
                        }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php
                            foreach ($dashboard_data['device_stats'] as $os) {
                                echo $os['count'] . ",";
                            }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Biểu đồ trình duyệt
        var browserCtx = document.getElementById('browserChart').getContext('2d');
        var browserChart = new Chart(browserCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php
                        foreach ($dashboard_data['browser_stats'] as $browser) {
                            echo "'" . $browser['browser'] . "',";
                        }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php
                            foreach ($dashboard_data['browser_stats'] as $browser) {
                                echo $browser['count'] . ",";
                            }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
        
        // Biểu đồ nhà mạng
        var networkCtx = document.getElementById('networkChart').getContext('2d');
        var networkChart = new Chart(networkCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php
                        foreach ($dashboard_data['network_stats'] as $network) {
                            echo "'" . $network['network_provider'] . "',";
                        }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php
                            foreach ($dashboard_data['network_stats'] as $network) {
                                echo $network['count'] . ",";
                            }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html> mb-3"></i>
                                            <p>Chưa có dữ liệu</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <tbody>
                                                    <?php foreach ($dashboard_data['top_pages'] as $page): ?>
                                                        <tr>
                                                            <td>
                                                                <?php
                                                                    $url = htmlspecialchars(urldecode($page['page']));
                                                                    $displayUrl = strlen($url) > 40 ? substr($url, 0, 37) . '...' : $url;
                                                                    echo $displayUrl;
                                                                ?>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="badge bg-primary rounded-pill"><?php echo $page['count']; ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Device Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card dashboard-card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Hệ điều hành</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="osChart" height="260"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card dashboard-card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Trình duyệt</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="browserChart" height="260"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card dashboard-card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Nhà mạng</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="networkChart" height="260"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Interactions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card dashboard-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Tương tác gần đây</h5>
                                    <a href="interactions.php?site_id=<?php echo $current_site_id; ?>" class="btn btn-sm btn-primary">Xem tất cả</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($dashboard_data['recent_interactions'])): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x<?php
session_start();

// Import config & functions
require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT username, email, is_admin FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();
} catch(PDOException $e) {
    logError("Error fetching user info: " . $e->getMessage());
    die("Lỗi hệ thống");
}

// Lấy danh sách website của người dùng
try {
    $stmt = $conn->prepare("SELECT * FROM sites WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $sites = $stmt->fetchAll();
} catch(PDOException $e) {
    logError("Error fetching sites: " . $e->getMessage());
    $sites = [];
}

// Xác định website đang xem
$current_site_id = isset($_GET['site_id']) ? intval($_GET['site_id']) : (count($sites) > 0 ? $sites[0]['id'] : 0);

// Thời gian thống kê
$period = isset($_GET['period']) ? $_GET['period'] : 'week';
$end_date = date('Y-m-d');

switch ($period) {
    case 'day':
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $title_period = 'Hôm nay';
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $title_period = '30 ngày qua';
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        $title_period = '365 ngày qua';
        break;
    default: // week
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $title_period = '7 ngày qua';
}

// Lấy thống kê nếu có website
$dashboard_data = [];
if ($current_site_id > 0) {
    try {
        // Lấy thông tin tổng quan
        $stmt = $conn->prepare("
            SELECT 
                SUM(visits) as total_visits,
                SUM(unique_visitors) as total_unique_visitors,
                SUM(bot_visits) as total_bot_visits,
                SUM(tel_interactions) as total_tel,
                SUM(zalo_interactions) as total_zalo,
                SUM(messenger_interactions) as total_messenger,
                SUM(address_interactions) as total_address
            FROM daily_stats 
            WHERE site_id = :site_id AND date BETWEEN :start_date AND :end_date
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $overview = $stmt->fetch();
        
        if (!$overview || $overview['total_visits'] === null) {
            $overview = [
                'total_visits' => 0,
                'total_unique_visitors' => 0,
                'total_bot_visits' => 0,
                'total_tel' => 0,
                'total_zalo' => 0,
                'total_messenger' => 0,
                'total_address' => 0
            ];
        }
        
        // Lấy dữ liệu theo ngày cho biểu đồ
        $stmt = $conn->prepare("
            SELECT 
                date,
                visits,
                unique_visitors,
                bot_visits,
                tel_interactions,
                zalo_interactions,
                messenger_interactions,
                address_interactions
            FROM daily_stats 
            WHERE site_id = :site_id AND date BETWEEN :start_date AND :end_date
            ORDER BY date ASC
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $chart_data = $stmt->fetchAll();
        
        // Lấy top 10 trang được xem nhiều nhất
        $stmt = $conn->prepare("
            SELECT page, COUNT(*) as count
            FROM visits
            WHERE site_id = :site_id AND timestamp BETWEEN :start_date AND :end_date AND is_bot = 0
            GROUP BY page
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date . ' 00:00:00');
        $stmt->bindParam(':end_date', $end_date . ' 23:59:59');
        $stmt->execute();
        $top_pages = $stmt->fetchAll();
        
        // Lấy thống kê theo thiết bị
        $stmt = $conn->prepare("
            SELECT 
                CASE 
                    WHEN os LIKE '%Android%' THEN 'Android'
                    WHEN os LIKE '%iOS%' THEN 'iOS'
                    WHEN os LIKE '%Windows%' THEN 'Windows'
                    WHEN os LIKE '%Mac%' THEN 'Mac OS'
                    ELSE 'Khác'
                END as device_os,
                COUNT(*) as count
            FROM visits
            WHERE site_id = :site_id AND timestamp BETWEEN :start_date AND :end_date AND is_bot = 0
            GROUP BY device_os
            ORDER BY count DESC
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date . ' 00:00:00');
        $stmt->bindParam(':end_date', $end_date . ' 23:59:59');
        $stmt->execute();
        $device_stats = $stmt->fetchAll();
        
        // Lấy thống kê theo trình duyệt
        $stmt = $conn->prepare("
            SELECT 
                browser,
                COUNT(*) as count
            FROM visits
            WHERE site_id = :site_id AND timestamp BETWEEN :start_date AND :end_date AND is_bot = 0
            GROUP BY browser
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date . ' 00:00:00');
        $stmt->bindParam(':end_date', $end_date . ' 23:59:59');
        $stmt->execute();
        $browser_stats = $stmt->fetchAll();
        
        // Lấy thống kê theo nhà mạng
        $stmt = $conn->prepare("
            SELECT 
                CASE
                    WHEN isp LIKE '%Viettel%' THEN 'Viettel'
                    WHEN isp LIKE '%VNPT%' OR isp LIKE '%FPT%' THEN 'VNPT/FPT'
                    WHEN isp LIKE '%MobiFone%' THEN 'MobiFone'
                    WHEN isp LIKE '%VinaPhone%' THEN 'VinaPhone'
                    ELSE 'Khác'
                END as network_provider,
                COUNT(*) as count
            FROM visits
            WHERE site_id = :site_id AND timestamp BETWEEN :start_date AND :end_date AND is_bot = 0
            GROUP BY network_provider
            ORDER BY count DESC
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date . ' 00:00:00');
        $stmt->bindParam(':end_date', $end_date . ' 23:59:59');
        $stmt->execute();
        $network_stats = $stmt->fetchAll();
        
        // Lấy các tương tác gần đây
        $stmt = $conn->prepare("
            SELECT i.*, v.ip, v.browser, v.os, v.city, v.country
            FROM interactions i
            LEFT JOIN visits v ON i.visit_id = v.id
            WHERE i.site_id = :site_id AND i.timestamp BETWEEN :start_date AND :end_date AND i.is_valid = 1
            ORDER BY i.timestamp DESC
            LIMIT 10
        ");
        $stmt->bindParam(':site_id', $current_site_id);
        $stmt->bindParam(':start_date', $start_date . ' 00:00:00');
        $stmt->bindParam(':end_date', $end_date . ' 23:59:59');
        $stmt->execute();
        $recent_interactions = $stmt->fetchAll();
        
        // Lưu tất cả dữ liệu vào một mảng
        $dashboard_data = [
            'overview' => $overview,
            'chart_data' => $chart_data,
            'top_pages' => $top_pages,
            'device_stats' => $device_stats,
            'browser_stats' => $browser_stats,
            'network_stats' => $network_stats,
            'recent_interactions' => $recent_interactions
        ];
        
    } catch(PDOException $e) {
        logError("Error fetching dashboard data: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ChuyenDoi.io.vn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 0.95);
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .dashboard-card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .dashboard-card .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .stat-card {
            border-left: 5px solid;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.info { border-left-color: #0dcaf0; }
        .stat-card.purple { border-left-color: #6f42c1; }
        .stat-card.orange { border-left-color: #fd7e14; }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }
        .visits-chart-container {
            height: 300px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">ChuyenDoi.io.vn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($user['is_admin']): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php"><i class="fas fa-cog"></i> Quản trị</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-card"></i> Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Cài đặt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>