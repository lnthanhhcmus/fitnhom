"""Đi thử Fit-Nhóm 0.2.5 trên máy phát triển (chủ dự án cài thử 01/11/2026 16:24): admin đổi Chế độ **trên trang cài đặt**
(không đặt bằng lệnh như các walk trước) → "Lúc bật" được ghi; bài nộp trước khi bật, gỡ ra nộp lại có câu khai → trang
*Thành viên nhóm* của course đếm được (0.1–0.2.4: "Chưa có bài nộp nào từ lúc bật Fit-Nhóm").

    python3 dev/walk_025.py      (máy chủ php -S ở 127.0.0.1:8080 đang chạy)

Ảnh ở docs/anh/0.2.5/. Dùng lại dữ liệu thử của walk_023 (course FN-023, người dùng fn23*), xóa khi xong; cấu hình Fit-Nhóm
(kể cả "Lúc bật") trả lại như trước.
"""
import json
import os
import sys

from playwright.sync_api import sync_playwright

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from walk_02 import ADMIN, KQ, MK, bo_tour, go, kiem, login, php  # noqa: E402
import walk_023  # noqa: E402

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.2.5")

NOP = r"""
require_once($CFG->dirroot . '/mod/assign/locallib.php');
$d = json_decode('%s', true);
$sv = core_user::get_user($d['u'][%d]); \core\session\manager::set_user($sv);
[$course, $cm] = get_course_and_cm_from_cmid($d['cm'], 'assign');
$a = new assign(context_module::instance($cm->id), $cm, $course); $n = [];
if (%d) { $a->remove_submission($sv->id); }
$a->save_submission((object) (['userid' => $sv->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
  'text' => '<p>Bài làm</p>', 'format' => FORMAT_HTML]] + json_decode('%s', true)), $n);
echo $a->get_user_submission($sv->id, false)->status;
"""


def nop(q, i, go_lai, khai):
    return php(NOP % (q, i, 1 if go_lai else 0, json.dumps(khai))).splitlines()[-1]


def chay():
    php(walk_023.XOA)
    bd_cu = php("echo (int) get_config('local_fitnhom', 'batdau');").strip()
    lang_cu = php("echo $DB->get_field('user', 'lang', ['id' => get_admin()->id]);").splitlines()[-1].strip()
    php("$DB->set_field('user', 'lang', 'vi', ['id' => get_admin()->id]);")
    d = json.loads(php(walk_023.DUNG).splitlines()[-1])
    q = json.dumps(d).replace("'", "\\'")
    try:
        # Trước khi cài / bật: chế độ Tắt, chưa từng bật; A nộp bài (không câu hỏi).
        php("set_config('chedo', 'TAT', 'local_fitnhom'); set_config('khoathu', '', 'local_fitnhom');"
            " unset_config('batdau', 'local_fitnhom'); purge_all_caches();")
        nop(q, 0, False, {})
        with sync_playwright() as p:
            br = p.chromium.launch()
            pg = br.new_context(viewport={"width": 1200, "height": 900}).new_page()
            # (1) Admin chuyển Thử nghiệm cho FN-023 trên trang cài đặt.
            login(pg, *ADMIN)
            go(pg, "/admin/settings.php?section=local_fitnhom")
            bo_tour(pg)
            pg.select_option("#id_s_local_fitnhom_chedo", "THU")
            pg.fill("#id_s_local_fitnhom_khoathu", "FN-023")
            pg.locator("#adminsettings button[type=submit]").first.click()
            pg.wait_for_load_state("networkidle")
            cfg = json.loads(php("echo json_encode([get_config('local_fitnhom', 'chedo'),"
                                 " (int) get_config('local_fitnhom', 'batdau')]);").splitlines()[-1])
            kiem(cfg[0] == "THU" and cfg[1] > 0, "(1) Lưu Chế độ Thử nghiệm trên trang cài đặt: \"Lúc bật\" được ghi")
            go(pg, "/local/fitnhom/tinhhinh.php")
            noi = pg.inner_text("#region-main")
            kiem("Chưa bật lần nào" not in noi, "(1) Tình hình: dòng Lúc bật có giờ")

            # (2) A gỡ bài ra nộp lại (Em làm một mình); B nộp bài nhóm có C.
            nop(q, 0, True, {"fitnhom_loai": "MOT"})
            nop(q, 1, False, {"fitnhom_loai": "NHOM", "fitnhom_tv": [d["u"][2]]})
            cid = php("echo $DB->get_field('course', 'id', ['shortname' => 'FN-023']);").splitlines()[-1].strip()
            go(pg, "/local/fitnhom/course.php?id=" + cid)
            noi = pg.inner_text("#region-main")
            kiem("Chưa có bài nộp nào" not in noi and "Đồ án nhóm 1" in noi,
                 "(2) Trang Thành viên nhóm của course: có bài Đồ án nhóm 1")
            hang = pg.locator("#region-main table tbody tr").first.inner_text()
            kiem("2" in hang, "(2) Dòng bài tập đếm 2 bài nộp (A một mình, B bài nhóm)")
            pg.locator("#region-main").screenshot(path=os.path.join(OUT, "01-trang-thanh-vien-nhom.png"))
            print("chụp 01-trang-thanh-vien-nhom.png")
            br.close()
    finally:
        php(walk_023.XOA)
        php("$DB->set_field('user', 'lang', %s, ['id' => get_admin()->id]);" % json.dumps(lang_cu or "en"))
        cfg = d["cfg"]
        php("set_config('chedo', %s, 'local_fitnhom'); set_config('khoathu', %s, 'local_fitnhom');"
            " set_config('nguoigui', %s, 'local_fitnhom');"
            % (json.dumps(cfg["chedo"] or "TAT"), json.dumps(cfg["khoathu"] or ""), json.dumps(cfg["nguoigui"] or "FIT-MOODLE")))
        php(("set_config('batdau', %s, 'local_fitnhom');" % bd_cu) if bd_cu != "0" else "unset_config('batdau', 'local_fitnhom');")
        php("\\local_fitnhom\\cho::xoa_dem(); purge_all_caches();")
        print("đã xóa dữ liệu thử")


if __name__ == "__main__":
    os.makedirs(OUT, exist_ok=True)
    chay()
    dat = sum(KQ.values())
    print("\n%d/%d đạt" % (dat, len(KQ)))
    sys.exit(0 if dat == len(KQ) else 1)
