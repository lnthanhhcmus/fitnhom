# Phiếu cài đặt và đi thử Fit-Nhóm 0.1

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.1 (version 2026102700) · 26/10/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · thiết kế TL54 v0.3*

## A. Fit-Nhóm làm gì (đọc trước)

Nhiều bài tập / đồ án là **bài nhóm nhưng chỉ một bạn nộp**, nên Moodle (và FIT-CNL đọc dữ liệu Moodle) coi các bạn còn lại là **chưa nộp**. Fit-Nhóm thêm vào **trang nộp bài** một câu hỏi bắt buộc:

> **Bài này em làm thế nào?** ○ Em làm một mình ○ Bài nhóm — chọn các bạn cùng làm *(ô gõ-chọn MSSV – Họ tên)*

- Chỉ **người nộp** trả lời; các bạn được khai nhận **thông báo** Moodle kèm nút **Không phải em**.
- **Giảng viên không phải làm gì.** Không có ô bật / tắt trong cài đặt bài tập; *Tải toàn bộ bài nộp* và sổ điểm **không đổi**; bảng chấm có thêm cột **Thành viên nhóm**.
- Bài nộp **qua ứng dụng điện thoại** (không hiện câu hỏi) → lần sau em vào Moodle bằng trình duyệt có **hộp nổi** hỏi khai. **Không** khai bổ sung bài nộp trước khi bật, **không** thư nhắc, **không** tác vụ chạy đêm.
- Bài đã bật *Nộp theo nhóm* (Students submit in groups) của Moodle: không hỏi.

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.1.zip` | `local/fitnhom` (phần chính, trang quản trị) | `43a8f6e574aa557adfdc8ca42d5fa85c37fbbf6fe010a483713418eef793fa64` |
| 2 | `assignsubmission_fitnhom_0.1.zip` | `mod/assign/submission/fitnhom` (câu hỏi trên trang nộp bài) | `62f0ae2fccf45ed676e62b958bdabd4f9641673c23da9bd0f79b19b3dc893e93` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL; nếu có FIT-CNL thì từ FIT-CNL 1.7.54 mới đọc dữ liệu của Fit-Nhóm.

## C. Cài đặt (quản trị Moodle)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.1.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước** |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.1.zip` | phụ thuộc `local_fitnhom` |
| ☐ | Quản trị trang › Plugin › Bài tập › **Plugin nộp bài**: dòng *Fit-Nhóm — Thành viên nhóm* đang **hiện** (biểu tượng con mắt mở) | ẩn ở đây = tắt hẳn câu hỏi |
| ☐ | Quản trị trang › Plugin › **Mô-đun cục bộ › Fit-Nhóm › Cài đặt**: **Chế độ = Tắt** (mặc định khi mới cài) | chưa hỏi ai |
| ☐ | Cùng trang: **Course không áp dụng — mẫu mã rút gọn** (mặc định `FITCNL:%`, course lớp chủ nhiệm của FIT-CNL); thêm mẫu cho **lớp sinh hoạt chung, course hành chính**; hay chọn **danh mục** loại trừ | mỗi dòng một mẫu, `%` là chuỗi bất kỳ |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | |

Không cài được qua giao diện (máy chủ cấm ghi): giải nén `local_fitnhom_0.1.zip` thành `local/fitnhom/`, `assignsubmission_fitnhom_0.1.zip` thành `mod/assign/submission/fitnhom/` rồi vào *Quản trị trang › Thông báo*.

## D. Thử trên một lớp trước

1. ☐ **Cài đặt**: *Chế độ* = **Thử nghiệm — chỉ các course chọn**; *Course thử nghiệm* = mã rút gọn của **một** lớp môn (mỗi dòng một mã) → *Lưu*. Lần đầu chuyển khỏi *Tắt* được ghi làm **lúc bật**.
2. ☐ **Tình hình và Quét ngay** (cùng mục Fit-Nhóm): dòng *Course thử nghiệm* ghi đúng tên course (không *không có course này*, không *đang bị loại trừ*).
3. ☐ Một sinh viên lớp đó mở **bài tập › Nộp bài**: thấy câu hỏi *Bài này em làm thế nào?*; không chọn thì *Lưu thay đổi* báo thiếu; chọn **Bài nhóm** và gõ tên bạn → lưu (Ảnh 02).
4. ☐ Bạn được khai: chuông thông báo *"… khai em cùng làm …"*; mở trang nộp bài thấy dòng *"Bạn … đã nộp bài này … — em không cần nộp lại"*, ô Bài nhóm điền sẵn (Ảnh 04); bấm liên kết trong thông báo → trang **Không phải em** (Ảnh 05).
5. ☐ Giảng viên: bài tập › **Bài nộp** (Submissions) → cột **Thành viên nhóm**; dòng bạn không tự nộp ghi *Có trong bài nhóm của: …* (Ảnh 06). *Tải toàn bộ bài nộp* như cũ.
6. ☐ Nộp bằng **ứng dụng Moodle trên điện thoại** (nếu có máy): ứng dụng có thể báo *không sửa được bài nộp trong ứng dụng* — ghi lại; nộp được thì lần sau vào bằng trình duyệt thấy **hộp nổi** (Ảnh 07), bấm *Em làm một mình* hay *Bài nhóm…*.
7. ☐ Course **ngoài** danh sách thử: trang nộp bài **không** có câu hỏi.
8. ☐ **Tình hình**: số bài nộp, đã khai một mình / nhóm, *Không phải em*, bài chờ khai; nút **Quét ngay** (tìm bài nộp từ lúc bật chưa có câu khai — vd. nộp qua ứng dụng — đưa vào hộp nổi; không gửi thư) (Ảnh 08).

## E. Bật cho toàn trang

☐ Thử ổn → *Chế độ* = **Bật toàn trang**. Mọi bài tập ở mọi course (trừ course không áp dụng, bài *Nộp theo nhóm*) có câu hỏi. Báo giảng viên, sinh viên bằng trang ngắn `Huong-dan\Gui-giang-vien-sinh-vien_0.1`.

## F. Khi có sự cố

- **Tắt nhanh**: *Chế độ* = **Tắt** (không hỏi nữa, dữ liệu đã khai giữ nguyên); hay ẩn plugin ở *Plugin nộp bài*.
- Trang **trạng thái bài nộp** của em không hiện câu khai (Moodle chỉ hiện phần có nội dung bài nộp); em xem / sửa câu khai ở trang **Sửa bài nộp**. Giảng viên thấy ở bảng chấm.
- Câu khai **không phải** bài nộp: em chỉ khai mà không có tệp / văn bản thì Moodle vẫn coi là chưa nộp.
- **Nâng cấp Moodle** (bản 4.5.x mới hay 5.x): thử lại mục D bước 3–5 trên máy thử trước — trang nộp bài của Assignment có thể đổi.
- Gỡ: Quản trị trang › Plugin › Tổng quan plugin → gỡ `assignsubmission_fitnhom` trước, rồi `local_fitnhom` (xóa dữ liệu câu khai).

## G. Đi thử trên máy phát triển (`dev/walk_01.py` — 12/12 đạt)

Course *FN-THU-01* (thử nghiệm) và *FN-KHAC-01* (ngoài danh sách), bốn sinh viên, một giảng viên: (1) trang Cài đặt có phần mục đích; (2) trang nộp bài có câu hỏi, chưa chọn thì không lưu, An khai Bình; (3) Bình nhận một thông báo, mở trang nộp thấy gợi ý, trang Không phải em; (4) giảng viên thấy cột Thành viên nhóm; (5) Cường nộp như ứng dụng → dải nhắc + hộp nổi, bấm *Em làm một mình*; (6) course ngoài danh sách không hỏi; (7) Tình hình, Quét ngay.

**Kiểm:** PHPUnit 14 bài (`local_fitnhom` 13: phạm vi, câu khai, Không phải em, cùng nhóm, giảng viên nộp thay, chép / xóa, hợp đồng API, hộp nổi / Quét ngay, riêng tư; `assignsubmission_fitnhom` 1); bộ kiểm của Moodle `mod_assign` vẫn đạt khi có plugin (374 bài); FIT-CNL trọn bộ vẫn đạt (681 bài, đồng hồ giả 24/10); phpcs, moodlecheck sạch.

*Ảnh chụp ở mục cuối (máy phát triển giao diện tiếng Anh vì chưa cài gói tiếng Việt của Moodle; chữ của Fit-Nhóm là tiếng Việt).*
