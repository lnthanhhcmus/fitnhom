# CLAUDE.md — quy ước cho phiên viết mã Fit-Nhóm

Fit-Nhóm: hai plugin Moodle 4.5 — `local_fitnhom` (chính) và `assignsubmission_fitnhom` (câu hỏi trên trang nộp bài) — để người nộp khai làm một mình hay bài nhóm cùng ai. **Dự án độc lập** với FIT-CNL (`lnthanhhcmus/fit-cnl`); FIT-CNL chỉ đọc qua `\local_fitnhom\api`. Chủ dự án viết tiếng Việt; tài liệu, chuỗi giao diện, commit bằng tiếng Việt; tên định danh tiếng Việt không dấu.

## Đọc trước khi sửa mã
1. **`docs/TL60_Thiet-ke-dot-1.8_*.md`** (hiện v1.2) — thiết kế **đợt 1.8** (Fit-Nhóm 0.2 + FIT-CNL 1.8.0): hợp đồng đầy đủ (mục 4), đặc tả 0.2 (mục 5), thứ tự làm (mục 7), câu mở phiên (mục 12), **sổ điều chỉnh (mục 13) — điều gì phải làm khác tài liệu thì ghi vào đó trước, đổi cả bản gốc ở `fit-cnl/docs/1.0/`**.
2. `docs/TL54_Fit-Nhom_Thiet-ke_*.md` (hiện v0.5) — thiết kế sản phẩm: mục 0 (vì sao có plugin), quyết định Q1–Q7, cách dùng (mục 2–5), hợp đồng rút gọn (mục 6), rủi ro (mục 8).
3. `docs/so-tay.md` — tình trạng, bản đồ mã, quyết định, việc kế tiếp; `docs/nhat-ky.md`; `docs/lich-su.md`.

## Bắt buộc
- **Giữ hợp đồng API** (TL60 mục 4, bảo đảm H1–H8): trong cùng `api::HOPDONG` không đổi ý nghĩa `dang_bat()`, `thanhvien()`; đổi thì thêm hàm hay tăng `HOPDONG` và ghi vào TL60 mục 13 + sổ tay; bài kiểm `tests/hopdong_test.php` phải đạt.
- Tài liệu dài hạn đổi phiên bản thì bản trước vào `docs/ban-cu/` (tên có phiên bản); Drive không giữ bản cũ.
- Không làm giảng viên thêm việc: không ô bật / tắt trong cài đặt bài tập; không đổi *Tải toàn bộ bài nộp*, sổ điểm.
- Câu khai **không tính là bài nộp** (`is_empty` đúng).
- Bảng có dữ liệu người khai ở `privacy/provider.php`; không ghi tên người được khai vào mô tả nhật ký.
- Hàm chạy từ tác vụ nhận mốc thời gian, không gọi `time()` trực tiếp.
- Mỗi bản: bài kiểm mới, PHPUnit đạt, phpcs (chuẩn moodle) sạch; cập nhật `docs/so-tay.md`, `docs/nhat-ky.md`, `docs/lich-su.md`; gói zip + SHA-256; tài liệu Word gửi chủ dự án; chép lên Drive `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\`.
- Commit tiếng Việt, hai dòng ký theo nhắc của môi trường; `git fetch` trước `git push origin main`.

## Không bao giờ
- Đụng ba Moodle thật của Khoa khi chủ dự án chưa yêu cầu rõ.
- Ghi khóa, mật khẩu, mã truy cập vào kho; đọc tệp khóa / tài khoản trong Drive.
- Để Fit-Nhóm phụ thuộc FIT-CNL (gọi lớp, đọc bảng của FIT-CNL).
