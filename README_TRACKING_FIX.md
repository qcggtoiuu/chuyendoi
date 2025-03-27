# Hướng dẫn sửa lỗi Tracking System

## Vấn đề
Hệ thống tracking không hoạt động khi sử dụng mã nhúng hiện tại. Lỗi xảy ra khi hệ thống chuyển từ việc sử dụng file `tracker.js` sang `chuyendoi-embed.js`. Cụ thể, khi truy cập endpoint `chuyendoi.io.vn/api/track.php`, hệ thống trả về lỗi 500 (Internal Server Error).

## Nguyên nhân
Sau khi phân tích mã nguồn, chúng tôi phát hiện vấn đề sau:

1. File `chuyendoi-embed.js` tải file `tracker.js` từ URL `https://chuyendoi.io.vn/assets/js/tracker.js`
2. Trong file `tracker.js`, URL API được hardcode là `https://chuyendoi.io.vn/api/track.php`
3. Có thể có sự không đồng bộ giữa các URL này và cấu hình thực tế của hệ thống

## Giải pháp
Chúng tôi đã tạo một file JavaScript mới (`chuyendoi-track.js`) kết hợp cả hai chức năng của `tracker.js` và `chuyendoi-embed.js` thành một file duy nhất. Điều này giúp:

1. Loại bỏ việc phải tải nhiều file JavaScript
2. Đơn giản hóa mã nhúng
3. Giảm thiểu các vấn đề về URL không đồng bộ

## Cách sử dụng mã nhúng mới

### Mã nhúng cơ bản
```html
<script src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" data-api-key="YOUR_API_KEY"></script>
```

### Mã nhúng đầy đủ với tất cả tùy chọn
```html
<script 
  src="https://chuyendoi.io.vn/assets/js/chuyendoi-track.js" 
  data-api-key="YOUR_API_KEY"
  data-debug="true"
  data-phone="0987654321"
  data-zalo="https://zalo.me/0987654321"
  data-messenger="https://m.me/your-page"
  data-maps="https://goo.gl/maps/your-location"
  data-style="fab"
  data-show-labels="true"
  data-primary-color="#3961AA"
  data-animation="true"
></script>
```

## Các tùy chọn có thể sử dụng

| Tùy chọn | Mô tả | Giá trị mặc định |
|----------|-------|-----------------|
| data-api-key | Khóa API của bạn (bắt buộc) | - |
| data-debug | Bật chế độ debug | false |
| data-phone | Số điện thoại | '' |
| data-zalo | Link Zalo | '' |
| data-messenger | Link Messenger | '' |
| data-maps | Link Google Maps | '' |
| data-style | Kiểu nút (fab/bar/sticky-right) | 'fab' |
| data-show-labels | Hiển thị nhãn | true |
| data-primary-color | Màu chủ đạo | '#3961AA' |
| data-animation | Bật hiệu ứng | true |

## Kiểm tra
Để kiểm tra xem mã nhúng mới có hoạt động không, bạn có thể sử dụng trang test:
```
https://chuyendoi.io.vn/test_combined_tracking.php?debug=1
```

Trang này sẽ hiển thị thông tin debug và cho phép bạn kiểm tra các sự kiện tracking.

## API JavaScript

Script mới cung cấp các phương thức sau để sử dụng trong mã JavaScript của bạn:

```javascript
// Khởi tạo thủ công (nếu cần)
window.ChuyenDoi.init({
    apiKey: 'YOUR_API_KEY',
    debug: true,
    // Các tùy chọn khác...
});

// Theo dõi sự kiện tùy chỉnh
window.ChuyenDoi.trackEvent('event_name', {
    category: 'category',
    action: 'action',
    label: 'label'
});

// Theo dõi click thủ công
window.ChuyenDoi.trackClick(element);

// Kiểm tra xem người dùng có phải là bot không
const isBot = window.ChuyenDoi.isBot();

// Kiểm tra xem có nên ẩn nút không
const shouldHideButtons = window.ChuyenDoi.shouldHideButtons();
```

## Các file đã tạo/sửa đổi

1. `assets/js/chuyendoi-track.js` - File JavaScript mới kết hợp cả hai chức năng
2. `integration/combined-embed-code.html` - Mẫu mã nhúng mới
3. `test_combined_tracking.php` - Trang kiểm tra mã nhúng mới

## Lưu ý quan trọng

1. Đảm bảo file `chuyendoi-track.js` được tải lên máy chủ và có thể truy cập được từ URL `https://chuyendoi.io.vn/assets/js/chuyendoi-track.js`
2. Nếu bạn thay đổi domain, hãy cập nhật URL trong file `chuyendoi-track.js` và trong mã nhúng
3. Nếu bạn gặp vấn đề với CORS, hãy đảm bảo rằng các header CORS đã được cấu hình đúng trong file `.htaccess`
