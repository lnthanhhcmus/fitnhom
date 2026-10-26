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
 * Hàm chung của local_fitnhom.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Gọi khi admin đổi Chế độ: ghi lúc bật lần đầu (Quét ngay chỉ xét bài nộp từ lúc này); không đặt lại khi tắt rồi bật lại.
 *
 * @param string $ten Tên cấu hình.
 * @return void
 */
function local_fitnhom_chedo_doi($ten) {
    if (get_config('local_fitnhom', 'chedo') !== 'TAT' && !get_config('local_fitnhom', 'batdau')) {
        set_config('batdau', time(), 'local_fitnhom');
    }
    \local_fitnhom\phamvi::xoa_dem();
}
