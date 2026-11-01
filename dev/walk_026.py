"""Đi thử Fit-Nhóm 0.2.6 trên máy phát triển (chủ dự án 01/11/2026 16:43, 16:46): chọn *Bài nhóm* thì hiện dòng nhắc "Lưu ý:
chọn bạn ở đây không thay cho báo cáo — báo cáo nộp vẫn phải ghi đủ MSSV, họ tên các thành viên." ngay dưới ô các bạn; chưa
chọn hay chọn *Em làm một mình* thì ẩn. *Tải toàn bộ bài nộp* không còn thư mục …_assignsubmission_fitnhom. Trang *Không
phải em*, hộp nổi, dải nhắc không còn chữ "khai" (chủ dự án 16:51 — thống nhất với thư: "liệt kê em", "cho biết thành viên
nhóm").

    python3 dev/walk_026.py      (máy chủ php -S ở 127.0.0.1:8080 đang chạy)

Ảnh ở docs/anh/0.2.6/. Dùng lại dữ liệu thử của walk_023 (course FN-023, người dùng fn23*), xóa khi xong.
"""
import json
import os
import sys

from playwright.sync_api import sync_playwright

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from walk_02 import KQ, MK, bo_tour, go, kiem, login, php  # noqa: E402
import walk_023  # noqa: E402

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.2.6")
NHAC = "#fitem_id_fitnhom_nhac"

TAI = r"""
require_once($CFG->dirroot . '/mod/assign/locallib.php');
$d = json_decode('%s', true);
\core\session\manager::set_user(get_admin());
[$course, $cm] = get_course_and_cm_from_cmid($d['cm'], 'assign');
$a = new assign(context_module::instance($cm->id), $cm, $course);
$t = new \mod_assign\downloader($a); $t->load_filelist();
echo json_encode(array_keys((new ReflectionProperty($t, 'filesforzipping'))->getValue($t)));
"""


NOP_APP = r"""
require_once($CFG->dirroot . '/mod/assign/locallib.php');
$d = json_decode('%s', true);
$sv = core_user::get_user($d['u'][%d]); \core\session\manager::set_user($sv);
[$course, $cm] = get_course_and_cm_from_cmid($d['cm'], 'assign');
$a = new assign(context_module::instance($cm->id), $cm, $course); $n = [];
$a->save_submission((object) ['userid' => $sv->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
  'text' => '<p>Nộp qua ứng dụng</p>', 'format' => FORMAT_HTML]], $n);
\local_fitnhom\cho::them((int) $a->get_user_submission($sv->id, false)->id, time());
"""


def chay():
    php(walk_023.XOA)
    d = json.loads(php(walk_023.DUNG).splitlines()[-1])
    q = json.dumps(d).replace("'", "\\'")
    try:
        with sync_playwright() as p:
            br = p.chromium.launch()
            pg = br.new_context(viewport={"width": 1200, "height": 900}).new_page()
            login(pg, "fn23sv1", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % d["cm"])
            bo_tour(pg)
            pg.wait_for_timeout(800)
            kiem(not pg.locator(NHAC).is_visible(), "(1) Chưa chọn: dòng nhắc ẩn")
            pg.locator("#id_fitnhom_loai_NHOM").check(force=True)
            pg.wait_for_timeout(500)
            nhac = pg.locator(NHAC)
            kiem(nhac.is_visible() and "không thay cho báo cáo" in nhac.inner_text() and "MSSV" in nhac.inner_text(),
                 "(2) Chọn Bài nhóm: dòng nhắc hiện dưới ô các bạn")
            pg.locator("body").click(position={"x": 5, "y": 5})
            pg.evaluate("() => { const f = document.querySelector('#fgroup_id_fitnhom_loai');"
                        " f.scrollIntoView({block: 'start'}); window.scrollBy(0, -20); }")
            b1 = pg.locator("#fgroup_id_fitnhom_loai").bounding_box()
            b2 = nhac.bounding_box()
            pg.screenshot(path=os.path.join(OUT, "01-dong-nhac-bai-nhom.png"),
                          clip={"x": b1["x"] - 10, "y": b1["y"] - 10, "width": 1000, "height": b2["y"] + b2["height"] - b1["y"] + 24})
            print("chụp 01-dong-nhac-bai-nhom.png")
            pg.locator("#id_fitnhom_loai_MOT").check(force=True)
            pg.wait_for_timeout(500)
            kiem(not pg.locator(NHAC).is_visible(), "(3) Chọn Em làm một mình: dòng nhắc ẩn")
            # Nộp bài nhóm có B để có câu khai trong gói tải.
            pg.locator("#id_fitnhom_loai_NHOM").check(force=True)
            pg.wait_for_function("() => window.tinymce && tinymce.activeEditor")
            pg.evaluate("() => tinymce.activeEditor.setContent('<p>Báo cáo nhóm</p>')")
            pg.locator("#fitem_id_fitnhom_tv input[type=text]").fill("Bình")
            pg.wait_for_timeout(1200)
            pg.keyboard.press("Enter")
            pg.wait_for_timeout(500)
            pg.locator("#id_submitbutton").click(force=True)
            pg.wait_for_load_state("networkidle")
            br.close()
        so = php("echo $DB->count_records('local_fitnhom_tv', ['assignid' => %d]);" % d["a"]).strip()
        tep = json.loads(php(TAI % q).splitlines()[-1])
        kiem(so == "1" and tep and not [t for t in tep if "fitnhom" in t],
             "(4) Tải toàn bộ bài nộp: có bài của A, không thư mục …_assignsubmission_fitnhom")
        print("  gói tải:", tep)

        # (5) B mở trang "Không phải em" (liên kết trong thư); (6) C nộp qua ứng dụng (không có câu hỏi) → hộp nổi, dải nhắc.
        tv = php("echo $DB->get_field('local_fitnhom_tv', 'id', ['assignid' => %d]);" % d["a"]).splitlines()[-1].strip()
        php(NOP_APP % (q, 2))
        with sync_playwright() as p:
            br = p.chromium.launch()
            pg = br.new_context(viewport={"width": 1200, "height": 900}).new_page()
            login(pg, "fn23sv2", MK)
            go(pg, "/local/fitnhom/tuchoi.php?id=" + tv)
            noi = pg.inner_text("#region-main")
            kiem("liệt kê em" in noi and "khai" not in noi.lower(), "(5) Trang Không phải em: \"liệt kê em\", không chữ khai")
            pg.locator("#region-main").screenshot(path=os.path.join(OUT, "02-trang-khong-phai-em.png"))
            print("chụp 02-trang-khong-phai-em.png")
            login(pg, "fn23sv3", MK)
            go(pg, "/my/")
            pg.wait_for_timeout(1500)
            modal = pg.locator(".modal-dialog:has-text('chưa cho biết thành viên nhóm')")
            dai = pg.locator(".local-fitnhom-dai")
            kiem(modal.count() == 1 and "khai" not in modal.inner_text().lower() and dai.count() == 1
                 and "khai" not in dai.inner_text().lower(), "(6) Hộp nổi, dải nhắc: \"chưa cho biết thành viên nhóm\", không chữ khai")
            modal.screenshot(path=os.path.join(OUT, "03-hop-noi.png"))
            print("chụp 03-hop-noi.png")
            br.close()
    finally:
        php(walk_023.XOA)
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
