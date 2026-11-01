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
 * Bản 0.2.5 (chủ dự án cài thử 01/11/2026 16:24): đổi Chế độ trên trang cài đặt không ghi "Lúc bật" (hàm gọi lại ở lib.php
 * chưa được nạp — Moodle bỏ qua lặng lẽ) → trang *Thành viên nhóm* báo "Chưa có bài nộp nào" dù bảng chấm đã có câu khai.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\phamvi
 * @covers     \local_fitnhom\baocao
 */
final class ban025_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Lưu Chế độ qua trang cài đặt (admin_write_settings, như admin bấm Lưu) ghi "Lúc bật"; hàm gọi lại là hàm của lớp (tự nạp,
     * không cần lib.php). Tắt rồi bật lại không đổi mốc.
     */
    public function test_trang_cai_dat_ghi_luc_bat(): void {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('chedo', phamvi::TAT, 'local_fitnhom');
        unset_config('batdau', 'local_fitnhom');

        $root = admin_get_root(true, true);
        $cd = null;
        foreach ($root->locate('local_fitnhom')->settings as $st) {
            if ($st->get_full_name() === 's_local_fitnhom_chedo') {
                $cd = $st;
            }
        }
        $this->assertNotNull($cd);
        $this->assertStringContainsString('::', (string) $cd->updatedcallback, 'Hàm của lớp, không phải hàm ở lib.php');
        $this->assertTrue(is_callable($cd->updatedcallback));

        $truoc = time();
        admin_write_settings((object) ['s_local_fitnhom_chedo' => phamvi::THU]);
        $this->assertSame(phamvi::THU, phamvi::che_do());
        $bd = phamvi::bat_dau();
        $this->assertGreaterThanOrEqual($truoc, $bd);

        admin_write_settings((object) ['s_local_fitnhom_chedo' => phamvi::TAT]);
        admin_write_settings((object) ['s_local_fitnhom_chedo' => phamvi::BAT]);
        $this->assertSame($bd, phamvi::bat_dau(), 'Lúc bật không đổi khi tắt rồi bật lại');
    }

    /**
     * Bước nâng cấp 2026110105 ghi bù "Lúc bật": đang Thử nghiệm / Bật mà thiếu mốc → lúc câu khai sớm nhất (chưa có câu khai →
     * lúc nâng cấp); đã có mốc hay đang Tắt → không đổi.
     */
    public function test_nang_cap_ghi_bu_luc_bat(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/fitnhom/db/upgradelib.php');
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(2);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::MOT]);
        $DB->set_field('local_fitnhom_khai', 'timecreated', 1700000000, []);

        set_config('chedo', phamvi::TAT, 'local_fitnhom');
        unset_config('batdau', 'local_fitnhom');
        $this->assertSame(0, local_fitnhom_upgrade_ghi_luc_bat(1800000000), 'Đang Tắt: không ghi');
        $this->assertFalse(get_config('local_fitnhom', 'batdau'));

        set_config('chedo', phamvi::THU, 'local_fitnhom');
        $this->assertSame(1700000000, local_fitnhom_upgrade_ghi_luc_bat(1800000000));
        $this->assertSame(1700000000, phamvi::bat_dau());
        $this->assertSame(0, local_fitnhom_upgrade_ghi_luc_bat(1800000000), 'Đã có mốc: không đổi');

        $DB->delete_records('local_fitnhom_khai');
        unset_config('batdau', 'local_fitnhom');
        $this->assertSame(1800000000, local_fitnhom_upgrade_ghi_luc_bat(1800000000), 'Chưa có câu khai: lúc nâng cấp');
    }

    /**
     * Trang *Thành viên nhóm* (theo bài) và *Theo course* luôn đếm bài nộp đã có câu khai — kể cả khi thiếu mốc hay mốc muộn
     * hơn bài nộp; bài nộp không câu khai trước mốc vẫn không đếm.
     */
    public function test_bai_co_cau_khai_luon_duoc_dem(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [$course, $a, $sv] = $this->bai_tap(3);
        $this->nop($sv[1], $a, ['fitnhom_loai' => khai::MOT]);
        $aid = (int) $a->get_instance()->id;
        // Bài của sv[0] nộp trước khi bật (không câu khai).
        set_config('chedo', phamvi::TAT, 'local_fitnhom');
        phamvi::xoa_dem();
        $this->nop($sv[0], $a);
        $DB->set_field('assign_submission', 'timemodified', 1000, ['assignment' => $aid, 'userid' => $sv[0]->id]);
        set_config('chedo', phamvi::BAT, 'local_fitnhom');
        phamvi::xoa_dem();

        unset_config('batdau', 'local_fitnhom');
        $ds = baocao::theo_bai((int) $course->id);
        $this->assertCount(1, $ds, 'Thiếu mốc: bài có câu khai vẫn được đếm');
        $this->assertSame(1, $ds[0]['nop']);
        $this->assertSame(1, $ds[0]['mot']);
        $tc = baocao::theo_course();
        $this->assertSame(1, $tc[0]['nop']);

        set_config('batdau', time() + 3600, 'local_fitnhom');
        $this->assertSame(1, baocao::theo_bai((int) $course->id)[0]['nop'], 'Mốc muộn hơn bài nộp: vẫn đếm bài có câu khai');

        set_config('batdau', 500, 'local_fitnhom');
        $this->assertSame(2, baocao::theo_bai((int) $course->id)[0]['nop'], 'Mốc sớm: đếm cả bài chưa khai');
    }
}
