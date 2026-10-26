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

/**
 * Dựng dữ liệu cho bài kiểm Fit-Nhóm: course, bài tập (văn bản trực tuyến), sinh viên.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/tests/generator.php');

/**
 * Hàm dựng dùng chung.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait local_fitnhom_dung {
    use mod_assign_test_generator;

    /**
     * Bật chế độ (ghi lúc bật như trang quản trị).
     *
     * @param string $chedo TAT | THU | BAT.
     * @param string $khoathu Danh sách thử.
     * @return void
     */
    protected function che_do(string $chedo, string $khoathu = ''): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/fitnhom/lib.php');
        set_config('chedo', $chedo, 'local_fitnhom');
        set_config('khoathu', $khoathu, 'local_fitnhom');
        set_config('loaitru_mau', 'FITCNL:%', 'local_fitnhom');
        local_fitnhom_chedo_doi('chedo');
        \local_fitnhom\phamvi::xoa_dem();
    }

    /**
     * Course + bài tập + n sinh viên + giảng viên.
     *
     * @param int $n Số sinh viên.
     * @param array $p Tham số bài tập thêm.
     * @param array $c Tham số course.
     * @return array [course, assign (testable), sinh viên[], giảng viên]
     */
    protected function bai_tap(int $n = 4, array $p = [], array $c = []): array {
        global $CFG;
        require_once($CFG->dirroot . '/local/fitnhom/lib.php');
        $g = $this->getDataGenerator();
        $course = $g->create_course($c);
        $sv = [];
        for ($i = 0; $i < $n; $i++) {
            $sv[] = $g->create_and_enrol($course, 'student', ['idnumber' => 'SV' . (2300 + $i)]);
        }
        $gv = $g->create_and_enrol($course, 'editingteacher');
        $a = $this->create_instance($course, $p + ['assignsubmission_onlinetext_enabled' => 1, 'submissiondrafts' => 0]);
        return [$course, $a, $sv, $gv];
    }

    /**
     * Em nộp bài (văn bản) kèm dữ liệu câu khai (có thể không có — như ứng dụng điện thoại).
     *
     * @param \stdClass $u Sinh viên.
     * @param \assign $a Bài tập.
     * @param array $khai ['fitnhom_loai' => …, 'fitnhom_tv' => […]] hay [].
     * @return array [kết quả, thông báo lỗi]
     */
    protected function nop(\stdClass $u, \assign $a, array $khai = []): array {
        $this->setUser($u);
        $data = (object) (['userid' => $u->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
            'text' => 'Bài của ' . $u->id, 'format' => FORMAT_HTML]] + $khai);
        $loi = [];
        $sink = $this->redirectMessages();
        $kq = $a->save_submission($data, $loi);
        $sink->close();
        return [$kq, $loi];
    }

    /**
     * Bài nộp mới nhất của em.
     *
     * @param \assign $a Bài tập.
     * @param \stdClass $u Sinh viên.
     * @return \stdClass
     */
    protected function bai_nop(\assign $a, \stdClass $u): \stdClass {
        return $a->get_user_submission($u->id, false);
    }
}
