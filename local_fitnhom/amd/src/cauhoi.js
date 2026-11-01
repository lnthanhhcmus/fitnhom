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
 * Câu "Thành viên nhóm": em bấm Lưu khi chưa chọn thì trang tải lại kèm dòng đỏ "Em chọn …" (lỗi do máy chủ dựng — Moodle
 * không tự xóa khi em chọn sau đó). Em chọn một trong hai nút thì tắt dòng đỏ (0.2.4 — góp ý chủ dự án 01/11/2026 15:11).
 *
 * @module     local_fitnhom/cauhoi
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = () => {
    document.addEventListener('change', (e) => {
        const nut = e.target;
        if (!nut || nut.name !== 'fitnhom_loai' || !nut.checked) {
            return;
        }
        // Khung nhóm (mỗi nút còn có khung .fitem riêng bên trong, dòng đỏ ở khung nhóm).
        const khung = nut.closest('[data-groupname="fitnhom_loai"]');
        if (!khung) {
            return;
        }
        khung.classList.remove('has-danger');
        khung.querySelectorAll('.form-control-feedback').forEach((dong) => {
            dong.textContent = '';
            dong.style.display = 'none';
        });
        khung.querySelectorAll('input[name="fitnhom_loai"]').forEach((r) => {
            r.classList.remove('is-invalid');
            r.removeAttribute('aria-invalid');
        });
    });
};
