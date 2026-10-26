# -*- coding: utf-8 -*-
"""Đi thử Fit-Nhóm 0.1 bằng trình duyệt trên Moodle phát triển, chụp ảnh vào docs/anh/0.1/.

Kịch bản (TL54 v0.3): admin đặt chế độ Thử nghiệm một course → em A nộp bài, khai bài nhóm cùng B → B nhận thông báo, mở trang
nộp thấy "bạn A đã nộp và khai em", có liên kết Không phải em → giảng viên thấy cột Thành viên nhóm ở bảng chấm → em C nộp qua
"ứng dụng" (không có câu khai) → hộp nổi lần sau vào Moodle, bấm "Em làm một mình" → course ngoài danh sách thử không hỏi →
trang Tình hình, Quét ngay. Dữ liệu thử tạo rồi xóa khi xong; cấu hình trả về như trước.

Dùng:  python3 dev/walk_01.py
"""
import json
import os
import subprocess
import sys

from playwright.sync_api import sync_playwright

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.1")
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
$g = new testing_data_generator();
$cfg = ['lang' => $DB->get_field('user', 'lang', ['id' => get_admin()->id]),'chedo' => get_config('local_fitnhom', 'chedo'), 'khoathu' => get_config('local_fitnhom', 'khoathu'),
  'batdau' => get_config('local_fitnhom', 'batdau')];
$kq = ['cfg' => $cfg];
foreach (['FN-THU-01' => 'Thử Fit-Nhóm 01', 'FN-KHAC-01' => 'Course ngoài danh sách thử'] as $sn => $fn) {
  $c = $g->create_course(['shortname' => $sn, 'fullname' => $fn]);
  $kq['c'][$sn] = (int) $c->id;
  $a = $g->get_plugin_generator('mod_assign')->create_instance(['course' => $c->id, 'name' => 'Đồ án nhóm 1',
    'assignsubmission_onlinetext_enabled' => 1, 'submissiondrafts' => 0, 'duedate' => time() + 7 * 86400]);
  $kq['a'][$sn] = (int) $a->cmid;
}
$ten = [['An', 'Nguyễn Văn'], ['Bình', 'Trần Thị'], ['Cường', 'Lê Văn'], ['Dung', 'Phạm Thị']];
foreach ($ten as $i => [$f, $l]) {
  $u = $g->create_user(['username' => 'fnsv' . ($i + 1), 'password' => '%s', 'firstname' => $f, 'lastname' => $l,
    'idnumber' => '2312' . (100 + $i), 'email' => 'fnsv' . ($i + 1) . '@example.com']);
  foreach ($kq['c'] as $cid) { $g->enrol_user($u->id, $cid, 'student'); }
  $kq['u'][] = (int) $u->id;
}
$t = $g->create_user(['username' => 'fngv', 'password' => '%s', 'firstname' => 'Hà', 'lastname' => 'Giảng viên']);
foreach ($kq['c'] as $cid) { $g->enrol_user($t->id, $cid, 'editingteacher'); }
$DB->set_field('user', 'lang', 'vi', ['id' => get_admin()->id]);
set_config('chedo', 'THU', 'local_fitnhom');
set_config('khoathu', 'FN-THU-01', 'local_fitnhom');
require_once($CFG->dirroot . '/local/fitnhom/lib.php');
local_fitnhom_chedo_doi('chedo');
echo json_encode($kq);
""" % (MK, MK)

XOA = r"""
\core\session\manager::set_user(get_admin());
foreach (['FN-THU-01', 'FN-KHAC-01'] as $sn) {
  if ($c = $DB->get_record('course', ['shortname' => $sn])) { delete_course($c, false); }
}
foreach ($DB->get_records_select('user', "username LIKE 'fnsv%' OR username = 'fngv'") as $u) { delete_user($u); }
"""


def nop_ung_dung(cmid, userid):
    """Nộp như ứng dụng điện thoại / dịch vụ web: không có trường câu khai."""
    php("""require_once($CFG->dirroot . '/mod/assign/locallib.php');
$u = core_user::get_user(%d); \\core\\session\\manager::set_user($u);
[$course, $cm] = get_course_and_cm_from_cmid(%d, 'assign');
$a = new assign(context_module::instance($cm->id), $cm, $course); $n = [];
$a->save_submission((object) ['userid' => $u->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
  'text' => 'Nộp bằng ứng dụng', 'format' => FORMAT_HTML]], $n);""" % (userid, cmid))


def chay():
    php(XOA)
    d = json.loads(php(DUNG).splitlines()[-1])
    cfg = d["cfg"]
    cm = d["a"]["FN-THU-01"]
    try:
        with sync_playwright() as p:
            br = p.chromium.launch()
            pg = br.new_page(viewport={"width": 1366, "height": 900})
            # (1) Admin: trang cài đặt có mục đích; Tình hình.
            login(pg, *ADMIN)
            go(pg, "/admin/settings.php?section=local_fitnhom")
            kiem("Fit-Nhóm dùng để làm gì" in pg.content() and pg.locator("#id_s_local_fitnhom_chedo").count() == 1,
                 "(1) Cài đặt: phần mục đích, ô Chế độ")
            shot(pg, "01-cai-dat.png")
            # (2) Em A nộp: câu hỏi bắt buộc; chưa chọn thì không lưu; chọn Bài nhóm + B.
            login(pg, "fnsv1", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % cm)
            kiem(pg.locator("input[name=fitnhom_loai]").count() == 2, "(2) Trang nộp bài có câu hỏi Một mình / Bài nhóm")
            pg.locator("textarea[name='onlinetext_editor[text]']").wait_for(state="attached")
            pg.wait_for_timeout(2500)
            pg.evaluate("""() => { const t = document.querySelector("textarea[name='onlinetext_editor[text]']");
                 if (t) { t.value = '<p>Báo cáo đồ án nhóm</p>'; }
                 if (window.tinymce && tinymce.activeEditor) { tinymce.activeEditor.setContent('<p>Báo cáo đồ án nhóm</p>'); } }""")
            pg.click("#id_submitbutton")
            pg.wait_for_load_state("networkidle")
            kiem(pg.locator("#id_submitbutton").count() == 1, "(2) Chưa chọn câu khai: không lưu được, ở lại trang")
            pg.locator("input[name=fitnhom_loai][value=NHOM]").check()
            ac = pg.locator("#fitem_id_fitnhom_tv input[type=text]").first
            ac.fill("Bình")
            pg.wait_for_timeout(600)
            pg.keyboard.press("ArrowDown")
            pg.keyboard.press("Enter")
            pg.wait_for_timeout(300)
            shot(pg, "02-trang-nop-bai.png", "#fitem_id_fitnhom_gioithieu, form.mform")
            pg.evaluate("""() => { if (window.tinymce && tinymce.activeEditor) {
                 tinymce.activeEditor.setContent('<p>Báo cáo đồ án nhóm</p>'); } }""")
            pg.click("#id_submitbutton")
            pg.wait_for_load_state("networkidle")
            k = php("$k = $DB->get_record('local_fitnhom_khai', ['userid' => %d]); echo $k ? $k->loai . ':' . "
                    "implode(',', $DB->get_fieldset_select('local_fitnhom_tv', 'userid', 'khaiid = ?', [$k->id])) : '';" % d["u"][0])
            kiem(pg.locator(".submissionstatussubmitted").count() >= 1 and k == "NHOM:%d" % d["u"][1],
                 "(2) Đã nộp; câu khai NHOM cùng B lưu đúng")
            shot(pg, "03-trang-thai-bai-nop.png")
            # (3) B: thông báo; trang nộp có dòng gợi ý điền sẵn A.
            n = php("echo $DB->count_records_select('notifications', \"component = 'local_fitnhom' AND useridto = %d\");" % d["u"][1])
            kiem(n == "1", "(3) B nhận một thông báo 'được khai'")
            login(pg, "fnsv2", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % cm)
            kiem("đã nộp bài này" in pg.content() and pg.locator("input[name=fitnhom_loai][value=NHOM]").is_checked(),
                 "(3) B mở trang nộp: 'Bạn An đã nộp … khai em', điền sẵn Bài nhóm")
            shot(pg, "04-goi-y-cho-ban-B.png", "form.mform")
            tv = php("echo $DB->get_field('local_fitnhom_tv', 'id', ['userid' => %d]);" % d["u"][1])
            go(pg, "/local/fitnhom/tuchoi.php?id=%s" % tv)
            kiem(pg.locator("button:has-text('Không phải em'), input[value='Không phải em']").count() == 1,
                 "(3) Trang Không phải em của B")
            shot(pg, "05-khong-phai-em.png")
            # (4) Giảng viên: cột Thành viên nhóm ở bảng chấm.
            login(pg, "fngv", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=grading" % cm)
            kiem("Thành viên nhóm" in pg.content() and "Bình" in pg.content(), "(4) Bảng chấm có cột Thành viên nhóm")
            shot(pg, "06-bang-cham.png")
            # (5) C nộp bằng 'ứng dụng' → hộp nổi lần sau vào Moodle; bấm Em làm một mình.
            nop_ung_dung(cm, d["u"][2])
            login(pg, "fnsv3", MK)
            go(pg, "/my/")
            pg.wait_for_timeout(1200)
            modal = pg.locator(".modal-dialog:has-text('cần khai thành viên')")
            kiem(modal.count() == 1 and pg.locator(".local-fitnhom-dai").count() == 1, "(5) C: dải nhắc + hộp nổi")
            shot(pg, "07-hop-noi.png", ".modal-dialog")
            modal.locator("button:has-text('Em làm một mình')").first.click()
            pg.wait_for_load_state("networkidle")
            kiem(pg.locator(".local-fitnhom-dai").count() == 0
                 and php("echo $DB->get_field('local_fitnhom_khai', 'loai', ['userid' => %d]);" % d["u"][2]) == "MOT",
                 "(5) Bấm Em làm một mình: lưu, hết dải nhắc")
            # (6) Course ngoài danh sách thử: không hỏi.
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % d["a"]["FN-KHAC-01"])
            kiem(pg.locator("input[name=fitnhom_loai]").count() == 0, "(6) Course ngoài danh sách thử: không có câu hỏi")
            # (7) Admin: Tình hình, Quét ngay.
            login(pg, *ADMIN)
            go(pg, "/local/fitnhom/tinhhinh.php")
            pg.locator("button:has-text('Quét ngay'), input[value='Quét ngay']").first.click()
            pg.wait_for_load_state("networkidle")
            kiem("Đã quét" in pg.content() and "FN-THU-01" in pg.content(), "(7) Tình hình: Quét ngay, course thử")
            shot(pg, "08-tinh-hinh.png")
            br.close()
    finally:
        php(XOA)
        php("set_config('chedo', %s, 'local_fitnhom'); set_config('khoathu', %s, 'local_fitnhom');"
            % (json.dumps(cfg["chedo"] or "TAT"), json.dumps(cfg["khoathu"] or "")))
        php("$DB->set_field('user', 'lang', %s, ['id' => get_admin()->id]);" % json.dumps(cfg["lang"]))
        if not cfg["batdau"]:
            php("unset_config('batdau', 'local_fitnhom');")
        print("đã xóa dữ liệu thử")


if __name__ == "__main__":
    chay()
    dat = sum(KQ.values())
    print("\n%d/%d đạt" % (dat, len(KQ)))
    sys.exit(0 if dat == len(KQ) else 1)
