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
$danhmuc = optional_param('danhmuc', 0, PARAM_INT);
$xem = optional_param('xem', '', PARAM_ALPHANUMEXT);
if (optional_param('quet', 0, PARAM_BOOL)) {
    require_sesskey();
    $q = \local_fitnhom\cho::quet_xem(time(), (int) $USER->id);
    redirect($url, get_string('quet_xong', 'local_fitnhom', $q['them']), null, \core\output\notification::NOTIFY_SUCCESS);
}
if (optional_param('tai', 0, PARAM_BOOL)) {
    require_sesskey();
    \local_fitnhom\baocao::tai_excel(
        \local_fitnhom\baocao::bang_theo_course($danhmuc),
        'FitNhom_TheoCourse_' . userdate(time(), '%Y%m%d', 99, false)
    );
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
    [get_string('th_quetluc', 'local_fitnhom'), $d['quetluc'] ? userdate($d['quetluc'], $fmt) . ' · '
        . get_string('th_quet_truoc', 'local_fitnhom', (object) ['xem' => $d['quetxem'], 'them' => $d['quetthem']]) : '—'],
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

// Theo course (TL60 mục 5.3) — không tên sinh viên.
echo $OUTPUT->heading(get_string('th_theocourse', 'local_fitnhom'), 3, 'mt-4');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $url->out(false), 'class' => 'form-inline mb-2']);
echo html_writer::label(get_string('loaitru_danhmuc_loc', 'local_fitnhom'), 'local-fitnhom-dm', true, ['class' => 'me-2 mr-2']);
echo html_writer::select(
    [0 => get_string('th_moidanhmuc', 'local_fitnhom')] + core_course_category::make_categories_list(),
    'danhmuc',
    $danhmuc,
    false,
    ['id' => 'local-fitnhom-dm', 'class' => 'custom-select me-2 mr-2', 'onchange' => 'this.form.submit()']
);
echo html_writer::end_tag('form');
$tc = \local_fitnhom\baocao::theo_course($danhmuc);
if ($tc) {
    $b = new html_table();
    $b->attributes['class'] = 'generaltable table-sm';
    $b->head = [get_string('cot_course', 'local_fitnhom'), get_string('cot_nop', 'local_fitnhom'),
        get_string('cot_dakhai', 'local_fitnhom'), get_string('cot_chuakhai', 'local_fitnhom'),
        get_string('cot_tuchoi', 'local_fitnhom'), get_string('cot_tile', 'local_fitnhom')];
    foreach ($tc as $r) {
        $lk = html_writer::link(
            new moodle_url('/local/fitnhom/course.php', ['id' => $r['courseid']]),
            $r['ma'],
            ['title' => $r['ten']]
        );
        $b->data[] = [$lk, $r['nop'], $r['khai'], $r['chuakhai'], $r['tuchoi'], $r['tile'] . '%'];
    }
    echo html_writer::table($b);
    echo $OUTPUT->single_button(
        new moodle_url($url, ['tai' => 1, 'danhmuc' => $danhmuc]),
        get_string('nut_taiexcel', 'local_fitnhom'),
        'post'
    );
} else {
    echo html_writer::tag('p', get_string('th_theocourse_khong', 'local_fitnhom'), ['class' => 'text-muted']);
}

// Cần xem (TL60 mục 5.3, Q9): tên chỉ hiện khi bấm Xem; Fit-Nhóm không tự làm gì.
echo $OUTPUT->heading(get_string('th_canxem', 'local_fitnhom'), 3, 'mt-4');
$cx = \local_fitnhom\baocao::can_xem(time());
$ten = function (string $khoa, int $uid) use ($xem, $url, $danhmuc): string {
    if ($xem === $khoa) {
        $u = core_user::get_user($uid);
        return $u ? s(\local_fitnhom\khai::nhan($u)) : (string) $uid;
    }
    return html_writer::link(
        new moodle_url($url, ['xem' => $khoa, 'danhmuc' => $danhmuc]),
        get_string('nut_xem', 'local_fitnhom')
    );
};
if (!$cx['tuchoi'] && !$cx['khai']) {
    echo html_writer::tag('p', get_string('th_canxem_khong', 'local_fitnhom'), ['class' => 'text-muted']);
}
if ($cx['tuchoi']) {
    $b = new html_table();
    $b->attributes['class'] = 'generaltable table-sm';
    echo html_writer::tag('p', get_string('th_canxem_tuchoi', 'local_fitnhom', (object) [
        'n' => get_config('local_fitnhom', 'batthuong_tuchoi'),
        'ngay' => \local_fitnhom\baocao::NGAY_TUCHOI,
    ]), ['class' => 'fw-bold font-weight-bold mb-1']);
    $b->head = [get_string('cot_course', 'local_fitnhom') . ' · ' . get_string('cot_bai', 'local_fitnhom'),
        get_string('cot_solan', 'local_fitnhom'), get_string('cot_nguoinop', 'local_fitnhom')];
    foreach ($cx['tuchoi'] as $r) {
        $bai = array_map(fn($a) => s($a['ma']) . ' · ' . s($a['ten']), $r['bai']);
        $b->data[] = [implode('<br>', $bai), $r['so'], $ten($r['khoa'], $r['userid'])];
    }
    echo html_writer::table($b);
}
if ($cx['khai']) {
    $b = new html_table();
    $b->attributes['class'] = 'generaltable table-sm';
    echo html_writer::tag(
        'p',
        get_string('th_canxem_khai', 'local_fitnhom', get_config('local_fitnhom', 'batthuong_khai')),
        ['class' => 'fw-bold font-weight-bold mb-1']
    );
    $b->head = [get_string('cot_course', 'local_fitnhom') . ' · ' . get_string('cot_bai', 'local_fitnhom'),
        get_string('cot_songuoi', 'local_fitnhom'), get_string('cot_emduockhai', 'local_fitnhom')];
    foreach ($cx['khai'] as $r) {
        $b->data[] = [s($r['ma']) . ' · ' . html_writer::link(
            new moodle_url('/local/fitnhom/course.php', ['id' => $r['courseid'], 'assign' => $r['assignid']]),
            $r['ten']
        ), $r['so'], $ten($r['khoa'], $r['userid'])];
    }
    echo html_writer::table($b);
}
echo $OUTPUT->footer();
