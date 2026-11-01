# Phiếu cài đặt và đi thử Fit-Nhóm 0.2.6

*Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle · bản 0.2.6 (version 2026110106) · 01/11/2026 · Khoa Công nghệ Thông tin, Trường ĐH Khoa học Tự nhiên, ĐHQG-HCM · TL54 v0.6 mục 2, TL60 v1.3 mục 2.1, 13*

## A. Bản 0.2.6 sửa gì (so với 0.2.5)

Chủ dự án (01/11/2026 16:43, chuyển lời giảng viên; 16:51 gộp thêm việc đổi chữ *khai* phía sinh viên): *"sao có thêm thư mục fitnhom nữa, trong khi thầy cô chấm trong file sinh viên nộp, sinh viên đã điền thành viên trong file rồi … chỉ cần lưu ý nhóm nộp cần điền đầy đủ thông tin nhóm trong báo cáo nộp là được"*; chọn **bỏ hẳn** tệp ghi chú và **thêm dòng nhắc**; 16:46 chỉnh câu nhắc.

| Chỗ | 0.2.1–0.2.5 | 0.2.6 |
|---|---|---|
| *Tải toàn bộ bài nộp* | thêm thư mục `…_assignsubmission_fitnhom` chứa `Thanh-vien-nhom.txt` | **như Moodle gốc** — chỉ thư mục tệp em nộp |
| Trang nộp bài, chọn *Bài nhóm* | ô *Các bạn cùng làm* | ô *Các bạn cùng làm* + dòng nhắc **"Lưu ý: chọn bạn ở đây không thay cho báo cáo — báo cáo nộp vẫn phải ghi đủ MSSV, họ tên các thành viên."** (Ảnh 01) |
| Chưa chọn / *Em làm một mình* | — | dòng nhắc ẩn |
| Trang *Không phải em* (liên kết trong thư) | *"… và **khai** em cùng làm"* | *"… và **liệt kê em** là thành viên cùng làm"* (Ảnh 02) |
| Hộp nổi, dải nhắc (bài nộp qua ứng dụng) | *"Em còn n bài cần **khai** thành viên"*, nút *Khai ngay* | *"Em còn n bài **chưa cho biết thành viên nhóm**"*, nút *Trả lời ngay* (Ảnh 03) |
| Trang trả lời cho bài nộp qua ứng dụng; hai thông báo lỗi | *Khai thành viên nhóm*, *… câu khai …* | *Thành viên nhóm*; bỏ chữ *khai* |

Chữ sinh viên thấy nay thống nhất với thư (*liệt kê em*). Chữ trên trang của giảng viên / quản trị (*Chưa khai*, *Cần xem* …) giữ. Bảng chấm, trang *Thành viên nhóm* của course, thư, FIT-CNL **không đổi**. Không đổi bảng; không bước nâng cấp CSDL.

## B. Bộ cài

| Thứ tự | Tệp | Cài vào | SHA-256 |
|---|---|---|---|
| 1 | `local_fitnhom_0.2.6.zip` | `local/fitnhom` | `2ad7ac9fd8f5a9d803b5faf42dfa1c05c430198526e6129ac5d56bddf40c3c46` |
| 2 | `assignsubmission_fitnhom_0.2.6.zip` | `mod/assign/submission/fitnhom` | `e2e719864fcf9f3e1ded98247efd49208d4f99940d0c65712989a2db81a18ea1` |

Kiểm mã: `sha256sum <tệp>` hay PowerShell `Get-FileHash <tệp> -Algorithm SHA256`. Moodle **4.5**, PHP 8.1–8.3. Không cần FIT-CNL. Hai tệp luôn đi cùng nhau, cùng số bản.

## C. Nâng cấp từ 0.2.5 (hay bản cũ hơn, hay cài mới)

| ☐ | Việc | Ghi chú |
|---|---|---|
| ☐ | Sao lưu Moodle (CSDL + moodledata) | việc của quản trị Trường |
| ☐ | Quản trị trang › Plugin › **Cài plugin** → `local_fitnhom_0.2.6.zip` → *Nâng cấp CSDL Moodle ngay* | cài **trước**; `local_fitnhom` → **2026110106**. Từ 0.2.5: không có bước nâng cấp CSDL; từ 0.2.4 trở về trước: có bước ghi bù *Lúc bật* (Phiếu 0.2.5) |
| ☐ | Làm lại với `assignsubmission_fitnhom_0.2.6.zip` | đòi `local_fitnhom` ≥ 2026110106 |
| ☐ | Quản trị trang › Phát triển › *Xóa mọi bộ đệm* | |

## D. Đi thử

1. ☐ Sinh viên mở trang nộp bài: chưa chọn → không thấy dòng nhắc; chọn **Bài nhóm** → dòng *"Lưu ý: chọn bạn ở đây không thay cho báo cáo …"* dưới ô các bạn (Ảnh 01); chọn *Em làm một mình* → ẩn.
2. ☐ Giảng viên *Tải toàn bộ bài nộp* → trong tệp zip **không còn** thư mục `…_assignsubmission_fitnhom`.
3. ☐ Sinh viên được liệt kê mở liên kết *Em không cùng làm bài này* trong thư → câu *"… và liệt kê em là thành viên cùng làm"*, không còn chữ *khai*.
4. ☐ Phần còn lại: như Phiếu 0.2.5, 0.2.4, 0.2.3 mục D.

## E. Khi có sự cố — lùi

- **Tắt nhanh**: *Chế độ* = **Tắt**. Moodle không hạ phiên bản — giữ 0.2.6, báo phiên phát triển.

## F. Đi thử trên máy phát triển (`dev/walk_026.py`)

Course *FN-023*, sinh viên A, B, C. **6/6 đạt**: (1) chưa chọn — dòng nhắc ẩn; (2) chọn *Bài nhóm* — dòng nhắc hiện dưới ô các bạn (Ảnh 01); (3) chọn *Em làm một mình* — ẩn; (4) A nộp bài nhóm có B, gói *Tải toàn bộ bài nộp* chỉ có `…_assignsubmission_onlinetext/onlinetext.html`, không thư mục fitnhom; (5) B mở trang *Không phải em* — *"liệt kê em"*, không chữ *khai* (Ảnh 02); (6) C nộp qua ứng dụng — hộp nổi, dải nhắc *"chưa cho biết thành viên nhóm"*, không chữ *khai* (Ảnh 03).

**Kiểm:** PHPUnit 41 bài (40 local + 1 assignsubmission; mới `ban026_test` 3 bài — gói tải, dòng nhắc, chữ sinh viên không còn *khai*; bỏ `ban021_test::test_tep_ghi_chu`, `test_goi_phan_hoi_khong_nhan_tep_ghi_chu` vì khẳng định tính năng đã bỏ; `ban024_test` sửa một dòng — TL60 mục 13); bộ kiểm `mod_assign` của Moodle 374 bài đạt khi có Fit-Nhóm 0.2.6; phpcs, moodlecheck sạch.

*Ảnh ở thư mục `Anh\0.2.6\`. Gửi giảng viên / sinh viên: `Gui-giang-vien-sinh-vien_Fit-Nhom_0.2.6` (bỏ đoạn tệp ghi chú, thêm ý báo cáo vẫn phải ghi đủ thành viên, hộp nổi không còn chữ *khai*).*
