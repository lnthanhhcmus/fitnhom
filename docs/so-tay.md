# Sổ tay Fit-Nhóm — đọc 5 phút nhớ lại tất cả

Cập nhật: 01/11/2026 — **bản 0.2.1** (version 2026110101) phát hành theo góp ý của chủ dự án khi cài thử 0.2; 33 bài PHPUnit đạt (14 bài 0.1 + 14 bài 0.2 + 5 bài `ban021_test`), lint sạch, `mod_assign` của Moodle 374 bài đạt, `dev/walk_021.py` đạt. Thiết kế sản phẩm **TL54 v0.6**; **đợt 1.8** ở **TL60 v1.3** (`docs/TL60_…md`, bản chép của `fit-cnl/docs/1.0/` — đổi thì đổi cả hai, ghi mục 13). Bước 2 (FIT-CNL 1.8.0) làm ở kho `fit-cnl` — hợp đồng không đổi ở 0.2.1.

## 1. Tình trạng
- Kho `lnthanhhcmus/fitnhom`. Thư mục Drive: `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\` (Thiet-ke, Cai-dat, Anh, Huong-dan).
- Hai plugin: `local_fitnhom` (phần chính, `local/fitnhom`) và `assignsubmission_fitnhom` (`mod/assign/submission/fitnhom`, phụ thuộc phần chính).
- 0.1: PHPUnit 14 bài đạt, phpcs + moodlecheck sạch, `dev/walk_01.py` 12/12.
- 0.2 (31/10/2026): PHPUnit 28 bài (`ban02_test` 13 + `hopdong_test` thêm 1), phpcs + moodlecheck sạch, `mod_assign` 374 bài đạt khi có plugin, `dev/walk_02.py` 15/15 (8 ảnh `docs/anh/0.2/`). Gói `dist/*_0.2.zip` + `SHA256SUMS_0.2.txt` (không vào kho; chép Drive `Fit-Nhom\Cai-dat\`). Phiếu `docs/huong-dan/Phieu-cai-dat_0.2.md`.
- 0.2.1 (01/11/2026, góp ý khi cài thử 0.2): câu *Thành viên nhóm*; dòng đỏ khi chưa chọn (lỗi 0.1–0.2: hai nút rời không có chỗ hiện lỗi); cạnh tên bạn đã nộp có liệt kê em; tệp `Thanh-vien-nhom.txt` trong *Tải toàn bộ bài nộp* (Q2 điều chỉnh); thư người gửi FIT-MOODLE, có chào / ký, chữ *liệt kê*. PHPUnit 33 bài, `walk_021.py` (5 ảnh `docs/anh/0.2.1/`). Gói `dist/*_0.2.1.zip` + `SHA256SUMS_0.2.1.txt`. Phiếu `docs/huong-dan/Phieu-cai-dat_0.2.1.md`.

## 2. Vì sao có dự án này
Bài nhóm chỉ một bạn nộp → Moodle / FIT-CNL coi các bạn khác là chưa nộp. Người nộp khai thành viên ngay lúc nộp (TL54 mục 0; các cách đã loại).

## 3. Bản đồ mã
| Tệp | Việc |
|---|---|
| `local_fitnhom/classes/phamvi.php` | Chế độ TAT / THU / BAT, lúc bật (`batdau`), course áp dụng: danh sách thử, **định dạng course loại trừ** (`loaitru_format`, mặc định `fitcnl` — 0.2), mẫu / danh mục loại trừ; `ly_do()` (0.2) trả `'' / TAT / KHONG_THU / FORMAT / MAU / DANHMUC`; bài Nộp theo nhóm không hỏi |
| `local_fitnhom/classes/khai.php` | Lưu câu khai (kiểm bạn trong lớp, tối đa), Không phải em (+ hoàn tác 7 ngày), gợi ý theo bạn đã nộp (TL54 3a), cùng nhóm, chép / xóa, `ung_vien`, `nhan`, câu hỏi dùng chung (`them_cau_hoi` — 0.2.1: hai nút trong **một nhóm cùng tên** `fitnhom_loai` để có chỗ hiện dòng đỏ); 0.2.1: `ung_vien_kem()` (ghi cạnh tên bạn đã nộp có liệt kê mình), `ban_da_nop_co_em()`; `tom_tat()` chỉ chuyển tiếp |
| `local_fitnhom/classes/thongbao.php` (0.2) | Hai thông báo Moodle: `duoc_khai()`, `tu_choi()`; 0.2.1: `nguoi_gui()` (người dùng không-trả-lời mang tên cấu hình `nguoigui`, mặc định FIT-MOODLE), `ky_ten()` (dòng ký) |
| `local_fitnhom/classes/tomtat.php` (0.2) | `cua_bai_nop()` chữ cột *Thành viên nhóm* ở bảng chấm; `lien_ket()` liên kết trang giảng viên (một lần mỗi lượt, người chấm được); 0.2.1: `ghi_chu()` nội dung `Thanh-vien-nhom.txt` (UTF-8 BOM, CRLF, không có giờ tạo) |
| `local_fitnhom/classes/api.php` | **Hợp đồng** cho plugin khác: `HOPDONG` = 1, `dang_bat()`, `thanhvien($assignid)`, 0.2 thêm `cua_nguoi($userid, $tu, $den)`, `ap_dung($courseid)` — ngữ nghĩa TL60 mục 4 (H1–H8) |
| `local_fitnhom/classes/cho.php`, `observer.php` | Bài nộp chưa có câu khai → danh sách chờ (lúc nộp, Quét ngay); danh sách của một người; 0.2: `co_cho()` + bộ đệm MUC `local_fitnhom/cho` (`db/caches.php`, `xoa_dem()`), `quet_xem()` trả `['xem', 'them']` (`quet()` giữ trả số — TL60 mục 13 v1.2) |
| `local_fitnhom/classes/baocao.php` (0.2) | Số liệu: `theo_bai()`, `chi_tiet()`, `duoc_tinh_nho_nhom()`, `theo_course()`, `can_xem()`; bảng tính `bang_course()`, `bang_theo_course()`, `ghi_excel()` (ô chữ kiểu chuỗi tường minh), `tai_excel()` |
| `local_fitnhom/classes/output/course.php`, `templates/course.mustache`, `course.php` (0.2) | Trang *Thành viên nhóm* của giảng viên / trợ giảng (`mod/assign:grade`): theo bài, chi tiết, Tải Excel; sự kiện `course_xem` |
| `local_fitnhom/classes/hook_callbacks.php`, `templates/`, `amd/src/hopnoi.js` | Dải nhắc + hộp nổi một lần mỗi ngày (`local_fitnhom_desau`); 0.2: `dau_trang()` giữ điều kiện môi trường, thân ở `them_vao()` (kiểm nhanh `co_cho()` trước) |
| `local_fitnhom/khai.php`, `tuchoi.php`, `tinhhinh.php`, `settings.php`, `lib.php` | Trang khai, Không phải em, Tình hình (+ 0.2: Theo course, Cần xem, đã xem / thêm của Quét ngay), Cài đặt (chỉ admin); `lib.php` 0.2 thêm mục điều hướng course |
| `local_fitnhom/db/upgrade.php` (0.2) | Bước 2026110100: ba cấu hình mới (`loaitru_format`, `batthuong_tuchoi`, `batthuong_khai`), không đổi bảng; bước 2026110101 (0.2.1): `nguoigui` = FIT-MOODLE |
| `assignsubmission_fitnhom/locallib.php` | `is_configurable()` false, `is_enabled()` theo phạm vi, câu hỏi, `save`, `is_empty` luôn đúng, `view_summary` (0.2: liên kết trang giảng viên ở ô đầu), chép / xóa; 0.2.1: `get_files()` — `Thanh-vien-nhom.txt` **chỉ** khi `mod_assign\downloader` dựng gói (`$submission->exportfullpath` có mặt) hay `assignfeedback_file_zip_importer` so gói phản hồi; không cho Chú thích PDF (editpdf cũng gọi `get_files`), không khi chấm ẩn danh |

Bố cục kho: `local_fitnhom/`, `assignsubmission_fitnhom/`, `dev/` (công cụ — `dev/README.md`), `docs/` (TL54, TL60, sổ tay, nhật ký, lịch sử; `huong-dan/` phiếu cài đặt + trang gửi giảng viên / sinh viên; `anh/<bản>/` ảnh đi thử; `ban-cu/` bản cũ của TL54 / TL60). Bản đồ mã sau 0.2: TL60 mục 5.8.

## 4. Lệnh
```bash
bash dev/sync.sh        # chép vào ~/moodle-dev/moodle, nâng cấp, xóa đệm (buộc FIT-CNL khởi tạo lại PHPUnit lần sau)
bash dev/test.sh        # PHPUnit hai plugin
bash dev/lint.sh        # phpcs + moodlecheck
bash dev/amd.sh         # dựng amd/build
python3 dev/walk_01.py  # đi thử 0.1 bằng trình duyệt, ảnh vào docs/anh/0.1/
python3 dev/walk_02.py  # đi thử 0.2 (trang giảng viên, Excel, Tình hình, hộp nổi), ảnh vào docs/anh/0.2/
python3 dev/walk_021.py # đi thử 0.2.1 (dòng đỏ, bạn đã nộp, gói tải có tệp ghi chú, thư), ảnh vào docs/anh/0.2.1/
bash dev/package.sh     # zip + SHA256 vào dist/
python3 dev/mkdocx.py <md> <docx> [thư-mục-ảnh]
```

## 5. Quyết định
- Q1–Q7 của chủ dự án: TL54 mục 0. Q7 (22:12): **không** khai bổ sung bài cũ, không thư nhắc, không tác vụ đêm.
- 0.1 (tôi chọn, ghi ở TL54 / phiếu): không chặn nút *Nộp để chấm* (bài qua ứng dụng không bị kẹt); người nộp thay (quyền sửa bài nộp của em) không bị bắt buộc khai; bảng chấm dòng bạn không tự nộp ghi *Có trong bài nhóm của …*; lúc bật chỉ ghi lần đầu (tắt rồi bật lại không đặt lại).
- Đợt 1.8 (31/10, TL60 mục 2.1): D1–D9 — lúc nộp = `assign_submission.timemodified` (cả hai bên); nhận diện lớp chủ nhiệm bằng `course.format` (mặc định `fitcnl`); thứ tự Fit-Nhóm 0.2 trước, FIT-CNL 1.8.0 sau, một phiên; `HOPDONG` giữ 1 khi chỉ thêm hàm. Q8 (trợ giảng xem trang *Thành viên nhóm*): **có** — ai có `mod/assign:grade`. Q9: giữ khối Cần xem, ngưỡng 3 / 5 (cấu hình).
- 0.2 (phiên viết mã Opus, TL60 mục 13 v1.2): `cho::quet()` giữ trả số (bài kiểm cũ), `quet_xem()` trả mảng; thân hộp nổi ở `hook_callbacks::them_vao()` để bài kiểm gọi; *Bài nộp* ở trang giảng viên / Theo course đếm bài nộp **từ lúc bật**; *Được tính nhờ nhóm* = em có trong `api::thanhvien()` mà không có bài nộp riêng đã gửi; khối Cần xem (a) một dòng mỗi người nộp kèm các course · bài, (b) một dòng mỗi (bài, em); tên chỉ hiện khi bấm *Xem*.
- 0.2.1 (chủ dự án 01/11/2026 10:32–10:45, TL60 mục 13 v1.3): câu hỏi **Thành viên nhóm** (không dùng *"(nếu có)"* vì câu bắt buộc); cạnh tên **chỉ** bạn đã nộp có liệt kê em (không lộ ai đã nộp); không thêm câu *đại diện / bản cập nhật*; **Q2 điều chỉnh** — *Tải toàn bộ bài nộp* có tệp ghi chú (không đổi tên tệp em nộp); thư: người gửi FIT-MOODLE (cấu hình), *Chào em,*, *liệt kê*, liên kết *Em không cùng làm bài này*, ký *Hệ thống FIT-MOODLE*. Phần *"(qua <tên trang>)"* sau tên người gửi là cài đặt chung Moodle (*Email đi › Thông tin "qua"* = *Không bao giờ* thì mất) — việc của quản trị trang, Fit-Nhóm không đổi.

## 6. Chỗ chưa làm / biết lỗi
- Ứng dụng Moodle trên điện thoại không hiện câu hỏi (chưa thử máy thật) — bài đi vào hộp nổi.
- Trang trạng thái bài nộp của em không hiện câu khai (Moodle chỉ hiện plugin có nội dung).
- Rà soát 0.1 ngày 31/10 (TL60 mục 3, R1–R12): **đã làm ở 0.2** (R1 kiểm nhanh + bộ đệm, R3 `upgrade.php`, R4 privacy xuất `cho`, R6 tách `thongbao` / `tomtat`, R7 Quét ngay đếm bài đã xem, R10 `soxem`, R11 bài kiểm hộp nổi + nộp lần hai).
- Thư: tên người gửi do Fit-Nhóm đặt kèm một khoảng trắng thừa trước *"(qua …)"* (Moodle ghép họ + tên, họ để trống — như thư gốc của Moodle); hộp thư web gộp khoảng trắng nên không thấy.
- Chữ trên trang của sinh viên ngoài thư (trang *Không phải em*, hộp nổi) vẫn dùng chữ *khai* — chủ dự án chỉ yêu cầu đổi trong thư; đổi nốt nếu chủ dự án muốn.
- Mục *Thành viên nhóm (Fit-Nhóm)* ở menu *Thêm* của course đã thấy trên theme Boost (walk_02); theme của Trường chưa thử — không thấy thì vào thẳng `local/fitnhom/course.php?id=`.
- Trang giảng viên / Theo course đếm bằng truy vấn gộp, chưa đo trên course nghìn bài nộp (đo khi chủ dự án thử thật).

## 7. Việc kế tiếp
1. Chủ dự án cài thử **0.2.1** (Phiếu cài đặt 0.2.1 mục C–D; nâng từ 0.2 / 0.1 hay cài mới) trên Moodle thử, thử một course; báo nếu menu *Thêm* không có *Thành viên nhóm*.
2. Đợt 1.8 tiếp: **bước 2 FIT-CNL 1.8.0** (TL60 mục 6, kho `fit-cnl`); **bước 3** kiểm chéo TL60 mục 8 (ghi kết quả vào `nhat-ky.md` hai kho).
3. Fit-Nhóm 0.3 (TL60 mục 10): ứng dụng Moodle trên điện thoại — chỉ sau khi thử 0.1 / 0.2 trên máy thật.
