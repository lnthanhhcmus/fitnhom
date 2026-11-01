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
 * Thông báo Moodle của Fit-Nhóm (TL54 mục 3): bạn được khai, người nộp khi một bạn bấm "Không phải em", và (0.2.3) bạn bị bỏ
 * khỏi danh sách khi người nộp sửa lại câu khai. Tách từ khai ở
 * 0.2 (TL60 mục 5.8). 0.2.1 (góp ý chủ dự án 01/11/2026): người gửi mang tên ở Cài đặt (mặc định FIT-MOODLE), thư có
 * câu chào, chữ "liệt kê" thay "khai", liên kết báo lại ghi đầy đủ, dòng ký "Hệ thống …". Gọi sau khi giao dịch đã ghi.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class thongbao {
    /**
     * Thông báo cho một bạn vừa được khai (TL54 mục 3).
     *
     * @param int $khaiid Câu khai.
     * @param int $uid Bạn được khai.
     * @param \stdClass $assign Bài tập.
     * @param \stdClass $cm Course module.
     * @param int $nguoinop Người nộp.
     * @return void
     */
    public static function duoc_khai(int $khaiid, int $uid, \stdClass $assign, \stdClass $cm, int $nguoinop): void {
        global $DB;
        $tvid = (int) $DB->get_field('local_fitnhom_tv', 'id', ['khaiid' => $khaiid, 'userid' => $uid]);
        $nop = \core_user::get_user($nguoinop);
        $nhan = \core_user::get_user($uid);
        if (!$nop || !$nhan || !$tvid) {
            return;
        }
        $mon = format_string((string) $DB->get_field('course', 'fullname', ['id' => $assign->course]));
        $a = (object) ['ban' => fullname($nop), 'bai' => format_string($assign->name), 'mon' => $mon,
            'url' => (new \moodle_url('/local/fitnhom/tuchoi.php', ['id' => $tvid]))->out(false),
            'urlbai' => (new \moodle_url('/mod/assign/view.php', ['id' => $cm->id]))->out(false), 'ky' => self::ky_ten()];
        $m = new \core\message\message();
        $m->component = 'local_fitnhom';
        $m->name = 'duockhai';
        $m->userfrom = self::nguoi_gui();
        $m->userto = $nhan;
        $m->courseid = (int) $assign->course;
        $m->subject = get_string('tb_duockhai_tieude', 'local_fitnhom', $a);
        $m->fullmessage = get_string('tb_duockhai', 'local_fitnhom', $a);
        $m->fullmessageformat = FORMAT_PLAIN;
        $m->fullmessagehtml = get_string('tb_duockhai_html', 'local_fitnhom', $a);
        $m->smallmessage = get_string('tb_duockhai_ngan', 'local_fitnhom', $a);
        $m->notification = 1;
        $m->contexturl = $a->url;
        $m->contexturlname = get_string('tb_baolai', 'local_fitnhom');
        message_send($m);
    }

    /**
     * Thông báo cho một bạn vừa bị bỏ khỏi danh sách thành viên khi người nộp sửa câu khai — bớt bạn, hay đổi sang "Em làm một
     * mình" (0.2.3, chủ dự án 01/11/2026): em biết mình không còn được tính theo bài nộp đó.
     *
     * @param int $uid Bạn bị bỏ.
     * @param \stdClass $assign Bài tập (id, course, name).
     * @param \stdClass $cm Course module.
     * @param int $nguoinop Người nộp.
     * @return void
     */
    public static function khong_con(int $uid, \stdClass $assign, \stdClass $cm, int $nguoinop): void {
        global $DB;
        $nop = \core_user::get_user($nguoinop);
        $nhan = \core_user::get_user($uid);
        if (!$nop || !$nhan) {
            return;
        }
        $mon = format_string((string) $DB->get_field('course', 'fullname', ['id' => $assign->course]));
        $a = (object) ['ban' => fullname($nop), 'bai' => format_string($assign->name), 'mon' => $mon,
            'urlbai' => (new \moodle_url('/mod/assign/view.php', ['id' => $cm->id]))->out(false), 'ky' => self::ky_ten()];
        $m = new \core\message\message();
        $m->component = 'local_fitnhom';
        $m->name = 'khongcon';
        $m->userfrom = self::nguoi_gui();
        $m->userto = $nhan;
        $m->courseid = (int) $assign->course;
        $m->subject = get_string('tb_khongcon_tieude', 'local_fitnhom', $a);
        $m->fullmessage = get_string('tb_khongcon', 'local_fitnhom', $a);
        $m->fullmessageformat = FORMAT_PLAIN;
        $m->fullmessagehtml = get_string('tb_khongcon_html', 'local_fitnhom', $a);
        $m->smallmessage = $m->subject . '.';
        $m->notification = 1;
        $m->contexturl = $a->urlbai;
        $m->contexturlname = $a->bai;
        message_send($m);
    }

    /**
     * Thông báo cho người nộp: một bạn được khai bấm "Không phải em" (TL54 mục 3).
     *
     * @param \stdClass $k Câu khai (local_fitnhom_khai).
     * @param int $userid Bạn bấm "Không phải em".
     * @param \stdClass $assign Bài tập (id, course, name).
     * @param \stdClass $cm Course module.
     * @return void
     */
    public static function tu_choi(\stdClass $k, int $userid, \stdClass $assign, \stdClass $cm): void {
        $nop = \core_user::get_user((int) $k->userid);
        $ban = \core_user::get_user($userid);
        if (!$nop || !$ban) {
            return;
        }
        $a = (object) ['ban' => fullname($ban), 'bai' => format_string($assign->name),
            'url' => (new \moodle_url('/mod/assign/view.php', ['id' => $cm->id]))->out(false), 'ky' => self::ky_ten()];
        $m = new \core\message\message();
        $m->component = 'local_fitnhom';
        $m->name = 'tuchoi';
        $m->userfrom = self::nguoi_gui();
        $m->userto = $nop;
        $m->courseid = (int) $assign->course;
        $m->subject = get_string('tb_tuchoi_tieude', 'local_fitnhom', $a);
        $m->fullmessage = get_string('tb_tuchoi', 'local_fitnhom', $a);
        $m->fullmessageformat = FORMAT_PLAIN;
        $m->fullmessagehtml = get_string('tb_tuchoi_html', 'local_fitnhom', $a);
        $m->smallmessage = $m->subject;
        $m->notification = 1;
        $m->contexturl = $a->url;
        $m->contexturlname = format_string($assign->name);
        message_send($m);
    }

    /**
     * Người gửi thông báo: người dùng "không trả lời" của Moodle mang tên ở Cài đặt (mặc định "FIT-MOODLE") thay cho
     * "Xin đừng trả lời về địa chỉ gửi thư này" (góp ý 01/11/2026). Địa chỉ gửi vẫn là địa chỉ không trả lời của trang;
     * phần "(qua <tên trang>)" sau tên là cài đặt chung của Moodle (Email đi › Thông tin "qua").
     *
     * @return \stdClass
     */
    public static function nguoi_gui(): \stdClass {
        $u = clone \core_user::get_noreply_user();
        $ten = trim((string) get_config('local_fitnhom', 'nguoigui'));
        if ($ten !== '') {
            $u->firstname = $ten;
            $u->lastname = '';
            foreach (['firstnamephonetic', 'lastnamephonetic', 'middlename', 'alternatename'] as $f) {
                $u->$f = '';
            }
        }
        return $u;
    }

    /**
     * Dòng ký cuối thư: "Hệ thống FIT-MOODLE" (tên người gửi ở Cài đặt; trống thì tên rút gọn của trang).
     *
     * @return string
     */
    public static function ky_ten(): string {
        global $SITE;
        $ten = trim((string) get_config('local_fitnhom', 'nguoigui'));
        return get_string('tb_kyten', 'local_fitnhom', $ten !== '' ? $ten : format_string($SITE->shortname));
    }
}
