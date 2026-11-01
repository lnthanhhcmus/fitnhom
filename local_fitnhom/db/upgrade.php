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
    require_once(__DIR__ . '/upgradelib.php');
    if ($oldversion < 2026110100) {
        // 0.2: ba cấu hình mới (định dạng course loại trừ, ngưỡng khối Cần xem). Không đổi bảng.
        foreach (['loaitru_format' => 'fitcnl', 'batthuong_tuchoi' => 3, 'batthuong_khai' => 5] as $ten => $macdinh) {
            if (get_config('local_fitnhom', $ten) === false) {
                set_config($ten, $macdinh, 'local_fitnhom');
            }
        }
        upgrade_plugin_savepoint(true, 2026110100, 'local', 'fitnhom');
    }
    if ($oldversion < 2026110101) {
        // 0.2.1: tên người gửi thông báo (mặc định FIT-MOODLE — góp ý chủ dự án 01/11/2026). Không đổi bảng.
        if (get_config('local_fitnhom', 'nguoigui') === false) {
            set_config('nguoigui', 'FIT-MOODLE', 'local_fitnhom');
        }
        upgrade_plugin_savepoint(true, 2026110101, 'local', 'fitnhom');
    }
    if ($oldversion < 2026110105) {
        // 0.2.5: "Lúc bật" chưa được ghi khi đổi Chế độ trên trang cài đặt (0.1–0.2.4) — ghi bù. Không đổi bảng.
        local_fitnhom_upgrade_ghi_luc_bat(time());
        upgrade_plugin_savepoint(true, 2026110105, 'local', 'fitnhom');
    }
    return true;
}
