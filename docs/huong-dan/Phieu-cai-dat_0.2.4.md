# Phiếu cài đặt và đi thử Fit-Nhóm 0.2.4

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.2.4 (version 2026110104) · 01/11/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · thiết kế TL54 v0.6 mục 2*

## A. Bản 0.2.4 sửa gì (so với 0.2.3)

Theo góp ý của chủ dự án (01/11/2026 15:11): *"Câu màu đỏ "Em chọn ..." nên tắt đi khi sinh viên đã chọn."* — và yêu cầu 12:44 *"chỗ các bạn cùng làm chỉ xuất hiện khi sinh viên chọn bài nhóm"*. Chỉ sửa trang nộp bài; không đổi chữ, không đổi bảng, hợp đồng với FIT-CNL **không đổi**. Thư *"không còn liệt kê em"* của 0.2.3 và các thay đổi trước giữ nguyên (Phiếu 0.2.3).

| Trên trang nộp bài | Trước (0.1–0.2.3) | Nay (0.2.4) |
|---|---|---|
| Mở trang, **chưa chọn nút nào** | ô *Các bạn cùng làm* **vẫn hiện** (lỗi) | ô ẩn (Ảnh 01) |
| Bấm Lưu khi chưa chọn | trang tải lại, dòng đỏ *"Em chọn "Em làm một mình" hay "Bài nhóm"."* | như cũ (Ảnh 01) |
| Sau đó bấm *Bài nhóm* hay *Em làm một mình* | dòng đỏ **còn nguyên** tới lần Lưu sau | dòng đỏ **tắt ngay** (Ảnh 02) |
| Chọn *Bài nhóm* | ô *Các bạn cùng làm* hiện | như cũ |
| Chọn *Em làm một mình* | ô ẩn | như cũ |

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.2.4.zip` | `local/fitnhom` | `1f73fe09122ad8ed3695b0fc867f10d05e9f888db649067feaba7f62de6a5d65` |
| 2 | `assignsubmission_fitnhom_0.2.4.zip` | `mod/assign/submission/fitnhom` | `487eb5f982281444c88a3069fde85ba3b77ed8cbd3168a8f0981a46307706621` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL. Hai tệp luôn đi cùng nhau, cùng số bản.

## C. Nâng cấp từ 0.2.3 (hay bản cũ hơn, hay cài mới)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.2.4.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước**; `local_fitnhom` → **2026110104**. Từ 0.2.3 / 0.2.2 / 0.2.1: không có bước nâng cấp CSDL; từ 0.2: một bước đặt *Tên người gửi thông báo* = FIT-MOODLE |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.2.4.zip` | đòi `local_fitnhom` ≥ 2026110104 |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | **cần** — bản này có mã JavaScript mới; sinh viên đang mở trang thì tải lại trang |

## D. Đi thử trên một course

1. ☐ Sinh viên mở trang nộp bài của một bài tập **chưa nộp** → hai nút chưa chọn, **không** thấy ô *Các bạn cùng làm*.
2. ☐ Nhập bài, bấm **Lưu** mà chưa chọn → trang tải lại, dòng đỏ *"Em chọn …"* dưới hai nút.
3. ☐ Bấm *Bài nhóm* → dòng đỏ tắt, ô *Các bạn cùng làm* hiện. Bấm *Em làm một mình* → ô ẩn, không dòng đỏ. Lưu → được.
4. ☐ Phần thư khi sửa danh sách: như Phiếu 0.2.3 mục D.

## E. Khi có sự cố — lùi

- **Tắt nhanh**: *Chế độ* = **Tắt**. Moodle không hạ phiên bản — giữ 0.2.4, báo phiên phát triển. Các mục khác như Phiếu 0.2.2 mục F.

## F. Đi thử trên máy phát triển (`dev/walk_024.py`)

Course *FN-023*, sinh viên A, chế độ *Bật toàn trang*. **6/6 đạt**: (0) mở trang, chưa chọn — ô các bạn ẩn; (1) Lưu khi chưa chọn — dòng đỏ hiện, ô các bạn vẫn ẩn (Ảnh 01); (2) chọn *Bài nhóm* — dòng đỏ tắt, ô các bạn hiện (Ảnh 02); (3) đổi sang *Em làm một mình* — không dòng đỏ, ô ẩn; Lưu — câu khai *Em làm một mình* đã ghi.

**Kiểm:** PHPUnit 37 bài (36 local + 1 assignsubmission; mới `ban024_test` 2 bài: trang nạp mô-đun tắt dòng đỏ, điều kiện ẩn ô các bạn; bài cũ không sửa); bộ kiểm `mod_assign` của Moodle 374 bài đạt khi có Fit-Nhóm 0.2.4; phpcs, moodlecheck sạch.

*Ảnh ở thư mục `Anh\0.2.4\`.*
