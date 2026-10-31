# Nhật ký Fit-Nhóm

## 26/10/2026 — Khởi đầu

- 18:09: chủ dự án nêu vấn đề bài nhóm chỉ một bạn nộp, FIT-CNL tính các bạn khác là không nộp. Đã xét: mở tệp (không được), *Nộp theo nhóm* của Moodle (giảng viên không tuân theo), sinh viên tự khai ở trang chủ nhiệm (không ai khai).
- 18:17: chủ dự án chọn hướng người nộp khai lúc nộp.
- 18:34: đồng ý bỏ A15-2 của FIT-CNL riêng cho việc này; bắt buộc mọi bài, giảng viên không tắt; admin tắt được; trang quản trị trong mô-đun cục bộ; báo thành viên; khai bổ sung bài cũ (người nộp, nhắc 4 ngày, tự nghỉ).
- 18:35: hộp nổi danh sách bài để khai ngay; thư có liên kết từng bài.
- 18:46: tạo kho `fitnhom`; thử trên một lớp trước; nút Quét ngay; tên Fit-Nhóm, ghi rõ mục đích.
- Viết TL54 v0.1, README, CLAUDE.md, sổ tay; tạo thư mục Drive `Fit-Nhom\`.
- 20:00: chủ dự án hỏi hai bạn cùng nhóm đều nộp / nộp bản cập nhật → TL54 v0.2 thêm mục 3a (mỗi bài nộp một câu khai; điền sẵn theo bản khai của bạn đã nộp; bảng chấm ghi "cùng nhóm với bài nộp của …"; tính đã nộp theo lúc sớm nhất). v0.1 vào `docs/ban-cu/`.
- 22:12: chủ dự án chọn hướng (A) — bỏ khai bổ sung bài cũ, thư nhắc, tác vụ đêm; giữ hộp nổi cho bài nộp chưa khai sau lúc bật. TL54 v0.3. Bắt đầu viết Fit-Nhóm 0.1.
- 22:12–: viết bản 0.1 — `local_fitnhom` + `assignsubmission_fitnhom`; công cụ `dev/` (sync, test, lint, amd, walk, package, mkdocx); PHPUnit 14 bài; đi thử 12/12 (sửa: bảng chấm dòng bạn không tự nộp ghi "Có trong bài nhóm của …"; trang trạng thái bài nộp không hiện câu khai — ghi ở rủi ro); phiếu cài đặt, trang gửi giảng viên / sinh viên.

## 31/10/2026 — Thiết kế bản 0.2 (phiên dọn dẹp FIT-CNL 1.7.80, Fable)

- Chủ dự án (00:23): "các tính năng về Fit-Nhóm tôi chưa thực hiện trong bản trước, hãy thiết kế luôn sao cho tốt nhất". Rà kho `fit-cnl`: FIT-CNL 1.7.54 → 1.7.79 chưa có dòng nào đọc `\local_fitnhom\api` (1.7.58 đã dành cho Google Calendar).
- Viết **TL59** (kho `fit-cnl`): FIT-CNL 1.8.0 đọc Fit-Nhóm — bốn điều kiện, lớp `dichvu\hoatdong\nopchung`, hai cột `hd_baitap.nguon` / `nguoinop`, sửa `sap_toi`, quy tắc hiện tên người nộp, cấu hình `hd_fitnhom_bat`, `ban180_test`, câu mở phiên.
- **TL54 v0.4**: mục 6 thêm `api::cua_nguoi()`, `api::ap_dung()` (HOPDONG giữ 1); mục 9 cập nhật tình trạng; mục 10 bản 0.2 (trang giảng viên trong course, loại course theo `format = fitcnl`, tình hình theo course + dấu hiệu bất thường, giao dịch cho `khai::luu`); mục 11 khung bản 0.3 (ứng dụng điện thoại); mục 12 câu mở phiên. v0.3 vào `docs/ban-cu/`. README, sổ tay mục 7, lịch sử.
- Chưa làm mã. Bản Word TL54 v0.4 chép lên Drive `Fit-Nhom\Thiet-ke\` (v0.1 → v0.3 vào `Thiet-ke\Ban-cu\` theo kịch bản dọn `Xay-lai-1.0\Don-dep\Don-dep_1.7.80`).

## 31/10/2026 tối — Rà soát kho, tái cấu trúc, thiết kế đợt 1.8 (TL60; phiên Fable)

- Chủ dự án (21:16): "kiểm tra lại thư mục Fit-Nhom, tái cấu trúc và viết bản thiết kế lại cho 1.8 … tôi sẽ mở session mới để dùng mô hình Opus để viết code".
- Rà kho: `dev/sync.sh` + `dev/test.sh` 14 bài đạt, `dev/lint.sh` sạch, chuỗi vi / en khớp. Phát hiện R1–R12 ghi ở TL60 mục 3 (hộp nổi join ở mọi trang; chưa `upgrade.php`; privacy chưa xuất `cho`; `khai.php` bốn việc; TL54 v0.4 10.4 "giao dịch" thừa vì 0.1 đã có; Thử nghiệm nhận textarea mã rút gọn). Không lỗi an toàn.
- Tái cấu trúc: bỏ tệp rỗng `readme` ở gốc; thêm `dev/README.md`, `docs/ban-cu/README.md`; TL54 lên **v0.5** (mục 10–12 bỏ, mục 6 chỉ còn hợp đồng rút gọn trỏ TL60 mục 4; sửa chữ theo rà soát), v0.4 vào `docs/ban-cu/`; README, CLAUDE.md, sổ tay (mục 3 bố cục kho, 5, 6, 7), lịch sử trỏ TL60.
- Viết **TL60 v1.0** — một tài liệu cho cả đợt 1.8 (bản gốc `fit-cnl/docs/1.0/`, bản chép ở đây): quyết định D1–D9, Q8–Q9 chờ chủ dự án (có mặc định), rà soát 0.1, hợp đồng H1–H8 + ma trận ai được làm gì, đặc tả 0.2 (mục 5) và 1.8.0 (mục 6), thứ tự ba bước, kiểm chéo 12 tình huống, cài thật / lùi, rủi ro, câu mở phiên Opus, sổ điều chỉnh. TL59 (kho `fit-cnl`) bị TL60 thay. Drive `Fit-Nhom\Thiet-ke\`: TL60 + TL54 v0.5 (Word); chủ dự án tự xóa TL54 v0.4 docx và `Ban-cu\`.
- 22:11–22:13 chủ dự án trả lời Q8 "ok", Q9 "à, ok, cứ theo ý bạn" → **TL60 v1.1** (mục 2.2 đóng; v1.0 vào `docs/ban-cu/`), đồng bộ với kho `fit-cnl`. Không còn câu chờ — sẵn sàng cho phiên Opus (câu mở phiên TL60 mục 12).

## 31/10/2026 khuya — Đợt 1.8 bước 0 + bước 1: Fit-Nhóm 0.2 (phiên viết mã Opus, TL60 v1.2)

- Bước 0: dựng máy (`fit-cnl/dev/1.0/setup.sh`, Moodle 4.5.15+, MariaDB 10.11, PHP 8.3); Fit-Nhóm 0.1 sạch (`git archive HEAD`) → `dev/test.sh` **14/14**; FIT-CNL 1.7.81 trọn bộ **767** (766 + 1 format) — một lần `ban1728_test::test_thu_nhac` đỏ ngẫu nhiên vì em sinh ra trùng họ tên với chủ nhiệm (`Иван Иванов`; điều 43 HDBT), chạy lại riêng 3 lần đều đạt. Không sửa bài kiểm.
- Bước 1 theo TL60 mục 7: `upgrade.php` (2026110100) → phạm vi theo `course.format` + `ly_do()` → `api::cua_nguoi()`, `api::ap_dung()` + `hopdong_test` thêm một hàm → tách `thongbao`, `tomtat` (bộ cũ xanh) → hộp nổi kiểm nhanh + bộ đệm `local_fitnhom/cho` → `baocao` + Tình hình (Theo course, Cần xem, Quét ngay đã xem / thêm) → trang `course.php` (giảng viên / trợ giảng, chi tiết, Excel, sự kiện `course_xem`, mục menu course, liên kết ở cột bảng chấm) → privacy xuất `cho` → `ban02_test` 13 bài → chuỗi (vi / en khớp 160 khóa) → `walk_02`.
- Điều phải khác TL60 (ghi mục 13 **trước** khi làm, TL60 v1.1 → **v1.2**, đổi cả hai kho): (1) `cho::quet()` giữ trả `int` vì `cho_test` cũ khẳng định số, hàm mới `quet_xem()` trả mảng; (2) thân hộp nổi ở `hook_callbacks::them_vao()` (PHPUnit luôn `CLI_SCRIPT`); (3) ca 300 bài nộp: đúng 1 truy vấn, < 250 ms (thay 100 ms); (4) thêm một số chuỗi ngoài danh sách 5.9; (5) `hopdong_test` cũ chỉ thêm một hàm. Không đụng mục 2.1.
- Đi thử `walk_02` 15/15 lần ba: lần đầu tour hướng dẫn của Moodle che menu *More* (thêm `bo_tour`); lần hai hai kỳ vọng của kịch bản sai (bài 2 thì A không tự nộp nên **được tính nhờ nhóm = 1** là đúng; bài *Nộp theo nhóm* chỉ hiện khi có bài nộp — đúng đặc tả) → sửa kịch bản, không sửa mã. Ảnh 01 chụp lại để thấy menu thả xuống; chú thích bảng Cần xem đổi từ caption (quá to ở Boost) sang dòng đậm.
- Kiểm: PHPUnit **28/28** (14 cũ không đổi dòng nào + `ban02_test` 13 + `hopdong_test::test_hop_dong_1_them_ham_02`); `mod_assign` của Moodle **374/374** khi có Fit-Nhóm 0.2; `dev/lint.sh` phpcs + moodlecheck sạch. Nâng cấp thật 0.1 → 0.2 trên máy phát triển: ba cấu hình mới có, chế độ giữ.
- Phát hành: `dist/local_fitnhom_0.2.zip`, `dist/assignsubmission_fitnhom_0.2.zip`, `SHA256SUMS_0.2.txt`; `docs/huong-dan/Phieu-cai-dat_0.2.md`, `Gui-giang-vien-sinh-vien_0.2.md`; sổ tay mục 1, 3, 5–7; lịch sử; TL54 v0.5 mục 9.
