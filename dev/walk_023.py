"""Đi thử Fit-Nhóm 0.2.3 trên máy phát triển (chủ dự án 01/11/2026 12:44): người nộp sửa câu khai — bớt / thêm bạn, đổi sang
"Em làm một mình" — thì bạn bị bỏ được xóa và nhận thư "không còn liệt kê em"; liên kết "Em không cùng làm bài này" trong thư
cũ của bạn bị bỏ mở ra trang báo nhẹ (không lỗi).

    python3 dev/walk_023.py      (máy chủ php -S ở 127.0.0.1:8080 đang chạy)

Ảnh ở docs/anh/0.2.3/. Dữ liệu thử (course FN-023, người dùng fn23*) xóa khi xong.
"""
import html
import json
import os
import sys

from playwright.sync_api import sync_playwright

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from walk_02 import BASE, KQ, MK, bo_tour, go, kiem, login, php  # noqa: E402
from walk_021 import KHUNG  # noqa: E402

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.2.3")

DUNG = r"""
\core\session\manager::set_user(get_admin());
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/local/fitnhom/lib.php');
$g = new testing_data_generator();
$cfg = ['chedo' => get_config('local_fitnhom', 'chedo'), 'khoathu' => get_config('local_fitnhom', 'khoathu'),
  'nguoigui' => get_config('local_fitnhom', 'nguoigui')];
$kq = ['cfg' => $cfg];
$c = $g->create_course(['shortname' => 'FN-023', 'fullname' => 'Nhập môn lập trình (thử Fit-Nhóm)']);
$ten = [['An', 'Nguyễn Văn'], ['Bình', 'Trần Thị'], ['Cường', 'Lê Văn'], ['Dung', 'Phạm Thị']];
foreach ($ten as $i => [$f, $l]) {
  $u = $g->create_user(['username' => 'fn23sv' . ($i + 1), 'password' => '%s', 'firstname' => $f, 'lastname' => $l,
    'idnumber' => '2312' . (300 + $i), 'email' => 'fn23sv' . ($i + 1) . '@example.com', 'lang' => 'vi']);
  $g->enrol_user($u->id, $c->id, 'student');
  $kq['u'][] = (int) $u->id;
}
set_config('chedo', 'BAT', 'local_fitnhom');
set_config('nguoigui', 'FIT-MOODLE', 'local_fitnhom');
local_fitnhom_chedo_doi('chedo');
\local_fitnhom\phamvi::xoa_dem();
$x = $g->get_plugin_generator('mod_assign')->create_instance(['course' => $c->id, 'name' => 'Đồ án nhóm 1',
  'assignsubmission_onlinetext_enabled' => 1, 'submissiondrafts' => 0, 'duedate' => time() + 3 * 86400]);
$kq['cm'] = (int) $x->cmid;
$kq['a'] = (int) $x->id;
echo json_encode($kq);
""" % MK

NOP = r"""
require_once($CFG->dirroot . '/mod/assign/locallib.php');
$d = json_decode('%s', true); $u = $d['u'];
$sv = core_user::get_user($u[0]); \core\session\manager::set_user($sv);
[$course, $cm] = get_course_and_cm_from_cmid($d['cm'], 'assign');
$a = new assign(context_module::instance($cm->id), $cm, $course); $n = [];
$a->save_submission((object) ['userid' => $sv->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
  'text' => '<p>Bài nộp nhóm</p>', 'format' => FORMAT_HTML], 'fitnhom_loai' => 'NHOM', 'fitnhom_tv' => %s], $n);
$tv = $DB->get_records_menu('local_fitnhom_tv', ['assignid' => $d['a']], 'userid', 'userid, id');
echo json_encode($tv);
"""

THU = r"""
$d = json_decode('%s', true);
[$in, $p] = $DB->get_in_or_equal($d['u']);
$r = $DB->get_records_select('notifications', "component = 'local_fitnhom' AND useridto $in", $p, 'id');
$kq = [];
foreach ($r as $m) {
  $kq[] = ['den' => (int) $m->useridto, 'loai' => $m->eventtype, 'tieude' => $m->subject, 'html' => $m->fullmessagehtml,
    'chu' => $m->fullmessage, 'tenden' => fullname(core_user::get_user($m->useridto))];
}
echo json_encode($kq);
"""

XOA = r"""
\core\session\manager::set_user(get_admin());
if ($c = $DB->get_record('course', ['shortname' => 'FN-023'])) { delete_course($c, false); }
foreach ($DB->get_records_select('user', "username LIKE 'fn23%'") as $u) {
  $DB->delete_records('notifications', ['useridto' => $u->id]);
  delete_user($u);
}
"""


def shot(pg, name, sel):
    pg.locator(sel).first.screenshot(path=os.path.join(OUT, name))
    print("chụp", name)


def thu(d):
    return json.loads(php(THU % json.dumps(d).replace("'", "\\'")).splitlines()[-1])


def chay():
    php(XOA)
    d = json.loads(php(DUNG).splitlines()[-1])
    u, cm = d["u"], d["cm"]
    q = json.dumps(d).replace("'", "\\'")
    try:
        # (1) A nộp bài nhóm cùng B, C; rồi sửa: bỏ C, thêm D.
        tv1 = json.loads(php(NOP % (q, json.dumps([u[1], u[2]]))).splitlines()[-1])
        tvc = int(tv1[str(u[2])])
        tv2 = json.loads(php(NOP % (q, json.dumps([u[1], u[3]]))).splitlines()[-1])
        kiem(sorted(map(int, tv2)) == sorted([u[1], u[3]]), "(1) Sửa câu khai: danh sách còn B, D (C bị xóa)")
        t = thu(d)
        loai = [(m["den"], m["loai"]) for m in t]
        kiem(sorted(loai) == sorted([(u[1], "duockhai"), (u[2], "duockhai"), (u[2], "khongcon"), (u[3], "duockhai")]),
             "(1) Thư: B, C 'liệt kê em' lần đầu; lần sửa C 'không còn liệt kê em', D 'liệt kê em', B không nhận lại")

        with sync_playwright() as p:
            br = p.chromium.launch()
            pg = br.new_context(viewport={"width": 1200, "height": 900}).new_page()
            # (2) A mở lại trang nộp, chọn Em làm một mình: ô chọn bạn ẩn; Lưu → B, D bị bỏ.
            login(pg, "fn23sv1", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % cm)
            bo_tour(pg)
            pg.wait_for_timeout(800)
            kiem(pg.locator("#id_fitnhom_loai_NHOM").is_checked() and pg.locator("#fitem_id_fitnhom_tv").is_visible(),
                 "(2) Trang nộp mở lại: Bài nhóm, ô các bạn hiện")
            pg.locator("#id_fitnhom_loai_MOT").check(force=True)
            pg.wait_for_timeout(500)
            kiem(not pg.locator("#fitem_id_fitnhom_tv").is_visible(), "(2) Chọn Em làm một mình: ô các bạn ẩn")
            pg.evaluate("() => { const f = document.querySelector('#fgroup_id_fitnhom_loai');"
                        " f.scrollIntoView({block: 'start'}); window.scrollBy(0, -20); }")
            box = pg.locator("#fgroup_id_fitnhom_loai").bounding_box()
            pg.screenshot(path=os.path.join(OUT, "01-doi-sang-mot-minh.png"),
                          clip={"x": box["x"] - 10, "y": box["y"] - 10, "width": 980, "height": box["height"] + 20})
            print("chụp 01-doi-sang-mot-minh.png")
            pg.locator("#id_submitbutton").click(force=True)
            pg.wait_for_load_state("networkidle")
            con = php("echo $DB->count_records('local_fitnhom_tv', ['assignid' => %d]);" % d["a"])
            kiem(con == "0", "(2) Lưu: không còn bạn nào trong danh sách")
            t2 = thu(d)
            moi = [(m["den"], m["loai"]) for m in t2[len(t):]]
            t = t2
            kiem(sorted(moi) == sorted([(u[1], "khongcon"), (u[3], "khongcon")]),
                 "(2) B, D nhận thư 'không còn liệt kê em'; C (đã bị bỏ trước) không nhận lại")

            # (3) Thư cho bạn bị bỏ.
            m = next(x for x in t if x["den"] == u[2] and x["loai"] == "khongcon")
            kiem(m["chu"].startswith("Chào em,") and "không còn liệt kê em" in m["chu"] and "Hệ thống FIT-MOODLE" in m["chu"]
                 and "khai" not in m["chu"] and "/mod/assign/view.php" in m["chu"] and "tuchoi.php" not in m["chu"],
                 "(3) Thư: Chào em, không còn liệt kê em, liên kết trang bài tập, ký Hệ thống FIT-MOODLE")
            pg.set_content(KHUNG % ('<div class="hop"><h1>%s</h1><div class="dau"><b>FIT-MOODLE</b> &lt;địa chỉ không trả '
                                    'lời của trang&gt;<br>đến %s</div>%s<div class="nhan">Thư thật của plugin, lấy từ thông '
                                    'báo Moodle đã ghi (máy phát triển không gửi thư).</div></div>'
                                    % (html.escape(m["tieude"]), html.escape(m["tenden"]), m["html"])))
            shot(pg, "02-thu-khong-con-liet-ke.png", "#khung")

            # (4) C mở liên kết "Em không cùng làm bài này" trong thư cũ.
            login(pg, "fn23sv3", MK)
            go(pg, "/local/fitnhom/tuchoi.php?id=%d" % tvc)
            noi = pg.inner_text("#region-main")
            kiem("không còn liệt kê em" in noi and "Không tìm thấy" not in noi and "error" not in pg.url,
                 "(4) Liên kết cũ của bạn bị bỏ: trang báo nhẹ, không lỗi")
            shot(pg, "03-lien-ket-cu.png", "#region-main")
            br.close()
    finally:
        php(XOA)
        cfg = d["cfg"]
        php("set_config('chedo', %s, 'local_fitnhom'); set_config('khoathu', %s, 'local_fitnhom');"
            " set_config('nguoigui', %s, 'local_fitnhom');"
            % (json.dumps(cfg["chedo"] or "TAT"), json.dumps(cfg["khoathu"] or ""), json.dumps(cfg["nguoigui"] or "FIT-MOODLE")))
        php("\\local_fitnhom\\cho::xoa_dem(); purge_all_caches();")
        print("đã xóa dữ liệu thử")


if __name__ == "__main__":
    os.makedirs(OUT, exist_ok=True)
    chay()
    dat = sum(KQ.values())
    print("\n%d/%d đạt" % (dat, len(KQ)))
    sys.exit(0 if dat == len(KQ) else 1)
