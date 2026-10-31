# TL60 — Thiết kế đợt 1.8: Fit-Nhóm 0.2 và FIT-CNL 1.8.0 (một tài liệu cho phiên viết mã)

*v1.1 · 31/10/2026 22:13 (chủ dự án trả lời Q8, Q9 — mục 2.2 đóng, không còn câu chờ; v1.0 ở `ban-cu/`) · Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM.*

*Bản gốc ở kho `lnthanhhcmus/fit-cnl` (`docs/1.0/TL60_….md`); kho `lnthanhhcmus/fitnhom` giữ **bản chép cùng phiên bản** (`docs/TL60_….md`). Đổi tài liệu thì đổi cả hai trong cùng commit-ngày và ghi vào mục 13 (Sổ điều chỉnh). Tài liệu này **thay** TL59 v1.0 (phía FIT-CNL) và mục 10–12 của TL54 v0.4 (phía Fit-Nhóm); TL54 v0.5 còn lại là thiết kế sản phẩm Fit-Nhóm (vì sao có, Q1–Q7, cách dùng, hợp đồng), TL45 1.7.81 là hành vi đã chốt của FIT-CNL.*

*Yêu cầu của chủ dự án (31/10/2026 21:16): "kiểm tra lại thư mục Fit-Nhom, tái cấu trúc và viết bản thiết kế lại cho 1.8 … tôi sẽ mở session mới để dùng mô hình Opus để viết code, miễn bạn thiết kế tốt để mô hình Opus hiểu và cập nhật các điều chỉnh tương lai nếu có."*

---

## 0. Đọc tài liệu này thế nào

| Ai | Đọc gì |
|---|---|
| Phiên viết mã (Opus) | Trọn tài liệu, theo thứ tự. Mục 12 là câu mở phiên. Mục 13 là nơi ghi mọi điều phải làm khác với tài liệu. |
| Chủ dự án | Mục 1 (đợt 1.8 là gì), mục 2 (các quyết định — Q8, Q9 đã trả lời 31/10 22:13, không còn câu chờ), mục 7 (thứ tự làm, xong khi nào), mục 9 (cài thật theo thứ tự nào). |
| Người bảo trì sau này | Mục 4 (hợp đồng giữa hai plugin) và mục 13 (lịch sử điều chỉnh). |

Ba nguyên tắc của tài liệu:

1. **Một nguồn cho cả hai kho.** Đợt 1.8 đụng hai kho (`fitnhom`, `fit-cnl`) và hai bản (Fit-Nhóm 0.2, FIT-CNL 1.8.0). Mọi quy tắc chung — nhất là hợp đồng mục 4 và định nghĩa *lúc nộp* — chỉ viết **ở đây**, không viết lại ở nơi khác.
2. **Tài liệu sống.** Phiên viết mã gặp điều gì bắt buộc phải khác tài liệu (Moodle không cho, dữ liệu thật khác giả định, chủ dự án đổi ý) thì **ghi vào mục 13 trước khi làm**, tăng phiên bản tài liệu (v1.0 → v1.1 …), rồi mới sửa mã. Điều chỉnh đụng quyết định ở mục 2 thì **dừng và hỏi chủ dự án**; đụng cách thực hiện ở mục 5–6 thì phiên tự quyết và ghi lý do.
3. **Tách bạch hai bên.** Fit-Nhóm không biết FIT-CNL tồn tại (không gọi lớp, không đọc bảng, không nhận diện lớp chủ nhiệm bằng tên); FIT-CNL chỉ gọi `\local_fitnhom\api` và chạy bình thường khi Fit-Nhóm vắng mặt. Mục 4.4 liệt kê đúng những gì mỗi bên được làm.

Tài liệu cần đọc kèm (phiên viết mã đọc trước khi bắt đầu mỗi bước):

| Kho | Tệp | Để làm gì |
|---|---|---|
| `fitnhom` | `CLAUDE.md`, `docs/so-tay.md`, `docs/TL54_Fit-Nhom_Thiet-ke_v0.5.md` mục 0–8 | quy ước kho, bản đồ mã 0.1, Q1–Q7, cách dùng của sản phẩm |
| `fit-cnl` | `CLAUDE.md`, `docs/1.0/TL58_Ho-so-phat-hanh-1.7.80.md` mục 4 và 9, `docs/1.0/TL45_Thiet-ke-tong-hop-1.7.81.md` mục 0–9 + A2, A14-11, A15, Phụ lục B, `docs/1.0/huong-dan-bao-tri.md` mục 2–4, `docs/1.0/so-tay.md` mục 1, 5, 6 | quy ước kho, hành vi đã chốt, bảng kiểm quyền, cách phát hành |

---

## 1. Đợt 1.8 là gì

### 1.1 Vấn đề

Bài tập / đồ án trên Moodle thường là **bài nhóm nhưng chỉ một bạn nộp**. Moodle chỉ ghi bài nộp cho người bấm nộp. FIT-CNL (module *Hoạt động Moodle*) đọc dữ liệu Moodle để tính *đúng hạn / trễ / bỏ lỡ* cho từng em, nên các bạn cùng nhóm không tự nộp bị tính **Bỏ lỡ**, đếm vào *trễ / bỏ lỡ ≥ 3 bài* của mức chú ý, hiện ở Danh sách lớp, hồ sơ em, Sinh viên cần quan tâm, thư nhắc — cảnh báo sai, chủ nhiệm mất tin.

### 1.2 Hai dự án, hai bản

| | Fit-Nhóm | FIT-CNL |
|---|---|---|
| Kho | `lnthanhhcmus/fitnhom` | `lnthanhhcmus/fit-cnl` |
| Plugin | `local_fitnhom` (chính) + `assignsubmission_fitnhom` (câu hỏi trên trang nộp bài) | `local_fitcnl` + `format_fitcnl` |
| Cài ở | Moodle **môn học** (cùng Moodle với FIT-CNL, nhưng là course môn) | Moodle, course *Lớp chủ nhiệm* |
| Hiện nay | **0.1** (26/10/2026, version 2026102700): hỏi *một mình / bài nhóm cùng ai* lúc nộp, báo bạn được khai, *Không phải em*, hộp nổi cho bài nộp chưa khai, chế độ Tắt / Thử nghiệm / Bật, Quét ngay, API hợp đồng 1. 14 bài PHPUnit, lint sạch, đi thử 12/12. **Chủ dự án chưa cài thử.** | **1.7.81** (31/10/2026, version 2026101981; mốc lưu trữ 1.7.80 = thẻ `v1.7.80`). Chưa có dòng mã nào đọc Fit-Nhóm. |
| Đợt 1.8 | **0.2** — mục 5 | **1.8.0** — mục 6 |

### 1.3 Phạm vi đợt 1.8 — làm đúng ba việc

1. **FIT-CNL đọc Fit-Nhóm** qua hợp đồng (mục 4) để tính *đã nộp* cho em được khai tên, ở mọi nơi FIT-CNL dùng bài có hạn; sửa luôn *Sắp tới hạn 7 ngày* đang bỏ sót bài nhóm Moodle (lỗi từ 1.3.0).
2. **Fit-Nhóm 0.2** — những gì 0.1 còn thiếu để Khoa dùng thật: giảng viên có trang *Thành viên nhóm* trong course; nhận diện lớp chủ nhiệm theo **định dạng course** (không chỉ theo tên); quản trị thấy tình hình theo course và dấu hiệu khai bừa; hộp nổi nhẹ hơn; hợp đồng thêm hai hàm tiện; `upgrade.php`.
3. **Người dùng thấy** vì sao một bài được tính đã nộp dù em không bấm nộp (*nộp chung*), và quản trị FIT-CNL thấy FIT-CNL có đang đọc được Fit-Nhóm hay không.

### 1.4 Không làm trong đợt này (đã quyết, đừng tự thêm)

- Khai bổ sung bài nộp cũ, thư nhắc khai, tác vụ đêm (TL54 Q7).
- FIT-CNL ghi gì sang Fit-Nhóm hay course môn (TL45 A15-2 giữ cho FIT-CNL; ngoại lệ duy nhất là **đọc** hợp đồng).
- FIT-CNL nhắc em khai, hiện danh sách thành viên nhóm cho chủ nhiệm / Giáo vụ (chỉ *đã nộp chung* và tên người nộp theo quy tắc mục 6.1).
- Ứng dụng Moodle trên điện thoại (Fit-Nhóm 0.3, mục 10 — sau khi thử 0.1 trên máy thật).
- Gộp / xóa bài nộp trùng; xét ai đóng góp bao nhiêu trong nhóm.

---

## 2. Quyết định nền

### 2.1 Đã chốt (không bàn lại trong phiên viết mã)

| # | Quyết định | Nguồn |
|---|---|---|
| Q1–Q7 | Người nộp khai lúc nộp; bắt buộc mọi bài, giảng viên không tắt; admin tắt; báo bạn được khai; thử một course trước; tên *Fit-Nhóm*; không khai bổ sung bài cũ | chủ dự án 26/10 — TL54 mục 0 |
| D1 | Fit-Nhóm là **dự án độc lập**, FIT-CNL chỉ **đọc** qua `\local_fitnhom\api`; thiếu Fit-Nhóm thì FIT-CNL chạy như cũ | chủ dự án 26/10 (Q1) |
| D2 | **Lúc nộp của một em** = sớm nhất trong ba nguồn: bài nộp của chính em; bài nộp của nhóm Moodle (bài bật *Nộp theo nhóm*); bài nộp của bạn khác có khai em (Fit-Nhóm, em chưa bấm *Không phải em*). A nộp đúng hạn khai B, B nộp bản cập nhật sau hạn → B vẫn **đúng hạn** | chủ dự án 26/10 20:00 — TL54 mục 3a |
| D3 | *Lúc nộp* của một bài nộp = `assign_submission.timemodified` của dòng `latest = 1, status = 'submitted'` — **cả hai bên** dùng đúng cột này (FIT-CNL đã dùng từ 1.3.0 cho bài tự nộp) | phiên thiết kế 31/10 (kỹ thuật) |
| D4 | Fit-Nhóm nhận diện course **không áp dụng** (lớp chủ nhiệm, course hành chính) bằng **`course.format`** (mặc định loại `fitcnl`) cộng mẫu tên và danh mục như 0.1 — không gọi FIT-CNL | phiên thiết kế 31/10 (TL54 v0.4 10.2) |
| D5 | FIT-CNL **không thêm trạng thái mới** cho bài: vẫn DUNGHAN / TRE / BOLO / QUAHAN; chỉ thêm **nguồn** của lúc nộp (`TU` / `NHOMMD` / `FITNHOM`) và người nộp. Mọi tổng hợp tự đúng theo | phiên thiết kế 31/10 |
| D6 | Tên người nộp hiện cho **chính em** luôn; cho chủ nhiệm / Giáo vụ chỉ khi người nộp **cùng lớp** với em đang xem (thành viên hiện hành của lớp); khác lớp thì *nộp chung với một bạn cùng nhóm* | phiên thiết kế 31/10 — theo TL45 mục 4 (dữ liệu theo lớp) |
| D7 | Thứ tự làm: **Fit-Nhóm 0.2 trước, FIT-CNL 1.8.0 sau**, trong **một phiên** Opus, dừng giữa hai bước để gửi gói (mục 7). Lý do: 1.8.0 đi thử cần Fit-Nhóm trên máy phát triển, và hai hàm mới của hợp đồng nên có trước | phiên thiết kế 31/10 |
| D8 | FIT-CNL 1.8.0 có **công tắc** `hd_fitnhom_bat` (mặc định bật) để quản trị tắt việc đọc mà không gỡ plugin; trạng thái đọc được / không hiện ở Cấu hình | phiên thiết kế 31/10 |
| D9 | Chuyển `HOPDONG` lên 2 chỉ khi đổi **ý nghĩa** hàm đã có; thêm hàm thì giữ 1. FIT-CNL coi số lạ là *không dùng* | TL54 mục 6 |

### 2.2 Câu đã hỏi chủ dự án (đã trả lời 31/10/2026 22:11–22:13 — nay là quyết định, như mục 2.1)

| # | Câu hỏi | Trả lời |
|---|---|---|
| **Q8** | Trang *Thành viên nhóm* của giảng viên (mục 5.1) có cho **trợ giảng** (quyền `mod/assign:grade` nhưng không phải giảng viên chính) xem không? | **"ok"** (22:11) → **Có** — ai chấm được thì xem được; không có vai riêng |
| **Q9** | Khối *Cần xem* của quản trị (mục 5.3): ngưỡng *n* lần bị *Không phải em* trong 60 ngày và *m* bài nộp của người khác nhau khai cùng một em — giữ mặc định 3 và 5? | **"cứ theo ý bạn"** (22:13, sau khi được giải thích hai dấu hiệu) → **Giữ** khối Cần xem, 3 và 5, là cấu hình đổi được sau |

Không còn câu chờ. Câu mới phát sinh trong phiên viết mã: thêm dòng vào bảng này (Q10 …) kèm mặc định, ghi mục 13, rồi làm tiếp theo mặc định.

---

## 3. Rà soát Fit-Nhóm 0.1 (31/10/2026) — nền cho 0.2

Kết quả chạy trên máy phát triển 31/10: `bash dev/test.sh` **14 bài đạt** (13 + 1), `bash dev/lint.sh` phpcs + moodlecheck sạch, chuỗi vi / en khớp khóa (102 + 5). Kho gọn (68 tệp, 3.253 dòng PHP); dọn hôm nay: bỏ tệp rỗng `readme` ở gốc, thêm `docs/ban-cu/README.md`, `dev/README.md`, TL54 lên v0.5 (mục 10–12 chuyển sang đây), CLAUDE.md / README / sổ tay trỏ TL60.

| # | Phát hiện | Làm gì ở 0.2 |
|---|---|---|
| R1 | `hook_callbacks::dau_trang` chạy `cho::cua()` (join 4 bảng + một `get_coursemodule_from_instance` mỗi dòng) ở **mọi trang** của mọi người đăng nhập khi chế độ ≠ Tắt — Moodle ~3.000 sinh viên, phần lớn không có bài chờ | Mục 5.5: kiểm nhanh `record_exists('local_fitnhom_cho', ['userid'])` trước (chỉ mục `userid_ix`), rồi mới dựng danh sách; thêm bộ đệm MUC theo người (`db/caches.php`, xóa khi thêm / khai / xóa dòng chờ) |
| R2 | `khai::luu` **đã** bọc giao dịch và gửi thông báo sau `allow_commit` — TL54 v0.4 mục 10.4 ghi "kiểm lại" là thừa | Bỏ việc đó khỏi 0.2 (ghi ở đây cho khỏi làm lại) |
| R3 | Chưa có `db/upgrade.php` (0.1 cài mới) | Mục 5.6: thêm với bước đầu tiên `2026110100` (chỉ `set_config` mặc định cấu hình mới, không đổi bảng) |
| R4 | Privacy: `cho` đã khai metadata và xóa, **chưa xuất** (`export_user_data` chỉ `khai`, `tv`) | Mục 5.7 |
| R5 | `phamvi` chế độ *Thử nghiệm* nhận **danh sách mã rút gọn** (textarea `khoathu`), TL54 v0.4 mục 4 ghi "ô gõ-chọn course" | Giữ textarea (đơn giản, admin chép mã); TL54 v0.5 sửa chữ |
| R6 | `khai.php` 486 dòng gồm bốn việc: ứng viên / lưu / thông báo / tóm tắt | Mục 5.8: tách `thongbao.php` (hai thông báo) và `tomtat.php` (chữ cho bảng chấm); `khai.php` chỉ còn lưu, gợi ý, từ chối, xóa |
| R7 | `cho::quet` duyệt mọi bài nộp từ lúc bật trên toàn trang bằng recordset, lọc course bằng PHP (đệm theo course) — chạy tay bởi admin, chấp nhận; nhưng không ghi *số bài đã xem* | Mục 5.3: ghi `quet_so_xem`, `quet_so_them`, hiện ở Tình hình |
| R8 | `assignsubmission_fitnhom::is_enabled()` trả false khi course không áp dụng → Moodle không gọi plugin → cột *Thành viên nhóm* cũng biến mất ở course đó — đúng ý | Không đổi; ghi vào TL54 v0.5 mục 2 cho rõ |
| R9 | `cho::them` nhận mốc thời gian từ sự kiện (`$e->timecreated`) ✔; `locallib::save` dùng `time()` (yêu cầu web, chấp nhận) | Không đổi |
| R10 | Sự kiện `khai_luu` có `other.loai`, `soban`, `nguon`, không tên bạn ✔; `quet_ngay` có `other.so` | 0.2 thêm `other.soxem` cho `quet_ngay` |
| R11 | Bài kiểm 14 bài đủ cho 0.1; chưa có bài kiểm cho hộp nổi hiện / ẩn theo ngày (`DESAU`) và cho `copy_submission` khi nộp lần hai | Mục 5.10 bổ sung hai ca |
| R12 | `api::thanhvien` xếp `ORDER BY s.timemodified ASC, t.id ASC` rồi lấy lần đầu gặp mỗi em → đúng "sớm nhất" (D2) ✔ | Giữ; ghi thành bảo đảm ở mục 4.2 |

Không phát hiện lỗi an toàn: mọi trang của Fit-Nhóm `require_login`, kiểm chính chủ (`tuchoi.php`, `khai.php` kiểm `submission.userid`), admin qua `admin_externalpage_setup`; sinh viên không gõ tay tên bạn (chỉ chọn từ `ung_vien` = người có `mod/assign:submit` trong course).

---

## 4. Hợp đồng `\local_fitnhom\api` — phiên bản 1

Đây là **toàn bộ** những gì FIT-CNL (hay plugin khác) được biết về Fit-Nhóm. Mọi câu ở mục này là bảo đảm; bài kiểm `fitnhom/local_fitnhom/tests/hopdong_test.php` giữ chúng.

### 4.1 Chữ ký

```php
namespace local_fitnhom;

final class api {
    public const HOPDONG = 1;

    /** Fit-Nhóm đang hỏi thành viên (chế độ Thử nghiệm hay Bật toàn trang). */
    public static function dang_bat(): bool;

    /** Các bạn được khai cùng nhóm ở một bài tập, để tính "đã nộp" cho bạn không tự nộp.
     *  @return array userid => ['nguoinop' => userid, 'luc' => int]  */
    public static function thanhvien(int $assignid): array;

    // ---- 0.2 thêm (HOPDONG vẫn 1; plugin gọi kiểm method_exists trước) ----

    /** Các bài tập mà em được khai cùng nộp, bài nộp trong [tu, den].
     *  @return array assignid => ['nguoinop' => userid, 'luc' => int]  */
    public static function cua_nguoi(int $userid, int $tu, int $den): array;

    /** Course có áp dụng Fit-Nhóm không (chế độ, danh sách thử, loại trừ theo định dạng / mẫu tên / danh mục). */
    public static function ap_dung(int $courseid): bool;
}
```

### 4.2 Ngữ nghĩa và bảo đảm

| # | Bảo đảm |
|---|---|
| H1 | `thanhvien()` chỉ tính bài nộp **đã gửi** (`assign_submission.status = 'submitted'`, `latest = 1`) có câu khai loại NHÓM; bạn có `trangthai = KHAI` (chưa bấm *Không phải em*, hay đã hoàn tác); **không gồm** chính người nộp. |
| H2 | Một em được nhiều bài nộp khai → trả **bài nộp sớm nhất** (`luc` nhỏ nhất), kèm `nguoinop` của bài ấy. |
| H3 | `luc` = `assign_submission.timemodified` của bài nộp đó (D3). Em cũng có bài nộp riêng vẫn **có mặt** trong kết quả — plugin gọi tự lấy sớm nhất giữa bài riêng của em và `luc` này. |
| H4 | Kết quả **không phụ thuộc chế độ / phạm vi hiện tại**: bài đã khai ở course từng áp dụng vẫn trả về dù sau đó course bị loại trừ hay chế độ về Tắt. Plugin gọi muốn tôn trọng chế độ thì tự kiểm `dang_bat()` / `ap_dung()`. (Lý do: dữ liệu khai là sự thật đã xảy ra; FIT-CNL kiểm `dang_bat()` ở `nopchung::dung()`.) |
| H5 | `cua_nguoi()` cùng luật lọc H1–H3, khóa theo `assignid`, chỉ bài nộp có `luc` trong `[tu, den]` (đóng hai đầu), mỗi bài tập một dòng (sớm nhất). |
| H6 | `ap_dung()` trả đúng `phamvi::course_ap_dung()` (chế độ Tắt → false; `SITEID` → false). |
| H7 | Mọi hàm **không ném lỗi** với tham số hợp lệ; `assignid` không tồn tại → `[]`; không ghi CSDL; không gửi thông báo; chi phí một truy vấn mỗi lời gọi (`thanhvien`: join `local_fitnhom_tv` → `local_fitnhom_khai` → `assign_submission` theo chỉ mục `assignid_userid_ix`). |
| H8 | Trong cùng `HOPDONG` = 1, ý nghĩa H1–H7 **không đổi**. Đổi bảng, cột, cách lưu bên trong tùy ý. Muốn đổi ý nghĩa → tăng `HOPDONG` và ghi vào mục 13 + TL54. |

### 4.3 Bên gọi phải làm gì (FIT-CNL làm ở `nopchung`, mục 6.2)

Dùng hợp đồng **chỉ khi đủ bốn điều**: (1) `core_plugin_manager::instance()->get_plugin_info('local_fitnhom')` tồn tại và `is_enabled()` không phải `false`; (2) `class_exists('\local_fitnhom\api')`; (3) `api::HOPDONG` nằm trong danh sách số bên gọi biết (FIT-CNL 1.8.0 biết `[1]`); (4) lời gọi bọc `try/catch \Throwable`. Thiếu một điều → chạy như không có Fit-Nhóm, ghi lý do để quản trị xem, **không** `debugging()` lặp mỗi lượt.

### 4.4 Ma trận "ai được làm gì"

| | Fit-Nhóm | FIT-CNL |
|---|---|---|
| Đọc bảng `local_fitnhom_*` | có | **không** (chỉ qua `api`) |
| Đọc bảng `local_fitcnl_*`, gọi lớp `\local_fitcnl\…` | **không** | có |
| Nhận diện course lớp chủ nhiệm | bằng `course.format = 'fitcnl'` + mẫu tên + danh mục (cấu hình của Fit-Nhóm) | bằng dữ liệu của chính mình (`local_fitcnl_lop.courseid`) |
| Ghi vào course môn | có (câu khai, cột bảng chấm — Q1) | **không** |
| Gửi thông báo cho sinh viên về nhóm | có (bạn được khai, từ chối) | **không** |
| Lưu danh sách thành viên nhóm | có | **không** — chỉ `nguoinop` (mã người) của bài nộp dùng để tính |

---

## 5. Fit-Nhóm 0.2 — đặc tả

Bản 0.2: `local_fitnhom` + `assignsubmission_fitnhom` cùng version **2026110100**, release `0.2`; `assignsubmission_fitnhom` phụ thuộc `local_fitnhom` 2026110100. Không bảng mới, không đổi cột. Ba cấu hình mới, một bảng đệm MUC, một trang mới, một sự kiện thêm trường.

### 5.1 Trang *Thành viên nhóm* cho giảng viên (trong course)

- **Vào từ:** mục *Thành viên nhóm (Fit-Nhóm)* ở điều hướng course (`lib.php`: `local_fitnhom_extend_navigation_course(navigation_node $nav, stdClass $course, context_course $ctx)` thêm `navigation_node` loại `TYPE_SETTING` → Moodle 4.x đưa vào menu *Thêm* của course) và một liên kết nhỏ ở đầu cột *Thành viên nhóm* trong bảng chấm (`locallib::get_name()` không đổi; liên kết đặt ở `view_summary` khi người xem có `mod/assign:grade` — chỉ một lần, qua biến tĩnh mỗi request).
- **Địa chỉ:** `local/fitnhom/course.php?id=<courseid>` (`require_login($course)`, `require_capability('mod/assign:grade', context_course)` — Q8: trợ giảng vào được). Sinh viên không thấy mục; gõ địa chỉ → `require_capability` từ chối.
- **Nội dung:** 
  - Đầu trang: chế độ hiện tại và câu *Course này đang áp dụng Fit-Nhóm* / *không áp dụng (lý do: chế độ Tắt | không trong danh sách thử | định dạng `x` | mẫu tên | danh mục)* — khi không áp dụng thì **chỉ** câu đó, không bảng.
  - Bảng **theo bài tập** (mọi assign của course có bài nộp từ lúc bật, bỏ bài bật *Nộp theo nhóm* — ghi *(nộp theo nhóm của Moodle — không hỏi)*): tên bài (liên kết tới bảng chấm), hạn, **bài nộp** (đã gửi), *một mình*, *bài nhóm*, **bạn được khai** (số dòng `tv` KHAI), **Không phải em**, **chưa khai** (số dòng `cho` còn chờ), **được tính nhờ nhóm** = số em **không có bài nộp riêng** nhưng có trong `thanhvien()` của bài (đây là số em Fit-Nhóm "cứu").
  - Bấm tên bài → `course.php?id=&assign=<assignid>`: **chi tiết** mỗi bài nộp một dòng: người nộp (MSSV – Họ tên), lúc nộp, câu khai (*Một mình* / *Bài nhóm*), các bạn (bạn từ chối ghi gạch ngang + *(không phải em)*), cờ *Cùng nhóm với bài nộp của …* (TL54 mục 3a, dùng `khai::cung_nhom`), *Chưa khai* cho dòng chờ. Thứ tự: lúc nộp giảm dần.
  - Nút **Tải Excel** (`course.php?id=&tai=1&sesskey=`): một tệp hai sheet *Theo bài* và *Chi tiết* (PhpSpreadsheet có sẵn trong Moodle: `\core\dataformat` hay `PhpOffice\PhpSpreadsheet` qua `$CFG->libdir/phpspreadsheet`); mọi ô chữ ghi kiểu chuỗi tường minh (chống chèn công thức khi tên bài bắt đầu bằng `=`); tên tệp `FitNhom_<shortname>_<Ymd>.xlsx`.
- **Không có** nút nhắc, không sửa câu khai hộ (giảng viên nộp thay thì sửa ở trang nộp bài như 0.1).
- Lớp mới `classes/baocao.php` (số liệu theo bài / chi tiết / Excel — dùng chung với 5.3), `classes/output/course.php` + `templates/course.mustache`.
- Sự kiện mới `event\course_xem` (`crud = r`, `edulevel = TEACHING`, không tên sinh viên) khi mở trang hay tải Excel.

### 5.2 Nhận diện course không áp dụng theo định dạng (D4)

- `phamvi::course_ap_dung()` thêm một lưới **trước** mẫu tên: `course.format` nằm trong cấu hình **`loaitru_format`** (textarea, mỗi dòng một định dạng, mặc định `fitcnl`) → không áp dụng. Lấy thêm cột `format` ở truy vấn `course` đang có (không thêm truy vấn).
- Mẫu `FITCNL:%` ở `loaitru_mau` **vẫn giữ** làm lưới thứ hai (mặc định không đổi).
- `phamvi::ly_do(int $courseid): string` mới — trả mã lý do `TAT | KHONG_THU | FORMAT | MAU | DANHMUC | ''` để trang 5.1 và Tình hình hiện lời.
- Bài kiểm: course `format = 'fitcnl'` tên `Bat ky` (không khớp mẫu) → không áp dụng; đổi `loaitru_format` rỗng → áp dụng.

### 5.3 Quản trị: Tình hình theo course, Cần xem, Quét ngay

Trang *Tình hình* (`tinhhinh.php`, chỉ admin) thêm ba khối, dữ liệu từ `classes/baocao.php`:

- **Theo course** (50 course nhiều bài nộp nhất từ lúc bật, lọc theo danh mục bằng ô chọn): tên rút gọn, bài nộp, đã khai, chưa khai, *Không phải em*, tỉ lệ bài nhóm. Không tên sinh viên. Nút *Tải Excel* (cùng cách 5.1).
- **Cần xem** (Q9, cấu hình `batthuong_tuchoi` mặc định 3, `batthuong_khai` mặc định 5): (a) người nộp bị ≥ *n* lần *Không phải em* trong 60 ngày; (b) em được khai trong ≥ *m* bài nộp của **những người nộp khác nhau** ở cùng một bài tập. Liệt kê **course và bài tập**, bấm *Xem* mới hiện tên (quyền admin đã có). Fit-Nhóm **không** tự làm gì với các dòng này. Không có dòng nào thì ghi *Không có gì bất thường*.
- **Quét ngay:** hiện *lần quét trước (giờ), đã xem n bài, thêm m bài vào hộp nổi*; `cho::quet()` trả `['xem' => n, 'them' => m]`, lưu `quet_so_xem`, `quet_so_them`, sự kiện `quet_ngay` thêm `other.soxem`. (Thay đổi giá trị trả về của `quet()` — chỉ `tinhhinh.php` và bài kiểm gọi.)

### 5.4 Hợp đồng: hai hàm thêm

`api::cua_nguoi()`, `api::ap_dung()` đúng mục 4.1–4.2 (H5, H6). `hopdong_test` thêm: `cua_nguoi` trả đúng và lọc *Không phải em*, khoảng `[tu, den]` đóng hai đầu, mỗi bài tập một dòng sớm nhất; `ap_dung` theo chế độ. `HOPDONG` **giữ 1**; bài kiểm khẳng định `api::HOPDONG === 1` (đổi số là đổi bài kiểm có chủ ý).

### 5.5 Hộp nổi nhẹ hơn (R1)

- `cho::co_cho(int $userid): bool` — `record_exists('local_fitnhom_cho', ['userid' => $userid])`; `hook_callbacks::dau_trang` gọi hàm này trước, `false` thì thôi (một truy vấn theo chỉ mục, không join).
- Bộ đệm MUC `local_fitnhom/cho` (`db/caches.php`, mode `MODE_APPLICATION`, khóa `userid`, giá trị `0|1`), ghi sau lần kiểm; **xóa khóa** của người đó ở `cho::them`, `khai::luu` (xóa dòng chờ), `khai::xoa_bai_nop`, `khai::xoa_bai_tap` (xóa cả cache — `purge`), và khi đổi chế độ (`lib.php::local_fitnhom_chedo_doi` purge).
- `cho::cua()` giữ, chỉ gọi khi `co_cho()` đúng.

### 5.6 `db/upgrade.php`

Bước `2026110100`: `set_config('loaitru_format', 'fitcnl', 'local_fitnhom')` khi chưa có; `batthuong_tuchoi` = 3, `batthuong_khai` = 5 khi chưa có. Không đổi bảng. `upgrade_plugin_savepoint(true, 2026110100, 'local', 'fitnhom')`. (Cài mới: `settings.php` mặc định đã đủ; `upgrade.php` chỉ cho máy đang chạy 0.1.)

### 5.7 Privacy

`provider::export_user_data` thêm bảng `cho` (bài nộp đang chờ khai của người: assign, course, lúc vào danh sách). `delete_*` đã có. `privacy_test` thêm khẳng định có khóa `cho`.

### 5.8 Cấu trúc mã sau 0.2 (R6)

| Tệp | Việc |
|---|---|
| `classes/phamvi.php` | như 0.1 + `loaitru_format`, `ly_do()` |
| `classes/khai.php` | lưu, gợi ý (`goi_y`, `chu_goi_y`), cùng nhóm, từ chối / hoàn tác, sao chép, xóa, `ung_vien`, `ma_sv`, `nhan`, `them_cau_hoi` |
| `classes/thongbao.php` (mới, tách từ `khai`) | `duoc_khai()`, `tu_choi()` — hai thông báo Moodle |
| `classes/tomtat.php` (mới, tách từ `khai`) | `cua_bai_nop()` chữ HTML cho bảng chấm / trạng thái, liên kết trang 5.1 |
| `classes/cho.php` | như 0.1 + `co_cho()`, bộ đệm, `quet()` trả mảng |
| `classes/baocao.php` (mới) | số liệu theo bài / chi tiết / theo course / cần xem / Excel |
| `classes/api.php` | + `cua_nguoi()`, `ap_dung()` |
| `classes/output/course.php`, `templates/course.mustache` | trang giảng viên |
| `course.php` (mới), `tinhhinh.php`, `settings.php`, `lib.php` | trang, cấu hình, điều hướng course |
| `db/upgrade.php`, `db/caches.php` (mới), `db/events.php`, `db/hooks.php` | |

Tách tệp **không đổi hành vi**; `khai_test` cũ giữ nguyên và vẫn phải đạt (chỉ đổi tên lớp gọi ở nơi cần — `tom_tat` → `tomtat::cua_bai_nop`; để lại `khai::tom_tat()` gọi chuyển tiếp một bản nếu bài kiểm cũ gọi).

### 5.9 Chuỗi (vi + en, cùng khóa)

`course_tieude`, `course_khongapdung`, `course_lydo_*` (5 lý do), `cot_bai`, `cot_han`, `cot_nop`, `cot_motminh`, `cot_nhom`, `cot_ban`, `cot_tuchoi`, `cot_chuakhai`, `cot_cuu`, `cot_nguoinop`, `cot_luc`, `cot_khai`, `cot_cacban`, `cungnhom_voi`, `nut_taiexcel`, `nut_xem`, `th_theocourse`, `th_canxem`, `th_canxem_khong`, `th_canxem_tuchoi`, `th_canxem_khai`, `th_quet_truoc`, `caidat_loaitru_format`, `caidat_loaitru_format_mota`, `caidat_batthuong_tuchoi`, `caidat_batthuong_khai`, `event_course_xem`, `nav_course`. Thêm bằng tay vào hai tệp `lang/` (kho này không có `addstrings.py`; giữ thứ tự khóa).

### 5.10 Kiểm thử 0.2

`tests/ban02_test.php` (≥ 10 bài; dùng `tests/helper.php` đã có — `che_do`, `bai_tap`, `nop`, `bai_nop`):

1. `course.php`: sinh viên → `required_capability_exception`; giảng viên và trợ giảng (vai `teacher`) → 200; course không áp dụng → câu lý do, không bảng.
2. Số liệu theo bài khớp dữ liệu dựng: 3 bài nộp (A khai B, C một mình, D qua hộp nổi chưa khai) → nộp 3, một mình 1, nhóm 1, bạn 1, chưa khai 1, *được tính nhờ nhóm* 1 (B).
3. Chi tiết bài: dòng của A có B; B bấm *Không phải em* → gạch ngang và *được tính nhờ nhóm* về 0.
4. `format = fitcnl` không áp dụng dù tên không khớp mẫu; `ly_do()` = `FORMAT`.
5. `cua_nguoi` (H5): hai bài khai B, khoảng chỉ ôm một bài → một dòng; B từ chối bài kia → không có.
6. `ap_dung` theo chế độ Tắt / Thử (course trong danh sách) / Bật.
7. Cần xem: 3 lần từ chối trong 60 ngày → có dòng; 2 lần → không; 5 người khác nhau khai cùng em → có.
8. `quet()` trả `['xem', 'them']` đúng; sự kiện có `soxem`.
9. Hộp nổi: `co_cho` false → `dau_trang` không thêm HTML (bắt bằng `$hook->get_output()` rỗng); thêm dòng chờ → có; đã bấm *Để sau* hôm nay → có dải, không gọi JS (kiểm `$PAGE->requires` bằng phản chiếu như FIT-CNL `ban1757`); bộ đệm xóa đúng sau `khai::luu`.
10. Nộp lần hai (bài cho phép nộp lại, `attemptreopenmethod`): câu khai được chép sang bài nộp mới (`copy_submission`).
11. Excel: tải về mở được bằng PhpSpreadsheet, ô tên bài `=1+1` là chuỗi.
12. Privacy: xuất có `cho`.

`dev/walk_02.py` (Playwright như `walk_01.py`): 8 ảnh vào `docs/anh/0.2/` — trang giảng viên (tổng + chi tiết), Excel tải, Tình hình theo course + Cần xem, Cài đặt có `loaitru_format`, hộp nổi.

### 5.11 Phát hành 0.2

`bash dev/test.sh` (0.1 cũ 14 + mới ≥ 12), `bash dev/lint.sh` sạch, `bash dev/package.sh` → `dist/local_fitnhom_0.2.zip`, `assignsubmission_fitnhom_0.2.zip`, `SHA256SUMS_0.2.txt`; `docs/huong-dan/Phieu-cai-dat_0.2.md` (nâng cấp từ 0.1: cài đè hai gói theo thứ tự `local` trước; bước 2026110100; kiểm *Thành viên nhóm* hiện ở menu course; thử trên **một course** rồi mới *Bật toàn trang*); `docs/huong-dan/Gui-giang-vien-sinh-vien_0.2.md` thêm đoạn trang *Thành viên nhóm*; sổ tay mục 1, 3, 5–7; `lich-su.md`; `nhat-ky.md`; TL54 v0.5 mục 9 bảng kế hoạch; Word (`dev/mkdocx.py`) phiếu + hướng dẫn; chép Drive `Fit-Nhom\Cai-dat\`, `Huong-dan\`, `Anh\0.2\`. Commit tiếng Việt, hai dòng ký, `git fetch` trước `git push origin main`. **Dừng, gửi gói + phiếu** (mục 7 bước 1).

---

## 6. FIT-CNL 1.8.0 — đặc tả

Bản 1.8.0 lên trên **1.7.81**: `local_fitcnl` version **2026110100**, release `1.8.0`; `format_fitcnl` cùng version, `dependencies` giữ `local_fitcnl => 2026101980` (quy ước mốc — HDBT mục 4 bước 1). **Một bước nâng cấp** `2026110100` (hai cột). Không bảng mới, không tác vụ mới, không mã nhật ký mới.

### 6.1 Quy tắc

- **Bốn điều kiện** mục 4.3 + công tắc `hd_fitnhom_bat` (nhóm cấu hình `hoatdong`, mặc định 1, D8). Module `hoatdong` tắt thì không có gì để đọc.
- **Lúc nộp** theo D2 / D3. Dữ liệu Fit-Nhóm là **đầu vào** của bước tính đã có (D5).
- **Hiện tên người nộp** theo D6: chính em → tên; người xem có quyền xem học tập của em **và** `nguoinop` đang là thành viên hiện hành của **lớp đang xem** (`thanhvien::dang_trong_lop($lopid, $userid)`, hàm có sẵn hay thêm nhỏ) → tên; còn lại → *nộp chung với một bạn cùng nhóm*. Tên qua `fullname()` như mọi chỗ.
- Bài có hạn ở course **không áp dụng** Fit-Nhóm không có câu khai → `thanhvien()` trả `[]` → không gì đổi; FIT-CNL **không** cần hỏi `ap_dung()` (tránh một lời gọi mỗi bài), nhưng được phép dùng `ap_dung()` ở trang Cấu hình để hiện *course lớp chủ nhiệm có bị loại đúng không* (tùy chọn, không bắt buộc).
- Khi Fit-Nhóm nâng `HOPDONG` lên 2 → `HOPDONG_LA` → chạy như cũ (D9). Khi plugin bị tắt / gỡ giữa chừng → lượt gộp sau tính lại không có nguồn FITNHOM → dòng có thể đổi về BOLO: **đúng ý**, không cần giữ dữ liệu cũ.

### 6.2 `dichvu\hoatdong\nopchung` (mới, ~120 dòng)

```php
namespace local_fitcnl\dichvu\hoatdong;

final class nopchung {
    public const HOPDONG_BIET = [1];
    public const LYDO = ['', 'TAT_CAU_HINH', 'MODULE_TAT', 'CHUA_CAI', 'PLUGIN_TAT', 'KHONG_LOP', 'HOPDONG_LA', 'TAT_FITNHOM', 'LOI'];
    /** @var callable|null Giả lập cho kiểm thử: fn(int $assignid): array — như gcal_api::$gia. */
    public static $gia = null;
    /** @var array|null Trạng thái giả cho kiểm thử: ['dung' => bool, 'lydo' => string, 'hopdong' => ?int]. */
    public static $giatrangthai = null;

    public static function trang_thai(): array;      // ['dung' => bool, 'lydo' => string, 'hopdong' => ?int, 'loi' => string] — tính một lần mỗi request
    public static function dung(): bool;              // trang_thai()['dung']
    public static function thanhvien(int $assignid): array;  // [userid => ['nguoinop' => int, 'luc' => int]]; [] khi không dùng / lỗi
    public static function xoa_dem(): void;           // cho kiểm thử và sau khi đổi cấu hình
}
```

- `trang_thai()` thứ tự kiểm: `module_registry::bat('hoatdong')` → `caidat::get('hd_fitnhom_bat')` → plugin info → class → `HOPDONG` → `api::dang_bat()` (false → `TAT_FITNHOM`). Mỗi lý do một chuỗi `fitnhom_ly_<mã>` (vi + en). `$giatrangthai` đặt → trả thẳng (bài kiểm).
- `thanhvien()`: `$gia` đặt → gọi `$gia`; không thì `dung()` false → `[]`; có → `api::thanhvien($assignid)` trong `try/catch \Throwable`: lỗi → `debugging()` **một lần mỗi request** (biến tĩnh), đặt trạng thái `LOI` với `getMessage()` cắt 200 ký tự, trả `[]`. Kết quả mỗi `assignid` nhớ trong request (gộp gọi một lần mỗi bài, `sap_toi` gọi vài lần).

### 6.3 Dữ liệu: `local_fitcnl_hd_baitap` thêm hai cột (bước `2026110100`)

| Cột | Kiểu | Ý nghĩa |
|---|---|---|
| `nguon` | char(10) NOT NULL DEFAULT `TU` | nguồn của `nop`: `TU` (em tự nộp, hay chưa nộp), `NHOMMD` (bài nộp theo nhóm Moodle), `FITNHOM` (bạn khác khai em) |
| `nguoinop` | int(10) NULL | userid người nộp khi `nguon = FITNHOM`; **NULL** với `TU` và `NHOMMD` (Moodle ghi `userid = 0` ở bản nộp nhóm — không biết ai bấm) |

- `install.xml` thêm hai FIELD (COMMENT ghi 1.8.0, TL60); `upgrade.php` bước mới `if ($oldversion < 2026110100)`: `add_field` hai cột nếu chưa có; dòng cũ nhận mặc định `TU`. Không chạm bước cũ.
- `gop::ghi_bai`: `$cot` thêm `nguon, nguoinop`; `$giong` so thêm hai cột.
- `privacy/provider.php`: cột xuất của `hd_baitap` thêm `nguon`, `nguoinop`; xóa dữ liệu của người B: `UPDATE … SET nguoinop = NULL WHERE nguoinop = B` (nguồn giữ `FITNHOM`), thêm vào `xoa_cua`; `COT_USER` **không** thêm `nguoinop` (không phải dữ liệu "của" B — như quy ước `usermodified`, HDBT điều 45). Bảng không có `lopid` → không thuộc đặt lại lớp (điều 41 không áp); `ban1780_test::bang_lopid_duoc_ke_du` không ảnh hưởng. Sao lưu: `saoluu::BANG` không chứa `hd_baitap` (bảng đệm tính lại được) — giữ nguyên.

### 6.4 `gop::bai_assign` (sửa)

Sau khi có `$nop` và `$nhom` (mã hiện tại), với mỗi `$a` không bật `teamsubmission` và `nopchung::dung()`: `$fn = nopchung::thanhvien((int) $a->id)` — **một lời gọi mỗi bài tập**. Với từng em `$u` (sau khi đã tính `$han`, `$hanchot` như cũ):

```
$ungvien = [];
if (isset($nop['u'.$u]))                 $ungvien[] = [$nop['u'.$u], 'TU', null];
if ($a->teamsubmission) foreach nhóm g của $u: if (isset($nop['g'.$g])) { $ungvien[] = [$nop['g'.$g], 'NHOMMD', null]; break; }
if (isset($fn[$u]))                      $ungvien[] = [$fn[$u]['luc'], 'FITNHOM', $fn[$u]['nguoinop']];
chọn phần tử có luc nhỏ nhất → [$lucnop, $nguon, $nguoinop]; rỗng → [null, 'TU', null]
```

`trang_thai($han, $hanchot, $lucnop, $luc)` không đổi. Dòng ghi thêm `nguon`, `nguoinop`. (Lưu ý: hiện mã chỉ lấy nhóm khi `$lucnop === null`; đổi thành "sớm nhất trong ba" là cách thống nhất D2 — với dữ liệu thật, bài tự nộp và bài nhóm hiếm khi cùng có, kết quả không đổi.)

Kết quả `gop::chay()` (mảng tóm tắt lượt) thêm `fitnhom` (0/1: lượt này có đọc Fit-Nhóm) và `nopchung` (số dòng ghi với `nguon = FITNHOM`) — hiện ở Quản trị › Hoạt động Moodle › *Lượt gộp gần nhất*.

### 6.5 `hoatdong::sap_toi` (sửa — sửa lỗi 1.3.0)

Bài assign 7 ngày tới của em **không** vào danh sách nếu một trong ba: em đã nộp (SQL hiện có); bài bật `teamsubmission` và nhóm của em (`teamsubmissiongroupingid`, hay mọi nhóm của course khi 0) có bài nộp `submitted` (`latest = 1`); `nopchung::thanhvien($assignid)` có em. Giữ SQL phần *chưa tự nộp*, lọc hai điều còn lại bằng PHP (số bài 7 ngày tới nhỏ; `groups_get_user_groups` cho nhóm). Quiz không đổi. Kết quả mỗi dòng thêm `nguon` (`''`, `NHOMMD`, `FITNHOM`) để trang của em ghi được *(bạn cùng nhóm đã nộp)* nếu muốn — 1.8.0 chỉ **bỏ** dòng, không cần ghi.

### 6.6 Những nơi tự đúng theo (không sửa mã, phải có bài kiểm khẳng định)

`hoatdong::tom_tat` (n / m đã nộp, trễ, `tremon`), `chi_tiet`, `danhsach::tinh` (mức chú ý mục Moodle), `quantam`, `theodoi::tin_hieu`, `ketxuat\tinhhinh`, thư nhắc — tất cả đọc `hd_baitap.trangthai`. Bài kiểm 1.8.0 khẳng định ít nhất ở `tom_tat` và `danhsach::tinh`.

### 6.7 Giao diện (chỉ thêm, không đổi bố cục)

| Nơi | Thêm |
|---|---|
| Hồ sơ em › Hoạt động Moodle (`output\hoatdong\xem`, chủ nhiệm / Giáo vụ / Ban điều hành theo A14-11) | dòng tổng *Bài có hạn: n/m đã nộp* thêm *(k bài nộp chung)* khi k > 0; chú thích một dòng `hd_nopchung_chuthich` *Bài nhóm: tính đã nộp khi bạn cùng nhóm nộp và khai tên em (Fit-Nhóm)* — chỉ khi `nopchung::dung()`. Bảng *trễ / bỏ lỡ gần đây* không gì thêm (bài nộp chung không còn trễ) |
| Trang của em › Hoạt động của tôi (`hoatdong\xem` chế độ `choem` / `sv_hoatdong`) | *Sắp tới hạn* đã bỏ bài nộp chung; khối gọn **Bài đã nộp chung (k)** — tên bài, môn, *nộp chung với bạn <Họ tên>* (em thấy tên), lúc nộp; tối đa 10 dòng, 30 ngày; ẩn khi k = 0 |
| Chủ nhiệm xem hồ sơ em, bảng bài gần đây (nếu có cột nguồn) | dòng `FITNHOM` ghi *nộp chung với <tên>* hay *với một bạn cùng nhóm* (D6) |
| Danh sách lớp, Sinh viên cần quan tâm, Theo dõi, Excel | không đổi (số đã đúng) |
| Quản trị FIT-CNL › Cấu hình › Hoạt động Moodle | công tắc `hd_fitnhom_bat`; dòng trạng thái **Fit-Nhóm: đang dùng (hợp đồng 1)** / **không dùng — <lời của lý do>**; khi có plugin: liên kết *Cài đặt Fit-Nhóm* (`admin/settings.php?section=local_fitnhom`) |
| Quản trị › Hệ thống › Tình trạng | cùng một dòng |
| *Hệ thống tự làm* (`tudong`) | mô tả `gop_hoatdong` thêm *đọc Fit-Nhóm khi có* |

Chuỗi mới (vi + en, `python3 dev/1.0/addstrings.py`): `hd_nopchung_tong` (`{$a}` = k), `hd_nopchung_chuthich`, `hd_nopchung_voi` (`{$a}` = tên), `hd_nopchung_banlop`, `hd_nopchung_khoi`, `hd_nopchung_khong`, `caidat_hd_fitnhom_bat`, `caidat_hd_fitnhom_bat_mota`, `fitnhom_tt_dung`, `fitnhom_tt_khong`, `fitnhom_ly_tat_cau_hinh`, `fitnhom_ly_module_tat`, `fitnhom_ly_chua_cai`, `fitnhom_ly_plugin_tat`, `fitnhom_ly_khong_lop`, `fitnhom_ly_hopdong_la`, `fitnhom_ly_tat_fitnhom`, `fitnhom_ly_loi`, `fitnhom_caidat_lienket`, `tudong_gop_fitnhom`. Không chuỗi `nhatky_*` mới (không mã nhật ký mới — điều 42 không đụng).

### 6.8 Cấu hình, trạng thái

- `caidat::MACDINH['hd_fitnhom_bat'] = 1`; nhóm `hoatdong` thêm khóa; kiểu công tắc (`CO`); mô tả nói rõ *chỉ đọc; tắt thì FIT-CNL tính như không có Fit-Nhóm từ lượt gộp sau*.
- `nopchung::trang_thai()` không ghi CSDL; trang Cấu hình và Tình trạng gọi trực tiếp.
- Đổi `hd_fitnhom_bat` không chạy gộp ngay; dòng mô tả ghi *có hiệu lực từ lượt gộp 02:30 kế tiếp, hay bấm Gộp ngay* (nút đã có ở Hoạt động Moodle).

### 6.9 Riêng tư

FIT-CNL chỉ lưu `nguoinop` (mã người), không danh sách thành viên, không câu khai. Xuất dữ liệu của em A: dòng bài X có `nguon = FITNHOM`, `nguoinop = <mã>`. Xóa dữ liệu của B: `nguoinop` trỏ B → NULL. Em thấy tên người nộp (Fit-Nhóm đã báo em — TL54 mục 3); chủ nhiệm / Giáo vụ chỉ khi cùng lớp (D6).

### 6.10 Kiểm thử 1.8.0

`tests/ban180_test.php` (≥ 9 bài), dùng bộ sinh (`goi`, `loi`, `tao_lop_mo(…, ['ten'])`, `tao_he_hocky()`; cần ngày tuyệt đối thì `tao_he_hocky_2026()` — điều 43) và `nopchung::$gia` / `$giatrangthai` (máy thử **không cần** Fit-Nhóm):

1. Điều kiện: không plugin → `dung()` false, `CHUA_CAI`; `$giatrangthai` đủ → true; `hd_fitnhom_bat = 0` → `TAT_CAU_HINH` dù giả lập; hợp đồng 2 → `HOPDONG_LA`; module `hoatdong` tắt → `MODULE_TAT`.
2. `bai_assign`: A nộp đúng hạn, `$gia` trả B → B `DUNGHAN`, `nguon = FITNHOM`, `nguoinop = A`; B tự nộp sau hạn → vẫn đúng hạn theo A (sớm nhất), `nguon = FITNHOM`; `$gia` không trả B (đã từ chối) → B `BOLO`, `nguon = TU`; `$gia` ném lỗi → chạy như cũ, `trang_thai()['lydo'] = LOI`, tác vụ không nổ, `debugging` một lần.
3. Bài nhóm Moodle: nhóm có nộp → em `DUNGHAN`, `nguon = NHOMMD`, `nguoinop = NULL`.
4. `sap_toi`: bài nhóm Moodle đã có bạn nộp → không còn; `$gia` khai em → không còn; không ai → còn.
5. `tom_tat` của B: `bai.nop` tăng, `tre` không; `danhsach::tinh` không gắn lý do Moodle cho B (A15-7).
6. Nâng cấp: bước `2026110100` thêm hai cột; dòng cũ `nguon = TU` (kiểm bằng `$DB->get_columns`).
7. Privacy: xuất có `nguon`, `nguoinop`; xóa B → NULL.
8. Hiện tên (D6): chính em thấy tên; chủ nhiệm lớp có cả A và B thấy tên; chủ nhiệm lớp chỉ có B thấy `hd_nopchung_banlop`.
9. Trang Cấu hình: dòng trạng thái đúng với từng lý do; công tắc đổi được (`goi` hành động `caidat.luu`).

Bộ sinh thêm `tao_assign_nop(int $courseid, int $userid, int $luc, array $gt = []): array` (tạo assign + bài nộp `submitted` lúc `$luc`; `$gt['teamsubmission']`, `['duedate']`) — dùng cho bài kiểm 1.8.0 và sau này; bài kiểm cũ không sửa.

`dev/1.0/walk/walk_180.py`: với Fit-Nhóm 0.2 cài trên máy phát triển (`fitnhom/dev/sync.sh`) → đi thật: course môn + assign, hai em A, B cùng lớp thử; A nộp khai B → Gộp ngay → hồ sơ B (*1/1 đã nộp (1 bài nộp chung)*), trang của B (khối *Bài đã nộp chung*), Cấu hình (dòng *đang dùng (hợp đồng 1)*); tắt `hd_fitnhom_bat` → dòng đổi. Không có Fit-Nhóm → chỉ chụp Cấu hình (lý do *chưa cài*). 6 ảnh vào `docs/1.0/anh/180/`.

### 6.11 Tài liệu FIT-CNL phải cập nhật trong bản 1.8.0

- **TL45** → `TL45_Thiet-ke-tong-hop-1.8.0.md` v13.2 (v13.1 = bản 1.7.81 trỏ TL60) (bản 1.7.81 **không** chép vào `ban-cu/TL45/` — chỉ bản mốc, Q11): A15-2 thêm câu *(1.8.0, TL60) FIT-CNL **đọc** `\local_fitnhom\api` khi đủ bốn điều kiện — ngoại lệ duy nhất của "không đụng course môn", vẫn chỉ đọc; Fit-Nhóm là dự án riêng (TL54), FIT-CNL không phụ thuộc*; A15-4 thêm D2 / D3 và hai cột; A15-7 thêm *Sắp tới hạn bỏ bài đã có bạn nộp (nhóm Moodle, Fit-Nhóm) — sửa lỗi 1.3.0*; **A15-9** mới: quy tắc hiện tên (D6) và khối *Bài đã nộp chung*; A2 thêm `hd_fitnhom_bat`; Phụ lục B thêm *B-FN1: FIT-CNL không nhắc em khai Fit-Nhóm, không hiện danh sách thành viên nhóm cho chủ nhiệm, không ghi gì sang Fit-Nhóm*; Phụ lục D: *Fit-Nhóm*, *nộp chung*, *hợp đồng (HOPDONG)*; mục 8 bỏ dòng 1.8.0 chờ; mục 10 câu mở phiên đổi bản; Phụ lục E dòng v13.2.
- **HDBT** v4.5: mục 1 (bảng `hd_baitap` + 2 cột, lớp `nopchung`), mục 2 thêm **điều 47**: *Đọc plugin ngoài chỉ qua lớp hợp đồng có số phiên bản, bọc `\Throwable`, bốn điều kiện, trạng thái hiện ở Cấu hình; không đọc bảng của plugin ngoài* (mẫu: `nopchung`); mục 6 dòng `gop_hoatdong`.
- `kich-ban/TL38_1.8.0.md`, `phieu-cai-dat.md` (mục H: bước CSDL 2026110100; thứ tự cài khi có Fit-Nhóm — mục 9 dưới), `so-tay.md` mục 1 / 3 / 5 / 6, `lich-su-cac-ban.md`, `nhat-ky.md`, sổ tay theo vai (`local_fitcnl/sotay/`: chủ nhiệm — đoạn *nộp chung*; sinh viên — *Bài đã nộp chung*; quản trị — công tắc), README gốc, CLAUDE.md (TL45 1.8.0, HDBT v4.5 điều 1–47, TL60).

---

## 7. Thứ tự làm và tiêu chí xong

| Bước | Kho | Việc | Xong khi |
|---|---|---|---|
| **0** | cả hai | Đọc mục 0 → 6 trọn; dựng máy (`fit-cnl/dev/1.0/setup.sh` nếu chưa; `fit-cnl/dev/1.0/test.sh` đủ **767** bài; `fitnhom/dev/sync.sh` + `fitnhom/dev/test.sh` đủ **14** bài) | hai bộ kiểm xanh trước khi sửa gì |
| **1** | `fitnhom` | Fit-Nhóm 0.2 — mục 5 (thứ tự gợi ý: 5.6 upgrade → 5.2 phạm vi → 5.4 API + `hopdong_test` → 5.8 tách tệp (bộ cũ vẫn xanh) → 5.5 hộp nổi → 5.3 báo cáo quản trị → 5.1 trang giảng viên → 5.7 privacy → 5.10 bài kiểm → 5.9 chuỗi rà → 5.11 phát hành) | PHPUnit ≥ 26 bài đạt, lint sạch, `walk_02` 8 ảnh, gói 0.2 + SHA, phiếu 0.2, Word trên Drive, commit đã đẩy. **Dừng**: gửi chủ dự án gói + phiếu + ảnh, nêu điều gì khác TL60 (ghi ở mục 13 rồi). |
| **2** | `fit-cnl` | FIT-CNL 1.8.0 — mục 6 (thứ tự: 6.3 lược đồ + privacy → 6.2 `nopchung` → 6.4 gộp → 6.5 sắp tới → 6.8 cấu hình → 6.7 giao diện + chuỗi → 6.10 bài kiểm (viết song song với từng phần) → `walk_180` → 6.11 tài liệu → phát hành theo HDBT mục 4) | 767 + ≥ 9 bài đạt, **cả với** `faketime '2027-02-15'` (HDBT mục 4 bước 2 — khởi tạo bằng giờ thật trước); lint sạch; `walk_180` đi thật với 0.2 trên máy; gói 1.8.0 + SHA; TL38_1.8.0; TL45 1.8.0; Word trên Drive `Xay-lai-1.0\`; commit đã đẩy. **Dừng**: gửi gói + TL38. |
| **3** | cả hai | Kiểm chéo mục 8 trên máy phát triển (đã nằm trong `walk_180`, nhưng chạy lại **toàn bộ bảng 8** bằng tay một lượt) | mọi dòng bảng 8 đúng; ghi kết quả vào `nhat-ky.md` hai kho |

Một phiên làm cả ba bước; mỗi bước **dừng** để chủ dự án xem (không cần chờ chủ dự án cài thật mới làm bước sau — máy phát triển đủ để đi thử). Việc ở bước 2 không phụ thuộc gì vào Moodle thật.

---

## 8. Kiểm chéo (trên máy phát triển, Fit-Nhóm 0.2 chế độ *Bật*, FIT-CNL 1.8.0, lớp thử có A, B, C; course môn M có bài X hạn hôm qua, bài Y hạn 3 ngày tới)

| # | Tình huống | Fit-Nhóm phải | FIT-CNL phải (sau *Gộp ngay*) |
|---|---|---|---|
| 1 | A nộp X đúng hạn, khai *bài nhóm* với B | B nhận thông báo; bảng chấm: A *Bài nhóm: B*, B *Có trong bài nhóm của A* | B: X **đúng hạn**, nguồn FITNHOM, người nộp A; hồ sơ B *(1 bài nộp chung)*; trang B có khối *Bài đã nộp chung* ghi tên A |
| 2 | B bấm *Không phải em* | A nhận thông báo; B gạch ngang | lượt gộp sau: B **Bỏ lỡ** (như trước 1.8), nguồn TU |
| 3 | B hoàn tác trong 7 ngày | B trở lại KHAI | B lại đúng hạn |
| 4 | C nộp X **sau hạn**, khai với A (A đã nộp đúng hạn khai C? — không) | C bài riêng, trễ | C **trễ** (chỉ bài riêng); A không đổi |
| 5 | A nộp X đúng hạn khai C; C nộp bản cập nhật sau hạn | hai bài nộp; bảng chấm C ghi *Cùng nhóm với bài nộp của A* | C **đúng hạn** (sớm nhất = bài của A), nguồn FITNHOM |
| 6 | A nộp Y (tương lai) khai B | — | *Sắp tới hạn* của B **không** có Y; của C có |
| 7 | Bài Z bật *Nộp theo nhóm* của Moodle, nhóm {A, B}, A nộp | Fit-Nhóm không hỏi | B đúng hạn, nguồn NHOMMD, người nộp trống; *Sắp tới hạn* của B không có Z |
| 8 | Quản trị FIT-CNL tắt `hd_fitnhom_bat` | — | Cấu hình: *không dùng — tắt ở cấu hình*; gộp lại → B ở tình huống 1 thành Bỏ lỡ |
| 9 | Quản trị Fit-Nhóm chuyển chế độ **Tắt** | không hỏi nữa | Cấu hình FIT-CNL: *không dùng — Fit-Nhóm đang tắt*; dữ liệu khai cũ **không** còn được dùng (H4 + `dang_bat()` ở `nopchung::dung()`) |
| 10 | Gỡ cài `local_fitnhom` | — | *chưa cài*; FIT-CNL mọi trang chạy bình thường |
| 11 | Chủ nhiệm lớp khác (chỉ có B) xem hồ sơ B | — | *nộp chung với một bạn cùng nhóm* (không tên A) |
| 12 | Course lớp chủ nhiệm (`format = fitcnl`) có assign | không hỏi, trang *Thành viên nhóm* ghi lý do *định dạng fitcnl* | không gì đổi |

---

## 9. Cài thật — thứ tự và cách lùi

1. **Fit-Nhóm 0.2** (hay 0.1 nếu đã cài) trên Moodle môn: chế độ *Thử nghiệm* với **một course** của một giảng viên đồng ý thử (Q5) → vài tuần → *Bật toàn trang*. Không phụ thuộc FIT-CNL.
2. **FIT-CNL 1.8.0** nâng theo phiếu mục H bất cứ lúc nào (có hay không có Fit-Nhóm). Sau khi nâng, Cấu hình › Hoạt động Moodle cho biết đang đọc được hay không.
3. Lùi: tắt `hd_fitnhom_bat` (FIT-CNL về cách tính cũ từ lượt gộp sau) hay chuyển Fit-Nhóm về *Tắt* (câu hỏi biến mất ngay; dữ liệu khai giữ, không dùng). Không cần gỡ plugin, không mất dữ liệu.
4. Không đụng ba Moodle thật từ phiên viết mã; chủ dự án cài theo phiếu.

---

## 10. Để sau (ngoài đợt 1.8)

- **Fit-Nhóm 0.3 — ứng dụng Moodle trên điện thoại**: `assignsubmission_fitnhom/db/mobile.php`, handler `AddonModAssignSubmissionDelegate`, mẫu Ionic hai lựa chọn + ô chọn bạn từ dịch vụ web `local_fitnhom_ung_vien`; không nộp ngoại tuyến; thử trên máy thật Android / iOS. Chỉ làm sau khi 0.1 / 0.2 chạy thật và thấy nhiều bài nộp qua ứng dụng (số *chưa khai* ở Tình hình cao).
- FIT-CNL: ghi *(bạn cùng nhóm đã nộp)* ở *Sắp tới hạn* thay vì bỏ dòng (nếu sinh viên muốn thấy); cột nguồn ở Excel *Tình hình hoạt động*.
- Hai kho dùng chung công cụ `addstrings.py`, `mkdocx.py` (chép sang `fitnhom/dev/` khi có nhu cầu).

---

## 11. Rủi ro

| Rủi ro | Cách giảm |
|---|---|
| Phiên viết mã đọc TL59 v1.0 cũ thay vì TL60 | TL59 trong kho `fit-cnl` chuyển vào `docs/1.0/ban-cu/` (chỉ để tra), mọi chỗ trỏ TL60; Project claude.ai và Drive chỉ còn TL60; câu mở phiên chỉ nêu TL60 |
| Hai bản chép TL60 lệch nhau | Mục 0 nguyên tắc 1; câu mở phiên bắt đổi cả hai; mục 13 ghi phiên bản |
| Bài kiểm cũ của Fit-Nhóm đỏ khi tách tệp (5.8) | giữ `khai::tom_tat()` chuyển tiếp; tách tệp là bước riêng, chạy bộ cũ ngay sau |
| `extend_navigation_course` không hiện ở theme Trường | kiểm bằng walk với theme mặc định (Boost); phiếu cài đặt ghi *nếu không thấy mục, vào `local/fitnhom/course.php?id=`* |
| Hiệu năng `thanhvien()` khi assign có hàng nghìn bài nộp | truy vấn theo chỉ mục `assignid_userid_ix`; gộp gọi một lần mỗi bài trong cửa sổ 35 ngày; đo ở `walk_180` với 300 bài nộp giả (một ca trong `ban02` dựng 300 bài nộp, khẳng định < 1 truy vấn / 100 ms) |
| Moodle đổi biểu mẫu nộp bài ở bản mới | ghi ở phiếu cài đặt Fit-Nhóm (đã có từ 0.1) |

---

## 12. Câu mở phiên (dán vào phiên Opus — một câu cho cả đợt)

> Bạn làm **đợt 1.8** của FIT-CNL theo **`TL60_Thiet-ke-dot-1.8_Fit-Nhom-0.2-va-FIT-CNL-1.8.0_v1.1.md`** (bản gốc `fit-cnl/docs/1.0/`, bản chép `fitnhom/docs/`; hai bản giống nhau, đổi thì đổi cả hai và ghi mục 13). Hai kho GitHub: `lnthanhhcmus/fitnhom` (bản hiện hành 0.1, version 2026102700) và `lnthanhhcmus/fit-cnl` (bản hiện hành 1.7.81, version 2026101981; mốc lưu trữ 1.7.80 = thẻ `v1.7.80`). Đọc trọn TL60 trước, rồi các tệp ở TL60 mục 0 (bảng *Tài liệu cần đọc kèm*) của kho đang làm. Làm theo **TL60 mục 7**: bước 0 dựng máy và chạy hai bộ kiểm (767 bài FIT-CNL, 14 bài Fit-Nhóm — chưa đủ thì sửa máy, không sửa bài kiểm); **bước 1** Fit-Nhóm 0.2 (mục 5) rồi dừng gửi tôi gói + phiếu + ảnh; **bước 2** FIT-CNL 1.8.0 (mục 6) rồi dừng gửi gói + TL38; **bước 3** kiểm chéo mục 8, ghi nhật ký hai kho. Quy tắc: (1) hợp đồng mục 4 là luật — `HOPDONG` = 1, không đổi ý nghĩa `dang_bat()` / `thanhvien()`, chỉ thêm `cua_nguoi()` / `ap_dung()`; FIT-CNL chỉ gọi `\local_fitnhom\api`, bọc `\Throwable`, bốn điều kiện mục 4.3, thiếu thì chạy như cũ; (2) TL60 mục 2.1 là quyết định đã chốt — gặp điều bắt buộc phải khác thì **ghi vào mục 13 trước**, đụng mục 2.1 thì dừng hỏi tôi, đụng mục 5–6 thì tự quyết và ghi lý do; mục 2.2 (Q8, Q9) đã trả lời; (3) FIT-CNL: không đổi hành vi TL45 Phụ lục A ngoài những gì TL60 mục 6.11 ghi, không làm Phụ lục B, không quay về Phụ lục C; mọi quy tắc CLAUDE.md và hướng dẫn bảo trì mục 2 (điều 1–46, nhất là 41–45) của kho ấy; (4) Fit-Nhóm: không thêm việc cho sinh viên / giảng viên, không thư nhắc, không tác vụ đêm, câu khai không phải bài nộp, không phụ thuộc FIT-CNL (nhận diện lớp chủ nhiệm bằng `course.format`), privacy cho bảng có dữ liệu người, sự kiện không chép tên bạn; (5) bài kiểm mới đúng mục 5.10 / 6.10, bài kiểm cũ không sửa; PHPUnit trọn bộ đạt (FIT-CNL: cả với `faketime '2027-02-15'`, khởi tạo bằng giờ thật trước), lint sạch, walk có ảnh; (6) cuối mỗi bước: version + upgrade, sổ tay / nhật ký / lịch sử / phiếu / TL38 / TL45 (bản 1.8.0, không chép 1.7.81 vào `ban-cu/TL45/`) / HDBT v4.5 điều 47 như TL60 mục 5.11 và 6.11; gói zip + SHA-256; bản Word (`mkdocx.py`) gửi tôi và chép lên Drive (`Fit-Nhom\…`, `Xay-lai-1.0\…` — Drive không giữ bản cũ, bản mới thay bản cũ cùng tên nhóm); commit tiếng Việt, hai dòng ký theo nhắc của môi trường, `git fetch` trước `git push origin main`; (7) không đụng ba Moodle thật, không ghi khóa vào kho; (8) **xong mỗi bước thì dừng**, nêu điều gì khác TL60 (đã ghi ở mục 13).

---

## 13. Sổ điều chỉnh (tài liệu sống — thêm dòng, không xóa dòng)

Cách ghi: mỗi điều chỉnh một dòng — ngày giờ, ai (chủ dự án / phiên viết mã Opus / phiên thiết kế), **điều gì đổi so với bản trước**, mục bị ảnh hưởng, phiên bản TL60 sau khi đổi. Điều chỉnh đụng mục 2.1 phải có chữ của chủ dự án (trích). Khi đổi, sửa luôn mục liên quan ở trên và tăng số ở dòng đầu tài liệu; **đổi cả hai bản chép**.

| Ngày | Ai | Điều chỉnh | Mục | TL60 |
|---|---|---|---|---|
| 31/10/2026 21:16 | phiên thiết kế (Fable) | Viết v1.0 từ TL54 v0.4 mục 10–12 và TL59 v1.0; thêm D3–D9, Q8–Q9, rà soát 0.1 (R1–R12), bước 0 / 3, bảng kiểm chéo, cách lùi | tất cả | v1.0 |
| 31/10/2026 22:13 | chủ dự án | Q8: "ok" (trợ giảng xem được trang *Thành viên nhóm*); Q9: "à, ok, cứ theo ý bạn" (giữ khối *Cần xem*, ngưỡng 3 / 5). Không đổi đặc tả — mục 2.2 chuyển thành đã trả lời; mục 0, 5.1, 12 bỏ chữ "mặc định" | 0, 2.2, 5.1, 12 | v1.1 |
