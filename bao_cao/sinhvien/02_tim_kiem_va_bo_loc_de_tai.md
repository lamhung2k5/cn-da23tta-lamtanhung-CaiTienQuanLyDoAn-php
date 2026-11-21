# Tìm kiếm và bộ lọc đề tài

Ngày thực hiện: 21/11/2026. Đã triển khai, chờ dùng thử giao diện demo.

## Thay đổi và cách dùng

Thanh tìm kiếm đặt dưới hai tab Cơ sở ngành / Chuyên ngành. Nhập tên đề tài hoặc tên giảng viên, chọn giảng viên, trạng thái quan tâm (tất cả / đã / chưa quan tâm), sắp xếp (mới nhất / cũ nhất / tên A–Z), rồi bấm Tìm kiếm hoặc Enter. Bỏ tự tải lại trang sau khi ngừng gõ 0,5 giây.

Các điều kiện kết hợp đồng thời. Trang giữ lựa chọn và hiển thị số kết quả. Xóa bộ lọc trở về mặc định của tab hiện tại. Chuyển tab giữ từ khóa, quan tâm và sắp xếp; nếu giảng viên không có đề tài khả dụng ở hệ mới thì đưa về Tất cả.

Danh sách giảng viên lấy từ đề tài đã duyệt, chưa đủ số người đăng ký trong hệ đang xem, độc lập với từ khóa và trạng thái quan tâm. Đề tài đã có người đăng ký tiếp tục bị loại theo quy tắc hiện hành. Quyền quan tâm và đăng ký giữ nguyên.

## Kỹ thuật và phạm vi

- Giữ kiến trúc PHP hiện tại; dùng GET lưu điều kiện trên URL.
- Không đổi cấu trúc hoặc dữ liệu CSDL.
- Quan tâm lọc theo hồ sơ sinh viên đang đăng nhập.
- Truy vấn dùng tham số ràng buộc; thứ tự sắp xếp lấy từ danh sách cố định.
- Riêng trang này tìm tiêu đề và tên giảng viên, không tìm mô tả. Các nơi gọi model theo cách cũ vẫn giữ phạm vi tìm cũ.
- Chưa thêm bộ lọc chuyên ngành, năm học, học kỳ; cần thống nhất dữ liệu và nghiệp vụ trước. Bộ lọc công nghệ đã bổ sung như mô tả bên dưới.

Các file: `sinh_vien/danh_sach_de_tai.php`, `models/DeTaiModel.php`, `tests/followed/search.php`.

## Kiểm tra

Lệnh `E:/xampp/php/php.exe tests/followed/search.php` đạt 16/16 kiểm tra chỉ đọc trên CSDL riêng `qldt_follow_test`: hai hệ, giảng viên, phân chia đã/chưa quan tâm đúng sinh viên, thứ tự thời gian, thực thi sắp xếp tên, từ khóa chứa ký tự SQL và kết hợp điều kiện. Cú pháp PHP các file thay đổi đạt.

Chưa kiểm tra trực quan bằng trình duyệt trong lần chỉnh này. Cần dùng thử demo trên máy tính/điện thoại: nhập rồi Enter, giữ lựa chọn sau tìm kiếm và chuyển hệ, Xóa bộ lọc, không có kết quả. Không commit hoặc push trong lần thực hiện này.

## Bổ sung: Công nghệ sử dụng

Thêm danh sách chọn Công nghệ sử dụng, mặc định Tất cả công nghệ. Các lựa chọn lấy độc lập từ toàn bộ đề tài khả dụng trong hệ đang xem, dùng trường `cong_nghe` hiện có. Form tạo/sửa đề tài cũ hướng dẫn nhập dấu phẩy, ví dụ `PHP, MySQL, Bootstrap, React`.

Tách theo dấu phẩy, bỏ mục rỗng, chuẩn hóa khoảng trắng và gộp tên chỉ khác hoa/thường. So khớp nguyên tên công nghệ: Java không khớp JavaScript; giữ nguyên dấu trong C++, .NET, Node.js. Không tự gộp các tên khác cách viết như React/ReactJS/React.js. Không sửa dữ liệu gốc hoặc form giảng viên.

Công nghệ kết hợp với từ khóa, giảng viên, quan tâm và sắp xếp. Giữ lựa chọn sau tìm kiếm; khi đổi hệ nếu không còn công nghệ đó thì về Tất cả. Xóa bộ lọc xóa cả lựa chọn công nghệ. Tham số công nghệ dạng mảng hoặc không thuộc danh sách của hệ được bỏ qua.

Không thêm bảng/cột. Model lọc tên công nghệ trong PHP sau truy vấn SQL, giữ thứ tự đã chọn. Cách này phù hợp với danh sách hiện chưa phân trang; nếu bổ sung phân trang cần đưa điều kiện vào bước tính tổng và lấy trang, hoặc chuẩn hóa bảng công nghệ trước.

Demo hiện có 6 đề tài chưa điền công nghệ, vì vậy chỉ có Tất cả công nghệ và lời nhắc chưa có dữ liệu. Giảng viên có thể dùng form sửa đề tài hiện tại để bổ sung; không tự thêm dữ liệu giả vào demo.

Kiểm thử `E:/xampp/php/php.exe tests/followed/technology.php`: 21/21 đạt, bao gồm tên đầy đủ, hoa/thường, khoảng trắng, trường rỗng, ký tự đặc biệt, Java/JavaScript và bộ lọc kết hợp trên cả hai hệ. Dữ liệu tạm chỉ ghi trong transaction ở `qldt_follow_test` và được rollback. Chạy lại `tests/followed/search.php`: 16/16 đạt. PHP lint model và trang danh sách đạt. Chưa kiểm tra trực quan trên trình duyệt trong lần bổ sung này.
