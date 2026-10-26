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
 * Bài nộp chưa có câu khai (TL54 mục 5): bắt lúc nộp, Quét ngay chỉ từ lúc bật, hộp nổi / dải nhắc, khai một bấm.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\cho
 * @covers     \local_fitnhom\observer
 * @covers     \local_fitnhom\hook_callbacks
 * @covers     \local_fitnhom\tinhhinh
 */
final class cho_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Nộp không có câu khai (như ứng dụng điện thoại) → vào danh sách chờ ngay; khai ở hộp nổi → ra khỏi danh sách.
     */
    public function test_bat_luc_nop(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(2);
        [$ok] = $this->nop($sv[0], $a);
        $this->assertTrue($ok);
        $ds = cho::cua((int) $sv[0]->id);
        $this->assertCount(1, $ds);
        $this->assertSame(format_string($a->get_instance()->name), $ds[0]['bai']);
        $d = hook_callbacks::du_lieu($ds);
        $this->assertSame(1, $d['so']);
        $html = $GLOBALS['OUTPUT']->render_from_template('local_fitnhom/dai', $d);
        $this->assertStringContainsString('local-fitnhom-hopnoi', $html);
        $this->assertStringContainsString('value="mot"', $html);
        khai::luu($this->bai_nop($a, $sv[0]), khai::MOT, [], khai::HOPNOI, time(), (int) $sv[0]->id);
        $this->assertSame([], cho::cua((int) $sv[0]->id));
        $this->assertSame(khai::HOPNOI, $DB->get_field('local_fitnhom_khai', 'nguon', []));
    }

    /**
     * Quét ngay: chỉ bài nộp đã gửi từ lúc bật, ở course áp dụng; bài trước lúc bật không hỏi (Q7); chế độ Tắt không quét.
     */
    public function test_quet_ngay(): void {
        global $DB;
        $this->resetAfterTest();
        [, $a, $sv] = $this->bai_tap(3);
        // Nộp khi plugin đang tắt (chưa bật lần nào).
        $this->nop($sv[0], $a);
        $DB->set_field('assign_submission', 'timemodified', time() - DAYSECS, ['userid' => $sv[0]->id]);
        $this->assertSame(0, cho::quet(time(), 2), 'Tắt: không quét');
        $this->che_do(phamvi::BAT);
        $this->assertSame(0, cho::quet(time(), 2), 'Bài trước lúc bật không hỏi');
        // Tạm tắt rồi bật lại: bài nộp lúc tắt (sau lúc bật) được quét.
        $this->che_do(phamvi::TAT);
        $this->nop($sv[1], $a);
        $this->che_do(phamvi::BAT);
        $ev = $this->redirectEvents();
        $this->assertSame(1, cho::quet(time(), 2));
        $this->assertCount(1, array_filter($ev->get_events(), fn($e) => $e instanceof event\quet_ngay));
        $ev->close();
        $this->assertSame(0, cho::quet(time(), 2), 'Quét lại không trùng');
        $t = tinhhinh::so_lieu();
        $this->assertSame(1, $t['cho']);
        $this->assertSame(1, $t['bainop']);
        $this->assertTrue($t['subplugin']);
        $this->assertGreaterThan(0, $t['quetluc']);
        // Course không áp dụng: không quét.
        set_config('loaitru_mau', 'FITCNL:%' . "\n" . $a->get_course()->shortname, 'local_fitnhom');
        phamvi::xoa_dem();
        $DB->delete_records('local_fitnhom_cho');
        $this->assertSame(0, cho::quet(time(), 2));
    }
}
