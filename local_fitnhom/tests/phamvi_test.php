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
 * Chế độ, danh sách thử, loại trừ (TL54 mục 4).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\phamvi
 */
final class phamvi_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Tắt (mặc định) / thử nghiệm / bật; mẫu và danh mục loại trừ; Nộp theo nhóm không hỏi; lúc bật ghi một lần.
     */
    public function test_che_do_va_pham_vi(): void {
        $this->resetAfterTest();
        $g = $this->getDataGenerator();
        $a = $g->create_course(['shortname' => 'CSC10001_1']);
        $b = $g->create_course(['shortname' => 'CSC10002_1']);
        $cnl = $g->create_course(['shortname' => 'FITCNL:23CTT1']);
        $dm = $g->create_category();
        $con = $g->create_category(['parent' => $dm->id]);
        $shc = $g->create_course(['shortname' => 'SHC', 'category' => $con->id]);
        $this->bai_tap(0);
        $this->assertSame(phamvi::TAT, phamvi::che_do(), 'Mới cài: Tắt');
        $this->assertFalse(phamvi::course_ap_dung((int) $a->id));
        $this->assertSame(0, phamvi::bat_dau());
        $this->che_do(phamvi::THU, " csc10001_1 \n");
        $this->assertTrue(phamvi::course_ap_dung((int) $a->id), 'Không phân biệt hoa thường, cắt khoảng trắng');
        $this->assertFalse(phamvi::course_ap_dung((int) $b->id));
        $bd = phamvi::bat_dau();
        $this->assertGreaterThan(0, $bd);
        $this->che_do(phamvi::BAT);
        $this->assertSame($bd, phamvi::bat_dau(), 'Lúc bật không đổi khi chuyển chế độ');
        $this->assertTrue(phamvi::course_ap_dung((int) $b->id));
        $this->assertFalse(phamvi::course_ap_dung((int) $cnl->id), 'Mẫu FITCNL:% mặc định');
        $this->assertFalse(phamvi::course_ap_dung(SITEID));
        set_config('loaitru_danhmuc', (string) $dm->id, 'local_fitnhom');
        phamvi::xoa_dem();
        $this->assertFalse(phamvi::course_ap_dung((int) $shc->id), 'Danh mục con cũng loại');
        $this->assertFalse(phamvi::assign_ap_dung((object) ['course' => $b->id, 'teamsubmission' => 1]));
        $this->assertTrue(phamvi::assign_ap_dung((object) ['course' => $b->id, 'teamsubmission' => 0]));
        $this->assertTrue(phamvi::khop('CSC10001_23CLC', 'csc1000_%'));
        $this->assertFalse(phamvi::khop('CSC10001', 'csc1000_x%'));
        $this->che_do(phamvi::TAT);
        $this->assertFalse(phamvi::course_ap_dung((int) $b->id));
        $this->assertSame($bd, phamvi::bat_dau());
    }
}
