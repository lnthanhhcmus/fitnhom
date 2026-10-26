# Fit-Nhóm

Plugin Moodle 4.5 cho Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM.

**Mục đích.** Nhiều bài tập / đồ án là bài nhóm nhưng chỉ một bạn nộp lên Moodle; Moodle (và các hệ đọc dữ liệu Moodle như FIT-CNL — theo dõi học tập cho chủ nhiệm lớp) coi các bạn còn lại là **không nộp**. Fit-Nhóm thêm vào trang nộp bài một câu hỏi bắt buộc — *Em làm một mình* hay *Bài nhóm cùng các bạn…* (chọn từ danh sách, không gõ tay) — để biết ai cùng nộp. Các bạn được khai tên nhận thông báo và có thể bấm *Không phải em*. Bài đã nộp trước khi cài được khai bổ sung qua hộp nổi và thư nhắc.

Giảng viên không phải làm gì; tải bài về chấm, sổ điểm không đổi. Admin bật / tắt, thử trên một course trước.

| Thành phần | Cài vào |
|---|---|
| `local_fitnhom` | `local/fitnhom` (cài trước) |
| `assignsubmission_fitnhom` | `mod/assign/submission/fitnhom` |

Thiết kế: [`docs/TL54_Fit-Nhom_Thiet-ke_v0.1.md`](docs/TL54_Fit-Nhom_Thiet-ke_v0.1.md). Sổ tay dự án: [`docs/so-tay.md`](docs/so-tay.md).

Plugin khác đọc dữ liệu qua `\local_fitnhom\api` (có số phiên bản hợp đồng `HOPDONG`) — không đọc bảng trực tiếp.

Giấy phép: GNU GPL v3 trở lên.
