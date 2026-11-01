<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_fitnhom;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/helper.php');

/**
 * Bản 0.2.3 (chủ dự án 01/11/2026 12:44): người nộp sửa câu khai — bớt bạn hay đổi sang "Em làm một mình" — thì các bạn bị bỏ
 * được xóa khỏi danh sách và nhận thư "không còn liệt kê em"; bạn mới thêm nhận thư "liệt kê em" như cũ; bạn giữ nguyên
 * không nhận gì; bạn đã bấm "Không phải em" thì không nhận thư khi bị bỏ.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\khai
 * @covers     \local_fitnhom\thongbao
 */
final class ban023_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Thêm / bớt bạn so với lần nộp trước: bạn bị bỏ nhận "không còn", bạn mới nhận "liệt kê", bạn giữ nguyên không nhận gì;
     * lưu lại y như cũ không gửi thư nào.
     */
    public function test_them_bot_ban(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(4);
        $this->luu($sv[0], $a, khai::NHOM, [$sv[1]->id, $sv[2]->id]);

        $tb = $this->luu($sv[0], $a, khai::NHOM, [$sv[1]->id, $sv[3]->id]);
        $this->assertSame([(int) $sv[2]->id => 'khongcon', (int) $sv[3]->id => 'duockhai'], $tb);
        $k = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        $this->assertEqualsCanonicalizing([(int) $sv[1]->id, (int) $sv[3]->id], array_keys($k->tv));
        $tv = api::thanhvien((int) $a->get_instance()->id);
        $this->assertArrayNotHasKey((int) $sv[2]->id, $tv, 'Bạn bị bỏ không còn được tính đã nộp');
        $this->assertArrayHasKey((int) $sv[3]->id, $tv);

        $this->assertSame([], $this->luu($sv[0], $a, khai::NHOM, [$sv[1]->id, $sv[3]->id]), 'Không đổi: không thư');
    }

    /**
     * Bài nhóm đổi sang "Em làm một mình": mọi bạn bị xóa; bạn còn được liệt kê nhận "không còn", bạn đã bấm "Không phải em"
     * thì không. Thư có chào, tên bài, liên kết trang bài tập, dòng ký; không có liên kết "Em không cùng làm bài này".
     */
    public function test_doi_sang_mot_minh(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        set_config('nguoigui', 'FIT-MOODLE', 'local_fitnhom');
        [, $a, $sv] = $this->bai_tap(3);
        $aid = (int) $a->get_instance()->id;
        $this->luu($sv[0], $a, khai::NHOM, [$sv[1]->id, $sv[2]->id]);
        $tvid = (int) $DB->get_field('local_fitnhom_tv', 'id', ['assignid' => $aid, 'userid' => $sv[2]->id]);
        khai::tu_choi($tvid, (int) $sv[2]->id, false, time());

        $sink = $this->redirectMessages();
        $this->luu($sv[0], $a, khai::MOT, [$sv[1]->id], false);
        $tb = array_values(array_filter($sink->get_messages(), fn($m) => $m->component === 'local_fitnhom'));
        $sink->close();
        $this->assertCount(1, $tb);
        $m = $tb[0];
        $this->assertSame('khongcon', $m->eventtype);
        $this->assertSame((int) $sv[1]->id, (int) $m->useridto);
        $this->assertSame(\core_user::NOREPLY_USER, (int) $m->useridfrom);
        $this->assertSame(0, $DB->count_records('local_fitnhom_tv', ['assignid' => $aid]));
        $this->assertSame(khai::MOT, khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id)->loai);

        $ten = $a->get_instance()->name;
        $ky = get_string('tb_kyten', 'local_fitnhom', 'FIT-MOODLE');
        $this->assertStringStartsWith(strtok(get_string('tb_khongcon', 'local_fitnhom'), "\n"), $m->fullmessage);
        foreach ([$m->fullmessage, $m->fullmessagehtml] as $chu) {
            $this->assertStringContainsString($ten, $chu);
            $this->assertStringContainsString($ky, $chu);
            $this->assertStringContainsString('/mod/assign/view.php', $chu);
            $this->assertStringNotContainsString('/local/fitnhom/tuchoi.php', $chu);
        }
        $this->assertStringContainsString(fullname($sv[0]), $m->subject);

        // Chữ tiếng Việt (máy kiểm chạy tiếng Anh): chào, "không còn liệt kê", ký; không "khai".
        $string = [];
        include(__DIR__ . '/../lang/vi/local_fitnhom.php');
        foreach (['tb_khongcon', 'tb_khongcon_html'] as $khoa) {
            $this->assertStringStartsWith(str_contains($khoa, 'html') ? '<p>Chào em,</p>' : 'Chào em,', $string[$khoa]);
            $this->assertStringContainsString('{$a->ky}', $string[$khoa]);
        }
        foreach (['tb_khongcon', 'tb_khongcon_html', 'tb_khongcon_tieude', 'messageprovider:khongcon'] as $khoa) {
            $this->assertStringContainsString('liệt kê', $string[$khoa]);
            $this->assertStringNotContainsString('khai', $string[$khoa]);
        }
    }

    /**
     * Lưu câu khai qua trang nộp bài, trả về thư Fit-Nhóm đã gửi: [người nhận => loại].
     *
     * @param \stdClass $u Người nộp.
     * @param \assign $a Bài tập.
     * @param string $loai MOT | NHOM.
     * @param array $ban Các bạn.
     * @param bool $hung Bắt thư (false: để bên ngoài bắt).
     * @return array
     */
    private function luu(\stdClass $u, \assign $a, string $loai, array $ban, bool $hung = true): array {
        $this->setUser($u);
        $data = (object) ['userid' => $u->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
            'text' => 'Bài của ' . $u->id, 'format' => FORMAT_HTML], 'fitnhom_loai' => $loai, 'fitnhom_tv' => $ban];
        $loi = [];
        $sink = $hung ? $this->redirectMessages() : null;
        $this->assertTrue((bool) $a->save_submission($data, $loi), implode(' ', (array) $loi));
        if (!$sink) {
            return [];
        }
        $kq = [];
        foreach ($sink->get_messages() as $m) {
            if ($m->component === 'local_fitnhom') {
                $kq[(int) $m->useridto] = $m->eventtype;
            }
        }
        $sink->close();
        ksort($kq);
        return $kq;
    }
}
