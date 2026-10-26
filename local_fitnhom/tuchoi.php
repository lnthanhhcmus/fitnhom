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
 * "Không phải em" (TL54 mục 3): bạn được khai xác nhận mình không cùng làm bài; hoàn tác trong 7 ngày.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_fitnhom\khai;

require_login(null, false);
$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$t = $DB->get_record('local_fitnhom_tv', ['id' => $id], '*', MUST_EXIST);
if ((int) $t->userid !== (int) $USER->id) {
    throw new moodle_exception('loi_khongphaicuaem', 'local_fitnhom');
}
$k = $DB->get_record('local_fitnhom_khai', ['id' => $t->khaiid], '*', MUST_EXIST);
$a = $DB->get_record('assign', ['id' => $k->assignid], 'id, course, name', MUST_EXIST);
$nop = core_user::get_user((int) $k->userid);
$url = new moodle_url('/local/fitnhom/tuchoi.php', ['id' => $id]);
if ($action === 'tuchoi' || $action === 'hoantac') {
    require_sesskey();
    khai::tu_choi($id, (int) $USER->id, $action === 'hoantac', time());
    redirect(
        $url,
        get_string($action === 'hoantac' ? 'dahoantac' : 'datuchoi', 'local_fitnhom'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}
$PAGE->set_context(context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('khongphaiem', 'local_fitnhom'));
$PAGE->set_heading(get_string('khongphaiem', 'local_fitnhom'));
$o = (object) ['ban' => $nop ? s(fullname($nop)) : '?', 'bai' => format_string($a->name),
    'mon' => format_string(get_course($a->course)->fullname)];
echo $OUTPUT->header();
if ($t->trangthai === khai::TUCHOI) {
    echo $OUTPUT->notification(get_string('tuchoi_da', 'local_fitnhom', $o), \core\output\notification::NOTIFY_INFO);
    if (time() - (int) $t->tuchoiluc <= khai::HOANTAC) {
        echo $OUTPUT->single_button(new moodle_url($url, ['action' => 'hoantac']), get_string('hoantac', 'local_fitnhom'));
    }
} else {
    echo html_writer::tag('p', get_string('tuchoi_hoi', 'local_fitnhom', $o));
    echo $OUTPUT->single_button(new moodle_url($url, ['action' => 'tuchoi']), get_string('khongphaiem', 'local_fitnhom'));
}
echo $OUTPUT->footer();
