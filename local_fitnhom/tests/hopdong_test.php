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
 * Giữ hợp đồng với plugin khác (TL54 mục 6). Đổi bài này nghĩa là đổi hợp đồng: phải tăng api::HOPDONG.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\api
 */
final class hopdong_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * HOPDONG = 1; dang_bat theo chế độ; chữ ký hàm.
     */
    public function test_hop_dong_1(): void {
        $this->resetAfterTest();
        $this->assertSame(1, api::HOPDONG);
        $this->assertFalse(api::dang_bat());
        $this->che_do(phamvi::THU, 'X');
        $this->assertTrue(api::dang_bat());
        $r = new \ReflectionMethod(api::class, 'thanhvien');
        $this->assertTrue($r->isStatic() && $r->isPublic());
        $this->assertSame('array', (string) $r->getReturnType());
        $this->assertSame('int', (string) $r->getParameters()[0]->getType());
    }

    /**
     * thanhvien(): chỉ bài nộp đã gửi; bỏ người nộp, bỏ bạn "Không phải em"; một bạn nhiều bài khai → bài nộp sớm nhất; bạn
     * có bài nộp riêng vẫn có mặt; dạng [userid => ['nguoinop' => int, 'luc' => int]].
     */
    public function test_thanh_vien(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(5, ['submissiondrafts' => 1]);
        $aid = (int) $a->get_instance()->id;
        // A (sv0) nộp nháp khai B, C — chưa gửi thì chưa tính.
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id, $sv[2]->id]]);
        $this->assertSame([], api::thanhvien($aid));
        $this->submit_for_grading($sv[0], $a);
        $sa = $this->bai_nop($a, $sv[0]);
        $DB->set_field('assign_submission', 'timemodified', 1000, ['id' => $sa->id]);
        // B (sv1) cũng nộp, khai A và D (sv3), gửi sau.
        $this->nop($sv[1], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[0]->id, $sv[3]->id]]);
        $this->submit_for_grading($sv[1], $a);
        $DB->set_field('assign_submission', 'timemodified', 2000, ['id' => $this->bai_nop($a, $sv[1])->id]);
        // C từ chối.
        $k = khai::cua_bai_nop((int) $sa->id);
        khai::tu_choi((int) $k->tv[(int) $sv[2]->id]->id, (int) $sv[2]->id, false, time());
        $tv = api::thanhvien($aid);
        ksort($tv);
        $this->assertSame([
            (int) $sv[0]->id => ['nguoinop' => (int) $sv[1]->id, 'luc' => 2000],
            (int) $sv[1]->id => ['nguoinop' => (int) $sv[0]->id, 'luc' => 1000],
            (int) $sv[3]->id => ['nguoinop' => (int) $sv[1]->id, 'luc' => 2000],
        ], $tv, 'Không có C (từ chối), không có E (không ai khai)');
        $this->assertArrayNotHasKey((int) $sv[2]->id, $tv);
    }
}
