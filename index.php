<h4>Dễ dàng tích hợp</h4>
                            <p>Chỉ mất 2 phút để thêm mã theo dõi vào website của bạn. Tương thích với tất cả nền tảng: WordPress, Shopify, Wix...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h3 class="footer-heading">ChuyenDoi.io.vn</h3>
                    <p>Nền tảng hàng đầu về tracking, phân tích và tối ưu hoá chuyển đổi giúp doanh nghiệp tăng doanh thu và tiết kiệm chi phí quảng cáo.</p>
                    <div class="social-links mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h3 class="footer-heading">Liên kết</h3>
                    <ul class="list-unstyled">
                        <li><a href="#" class="footer-link">Trang chủ</a></li>
                        <li><a href="#features" class="footer-link">Tính năng</a></li>
                        <li><a href="#pricing" class="footer-link">Bảng giá</a></li>
                        <li><a href="#testimonials" class="footer-link">Khách hàng</a></li>
                        <li><a href="#contact" class="footer-link">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h3 class="footer-heading">Hỗ trợ</h3>
                    <ul class="list-unstyled">
                        <li><a href="docs/" class="footer-link">Tài liệu API</a></li>
                        <li><a href="docs/tutorial.php" class="footer-link">Hướng dẫn</a></li>
                        <li><a href="faq.php" class="footer-link">Câu hỏi thường gặp</a></li>
                        <li><a href="docs/changelog.php" class="footer-link">Cập nhật</a></li>
                        <li><a href="support.php" class="footer-link">Trung tâm hỗ trợ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3 class="footer-heading">Đăng ký nhận tin</h3>
                    <p>Đăng ký để nhận tin tức mới nhất và mẹo tối ưu hóa chuyển đổi.</p>
                    <form class="mt-3">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Email của bạn" required>
                            <button class="btn btn-primary" type="submit">Đăng ký</button>
                        </div>
                    </form>
                    <p class="small">Chúng tôi cam kết bảo mật thông tin của bạn.</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> ChuyenDoi.io.vn. Tất cả các quyền được bảo lưu.</p>
                <div class="mt-2">
                    <a href="privacy.php" class="text-decoration-none text-white-50 me-3">Chính sách bảo mật</a>
                    <a href="terms.php" class="text-decoration-none text-white-50">Điều khoản sử dụng</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xử lý form liên hệ
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Trong thực tế, bạn sẽ gửi dữ liệu đến server
            // Ở đây chỉ là minh họa
            alert('Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi trong thời gian sớm nhất!');
            this.reset();
        });
        
        // Hiệu ứng scroll mượt
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>

    <!-- How It Works -->
    <section class="py-5 my-5 bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Cách thức hoạt động</h2>
                <p>Ba bước đơn giản để bắt đầu theo dõi và tối ưu hoá chuyển đổi</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-1 text-primary mb-3">1</div>
                            <h4>Đăng ký và thêm website</h4>
                            <p>Tạo tài khoản miễn phí và thêm website của bạn vào hệ thống chỉ trong vài giây.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-1 text-primary mb-3">2</div>
                            <h4>Thêm mã theo dõi</h4>
                            <p>Sao chép và dán mã theo dõi vào website của bạn - chỉ cần thêm vào một lần duy nhất.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="display-1 text-primary mb-3">3</div>
                            <h4>Xem thống kê và tối ưu</h4>
                            <p>Theo dõi dữ liệu thực về lượt truy cập và tương tác để tối ưu hóa chiến dịch marketing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-5 my-5" id="pricing">
        <div class="container">
            <div class="section-title">
                <h2>Bảng giá</h2>
                <p>Chọn gói phù hợp với nhu cầu kinh doanh của bạn</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h3>Miễn phí</h3>
                            <div class="price">0₫<span class="price-period">/tháng</span></div>
                        </div>
                        <div class="pricing-body">
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> 1 website</li>
                                <li><i class="fas fa-check"></i> 1.000 lượt tracking/ngày</li>
                                <li><i class="fas fa-check"></i> Phát hiện Bot cơ bản</li>
                                <li><i class="fas fa-check"></i> 1 kiểu nút tương tác</li>
                                <li><i class="fas fa-times"></i> Phân tích nâng cao</li>
                                <li><i class="fas fa-times"></i> Báo cáo tùy chỉnh</li>
                                <li><i class="fas fa-times"></i> Hỗ trợ ưu tiên</li>
                            </ul>
                            <div class="text-center">
                                <a href="admin/register.php" class="btn btn-outline-primary btn-lg d-block">Bắt đầu miễn phí</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="pricing-card highlighted">
                        <div class="pricing-header">
                            <h3>Tiêu chuẩn</h3>
                            <div class="price">199.000₫<span class="price-period">/tháng</span></div>
                        </div>
                        <div class="pricing-body">
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> 5 website</li>
                                <li><i class="fas fa-check"></i> 10.000 lượt tracking/ngày</li>
                                <li><i class="fas fa-check"></i> Phát hiện Bot nâng cao</li>
                                <li><i class="fas fa-check"></i> Tất cả kiểu nút tương tác</li>
                                <li><i class="fas fa-check"></i> Phân tích nâng cao</li>
                                <li><i class="fas fa-check"></i> Báo cáo tùy chỉnh</li>
                                <li><i class="fas fa-times"></i> Hỗ trợ ưu tiên</li>
                            </ul>
                            <div class="text-center">
                                <a href="admin/register.php" class="btn btn-primary btn-lg d-block">Đăng ký ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h3>Doanh nghiệp</h3>
                            <div class="price">499.000₫<span class="price-period">/tháng</span></div>
                        </div>
                        <div class="pricing-body">
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i> 20 website</li>
                                <li><i class="fas fa-check"></i> 50.000 lượt tracking/ngày</li>
                                <li><i class="fas fa-check"></i> Phát hiện Bot cao cấp</li>
                                <li><i class="fas fa-check"></i> Tất cả kiểu nút + tùy biến</li>
                                <li><i class="fas fa-check"></i> Phân tích nâng cao</li>
                                <li><i class="fas fa-check"></i> Báo cáo tùy chỉnh</li>
                                <li><i class="fas fa-check"></i> Hỗ trợ ưu tiên 24/7</li>
                            </ul>
                            <div class="text-center">
                                <a href="admin/register.php" class="btn btn-outline-primary btn-lg d-block">Đăng ký ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <p>Cần nhiều tính năng hơn? <a href="#contact">Liên hệ với chúng tôi</a> để có gói doanh nghiệp tùy chỉnh.</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 my-5 bg-light" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Khách hàng nói gì</h2>
                <p>Các doanh nghiệp đã tin tưởng và sử dụng dịch vụ của chúng tôi</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">
                            ChuyenDoi.io.vn giúp chúng tôi loại bỏ hơn 40% lượt click ảo từ các bot, tiết kiệm ngân sách quảng cáo và tối ưu hiệu quả marketing.
                        </div>
                        <div class="testimonial-author">
                            <img src="assets/images/avatar1.jpg" alt="Nguyễn Văn A" class="testimonial-avatar">
                            <div class="testimonial-info">
                                <h5>Nguyễn Văn A</h5>
                                <p>Marketing Manager, ABC Shop</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">
                            Nút tương tác của ChuyenDoi.io.vn giúp tỷ lệ khách hàng liên hệ tăng 37% chỉ sau 2 tuần sử dụng. Thật ấn tượng!
                        </div>
                        <div class="testimonial-author">
                            <img src="assets/images/avatar2.jpg" alt="Trần Thị B" class="testimonial-avatar">
                            <div class="testimonial-info">
                                <h5>Trần Thị B</h5>
                                <p>CEO, XYZ Company</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-quote">
                            Cuối cùng tôi cũng biết được khách hàng thực sự đến từ đâu, sử dụng thiết bị gì và thường tương tác như thế nào với website của mình.
                        </div>
                        <div class="testimonial-author">
                            <img src="assets/images/avatar3.jpg" alt="Lê Văn C" class="testimonial-avatar">
                            <div class="testimonial-info">
                                <h5>Lê Văn C</h5>
                                <p>Chủ cửa hàng, Fashion Store</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5 my-5" id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Liên hệ</h2>
                <p>Có câu hỏi? Chúng tôi sẵn sàng hỗ trợ bạn!</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <form id="contact-form">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="name" placeholder="Họ tên" required>
                            <label for="name">Họ tên</label>
                        </div>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" placeholder="Email" required>
                            <label for="email">Email</label>
                        </div>
                        <div class="form-floating">
                            <input type="tel" class="form-control" id="phone" placeholder="Số điện thoại">
                            <label for="phone">Số điện thoại</label>
                        </div>
                        <div class="form-floating">
                            <textarea class="form-control" id="message" placeholder="Nội dung" style="height: 150px" required></textarea>
                            <label for="message">Nội dung</label>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Gửi tin nhắn</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Thông tin liên hệ</h4>
                            <div class="d-flex mb-3">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-map-marker-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h5>Địa chỉ</h5>
                                    <p>123 Đường Lê Lợi, Quận 1, TP.HCM</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-phone-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h5>Điện thoại</h5>
                                    <p><a href="tel:0909123456" class="text-decoration-none">0909 123 456</a></p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                                <div>
                                    <h5>Email</h5>
                                    <p><a href="mailto:info@chuyendoi.io.vn" class="text-decoration-none">info@chuyendoi.io.vn</a></p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <div>
                                    <h5>Giờ làm việc</h5>
                                    <p>Thứ 2 - Thứ 6: 8:30 - 17:30<br>Thứ 7: 8:30 - 12:00</p>
                                </div>
                            </div>
                        </div            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        .pricing-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .pricing-card.highlighted {
            border: 2px solid var(--primary-color);
            position: relative;
        }
        .pricing-card.highlighted::before {
            content: "Phổ biến nhất";
            position: absolute;
            top: 0;
            right: 0;
            background-color: var(--primary-color);
            color: white;
            padding: 5px 15px;
            font-size: 0.8rem;
            border-bottom-left-radius: 10px;
        }
        .pricing-header {
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .pricing-body {
            padding: 20px;
        }
        .price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .price-period {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0 0 20px;
        }
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        .feature-list li .fa-check {
            color: var(--success-color);
            margin-right: 10px;
        }
        .feature-list li .fa-times {
            color: var(--secondary-color);
            margin-right: 10px;
        }
        .testimonial-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 30px;
        }
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .testimonial-quote {
            font-size: 1.1rem;
            font-style: italic;
            margin-bottom: 20px;
            position: relative;
            padding: 0 20px;
        }
        .testimonial-quote::before,
        .testimonial-quote::after {
            content: '"';
            font-size: 2rem;
            color: var(--primary-color);
            position: absolute;
            line-height: 1;
        }
        .testimonial-quote::before {
            left: 0;
            top: 0;
        }
        .testimonial-quote::after {
            right: 0;
            bottom: -10px;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .testimonial-info h5 {
            margin: 0;
            font-weight: 600;
        }
        .testimonial-info p {
            margin: 0;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 60px 0 30px;
        }
        .footer-heading {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s;
            display: block;
            margin-bottom: 10px;
        }
        .footer-link:hover {
            color: white;
        }
        .social-links a {
            display: inline-block;
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 36px;
            margin-right: 10px;
            transition: all 0.3s;
        }
        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }
        .copyright {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        .nav-item .btn-primary {
            padding: 8px 20px;
            border-radius: 30px;
        }
        .section-title {
            margin-bottom: 50px;
            text-align: center;
        }
        .section-title h2 {
            font-weight: 700;
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary-color);
        }
        .section-title p {
            color: var(--secondary-color);
            font-size: 1.1rem;
        }
        #contact-form .form-control {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        #contact-form .btn-primary {
            padding: 12px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">ChuyenDoi.io.vn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Tính năng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Bảng giá</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Khách hàng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Liên hệ</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white" href="admin/index.php">
                                <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="admin/login.php">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white" href="admin/register.php">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Theo dõi và tối ưu chuyển đổi</h1>
                    <p class="hero-subtitle">ChuyenDoi.io.vn giúp bạn hiểu được hành vi thực của khách hàng, phân biệt giữa bot và người thật, tăng tỷ lệ chuyển đổi và thúc đẩy doanh thu.</p>
                    <div class="d-flex flex-wrap">
                        <a href="admin/register.php" class="btn btn-light btn-hero me-3 mb-3">Đăng ký miễn phí</a>
                        <a href="#features" class="btn btn-outline-light btn-hero mb-3">Tìm hiểu thêm</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="assets/images/hero-illustration.svg" alt="Minh họa" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 my-5" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Tính năng nổi bật</h2>
                <p>Theo dõi, phân tích và tối ưu hóa toàn bộ hành trình khách hàng</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h4>Phát hiện Bot thông minh</h4>
                            <p>Hệ thống tự động nhận diện và lọc ra bot/automation click để bạn có được thông tin chính xác về tương tác của khách hàng thực.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h4>Theo dõi tương tác</h4>
                            <p>Theo dõi chính xác tất cả các tương tác quan trọng: cuộc gọi điện thoại, tin nhắn Zalo, Messenger và xem địa chỉ.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4>Phân tích chuyên sâu</h4>
                            <p>Biểu đồ và báo cáo trực quan cho phép bạn hiểu rõ hành vi khách hàng, đưa ra quyết định dựa trên dữ liệu thực.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4>Thông tin thiết bị</h4>
                            <p>Thu thập thông tin chi tiết về trình duyệt, hệ điều hành, nhà mạng và kích thước màn hình của khách truy cập.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <h4>Nút tương tác tùy chỉnh</h4>
                            <p>Tạo và tùy chỉnh nút gọi điện, Zalo, Messenger theo phong cách riêng của bạn và theo dõi hiệu quả của từng nút.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <div class="feature-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <h4>Dễ dàng tích<?php
// Import config & functions
require_once 'includes/config.php'; 
require_once 'includes/functions.php';

// Kiểm tra session
session_start();
$logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChuyenDoi.io.vn - Theo dõi và phân tích tương tác khách hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --accent-color: #fd7e14;
            --success-color: #198754;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #343a40;
        }
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
            padding: 100px 0;
            color: white;
            margin-top: 56px;
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .btn-hero {
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .feature-card {
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: none;
            box-shadow: 0 0.125rem