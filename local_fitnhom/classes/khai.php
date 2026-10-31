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
 * Câu khai của một bài nộp (TL54 mục 2, 3, 3a): làm một mình hay bài nhóm cùng các bạn nào; bạn được khai bấm được "Không
 * phải em"; một bài nộp một câu khai; gợi ý điền sẵn theo bài của bạn đã nộp có khai mình. Thông báo ở thongbao, chữ cho bảng
 * chấm ở tomtat (0.2 tách — TL60 mục 5.8).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class khai {
    /** @var string Làm một mình. */
    public const MOT = 'MOT';
    /** @var string Bài nhóm. */
    public const NHOM = 'NHOM';
    /** @var string Khai ở trang nộp bài. */
    public const FORM = 'FORM';
    /** @var string Khai ở hộp nổi / trang khai của Fit-Nhóm. */
    public const HOPNOI = 'HOPNOI';
    /** @var string Bạn được khai. */
    public const KHAI = 'KHAI';
    /** @var string Bạn bấm "Không phải em". */
    public const TUCHOI = 'TUCHOI';
    /** @var int Thời hạn hoàn tác "Không phải em" (giây). */
    public const HOANTAC = 7 * DAYSECS;

    /**
     * Mã sinh viên hiện cạnh họ tên (Cấu hình: idnumber hay username; idnumber trống thì username).
     *
     * @param \stdClass $u Người (idnumber, username).
     * @return string
     */
    public static function ma_sv(\stdClass $u): string {
        $truong = get_config('local_fitnhom', 'truongma') === 'username' ? 'username' : 'idnumber';
        $ma = trim((string) ($u->$truong ?? ''));
        return $ma !== '' ? $ma : (string) ($u->username ?? '');
    }

    /**
     * "MSSV – Họ tên" của một người.
     *
     * @param \stdClass $u Người.
     * @return string
     */
    public static function nhan(\stdClass $u): string {
        return self::ma_sv($u) . ' – ' . fullname($u);
    }

    /**
     * Những người chọn được làm bạn cùng nhóm: được nộp bài ở bài tập đó (mod/assign:submit), đang ghi danh, bỏ người nộp.
     *
     * @param int $cmid Course module của bài tập.
     * @param int $tru Người nộp (bỏ khỏi danh sách).
     * @return array userid => "MSSV – Họ tên" (xếp theo họ tên)
     */
    public static function ung_vien(int $cmid, int $tru): array {
        $ctx = \context_module::instance($cmid);
        $f = 'u.id, u.idnumber, u.username, ' . implode(', ', array_map(
            fn($x) => 'u.' . $x,
            \core_user\fields::get_name_fields()
        ));
        $kq = [];
        foreach (get_enrolled_users($ctx, 'mod/assign:submit', 0, $f, 'u.lastname, u.firstname', 0, 0, true) as $u) {
            if ((int) $u->id !== $tru) {
                $kq[(int) $u->id] = self::nhan($u);
            }
        }
        return $kq;
    }

    /**
     * Câu khai của một bài nộp kèm các bạn (khóa userid).
     *
     * @param int $submissionid Bài nộp.
     * @return \stdClass|null Có thêm ->tv [userid => dòng local_fitnhom_tv].
     */
    public static function cua_bai_nop(int $submissionid): ?\stdClass {
        global $DB;
        $k = $DB->get_record('local_fitnhom_khai', ['submissionid' => $submissionid]);
        if (!$k) {
            return null;
        }
        $k->tv = [];
        foreach ($DB->get_records('local_fitnhom_tv', ['khaiid' => $k->id], 'id') as $t) {
            $k->tv[(int) $t->userid] = $t;
        }
        return $k;
    }

    /**
     * Lưu câu khai của một bài nộp (thay câu cũ). Bạn mới được khai nhận thông báo; bạn đã bấm "Không phải em" mà vẫn được
     * khai lại thì giữ trạng thái từ chối. Bỏ bài khỏi danh sách chờ hộp nổi.
     *
     * @param \stdClass $sub Bài nộp (assign_submission: id, assignment, userid).
     * @param string $loai MOT | NHOM.
     * @param int[] $ban Các bạn cùng làm (NHOM).
     * @param string $nguon FORM | HOPNOI.
     * @param int $luc Lúc lưu.
     * @param int $nguoibam Người bấm lưu (chính em, hay giảng viên nộp thay).
     * @return \stdClass Câu khai đã lưu (cua_bai_nop).
     */
    public static function luu(\stdClass $sub, string $loai, array $ban, string $nguon, int $luc, int $nguoibam): \stdClass {
        global $DB;
        if (
            !in_array($loai, [self::MOT, self::NHOM], true) || !in_array($nguon, [self::FORM, self::HOPNOI], true)
                || empty($sub->userid)
        ) {
            throw new \moodle_exception('loi_thamso', 'local_fitnhom');
        }
        $assign = $DB->get_record('assign', ['id' => $sub->assignment], 'id, course, name, teamsubmission', MUST_EXIST);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $assign->course, false, MUST_EXIST);
        $ban = array_values(array_unique(array_filter(array_map('intval', $ban), fn($x) => $x > 0 && $x !== (int) $sub->userid)));
        if ($loai === self::NHOM) {
            if (!$ban) {
                throw new \moodle_exception('loi_chuachonban', 'local_fitnhom');
            }
            $toida = max(1, (int) (get_config('local_fitnhom', 'toida') ?: 10));
            if (count($ban) > $toida) {
                throw new \moodle_exception('loi_quanhieu', 'local_fitnhom', '', $toida);
            }
            if (array_diff($ban, array_keys(self::ung_vien((int) $cm->id, (int) $sub->userid)))) {
                throw new \moodle_exception('loi_khongtronglop', 'local_fitnhom');
            }
        } else {
            $ban = [];
        }
        $tx = $DB->start_delegated_transaction();
        $cu = self::cua_bai_nop((int) $sub->id);
        if ($cu) {
            $DB->update_record('local_fitnhom_khai', (object) ['id' => $cu->id, 'loai' => $loai, 'nguon' => $nguon,
                'usermodified' => $nguoibam, 'timemodified' => $luc]);
            $khaiid = (int) $cu->id;
            foreach ($cu->tv as $uid => $t) {
                if (!in_array($uid, $ban, true)) {
                    $DB->delete_records('local_fitnhom_tv', ['id' => $t->id]);
                }
            }
        } else {
            $khaiid = (int) $DB->insert_record('local_fitnhom_khai', (object) ['assignid' => $assign->id,
                'submissionid' => $sub->id, 'courseid' => $assign->course, 'userid' => $sub->userid, 'loai' => $loai,
                'nguon' => $nguon, 'usermodified' => $nguoibam, 'timecreated' => $luc, 'timemodified' => $luc]);
        }
        $moi = [];
        foreach ($ban as $uid) {
            if (!$cu || !isset($cu->tv[$uid])) {
                $DB->insert_record('local_fitnhom_tv', (object) ['khaiid' => $khaiid, 'assignid' => $assign->id,
                    'userid' => $uid, 'trangthai' => self::KHAI, 'timecreated' => $luc]);
                $moi[] = $uid;
            }
        }
        $DB->delete_records('local_fitnhom_cho', ['submissionid' => $sub->id]);
        $tx->allow_commit();
        cho::xoa_dem((int) $sub->userid);
        $ctx = \context_module::instance($cm->id);
        event\khai_luu::create(['context' => $ctx, 'objectid' => $khaiid, 'relateduserid' => (int) $sub->userid,
            'other' => ['loai' => $loai, 'soban' => count($ban), 'nguon' => $nguon]])->trigger();
        foreach ($moi as $uid) {
            thongbao::duoc_khai($khaiid, $uid, $assign, $cm, (int) $sub->userid);
        }
        return self::cua_bai_nop((int) $sub->id);
    }

    /**
     * Bạn đã nộp bài này có khai mình cùng nhóm (để điền sẵn — TL54 mục 3a): bài nộp gần nhất đã gửi, mình chưa từ chối.
     *
     * @param int $assignid Bài tập.
     * @param int $userid Mình.
     * @return array|null ['nguoinop' => userid, 'luc' => giờ nộp, 'ban' => [userid…] (gồm người nộp, bỏ mình)]
     */
    public static function goi_y(int $assignid, int $userid): ?array {
        global $DB;
        $r = $DB->get_records_sql(
            "SELECT k.id, k.userid, s.timemodified
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
               JOIN {assign_submission} s ON s.id = k.submissionid
              WHERE t.assignid = :a AND t.userid = :u AND t.trangthai = :kh AND k.userid <> :u2
                    AND s.latest = 1 AND s.status = :st
           ORDER BY s.timemodified DESC, k.id DESC",
            ['a' => $assignid, 'u' => $userid, 'kh' => self::KHAI, 'u2' => $userid, 'st' => 'submitted'],
            0,
            1
        );
        if (!$r) {
            return null;
        }
        $k = reset($r);
        $ban = [(int) $k->userid];
        $tv = $DB->get_fieldset_select('local_fitnhom_tv', 'userid', 'khaiid = ? AND trangthai = ?', [$k->id, self::KHAI]);
        foreach ($tv as $u) {
            if ((int) $u !== $userid) {
                $ban[] = (int) $u;
            }
        }
        return ['nguoinop' => (int) $k->userid, 'luc' => (int) $k->timemodified, 'ban' => array_values(array_unique($ban))];
    }

    /**
     * Các bài nộp khác của cùng bài tập thuộc cùng nhóm với câu khai này (TL54 mục 3a: "Cùng nhóm với bài nộp của …").
     *
     * @param \stdClass $k Câu khai (cua_bai_nop).
     * @return array [['userid' => người nộp, 'luc' => giờ sửa bài nộp]]
     */
    public static function cung_nhom(\stdClass $k): array {
        global $DB;
        $nhom = array_merge([(int) $k->userid], array_keys(array_filter($k->tv, fn($t) => $t->trangthai === self::KHAI)));
        $kq = [];
        $ds = $DB->get_records_sql(
            "SELECT k.id, k.userid, s.timemodified
               FROM {local_fitnhom_khai} k
               JOIN {assign_submission} s ON s.id = k.submissionid
              WHERE k.assignid = :a AND k.id <> :k AND s.latest = 1",
            ['a' => $k->assignid, 'k' => $k->id]
        );
        foreach ($ds as $o) {
            $ban = array_map('intval', $DB->get_fieldset_select(
                'local_fitnhom_tv',
                'userid',
                'khaiid = ? AND trangthai = ?',
                [$o->id, self::KHAI]
            ));
            if (in_array((int) $o->userid, $nhom, true) || array_intersect($ban, $nhom)) {
                $kq[] = ['userid' => (int) $o->userid, 'luc' => (int) $o->timemodified];
            }
        }
        return $kq;
    }

    /**
     * Bạn được khai bấm "Không phải em" (hay hoàn tác trong 7 ngày). Người nộp nhận thông báo khi từ chối.
     *
     * @param int $tvid Dòng local_fitnhom_tv.
     * @param int $userid Người bấm (phải là chính bạn được khai).
     * @param bool $hoantac Hoàn tác.
     * @param int $luc Lúc bấm.
     * @return void
     */
    public static function tu_choi(int $tvid, int $userid, bool $hoantac, int $luc): void {
        global $DB;
        $t = $DB->get_record('local_fitnhom_tv', ['id' => $tvid], '*', MUST_EXIST);
        if ((int) $t->userid !== $userid) {
            throw new \moodle_exception('loi_khongphaicuaem', 'local_fitnhom');
        }
        $k = $DB->get_record('local_fitnhom_khai', ['id' => $t->khaiid], '*', MUST_EXIST);
        if ($hoantac) {
            if ($t->trangthai !== self::TUCHOI || $luc - (int) $t->tuchoiluc > self::HOANTAC) {
                throw new \moodle_exception('loi_hethoantac', 'local_fitnhom');
            }
            $DB->update_record('local_fitnhom_tv', (object) ['id' => $t->id, 'trangthai' => self::KHAI, 'tuchoiluc' => null]);
        } else {
            if ($t->trangthai === self::TUCHOI) {
                return;
            }
            $DB->update_record('local_fitnhom_tv', (object) ['id' => $t->id, 'trangthai' => self::TUCHOI, 'tuchoiluc' => $luc]);
        }
        $assign = $DB->get_record('assign', ['id' => $k->assignid], 'id, course, name', MUST_EXIST);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $assign->course, false, MUST_EXIST);
        event\thanhvien_tuchoi::create(['context' => \context_module::instance($cm->id), 'objectid' => $t->id,
            'relateduserid' => (int) $k->userid, 'other' => ['hoantac' => (int) $hoantac]])->trigger();
        if ($hoantac) {
            return;
        }
        thongbao::tu_choi($k, $userid, $assign, $cm);
    }

    /**
     * Tóm tắt câu khai cho bảng chấm — chuyển tiếp sang tomtat::cua_bai_nop() (0.2 tách tệp, TL60 mục 5.8; giữ cho bài kiểm cũ).
     *
     * @param \stdClass $sub Bài nộp.
     * @return string
     */
    public static function tom_tat(\stdClass $sub): string {
        return tomtat::cua_bai_nop($sub);
    }

    /**
     * Chép câu khai sang bài nộp mới (lần nộp mới dựa trên lần trước). Không báo lại các bạn.
     *
     * @param int $tu Bài nộp cũ.
     * @param int $den Bài nộp mới.
     * @return void
     */
    public static function sao_chep(int $tu, int $den): void {
        global $DB;
        $k = self::cua_bai_nop($tu);
        if (!$k || $DB->record_exists('local_fitnhom_khai', ['submissionid' => $den])) {
            return;
        }
        $id = $k->id;
        unset($k->id, $k->tv);
        $k->submissionid = $den;
        $moi = (int) $DB->insert_record('local_fitnhom_khai', $k);
        foreach ($DB->get_records('local_fitnhom_tv', ['khaiid' => $id]) as $t) {
            unset($t->id);
            $t->khaiid = $moi;
            $DB->insert_record('local_fitnhom_tv', $t);
        }
    }

    /**
     * Xóa câu khai, bạn, chờ của một bài nộp (bài nộp bị xóa).
     *
     * @param int $submissionid Bài nộp.
     * @return void
     */
    public static function xoa_bai_nop(int $submissionid): void {
        global $DB;
        foreach ($DB->get_fieldset_select('local_fitnhom_khai', 'id', 'submissionid = ?', [$submissionid]) as $id) {
            $DB->delete_records('local_fitnhom_tv', ['khaiid' => $id]);
        }
        $DB->delete_records('local_fitnhom_khai', ['submissionid' => $submissionid]);
        foreach ($DB->get_fieldset_select('local_fitnhom_cho', 'userid', 'submissionid = ?', [$submissionid]) as $u) {
            cho::xoa_dem((int) $u);
        }
        $DB->delete_records('local_fitnhom_cho', ['submissionid' => $submissionid]);
    }

    /**
     * Xóa mọi dữ liệu của một bài tập (bài tập / course bị xóa).
     *
     * @param int $assignid Bài tập.
     * @return void
     */
    public static function xoa_bai_tap(int $assignid): void {
        global $DB;
        $DB->delete_records('local_fitnhom_tv', ['assignid' => $assignid]);
        $DB->delete_records('local_fitnhom_khai', ['assignid' => $assignid]);
        $DB->delete_records('local_fitnhom_cho', ['assignid' => $assignid]);
        cho::xoa_dem();
    }

    /**
     * Câu hỏi "Bài này em làm thế nào?" — dùng chung cho trang nộp bài (assignsubmission_fitnhom) và trang khai của Fit-Nhóm.
     *
     * @param \MoodleQuickForm $m Biểu mẫu.
     * @param array $ungvien userid => "MSSV – Họ tên".
     * @param string $goiy Dòng báo "Bạn A đã nộp và khai em…" ('' = không).
     * @param bool $batbuoc Bắt buộc trả lời (người nộp tự nộp).
     * @return void
     */
    public static function them_cau_hoi(\MoodleQuickForm $m, array $ungvien, string $goiy, bool $batbuoc): void {
        $m->addElement(
            'static',
            'fitnhom_gioithieu',
            get_string('cauhoi', 'local_fitnhom'),
            \html_writer::span(get_string('cauhoi_vi', 'local_fitnhom'), 'text-muted')
        );
        if ($goiy !== '') {
            $m->addElement('static', 'fitnhom_goiy', '', \html_writer::div($goiy, 'alert alert-info mb-0'));
        }
        $m->addElement('radio', 'fitnhom_loai', '', get_string('chon_mot', 'local_fitnhom'), self::MOT);
        $m->addElement('radio', 'fitnhom_loai', '', get_string('chon_nhom', 'local_fitnhom'), self::NHOM);
        $m->setType('fitnhom_loai', PARAM_ALPHA);
        if ($batbuoc) {
            $m->addRule('fitnhom_loai', get_string('loi_chuachon', 'local_fitnhom'), 'required', null, 'client');
        }
        $m->addElement('autocomplete', 'fitnhom_tv', get_string('cacban', 'local_fitnhom'), $ungvien, ['multiple' => true,
            'noselectionstring' => get_string('cacban_chua', 'local_fitnhom'),
            'placeholder' => get_string('cacban_go', 'local_fitnhom')]);
        $m->setType('fitnhom_tv', PARAM_INT);
        $m->hideIf('fitnhom_tv', 'fitnhom_loai', 'neq', self::NHOM);
    }

    /**
     * Dòng báo bạn đã nộp có khai mình (TL54 mục 3a) — '' khi không có.
     *
     * @param array|null $g goi_y().
     * @return string
     */
    public static function chu_goi_y(?array $g): string {
        if (!$g) {
            return '';
        }
        $u = \core_user::get_user($g['nguoinop']);
        return $u ? get_string('goiy', 'local_fitnhom', (object) ['ban' => s(fullname($u)),
            'luc' => userdate($g['luc'], get_string('strftimedatetimeshort', 'langconfig'))]) : '';
    }
}
