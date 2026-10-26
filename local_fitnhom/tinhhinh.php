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
 * Tình hình Fit-Nhóm và nút Quét ngay (chỉ admin).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_fitnhom_tinhhinh');
$url = new moodle_url('/local/fitnhom/tinhhinh.php');
if (optional_param('quet', 0, PARAM_BOOL)) {
    require_sesskey();
    $so = \local_fitnhom\cho::quet(time(), (int) $USER->id);
    redirect($url, get_string('quet_xong', 'local_fitnhom', $so), null, \core\output\notification::NOTIFY_SUCCESS);
}
$d = \local_fitnhom\tinhhinh::so_lieu();
$fmt = get_string('strftimedatetimeshort', 'langconfig');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tinhhinh', 'local_fitnhom'));
echo html_writer::div(get_string('mucdich', 'local_fitnhom'), 'alert alert-secondary');
if (!$d['subplugin']) {
    echo $OUTPUT->notification(get_string('th_chuasubplugin', 'local_fitnhom'), \core\output\notification::NOTIFY_WARNING);
}
$t = new html_table();
$t->attributes['class'] = 'generaltable';
$t->data = [
    [get_string('chedo', 'local_fitnhom'), get_string('chedo_' . $d['chedo'], 'local_fitnhom')
        . ' · ' . html_writer::link(
            new moodle_url('/admin/settings.php', ['section' => 'local_fitnhom']),
            get_string('caidat', 'local_fitnhom')
        )],
    [get_string('th_batdau', 'local_fitnhom'),
        $d['batdau'] ? userdate($d['batdau'], $fmt) : get_string('th_chuabat', 'local_fitnhom')],
    [get_string('th_bainop', 'local_fitnhom'), $d['bainop']],
    [get_string('th_mot', 'local_fitnhom'), $d['mot']],
    [get_string('th_nhom', 'local_fitnhom'), $d['nhom']],
    [get_string('th_ban', 'local_fitnhom'), $d['ban']],
    [get_string('th_tuchoi', 'local_fitnhom'), $d['tuchoi']],
    [get_string('th_cho', 'local_fitnhom'), $d['cho']],
    [get_string('th_quetluc', 'local_fitnhom'), $d['quetluc'] ? userdate($d['quetluc'], $fmt) : '—'],
];
if ($d['chedo'] === 'THU') {
    $ds = [];
    foreach ($d['khoathu'] as $c) {
        $loaitru = $c['apdung'] ? '' : ' (' . get_string('th_loaitru', 'local_fitnhom') . ')';
        $ds[] = s($c['ma']) . ' — ' . ($c['co'] ? s($c['ten']) . $loaitru : get_string('th_khongco', 'local_fitnhom'));
    }
    $t->data[] = [get_string('khoathu', 'local_fitnhom'), $ds ? implode('<br>', $ds) : get_string('th_chuachon', 'local_fitnhom')];
}
echo html_writer::table($t);
echo $OUTPUT->single_button(new moodle_url($url, ['quet' => 1]), get_string('quetngay', 'local_fitnhom'), 'post');
echo html_writer::tag('p', get_string('quetngay_help', 'local_fitnhom'), ['class' => 'text-muted small mt-2']);
echo $OUTPUT->footer();
