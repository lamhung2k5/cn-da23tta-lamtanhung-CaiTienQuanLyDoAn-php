# 🎓 Hệ Thống Quản Lý Đồ Án & Đề Tài NCKH
> **Khoa Công Nghệ Thông Tin — Trường Đại học Trà Vinh (TVU)**

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.style=for-the-badge)](LICENSE)

---

## 📌 Giới Thiệu Tổng Quan

**Hệ Thống Quản Lý Đồ Án & Đề Tài** là nền tảng web hiện đại giúp số hóa toàn bộ quy trình quản lý, đăng ký và duyệt đồ án tốt nghiệp, đồ án môn học (Cơ sở ngành, Chuyên ngành) dành cho **Khoa Công nghệ Thông tin - Trường Đại học Trà Vinh**.

Hệ thống tối ưu hóa sự tương tác giữa **Sinh viên**, **Giảng viên** và **Lãnh đạo Khoa**, giúp minh bạch tiến độ, giảm thiểu thủ tục thủ công và theo dõi kết quả chuẩn xác.

---

## ✨ Tính Năng Nổi Bật Theo Vai Trò

### 🎓 1. Sinh Viên (Student)
* **Tra cứu đề tài:** Xem danh sách đề tài công khai, lọc theo Hệ đào tạo (Cơ sở ngành / Chuyên ngành) và Giảng viên hướng dẫn.
* **Đăng ký đề tài:** Chọn và đăng ký đề tài mong muốn trực tuyến theo thời gian quy định.
* **Theo dõi trạng thái:** Cập nhật trạng thái duyệt đề tài và phản hồi từ giảng viên.
* **Hủy đăng ký / Đổi đề tài:** Linh hoạt quản lý đăng ký trong thời hạn cho phép.

### 👨‍🏫 2. Giảng Viên (Lecturer)
* **Quản lý đề tài:** Tạo mới, chỉnh sửa, ẩn/hiện đề tài cho từng hệ đào tạo.
* **Duyệt đăng ký:** Xem danh sách sinh viên đăng ký đề tài của mình, chấp nhận hoặc từ chối.
* **Hồ sơ giảng viên:** Cập nhật thông tin cá nhân, chuyên môn và hướng nghiên cứu.
* **Import/Export:** Nhập danh sách đề tài hoặc xuất thông tin nhanh chóng.

### 👔 3. Lãnh Đạo Khoa / Admin (Leadership)
* **Duyệt đề tài cấp Khoa:** Xét duyệt danh sách đề tài do giảng viên đề xuất trước khi công khai cho sinh viên.
* **Quản lý tài khoản:** Quản lý, cấp tài khoản cho Sinh viên và Giảng viên (hỗ trợ import Excel/CSV).
* **Quản lý thông báo & Timeline:** Đăng thông báo mốc thời gian làm đồ án, lịch nộp báo cáo, biểu mẫu đồ án.
* **Thống kê & Báo cáo:** Xem biểu đồ tổng quan số lượng đề tài, tỷ lệ đăng ký theo từng chuyên ngành.
* **Cấu hình hệ thống:** Tùy chỉnh các liên kết menu, tham số hệ thống và mở/đóng đợt đăng ký.

### 🔐 4. Xác Thực & Bảo Mật (Authentication & Security)
* Đăng nhập đa phương thức: **Tài khoản Local**, **Google OAuth 2.0**, **GitHub OAuth**, **Microsoft OAuth**.
* Xác thực **OTP** qua Email cho các thao tác quan trọng hoặc quên mật khẩu.
* Bảo mật hệ thống với **CSRF Token**, **Password Hashing (Bcrypt)**, chống **SQL Injection (PDO Prepared Statements)** và **XSS Clean**.
* Tự động nhận diện môi trường (Localhost XAMPP / Hosting InfinityFree).

---

## 🛠 Công Nghệ Sử Dụng (Tech Stack)

| Thành phần | Công nghệ / Thư viện |
| :--- | :--- |
| **Backend** | PHP Native (Kiến trúc MVC Pattern / Clean Code) |
| **Database** | MySQL / MariaDB (Kết nối Singleton PDO) |
| **Frontend** | HTML5, CSS3 Vanilla, JavaScript (ES6+), Bootstrap 5.3 |
| **Icons & Style** | Bootstrap Icons, Google Fonts (Inter, Roboto) |
| **OAuth Providers**| Google Identity, GitHub API, Azure AD (Microsoft) |
| **Server Support** | Apache (`.htaccess` Rewrite Rule) |

---

## 📁 Cấu Trúc Thư Mục Dự Án

```text
WebsiteQuanLyDeTai/
├── assets/                  # Tài nguyên tĩnh (CSS, JS, Images, Logo)
├── auth/                    # Xử lý đăng nhập, đăng xuất, OAuth, OTP
│   ├── google_callback.php
│   ├── github_callback.php
│   ├── login.php
│   └── logout.php
├── config/                  # Cấu hình hệ thống & Database
│   ├── config.php           # Base URL & Environment Config
│   ├── database.php         # Kết nối Database PDO (Singleton)
│   └── faculty_members.php  # Danh sách giảng viên mặc định
├── helpers/                 # Hàm tiện ích dùng chung (Functions, Security)
├── includes/                # Thành phần UI dùng lại (Header, Footer, Navbar)
├── lanh_dao/                # Trang chức năng dành cho Lãnh đạo Khoa
├── giang_vien/              # Trang chức năng dành cho Giảng viên
├── sinh_vien/               # Trang chức năng dành cho Sinh viên
├── models/                  # Lớp xử lý dữ liệu (BaseModel, User, DeTai...)
├── .htaccess                # Cấu hình Rewrite URL & Bảo mật Apache
├── bootstrap.php            # Nạp autoload, session & helper
├── index.php                # Trang chủ hệ thống
└── README.md                # Tài liệu hướng dẫn dự án
```

---

## 🚀 Hướng Dẫn Cài Đặt & Chạy Cục Bộ (Localhost)

### 📋 1. Yêu cầu môi trường
* Đã cài đặt [XAMPP](https://www.apachefriends.org/) (hoặc WampServer, Laragon) với **PHP >= 7.4** và **MySQL >= 5.7**.

### 🔧 2. Các bước thực hiện

1. **Clone repository về thư mục web server:**
   ```bash
   cd c:\xampp\htdocs\
   git clone https://github.com/NhatHao2004/Quanlydetai.git WebsiteQuanLyDeTai
   ```

2. **Tạo và Import Database:**
   * Mở trình duyệt truy cập `http://localhost/phpmyadmin`
   * Tạo cơ sở dữ liệu mới tên: `qldt_database` (tùy chọn Collation: `utf8mb4_unicode_ci`)
   * Import file cơ sở dữ liệu `.sql` của dự án vào database vừa tạo.

3. **Cấu hình kết nối Database:**
   * Mở file `config/database.php` và điều chỉnh thông tin nếu cần:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'qldt_database');
     ```

4. **Khởi chạy ứng dụng:**
   * Khởi động Apache & MySQL trong **XAMPP Control Panel**.
   * Mở trình duyệt và truy cập:
     ```text
     http://localhost/WebsiteQuanLyDeTai/
     ```

---

## 🌐 Triển Khai Lên Hosting (InfinityFree)

Hệ thống đã được tích hợp cơ chế **Auto-Environment Detection** tự động chuyển đổi qua lại giữa môi trường Localhost và Hosting:
1. Upload toàn bộ bộ nguồn lên thư mục `htdocs/` của InfinityFree.
2. Cập nhật thông tin kết nối Database InfinityFree trong `config/database.php`.
3. File `.htaccess` đã được tối ưu sẵn cho hosting root domain.

---

## 🤝 Đóng Góp (Contributing)

Mọi đóng góp nhằm hoàn thiện hệ thống đều được hoan nghênh:
1. Fork dự án
2. Tạo nhánh tính năng mới (`git checkout -b feature/TinhNangMoi`)
3. Commit thay đổi (`git commit -m 'Thêm tính năng mới'`)
4. Push lên nhánh (`git push origin feature/TinhNangMoi`)
5. Tạo **Pull Request**

---

## 📝 Bản Quyền (License)

Dự án phát triển bởi **Khoa Công nghệ Thông tin - Đại học Trà Vinh**. Bản quyền thuộc về tác giả.
