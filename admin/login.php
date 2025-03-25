<?php
session_start();

// Nếu đã đăng nhập, chuyển hướng đến dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Import config & functions
require_once '../includes/config.php'; 
require_once '../includes/functions.php';

$error = '';
$success = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin đăng nhập.';
    } else {
        try {
            // Kiểm tra thông tin đăng nhập
            $stmt = $conn->prepare("SELECT id, username, password, email, is_admin FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $username); // Cho phép đăng nhập bằng email
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Kiểm tra mật khẩu
                if (verifyPassword($password, $user['password'])) {
                    // Đăng nhập thành công
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    // Nếu chọn remember me, lưu cookie
                    if ($remember) {
                        $token = generateAuthToken($user['id']);
                        setcookie('auth_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true); // 30 ngày
                    }
                    
                    // Cập nhật thời gian đăng nhập
                    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $stmt->bindParam(':id', $user['id']);
                    $stmt->execute();
                    
                    // Chuyển hướng đến trang chủ
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
                }
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        } catch (PDOException $e) {
            logError("Login error: " . $e->getMessage());
            $error = 'Đã xảy ra lỗi. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ChuyenDoi.io.vn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 450px;
            margin: 80px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-weight: 600;
            color: #343a40;
        }
        .login-form .form-control {
            padding: 12px;
            border-radius: 5px;
        }
        .login-form .form-floating>label {
            padding: 12px;
        }
        .btn-primary {
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 5px;
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
        .login-footer a {
            color: #0d6efd;
            text-decoration: none;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h1>ChuyenDoi.io.vn</h1>
                <p class="text-muted">Đăng nhập để tiếp tục</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="post" action="">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Tên đăng nhập hoặc Email" required autofocus>
                    <label for="username"><i class="fas fa-user me-2"></i>Tên đăng nhập hoặc Email</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Mật khẩu" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Mật khẩu</label>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                    <a href="forgot_password.php" class="float-end">Quên mật khẩu?</a>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                    </button>
                </div>
            </form>
            
            <div class="login-footer">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
                <p class="mt-3"><a href="../index.php">Quay lại trang chủ</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>