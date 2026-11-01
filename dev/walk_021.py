# -*- coding: utf-8 -*-
"""Đi thử Fit-Nhóm 0.2.1 / 0.2.2 bằng trình duyệt trên Moodle phát triển, chụp ảnh vào docs/anh/0.2.2/ (góp ý chủ dự án 01/11/2026).

Kịch bản: course FN-021 (bài tập văn bản trực tuyến), năm sinh viên A–E, một giảng viên; chế độ Bật toàn trang.
(1) A mở trang nộp: nhãn "Thành viên nhóm", hai nút, không câu giải thích (0.2.2); bấm Lưu khi chưa chọn → dòng đỏ.
(2) Dữ liệu dựng bằng PHP: A nộp bài nhóm liệt kê B, C, E; C báo không cùng làm; B nộp bản cập nhật một giờ sau (liệt kê A);
    D làm một mình. E mở trang nộp: khung xanh "Bạn A đã nộp …", ô chọn bạn ghi cạnh tên A "đã nộp lúc …, có tên em trong
    nhóm"; cạnh tên B, D (đã nộp nhưng không liệt kê E) không ghi gì.
(3) Giảng viên *Tải toàn bộ bài nộp*: gói có Thanh-vien-nhom.txt cho A, B, D (không cho E — chưa nộp); tệp của A ghi C báo không
    cùng làm, "Nhóm có bản nộp mới hơn: bài của B"; tệp của B "bản nộp mới nhất".
(4) Thư cho bạn được liệt kê: người gửi FIT-MOODLE, "Chào em,", "liệt kê", dòng ký "Hệ thống FIT-MOODLE" (dựng từ chuỗi và
    người gửi thật của plugin — máy phát triển không gửi thư).
(5) Cài đặt có ô *Tên người gửi thông báo* = FIT-MOODLE.
Dữ liệu thử tạo rồi xóa khi xong; cấu hình trả về như trước.

Dùng:  python3 dev/walk_021.py
"""
import html
import io
import json
import os
import re
import sys
import urllib.parse
import zipfile

from playwright.sync_api import sync_playwright

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from walk_02 import ADMIN, BASE, KQ, MK, bo_tour, go, kiem, login, php  # noqa: E402

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.2.2")


def shot(pg, name, sel=None):
    os.makedirs(OUT, exist_ok=True)
    if sel:
        pg.locator(sel).first.screenshot(path=os.path.join(OUT, name))
    else:
        pg.screenshot(path=os.path.join(OUT, name), full_page=True)
    print("chụp", name)


DUNG = r"""
\core\session\manager::set_user(get_admin());
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/local/fitnhom/lib.php');
$g = new testing_data_generator();
$cfg = ['chedo' => get_config('local_fitnhom', 'chedo'), 'khoathu' => get_config('local_fitnhom', 'khoathu'),
  'nguoigui' => get_config('local_fitnhom', 'nguoigui'), 'lang' => $DB->get_field('user', 'lang', ['id' => get_admin()->id])];
$DB->set_field('user', 'lang', 'vi', ['id' => get_admin()->id]);
$kq = ['cfg' => $cfg];
$c = $g->create_course(['shortname' => 'FN-021', 'fullname' => 'Nhập môn lập trình (thử Fit-Nhóm)']);
$kq['c'] = (int) $c->id;
$ten = [['An', 'Nguyễn Văn'], ['Bình', 'Trần Thị'], ['Cường', 'Lê Văn'], ['Dung', 'Phạm Thị'], ['Em', 'Hoàng Văn']];
foreach ($ten as $i => [$f, $l]) {
  $u = $g->create_user(['username' => 'fn21sv' . ($i + 1), 'password' => '%s', 'firstname' => $f, 'lastname' => $l,
    'idnumber' => '2312' . (200 + $i), 'email' => 'fn21sv' . ($i + 1) . '@example.com']);
  $g->enrol_user($u->id, $c->id, 'student');
  $kq['u'][] = (int) $u->id;
}
$t = $g->create_user(['username' => 'fn21gv', 'password' => '%s', 'firstname' => 'Hà', 'lastname' => 'Giảng viên']);
$g->enrol_user($t->id, $c->id, 'editingteacher');
set_config('chedo', 'BAT', 'local_fitnhom');
set_config('nguoigui', 'FIT-MOODLE', 'local_fitnhom');
local_fitnhom_chedo_doi('chedo');
\local_fitnhom\phamvi::xoa_dem();
$x = $g->get_plugin_generator('mod_assign')->create_instance(['course' => $c->id, 'name' => 'Đồ án nhóm 1',
  'assignsubmission_onlinetext_enabled' => 1, 'submissiondrafts' => 0, 'duedate' => time() + 3 * 86400]);
$kq['cm'] = (int) $x->cmid;
echo json_encode($kq);
""" % (MK, MK)

NOP = r"""
function fn_nop($cmid, $uid, $khai) {
  $u = core_user::get_user($uid); \core\session\manager::set_user($u);
  [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'assign');
  $a = new assign(context_module::instance($cm->id), $cm, $course); $n = [];
  $a->save_submission((object) (['userid' => $u->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
    'text' => '<p>Bài nộp của ' . fullname($u) . '</p>', 'format' => FORMAT_HTML]] + $khai), $n);
  return $a->get_user_submission($uid, false);
}
require_once($CFG->dirroot . '/mod/assign/locallib.php');
$d = json_decode('%s', true); $u = $d['u']; $cm = $d['cm'];
$sa = fn_nop($cm, $u[0], ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$u[1], $u[2], $u[4]]]);
$k = \local_fitnhom\khai::cua_bai_nop((int) $sa->id);
\local_fitnhom\khai::tu_choi((int) $k->tv[$u[2]]->id, $u[2], false, time());
$sb = fn_nop($cm, $u[1], ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$u[0]]]);
$DB->set_field('assign_submission', 'timemodified', (int) $sa->timemodified + 3600, ['id' => $sb->id]);
fn_nop($cm, $u[3], ['fitnhom_loai' => 'MOT']);
\core\session\manager::set_user(get_admin());
echo 'ok';
"""

THU = r"""
$d = json_decode('%s', true); $u = $d['u'];
$nop = core_user::get_user($u[0]); $nhan = core_user::get_user($u[4]);
force_current_language('vi');
$a = (object) ['ban' => fullname($nop), 'bai' => 'Đồ án nhóm 1', 'mon' => 'Nhập môn lập trình (thử Fit-Nhóm)',
  'url' => (new moodle_url('/local/fitnhom/tuchoi.php', ['id' => 1]))->out(false),
  'urlbai' => (new moodle_url('/mod/assign/view.php', ['id' => $d['cm']]))->out(false),
  'ky' => \local_fitnhom\thongbao::ky_ten()];
$tu = trim(fullname(\local_fitnhom\thongbao::nguoi_gui()));
if ($CFG->emailfromvia != EMAIL_VIA_NEVER) {
  $tu = get_string('emailvia', 'core', (object) ['name' => $tu, 'siteshortname' => format_string($SITE->shortname)]);
}
echo json_encode(['tu' => $tu, 'den' => fullname($nhan), 'tieude' => get_string('tb_duockhai_tieude', 'local_fitnhom', $a),
  'html' => get_string('tb_duockhai_html', 'local_fitnhom', $a), 'chu' => get_string('tb_duockhai', 'local_fitnhom', $a)]);
"""

XOA = r"""
\core\session\manager::set_user(get_admin());
foreach (['FN-021', 'FN-DO'] as $sn) {
  if ($c = $DB->get_record('course', ['shortname' => $sn])) { delete_course($c, false); }
}
foreach ($DB->get_records_select('user', "username LIKE 'fn21%' OR username LIKE 'fndo%'") as $u) { delete_user($u); }
"""

KHUNG = """<html><head><meta charset="utf-8"><style>
body{font-family:"DejaVu Sans",Arial,Helvetica,sans-serif;margin:0;background:#fff;color:#202124}
#khung{padding:16px;width:820px}
.hop{border:1px solid #c7c7c7;border-radius:8px;padding:14px 18px}
.dau{font-size:13px;color:#5f6368;margin-bottom:10px} .dau b{color:#202124}
h1{font-size:20px;font-weight:normal;margin:0 0 12px} pre{font-family:"DejaVu Sans",Arial,sans-serif;font-size:13px;
margin:0;white-space:pre-wrap;line-height:1.45} .cay{font-size:13px;line-height:1.6} .moi{background:#fff3cd}
.nhan{font-size:12px;color:#5f6368;margin-top:10px}
</style></head><body><div id="khung">%s</div></body></html>"""


def ten_zip(cd):
    m = re.search(r"filename\*=UTF-8''([^;]+)", cd)
    if m:
        return urllib.parse.unquote(m.group(1))
    m = re.search(r'filename="?([^";]+)', cd)
    return m.group(1) if m else "?"


def chay():
    php(XOA)
    d = json.loads(php(DUNG).splitlines()[-1])
    cfg = d["cfg"]
    u = d["u"]
    cm = d["cm"]
    try:
        with sync_playwright() as p:
            br = p.chromium.launch()
            ctx = br.new_context(viewport={"width": 1200, "height": 900}, accept_downloads=True)
            pg = ctx.new_page()
            # (1) A: câu mới, chưa chọn mà bấm Lưu → dòng đỏ.
            login(pg, "fn21sv1", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % cm)
            bo_tour(pg)
            pg.wait_for_timeout(800)
            nhom = pg.locator("#fgroup_id_fitnhom_loai")
            kiem("Thành viên nhóm" in pg.inner_text("#fgroup_id_fitnhom_loai")
                 and pg.locator("#id_fitnhom_loai_NHOM").locator("xpath=..").inner_text().strip() == "Bài nhóm"
                 and "Bắt buộc chọn một" not in pg.content() and "các bạn được tính đã nộp" not in pg.content(),
                 "(1) Nhãn Thành viên nhóm, hai nút Em làm một mình / Bài nhóm, không câu giải thích (0.2.2)")
            pg.locator("#id_submitbutton").click(force=True)
            pg.wait_for_load_state("networkidle")
            pg.wait_for_timeout(600)
            bo_tour(pg)
            loi = pg.locator("#fgroup_id_error_fitnhom_loai")
            kiem(loi.count() == 1 and loi.is_visible() and "Em chọn" in loi.inner_text(),
                 "(1) Chưa chọn mà bấm Lưu: dòng đỏ dưới hai nút")
            pg.evaluate("() => { const f = document.querySelector('#fgroup_id_fitnhom_loai');"
                        " f.scrollIntoView({block: 'start'}); window.scrollBy(0, -20); }")
            box = pg.locator("#fgroup_id_fitnhom_loai").bounding_box()
            box2 = pg.locator("#fitem_id_fitnhom_tv").bounding_box()
            pg.screenshot(path=os.path.join(OUT, "01-chua-chon-dong-do.png"),
                          clip={"x": box["x"] - 10, "y": box["y"] - 10, "width": 980,
                                "height": box2["y"] + box2["height"] - box["y"] + 20})
            print("chụp 01-chua-chon-dong-do.png")
            kiem(nhom.count() == 1, "(1) Hai nút nằm trong một nhóm (fgroup_id_fitnhom_loai)")

            # (2) Dữ liệu nộp; E mở trang nộp.
            kiem(php(NOP % json.dumps(d).replace("'", "\\'")).endswith("ok"), "(2) Dựng bài nộp A, B (bản cập nhật), D")
            login(pg, "fn21sv5", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % cm)
            bo_tour(pg)
            pg.wait_for_timeout(800)
            kiem("đã nộp bài này" in pg.content() and "có tên em trong nhóm" in pg.content(),
                 "(2) E: khung xanh 'Bạn A đã nộp …' và cạnh tên A 'có tên em trong nhóm'")
            nhan = pg.evaluate("() => Object.fromEntries([...document.querySelectorAll('#id_fitnhom_tv option')]"
                               ".map(o => [o.value, o.textContent.trim()]))")
            kiem("có tên em trong nhóm" in nhan.get(str(u[0]), "")
                 and "đã nộp" not in nhan.get(str(u[1]), "") and "đã nộp" not in nhan.get(str(u[3]), ""),
                 "(2) B, D đã nộp nhưng không liệt kê E: không ghi gì cạnh tên")
            pg.locator("#fitem_id_fitnhom_tv .form-autocomplete-downarrow").first.click()
            pg.wait_for_timeout(600)
            box = pg.locator("#fitem_id_fitnhom_goiy").bounding_box()
            sug = pg.locator("#fitem_id_fitnhom_tv .form-autocomplete-suggestions").bounding_box()
            pg.screenshot(path=os.path.join(OUT, "02-ban-da-nop-co-ten-em.png"),
                          clip={"x": box["x"] - 10, "y": box["y"] - 10, "width": 980,
                                "height": sug["y"] + sug["height"] - box["y"] + 20})
            print("chụp 02-ban-da-nop-co-ten-em.png")

            # (3) Giảng viên tải toàn bộ bài nộp.
            login(pg, "fn21gv", MK)
            r = ctx.request.get(BASE + "/mod/assign/view.php?id=%d&action=downloadall" % cm)
            z = zipfile.ZipFile(io.BytesIO(r.body()))
            ten = sorted(z.namelist())
            ghichu = {n: z.read(n).decode("utf-8-sig") for n in ten if n.endswith("Thanh-vien-nhom.txt")}
            kiem(len(ghichu) == 3 and all("_assignsubmission_fitnhom/" in n for n in ghichu),
                 "(3) Gói tải có Thanh-vien-nhom.txt cho A, B, D (thư mục …_assignsubmission_fitnhom)")
            ta = next((v for n, v in ghichu.items() if n.startswith("Nguyễn Văn An") or "An" in n.split("_")[0]), "")
            tb = next((v for n, v in ghichu.items() if "Bình" in n.split("_")[0]), "")
            kiem("báo không cùng làm" in ta and "bản nộp mới hơn" in ta and "Bình" in ta,
                 "(3) Tệp của A: C báo không cùng làm, nhóm có bản mới hơn của B")
            kiem("bản nộp mới nhất" in tb, "(3) Tệp của B: bản nộp mới nhất của nhóm")
            cay = "".join('<div%s>%s</div>' % (' class="moi"' if n in ghichu else "", html.escape(n)) for n in ten)
            ten_a = next(n for n in ghichu if ghichu[n] == ta)
            noidung = ('<h1>Tải toàn bộ bài nộp — %s</h1><div class="hop"><div class="dau">Các tệp trong gói (dòng tô vàng: '
                       'tệp ghi chú Fit-Nhóm)</div><div class="cay">%s</div></div><br><div class="hop"><div class="dau">'
                       'Mở <b>%s</b></div><pre>%s</pre></div>'
                       % (html.escape(ten_zip(r.headers.get("content-disposition", ""))),
                          cay, html.escape(ten_a), html.escape(ta)))
            pg.set_content(KHUNG % noidung)
            shot(pg, "03-tai-toan-bo-bai-nop.png", "#khung")

            # (4) Thư cho bạn được liệt kê.
            t = json.loads(php(THU % json.dumps(d).replace("'", "\\'")).splitlines()[-1])
            kiem(t["tu"].startswith("FIT-MOODLE") and "Chào em," in t["chu"] and "liệt kê" in t["tieude"]
                 and "Hệ thống FIT-MOODLE" in t["chu"] and " khai " not in t["chu"],
                 "(4) Thư: người gửi FIT-MOODLE, Chào em, liệt kê, ký Hệ thống FIT-MOODLE")
            pg.set_content(KHUNG % ('<div class="hop"><h1>%s</h1><div class="dau"><b>%s</b> &lt;địa chỉ không trả lời của '
                                    'trang&gt;<br>đến %s</div>%s<div class="nhan">Bản xem thư dựng từ chuỗi và người gửi '
                                    'thật của plugin (máy phát triển không gửi thư).</div></div>'
                                    % (html.escape(t["tieude"]), html.escape(t["tu"]), html.escape(t["den"]), t["html"])))
            shot(pg, "04-thu-ban-duoc-liet-ke.png", "#khung")

            # (5) Cài đặt: tên người gửi.
            login(pg, *ADMIN)
            go(pg, "/admin/settings.php?section=local_fitnhom")
            o = pg.locator("#id_s_local_fitnhom_nguoigui")
            kiem(o.count() == 1 and o.input_value() == "FIT-MOODLE", "(5) Cài đặt: Tên người gửi thông báo = FIT-MOODLE")
            shot(pg, "05-cai-dat-nguoi-gui.png", "#admin-nguoigui")
            br.close()
    finally:
        php(XOA)
        php("set_config('chedo', %s, 'local_fitnhom'); set_config('khoathu', %s, 'local_fitnhom');"
            " set_config('nguoigui', %s, 'local_fitnhom');"
            % (json.dumps(cfg["chedo"] or "TAT"), json.dumps(cfg["khoathu"] or ""), json.dumps(cfg["nguoigui"] or "FIT-MOODLE")))
        php("$DB->set_field('user', 'lang', %s, ['id' => get_admin()->id]);" % json.dumps(cfg["lang"]))
        php("\\local_fitnhom\\cho::xoa_dem(); purge_all_caches();")
        print("đã xóa dữ liệu thử")


if __name__ == "__main__":
    os.makedirs(OUT, exist_ok=True)
    chay()
    dat = sum(KQ.values())
    print("\n%d/%d đạt" % (dat, len(KQ)))
    sys.exit(0 if dat == len(KQ) else 1)
