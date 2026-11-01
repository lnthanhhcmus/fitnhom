# Phiếu cài đặt và đi thử Fit-Nhóm 0.2.1

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.2.1 (version 2026110101) · 01/11/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · thiết kế TL54 v0.6, TL60 v1.3 mục 13*

## A. Bản 0.2.1 sửa gì (so với 0.2)

Theo góp ý của chủ dự án khi cài thử 0.2 (01/11/2026). Hợp đồng với FIT-CNL **không đổi**; không đổi bảng.

| | Trước (0.2) | Nay (0.2.1) |
|---|---|---|
| Câu hỏi trên trang nộp bài | *Bài này em làm thế nào?* — *Để các bạn cùng nhóm không bị tính là chưa nộp.* | **Thành viên nhóm** — *Bắt buộc chọn một. Bài nhóm: chọn các bạn cùng làm — các bạn được tính đã nộp, không phải nộp lại.* · ○ Em làm một mình ○ **Bài nhóm — em nộp cho cả nhóm** (Ảnh 01) |
| Chưa chọn mà bấm *Lưu* | trang hiện lại, **không có dòng báo** (lỗi) | **dòng đỏ** *Em chọn "Em làm một mình" hay "Bài nhóm".* ngay dưới hai nút (Ảnh 01) |
| Chọn bạn đã nộp | chỉ khung xanh đầu câu hỏi khi bạn đã nộp có tên em | thêm: cạnh tên **bạn đã nộp có tên em** ghi *"· đã nộp lúc …, có tên em trong nhóm"* (bạn đã nộp mà không có tên em thì không ghi — không lộ ai đã nộp) (Ảnh 02) |
| *Tải toàn bộ bài nộp* | không đổi | thêm **`Thanh-vien-nhom.txt`** trong thư mục `<Họ tên>_<mã>_assignsubmission_fitnhom` cho mỗi bài nộp có câu khai: người nộp, một mình / nhóm cùng ai, bạn báo không cùng làm, **bài nào là bản mới nhất của nhóm**. Tệp em nộp không đổi; chấm ẩn danh thì không có tệp (Ảnh 03) |
| Thư cho bạn được liệt kê | người gửi *"Xin đừng trả lời về địa chỉ gửi thư này"*; *"… khai em cùng làm …"*; *"Nếu không đúng: Không phải em"* | người gửi **FIT-MOODLE**; *"Chào em,"*; *"… liệt kê em là thành viên cùng làm …"*; *"Nếu em không cùng làm bài này, em bấm vào liên kết sau để báo lại: Em không cùng làm bài này"*; ký *"Hệ thống FIT-MOODLE"* (Ảnh 04). Thư báo người nộp khi một bạn báo không cùng làm cũng có chào và ký |
| Cài đặt | — | ô mới **Tên người gửi thông báo** (mặc định `FIT-MOODLE`; để trống thì như Moodle) (Ảnh 05) |

Phần *"(qua Moodle CTDA fit@hcmus)"* sau tên người gửi là **cài đặt chung của Moodle**, Fit-Nhóm không đổi: muốn bỏ thì quản trị trang vào *Quản trị trang › Máy chủ › Email › Email đi › Thông tin "qua"* (Email via information) = *Không bao giờ* — đổi cho **mọi** thư của Moodle trang đó.

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.2.1.zip` | `local/fitnhom` | `97d982a6a3484164a63766fe50f11dc5b0376736c3fddec547ed29b9e28b300c` |
| 2 | `assignsubmission_fitnhom_0.2.1.zip` | `mod/assign/submission/fitnhom` | `8c3a2d15078aa41c78b8c77a4e903dfbe86ebb35f1a63b9daa5070fcccab8f1a` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL. Hai tệp luôn đi cùng nhau, cùng số bản.

## C. Nâng cấp từ 0.2 (hay 0.1, hay cài mới)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.2.1.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước**; trang nâng cấp ghi `local_fitnhom` 2026110100 → **2026110101** (một bước — chỉ đặt *Tên người gửi thông báo* = FIT-MOODLE, không đổi bảng). Từ 0.1 thì hai bước (2026110100, 2026110101) |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.2.1.zip` | đòi `local_fitnhom` ≥ 2026110101 |
| ☐ | **Cài đặt** Fit-Nhóm: thấy ô *Tên người gửi thông báo* = `FIT-MOODLE`; các ô khác và **Chế độ không đổi** | |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | để chữ mới hiện ngay |

## D. Đi thử trên một course (như 0.2 — chế độ Thử nghiệm, một course)

1. ☐ Sinh viên A mở trang nộp bài: mục **Thành viên nhóm**, hai nút *Em làm một mình* / *Bài nhóm — em nộp cho cả nhóm*. Bấm **Lưu** khi chưa chọn → **dòng đỏ** dưới hai nút, bài không lưu (Ảnh 01).
2. ☐ A chọn *Bài nhóm*, chọn B, C; nộp. B, C nhận thư từ **FIT-MOODLE**: *"Chào em, … liệt kê em …"*, liên kết *Em không cùng làm bài này*, ký *Hệ thống FIT-MOODLE* (Ảnh 04). Thư có thể đến sau vài phút (tác vụ gửi thư của Moodle).
3. ☐ B mở trang nộp bài: khung xanh *"Bạn A đã nộp bài này lúc … và có tên em trong nhóm"*; mở ô chọn bạn → cạnh tên A ghi *"đã nộp lúc …, có tên em trong nhóm"*; cạnh tên bạn khác đã nộp (không có tên B) **không** ghi gì (Ảnh 02).
4. ☐ B nộp bản cập nhật (có tên A). C bấm *Em không cùng làm bài này* trong thư → *Không phải em*.
5. ☐ **Giảng viên** *Tải toàn bộ bài nộp* (Download all submissions) → giải nén: cạnh thư mục bài của A, B có thư mục `…_assignsubmission_fitnhom` chứa `Thanh-vien-nhom.txt`; tệp của A ghi C *báo không cùng làm* và *"Nhóm có bản nộp mới hơn: bài của B"*; tệp của B ghi *"Bài này là bản nộp mới nhất của nhóm"* (Ảnh 03). Mở bằng Notepad / Excel không lỗi dấu.
6. ☐ (Nếu dùng *Chú thích PDF* — Annotate PDF) mở chấm bài của A: **không** có trang ghi chú Fit-Nhóm trong PDF.
7. ☐ Các mục D của Phiếu 0.2 (trang *Thành viên nhóm*, Tình hình, hộp nổi) vẫn như cũ.

## E. Bật toàn trang

☐ Ổn → *Chế độ* = **Bật toàn trang**. Gửi giảng viên, sinh viên trang ngắn `Huong-dan\Gui-giang-vien-sinh-vien_0.2.1` (chữ mới *Thành viên nhóm*, thư FIT-MOODLE, tệp ghi chú khi tải bài).

## F. Khi có sự cố — lùi

- **Tắt nhanh**: *Chế độ* = **Tắt** (câu hỏi và tệp ghi chú biến mất ngay; dữ liệu đã khai giữ).
- Chỉ muốn bỏ tên *FIT-MOODLE*: xóa trống ô *Tên người gửi thông báo*.
- Tệp ghi chú làm phiền ai đó khi tải bài: báo phiên phát triển (bỏ được ở bản sau mà không đụng dữ liệu); tạm thời giảng viên bỏ qua thư mục `…_assignsubmission_fitnhom`.
- Về 0.2: Moodle không hạ phiên bản — giữ 0.2.1, đặt *Chế độ = Tắt* và báo phiên phát triển. Các mục khác như Phiếu 0.2 mục F.

## G. Đi thử trên máy phát triển (`dev/walk_021.py`)

Course *FN-021* (một bài tập văn bản trực tuyến), năm sinh viên A–E, một giảng viên; chế độ *Bật toàn trang*. **11/11 đạt**: (1) câu mới, chưa chọn mà bấm Lưu có dòng đỏ, hai nút trong một nhóm (Ảnh 01); (2) A nộp bài nhóm có B, C, E; C báo không cùng làm; B nộp bản cập nhật một giờ sau; D làm một mình — E mở trang nộp: khung xanh, cạnh tên A *"có tên em trong nhóm"*, cạnh tên B, D không ghi gì (Ảnh 02); (3) giảng viên tải toàn bộ bài nộp: ba tệp ghi chú (A, B, D), nội dung đúng (Ảnh 03); (4) thư: người gửi FIT-MOODLE, chào, *liệt kê*, ký (Ảnh 04); (5) Cài đặt có ô *Tên người gửi thông báo* (Ảnh 05).

**Kiểm:** PHPUnit 33 bài (28 bài 0.1–0.2 không đổi + `ban021_test` 5: dòng đỏ, bạn đã nộp có tên em, tệp ghi chú trong gói tải — không cho Chú thích PDF, không khi chấm ẩn danh, cùng dữ liệu cùng nội dung —, gói phản hồi tải lên lại không nhận tệp ghi chú, thư và tên người gửi); bộ kiểm `mod_assign` của Moodle 374 bài đạt khi có Fit-Nhóm 0.2.1; phpcs, moodlecheck sạch. Nâng cấp 0.2 → 0.2.1 trên máy phát triển: *Tên người gửi* = FIT-MOODLE, chế độ giữ nguyên.

*Ảnh chụp ở thư mục `Anh\0.2.1\` (Ảnh 03, 04 dựng lại từ gói tải thật và chuỗi thư thật của plugin — máy phát triển không gửi thư).*
