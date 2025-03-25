# ChuyenDoi Tracking System

Hệ thống theo dõi IP và phát hiện bot/automation click, giúp đo lường chuyển đổi chính xác và ngăn chặn các chuyển đổi ảo.

## Tính Năng

- **Theo dõi thông tin người dùng**:
  - IP address (sử dụng ipify.org)
  - Trình duyệt và phiên bản
  - Nhà mạng (ISP)
  - Loại kết nối (3G/WiFi)
  - Hệ điều hành và phiên bản
  - Kích thước màn hình
  - Vị trí địa lý (thành phố, quốc gia)
  - Trang đang xem
  - Thời gian online
  - Theo dõi lượt click vào các liên kết cụ thể (tel:, zalo.me, m.me, google maps)

- **Phát hiện bot/automation click**:
  - Phân tích hành vi chuột
  - Phân tích thời gian tương tác
  - Kiểm tra trình duyệt headless
  - Phân tích JavaScript
  - Phân tích HTTP header
  - Tính điểm bot dựa trên nhiều yếu tố

- **Phát hiện chuyển đổi bất thường**:
  - Phân tích thống kê (Z-score)
  - Phát hiện đột biến (Spike Detection)
  - Phân tích mẫu hình thời gian
  - Phân tích phân cụm

- **Nút tương tác**:
  - Hiển thị nút tương tác trên website
  - Tự động ẩn nút đối với bot/automation click
  - Nhiều kiểu dáng và tùy chọn tùy chỉnh

## Cài Đặt

### Yêu Cầu Hệ Thống

- PHP 7.4 trở lên
- MariaDB 10.11.11 trở lên
- Hỗ trợ cURL và JSON

### Cài Đặt Cơ Sở Dữ Liệu

1. Tạo cơ sở dữ liệu mới
2. Nhập file `database.sql` vào cơ sở dữ liệu
3. Cấu hình thông tin kết nối trong file `.env`

### Cấu Hình

1. Sao chép file `.env.example` thành `.env`
2. Cập nhật thông tin cơ sở dữ liệu trong file `.env`
3. (Tùy chọn) Đăng ký tài khoản ipinfo.io và cập nhật token trong file `.env`

## Sử Dụng

### Đăng Nhập Admin

1. Truy cập trang admin tại `https://chuyendoi.io.vn/admin`
2. Đăng nhập với tài khoản mặc định:
   - Tên đăng nhập: `quantri`
   - Mật khẩu: `P8j2mK9xL5qR3sT7`
   - **Lưu ý**: Hãy đổi mật khẩu sau khi đăng nhập lần đầu

### Thêm Website

1. Đăng nhập vào trang admin
2. Chọn "Websites" > "Add Website"
3. Nhập thông tin website và thông tin liên hệ
4. Lưu lại để nhận API key

### Tích Hợp Vào Website

#### Tích Hợp Vào Website PHP

```php
<?php
// Tại cuối trang, trước thẻ đóng </body>
require_once 'path/to/integration/php-snippet.php';
echo chuyendoi_tracking('your-api-key', [
    'phone' => '0916152929',
    'zalo' => 'https://zalo.me/0916152929',
    'messenger' => 'https://m.me/dienmaytotvietnam',
    'maps' => 'https://goo.gl/maps/Z4pipWWc1GW2aY6p8'
]);
?>
```

#### Tích Hợp Vào Website Next.js

```jsx
// components/Layout.js
import ChuyenDoiTracker from '../components/ChuyenDoiTracker';

export default function Layout({ children }) {
  return (
    <>
      {children}
      <ChuyenDoiTracker
        apiKey="your-api-key"
        phone="0916152929"
        zalo="https://zalo.me/0916152929"
        messenger="https://m.me/dienmaytotvietnam"
        maps="https://goo.gl/maps/Z4pipWWc1GW2aY6p8"
      />
    </>
  );
}
```

### Xem Thống Kê

1. Đăng nhập vào trang admin
2. Xem thống kê tổng quan trên trang Dashboard
3. Xem chi tiết lượt truy cập, lượt click, và các bất thường

### Quản Lý Fraud Patterns

1. Đăng nhập vào trang admin
2. Chọn "Fraud Patterns"
3. Xem danh sách các mẫu hình fraud đã phát hiện
4. Thêm mẫu hình fraud mới hoặc chỉnh sửa mẫu hình hiện có

## Tùy Chọn Nút Tương Tác

### Kiểu Dáng

- `style`: Kiểu dáng nút (`'fab'` hoặc `'bar'`)

### Thông Tin Liên Hệ

- `phone`: Số điện thoại cho nút gọi điện
- `zalo`: Liên kết Zalo
- `messenger`: Liên kết Messenger
- `maps`: Liên kết Google Maps

### Hiển Thị

- `show_labels`: Hiển thị nhãn cho các nút (`true` hoặc `false`)
- `primary_color`: Màu chính cho nút (mã màu HEX)
- `animation`: Bật/tắt hiệu ứng động (`true` hoặc `false`)

## Phát Hiện Bot

Hệ thống sử dụng nhiều kỹ thuật để phát hiện bot:

1. **Phân tích hành vi chuột**:
   - Theo dõi chuyển động chuột tự nhiên
   - Phát hiện chuyển động theo đường thẳng hoặc mẫu hình lặp lại

2. **Phân tích thời gian tương tác**:
   - Đo thời gian giữa các tương tác
   - Phát hiện tương tác quá đều đặn hoặc quá nhanh

3. **Kiểm tra trình duyệt**:
   - Phát hiện trình duyệt headless
   - Phát hiện WebDriver
   - Kiểm tra tính nhất quán của thông tin trình duyệt

4. **Phân tích JavaScript**:
   - Kiểm tra các thuộc tính JavaScript bất thường
   - Phát hiện fingerprinting bất thường

5. **Phân tích HTTP header**:
   - Kiểm tra header bất thường
   - Phát hiện proxy hoặc VPN

## Phát Hiện Chuyển Đổi Bất Thường

Hệ thống sử dụng các thuật toán thống kê để phát hiện chuyển đổi bất thường:

1. **Z-score**:
   - So sánh tỷ lệ chuyển đổi hiện tại với tỷ lệ trung bình
   - Phát hiện khi tỷ lệ chuyển đổi vượt quá ngưỡng

2. **Phát hiện đột biến**:
   - Phát hiện khi tỷ lệ chuyển đổi tăng đột biến
   - Tính đến các yếu tố theo mùa (giờ trong ngày, ngày trong tuần)

3. **Phân tích mẫu hình thời gian**:
   - Phát hiện các mẫu hình không tự nhiên
   - Phát hiện chu kỳ đều đặn

4. **Phân tích phân cụm**:
   - Nhóm các lượt truy cập theo hành vi
   - Phát hiện các cụm bất thường

## Đóng Góp

Nếu bạn muốn đóng góp vào dự án, vui lòng liên hệ với chúng tôi qua email: contact@chuyendoi.io.vn

## Giấy Phép

Bản quyền © 2025 ChuyenDoi Tracking System. Đã đăng ký bản quyền.
