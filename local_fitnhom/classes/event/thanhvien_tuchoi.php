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
 * Bạn được khai bấm Không phải em (hay hoàn tác).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class thanhvien_tuchoi extends \core\event\base {
    /**
     * Khởi tạo.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_fitnhom_tv';
    }

    /**
     * Tên sự kiện.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('ev_thanhvien_tuchoi', 'local_fitnhom');
    }

    /**
     * Mô tả (không chép tên người được khai).
     *
     * @return string
     */
    public function get_description() {
        return "Người dùng {$this->userid} " . (empty($this->other['hoantac']) ? 'bấm "Không phải em"' : 'hoàn tác "Không phải em"')
            . " ở dòng thành viên {$this->objectid} (bài nộp của người dùng {$this->relateduserid}).";
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
