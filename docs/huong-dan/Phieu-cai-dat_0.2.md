# Phiếu cài đặt và đi thử Fit-Nhóm 0.2

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.2 (version 2026110100) · 31/10/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · thiết kế TL54 v0.5 (sản phẩm), TL60 v1.2 mục 5 (bản 0.2)*

## A. Bản 0.2 thêm gì (so với 0.1)

Fit-Nhóm vẫn làm đúng một việc: **người nộp** trả lời *Bài này em làm thế nào? — Em làm một mình / Bài nhóm cùng các bạn…* khi nộp bài (xem Phiếu cài đặt 0.1 mục A). Bản 0.2 **không** thêm việc cho sinh viên hay giảng viên, không thư nhắc, không tác vụ đêm. Thêm:

| | Ai thấy | Ở đâu |
|---|---|---|
| Trang **Thành viên nhóm (Fit-Nhóm)** của một course: bảng theo bài tập (bài nộp, một mình, bài nhóm, bạn được khai, *Không phải em*, chưa khai, **được tính nhờ nhóm**), chi tiết từng bài nộp, nút **Tải Excel** | giảng viên **và trợ giảng** (ai chấm được bài) — sinh viên không | menu **Thêm** của course; liên kết nhỏ ở đầu cột *Thành viên nhóm* của bảng chấm; hay gõ `…/local/fitnhom/course.php?id=<mã course>` |
| Course **lớp chủ nhiệm** (định dạng `fitcnl` của FIT-CNL) tự không hỏi, kể cả khi đổi tên | — | Cài đặt: ô mới *Course không áp dụng — định dạng course* (mặc định `fitcnl`) |
| Trang **Tình hình**: khối **Theo course** (lọc theo danh mục, Tải Excel), khối **Cần xem** (dấu hiệu khai bừa — tên chỉ hiện khi bấm *Xem*), Quét ngay ghi *đã xem n bài, thêm m bài* | quản trị trang | Quản trị trang › Plugin › Mô-đun cục bộ › Fit-Nhóm › Tình hình và Quét ngay |
| Hộp nổi nhẹ hơn (đầu mỗi trang chỉ kiểm một khóa bộ đệm) | — | — |
| Hợp đồng cho FIT-CNL thêm hai hàm (`cua_nguoi`, `ap_dung`), **vẫn hợp đồng 1** | — | FIT-CNL 1.8.0 đọc |

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.2.zip` | `local/fitnhom` | `8c84b2c55f33c04a9a8810110ba68cb43136d2b1c1caf20e9361a3b322efb797` |
| 2 | `assignsubmission_fitnhom_0.2.zip` | `mod/assign/submission/fitnhom` | `06fe0ce673fee474c5285c270040d9615ec470ac3572a67513239362c91ab17d` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL.

## C. Nâng cấp từ 0.1 (hay cài mới)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.2.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước**; trang nâng cấp ghi `local_fitnhom` 2026102700 → **2026110100** (một bước — chỉ đặt ba cấu hình mới, không đổi bảng) |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.2.zip` | đòi `local_fitnhom` ≥ 2026110100 |
| ☐ | **Cài đặt** Fit-Nhóm: thấy ô *Course không áp dụng — định dạng course* = `fitcnl`; *Cần xem — số lần "Không phải em" trong 60 ngày* = 3; *Cần xem — số người nộp khác nhau cùng khai một em* = 5. Chế độ **không đổi** (giữ như trước khi nâng cấp) | |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | |
| ☐ | Vào một course có bài tập bằng tài khoản **giảng viên**: menu **Thêm** có *Thành viên nhóm (Fit-Nhóm)* | không thấy mục (theme của Trường): gõ `…/local/fitnhom/course.php?id=<mã course>` — ghi lại để báo |

Cài mới (chưa có 0.1): làm như Phiếu cài đặt 0.1 mục C với hai tệp 0.2.

## D. Thử trên một course trước (như 0.1 — Q5)

1. ☐ **Chế độ = Thử nghiệm**, *Course thử nghiệm* = mã rút gọn của **một** lớp môn (giảng viên đồng ý thử).
2. ☐ Sinh viên lớp đó nộp bài, khai *Bài nhóm* với một bạn; bạn được khai nhận thông báo (như 0.1).
3. ☐ **Giảng viên** (và **trợ giảng**, nếu course có) mở *Thêm › Thành viên nhóm (Fit-Nhóm)*: bảng theo bài (Ảnh 02); bấm tên bài → từng bài nộp, bạn bấm *Không phải em* bị gạch ngang (Ảnh 03); **Tải Excel** mở được bằng Excel, hai sheet *Theo bài*, *Chi tiết* (Ảnh 04).
4. ☐ Sinh viên gõ địa chỉ trang trên → bị từ chối (không có quyền).
5. ☐ Mở trang đó ở **course lớp chủ nhiệm** (nếu Moodle có FIT-CNL): chỉ có câu *không áp dụng (lý do: định dạng course loại trừ)*.
6. ☐ **Tình hình** (quản trị): bấm **Quét ngay** → dòng *Lần Quét ngay gần nhất* ghi *đã xem n bài, thêm m bài vào hộp nổi*; khối **Theo course** có course thử; khối **Cần xem** — thường *Không có gì bất thường*; có dòng thì bấm **Xem** mới hiện tên, Fit-Nhóm không tự làm gì (Ảnh 06).
7. ☐ Một sinh viên có bài chờ khai (nộp bằng ứng dụng): hộp nổi như 0.1; bấm *Để sau* → hôm nay chỉ còn dải nhỏ đầu trang (Ảnh 07, 08).

## E. Bật toàn trang

☐ Ổn → *Chế độ* = **Bật toàn trang**. Gửi giảng viên, sinh viên trang ngắn `Huong-dan\Gui-giang-vien-sinh-vien_0.2` (thêm đoạn trang *Thành viên nhóm* cho giảng viên).

## F. Khi có sự cố — lùi

- **Tắt nhanh**: *Chế độ* = **Tắt** (câu hỏi biến mất ngay; dữ liệu đã khai giữ; FIT-CNL 1.8.0 thấy *Fit-Nhóm đang tắt* và tính như không có Fit-Nhóm từ lượt gộp sau).
- Về 0.1: không cần — 0.2 không đổi bảng; nếu buộc phải về, cài đè gói 0.1 không được (Moodle không hạ phiên bản) → giữ 0.2 và đặt *Chế độ = Tắt*, báo phiên phát triển.
- Trang *Thành viên nhóm* chậm ở course rất lớn: ghi số bài nộp của course và thời gian mở trang để báo.
- Các mục khác như Phiếu 0.1 mục F.

## G. Đi thử trên máy phát triển (`dev/walk_02.py`)

Course *FN-02-MON* (bốn bài tập, một bài *Nộp theo nhóm* của Moodle), course *Lop 25CTT-thu* định dạng `fitcnl`, sáu sinh viên, một giảng viên, một trợ giảng; chế độ *Bật toàn trang*. **15/15 đạt**: (1) menu *More* của course có *Thành viên nhóm (Fit-Nhóm)* (Ảnh 01); (2) bảng theo bài — bài nhóm Moodle ghi riêng, bài 2 (B, C cùng nộp, khai A) và bài 3 mỗi bài một em *được tính nhờ nhóm* (Ảnh 02); (3) chi tiết bài 1: ba bạn bấm *Không phải em* gạch ngang, F *Chưa khai* (Ảnh 03); (4) Excel hai sheet, tên bài `=…` vẫn là chữ (Ảnh 04); (5) trợ giảng vào được, sinh viên không, course `fitcnl` chỉ câu lý do; (6) Cài đặt có ô định dạng loại trừ và hai ngưỡng (Ảnh 05); (7) Tình hình: Quét ngay *đã xem n bài, thêm m bài*, Theo course, Cần xem (A bị ba lần *Không phải em*) — tên chỉ hiện sau *Xem* (Ảnh 06); (8) F (nộp qua ứng dụng) thấy hộp nổi; *Để sau* → hôm nay chỉ còn dải (Ảnh 07, 08).

**Kiểm:** PHPUnit 28 bài (14 bài 0.1 không đổi + `ban02_test` 13 + `hopdong_test` thêm 1: trang giảng viên và quyền, số liệu theo bài, chi tiết, định dạng loại trừ, `cua_nguoi`, `ap_dung`, Cần xem, Quét ngay, hộp nổi + bộ đệm, nộp lần hai, Excel, privacy, `thanhvien()` một truy vấn với 300 bài nộp); bộ kiểm `mod_assign` của Moodle 374 bài đạt khi có Fit-Nhóm 0.2; FIT-CNL 1.7.81 trọn bộ 767 bài đạt trên cùng máy; phpcs, moodlecheck sạch. Nâng cấp 0.1 → 0.2 trên máy phát triển: ba cấu hình mới, chế độ giữ nguyên.

*Ảnh chụp ở thư mục `Anh\0.2\` (máy phát triển giao diện tiếng Anh; chữ của Fit-Nhóm là tiếng Việt).*
