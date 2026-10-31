# Fit-Nhóm

Plugin Moodle 4.5 cho Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM.

**Mục đích.** Nhiều bài tập / đồ án là bài nhóm nhưng chỉ một bạn nộp lên Moodle; Moodle (và các hệ đọc dữ liệu Moodle như FIT-CNL — theo dõi học tập cho chủ nhiệm lớp) coi các bạn còn lại là **chưa nộp**. Fit-Nhóm thêm vào trang nộp bài một câu hỏi bắt buộc — *Em làm một mình* hay *Bài nhóm cùng các bạn…* (chọn từ danh sách, không gõ tay) — để biết ai cùng nộp. Các bạn được khai tên nhận thông báo và có thể bấm *Không phải em*. Bài nộp không có câu khai (ứng dụng điện thoại) được hỏi ở hộp nổi lần sau em vào Moodle bằng trình duyệt. Không khai bổ sung bài nộp trước khi bật.

Giảng viên không phải làm gì; tải bài về chấm, sổ điểm không đổi; bảng chấm có thêm cột *Thành viên nhóm*. Admin bật / tắt, thử trên một course trước (Quản trị trang › Plugin › Mô-đun cục bộ › Fit-Nhóm).

| Thành phần | Cài vào |
|---|---|
| `local_fitnhom` | `local/fitnhom` (cài trước) |
| `assignsubmission_fitnhom` | `mod/assign/submission/fitnhom` |

- Thiết kế sản phẩm: [`docs/TL54_Fit-Nhom_Thiet-ke_v0.5.md`](docs/TL54_Fit-Nhom_Thiet-ke_v0.5.md) — vì sao có, Q1–Q7, cách dùng, hợp đồng (bản cũ ở `docs/ban-cu/`).
- **Đợt 1.8** (Fit-Nhóm 0.2 đã phát hành 31/10/2026; FIT-CNL 1.8.0 — bước 2): [`docs/TL60_Thiet-ke-dot-1.8_Fit-Nhom-0.2-va-FIT-CNL-1.8.0_v1.2.md`](docs/TL60_Thiet-ke-dot-1.8_Fit-Nhom-0.2-va-FIT-CNL-1.8.0_v1.2.md) — bản chép của `fit-cnl/docs/1.0/`; mục 12 câu mở phiên, mục 13 sổ điều chỉnh.
- Sổ tay dự án (bản đồ mã, lệnh, quyết định): [`docs/so-tay.md`](docs/so-tay.md); nhật ký [`docs/nhat-ky.md`](docs/nhat-ky.md); lịch sử bản [`docs/lich-su.md`](docs/lich-su.md).
- Cài đặt và đi thử: [`docs/huong-dan/`](docs/huong-dan/).

Plugin khác đọc dữ liệu qua `\local_fitnhom\api` (số phiên bản hợp đồng `HOPDONG`) — không đọc bảng trực tiếp.

Giấy phép: GNU GPL v3 trở lên.
