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
 * Hộp nổi "Em còn n bài cần khai thành viên" — nội dung có sẵn trong trang (template#local-fitnhom-hopnoi).
 *
 * @module     local_fitnhom/hopnoi
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Modal from 'core/modal';

export const init = () => {
    const tpl = document.getElementById('local-fitnhom-hopnoi');
    if (!tpl) {
        return;
    }
    Modal.create({title: tpl.dataset.title, body: tpl.innerHTML, large: true, show: true, removeOnClose: true});
};
