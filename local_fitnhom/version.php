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
 * Fit-Nhóm — khai thành viên nhóm khi nộp bài trên Moodle (phần chính).
 *
 * Vì sao có plugin này: bài nhóm thường chỉ một bạn nộp, nên Moodle (và FIT-CNL đọc dữ liệu Moodle) coi các bạn còn lại là
 * chưa nộp. Người nộp khai "làm một mình" hay "bài nhóm cùng các bạn…" ngay lúc nộp (assignsubmission_fitnhom). Thiết kế:
 * docs/TL54 trong kho github.com/lnthanhhcmus/fitnhom.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_fitnhom';
$plugin->version   = 2026102700;
$plugin->requires  = 2024100700;
$plugin->maturity  = MATURITY_BETA;
$plugin->release   = '0.1';
