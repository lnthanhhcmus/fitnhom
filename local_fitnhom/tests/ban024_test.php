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

/**
 * Bản 0.2.4 (chủ dự án 01/11/2026 15:11): dòng đỏ "Em chọn …" tắt khi em đã chọn. Lỗi do máy chủ dựng khi em bấm Lưu mà chưa
 * chọn — Moodle không tự xóa; trang có câu hỏi phải nạp mô-đun `local_fitnhom/cauhoi` (bấm chọn thì tắt dòng đỏ của khung
 * nhóm). Phần trình duyệt kiểm ở `dev/walk_024.py`.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\khai
 */
final class ban024_test extends \advanced_testcase {
    /**
     * Câu hỏi có hay không bắt buộc đều nạp mô-đun tắt dòng đỏ; tệp mô-đun đã dựng nhắm khung nhóm `fitnhom_loai`.
     */
    public function test_nap_mo_dun_tat_dong_do(): void {
        global $CFG, $PAGE;
        require_once($CFG->libdir . '/formslib.php');
        $this->resetAfterTest();
        $PAGE->set_url('/mod/assign/view.php');
        khai::them_cau_hoi(new \MoodleQuickForm('thu', 'post', ''), [], '', true);
        $this->assertStringContainsString("require(['local_fitnhom/cauhoi']", $PAGE->requires->get_end_code());

        $build = file_get_contents(__DIR__ . '/../amd/build/cauhoi.min.js');
        $this->assertStringContainsString('define("local_fitnhom/cauhoi"', $build);
        $this->assertStringContainsString('[data-groupname="fitnhom_loai"]', $build);
        $this->assertStringContainsString('form-control-feedback', $build);
    }

    /**
     * Ô "Các bạn cùng làm" ẩn khi nút "Bài nhóm" chưa được chọn — kể cả lúc chưa chọn nút nào (điều kiện 'notchecked' trên nút
     * NHOM; 'neq' của 0.1–0.2.3 bỏ qua nút chưa chọn nên ô vẫn hiện). Phần trình duyệt kiểm ở `dev/walk_024.py`.
     */
    public function test_o_cac_ban_an_khi_chua_chon_bai_nhom(): void {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        $this->resetAfterTest();
        $m = new \MoodleQuickForm('thu', 'post', '');
        khai::them_cau_hoi($m, [], '', true);
        $an = (new \ReflectionProperty($m, '_hideifs'))->getValue($m);
        $this->assertSame(['notchecked' => [khai::NHOM => ['fitnhom_tv']]], $an['fitnhom_loai']);
    }
}
