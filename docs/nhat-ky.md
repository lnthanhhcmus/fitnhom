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
