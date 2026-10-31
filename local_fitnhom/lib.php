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
    \local_fitnhom\cho::xoa_dem();
}

/**
 * Đổi cấu hình phạm vi (danh sách thử, loại trừ): xóa đệm phạm vi trong lượt chạy.
 *
 * @param string $ten Tên cấu hình.
 * @return void
 */
function local_fitnhom_phamvi_doi($ten) {
    \local_fitnhom\phamvi::xoa_dem();
}

/**
 * Mục *Thành viên nhóm (Fit-Nhóm)* ở điều hướng course (menu *Thêm*) cho người chấm được bài (giảng viên, trợ giảng — TL60
 * mục 5.1, Q8). Sinh viên không thấy.
 *
 * @param navigation_node $nav Nút điều hướng course.
 * @param stdClass $course Course.
 * @param context_course $ctx Ngữ cảnh course.
 * @return void
 */
function local_fitnhom_extend_navigation_course(navigation_node $nav, stdClass $course, context_course $ctx) {
    if ((int) $course->id <= SITEID || !has_capability('mod/assign:grade', $ctx)) {
        return;
    }
    $nav->add(
        get_string('nav_course', 'local_fitnhom'),
        new moodle_url('/local/fitnhom/course.php', ['id' => $course->id]),
        navigation_node::TYPE_SETTING,
        null,
        'local_fitnhom_course',
        new pix_icon('i/group', '')
    );
}
