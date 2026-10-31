# Sổ tay Fit-Nhóm — đọc 5 phút nhớ lại tất cả

Cập nhật: 31/10/2026 tối — **bản 0.1** (version 2026102700), chờ chủ dự án cài thử; kho rà soát 31/10 (14 bài PHPUnit đạt, lint sạch, TL60 mục 3). Thiết kế sản phẩm **TL54 v0.5**; **đợt 1.8** (Fit-Nhóm 0.2 + FIT-CNL 1.8.0) ở **TL60 v1.0** (`docs/TL60_…md`, bản chép của `fit-cnl/docs/1.0/` — đổi thì đổi cả hai, ghi mục 13). TL59 (kho `fit-cnl`) đã bị TL60 thay.

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
| `local_fitnhom/classes/api.php` | **Hợp đồng** cho plugin khác: `HOPDONG` = 1, `dang_bat()`, `thanhvien($assignid)` (0.2 thêm `cua_nguoi()`, `ap_dung()`) — ngữ nghĩa TL60 mục 4 |
| `local_fitnhom/classes/cho.php`, `observer.php` | Bài nộp chưa có câu khai → danh sách chờ (lúc nộp, Quét ngay); danh sách của một người |
| `local_fitnhom/classes/hook_callbacks.php`, `templates/`, `amd/src/hopnoi.js` | Dải nhắc + hộp nổi một lần mỗi ngày (`local_fitnhom_desau`) |
| `local_fitnhom/khai.php`, `tuchoi.php`, `tinhhinh.php`, `settings.php` | Trang khai, Không phải em, Tình hình + Quét ngay, Cài đặt (chỉ admin) |
| `assignsubmission_fitnhom/locallib.php` | `is_configurable()` false, `is_enabled()` theo phạm vi, câu hỏi, `save`, `is_empty` luôn đúng, `view_summary`, chép / xóa |

Bố cục kho: `local_fitnhom/`, `assignsubmission_fitnhom/`, `dev/` (công cụ — `dev/README.md`), `docs/` (TL54, TL60, sổ tay, nhật ký, lịch sử; `huong-dan/` phiếu cài đặt + trang gửi giảng viên / sinh viên; `anh/<bản>/` ảnh đi thử; `ban-cu/` bản cũ của TL54 / TL60). Bản đồ mã sau 0.2: TL60 mục 5.8.

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
- Đợt 1.8 (31/10, TL60 mục 2.1): D1–D9 — lúc nộp = `assign_submission.timemodified` (cả hai bên); nhận diện lớp chủ nhiệm bằng `course.format` (mặc định `fitcnl`); thứ tự Fit-Nhóm 0.2 trước, FIT-CNL 1.8.0 sau, một phiên; `HOPDONG` giữ 1 khi chỉ thêm hàm. Chờ chủ dự án: Q8 (trợ giảng xem trang *Thành viên nhóm* — mặc định có), Q9 (ngưỡng Cần xem 3 / 5 — mặc định giữ).

## 6. Chỗ chưa làm / biết lỗi
- Ứng dụng Moodle trên điện thoại không hiện câu hỏi (chưa thử máy thật) — bài đi vào hộp nổi.
- Trang trạng thái bài nộp của em không hiện câu khai (Moodle chỉ hiện plugin có nội dung).
- Rà soát 0.1 ngày 31/10 (TL60 mục 3, R1–R12): hộp nổi chạy join ở mọi trang (R1 → 0.2 kiểm nhanh + bộ đệm); chưa có `db/upgrade.php` (R3); privacy chưa xuất `cho` (R4); `khai.php` gồm bốn việc (R6 → tách `thongbao`, `tomtat`); chưa có bài kiểm cho hộp nổi theo ngày và nộp lần hai (R11). Không lỗi an toàn.

## 7. Việc kế tiếp
1. Chủ dự án cài thử 0.1 (phiếu cài đặt mục C–D) trên Moodle thử, thử một course. Trả lời Q8, Q9 của TL60 mục 2.2 (không chặn việc).
2. **Đợt 1.8 — một phiên viết mã (Opus), câu mở phiên TL60 mục 12**: bước 0 dựng máy, hai bộ kiểm xanh (767 FIT-CNL, 14 Fit-Nhóm); **bước 1 Fit-Nhóm 0.2** (TL60 mục 5: upgrade → phạm vi theo `format` → `cua_nguoi` / `ap_dung` → tách tệp → hộp nổi nhẹ → báo cáo quản trị → trang giảng viên → privacy → `ban02_test` ≥ 12 → `walk_02` → phát hành 0.2, dừng gửi gói); **bước 2 FIT-CNL 1.8.0** (TL60 mục 6, kho `fit-cnl`); **bước 3** kiểm chéo TL60 mục 8.
3. Fit-Nhóm 0.3 (TL60 mục 10): ứng dụng Moodle trên điện thoại — chỉ sau khi thử 0.1 / 0.2 trên máy thật.
