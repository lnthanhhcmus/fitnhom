# CLAUDE.md — quy ước cho phiên viết mã Fit-Nhóm

Fit-Nhóm: hai plugin Moodle 4.5 — `local_fitnhom` (chính) và `assignsubmission_fitnhom` (câu hỏi trên trang nộp bài) — để người nộp khai làm một mình hay bài nhóm cùng ai. **Dự án độc lập** với FIT-CNL (`lnthanhhcmus/fit-cnl`); FIT-CNL chỉ đọc qua `\local_fitnhom\api`. Chủ dự án viết tiếng Việt; tài liệu, chuỗi giao diện, commit bằng tiếng Việt; tên định danh tiếng Việt không dấu.

## Đọc trước khi sửa mã
1. `docs/TL54_Fit-Nhom_Thiet-ke_*.md` (bản mới nhất, hiện v0.4) — mục 0 (vì sao có plugin), quyết định Q1–Q7, hợp đồng API (mục 6), bản 0.2 (mục 10), câu mở phiên (mục 12). Phía FIT-CNL đọc Fit-Nhóm: `fit-cnl/docs/1.0/TL59_*.md`.
2. `docs/so-tay.md` — tình trạng, quyết định, việc kế tiếp; `docs/nhat-ky.md`.

## Bắt buộc
- **Giữ hợp đồng API**: trong cùng `api::HOPDONG` không đổi ý nghĩa `dang_bat()`, `thanhvien()`; đổi thì thêm hàm hay tăng `HOPDONG` và ghi vào sổ tay; bài kiểm `tests/hopdong_test.php` phải đạt.
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
