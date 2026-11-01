"""Đi thử Fit-Nhóm 0.2.4 trên máy phát triển (chủ dự án 01/11/2026 12:44, 15:11): ô "Các bạn cùng làm" ẩn khi chưa chọn
"Bài nhóm" — kể cả lúc chưa chọn nút nào (0.1–0.2.3: ô vẫn hiện); em bấm Lưu khi chưa chọn thì trang tải lại kèm dòng đỏ
"Em chọn …"; em bấm một trong hai nút thì dòng đỏ tắt (0.1–0.2.3: dòng đỏ còn mãi tới lần Lưu sau).

    python3 dev/walk_024.py      (máy chủ php -S ở 127.0.0.1:8080 đang chạy)

Ảnh ở docs/anh/0.2.4/. Dùng lại dữ liệu thử của walk_023 (course FN-023, người dùng fn23*), xóa khi xong.
"""
import json
import os
import sys

from playwright.sync_api import sync_playwright

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from walk_02 import KQ, MK, bo_tour, go, kiem, login, php  # noqa: E402
import walk_023  # noqa: E402

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(REPO, "docs", "anh", "0.2.4")
LOI = "#fgroup_id_error_fitnhom_loai"


def chup(pg, name):
    pg.evaluate("() => { const f = document.querySelector('#fgroup_id_fitnhom_loai');"
                " f.scrollIntoView({block: 'start'}); window.scrollBy(0, -20); }")
    box = pg.locator("#fgroup_id_fitnhom_loai").bounding_box()
    pg.screenshot(path=os.path.join(OUT, name),
                  clip={"x": box["x"] - 10, "y": box["y"] - 10, "width": 980, "height": box["height"] + 120})
    print("chụp", name)


def chay():
    php(walk_023.XOA)
    d = json.loads(php(walk_023.DUNG).splitlines()[-1])
    try:
        with sync_playwright() as p:
            br = p.chromium.launch()
            pg = br.new_context(viewport={"width": 1200, "height": 900}).new_page()
            login(pg, "fn23sv1", MK)
            go(pg, "/mod/assign/view.php?id=%d&action=editsubmission" % d["cm"])
            bo_tour(pg)
            pg.wait_for_function("() => window.tinymce && tinymce.activeEditor")
            pg.evaluate("() => tinymce.activeEditor.setContent('<p>Bài làm của em</p>')")
            # (0) Mở trang, chưa chọn nút nào: ô các bạn ẩn.
            kiem(not pg.locator("#id_fitnhom_loai_MOT").is_checked() and not pg.locator("#id_fitnhom_loai_NHOM").is_checked()
                 and not pg.locator("#fitem_id_fitnhom_tv").is_visible(), "(0) Mở trang, chưa chọn: ô các bạn ẩn")
            # (1) Lưu khi chưa chọn: trang tải lại, dòng đỏ hiện.
            pg.locator("#id_submitbutton").click(force=True)
            pg.wait_for_load_state("networkidle")
            pg.wait_for_timeout(800)
            kiem(pg.locator(LOI).is_visible() and "Em chọn" in pg.locator(LOI).inner_text(),
                 "(1) Lưu khi chưa chọn: dòng đỏ \"Em chọn …\" hiện")
            kiem(not pg.locator("#fitem_id_fitnhom_tv").is_visible(), "(1) Trang tải lại, chưa chọn: ô các bạn ẩn")
            chup(pg, "01-chua-chon-bam-luu.png")
            # (2) Chọn Bài nhóm: dòng đỏ tắt, ô các bạn hiện.
            pg.locator("#id_fitnhom_loai_NHOM").check(force=True)
            pg.wait_for_timeout(500)
            pg.locator("body").click(position={"x": 5, "y": 5})
            kiem(not pg.locator(LOI).is_visible() and pg.locator("#fitem_id_fitnhom_tv").is_visible()
                 and "has-danger" not in (pg.locator("#fgroup_id_fitnhom_loai").get_attribute("class") or ""),
                 "(2) Chọn Bài nhóm: dòng đỏ tắt, ô các bạn hiện")
            chup(pg, "02-da-chon-het-dong-do.png")
            # (3) Đổi sang Em làm một mình: vẫn không dòng đỏ; Lưu được.
            pg.locator("#id_fitnhom_loai_MOT").check(force=True)
            pg.wait_for_timeout(500)
            pg.wait_for_function("() => window.tinymce && tinymce.activeEditor")
            pg.evaluate("() => tinymce.activeEditor.setContent('<p>Bài làm của em</p>')")
            kiem(not pg.locator(LOI).is_visible() and not pg.locator("#fitem_id_fitnhom_tv").is_visible(),
                 "(3) Đổi sang Em làm một mình: không dòng đỏ, ô các bạn ẩn")
            pg.locator("#id_submitbutton").click(force=True)
            pg.wait_for_load_state("networkidle")
            loai = php("echo $DB->get_field('local_fitnhom_khai', 'loai', ['assignid' => %d]);" % d["a"])
            kiem(loai == "MOT", "(3) Lưu: câu khai Em làm một mình đã ghi")
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
