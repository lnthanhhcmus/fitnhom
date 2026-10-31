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

namespace local_fitnhom\event;

/**
 * Giảng viên / trợ giảng mở trang *Thành viên nhóm* của course hay tải Excel (TL60 mục 5.1). Không tên sinh viên.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_xem extends \core\event\base {
    /**
     * Khởi tạo.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Tên sự kiện.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_course_xem', 'local_fitnhom');
    }

    /**
     * Mô tả (không tên sinh viên).
     *
     * @return string
     */
    public function get_description() {
        $bai = (int) ($this->other['assignid'] ?? 0);
        return "Người dùng {$this->userid} " . (!empty($this->other['tai']) ? 'tải Excel' : 'mở trang')
            . " Thành viên nhóm của course {$this->courseid}" . ($bai ? " (bài tập {$bai})" : '') . '.';
    }

    /**
     * Trang đã xem.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/fitnhom/course.php', ['id' => $this->courseid]);
    }

    /**
     * Không ánh xạ khi khôi phục nhật ký (dữ liệu Fit-Nhóm không vào bản sao lưu course).
     *
     * @return bool
     */
    public static function get_objectid_mapping() {
        return false;
    }

    /**
     * Không ánh xạ trường other.
     *
     * @return bool
     */
    public static function get_other_mapping() {
        return false;
    }
}
