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
 * Trang khai của Fit-Nhóm (TL54 mục 5): danh sách bài nộp chưa khai; khai "Em làm một mình" một bấm; "Bài nhóm…" chọn bạn;
 * "Để sau" (hộp nổi hôm sau mới hiện lại).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_fitnhom\khai;

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('');
}
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$ve = optional_param('ve', '', PARAM_LOCALURL);
$trang = new moodle_url('/local/fitnhom/khai.php');
$luc = time();

if ($action === 'desau') {
    require_sesskey();
    set_user_preference(\local_fitnhom\hook_callbacks::DESAU, userdate($luc, '%Y%m%d', 99, false));
    redirect(new moodle_url($ve ?: '/'));
}

/**
 * Bài nộp của chính người đang đăng nhập ở bài tập áp dụng — hay báo lỗi.
 *
 * @param int $id Bài nộp.
 * @return array [bài nộp, assign, cm]
 */
function local_fitnhom_bai_cua_em(int $id): array {
    global $DB, $USER;
    $s = $DB->get_record('assign_submission', ['id' => $id], '*', MUST_EXIST);
    $a = $DB->get_record('assign', ['id' => $s->assignment], 'id, course, name, teamsubmission', MUST_EXIST);
    if ((int) $s->userid !== (int) $USER->id || !\local_fitnhom\phamvi::assign_ap_dung($a)) {
        throw new moodle_exception('loi_khongphaicuaem', 'local_fitnhom');
    }
    $cm = get_coursemodule_from_instance('assign', $a->id, $a->course, false, MUST_EXIST);
    return [$s, $a, $cm];
}

if ($action === 'mot') {
    require_sesskey();
    [$s] = local_fitnhom_bai_cua_em($id);
    khai::luu($s, khai::MOT, [], khai::HOPNOI, $luc, (int) $USER->id);
    redirect(new moodle_url($ve ?: $trang), get_string('dakhai', 'local_fitnhom'), null, \core\output\notification::NOTIFY_SUCCESS);
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url($trang, $id ? ['id' => $id] : []);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('trangkhai', 'local_fitnhom'));
$PAGE->set_heading(get_string('trangkhai', 'local_fitnhom'));

if ($id) {
    [$s, $a, $cm] = local_fitnhom_bai_cua_em($id);
    $PAGE->set_url($trang, ['id' => $id]);
    $k = khai::cua_bai_nop((int) $s->id);
    $g = $k ? null : khai::goi_y((int) $a->id, (int) $USER->id);
    $form = new \local_fitnhom\form\khai_form($PAGE->url, ['ungvien' => khai::ung_vien((int) $cm->id, (int) $USER->id),
        'goiy' => khai::chu_goi_y($g)]);
    $mac = ['id' => $s->id, 've' => $ve, 'fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => $g ? $g['ban'] : []];
    if ($k) {
        $mac['fitnhom_loai'] = $k->loai;
        $mac['fitnhom_tv'] = array_keys($k->tv);
    }
    $form->set_data($mac);
    if ($form->is_cancelled()) {
        redirect(new moodle_url($ve ?: $trang));
    }
    if ($d = $form->get_data()) {
        khai::luu($s, $d->fitnhom_loai, (array) ($d->fitnhom_tv ?? []), khai::HOPNOI, $luc, (int) $USER->id);
        redirect(
            new moodle_url($ve ?: $trang),
            get_string('dakhai', 'local_fitnhom'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($a->name) . ' · ' . format_string(get_course($a->course)->shortname), 3);
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

$ds = \local_fitnhom\cho::cua((int) $USER->id);
echo $OUTPUT->header();
if (!$ds) {
    echo $OUTPUT->notification(get_string('khongconbai', 'local_fitnhom'), \core\output\notification::NOTIFY_SUCCESS);
} else {
    $d = \local_fitnhom\hook_callbacks::du_lieu($ds);
    $d['ve'] = $trang->out_as_local_url(false);
    echo $OUTPUT->render_from_template('local_fitnhom/danhsach', $d);
}
echo $OUTPUT->footer();
