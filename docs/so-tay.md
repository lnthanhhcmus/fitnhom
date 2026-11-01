# Sổ tay Fit-Nhóm — đọc 5 phút nhớ lại tất cả

Cập nhật: 01/11/2026 17:00 — **bản 0.2.5** (version 2026110105): **sửa lỗi** đổi Chế độ trên trang cài đặt không ghi *Lúc bật* — trang *Thành viên nhóm* báo *"Chưa có bài nộp nào"*, Quét ngay / hộp nổi / Theo course coi như chưa bật (chủ dự án cài thử 16:24); bước nâng cấp ghi bù. 0.2.4 (15:40) ô các bạn ẩn khi chưa chọn, dòng đỏ tắt khi chọn; 0.2.3 thư *"không còn liệt kê em"*; 0.2.2, 0.2.1 chữ và góp ý cài thử 0.2. 40 bài PHPUnit đạt, lint sạch, `mod_assign` 374 bài đạt, `dev/walk_025.py` 4/4. Thiết kế sản phẩm **TL54 v0.6**; **đợt 1.8** ở **TL60 v1.3** (`docs/TL60_…md`, bản chép của `fit-cnl/docs/1.0/` — đổi thì đổi cả hai, ghi mục 13). Bước 2 (FIT-CNL 1.8.0) làm ở kho `fit-cnl` — hợp đồng không đổi.

## 1. Tình trạng
- Kho `lnthanhhcmus/fitnhom`. Thư mục Drive: `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\` (Thiet-ke, Cai-dat, Anh, Huong-dan).
- Hai plugin: `local_fitnhom` (phần chính, `local/fitnhom`) và `assignsubmission_fitnhom` (`mod/assign/submission/fitnhom`, phụ thuộc phần chính).
- 0.1: PHPUnit 14 bài đạt, phpcs + moodlecheck sạch, `dev/walk_01.py` 12/12.
- 0.2 (31/10/2026): PHPUnit 28 bài (`ban02_test` 13 + `hopdong_test` thêm 1), phpcs + moodlecheck sạch, `mod_assign` 374 bài đạt khi có plugin, `dev/walk_02.py` 15/15 (8 ảnh `docs/anh/0.2/`). Gói `dist/*_0.2.zip` + `SHA256SUMS_0.2.txt` (không vào kho; chép Drive `Fit-Nhom\Cai-dat\`). Phiếu `docs/huong-dan/Phieu-cai-dat_0.2.md`.
- 0.2.1 (01/11/2026, góp ý khi cài thử 0.2): câu *Thành viên nhóm*; dòng đỏ khi chưa chọn (lỗi 0.1–0.2: hai nút rời không có chỗ hiện lỗi); cạnh tên bạn đã nộp có liệt kê em; tệp `Thanh-vien-nhom.txt` trong *Tải toàn bộ bài nộp* (Q2 điều chỉnh); thư người gửi FIT-MOODLE, có chào / ký, chữ *liệt kê*. PHPUnit 33 bài, `walk_021.py` (5 ảnh `docs/anh/0.2.1/`). Gói `dist/*_0.2.1.zip` + `SHA256SUMS_0.2.1.txt`. Phiếu `docs/huong-dan/Phieu-cai-dat_0.2.1.md`.
- 0.2.2 (01/11/2026 11:30): chỉ đổi chữ — bỏ dòng *"Bắt buộc chọn một …"* (dễ hiểu nhầm là bắt buộc làm một mình), nút *Bài nhóm*; nhãn nằm trên nhóm hai nút. Gói `dist/*_0.2.2.zip` + `SHA256SUMS_0.2.2.txt`, phiếu `docs/huong-dan/Phieu-cai-dat_0.2.2.md`, ảnh `docs/anh/0.2.2/`.
- 0.2.3 (01/11/2026 13:10): thông báo thứ ba `khongcon` cho bạn bị bỏ khi người nộp sửa câu khai (bớt bạn, đổi sang một mình); liên kết cũ *Em không cùng làm bài này* của bạn bị bỏ không báo lỗi; mỗi thư gửi riêng. `ban023_test` 2 bài, `walk_023.py` 8/8 (3 ảnh `docs/anh/0.2.3/`). Gói `dist/*_0.2.3.zip` + `SHA256SUMS_0.2.3.txt`, phiếu `docs/huong-dan/Phieu-cai-dat_0.2.3.md`.
- 0.2.4 (01/11/2026 15:40): ô *Các bạn cùng làm* ẩn khi nút *Bài nhóm* chưa được chọn (`hideIf … 'notchecked', NHOM` thay `'neq'` — `lib/form/form.js` bỏ qua nút chưa chọn nên lúc chưa chọn nút nào ô vẫn hiện); mô-đun `local_fitnhom/cauhoi` tắt dòng đỏ *"Em chọn …"* (lỗi máy chủ dựng khi bấm Lưu mà chưa chọn — Moodle không tự xóa) khi em bấm một nút. `ban024_test` 2 bài, `walk_024.py` 6/6 (2 ảnh `docs/anh/0.2.4/`). Gói `dist/*_0.2.4.zip` + `SHA256SUMS_0.2.4.txt`, phiếu `docs/huong-dan/Phieu-cai-dat_0.2.4.md`.
- 0.2.5 (01/11/2026 17:00): hàm gọi lại khi đổi cấu hình là hàm của lớp `phamvi::chedo_doi()` / `phamvi_doi()` (tự nạp) thay hàm ở `lib.php` — trang cài đặt lưu cấu hình trước khi `lib.php` được nạp, Moodle bỏ qua lặng lẽ nên *Lúc bật* (`batdau`) không bao giờ được ghi khi admin đổi Chế độ trên trang; bước nâng cấp 2026110105 ghi bù (`db/upgradelib.php`: lúc câu khai sớm nhất, chưa có thì lúc nâng cấp); `baocao::theo_bai()` / `theo_course()` luôn đếm bài nộp có câu khai. `ban025_test` 3 bài (lưu qua `admin_write_settings`), `walk_025.py` 4/4 (đổi Chế độ trên trang). Gói `dist/*_0.2.5.zip` + `SHA256SUMS_0.2.5.txt`, phiếu `docs/huong-dan/Phieu-cai-dat_0.2.5.md`.

## 2. Vì sao có dự án này
Bài nhóm chỉ một bạn nộp → Moodle / FIT-CNL coi các bạn khác là chưa nộp. Người nộp khai thành viên ngay lúc nộp (TL54 mục 0; các cách đã loại).

## 3. Bản đồ mã
| Tệp | Việc |
|---|---|
| `local_fitnhom/classes/phamvi.php` | Chế độ TAT / THU / BAT, lúc bật (`batdau`; 0.2.5: `chedo_doi()` / `phamvi_doi()` là hàm gọi lại của trang cài đặt), course áp dụng: danh sách thử, **định dạng course loại trừ** (`loaitru_format`, mặc định `fitcnl` — 0.2), mẫu / danh mục loại trừ; `ly_do()` (0.2) trả `'' / TAT / KHONG_THU / FORMAT / MAU / DANHMUC`; bài Nộp theo nhóm không hỏi |
| `local_fitnhom/classes/khai.php` | Lưu câu khai (kiểm bạn trong lớp, tối đa), Không phải em (+ hoàn tác 7 ngày), gợi ý theo bạn đã nộp (TL54 3a), cùng nhóm, chép / xóa, `ung_vien`, `nhan`, câu hỏi dùng chung (`them_cau_hoi` — 0.2.1: hai nút trong **một nhóm cùng tên** `fitnhom_loai` để có chỗ hiện dòng đỏ); 0.2.1: `ung_vien_kem()` (ghi cạnh tên bạn đã nộp có liệt kê mình), `ban_da_nop_co_em()`; `tom_tat()` chỉ chuyển tiếp |
| `local_fitnhom/classes/thongbao.php` (0.2) | Thông báo Moodle: `duoc_khai()`, `tu_choi()`, 0.2.3 `khong_con()` (bạn bị bỏ khi người nộp sửa câu khai — `khai::luu` gom `$bot`, gửi sau giao dịch, mỗi thư một `try`); 0.2.1: `nguoi_gui()` (người dùng không-trả-lời mang tên cấu hình `nguoigui`, mặc định FIT-MOODLE), `ky_ten()` (dòng ký) |
| `local_fitnhom/classes/tomtat.php` (0.2) | `cua_bai_nop()` chữ cột *Thành viên nhóm* ở bảng chấm; `lien_ket()` liên kết trang giảng viên (một lần mỗi lượt, người chấm được); 0.2.1: `ghi_chu()` nội dung `Thanh-vien-nhom.txt` (UTF-8 BOM, CRLF, không có giờ tạo) |
| `local_fitnhom/classes/api.php` | **Hợp đồng** cho plugin khác: `HOPDONG` = 1, `dang_bat()`, `thanhvien($assignid)`, 0.2 thêm `cua_nguoi($userid, $tu, $den)`, `ap_dung($courseid)` — ngữ nghĩa TL60 mục 4 (H1–H8) |
| `local_fitnhom/classes/cho.php`, `observer.php` | Bài nộp chưa có câu khai → danh sách chờ (lúc nộp, Quét ngay); danh sách của một người; 0.2: `co_cho()` + bộ đệm MUC `local_fitnhom/cho` (`db/caches.php`, `xoa_dem()`), `quet_xem()` trả `['xem', 'them']` (`quet()` giữ trả số — TL60 mục 13 v1.2) |
| `local_fitnhom/classes/baocao.php` (0.2) | Số liệu: `theo_bai()`, `chi_tiet()`, `duoc_tinh_nho_nhom()`, `theo_course()`, `can_xem()`; bảng tính `bang_course()`, `bang_theo_course()`, `ghi_excel()` (ô chữ kiểu chuỗi tường minh), `tai_excel()` |
| `local_fitnhom/classes/output/course.php`, `templates/course.mustache`, `course.php` (0.2) | Trang *Thành viên nhóm* của giảng viên / trợ giảng (`mod/assign:grade`): theo bài, chi tiết, Tải Excel; sự kiện `course_xem` |
| `local_fitnhom/classes/hook_callbacks.php`, `templates/`, `amd/src/hopnoi.js` | Dải nhắc + hộp nổi một lần mỗi ngày (`local_fitnhom_desau`); 0.2: `dau_trang()` giữ điều kiện môi trường, thân ở `them_vao()` (kiểm nhanh `co_cho()` trước) |
| `local_fitnhom/amd/src/cauhoi.js` (0.2.4) | Câu *Thành viên nhóm*: em bấm một nút thì tắt dòng đỏ *"Em chọn …"* của khung nhóm (`[data-groupname="fitnhom_loai"]`); nạp từ `khai::them_cau_hoi()` |
| `local_fitnhom/khai.php`, `tuchoi.php`, `tinhhinh.php`, `settings.php`, `lib.php` | Trang khai, Không phải em, Tình hình (+ 0.2: Theo course, Cần xem, đã xem / thêm của Quét ngay), Cài đặt (chỉ admin); `lib.php` 0.2 thêm mục điều hướng course |
| `local_fitnhom/db/upgrade.php` (0.2) | Bước 2026110100: ba cấu hình mới (`loaitru_format`, `batthuong_tuchoi`, `batthuong_khai`), không đổi bảng; bước 2026110101 (0.2.1): `nguoigui` = FIT-MOODLE; bước 2026110105 (0.2.5): ghi bù *Lúc bật* (`db/upgradelib.php` — `local_fitnhom_upgrade_ghi_luc_bat()`) |
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
python3 dev/walk_021.py # đi thử 0.2.1 / 0.2.2 (dòng đỏ, bạn đã nộp, gói tải có tệp ghi chú, thư), ảnh vào docs/anh/0.2.2/
python3 dev/walk_023.py # đi thử 0.2.3 (bớt bạn / đổi sang một mình → thư "không còn liệt kê em"), ảnh vào docs/anh/0.2.3/
python3 dev/walk_024.py # đi thử 0.2.4 (ô các bạn ẩn khi chưa chọn Bài nhóm; dòng đỏ tắt khi chọn), ảnh vào docs/anh/0.2.4/
python3 dev/walk_025.py # đi thử 0.2.5 (đổi Chế độ trên trang cài đặt → Lúc bật; trang Thành viên nhóm đếm được), ảnh vào docs/anh/0.2.5/
bash dev/package.sh     # zip + SHA256 vào dist/
python3 dev/mkdocx.py <md> <docx> [thư-mục-ảnh]
```

## 5. Quyết định
- Q1–Q7 của chủ dự án: TL54 mục 0. Q7 (22:12): **không** khai bổ sung bài cũ, không thư nhắc, không tác vụ đêm.
- 0.1 (tôi chọn, ghi ở TL54 / phiếu): không chặn nút *Nộp để chấm* (bài qua ứng dụng không bị kẹt); người nộp thay (quyền sửa bài nộp của em) không bị bắt buộc khai; bảng chấm dòng bạn không tự nộp ghi *Có trong bài nhóm của …*; lúc bật chỉ ghi lần đầu (tắt rồi bật lại không đặt lại).
- Đợt 1.8 (31/10, TL60 mục 2.1): D1–D9 — lúc nộp = `assign_submission.timemodified` (cả hai bên); nhận diện lớp chủ nhiệm bằng `course.format` (mặc định `fitcnl`); thứ tự Fit-Nhóm 0.2 trước, FIT-CNL 1.8.0 sau, một phiên; `HOPDONG` giữ 1 khi chỉ thêm hàm. Q8 (trợ giảng xem trang *Thành viên nhóm*): **có** — ai có `mod/assign:grade`. Q9: giữ khối Cần xem, ngưỡng 3 / 5 (cấu hình).
- 0.2 (phiên viết mã Opus, TL60 mục 13 v1.2): `cho::quet()` giữ trả số (bài kiểm cũ), `quet_xem()` trả mảng; thân hộp nổi ở `hook_callbacks::them_vao()` để bài kiểm gọi; *Bài nộp* ở trang giảng viên / Theo course đếm bài nộp **từ lúc bật**; *Được tính nhờ nhóm* = em có trong `api::thanhvien()` mà không có bài nộp riêng đã gửi; khối Cần xem (a) một dòng mỗi người nộp kèm các course · bài, (b) một dòng mỗi (bài, em); tên chỉ hiện khi bấm *Xem*.
- 0.2.1 (chủ dự án 01/11/2026 10:32–10:45, TL60 mục 13 v1.3): câu hỏi **Thành viên nhóm** (không dùng *"(nếu có)"* vì câu bắt buộc; 0.2.2 bỏ dòng giải thích — "đừng ghi dài dòng"); cạnh tên **chỉ** bạn đã nộp có liệt kê em (không lộ ai đã nộp); không thêm câu *đại diện / bản cập nhật*; **Q2 điều chỉnh** — *Tải toàn bộ bài nộp* có tệp ghi chú (không đổi tên tệp em nộp); thư: người gửi FIT-MOODLE (cấu hình), *Chào em,*, *liệt kê*, liên kết *Em không cùng làm bài này*, ký *Hệ thống FIT-MOODLE*. Phần *"(qua <tên trang>)"* sau tên người gửi là cài đặt chung Moodle (*Email đi › Thông tin "qua"* = *Không bao giờ* thì mất) — việc của quản trị trang, Fit-Nhóm không đổi.
- 0.2.3 (chủ dự án 12:44, TL60 mục 13): bạn bị bỏ khi người nộp sửa câu khai nhận thư *"không còn liệt kê em"* — trừ bạn đã bấm *Không phải em*; bạn giữ nguyên **không** nhận thư (tôi chọn — tránh thư thừa mỗi lần nộp bản cập nhật); thư bỏ liên kết *Em không cùng làm* (không còn gì để báo), có liên kết trang bài tập. Bài kiểm cũ `khai_test::test_sua_cau_khai` sửa một dòng (chỉ xét thư `duockhai`) vì nó khẳng định hành vi cũ chủ dự án vừa đổi.
- 0.2.4 (chủ dự án 15:11): dòng đỏ tắt ngay khi em bấm một nút (mô-đun JS nhỏ `amd/src/cauhoi.js`, dựng bằng `dev/amd.sh`). Đi thử thấy thêm ô *Các bạn cùng làm* hiện khi chưa chọn nút nào — trái yêu cầu 12:44 (phiên 0.2.3 báo nhầm "đã có từ 0.1" vì chỉ thử trang đã chọn sẵn); sửa cùng bản. Không đổi chữ, không đổi CSDL, không đổi hợp đồng; TL60 không phải ghi mục 13.
- 0.2.5 (chủ dự án 16:24 — trang *Thành viên nhóm* báo chưa có bài nộp dù bảng chấm có câu khai): lỗi từ 0.1, các walk trước đặt Chế độ bằng lệnh (`set_config` + gọi hàm) nên không lộ — **walk từ nay đổi cấu hình có hàm gọi lại qua trang** khi kiểm hàm đó. Ghi bù mốc bằng lúc câu khai sớm nhất (câu khai chỉ có khi đã bật) thay vì lúc nâng cấp — để bài đã khai trước khi nâng cấp vẫn trong phạm vi Quét ngay. Bài có câu khai luôn được đếm ở trang giảng viên / Theo course, kể cả khi mốc thiếu hay muộn. TL60 mục 13 ghi (bước nâng cấp mới).

## 6. Chỗ chưa làm / biết lỗi
- Ứng dụng Moodle trên điện thoại không hiện câu hỏi (chưa thử máy thật) — bài đi vào hộp nổi.
- Trang trạng thái bài nộp của em không hiện câu khai (Moodle chỉ hiện plugin có nội dung).
- Rà soát 0.1 ngày 31/10 (TL60 mục 3, R1–R12): **đã làm ở 0.2** (R1 kiểm nhanh + bộ đệm, R3 `upgrade.php`, R4 privacy xuất `cho`, R6 tách `thongbao` / `tomtat`, R7 Quét ngay đếm bài đã xem, R10 `soxem`, R11 bài kiểm hộp nổi + nộp lần hai).
- Thư: tên người gửi do Fit-Nhóm đặt kèm một khoảng trắng thừa trước *"(qua …)"* (Moodle ghép họ + tên, họ để trống — như thư gốc của Moodle); hộp thư web gộp khoảng trắng nên không thấy.
- Bạn bị bỏ đã bấm *Không phải em*: dòng từ chối bị xóa cùng — khối *Cần xem* (ngưỡng từ chối) không còn đếm lần đó (như 0.1).
- Chữ trên trang của sinh viên ngoài thư (trang *Không phải em*, hộp nổi) vẫn dùng chữ *khai* — chủ dự án chỉ yêu cầu đổi trong thư; đổi nốt nếu chủ dự án muốn.
- Mục *Thành viên nhóm (Fit-Nhóm)* ở menu *Thêm* của course đã thấy trên theme Boost (walk_02); theme của Trường chưa thử — không thấy thì vào thẳng `local/fitnhom/course.php?id=`.
- Trang giảng viên / Theo course đếm bằng truy vấn gộp, chưa đo trên course nghìn bài nộp (đo khi chủ dự án thử thật).

## 7. Việc kế tiếp
1. Chủ dự án cài thử **0.2.5** (Phiếu cài đặt 0.2.5 mục C–D; Phiếu 0.2.4, 0.2.3 mục D; nâng từ 0.2.1 / 0.2 / 0.1 hay cài mới) trên Moodle thử, thử một course; báo nếu menu *Thêm* không có *Thành viên nhóm*.
2. Đợt 1.8 tiếp: **bước 2 FIT-CNL 1.8.0** (TL60 mục 6, kho `fit-cnl`); **bước 3** kiểm chéo TL60 mục 8 (ghi kết quả vào `nhat-ky.md` hai kho).
3. Fit-Nhóm 0.3 (TL60 mục 10): ứng dụng Moodle trên điện thoại — chỉ sau khi thử 0.1 / 0.2 trên máy thật.
