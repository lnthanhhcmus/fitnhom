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

namespace assignsubmission_fitnhom;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/fitnhom/tests/helper.php');

/**
 * Plugin nộp bài: không cấu hình được, không tệp, không tính là bài nộp, cột bảng chấm, biểu mẫu có câu hỏi bắt buộc.
 *
 * @package    assignsubmission_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \assign_submission_fitnhom
 */
final class plugin_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Thuộc tính cố định; biểu mẫu nộp bài của em có câu hỏi (bắt buộc), có điền sẵn theo bạn đã nộp; cột bảng chấm.
     */
    public function test_plugin(): void {
        $this->resetAfterTest();
        $this->che_do(\local_fitnhom\phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(3);
        $p = $a->get_submission_plugin_by_type('fitnhom');
        $this->assertFalse($p->is_configurable());
        $this->assertSame([], $p->get_file_areas());
        $this->assertSame(get_string('tencot', 'assignsubmission_fitnhom'), $p->get_name());
        $this->assertTrue($p->submission_is_empty((object) ['fitnhom_loai' => 'MOT']));
        $this->nop($sv[0], $a, ['fitnhom_loai' => 'NHOM', 'fitnhom_tv' => [$sv[1]->id]]);
        // Biểu mẫu của bạn B: câu hỏi bắt buộc, điền sẵn bài nhóm cùng A.
        $this->setUser($sv[1]);
        $m = new \MoodleQuickForm('f', 'post', '');
        $d = new \stdClass();
        $p->get_form_elements_for_user(null, $m, $d, $sv[1]->id);
        $this->assertTrue($m->elementExists('fitnhom_loai'));
        $this->assertTrue($m->elementExists('fitnhom_tv'));
        $this->assertTrue($m->elementExists('fitnhom_goiy'));
        $this->assertTrue($m->isElementRequired('fitnhom_loai'));
        $this->assertSame('NHOM', $d->fitnhom_loai);
        $this->assertSame([(int) $sv[0]->id], $d->fitnhom_tv);
        $show = true;
        $tt = $p->view_summary($a->get_user_submission($sv[0]->id, false), $show);
        $this->assertFalse($show);
        $this->assertStringContainsString(fullname($sv[1]), $tt);
        // Người khác (nộp thay) mở biểu mẫu: không bắt buộc.
        $this->setAdminUser();
        $m2 = new \MoodleQuickForm('f2', 'post', '');
        $p->get_form_elements_for_user(null, $m2, new \stdClass(), $sv[2]->id);
        $this->assertFalse($m2->isElementRequired('fitnhom_loai'));
        $this->assertSame('privacy:metadata', \assignsubmission_fitnhom\privacy\provider::get_reason());
    }
}
