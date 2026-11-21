# Kiểm thử — Đề tài quan tâm

Ngày: 21/11/2026. Database giả lập: `qldt_follow_test`; máy chủ PHP loopback cổng 8089. Node.js/Edge headless kiểm tra thao tác thực và màn hình 1280px/390px. Không dùng dữ liệu thật để thử thay đổi.

Kết quả: **64/64 mục đạt**. Hồi quy hệ thống cũ: **112/112 mục đạt** trên `qldt_security_test`, tách riêng với demo.

| Ca kiểm tra | Kết quả | Bằng chứng |
|---|---|---|
| Empty saved list displays guidance | Đạt | HTTP 200 |
| GET cannot change saved topics | Đạt | HTTP 405 |
| Missing CSRF cannot save | Đạt | HTTP 403 |
| Lecturer cannot use student followed actions | Đạt | /WebsiteQuanLyDeTai/giang_vien/dashboard.php |
| Saving before registration opens is allowed | Đạt | Saved while schedule starts in 2100 |
| Saved page blocks quick registration before opening | Đạt | Server-rendered action unavailable |
| Direct POST cannot register before opening | Đạt | Database unchanged |
| Repeated save is idempotent | Đạt | One row only |
| Default limit accepts six across both types | Đạt | Combined limit, not six per type |
| Seventh topic is rejected | Đạt | No extra row |
| Type filter returns only selected topics | Đạt | Two saved major topics |
| Query cannot expose another student list | Đạt | Student identity derives from session |
| Cross-student removal is rejected | Đạt | Owner row preserved |
| Draft and nonexistent topics cannot be saved | Đạt | No invalid saved entries |
| Leadership can reduce limit without deleting entries | Đạt | Existing six retained |
| Reduced limit blocks additional saves | Đạt | Still six |
| Invalid limits do not change settings | Đạt | Reject 0, 51 and non-integer |
| Student cannot change followed limit | Đạt | Leadership only |
| Invalid dates roll back all configuration changes | Đạt | Limit unchanged with invalid date range |
| Hidden saved topic shows unavailable without draft content | Đạt | Uses title snapshot |
| Deleted topic remains removable as unavailable | Đạt | ON DELETE SET NULL |
| Removing unavailable entry frees a followed slot | Đạt | Can add after removal |
| Following never reserves registration slots | Đạt | No registrations or counters added |
| Saved topic reports another student has registered | Đạt | One seat: another successful registration makes the topic unavailable |
| Catalog hides a topic filled by another student | Đạt | Old catalog filtering restored; saved list keeps its status |
| Following does not bypass full-topic validation | Đạt | Only the first student registered |
| Direct POST cannot register after closing | Đạt | Expired schedule enforced at server |
| Global pause blocks registration | Đạt | Pause takes priority |
| Global registration pause still permits following | Đạt | Independent from registration permission |
| Quick registration approves immediately in valid window | Đạt | Confirmed business rule |
| Saved item reflects own successful registration | Đạt | Entry remains saved; no auto-removal |
| Previously saved registered topic still allows removal | Đạt | Existing saved entry retained |
| Registered unsaved topic disables follow button with type tooltip | Đạt | No extra explanation paragraph |
| Catalog hides own registered topic after last seat is taken | Đạt | Matches pre-maintenance behavior for one-student topics |
| Catalog omits newly introduced explanation paragraphs | Đạt | Registration controls remain compact |
| Direct POST cannot follow an already registered topic | Đạt | Server rechecks registered training type inside transaction |
| Direct POST cannot follow another topic of registered type | Đạt | Restriction applies to every topic of the registered type |
| Another topic of registered type has disabled follow button | Đạt | Detail and catalog share the same component |
| Taken topic status takes precedence over same-type restriction | Đạt | Other student took the one seat; saved row retained |
| Previously saved topic of registered type can still be removed | Đạt | Removal stays enabled |
| Removed same-type topic cannot be added again after registration | Đạt | No re-add bypass |
| Topics of the unregistered type can still be followed | Đạt | CSN still open for following after CN registration |
| Ten simultaneous saves respect limit of three | Đạt | Saved rows=3 |
| Eight simultaneous duplicate saves create one row | Đạt | Matching rows=1 |
| Just before opening | Đạt | Clock injected directly in policy test, never from HTTP parameters |
| Exact opening time | Đạt | Clock injected directly in policy test, never from HTTP parameters |
| Exact closing time | Đạt | Clock injected directly in policy test, never from HTTP parameters |
| Just after closing | Đạt | Clock injected directly in policy test, never from HTTP parameters |
| Invalid stored schedule fails closed | Đạt | Invalid date does not allow registration |
| Leadership registration tab opens and saves | Đạt |  |
| Sidebar shows followed count and configured limit | Đạt |  |
| Topic card toggles to saved via POST | Đạt |  |
| Saved page shows topic and blocks pre-opening registration | Đạt |  |
| Saved state survives page reload | Đạt |  |
| Saved page fits mobile viewport | Đạt |  |
| Quick registration appears when schedule opens | Đạt |  |
| Quick registration submits POST successfully | Đạt |  |
| Registered saved item updates its state | Đạt |  |
| Removing updates empty state and sidebar | Đạt |  |
| Registered topic follow button is disabled with type tooltip | Đạt |  |
| Catalog removes the registered one-student topic | Đạt |  |
| Other topics of registered type have disabled follow buttons | Đạt |  |
| Unregistered type still permits following | Đạt |  |
| No JavaScript exceptions in followed flow | Đạt |  |

## Kiểm tra trực quan

Đã xem ảnh trang quan tâm trên máy tính và điện thoại. Trang mới dùng CSS/JS riêng để lấy chiều cao navbar thực, giữ sidebar/nội dung/footers không bị che. Không đổi bố cục landing page.

Ảnh chạy thử cục bộ: `storage/baseline/followed-desktop.png`, `storage/baseline/followed-mobile.png`. Ảnh và log runtime bị Git bỏ qua, không phải tài liệu chứa dữ liệu người dùng thật.

## Chạy lại

```powershell
./tests/followed/run.ps1 -ResetTestDatabase -Browser
./tests/security/run.ps1 -ResetTestDatabase -Browser
```

Switch reset chỉ xóa các database thử viết cố định trong từng runner. Đọc `tests/followed/README.md` trước khi chạy. Kết quả JSON đầy đủ lưu trong `storage/baseline/`.

## Giới hạn

Chưa kiểm tra tải lớn, hosting production hoặc gửi thông báo tự động. Trạng thái chỗ trống được cập nhật khi tải lại, server vẫn kiểm tra lại trong giao dịch khi bấm đăng ký. Demo đã nhận migration, database thật chưa được migrate.
## Xác minh bàn giao

- 135 file PHP kiểm tra cú pháp không lỗi.
- Tài khoản Sinh viên/Lãnh đạo demo đăng nhập và mở trang mới thành công.
- Demo giữ giới hạn 6, ngày đóng CSN 23/12/2026 và CN 25/12/2026.
- Dump database thật trước/sau giống nhau khi bỏ thời điểm hoàn tất dump.
