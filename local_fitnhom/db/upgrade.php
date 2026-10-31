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
 * Nâng cấp CSDL của local_fitnhom. Bước cũ không sửa; bước mới thêm ở cuối (TL60 mục 5.6).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Nâng cấp.
 *
 * @param int $oldversion Phiên bản đang cài.
 * @return bool
 */
function xmldb_local_fitnhom_upgrade($oldversion) {
    if ($oldversion < 2026110100) {
        // 0.2: ba cấu hình mới (định dạng course loại trừ, ngưỡng khối Cần xem). Không đổi bảng.
        foreach (['loaitru_format' => 'fitcnl', 'batthuong_tuchoi' => 3, 'batthuong_khai' => 5] as $ten => $macdinh) {
            if (get_config('local_fitnhom', $ten) === false) {
                set_config($ten, $macdinh, 'local_fitnhom');
            }
        }
        upgrade_plugin_savepoint(true, 2026110100, 'local', 'fitnhom');
    }
    return true;
}
