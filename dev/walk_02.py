# -*- coding: utf-8 -*-
"""Đi thử Fit-Nhóm 0.2 bằng trình duyệt trên Moodle phát triển, chụp ảnh vào docs/anh/0.2/ (TL60 mục 5.10).

Kịch bản: chế độ Bật toàn trang → em A nộp bài nhóm khai B, C, D (dữ liệu dựng bằng PHP, như trang nộp bài 0.1 đã đi thử) → B,
C, D bấm "Không phải em" (A vào khối Cần xem) → E làm một mình, F nộp qua "ứng dụng" (chờ khai) → giảng viên mở menu course thấy
"Thành viên nhóm (Fit-Nhóm)", trang theo bài, chi tiết, tải Excel → trợ giảng (vai teacher) vào được, sinh viên không → course
định dạng fitcnl ghi lý do → admin: Cài đặt có "định dạng course", Tình hình (Theo course, Cần xem, Quét ngay) → F thấy hộp nổi.
Dữ liệu thử tạo rồi xóa khi xong; cấu hình trả về như trước.

Dùng:  python3 dev/walk_02.py
"""
import json
import os
import subprocess
import sys

from playwright.sync_api import sync_playwright

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.2")
BASE = os.environ.get("MOODLE_DEV_URL", "http://127.0.0.1:8080")
MOODLE = os.environ.get("MOODLE_DIR", os.path.expanduser("~/moodle-dev/moodle"))
ADMIN = (os.environ.get("MOODLE_DEV_USER", "admin"), os.environ.get("MOODLE_DEV_PASS", "Admin.dev1"))
MK = "Fitnhom.dev1"  # Mật khẩu TẠM cho tài khoản thử trên máy phát triển — xóa cùng dữ liệu thử.
KQ = {}


def php(code):
    r = subprocess.run(["php", "-r", "define('CLI_SCRIPT', true); require('config.php'); " + code],
                       cwd=MOODLE, capture_output=True, text=True)
    if r.returncode:
        print(r.stdout, r.stderr)
    return r.stdout.strip()


def kiem(dieu, mota):
    print(("ĐẠT  " if dieu else "SAI  ") + mota, flush=True)
    KQ[mota] = bool(dieu)


def login(pg, user, password):
    for _ in range(3):
        pg.context.clear_cookies()
        pg.goto(BASE + "/login/index.php")
        pg.wait_for_load_state("networkidle")
        pg.fill("#username", user)
        pg.fill("#password", password)
        pg.click("#loginbtn")
        pg.wait_for_load_state("networkidle")
        if "/login/" not in pg.url:
            return
    raise RuntimeError("không đăng nhập được " + user)


def go(pg, path):
    pg.goto(BASE + path)
    pg.wait_for_load_state("networkidle")


def bo_tour(pg):
    """Đóng hướng dẫn tham quan (user tour) của Moodle nếu có — nó che menu."""
    pg.wait_for_timeout(500)
    nut = pg.locator("[data-role='end'], button:has-text('End tour'), button:has-text('Skip tour')")
    if nut.count():
        nut.first.click()
        pg.wait_for_timeout(300)
    pg.evaluate("() => document.querySelectorAll('[data-flexitour]').forEach(e => e.remove())")


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
$cfg = ['lang' => $DB->get_field('user', 'lang', ['id' => get_admin()->id]), 'chedo' => get_config('local_fitnhom', 'chedo'),
  'khoathu' => get_config('local_fitnhom', 'khoathu'), 'batdau' => get_config('local_fitnhom', 'batdau')];
$kq = ['cfg' => $cfg];
$c = $g->create_course(['shortname' => 'FN-02-MON', 'fullname' => 'Nhập môn lập trình (thử Fit-Nhóm 0.2)']);
$cnl = $g->create_course(['shortname' => 'Lop 25CTT-thu', 'fullname' => 'Lớp chủ nhiệm (định dạng fitcnl)']);
$DB->set_field('course', 'format', 'fitcnl', ['id' => $cnl->id]);
$kq['c'] = (int) $c->id; $kq['cnl'] = (int) $cnl->id;
$ten = [['An', 'Nguyễn Văn'], ['Bình', 'Trần Thị'], ['Cường', 'Lê Văn'], ['Dung', 'Phạm Thị'], ['Em', 'Hoàng Văn'], ['Phúc', 'Võ Minh']];
foreach ($ten as $i => [$f, $l]) {
  $u = $g->create_user(['username' => 'fnsv' . ($i + 1), 'password' => '%s', 'firstname' => $f, 'lastname' => $l,
    'idnumber' => '2312' . (100 + $i), 'email' => 'fnsv' . ($i + 1) . '@example.com']);
  $g->enrol_user($u->id, $c->id, 'student'); $g->enrol_user($u->id, $cnl->id, 'student');
  $kq['u'][] = (int) $u->id;
}
$t = $g->create_user(['username' => 'fngv', 'password' => '%s', 'firstname' => 'Hà', 'lastname' => 'Giảng viên']);
$g->enrol_user($t->id, $c->id, 'editingteacher'); $g->enrol_user($t->id, $cnl->id, 'editingteacher');
$tg = $g->create_user(['username' => 'fntg', 'password' => '%s', 'firstname' => 'Lan', 'lastname' => 'Trợ giảng']);
$g->enrol_user($tg->id, $c->id, 'teacher');
$DB->set_field('user', 'lang', 'vi', ['id' => get_admin()->id]);
set_config('chedo', 'BAT', 'local_fitnhom');
set_config('loaitru_format', 'fitcnl', 'local_fitnhom');
local_fitnhom_chedo_doi('chedo');
\local_fitnhom\phamvi::xoa_dem();
$pg = $g->get_plugin_generator('mod_assign');
foreach (['Đồ án nhóm 1' => -86400, 'Bài tập tuần 3' => 3 * 86400, '=Bài thử ký tự' => 5 * 86400] as $n => $h) {
  $x = $pg->create_instance(['course' => $c->id, 'name' => $n, 'assignsubmission_onlinetext_enabled' => 1,
    'submissiondrafts' => 0, 'duedate' => time() + $h]);
  $kq['a'][] = (int) $x->cmid;
}
$tm = $pg->create_instance(['course' => $c->id, 'name' => 'Đồ án nộp theo nhóm (Moodle)', 'teamsubmission' => 1,
  'assignsubmission_onlinetext_enabled' => 1, 'submissiondrafts' => 0, 'duedate' => time() + 6 * 86400]);
$kq['tm'] = (int) $tm->cmid;
$pg->create_instance(['course' => $cnl->id, 'name' => 'Phiếu khảo sát lớp', 'assignsubmission_onlinetext_enabled' => 1]);
function fn_nop($cmid, $uid, $khai) {
  $u = core_user::get_user($uid); \core\session\manager::set_user($u);
  [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'assign');
  $a = new assign(context_module::instance($cm->id), $cm, $course); $n = [];
  $a->save_submission((object) (['userid' => $u->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
    'text' => 'Bài nộp', 'format' => FORMAT_HTML]] + $khai), $n);
  return $a->get_user_submission($uid, false);
}
$u = $kq['u'];
// Bài 1: A khai B, C, D; E làm một mình; F nộp qua "ứng dụng" (không câu khai).
fn_nop($kq['a'][0], $u[0], ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$u[1], $u[2], $u[3]]]);
fn_nop($kq['a'][0], $u[4], ['fitnhom_loai' => 'MOT']);
fn_nop($kq['a'][0], $u[5], []);
// Bài 2: B nộp khai A, C (hai bài nhóm cùng nhóm); bài 3: A nộp khai B.
fn_nop($kq['a'][1], $u[1], ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$u[0], $u[2]]]);
fn_nop($kq['a'][1], $u[2], ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$u[0], $u[1]]]);
fn_nop($kq['a'][2], $u[0], ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$u[1]]]);
// Bài Nộp theo nhóm của Moodle: E nộp cho nhóm (Fit-Nhóm không hỏi).
fn_nop($kq['tm'], $u[4], []);
// B, C, D bấm "Không phải em" ở bài 1 của A → A vào khối Cần xem (3 lần trong 60 ngày).
$k = \local_fitnhom\khai::cua_bai_nop((int) $DB->get_field_select('assign_submission', 'id', 'userid = ? AND latest = 1 AND assignment = ?',
  [$u[0], $DB->get_field('course_modules', 'instance', ['id' => $kq['a'][0]])]));
foreach ([1, 2, 3] as $i) { \local_fitnhom\khai::tu_choi((int) $k->tv[$u[$i]]->id, $u[$i], false, time()); }
\core\session\manager::set_user(get_admin());
echo json_encode($kq);
""" % (MK, MK, MK)

XOA = r"""
\core\session\manager::set_user(get_admin());
foreach (['FN-02-MON', 'Lop 25CTT-thu'] as $sn) {
  if ($c = $DB->get_record('course', ['shortname' => $sn])) { delete_course($c, false); }
}
foreach ($DB->get_records_select('user', "username LIKE 'fnsv%' OR username IN ('fngv', 'fntg')") as $u) { delete_user($u); }
"""


def chay():
    php(XOA)
    d = json.loads(php(DUNG).splitlines()[-1])
    cfg = d["cfg"]
    cid = d["c"]
    try:
        with sync_playwright() as p:
            br = p.chromium.launch()
            ctx = br.new_context(viewport={"width": 1366, "height": 900}, accept_downloads=True)
            pg = ctx.new_page()
            # (1) Giảng viên: menu Thêm của course có "Thành viên nhóm (Fit-Nhóm)".
            login(pg, "fngv", MK)
            go(pg, "/course/view.php?id=%d" % cid)
            bo_tour(pg)
            more = pg.locator(".secondary-navigation .moremenu .dropdown-toggle, .secondary-navigation button:has-text('Thêm'), "
                              ".secondary-navigation a:has-text('More')")
            if more.count():
                more.first.click()
                pg.wait_for_timeout(400)
            kiem(pg.locator("a:has-text('Thành viên nhóm (Fit-Nhóm)')").count() >= 1, "(1) Menu course có Thành viên nhóm (Fit-Nhóm)")
            os.makedirs(OUT, exist_ok=True)
            pg.screenshot(path=os.path.join(OUT, "01-menu-course.png"), clip={"x": 0, "y": 0, "width": 1366, "height": 520})
            print("chụp 01-menu-course.png")
            # (2) Trang theo bài: số liệu.
            go(pg, "/local/fitnhom/course.php?id=%d" % cid)
            html = pg.content()
            kiem("local-fitnhom-theobai" in html and "Đồ án nhóm 1" in html and "nộp theo nhóm của Moodle" in html,
                 "(2) Trang Thành viên nhóm: bảng theo bài, bài nộp theo nhóm Moodle ghi riêng")
            hang = pg.locator("table.local-fitnhom-theobai tr:has-text('Bài tập tuần 3') td")
            kiem(hang.nth(8).inner_text().strip() == "1", "(2) Bài 2: B, C cùng nộp, khai A — A được tính nhờ nhóm")
            hang = pg.locator("table.local-fitnhom-theobai tr:has-text('=Bài thử') td")
            kiem(hang.nth(8).inner_text().strip() == "1", "(2) Bài 3: B được tính nhờ nhóm")
            shot(pg, "02-trang-giang-vien.png", "#region-main")
            # (3) Chi tiết bài 1: A có B, C, D gạch ngang; F chưa khai.
            pg.click("table.local-fitnhom-theobai a:has-text('Đồ án nhóm 1')")
            pg.wait_for_load_state("networkidle")
            kiem(pg.locator("table.local-fitnhom-chitiet del").count() == 3 and "Chưa khai" in pg.content(),
                 "(3) Chi tiết: ba bạn gạch ngang (Không phải em), F Chưa khai")
            shot(pg, "03-chi-tiet-bai.png", "#region-main")
            # (4) Tải Excel: hai sheet, ô '=Bài…' là chữ.
            go(pg, "/local/fitnhom/course.php?id=%d" % cid)
            with pg.expect_download() as dl:
                pg.click("button:has-text('Tải Excel')")
            tep = os.path.join(OUT, "04-excel.xlsx")
            os.makedirs(OUT, exist_ok=True)
            dl.value.save_as(tep)
            import openpyxl
            wb = openpyxl.load_workbook(tep)
            o = [r for r in wb.worksheets[0].iter_rows(values_only=True) if r[0] and str(r[0]).startswith("=")]
            kiem(wb.sheetnames == ["Theo bài", "Chi tiết"] and o and wb.worksheets[0].cell(1, 1).data_type == "s",
                 "(4) Excel: hai sheet Theo bài / Chi tiết, tên bài '=…' giữ là chữ")
            bang = "".join("<tr>%s</tr>" % "".join("<td>%s</td>" % ("" if v is None else v) for v in r)
                           for r in wb.worksheets[0].iter_rows(values_only=True))
            pg.set_content("<html><body style='font-family:sans-serif;padding:16px;display:inline-block'><h3>%s — sheet %s</h3>"
                           "<table border=1 cellpadding=6 style='border-collapse:collapse'>%s</table></body></html>"
                           % (dl.value.suggested_filename, wb.sheetnames[0], bang))
            shot(pg, "04-excel.png", "body")
            os.remove(tep)
            # (5) Trợ giảng vào được; sinh viên bị từ chối; course fitcnl ghi lý do.
            login(pg, "fntg", MK)
            go(pg, "/local/fitnhom/course.php?id=%d" % cid)
            kiem("local-fitnhom-theobai" in pg.content(), "(5) Trợ giảng (vai teacher) vào được (Q8)")
            login(pg, "fnsv2", MK)
            go(pg, "/local/fitnhom/course.php?id=%d" % cid)
            kiem("local-fitnhom-theobai" not in pg.content(), "(5) Sinh viên không vào được")
            login(pg, "fngv", MK)
            go(pg, "/local/fitnhom/course.php?id=%d" % d["cnl"])
            kiem("định dạng course loại trừ" in pg.content() and "<table" not in pg.inner_html("#region-main"),
                 "(5) Course định dạng fitcnl: chỉ câu lý do, không bảng")
            # (6) Admin: Cài đặt có định dạng course loại trừ.
            login(pg, *ADMIN)
            go(pg, "/admin/settings.php?section=local_fitnhom")
            kiem(pg.locator("#id_s_local_fitnhom_loaitru_format").count() == 1
                 and pg.locator("#id_s_local_fitnhom_batthuong_tuchoi").count() == 1, "(6) Cài đặt: định dạng loại trừ, ngưỡng Cần xem")
            shot(pg, "05-cai-dat.png", "#adminsettings")
            # (7) Tình hình: Quét ngay, Theo course, Cần xem.
            go(pg, "/local/fitnhom/tinhhinh.php")
            pg.locator("button:has-text('Quét ngay'), input[value='Quét ngay']").first.click()
            pg.wait_for_load_state("networkidle")
            html = pg.content()
            kiem("Đã quét" in html and "đã xem" in html and "FN-02-MON" in html, "(7) Tình hình: Quét ngay (đã xem n bài), Theo course")
            kiem("Cần xem" in html and "Nguyễn Văn An" not in html, "(7) Cần xem có dòng, chưa hiện tên")
            pg.click("#region-main a:has-text('Xem')")
            pg.wait_for_load_state("networkidle")
            kiem("Nguyễn Văn An" in pg.content() or "An Nguyễn Văn" in pg.content(), "(7) Bấm Xem mới hiện tên")
            shot(pg, "06-tinh-hinh.png", "#region-main")
            # (8) F (nộp qua ứng dụng): hộp nổi.
            login(pg, "fnsv6", MK)
            go(pg, "/my/")
            pg.wait_for_timeout(1200)
            modal = pg.locator(".modal-dialog:has-text('cần khai thành viên')")
            kiem(modal.count() == 1 and pg.locator(".local-fitnhom-dai").count() == 1, "(8) F: dải nhắc + hộp nổi")
            shot(pg, "07-hop-noi.png", ".modal-dialog")
            modal.locator("button:has-text('Để sau')").first.click()
            pg.wait_for_load_state("networkidle")
            go(pg, "/my/")
            pg.wait_for_timeout(800)
            kiem(pg.locator(".modal-dialog:has-text('cần khai thành viên')").count() == 0
                 and pg.locator(".local-fitnhom-dai").count() == 1, "(8) Để sau: hôm nay chỉ còn dải, không hộp nổi")
            shot(pg, "08-de-sau-chi-con-dai.png", ".local-fitnhom-dai")
            br.close()
    finally:
        php(XOA)
        php("set_config('chedo', %s, 'local_fitnhom'); set_config('khoathu', %s, 'local_fitnhom');"
            % (json.dumps(cfg["chedo"] or "TAT"), json.dumps(cfg["khoathu"] or "")))
        php("$DB->set_field('user', 'lang', %s, ['id' => get_admin()->id]);" % json.dumps(cfg["lang"]))
        if not cfg["batdau"]:
            php("unset_config('batdau', 'local_fitnhom');")
        php("\\local_fitnhom\\cho::xoa_dem(); purge_all_caches();")
        print("đã xóa dữ liệu thử")


if __name__ == "__main__":
    chay()
    dat = sum(KQ.values())
    print("\n%d/%d đạt" % (dat, len(KQ)))
    sys.exit(0 if dat == len(KQ) else 1)
