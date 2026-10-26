# TL54 — Fit-Nhóm: khai thành viên nhóm khi nộp bài trên Moodle (thiết kế v0.2)

*v0.2 · 26/10/2026 tối — thêm mục 3a (hai bạn cùng nhóm đều nộp, nộp bản cập nhật; chủ dự án hỏi 20:00). Bản v0.1 ở `docs/ban-cu/`.*

*26/10/2026 · bản để chủ dự án duyệt trước khi viết mã · kho `github.com/lnthanhhcmus/fitnhom` · tài liệu trên Drive: `Thiết kế hệ thống hỗ trợ CNL\Fit-Nhom\`*

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
- **Q4** (18:34, 18:35) **Khai bổ sung** bài đã nộp từ đầu học kỳ: chỉ người nộp; thư có danh sách liên kết cần khai; **hộp nổi** liệt kê bài để bấm khai ngay; quét mỗi đêm, chưa khai **4 ngày** thì nhắc lại; xong hết thì **thôi chạy mỗi đêm**.
- **Q5** (18:46) **Thử trước trên một lớp** chọn trước rồi mới bật đồng loạt; có nút **Quét ngay** thay vì chờ qua đêm.
- **Q6** (18:46) Tên *Fit-Nhóm*; trong plugin ghi rõ mục đích để người sau hiểu.

## 1. Thành phần

Moodle chỉ cho thêm ô vào **trang nộp bài** qua một *plugin nộp bài* (subplugin của Assignment); plugin cục bộ không chen vào biểu mẫu đó được. Nên Fit-Nhóm gồm hai phần, chung một kho, chung số phiên bản:

| Thành phần | Thư mục cài | Việc |
|---|---|---|
| **`local_fitnhom`** (chính) | `local/fitnhom` | Bảng dữ liệu, trang quản trị, chế độ Tắt / Thử nghiệm / Bật, khai bổ sung, hộp nổi, thông báo, tác vụ, API cho plugin khác |
| **`assignsubmission_fitnhom`** (mỏng) | `mod/assign/submission/fitnhom` | Vẽ câu hỏi trên trang nộp bài, kiểm bắt buộc, chuyển dữ liệu cho `local_fitnhom` lưu; cột *Thành viên nhóm* ở bảng chấm |

`assignsubmission_fitnhom` phụ thuộc `local_fitnhom` (khai ở `version.php`). Moodle 4.5, PHP 8.1–8.3 như FIT-CNL.

## 2. Trang nộp bài

Câu hỏi **bắt buộc**, không chọn sẵn:

> **Bài này em làm thế nào?** *(để các bạn cùng nhóm không bị tính là chưa nộp)*
> ○ Em làm một mình  ○ Bài nhóm — các bạn cùng làm: [ô gõ-chọn *MSSV – Họ tên*]

- Ô gõ-chọn chỉ có **người được nộp bài trong course đó** (quyền `mod/assign:submit`), bỏ chính người nộp; tối đa *n* bạn (Cấu hình, mặc định 10). Không gõ tay tên.
- Chưa trả lời thì **không lưu / không nộp được** (luật bắt buộc của biểu mẫu + `precheck_submission` khi bài yêu cầu bấm *Nộp để chấm*).
- Câu trả lời **không tính là bài nộp**: chỉ khai mà không có tệp / văn bản thì Moodle vẫn coi là chưa nộp (`is_empty` luôn đúng).
- Sửa bài nộp sau đó: câu trả lời cũ điền sẵn, sửa được.
- **Giảng viên nộp thay** (sửa bài nộp của sinh viên): câu hỏi **không bắt buộc** — không làm giảng viên thêm việc.
- Bài đã bật *Nộp theo nhóm* của Moodle: **không hỏi** (Moodle đã biết nhóm).
- **Course không áp dụng** (mục 4) và chế độ Tắt: không hỏi.

Với giảng viên: **không có ô bật / tắt** trong cài đặt bài tập (plugin "không cấu hình được"; bật hay không do `local_fitnhom` quyết theo chế độ). *Tải toàn bộ bài nộp* **không đổi** (plugin không có tệp). Sổ điểm không đổi. Bảng chấm có thêm cột **Thành viên nhóm** (*Một mình* / tên các bạn; bạn đã bấm *Không phải em* ghi gạch ngang).

## 3. Thành viên được khai tên

- Thông báo Moodle (chuông, và thư theo cài đặt thông báo của em): *"Bạn A khai em cùng làm bài X (môn Y). Nếu không đúng, bấm Không phải em."*
- **Không phải em** (trang của Fit-Nhóm, phải đăng nhập, chỉ chính em bấm được): em đó không được tính; người nộp nhận thông báo; giảng viên thấy dấu gạch ở bảng chấm. Bấm nhầm thì *Hoàn tác* trong 7 ngày.

## 3a. Hai bạn cùng nhóm đều nộp, nộp bản cập nhật

Bài không bật *Nộp theo nhóm* thì Moodle giữ **bài nộp riêng của từng em**: B nộp không đè bài của A, giảng viên vẫn tải được cả hai như mọi khi. Fit-Nhóm gắn câu khai vào **từng bài nộp**, nên:

- **B mở trang nộp khi A đã nộp và khai tên B**: đầu câu hỏi ghi *"Bạn A đã nộp bài này lúc 20:15 25/10 và khai em cùng nhóm — em không cần nộp lại. Nếu nộp bản cập nhật, chọn các bạn cùng nhóm bên dưới."*; ô *Bài nhóm* **điền sẵn** A và các bạn A đã khai (bỏ chính B) để hai bản khai khớp nhau. B vẫn sửa được.
- **Cả hai cùng nộp, khai khác nhau**: lưu cả hai, không bản nào thắng bản nào; giảng viên thấy ở cột *Thành viên nhóm* của bảng chấm dòng **"Cùng nhóm với bài nộp của: A (20:15 25/10)"** để biết đâu là bản mới hơn và chấm theo cách của mình. Fit-Nhóm không gộp, không xóa bài nộp nào.
- **Tính đã nộp cho một em** (API mục 6, FIT-CNL dùng): lấy **lúc sớm nhất** trong các nguồn — bài nộp của chính em, hoặc bài nộp của bạn khác có khai em (em chưa bấm *Không phải em*). Ví dụ A nộp đúng hạn có khai B, sau hạn B nộp bản cập nhật → B vẫn tính **đúng hạn** theo bài của A. `thanhvien()` vì vậy trả **mọi** bạn được khai, kể cả bạn cũng có bài nộp riêng; FIT-CNL tự lấy lúc sớm nhất.
- **B đã nộp (tự khai) thì không bị hỏi bổ sung** dù A cũng khai B; khai bổ sung chỉ hỏi bài nộp chưa có câu khai.
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
| **Khai bổ sung** | Bật / tắt; *bài có hạn từ ngày*; *kết thúc đợt ngày* | tắt; đầu HK1; cuối HK1 |
| Nhắc lại sau / tối đa | ngày / lần | **4** / 3 |
| Nút **Quét ngay** | Tìm ngay các bài đã nộp chưa khai (theo chế độ, phạm vi, đợt) — không chờ đêm | |
| Nút **Gửi nhắc ngay** | Gửi thư / thông báo nhắc cho người đang chờ (bỏ qua chu kỳ 4 ngày) — để thử | |
| Bảng tình hình | Số bài chưa khai, đã khai (một mình / nhóm), số *Không phải em*, lần quét / nhắc gần nhất, tác vụ đêm đang chạy hay đã nghỉ | |

Bài ở course **không áp dụng** không hỏi, không khai bổ sung. Ở course áp dụng mà bài không phải bài nhóm thì sinh viên **bấm "Em làm một mình"** — một cú bấm.

Thử nghiệm: chọn **một course**, chế độ *Thử nghiệm* → chỉ course đó có câu hỏi, khai bổ sung, hộp nổi, thông báo. Ổn thì chuyển *Bật toàn trang*.

## 5. Khai bổ sung bài cũ

**Ai, bài nào:** bài nộp đã gửi (`submitted`) ở course áp dụng, bài có hạn từ *ngày bắt đầu đợt*, chưa có câu khai; chỉ hỏi **người nộp**. Cũng gồm bài nộp qua **ứng dụng Moodle trên điện thoại** hay dịch vụ web (không có ô khai).

**Hộp nổi:** vào Moodle mà còn bài chưa khai → **hộp nổi** (một lần mỗi ngày) *"Em còn n bài cần khai thành viên"*: mỗi dòng một bài (môn, tên bài, ngày nộp) với **Một mình** / **Bài nhóm…** (mở ô gõ-chọn) / **Lưu** ngay trong hộp; khai xong dòng nào dòng đó biến mất, hết thì hộp đóng. **Để sau** → mai hiện lại; dải nhỏ trên đầu trang mở lại hộp lúc nào cũng được.

**Thư nhắc** (qua hệ thông báo Moodle — thư + chuông): danh sách bài, **mỗi bài một liên kết** mở thẳng trang khai bài đó; cuối thư **Khai tất cả n bài**. Liên kết chỉ dẫn vào Moodle (phải đăng nhập) — thư không chứa mã tự xác nhận. Lần đầu khi quét thấy; nhắc lại sau **4 ngày** nếu còn; **tối đa 3 lần**.

**Tự nghỉ:** tác vụ đêm (01:40) chỉ chạy khi còn việc. Khi **hết bài chờ** hoặc **qua ngày kết thúc đợt**, tác vụ **tự tắt**. Bài nộp mới qua ứng dụng (không khai được) được bắt **ngay lúc nộp** (lắng nghe sự kiện nộp bài của Moodle): ghi vào danh sách chờ và **bật lại** tác vụ. Nhờ vậy máy chủ không làm gì mỗi đêm khi không có việc.

## 6. Hợp đồng với plugin khác (FIT-CNL)

Plugin khác **không đọc bảng** của Fit-Nhóm, chỉ gọi:

```php
\local_fitnhom\api::HOPDONG        // int — phiên bản hợp đồng, hiện 1
\local_fitnhom\api::dang_bat(): bool                  // chế độ khác Tắt
\local_fitnhom\api::thanhvien(int $assignid): array   // [userid => ['nguoinop' => userid, 'luc' => giờ nộp]]
                                                      // các bạn được khai, chưa bấm "Không phải em", không gồm người nộp;
                                                      // một em được nhiều bài khai → lấy bài nộp sớm nhất (mục 3a)
```

**Luật giữ hợp đồng:** đổi bảng / cách lưu bên trong thoải mái; **không đổi** ý nghĩa ba thứ trên trong cùng `HOPDONG`. Muốn đổi thì thêm hàm mới, hoặc tăng `HOPDONG` (khi đó plugin cũ tự bỏ qua). Có bài kiểm giữ hợp đồng.

**Phía FIT-CNL (bản 1.7.54):** chỉ dùng khi đủ cả bốn điều — `local_fitnhom` đã cài **và** đang bật trong danh sách plugin, lớp `\local_fitnhom\api` tồn tại, `HOPDONG` là số FIT-CNL biết (1), lời gọi không ném lỗi (bọc `try/catch \Throwable`). Thiếu một điều: chạy như hiện nay, trang Hoạt động Moodle (quản trị FIT-CNL) ghi một dòng *Fit-Nhóm: không dùng (lý do)*. Bài được tính *nộp chung* hiện **Đã nộp — nộp chung với bạn A**; không còn tính vào *trễ / bỏ lỡ*. FIT-CNL vẫn **chỉ đọc** (A15-2 giữ cho FIT-CNL; ngoại lệ ghi rõ cho Fit-Nhóm).

## 7. Dữ liệu và riêng tư

| Bảng (`local_fitnhom_…`) | Cột chính |
|---|---|
| `khai` | assign, submission, course, người khai, loại (MOT / NHOM), nguồn (FORM / BOSUNG / HOPNOI), thời gian |
| `thanhvien` | khai, userid, trạng thái (KHAI / TUCHOI), lúc từ chối |
| `cho` | submission, người nộp, số lần nhắc, lần nhắc gần nhất |

Không lưu nội dung bài. Khai báo với **Privacy API** của Moodle (xuất / xóa dữ liệu theo người). Xóa course / bài tập → xóa dòng liên quan. Nhật ký Moodle: mỗi thao tác một sự kiện (`khai_luu`, `thanhvien_tuchoi`, `quet_ngay`, `nhac_gui`) — không chép tên người được khai vào phần mô tả.

## 8. Rủi ro đã biết

- **Ứng dụng Moodle trên điện thoại**: ứng dụng không vẽ được câu hỏi của plugin lạ; có thể báo *không sửa được bài nộp trong ứng dụng* → em nộp bằng trình duyệt (cả trên điện thoại). Bản 0.2 có thể thêm phần cho ứng dụng nếu cần. Bài đã nộp qua ứng dụng vẫn được bắt và nhắc khai (mục 5). *Cần thử trên máy thật.*
- **Khai bừa**: kiểm bằng thông báo *Không phải em* + giảng viên thấy ở bảng chấm; Fit-Nhóm không xét ai đóng góp bao nhiêu.
- **Course "không phải môn học"** chưa vào danh sách loại trừ: sinh viên phải bấm *Một mình* — phiền nhỏ; admin thêm mẫu loại trừ.
- **Cập nhật Moodle**: biểu mẫu nộp bài của Assignment đổi ở bản Moodle mới → kiểm lại `assignsubmission_fitnhom` mỗi lần nâng cấp Moodle (ghi ở phiếu cài đặt).

## 9. Kế hoạch bản

| Bản | Nội dung |
|---|---|
| **Fit-Nhóm 0.1** | Hai thành phần; mục 2–5, 7; trang quản trị; Quét ngay / Gửi nhắc ngay; PHPUnit + đi thử trình duyệt; README, hướng dẫn quản trị, trang ngắn cho giảng viên / sinh viên; phiếu cài đặt |
| **FIT-CNL 1.7.54** | Đọc API (mục 6); sửa *Sắp tới hạn 7 ngày* chưa tính bài nộp theo nhóm của Moodle; TL45 A15-2 ghi ngoại lệ, A15-4 thêm *nộp chung* |
| Fit-Nhóm 0.2 (nếu cần) | Ứng dụng điện thoại; báo cáo cho giảng viên |

Cài thử: máy phát triển (Moodle 4.5 dev) — **không đụng ba Moodle thật** khi chủ dự án chưa yêu cầu.
