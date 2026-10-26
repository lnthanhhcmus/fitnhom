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

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use local_fitnhom\privacy\provider;

/**
 * Riêng tư: ngữ cảnh, xuất, xóa.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\privacy\provider
 */
final class privacy_test extends \core_privacy\tests\provider_testcase {
    use \local_fitnhom_dung;

    /**
     * Người nộp và bạn được khai có ngữ cảnh bài tập; xuất được; xóa một người không đụng người kia.
     */
    public function test_xuat_xoa(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(3);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $this->nop($sv[2], $a);
        $ctx = $a->get_context();
        foreach ([0, 1, 2] as $i) {
            $this->assertContains((int) $ctx->id, array_map(
                'intval',
                provider::get_contexts_for_userid((int) $sv[$i]->id)->get_contextids()
            ));
        }
        $this->export_context_data_for_user((int) $sv[1]->id, $ctx, 'local_fitnhom');
        $d = writer::with_context($ctx)->get_data([get_string('pluginname', 'local_fitnhom')]);
        $this->assertSame(khai::KHAI, $d->duockhai[0]['trangthai']);
        provider::delete_data_for_user(new approved_contextlist($sv[1], 'local_fitnhom', [(int) $ctx->id]));
        $this->assertSame(0, $DB->count_records('local_fitnhom_tv'));
        $this->assertSame(1, $DB->count_records('local_fitnhom_khai'));
        provider::delete_data_for_all_users_in_context($ctx);
        $this->assertSame(0, $DB->count_records('local_fitnhom_khai') + $DB->count_records('local_fitnhom_cho'));
    }
}
