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

namespace local_fitnhom;

/**
 * Chữ HTML tóm tắt câu khai cho cột *Thành viên nhóm* ở bảng chấm / trạng thái bài nộp (TL54 mục 2). Tách từ khai ở 0.2
 * (TL60 mục 5.8) — không đổi chữ.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tomtat {
    /**
     * Tóm tắt câu khai cho bảng chấm / trang trạng thái bài nộp (HTML): Một mình / tên các bạn (bạn từ chối gạch ngang) /
     * "Cùng nhóm với bài nộp của …".
     *
     * @param \stdClass $sub Bài nộp.
     * @return string
     */
    public static function cua_bai_nop(\stdClass $sub): string {
        $k = khai::cua_bai_nop((int) $sub->id);
        if (!$k) {
            // Em chưa tự khai: có trong bài nhóm của bạn khác thì ghi; bài chưa gửi thì để trống.
            $g = !empty($sub->assignment) && !empty($sub->userid) ? khai::goi_y((int) $sub->assignment, (int) $sub->userid) : null;
            if ($g && ($u = \core_user::get_user($g['nguoinop']))) {
                return get_string('cotrongnhom', 'local_fitnhom', s(fullname($u)) . ' ('
                    . userdate($g['luc'], get_string('strftimedatetimeshort', 'langconfig')) . ')');
            }
            return ($sub->status ?? '') === 'submitted'
                ? \html_writer::span(get_string('chuakhai', 'local_fitnhom'), 'text-muted') : '';
        }
        if ($k->loai === khai::MOT) {
            $chu = get_string('motminh', 'local_fitnhom');
        } else {
            $ten = [];
            foreach ($k->tv as $uid => $t) {
                $u = \core_user::get_user($uid);
                if (!$u) {
                    continue;
                }
                $ten[] = $t->trangthai === khai::TUCHOI
                    ? \html_writer::tag('del', s(khai::nhan($u)), ['title' => get_string('dakhongphai', 'local_fitnhom')])
                    : s(khai::nhan($u));
            }
            $chu = get_string('nhomvoi', 'local_fitnhom') . ' ' . implode(', ', $ten);
        }
        $cung = [];
        foreach (khai::cung_nhom($k) as $c) {
            $u = \core_user::get_user($c['userid']);
            if ($u) {
                $cung[] = s(fullname($u)) . ' (' . userdate($c['luc'], get_string('strftimedatetimeshort', 'langconfig')) . ')';
            }
        }
        if ($cung) {
            $chu .= \html_writer::div(get_string('cungnhomvoi', 'local_fitnhom', implode(', ', $cung)), 'small text-muted');
        }
        return $chu;
    }

    /** @var bool Đã đặt liên kết trang Thành viên nhóm trong lượt này chưa. */
    private static $dadat = false;

    /**
     * Liên kết nhỏ tới trang *Thành viên nhóm* (TL60 mục 5.1) — chỉ cho người chấm được bài, chỉ một lần mỗi lượt (ô đầu tiên
     * của cột ở bảng chấm). '' khi không đủ điều kiện hay đã đặt.
     *
     * @param int $courseid Course.
     * @return string
     */
    public static function lien_ket(int $courseid): string {
        if (self::$dadat || $courseid <= SITEID || !has_capability('mod/assign:grade', \context_course::instance($courseid))) {
            return '';
        }
        self::$dadat = true;
        return \html_writer::div(\html_writer::link(
            new \moodle_url('/local/fitnhom/course.php', ['id' => $courseid]),
            get_string('nav_course', 'local_fitnhom'),
            ['class' => 'small']
        ), 'local-fitnhom-lienket mb-1');
    }

    /**
     * Cho bài kiểm: đặt lại cờ "đã đặt liên kết".
     *
     * @return void
     */
    public static function dat_lai(): void {
        self::$dadat = false;
    }
}
