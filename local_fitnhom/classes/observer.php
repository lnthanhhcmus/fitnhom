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
 * Lắng nghe sự kiện nộp bài của Moodle: bài nộp chưa có câu khai (ứng dụng điện thoại, dịch vụ web) vào danh sách chờ.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Một bài nộp vừa gửi.
     *
     * @param \mod_assign\event\assessable_submitted $e Sự kiện.
     * @return void
     */
    public static function da_nop(\mod_assign\event\assessable_submitted $e): void {
        try {
            cho::them((int) $e->objectid, (int) $e->timecreated);
        } catch (\Throwable $ex) {
            // Fit-Nhóm không được làm hỏng việc nộp bài.
            debugging('local_fitnhom: ' . $ex->getMessage(), DEBUG_DEVELOPER);
        }
    }
}
