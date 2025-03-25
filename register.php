<?php
// Define system constant
define('TRACKING_SYSTEM', true);

// Include required files
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: admin/index.php');
    exit;
}

// Initialize variables
$username = '';
$email = '';
$error = '';
$success = false;

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate form data
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Vui lòng điền đầy đủ thông tin';
    } else if ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else if (strlen($password) < 8) {
        $error = 'Mật khẩu phải có ít nhất 8 ký tự';
    } else {
        // Get database instance
        $db = Database::getInstance();
        
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Tên đăng nhập đã tồn tại';
        } else {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email đã tồn tại';
            } else {
                // Hash password
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                
                // Check if this is the first user (WordPress-like behavior)
                $checkStmt = $db->query("SELECT COUNT(*) as count FROM users");
                $userCount = $checkStmt->fetch_assoc()['count'];
                
                // Set role and approval status
                if ($userCount === 0) {
                    // First user is admin and automatically approved
                    $role = 'admin';
                    $isApproved = true;
                } else {
                    // Subsequent users are regular users and need approval
                    $role = 'user';
                    $isApproved = false;
                }
                
                // Insert user into database
                $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, role, is_approved) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $username, $passwordHash, $email, $role, $isApproved);
                
                if ($stmt->execute()) {
                    $success = true;
                    
                    // Clear form data
                    $username = '';
                    $email = '';
                } else {
                    $error = 'Đăng ký thất bại: ' . $db->error();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Hệ Thống Tracking IP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-container {
            max-width: 500px;
            width: 100%;
            padding: 15px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #3961AA;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background-color: #3961AA;
            border-color: #3961AA;
        }
        .btn-primary:hover {
            background-color: #2c4e8a;
            border-color: #2c4e8a;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card">
            <div class="card-header text-center">
                <h4 class="mb-0">Hệ Thống Tracking IP</h4>
            </div>
            <div class="card-body">
                <h5 class="card-title text-center mb-4">Đăng Ký Tài Khoản</h5>
                
                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Đăng ký thành công!</h4>
                    
                    <?php if (isset($role) && $role === 'admin'): ?>
                    <p>Tài khoản của bạn đã được tạo với quyền quản trị viên.</p>
                    <p>Bạn là người dùng đầu tiên đăng ký, nên tài khoản của bạn đã được tự động phê duyệt.</p>
                    <hr>
                    <p class="mb-0">Bạn có thể <a href="admin/login.php">đăng nhập</a> ngay bây giờ để bắt đầu quản lý hệ thống.</p>
                    <?php else: ?>
                    <p>Tài khoản của bạn đã được tạo và đang chờ phê duyệt từ quản trị viên.</p>
                    <p>Bạn sẽ nhận được thông báo qua email khi tài khoản được phê duyệt.</p>
                    <hr>
                    <p class="mb-0">Bạn có thể <a href="admin/login.php">đăng nhập</a> sau khi tài khoản được phê duyệt.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="" id="registerForm">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <small class="form-text text-muted">Tên đăng nhập phải là duy nhất và không chứa ký tự đặc biệt.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <small class="form-text text-muted">Email sẽ được sử dụng để thông báo khi tài khoản được phê duyệt.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="password-strength-meter mt-2">
                            <div class="progress">
                                <div class="progress-bar password-strength" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="form-text text-muted password-strength-text">Mật khẩu phải có ít nhất 8 ký tự.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="terms" name="terms" required>
                            <label class="custom-control-label" for="terms">Tôi đồng ý với <a href="#" data-toggle="modal" data-target="#termsModal">điều khoản sử dụng</a></label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Đăng Ký</button>
                    
                    <div class="text-center mt-3">
                        <p>Đã có tài khoản? <a href="admin/login.php">Đăng nhập</a></p>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Điều khoản sử dụng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>1. Chấp nhận điều khoản</h5>
                    <p>Bằng cách đăng ký và sử dụng dịch vụ, bạn đồng ý tuân thủ các điều khoản và điều kiện này.</p>
                    
                    <h5>2. Tài khoản</h5>
                    <p>Bạn chịu trách nhiệm duy trì tính bảo mật của tài khoản và mật khẩu của mình.</p>
                    <p>Tài khoản của bạn sẽ cần được phê duyệt bởi quản trị viên trước khi có thể sử dụng.</p>
                    
                    <h5>3. Quyền riêng tư</h5>
                    <p>Chúng tôi tôn trọng quyền riêng tư của bạn và cam kết bảo vệ thông tin cá nhân của bạn.</p>
                    
                    <h5>4. Sử dụng dịch vụ</h5>
                    <p>Bạn đồng ý sử dụng dịch vụ theo đúng mục đích và không vi phạm pháp luật.</p>
                    
                    <h5>5. Thay đổi điều khoản</h5>
                    <p>Chúng tôi có thể thay đổi điều khoản này bất cứ lúc nào. Việc tiếp tục sử dụng dịch vụ sau khi thay đổi đồng nghĩa với việc bạn chấp nhận các điều khoản mới.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="document.getElementById('terms').checked = true;">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.querySelector('.password-strength');
            const strengthText = document.querySelector('.password-strength-text');
            
            // Calculate strength
            let strength = 0;
            
            // Length check
            if (password.length >= 8) {
                strength += 25;
            }
            
            // Contains lowercase
            if (/[a-z]/.test(password)) {
                strength += 25;
            }
            
            // Contains uppercase
            if (/[A-Z]/.test(password)) {
                strength += 25;
            }
            
            // Contains number or special char
            if (/[0-9!@#$%^&*(),.?":{}|<>]/.test(password)) {
                strength += 25;
            }
            
            // Update UI
            strengthBar.style.width = strength + '%';
            
            // Update color and text
            if (strength < 25) {
                strengthBar.className = 'progress-bar password-strength bg-danger';
                strengthText.textContent = 'Rất yếu';
            } else if (strength < 50) {
                strengthBar.className = 'progress-bar password-strength bg-warning';
                strengthText.textContent = 'Yếu';
            } else if (strength < 75) {
                strengthBar.className = 'progress-bar password-strength bg-info';
                strengthText.textContent = 'Trung bình';
            } else {
                strengthBar.className = 'progress-bar password-strength bg-success';
                strengthText.textContent = 'Mạnh';
            }
        });
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
