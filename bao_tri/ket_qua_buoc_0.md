# Kết quả bước 0: chuẩn bị baseline

Ngày thực hiện: 20/11/2026 (Asia/Saigon).
Nhánh làm việc: `develop/maintenance-baseline`, tách từ `3f1f827`.
Nhánh `main` giữ nguyên cấu trúc thư mục nộp đồ án.

## Phạm vi đã hoàn tất

- Git nằm trong thư mục dự án; có bản mốc và nhánh phát triển riêng.
- Sao lưu riêng database đang dùng vào `storage/backups/` (bị Git bỏ qua).
- Xuất schema 13 bảng, tạo seed hoàn toàn giả lập và import thành công vào database độc lập `qldt_baseline_test`.
- Kiểm tra HTTP với ứng dụng thật trên PHP 8.2.12, MariaDB 10.4.32; máy chủ thử tạm chỉ bind loopback cổng 8088 và đã dừng sau kiểm tra.
- Thêm `LOCAL_DB_NAME` cho môi trường local để kiểm tra với database riêng; mặc định vẫn là `qldt_database`.
- Sửa `enforceHttps()` nhận diện hostname khi có cổng: `localhost:8088` trước đây bị chuyển nhầm sang HTTPS. URL localhost không có cổng vẫn trả HTTP 200. Không thay đổi `RewriteBase` vì hiện chưa có rule chuyển URL cần nó.
- Chặn truy cập Apache vào `bao_tri/`, `database/`, `storage/`: đã kiểm tra URL file thật trong cả ba thư mục đều trả HTTP 403.
- Đối chiếu hai bản dump database gốc trước/sau (bỏ dòng thời điểm hoàn tất dump): nội dung giống nhau.

- Kiểm tra cú pháp toàn bộ 107 file PHP của mã nguồn (không tính vendor, cấu hình riêng và script tạm): không có lỗi cú pháp.

## Kết quả kiểm tra

28 mục: **24 đạt, 4 không đạt**. Đây là kết quả HTTP và đối chiếu SQL, chưa phải đánh giá giao diện bằng trình duyệt.

| Mục | Kết quả | Bằng chứng |
|---|---|---|
| Public / | Đạt | HTTP 200, PHP diagnostics=0 (exclude CSS selectors) |
| Public /danh_sach_de_tai_cong_khai.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Public /thong_bao.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Public /thong_bao_do_an.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Guest blocked from student dashboard | Đạt | /WebsiteQuanLyDeTai/auth/login.php |
| Login sv1@example.test | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Login sv2@example.test | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Login gv1@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Login gv2@example.test | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Login ld1@example.test | Đạt | /WebsiteQuanLyDeTai/lanh_dao/dashboard.php |
| Dashboard sinh_vien/dashboard.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Dashboard giang_vien/dashboard.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Dashboard lanh_dao/dashboard.php | Đạt | HTTP 200, PHP diagnostics=0 |
| Student blocked from leadership dashboard | Đạt | /WebsiteQuanLyDeTai/sinh_vien/dashboard.php |
| Lecturer creates draft | Đạt | Matching rows=1 |
| Lecturer edits own draft | Đạt | Checked persisted title |
| Lecturer submits topic | Đạt | Checked persisted status |
| Leadership approves topic | Đạt | Checked persisted status |
| Student registers topic | Đạt | Registration ID=2 |
| Registration waits for lecturer approval | Không đạt | da_duyet |
| Full topic blocks second student (sequential) | Đạt | Sequential check only; no concurrency claim |
| Student views own topics | Đạt | HTTP 200 |
| Student deletes rejected registration | Đạt | JSON success and row removed |
| Student cannot delete approved registration | Đạt | Existing rule permits deletion only after rejection |
| Other lecturer cannot approve registration | Không đạt | Approver user ID=4 (4 is other lecturer) |
| Repeated approval preserves registration counter | Không đạt | Stored counter=4; actual registrations=1 |
| OTP storage table exists | Không đạt | Matching tables=0 |
| Logout clears access | Đạt | /WebsiteQuanLyDeTai/auth/login.php |

## Lỗi nền đã tái hiện / xác minh

| Mã | Hiện trạng | Hướng xử lý tiếp |
|---|---|---|
| B0-01 | Thiếu bảng `otp_verification`; `OTPModel` dùng bảng này. Chưa kiểm tra gửi OTP thực. | Thêm migration có thiết kế hạn dùng/giới hạn thử; không giả định schema hiện tại đã đủ. |
| B0-02 | SV đăng ký thì trạng thái đã là `da_duyet`, trong khi thông báo yêu cầu chờ GV duyệt. | Chốt quy tắc chờ duyệt hay tự duyệt rồi thống nhất trạng thái, thông báo và cách đếm chỗ. |
| B0-03 | GV02 duyệt được đăng ký thuộc đề tài GV01; SQL ghi `nguoi_duyet_id=4`. | Kiểm tra quyền sở hữu ở handler/service và quy định rõ ngoại lệ lãnh đạo. |
| B0-04 | Sau đăng ký và 3 lần duyệt, bộ đếm đề tài là 4 dù chỉ có 1 đăng ký. | Chặn chuyển trạng thái lặp; cập nhật bộ đếm nhất quán, có khóa/giao dịch phù hợp. |

Không sửa các lỗi trên trong bước chuẩn bị. Đây là đầu vào ưu tiên cho đợt vá bảo mật/nghiệp vụ tiếp theo.

## Giới hạn xác minh

- Chưa kiểm tra OAuth thực, gửi email, quên/đặt lại mật khẩu, import/export, phân công chấm và toàn bộ trang lãnh đạo.
- Chưa kiểm tra giao diện trực quan, thiết bị di động hoặc nhiều request đăng ký đồng thời. Ca đủ chỗ chỉ chạy tuần tự.
- Ca đăng ký mong đợi `cho_duyet` dựa trên thông báo hiện hành; cần xác nhận quy tắc nghiệp vụ trước khi sửa.
- Bản dump riêng đã tạo nhưng chưa diễn tập phục hồi toàn bộ; đã kiểm chứng import schema và seed vào database mới.
- Các lỗi bảo mật trong báo cáo đánh giá trước vẫn chưa được coi là đã vá. Seed/database baseline không phải dữ liệu báo cáo trước Hội đồng.

## Cách kiểm tra lại

Xem `database/README.md` để tạo database mới, import schema + seed, chọn `LOCAL_DB_NAME` và thực hiện các ca theo thứ tự. Không tái chạy ca thay đổi dữ liệu trên database thật. Các log, script tạm và kết quả JSON của lần kiểm tra nằm trong `storage/baseline/`, bị Git bỏ qua.

Bước tiếp theo: vá các điểm cấp quyền không hợp lệ, quyền sở hữu khi duyệt, CSRF/phương thức request và bảo toàn trạng thái/bộ đếm đăng ký; bổ sung migration OTP trước khi hoàn tất kiểm tra đăng ký.