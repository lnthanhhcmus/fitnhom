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
 * Trang *Thành viên nhóm (Fit-Nhóm)* của một course — cho người chấm được bài (giảng viên, trợ giảng; TL60 mục 5.1, Q8).
 * Vào từ menu *Thêm* của course hay liên kết ở đầu cột *Thành viên nhóm* của bảng chấm.
 *
 *   course.php?id=<courseid>                    bảng theo bài tập
 *   course.php?id=<courseid>&assign=<assignid>  chi tiết một bài
 *   course.php?id=<courseid>&tai=1&sesskey=…    tải Excel (hai sheet)
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$assignid = optional_param('assign', 0, PARAM_INT);
$tai = optional_param('tai', 0, PARAM_BOOL);
$course = get_course($id);
require_login($course);
$ctx = \local_fitnhom\output\course::kiem_quyen((int) $course->id);

if ($tai) {
    require_sesskey();
    \local_fitnhom\event\course_xem::create(['context' => $ctx, 'courseid' => $course->id,
        'other' => ['tai' => 1, 'assignid' => 0]])->trigger();
    \local_fitnhom\baocao::tai_excel(
        \local_fitnhom\baocao::bang_course((int) $course->id),
        'FitNhom_' . $course->shortname . '_' . userdate(time(), '%Y%m%d', 99, false)
    );
}

$url = new moodle_url('/local/fitnhom/course.php', ['id' => $course->id] + ($assignid ? ['assign' => $assignid] : []));
$PAGE->set_url($url);
$PAGE->set_context($ctx);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('course_tieude', 'local_fitnhom') . ' · ' . format_string($course->shortname));
$PAGE->set_heading(format_string($course->fullname));
\local_fitnhom\event\course_xem::create(['context' => $ctx, 'courseid' => $course->id,
    'other' => ['tai' => 0, 'assignid' => $assignid]])->trigger();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('course_tieude', 'local_fitnhom'));
echo $OUTPUT->render_from_template(
    'local_fitnhom/course',
    (new \local_fitnhom\output\course((int) $course->id, $assignid))->export_for_template($OUTPUT)
);
echo $OUTPUT->footer();
