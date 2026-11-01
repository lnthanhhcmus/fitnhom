# Phiếu cài đặt và đi thử Fit-Nhóm 0.2.3

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.2.3 (version 2026110103) · 01/11/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · thiết kế TL54 v0.6 mục 3, TL60 v1.3 mục 13*

## A. Bản 0.2.3 sửa gì (so với 0.2.2)

Theo yêu cầu của chủ dự án (01/11/2026 12:44): *"Nếu trước đây là bài nhóm mà sau đó có chọn lại một mình thì khi lưu sẽ xóa các thành viên khác. Cũng email đến thành viên đó về sự thay đổi. Kể cả khi thêm/bớt thành viên so với đợt nộp trước."* Hợp đồng với FIT-CNL **không đổi**; không đổi bảng. Các thay đổi của 0.2.1, 0.2.2 (câu *Thành viên nhóm*, dòng đỏ, tệp ghi chú khi tải bài, thư FIT-MOODLE) giữ nguyên, xem Phiếu 0.2.2.

| Người nộp mở lại trang nộp bài, sửa rồi Lưu | Trước (0.1–0.2.2) | Nay (0.2.3) |
|---|---|---|
| Ô *các bạn cùng làm* | chỉ hiện khi chọn *Bài nhóm* | như cũ (Ảnh 01) |
| Chọn lại **Em làm một mình** | các bạn bị xóa khỏi danh sách, **không ai được báo** | các bạn bị xóa; mỗi bạn nhận thư **"A không còn liệt kê em trong "X""** (Ảnh 02) |
| **Bớt** bạn | bạn bị bỏ bị xóa, không được báo | bị xóa, nhận thư như trên |
| **Thêm** bạn | bạn mới nhận thư *"A liệt kê em cùng làm "X""* | như cũ |
| Bạn giữ nguyên | không nhận thư | như cũ (không nhận lại mỗi lần nộp bản cập nhật) |
| Bạn bị bỏ đã bấm *Không phải em* | — | bị xóa, **không** nhận thư (bạn ấy đã báo không cùng làm) |
| Bạn bị bỏ mở liên kết *Em không cùng làm bài này* trong thư cũ | trang lỗi *"Không tìm thấy dữ liệu"* | trang báo *"Bài nộp này không còn liệt kê em … Em không cần làm gì thêm."* (Ảnh 03) |

Thư cho bạn bị bỏ: người gửi FIT-MOODLE, *"Chào em,"* — *"Bạn A vừa sửa bài nộp X (môn Y) và không còn liệt kê em là thành viên cùng làm, nên em không còn được tính là đã nộp theo bài của bạn ấy. Nếu em có cùng làm bài này, em trao đổi lại với bạn A, hoặc tự nộp bài ở trang bài tập."* — ký *Hệ thống FIT-MOODLE*. Trong *Tùy chọn thông báo* của em, loại thông báo mới tên *Em không còn được liệt kê trong một bài nộp* (bật sẵn chuông và thư như hai loại cũ).

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.2.3.zip` | `local/fitnhom` | `1244758311b3f98e837e5a4ff037434f244b0e535e61b06d22ba04220577966a` |
| 2 | `assignsubmission_fitnhom_0.2.3.zip` | `mod/assign/submission/fitnhom` | `3cdf4a205610e4d58992e8ce5120ef2560deede2a7e659a0bd8f847ba5fed2ca` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL. Hai tệp luôn đi cùng nhau, cùng số bản.

## C. Nâng cấp từ 0.2.2 (hay 0.2.1, 0.2, 0.1, hay cài mới)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.2.3.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước**; `local_fitnhom` → **2026110103**. Từ 0.2.2 / 0.2.1: không có bước nâng cấp CSDL (Moodle tự đăng ký loại thông báo mới); từ 0.2: một bước đặt *Tên người gửi thông báo* = FIT-MOODLE |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.2.3.zip` | đòi `local_fitnhom` ≥ 2026110103 |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | |

## D. Đi thử trên một course

1. ☐ Sinh viên A nộp *Bài nhóm* với B, C → B, C nhận thư *"A liệt kê em cùng làm …"*.
2. ☐ A mở lại trang nộp bài, bỏ C, thêm D, Lưu → C nhận thư *"A không còn liệt kê em …"*; D nhận thư *"liệt kê em"*; B **không** nhận thư mới.
3. ☐ C mở liên kết *Em không cùng làm bài này* trong thư đầu → trang báo nhẹ, không lỗi (Ảnh 03).
4. ☐ A mở lại, chọn **Em làm một mình** → ô các bạn ẩn (Ảnh 01); Lưu → B, D nhận thư *"không còn liệt kê em"*; bảng chấm cột *Thành viên nhóm* của A ghi *Làm một mình*; B, D không còn *Có trong bài nhóm của A*.
5. ☐ Thư có thể đến sau vài phút (tác vụ gửi thư của Moodle).

## E. Khi có sự cố — lùi

- **Tắt nhanh**: *Chế độ* = **Tắt**. Chỉ muốn tắt riêng thư mới: Quản trị trang › Chung › Nhắn tin › *Cài đặt thông báo* — tắt loại *Em không còn được liệt kê trong một bài nộp* của Fit-Nhóm.
- Moodle không hạ phiên bản — giữ 0.2.3, báo phiên phát triển. Các mục khác như Phiếu 0.2.2 mục F.

## F. Đi thử trên máy phát triển (`dev/walk_023.py`)

Course *FN-023*, bốn sinh viên A–D, chế độ *Bật toàn trang*. **8/8 đạt**: (1) A nộp bài nhóm có B, C rồi sửa — bỏ C, thêm D: danh sách còn B, D; thư: C *không còn*, D *liệt kê*, B không nhận lại; (2) A mở lại trang nộp bằng trình duyệt — *Bài nhóm*, ô các bạn hiện; chọn *Em làm một mình* — ô ẩn (Ảnh 01); Lưu — danh sách trống, B, D nhận thư *không còn*, C không nhận lại; (3) nội dung thư: chào, *không còn liệt kê em*, liên kết trang bài tập, ký (Ảnh 02); (4) C mở liên kết cũ — trang báo nhẹ (Ảnh 03).

**Kiểm:** PHPUnit 35 bài (34 local + 1 assignsubmission; mới `ban023_test` 2 bài: thêm / bớt bạn, đổi sang một mình kèm bạn đã bấm *Không phải em*; bài cũ `khai_test::test_sua_cau_khai` chỉnh một dòng — xem TL60 mục 13); bộ kiểm `mod_assign` của Moodle 374 bài đạt khi có Fit-Nhóm 0.2.3; phpcs, moodlecheck sạch.

*Ảnh ở thư mục `Anh\0.2.3\` (Ảnh 02 lấy từ thông báo Moodle thật đã ghi — máy phát triển không gửi thư).*
