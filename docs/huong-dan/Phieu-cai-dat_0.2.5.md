# Phiếu cài đặt và đi thử Fit-Nhóm 0.2.5

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.2.5 (version 2026110105) · 01/11/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · TL60 v1.3 mục 13*

## A. Bản 0.2.5 sửa gì (so với 0.2.4)

**Sửa lỗi có từ 0.1** — chủ dự án cài thử (01/11/2026 16:24): trang *Thành viên nhóm* của course báo *"Chưa có bài nộp nào từ lúc bật Fit-Nhóm"* trong khi bảng chấm đã ghi *Làm một mình*.

Nguyên nhân: khi admin đổi **Chế độ** trên trang cài đặt, Fit-Nhóm phải ghi lại *Lúc bật* (mốc tính bài nộp). Hàm ghi mốc nằm ở tệp Moodle chưa nạp lúc lưu cài đặt, Moodle bỏ qua mà không báo gì — nên trên mọi Moodle đã đổi Chế độ bằng trang cài đặt, mốc này **chưa bao giờ được ghi**. Hậu quả:

| Chỗ | 0.1–0.2.4 (thiếu *Lúc bật*) | 0.2.5 |
|---|---|---|
| Trang *Thành viên nhóm* của course (giảng viên) | *"Chưa có bài nộp nào…"* | đếm đúng (Ảnh 01) |
| Tình hình › dòng *Lúc bật* | *Chưa bật lần nào* | có giờ |
| Tình hình › *Theo course*, *Quét ngay* | trống / không quét | chạy |
| Hộp nổi nhắc em khai bài nộp qua ứng dụng | không bao giờ hiện | hiện như thiết kế |
| Câu hỏi trên trang nộp bài, bảng chấm, thư, FIT-CNL | **đúng** (không dùng mốc này) | như cũ |

Sửa: (1) đổi Chế độ trên trang cài đặt nay ghi được *Lúc bật*; (2) khi nâng lên 0.2.5, Moodle **tự ghi bù** mốc = lúc câu khai đầu tiên trên trang (chưa có câu khai thì lúc nâng cấp) — không phải đổi lại Chế độ; (3) trang giảng viên luôn đếm bài nộp đã có câu khai. Không đổi chữ, không đổi bảng; hợp đồng với FIT-CNL **không đổi**.

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.2.5.zip` | `local/fitnhom` | `ebd08b4d2dd2468ef626bcc0f6de5237edde76341d59915775f3130648249456` |
| 2 | `assignsubmission_fitnhom_0.2.5.zip` | `mod/assign/submission/fitnhom` | `7871516b05d16656fddc62cf754816d9fc873c61b33ec2d574597532adaff70c` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL. Hai tệp luôn đi cùng nhau, cùng số bản.

## C. Nâng cấp từ 0.2.4 (hay bản cũ hơn, hay cài mới)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.2.5.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước**; `local_fitnhom` → **2026110105**; một bước nâng cấp: ghi bù *Lúc bật* |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.2.5.zip` | đòi `local_fitnhom` ≥ 2026110105 |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | |

## D. Đi thử

1. ☐ Quản trị trang › Plugin › Plugin cục bộ › Fit-Nhóm › **Tình hình và Quét ngay** → dòng *Lúc bật* có giờ (trên Moodle thử của chủ dự án: lúc câu khai đầu tiên, khoảng 16:19 ngày 01/11/2026), không còn *Chưa bật lần nào*.
2. ☐ Course *Test nộp bài nhóm* › **Thêm › Thành viên nhóm** (hay liên kết ở đầu cột *Thành viên nhóm* của bảng chấm) → có dòng bài tập, *Bài nộp* 1, *Một mình* 1 (bài của svien_test2). Bài của svien_test1 nộp 26/10 — trước khi cài — không đếm (đúng).
3. ☐ Đổi thử Chế độ *Tắt* rồi *Thử nghiệm* lại → *Lúc bật* **không đổi** (mốc chỉ ghi lần đầu).
4. ☐ Phần ô các bạn / dòng đỏ / thư: như Phiếu 0.2.4, 0.2.3 mục D.

## E. Khi có sự cố — lùi

- **Tắt nhanh**: *Chế độ* = **Tắt**. Moodle không hạ phiên bản — giữ 0.2.5, báo phiên phát triển. Các mục khác như Phiếu 0.2.2 mục F.

## F. Đi thử trên máy phát triển (`dev/walk_025.py`)

Course *FN-023*, chế độ ban đầu *Tắt*, chưa từng bật; A nộp bài trước khi bật. **4/4 đạt**: (1) admin chọn *Thử nghiệm* + course FN-023 **trên trang cài đặt**, bấm Lưu — *Lúc bật* được ghi, trang Tình hình có giờ; (2) A gỡ bài ra nộp lại (*Em làm một mình*), B nộp bài nhóm có C — trang *Thành viên nhóm* có bài *Đồ án nhóm 1*, 2 bài nộp (Ảnh 01). Với mã 0.2.4, bước (1) để *Lúc bật* trống (đã thử).

**Kiểm:** PHPUnit 40 bài (39 local + 1 assignsubmission; mới `ban025_test` 3 bài: lưu Chế độ qua trang cài đặt ghi mốc, bước nâng cấp ghi bù, bài có câu khai luôn được đếm; bài cũ không sửa); bộ kiểm `mod_assign` của Moodle 374 bài đạt khi có Fit-Nhóm 0.2.5; phpcs, moodlecheck sạch.

*Ảnh ở thư mục `Anh\0.2.5\`.*
