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
 * Hàm dùng cho các bước nâng cấp của local_fitnhom (không gọi mã dịch vụ — chỉ đọc / ghi cấu hình và bảng).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * 0.2.5: ghi "Lúc bật" còn thiếu. 0.1–0.2.4 đổi Chế độ trên trang cài đặt không ghi được mốc này (hàm gọi lại ở lib.php chưa
 * được nạp), nên trang *Thành viên nhóm*, *Theo course*, Quét ngay coi như chưa bật. Đang ở Thử nghiệm / Bật toàn trang mà chưa
 * có mốc thì lấy lúc của câu khai sớm nhất (câu khai chỉ có khi Fit-Nhóm đã bật); chưa có câu khai nào thì lấy $luc.
 *
 * @param int $luc Lúc nâng cấp.
 * @return int Mốc đã ghi (0 = không phải ghi).
 */
function local_fitnhom_upgrade_ghi_luc_bat(int $luc): int {
    global $DB;
    $chedo = (string) get_config('local_fitnhom', 'chedo');
    if (!in_array($chedo, ['THU', 'BAT'], true) || (int) get_config('local_fitnhom', 'batdau')) {
        return 0;
    }
    $som = (int) $DB->get_field_sql('SELECT MIN(timecreated) FROM {local_fitnhom_khai}');
    $moc = $som ?: $luc;
    set_config('batdau', $moc, 'local_fitnhom');
    return $moc;
}
