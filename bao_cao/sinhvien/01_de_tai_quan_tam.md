# 01 — Đề tài quan tâm

## 1. Thông tin chung

- Ngày: 21/11/2026 (Asia/Saigon).
- Vai trò sử dụng chính: Sinh viên; Lãnh đạo cấu hình giới hạn và lịch đăng ký.
- Trạng thái: đã triển khai trên demo, đã kiểm tra chức năng và hồi quy; chờ người dùng dùng thử.
- Nhánh: `develop/maintenance-baseline`. Chưa commit thay đổi chức năng này.
- Kiến trúc: giữ PHP theo trang/handler hiện tại, chưa chuyển MVC. SQL tập trung trong Model; chính sách thời gian và trạng thái giao diện dùng lớp riêng.

## 2. Mục tiêu

Sinh viên lưu trước các đề tài phù hợp và mở danh sách riêng khi đến đợt đăng ký, giảm thời gian tìm lại. Quan tâm không tạo đăng ký, không giữ chỗ và không có ưu tiên so với người khác.

## 3. Quy tắc đã thống nhất

- Nút **Quan tâm / Đã quan tâm** trên thẻ đề tài; bấm Đã quan tâm để bỏ lưu. Trang chi tiết cũng có nút này.
- Sidebar có **Đề tài quan tâm**, kèm số lượng hiện tại/giới hạn; danh sách riêng lọc Tất cả, Cơ sở ngành hoặc Chuyên ngành.
- Mặc định 6 đề tài cho mỗi Sinh viên, tính tổng cả hai hệ. Lãnh đạo được đặt từ 1 đến 50 qua Cài đặt thông số → Đăng ký đề tài.
- Giảm giới hạn giữ nguyên những mục đã lưu; không cho thêm mới khi số lượng đã chạm/vượt mức mới. Không xóa thay Sinh viên.
- Chỉ thêm đề tài đang được duyệt/công khai. Có thể thêm/bỏ trước, trong hoặc sau đợt đăng ký, kể cả khi đề tài đã hết chỗ.
- Đề tài đã lưu sau đó bị ẩn hoặc xóa sẽ hiện **Đề tài hiện không khả dụng**, vẫn bỏ lưu được. Không hiển thị nội dung nháp mới sửa.
- Nút Đăng ký chỉ có khi trong lịch, không tạm đóng, còn chỗ, còn hạn mức Giảng viên và Sinh viên đủ điều kiện. Server kiểm tra lại trong giao dịch.
- Đăng ký thành công được duyệt ngay theo yêu cầu; mục quan tâm vẫn giữ, trạng thái đổi thành **Đã đăng ký**.
- Thêm trùng khi chưa đăng ký hệ đó không tạo thêm bản ghi hoặc tiêu tốn thêm hạn mức; sau khi đăng ký hệ đó, mọi request thêm đều bị chặn.
- Quy tắc cập nhật ngày 21/11/2026: đăng ký thành công CSN thì khóa thêm quan tâm mọi đề tài CSN; CN tương tự. Hệ chưa đăng ký vẫn cho quan tâm. Mục đã lưu từ trước luôn cho bỏ quan tâm. Nút bị khóa chỉ có chú thích ngắn theo hệ, không thêm đoạn giải thích dài trên thẻ; server kiểm tra lại trong giao dịch.

## 4. Luồng sử dụng

1. Đăng nhập Sinh viên → Đề tài có thể đăng ký → chọn Quan tâm.
2. Nút đổi thành Đã quan tâm; bộ đếm sidebar cập nhật sau khi chuyển trang.
3. Mở Đề tài quan tâm để xem các đề tài đã lưu, giảng viên, số chỗ và trạng thái mới nhất.
4. Trước thời gian mở: hiển thị thời điểm mở và khóa hành động đăng ký; vẫn thêm/bỏ quan tâm được.
5. Trong thời gian hợp lệ: bấm Đăng ký ngay trên mục đã lưu → được xác nhận → chuyển tới Đề tài của tôi.
6. Nếu người khác lấy chỗ trước, request bị từ chối và danh sách được tải lại với sức chứa mới nhất.

## 5. Các file thêm/sửa

| Nhóm | File và trách nhiệm |
|---|---|
| Dữ liệu quan tâm | `models/DeTaiQuanTamModel.php`: truy vấn danh sách, số lượng, quyền sở hữu, thêm trong giao dịch và bỏ bằng điều kiện đúng chủ |
| Chính sách | `helpers/DangKyThoiGian.php`: lịch dùng chung; `helpers/DeTaiQuanTamService.php`: trạng thái/điều kiện hiển thị |
| Trang và handler | `sinh_vien/de_tai_quan_tam.php`, `sinh_vien/xu_ly_quan_tam.php` |
| Thành phần dùng lại | `sinh_vien/includes/quan_tam_button.php`, `quan_tam_sidebar.php`, `dang_ky_button.php` |
| Tích hợp | Danh sách, chi tiết, dashboard và đề tài của tôi của Sinh viên; `models/DeTaiModel.php` chỉ trả đề tài còn chỗ như code cũ, với số chỗ tính theo đăng ký thực tế |
| Đăng ký | `models/RegistrationWorkflow.php` kiểm tra lịch trong transaction; `sinh_vien/dang_ky_de_tai.php` điều phối đăng ký và giữ ngữ cảnh khi thất bại |
| Cấu hình Lãnh đạo | `lanh_dao/includes/cai_dat_dang_ky.php`, `lanh_dao/xu_ly_cai_dat_dang_ky.php`, tab mới trong `cai_dat_thong_so.php`, `CaiDatModel::setValue()` |
| Giao diện riêng | `assets/css/de_tai_quan_tam.css`, `assets/js/de_tai_quan_tam.js`: chỉ chỉnh bố cục trang quan tâm theo chiều cao navbar, không đổi landing page |
| Nạp và whitelist | `bootstrap.php`, `models/BaseModel.php` |
| Cài đặt/kiểm tra | Migration 002, schema/seed, `database/apply_002.php`, `tests/followed/`; cập nhật fixture thời gian của bộ kiểm tra bảo mật |

## 6. Database và lịch đăng ký

### Bảng mới

`de_tai_quan_tam`: ID, Sinh viên, Đề tài, tiêu đề/hệ đào tạo đã lưu, thời điểm quan tâm.

- Unique `(sinh_vien_id, de_tai_id)` chống lưu trùng.
- Xóa Sinh viên: cascade danh sách của người đó.
- Xóa Đề tài: `de_tai_id` thành NULL, giữ mục không khả dụng để Sinh viên tự bỏ.
- Khi đề tài vẫn công khai, hiển thị tên/hệ hiện tại; khi không khả dụng, chỉ dùng thông tin đã lưu.

### Thông số

`gioi_han_de_tai_quan_tam`, `cho_phep_dang_ky` và bốn mốc `dang_ky_csn_bat_dau`, `dang_ky_csn_ket_thuc`, `dang_ky_cn_bat_dau`, `dang_ky_cn_ket_thuc` nằm trong `cai_dat`.

Trước thay đổi, lịch nằm trực tiếp trong trang danh sách, và hai ngày đóng đã được người dùng chỉnh sang tháng 12. Đã đưa các giá trị đó sang cấu hình, giữ nguyên:

| Hệ | Mở | Đóng |
|---|---|---|
| Cơ sở ngành | 22/09/2026 07:00:00 | 23/12/2026 23:59:59 |
| Chuyên ngành | 24/09/2026 07:00:00 | 25/12/2026 23:59:59 |

Thời gian Việt Nam. Cho phép đăng ký ngay tại mốc mở và mốc đóng; trước mốc mở/sau mốc đóng bị chặn. Ngày không hợp lệ bị từ chối, lịch lưu sai không mở đăng ký.

### Áp dụng

- Database mới: import `database/schema.sql` rồi `database/seed.sql`.
- Database có sẵn: sao lưu, chọn đúng database rồi import `database/migrations/002_de_tai_quan_tam.sql`; hoặc chạy `php database/apply_002.php` với cấu hình trỏ đúng database cần cập nhật.
- Migration có thể chạy lại; không xóa dữ liệu, không ghi đè giá trị thông số đã có.
- Đã áp dụng trên `qldt_demo`, sao lưu trước ở `storage/backups/`. Chưa áp dụng trên `qldt_database` thật; cần migration trước khi dùng chức năng ở đó.

## 7. Quyền và bảo mật

- Vai trò/CSRF dùng cơ chế hiện có; thêm/bỏ và lưu cấu hình chỉ qua POST.
- ID Sinh viên lấy từ session và hồ sơ; không nhận ID người sở hữu từ form/query.
- Model kiểm tra lại chủ danh sách; bỏ lưu yêu cầu ID mục và đúng Sinh viên.
- Khi thêm, kiểm tra bản đăng ký `da_duyet` của đúng Sinh viên theo hệ của đề tài sau khi khóa hồ sơ; chặn mọi đề tài cùng hệ. Luồng đăng ký dùng cùng khóa hồ sơ nên việc kiểm tra không bị bỏ qua bởi các request đồng thời.
- Thêm quan tâm khóa cấu hình giới hạn → hồ sơ Sinh viên → đề tài trong transaction READ COMMITTED. Lãnh đạo ghi giới hạn theo cùng thứ tự khóa để tránh vượt hạn mức khi nhiều tab cập nhật.
- Kiểm tra sức chứa, quota và lịch đăng ký trong transaction đăng ký hiện có; theo dõi không cập nhật bộ đếm đăng ký.
- Nội dung xuất HTML được escape; URL quay về chỉ nhận danh sách đích/bộ lọc cho phép.
- Snapshot giữ thông tin đã công khai, không lấy nội dung nháp mới vào trang theo dõi.

## 8. Kết quả kiểm thử

Chi tiết từng ca nằm trong [kết quả chức năng](../kiem_thu/01_de_tai_quan_tam.md).

- 64/64 mục kiểm tra tính năng đạt: HTTP, quyền/CSRF, giới hạn chung, giảm giới hạn, ẩn/xóa, đăng ký đúng lịch, biên thời gian và trình duyệt.
- Hai nhóm thêm đồng thời: 10 thao tác không vượt giới hạn 3; 8 thao tác trùng chỉ tạo một bản ghi.
- Đã xem ảnh máy tính và điện thoại; trang mới không tràn ngang, navbar không che các mục đầu và footer không bị sidebar che.
- Bộ 112 ca bảo mật/hồi quy cũ đạt sau tích hợp.
- Kiểm tra cú pháp 135 file PHP: không lỗi. Đã xác nhận đăng nhập demo và mở hai trang mới trả HTTP 200.
- Đối chiếu dump trước/sau: database thật không thay đổi; hai ngày đóng đăng ký tháng 12 trên demo được giữ nguyên.
- Không dùng database thật để kiểm thử thay đổi dữ liệu. Demo chỉ nhận migration, không chạy các kịch bản xóa hoặc thay lịch thử nghiệm 2100.

## 9. Dùng thử trên demo

Mở `http://localhost/WebsiteQuanLyDeTai/auth/login.php`, dùng tài khoản demo đã cấp trong seed. Không ghi mật khẩu thật vào báo cáo.

**Sinh viên:** `sv1@example.test` hoặc `sv2@example.test` → Đề tài có thể đăng ký → Quan tâm → sidebar Đề tài quan tâm. Thử thêm/bỏ, lọc hai hệ và đăng ký khi đủ điều kiện.

**Lãnh đạo:** `ld1@example.test` → Cài đặt thông số → tab Đăng ký đề tài. Thử giới hạn 5 hoặc 6; nếu thử thời gian mở trong tương lai, ghi nhớ các mốc đang dùng để khôi phục sau khi thử.

**Đường dẫn nhanh:** `sinh_vien/de_tai_quan_tam.php`, `lanh_dao/cai_dat_thong_so.php#tab-dang-ky`.

## 10. Giới hạn và hướng tiếp theo

- Chưa có thông báo tự động khi đề tài thay đổi hoặc mở đăng ký; cập nhật trạng thái khi tải lại trang.
- Chưa xếp hạng/thứ tự ưu tiên hoặc giữ chỗ; không phân giới hạn theo học kỳ/năm học.
- Giao diện dùng form POST và chuyển trang, chưa làm AJAX cập nhật danh sách ngay trên thẻ.
- Giới hạn 1–50 là giới hạn kiểm tra đầu vào của phiên bản này, mặc định nghiệp vụ 6.
- Migration cần áp dụng vào database thật trước khi chuyển cấu hình khỏi demo.
- Chưa chuyển MVC, chưa commit/push phần này để người dùng duyệt thử.

## 11. Đối chiếu phần Sinh viên với code Git cũ

Đã đối chiếu `3f1f827` (trước bảo trì), mốc bảo mật `671c275` và phần tính năng chưa commit. Phạm vi gồm Model đề tài, handler đăng ký, danh sách, chi tiết, dashboard và Đề tài của tôi.

| Nội dung | Code cũ | Kết quả sau đối chiếu |
|---|---|---|
| Danh sách đăng ký | Lọc đề tài chưa đủ số Sinh viên | Khôi phục lọc này; không giữ các thẻ đã hết chỗ trong danh sách đăng ký |
| Đề tài nhận 1 Sinh viên | Đăng ký thành công thì đủ chỗ, biến mất khỏi danh sách | Khôi phục đúng hành vi; danh sách quan tâm riêng vẫn giữ mục đã lưu |
| Đề tài nhận nhiều Sinh viên | Vẫn xuất hiện đến khi hết chỗ | Giữ đúng điều kiện cũ, không nhầm thành ẩn mọi đề tài ngay sau đăng ký đầu tiên |
| Giới hạn mỗi hệ | Code cũ đã kiểm tra CSN/CN và khóa nút khi Sinh viên đã đủ | Giữ kiểm tra; bỏ dòng giải thích mới “Mỗi sinh viên được đăng ký một đề tài được duyệt cho mỗi hệ” khỏi thẻ |
| Nút đăng ký ở danh sách | Nút đỏ “Đăng ký ngay”; lý do khóa trong title | Khôi phục nhãn/màu/cách hiển thị gọn; POST/CSRF vẫn được giữ |
| Thông báo mới sau đăng ký | Không có câu “Đề tài đã được xác nhận cho bạn” trên thẻ | Bỏ câu này, thông báo thành công chỉ là “Đăng ký đề tài thành công.” |
| Dashboard / Đề tài của tôi | Các nội dung vốn có | Chỉ bổ sung mục sidebar quan tâm, giữ nội dung cũ |
| Ngày đăng ký | Viết trực tiếp trong trang; người dùng đã chỉnh ngày đóng sang tháng 12 | Giữ các giá trị đã chỉnh trong cấu hình Lãnh đạo, không trả về mốc tháng 9 trong bản Git cũ |

Trước đối chiếu, tính năng quan tâm đã vô tình bỏ bộ lọc đề tài đầy khỏi danh sách đăng ký và thêm các đoạn diễn giải. Đã sửa lại. Mục đã quan tâm riêng vẫn hiển thị trạng thái hết chỗ/đã đăng ký để Sinh viên xem hoặc bỏ lưu, vì đây là chức năng mới được giữ.

Đăng ký vẫn tự duyệt theo yêu cầu đã xác nhận; không khôi phục thông báo chờ duyệt sai trạng thái. Giữ các kiểm tra bảo mật, quyền sở hữu, thời gian và giao dịch đã vá. Chưa commit/push thay đổi.

## 12. Luồng theo dõi sau khi đăng ký — quy tắc hiện hành

- Đề tài nhận một Sinh viên; đăng ký hợp lệ được duyệt ngay. Khi người khác đăng ký thì đề tài hết chỗ, không có tình huống vẫn còn chỗ trong nghiệp vụ hiện tại.
- Danh sách đăng ký thông thường tiếp tục ẩn đề tài đã có người đăng ký.
- Trong danh sách quan tâm: nếu người khác đăng ký, giữ mục với nhãn **Đã có sinh viên đăng ký**, khóa nút **Đăng ký**, vẫn có **Bỏ quan tâm**. Nhãn này được ưu tiên hơn lý do Sinh viên đã có đề tài cùng hệ để thể hiện đúng trạng thái đề tài.
- Nếu chính Sinh viên đăng ký: giữ mục đã lưu với nhãn **Đã đăng ký**, nút **Đề tài của tôi** và **Bỏ quan tâm**.
- Đăng ký một hệ thì khóa thêm quan tâm mọi đề tài trong hệ đó; vẫn được quan tâm hệ chưa đăng ký. Khi đã đăng ký cả hai hệ thì không thêm quan tâm mới.
- Xóa/bỏ những mục đã lưu không hủy đăng ký thật và không bị chặn theo hệ; mục đã bỏ không thể thêm lại nếu hệ đó đã đăng ký.
- Phần này thay thế quy tắc thử trước đó chỉ chặn chính đề tài đã đăng ký. Không tự xóa hoặc sửa danh sách/dữ liệu đăng ký hiện có.

## Điều chỉnh giao diện cuối đợt

Bỏ footer thông tin riêng ở trang Đề tài quan tâm theo yêu cầu. Vẫn tải các script và thẻ đóng trang từ include chung; các trang khác giữ footer hiện có.
