# Sổ tay Fit-Nhóm — đọc 5 phút nhớ lại tất cả

Cập nhật: 31/10/2026 — **bản 0.1** (version 2026102700), chờ chủ dự án cài thử. Thiết kế **TL54 v0.4** (31/10: mục 10 — bản 0.2; mục 11 — khung bản 0.3 ứng dụng điện thoại; mục 12 — câu mở phiên). Phía FIT-CNL: TL59 (kho `fit-cnl`, bản 1.8.0).

## 1. Tình trạng
- Kho `lnthanhhcmus/fitnhom`. Thư mục Drive: `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\` (Thiet-ke, Cai-dat, Anh, Huong-dan).
- Hai plugin: `local_fitnhom` (phần chính, `local/fitnhom`) và `assignsubmission_fitnhom` (`mod/assign/submission/fitnhom`, phụ thuộc phần chính).
- 0.1: PHPUnit 14 bài đạt, phpcs + moodlecheck sạch, `dev/walk_01.py` 12/12.

## 2. Vì sao có dự án này
Bài nhóm chỉ một bạn nộp → Moodle / FIT-CNL coi các bạn khác là chưa nộp. Người nộp khai thành viên ngay lúc nộp (TL54 mục 0; các cách đã loại).

## 3. Bản đồ mã
| Tệp | Việc |
|---|---|
| `local_fitnhom/classes/phamvi.php` | Chế độ TAT / THU / BAT, lúc bật (`batdau`), course áp dụng (danh sách thử, mẫu / danh mục loại trừ), bài Nộp theo nhóm không hỏi |
| `local_fitnhom/classes/khai.php` | Lưu câu khai (kiểm bạn trong lớp, tối đa), thông báo bạn được khai, Không phải em (+ hoàn tác 7 ngày), gợi ý theo bạn đã nộp (TL54 3a), cùng nhóm, tóm tắt cho bảng chấm, câu hỏi dùng chung (`them_cau_hoi`) |
| `local_fitnhom/classes/api.php` | **Hợp đồng** cho plugin khác: `HOPDONG` = 1, `dang_bat()`, `thanhvien($assignid)` |
| `local_fitnhom/classes/cho.php`, `observer.php` | Bài nộp chưa có câu khai → danh sách chờ (lúc nộp, Quét ngay); danh sách của một người |
| `local_fitnhom/classes/hook_callbacks.php`, `templates/`, `amd/src/hopnoi.js` | Dải nhắc + hộp nổi một lần mỗi ngày (`local_fitnhom_desau`) |
| `local_fitnhom/khai.php`, `tuchoi.php`, `tinhhinh.php`, `settings.php` | Trang khai, Không phải em, Tình hình + Quét ngay, Cài đặt (chỉ admin) |
| `assignsubmission_fitnhom/locallib.php` | `is_configurable()` false, `is_enabled()` theo phạm vi, câu hỏi, `save`, `is_empty` luôn đúng, `view_summary`, chép / xóa |

## 4. Lệnh
```bash
bash dev/sync.sh        # chép vào ~/moodle-dev/moodle, nâng cấp, xóa đệm (buộc FIT-CNL khởi tạo lại PHPUnit lần sau)
bash dev/test.sh        # PHPUnit hai plugin
bash dev/lint.sh        # phpcs + moodlecheck
bash dev/amd.sh         # dựng amd/build
python3 dev/walk_01.py  # đi thử bằng trình duyệt, ảnh vào docs/anh/0.1/
bash dev/package.sh     # zip + SHA256 vào dist/
python3 dev/mkdocx.py <md> <docx> [thư-mục-ảnh]
```

## 5. Quyết định
- Q1–Q7 của chủ dự án: TL54 mục 0. Q7 (22:12): **không** khai bổ sung bài cũ, không thư nhắc, không tác vụ đêm.
- 0.1 (tôi chọn, ghi ở TL54 / phiếu): không chặn nút *Nộp để chấm* (bài qua ứng dụng không bị kẹt); người nộp thay (quyền sửa bài nộp của em) không bị bắt buộc khai; bảng chấm dòng bạn không tự nộp ghi *Có trong bài nhóm của …*; lúc bật chỉ ghi lần đầu (tắt rồi bật lại không đặt lại).

## 6. Chỗ chưa làm / biết lỗi
- Ứng dụng Moodle trên điện thoại không hiện câu hỏi (chưa thử máy thật) — bài đi vào hộp nổi.
- Trang trạng thái bài nộp của em không hiện câu khai (Moodle chỉ hiện plugin có nội dung).

## 7. Việc kế tiếp
1. Chủ dự án cài thử 0.1 (phiếu cài đặt mục C–D) trên Moodle thử, thử một course.
2. **FIT-CNL 1.8.0** (kho `fit-cnl`, thiết kế `docs/1.0/TL59_Thiet-ke-1.8.0_FIT-CNL-doc-Fit-Nhom_v1.0.md`, 31/10/2026): đọc `\local_fitnhom\api` (bốn điều kiện, thiếu / lạ thì chạy như cũ); lúc nộp của em = sớm nhất trong tự nộp / nhóm Moodle / nộp chung; cột `hd_baitap.nguon`, `nguoinop`; sửa `hoatdong::sap_toi`; TL45 A15-2 ghi ngoại lệ. Các bản 1.7.54 → 1.7.79 của FIT-CNL **chưa** làm việc này (sổ tay FIT-CNL 1.7.79 mục 6.1a ghi nhầm "1.7.58").
3. **Fit-Nhóm 0.2** (TL54 v0.4 mục 10; câu mở phiên mục 12): trang *Thành viên nhóm* cho giảng viên trong course + Excel; loại course theo `course.format = fitcnl`; Tình hình theo course + dấu hiệu bất thường cho quản trị; `api::cua_nguoi()`, `api::ap_dung()` (vẫn HOPDONG 1); `khai::luu` có giao dịch; phiếu cài đặt 0.2.
4. Fit-Nhóm 0.3 (TL54 v0.4 mục 11): ứng dụng Moodle trên điện thoại — chỉ sau khi thử 0.1 trên máy thật.
