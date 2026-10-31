# TL54 — Fit-Nhóm: khai thành viên nhóm khi nộp bài trên Moodle (thiết kế v0.4)

*v0.4 · 31/10/2026 — bổ sung cho **bản 0.2**: trang *Thành viên nhóm* cho giảng viên trong course, nhận diện course lớp chủ nhiệm theo định dạng `fitcnl` (không chỉ theo mẫu tên), bảng tình hình theo course và số *Không phải em* bất thường cho quản trị, hợp đồng API thêm hàm (vẫn `HOPDONG` 1), chuẩn bị ứng dụng điện thoại (bản 0.3). Phía FIT-CNL đọc Fit-Nhóm thiết kế ở kho `fit-cnl`: `docs/1.0/TL59_Thiet-ke-1.8.0_FIT-CNL-doc-Fit-Nhom_v1.0.md` — mục 6 ở đây là hợp đồng, TL59 là cách dùng. Bản v0.3 ở `docs/ban-cu/`.*

*v0.3 · 26/10/2026 22:12 — chủ dự án chọn hướng (A): **bỏ khai bổ sung bài cũ** (thư nhắc, tác vụ đêm, *Gửi nhắc ngay*); giữ hộp nổi nhẹ cho bài nộp qua ứng dụng; Quét ngay chỉ quét bài nộp từ lúc bật (mục 4, 5, 7, 9). Bản v0.2 ở `docs/ban-cu/`.*

*v0.2 · 26/10/2026 tối — thêm mục 3a (hai bạn cùng nhóm đều nộp, nộp bản cập nhật; chủ dự án hỏi 20:00). Bản v0.1 ở `docs/ban-cu/`.*

*26/10/2026 · kho `github.com/lnthanhhcmus/fitnhom` · tài liệu trên Drive: `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\`*

## 0. Vì sao có plugin này (đọc trước khi sửa)

Nhiều bài tập / đồ án trên Moodle là **bài nhóm nhưng chỉ một bạn nộp**. Moodle chỉ ghi bài nộp cho người bấm nộp, nên mọi hệ thống đọc dữ liệu Moodle — trong đó có FIT-CNL (theo dõi học tập cho chủ nhiệm lớp) — thấy các bạn còn lại **không nộp** và cảnh báo sai.

Các cách đã cân nhắc và loại (chủ dự án 26/10/2026, 18:09–18:46):

| Cách | Vì sao không dùng |
|---|---|
| Mở từng tệp bài nộp để dò tên thành viên | Tệp đủ dạng (PDF, zip dự án…), không đọc tự động được |
| Bắt giảng viên bật *Nộp theo nhóm* (Students submit in groups) của Moodle | Giảng viên phải tạo nhóm; thực tế không ai tuân theo |
| Sinh viên tự khai ở trang lớp chủ nhiệm (FIT-CNL) | Sinh viên bận, mặc kệ cảnh báo; không ai siêng khai |
| **Người nộp khai thành viên ngay lúc nộp** | **Chọn** — chỉ người nộp làm thêm ~10 giây, các bạn khác không làm gì; giảng viên không phải bật gì |

Fit-Nhóm là **dự án độc lập**: không phụ thuộc FIT-CNL, Trường không cài FIT-CNL vẫn dùng được (giảng viên xem thành viên nhóm lúc chấm). FIT-CNL đọc dữ liệu của Fit-Nhóm qua một API có số phiên bản (mục 6); thiếu Fit-Nhóm thì FIT-CNL chạy như cũ.

Quyết định của chủ dự án làm nền:
- **Q1** (18:34) Bỏ TL45 A15-2 của FIT-CNL ("Đừng ghi gì vào course môn") **riêng cho việc này**: plugin riêng cài trên Moodle môn học; ghi tài liệu đầy đủ, duy trì như dự án độc lập; FIT-CNL đọc được mà không lỗi khi Fit-Nhóm vắng mặt hay đổi khác.
- **Q2** (18:34) **Mọi bài tập bắt buộc** khai cá nhân hay nhóm; **giảng viên không tắt được**; không ảnh hưởng việc tải bài về chấm và ghi điểm; **admin** tắt được khi có vấn đề hàng loạt; trang quản trị riêng, nằm trong danh sách **mô-đun cục bộ**.
- **Q3** (18:34) **Báo cho thành viên** được khai tên.
- ~~**Q4** (18:34, 18:35) Khai bổ sung bài đã nộp từ đầu học kỳ, thư nhắc 4 ngày, tác vụ đêm~~ — **bỏ ở Q7**. Giữ từ Q4: **hộp nổi** liệt kê bài để bấm khai ngay (chỉ cho bài nộp chưa có câu khai sau khi bật).
- **Q5** (18:46) **Thử trước trên một lớp** chọn trước rồi mới bật đồng loạt; có nút **Quét ngay** thay vì chờ qua đêm.
- **Q6** (18:46) Tên *Fit-Nhóm*; trong plugin ghi rõ mục đích để người sau hiểu.
- **Q7** (22:12, hướng A) **Không khai bổ sung bài cũ** — FIT-CNL chỉ tính *trễ / bỏ lỡ* trong 30 ngày nên lợi ích nhỏ, còn hỏi lại 5–15 bài mỗi em dễ làm sinh viên ghét plugin. Không thư nhắc, không tác vụ đêm. Bài **nộp mới mà chưa có câu khai** (qua ứng dụng điện thoại, dịch vụ web) → hộp nổi lần sau em vào Moodle bằng trình duyệt.

## 1. Thành phần

Moodle chỉ cho thêm ô vào **trang nộp bài** qua một *plugin nộp bài* (subplugin của Assignment); plugin cục bộ không chen vào biểu mẫu đó được. Nên Fit-Nhóm gồm hai phần, chung một kho, chung số phiên bản:

| Thành phần | Thư mục cài | Việc |
|---|---|---|
| **`local_fitnhom`** (chính) | `local/fitnhom` | Bảng dữ liệu, trang quản trị, chế độ Tắt / Thử nghiệm / Bật, hộp nổi cho bài chưa khai, thông báo, API cho plugin khác |
| **`assignsubmission_fitnhom`** (mỏng) | `mod/assign/submission/fitnhom` | Vẽ câu hỏi trên trang nộp bài, kiểm bắt buộc, chuyển dữ liệu cho `local_fitnhom` lưu; cột *Thành viên nhóm* ở bảng chấm |

`assignsubmission_fitnhom` phụ thuộc `local_fitnhom` (khai ở `version.php`). Moodle 4.5, PHP 8.1–8.3 như FIT-CNL.

## 2. Trang nộp bài

Câu hỏi **bắt buộc**, không chọn sẵn:

> **Bài này em làm thế nào?** *(để các bạn cùng nhóm không bị tính là chưa nộp)*
> ○ Em làm một mình  ○ Bài nhóm — các bạn cùng làm: [ô gõ-chọn *MSSV – Họ tên*]

- Ô gõ-chọn chỉ có **người được nộp bài trong course đó** (quyền `mod/assign:submit`), bỏ chính người nộp; tối đa *n* bạn (Cấu hình, mặc định 10). Không gõ tay tên.
- Chưa trả lời thì **không lưu được** trang nộp bài (luật bắt buộc của biểu mẫu). Không chặn nút *Nộp để chấm* (`precheck_submission`) — để bài nộp qua ứng dụng không bị kẹt; bài thiếu câu khai đi vào hộp nổi (mục 5).
- Câu trả lời **không tính là bài nộp**: chỉ khai mà không có tệp / văn bản thì Moodle vẫn coi là chưa nộp (`is_empty` luôn đúng).
- Sửa bài nộp sau đó: câu trả lời cũ điền sẵn, sửa được.
- **Giảng viên nộp thay** (sửa bài nộp của sinh viên): câu hỏi **không bắt buộc** — không làm giảng viên thêm việc.
- Bài đã bật *Nộp theo nhóm* của Moodle: **không hỏi** (Moodle đã biết nhóm).
- **Course không áp dụng** (mục 4) và chế độ Tắt: không hỏi.

Với giảng viên: **không có ô bật / tắt** trong cài đặt bài tập (plugin "không cấu hình được"; bật hay không do `local_fitnhom` quyết theo chế độ). *Tải toàn bộ bài nộp* **không đổi** (plugin không có tệp). Sổ điểm không đổi. Bảng chấm có thêm cột **Thành viên nhóm** (*Một mình* / tên các bạn; bạn đã bấm *Không phải em* ghi gạch ngang; dòng của bạn không tự nộp mà có trong bài nhóm của người khác ghi *Có trong bài nhóm của: A (giờ)*; bài đã gửi chưa khai ghi *Chưa khai*; chưa nộp thì trống).

## 3. Thành viên được khai tên

- Thông báo Moodle (chuông, và thư theo cài đặt thông báo của em): *"Bạn A khai em cùng làm bài X (môn Y). Nếu không đúng, bấm Không phải em."*
- **Không phải em** (trang của Fit-Nhóm, phải đăng nhập, chỉ chính em bấm được): em đó không được tính; người nộp nhận thông báo; giảng viên thấy dấu gạch ở bảng chấm. Bấm nhầm thì *Hoàn tác* trong 7 ngày.

## 3a. Hai bạn cùng nhóm đều nộp, nộp bản cập nhật

Bài không bật *Nộp theo nhóm* thì Moodle giữ **bài nộp riêng của từng em**: B nộp không đè bài của A, giảng viên vẫn tải được cả hai như mọi khi. Fit-Nhóm gắn câu khai vào **từng bài nộp**, nên:

- **B mở trang nộp khi A đã nộp và khai tên B**: đầu câu hỏi ghi *"Bạn A đã nộp bài này lúc 20:15 25/10 và khai em cùng nhóm — em không cần nộp lại. Nếu nộp bản cập nhật, chọn các bạn cùng nhóm bên dưới."*; ô *Bài nhóm* **điền sẵn** A và các bạn A đã khai (bỏ chính B) để hai bản khai khớp nhau. B vẫn sửa được.
- **Cả hai cùng nộp, khai khác nhau**: lưu cả hai, không bản nào thắng bản nào; giảng viên thấy ở cột *Thành viên nhóm* của bảng chấm dòng **"Cùng nhóm với bài nộp của: A (20:15 25/10)"** để biết đâu là bản mới hơn và chấm theo cách của mình. Fit-Nhóm không gộp, không xóa bài nộp nào.
- **Tính đã nộp cho một em** (API mục 6, FIT-CNL dùng): lấy **lúc sớm nhất** trong các nguồn — bài nộp của chính em, hoặc bài nộp của bạn khác có khai em (em chưa bấm *Không phải em*). Ví dụ A nộp đúng hạn có khai B, sau hạn B nộp bản cập nhật → B vẫn tính **đúng hạn** theo bài của A. `thanhvien()` vì vậy trả **mọi** bạn được khai, kể cả bạn cũng có bài nộp riêng; FIT-CNL tự lấy lúc sớm nhất.
- **B đã nộp (tự khai) thì không bị hỏi** ở hộp nổi dù A cũng khai B; hộp nổi chỉ hỏi bài nộp chưa có câu khai.
- **B bấm "Không phải em" ở bài của A** nhưng B có bài nộp riêng: B vẫn tính theo bài của chính B.

## 4. Chế độ, phạm vi, trang quản trị

**Quản trị trang › Plugin › Mô-đun cục bộ › Fit-Nhóm** (chỉ admin; không có trên thanh / thẻ nào khác). Đầu trang ghi mục đích (mục 0, rút gọn) và liên kết tài liệu.

| Mục | Ý nghĩa | Mặc định |
|---|---|---|
| **Chế độ** | *Tắt* / *Thử nghiệm — chỉ các course chọn* / *Bật toàn trang* | **Tắt** khi mới cài |
| Course thử nghiệm | Ô gõ-chọn course (một hay vài lớp môn) | trống |
| Course **không áp dụng** | Danh mục course loại trừ + mẫu tên rút gọn loại trừ (mỗi dòng một mẫu) — cho lớp chủ nhiệm, lớp sinh hoạt chung, course hành chính | mẫu `FITCNL:%` (course lớp chủ nhiệm của FIT-CNL) |
| Số bạn tối đa | Trong ô gõ-chọn | 10 |
| Trường hiện mã sinh viên | `idnumber` hay `username` | `idnumber` (trống thì `username`) |
| Nút **Quét ngay** | Tìm ngay các bài đã nộp **từ lúc bật** (chuyển khỏi *Tắt*) mà chưa có câu khai, ở course đang áp dụng, đưa vào hộp nổi của người nộp — không chờ gì | |
| Bảng tình hình | Lúc bật; số bài nộp từ lúc bật ở course áp dụng; đã khai (một mình / nhóm); số bạn được khai, số *Không phải em*; số bài đang chờ khai ở hộp nổi; lần quét gần nhất | |

Bài ở course **không áp dụng** không hỏi, không vào hộp nổi. Ở course áp dụng mà bài không phải bài nhóm thì sinh viên **bấm "Em làm một mình"** — một cú bấm.

Thử nghiệm: chọn **một course**, chế độ *Thử nghiệm* → chỉ course đó có câu hỏi, hộp nổi, thông báo. Ổn thì chuyển *Bật toàn trang*.

## 5. Bài nộp chưa có câu khai — hộp nổi (không khai bổ sung bài cũ, Q7)

**Bài nào:** bài nộp **sau lúc bật** (chế độ chuyển khỏi *Tắt*), ở course áp dụng, bài không bật *Nộp theo nhóm*, đã gửi mà **chưa có câu khai** — thường là nộp qua **ứng dụng Moodle trên điện thoại** hay dịch vụ web (không có ô khai). Bài nộp trước lúc bật **không hỏi**.

**Bắt lúc nào:** ngay khi nộp — Fit-Nhóm lắng nghe sự kiện nộp bài của Moodle (`assessable_submitted`); bài thiếu câu khai vào danh sách chờ của người nộp. Nút **Quét ngay** ở trang quản trị bắt lại những bài lọt (vd. nộp lúc plugin đang tắt rồi bật lại). **Không có tác vụ đêm, không thư nhắc.**

**Hộp nổi:** người nộp vào Moodle bằng trình duyệt mà còn bài chờ → **hộp nổi** (một lần mỗi ngày) *"Em còn n bài cần khai thành viên"*: mỗi dòng một bài (môn, tên bài, ngày nộp) với nút **Em làm một mình** (khai luôn, dòng biến mất) và **Bài nhóm…** (mở trang khai của bài đó, chọn các bạn từ danh sách *MSSV – Họ tên*). **Để sau** → hôm sau hiện lại; dải nhỏ trên đầu trang (*Em còn n bài cần khai*) mở trang khai lúc nào cũng được. Khai xong hết thì không còn gì hiện.

## 6. Hợp đồng với plugin khác (FIT-CNL)

Plugin khác **không đọc bảng** của Fit-Nhóm, chỉ gọi:

```php
\local_fitnhom\api::HOPDONG        // int — phiên bản hợp đồng, hiện 1
\local_fitnhom\api::dang_bat(): bool                  // chế độ khác Tắt
\local_fitnhom\api::thanhvien(int $assignid): array   // [userid => ['nguoinop' => userid, 'luc' => giờ nộp]]
                                                      // các bạn được khai, chưa bấm "Không phải em", không gồm người nộp;
                                                      // một em được nhiều bài khai → lấy bài nộp sớm nhất (mục 3a)
// 0.2 — thêm, không đổi hai hàm trên (vẫn HOPDONG 1):
\local_fitnhom\api::cua_nguoi(int $userid, int $tu, int $den): array
                                                      // [assignid => ['nguoinop' => userid, 'luc' => giờ nộp]] — các bài tập mà em
                                                      // được khai cùng nộp, bài nộp trong [tu, den]; cùng luật lọc như thanhvien().
                                                      // Cho trang của một em (FIT-CNL: "Bài đã nộp chung", Sắp tới hạn).
\local_fitnhom\api::ap_dung(int $courseid): bool      // course có áp dụng Fit-Nhóm không (chế độ, danh sách thử, loại trừ)
```

**Luật giữ hợp đồng:** đổi bảng / cách lưu bên trong thoải mái; **không đổi** ý nghĩa các hàm trên trong cùng `HOPDONG`. Thêm hàm mới thì giữ `HOPDONG`; đổi ý nghĩa hàm đã có thì tăng `HOPDONG` (khi đó plugin cũ tự bỏ qua). Có bài kiểm giữ hợp đồng (`tests/hopdong_test.php` — 0.2 thêm hai hàm).

**Phía FIT-CNL (bản 1.8.0, TL59):** chỉ dùng khi đủ cả bốn điều — `local_fitnhom` đã cài **và** đang bật trong danh sách plugin, lớp `\local_fitnhom\api` tồn tại, `HOPDONG` là số FIT-CNL biết (1), lời gọi không ném lỗi (bọc `try/catch \Throwable`). Thiếu một điều: chạy như hiện nay, Quản trị FIT-CNL › Cấu hình › Hoạt động Moodle ghi một dòng *Fit-Nhóm: không dùng (lý do)*. Lúc nộp của một em = sớm nhất trong tự nộp / nhóm Moodle / bài nộp chung; bài nộp chung hiện **Đã nộp — nộp chung với bạn A** (em thấy tên; chủ nhiệm chỉ thấy tên khi người nộp cùng lớp), không còn tính *trễ / bỏ lỡ*. FIT-CNL vẫn **chỉ đọc** (A15-2 giữ cho FIT-CNL; ngoại lệ ghi rõ cho Fit-Nhóm). FIT-CNL 1.8.0 chỉ cần `thanhvien()` và `dang_bat()` — hai hàm 0.2 là để tiện, FIT-CNL kiểm `method_exists` trước khi gọi.

## 7. Dữ liệu và riêng tư

| Bảng (`local_fitnhom_…`) | Cột chính |
|---|---|
| `khai` | assign, submission, course, người khai, loại (MOT / NHOM), nguồn (FORM / HOPNOI), thời gian |
| `thanhvien` | khai, userid, trạng thái (KHAI / TUCHOI), lúc từ chối |
| `cho` | submission, assign, course, người nộp, lúc vào danh sách chờ |

Không lưu nội dung bài. Khai báo với **Privacy API** của Moodle (xuất / xóa dữ liệu theo người). Xóa course / bài tập → xóa dòng liên quan. Nhật ký Moodle: mỗi thao tác một sự kiện (`khai_luu`, `thanhvien_tuchoi`, `quet_ngay`) — không chép tên người được khai vào phần mô tả.

## 8. Rủi ro đã biết

- **Ứng dụng Moodle trên điện thoại**: ứng dụng không vẽ được câu hỏi của plugin lạ; có thể báo *không sửa được bài nộp trong ứng dụng* → em nộp bằng trình duyệt (cả trên điện thoại). Bản 0.2 có thể thêm phần cho ứng dụng nếu cần. Bài đã nộp qua ứng dụng vẫn được bắt và hỏi ở hộp nổi khi em vào bằng trình duyệt (mục 5). *Cần thử trên máy thật.*
- **Trang trạng thái bài nộp của em** không có dòng câu khai: Moodle chỉ hiện plugin có nội dung bài nộp, mà câu khai không tính là bài nộp (`is_empty`). Em xem / sửa ở trang *Sửa bài nộp*; giảng viên thấy ở bảng chấm. (Đi thử 0.1.)
- **Khai bừa**: kiểm bằng thông báo *Không phải em* + giảng viên thấy ở bảng chấm; Fit-Nhóm không xét ai đóng góp bao nhiêu.
- **Course "không phải môn học"** chưa vào danh sách loại trừ: sinh viên phải bấm *Một mình* — phiền nhỏ; admin thêm mẫu loại trừ.
- **Cập nhật Moodle**: biểu mẫu nộp bài của Assignment đổi ở bản Moodle mới → kiểm lại `assignsubmission_fitnhom` mỗi lần nâng cấp Moodle (ghi ở phiếu cài đặt).

## 9. Kế hoạch bản

| Bản | Nội dung | Tình trạng |
|---|---|---|
| **Fit-Nhóm 0.1** | Hai thành phần; mục 2–5, 7; trang quản trị; Quét ngay; PHPUnit + đi thử trình duyệt; README, hướng dẫn quản trị, trang ngắn cho giảng viên / sinh viên; phiếu cài đặt | **phát hành 26/10/2026**, chờ chủ dự án cài thử trên một course |
| **FIT-CNL 1.8.0** | Đọc API (mục 6); sửa *Sắp tới hạn 7 ngày* chưa tính bài nộp theo nhóm của Moodle; TL45 A15-2 ghi ngoại lệ, A15-4 thêm *nộp chung* | thiết kế xong **TL59** (kho `fit-cnl`, 31/10/2026), chờ phiên viết mã |
| **Fit-Nhóm 0.2** | mục 10 dưới đây | thiết kế xong (v0.4), chờ phiên viết mã |
| Fit-Nhóm 0.3 (khi cần) | Ứng dụng điện thoại (mục 11) | sau khi thử 0.1 trên máy thật |

Cài thử: máy phát triển (Moodle 4.5 dev) — **không đụng ba Moodle thật** khi chủ dự án chưa yêu cầu.

## 10. Bản 0.2 — thiết kế (31/10/2026)

Ba việc, đều **không thêm việc cho sinh viên và giảng viên**, không thư nhắc, không tác vụ đêm (Q7 giữ):

### 10.1 Trang *Thành viên nhóm* cho giảng viên trong course

- Vào từ: mục **Thành viên nhóm (Fit-Nhóm)** trong menu *Thêm* của course (hàm `local_fitnhom_extend_navigation_course` trong `lib.php` thêm nút vào điều hướng course, Moodle 4.x tự đưa vào thanh điều hướng phụ) và một liên kết ở đầu cột *Thành viên nhóm* của bảng chấm. Trang `local/fitnhom/course.php?id=<course>`. Quyền: `mod/assign:grade` ở ngữ cảnh course (giảng viên, trợ giảng); sinh viên không thấy mục, vào thẳng địa chỉ thì bị từ chối.
- Nội dung: một bảng **bài tập × tình hình** của course đang áp dụng Fit-Nhóm: tên bài, hạn, số bài nộp, *một mình*, *bài nhóm*, số bạn được khai, *Không phải em*, **chưa khai** (bài nộp qua ứng dụng chưa trả lời), số em **không tự nộp nhưng có trong bài nhóm** (= số em Fit-Nhóm "cứu"). Bấm tên bài → bảng chi tiết của bài: mỗi bài nộp một dòng (người nộp, lúc nộp, câu khai, các bạn — gạch ngang bạn đã bấm *Không phải em*, cờ *cùng nhóm với bài nộp của …* theo mục 3a).
- Nút **Tải Excel** (một sheet tổng hợp + một sheet chi tiết; MSSV – Họ tên; chống chèn công thức). Không nút nhắc, không sửa câu khai hộ (giảng viên nộp thay thì sửa ở trang nộp bài như 0.1).
- Course không áp dụng hay chế độ Tắt: trang ghi lý do, không bảng.

### 10.2 Nhận diện course lớp chủ nhiệm theo định dạng course

- `phamvi::course_ap_dung` thêm điều kiện: course có `format = 'fitcnl'` (định dạng *Lớp chủ nhiệm* của FIT-CNL) → **không áp dụng**, không cần mẫu tên. Mẫu `FITCNL:%` vẫn giữ làm lưới thứ hai. Cấu hình mới **Định dạng course không áp dụng** (nhiều dòng, mặc định `fitcnl`) để Trường có định dạng course hành chính khác cũng loại được.
- Lý do: tên rút gọn do Giáo vụ đặt có thể đổi; định dạng course thì không.

### 10.3 Quản trị: tình hình theo course, dấu hiệu bất thường

- Trang *Tình hình* thêm bảng **theo course** (50 course nhiều bài nộp nhất từ lúc bật): bài nộp, đã khai, chưa khai, *Không phải em*, tỉ lệ bài nhóm; lọc theo danh mục; tải Excel. Không tên sinh viên.
- Khối **Cần xem** (chỉ quản trị): (a) người nộp có ≥ *n* (cấu hình, mặc định 3) lần bị bạn bấm *Không phải em* trong 60 ngày — có thể khai bừa; (b) em được khai trong ≥ *m* (mặc định 5) bài nộp của **những người khác nhau** trong một bài tập — dấu hiệu khai hộ hàng loạt. Chỉ liệt kê course và bài tập, bấm vào mới hiện tên (quyền admin); Fit-Nhóm **không** tự làm gì với các dòng này (Q — không xét ai đóng góp bao nhiêu, mục 8).
- Nút **Quét ngay** ghi thêm *lần quét trước* và số bài tìm được.

### 10.4 Hợp đồng và lặt vặt

- `api::cua_nguoi()`, `api::ap_dung()` (mục 6), bài kiểm `hopdong_test` thêm hai hàm; `HOPDONG` giữ 1.
- `khai::luu` bọc giao dịch (0.1 ghi hai bảng không giao dịch — kiểm lại); sự kiện `khai_luu` thêm `other.loai`, không tên bạn (giữ).
- Chuỗi en rà lại; privacy xuất thêm bảng `cho`; README, phiếu cài đặt 0.2 (nâng cấp từ 0.1: một bước `upgrade.php` cho cấu hình định dạng, không đổi bảng).

### 10.5 Kiểm thử 0.2

`tests/ban02_test.php`: trang giảng viên đúng quyền (sinh viên 403, trợ giảng vào được); số liệu theo bài khớp dữ liệu dựng; course `format = fitcnl` không áp dụng dù tên không khớp mẫu; `cua_nguoi` trả đúng và lọc *Không phải em*; dấu hiệu bất thường tính đúng ngưỡng; Excel mở được (openpyxl trong `dev/walk_02.py`). `dev/walk_02.py` chụp 6 ảnh vào `docs/anh/0.2/`.

## 11. Bản 0.3 — ứng dụng Moodle trên điện thoại (thiết kế khung, làm khi đã thử máy thật)

- Ứng dụng Moodle vẽ plugin nộp bài của bên thứ ba qua **site plugin**: `assignsubmission_fitnhom/db/mobile.php` khai `$addons['assignsubmission_fitnhom']` với handler `AddonModAssignSubmissionDelegate`, `method` trả mẫu Ionic (câu hỏi hai lựa chọn, ô chọn bạn từ danh sách ứng viên do dịch vụ web `local_fitnhom_ung_vien` trả), `offlinefunctions` để trống (không nộp ngoại tuyến). Khi lưu, ứng dụng gửi dữ liệu plugin theo `mod_assign_save_submission` → `locallib::save` nhận như trên web.
- Cần: dịch vụ web `local_fitnhom_ung_vien` (ứng viên của một bài, bỏ chính mình, theo quyền `mod/assign:submit`), `local_fitnhom_goi_y`; kiểm bằng ứng dụng thật trên Android / iOS (máy phát triển không có).
- Khi 0.3 xong, hộp nổi (mục 5) vẫn giữ cho các trường hợp còn lọt (dịch vụ web khác, bản ứng dụng cũ).

## 12. Câu mở phiên cho bản 0.2 (dán vào phiên viết mã)

> Bạn tiếp tục phát triển **Fit-Nhóm** — hai plugin Moodle 4.5 `local_fitnhom` + `assignsubmission_fitnhom` (kho GitHub `lnthanhhcmus/fitnhom`, nhánh `main`, bản hiện hành 0.1, version 2026102700). Việc của phiên này: **bản 0.2 theo `docs/TL54_Fit-Nhom_Thiet-ke_v0.4.md` mục 10** (đọc trọn TL54, nhất là mục 0 — Q1–Q7, mục 6 — hợp đồng). Đọc thêm `CLAUDE.md`, `docs/so-tay.md`, `docs/nhat-ky.md`. Máy: `bash dev/sync.sh` cần Moodle phát triển của FIT-CNL (`fit-cnl/dev/1.0/setup.sh` dựng, hai kho đặt cạnh nhau); `bash dev/test.sh` 14 bài đạt là máy đúng. Quy tắc: (1) giữ hợp đồng — không đổi `dang_bat()`, `thanhvien()`, `HOPDONG` = 1, chỉ thêm `cua_nguoi()`, `ap_dung()`; `tests/hopdong_test.php` phải đạt; (2) không thêm việc cho sinh viên, giảng viên; không thư nhắc, không tác vụ đêm (Q7); câu khai không phải bài nộp; (3) không phụ thuộc FIT-CNL (không gọi lớp, không đọc bảng của FIT-CNL — nhận diện lớp chủ nhiệm bằng `course.format`); (4) bảng có dữ liệu người vào privacy; mô tả sự kiện không chép tên bạn được khai; (5) `tests/ban02_test.php`, PHPUnit đạt, `bash dev/lint.sh` sạch, `dev/walk_02.py`; (6) cuối bản: `version.php` + `upgrade.php`, `docs/so-tay.md`, `nhat-ky.md`, `lich-su.md`, phiếu cài đặt 0.2 (`docs/huong-dan/`), gói `bash dev/package.sh` + SHA-256, bản Word (`dev/mkdocx.py`) gửi tôi và chép lên Drive `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\` (`Thiet-ke\`, `Cai-dat\`, `Anh\0.2\`, `Huong-dan\`); commit tiếng Việt, hai dòng ký theo nhắc của môi trường, `git fetch` trước `git push origin main`; (7) không đụng ba Moodle thật; **xong bản thì dừng**, gửi gói và phiếu, nêu điều gì khác TL54 (nếu có).
