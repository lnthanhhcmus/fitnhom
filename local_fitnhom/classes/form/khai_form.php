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

namespace local_fitnhom\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Biểu mẫu khai của một bài nộp ở trang Fit-Nhóm (hộp nổi › Bài nhóm…): làm một mình / bài nhóm cùng các bạn.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class khai_form extends \moodleform {
    /**
     * Định nghĩa: customdata 'ungvien' (userid => nhãn), 'goiy' (chữ, có thể rỗng).
     *
     * @return void
     */
    protected function definition() {
        $m = $this->_form;
        $m->addElement('hidden', 'id');
        $m->setType('id', PARAM_INT);
        $m->addElement('hidden', 've');
        $m->setType('ve', PARAM_LOCALURL);
        \local_fitnhom\khai::them_cau_hoi($m, $this->_customdata['ungvien'], (string) ($this->_customdata['goiy'] ?? ''), true);
        $this->add_action_buttons(true, get_string('luu', 'local_fitnhom'));
    }

    /**
     * Bài nhóm phải chọn ít nhất một bạn.
     *
     * @param array $data Dữ liệu.
     * @param array $files Tệp.
     * @return array
     */
    public function validation($data, $files) {
        $loi = parent::validation($data, $files);
        if (($data['fitnhom_loai'] ?? '') === \local_fitnhom\khai::NHOM && empty($data['fitnhom_tv'])) {
            $loi['fitnhom_tv'] = get_string('loi_chuachonban', 'local_fitnhom');
        }
        return $loi;
    }
}
