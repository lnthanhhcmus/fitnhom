# dev/ — công cụ cho máy phát triển Fit-Nhóm

Cần Moodle phát triển của FIT-CNL (`fit-cnl/dev/1.0/setup.sh` dựng vào `~/moodle-dev/moodle`; hai kho đặt cạnh nhau). Chạy từ gốc kho `fitnhom`.

| Lệnh | Việc |
|---|---|
| `bash dev/sync.sh` | chép hai plugin vào Moodle phát triển, nâng cấp, xóa bộ đệm (FIT-CNL phải khởi tạo lại PHPUnit lần sau) |
| `bash dev/test.sh` | PHPUnit hai plugin (0.1: 14 bài) |
| `bash dev/lint.sh` | phpcs (chuẩn moodle) + moodlecheck — phải sạch |
| `bash dev/amd.sh` | dựng `amd/build` |
| `python3 dev/walk_<bản>.py` | đi thử bằng trình duyệt (Playwright), ảnh vào `docs/anh/<bản>/` |
| `bash dev/package.sh` | zip hai plugin + SHA-256 vào `dist/` (không đưa `dist/` vào kho) |
| `python3 dev/mkdocx.py <md> <docx> [thư-mục-ảnh]` | bản Word của tài liệu cho chủ dự án |

Mỗi bản thêm `walk_<bản>.py` mới, không sửa walk cũ. MariaDB dừng thì `service mariadb start`.
