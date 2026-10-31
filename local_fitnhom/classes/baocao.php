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
 * Số liệu báo cáo (TL60 mục 5.1, 5.3): trang *Thành viên nhóm* của giảng viên (theo bài, chi tiết một bài) và trang Tình hình
 * của quản trị (theo course, Cần xem); xuất Excel. Chỉ đọc; không gửi gì, không sửa câu khai.
 *
 * Quy ước: "bài nộp" = assign_submission đã gửi (status submitted, latest = 1) của một người (userid > 0) — như api và cho.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class baocao {
    /** @var int Số ngày nhìn lại khi đếm "Không phải em" của một người nộp (khối Cần xem). */
    public const NGAY_TUCHOI = 60;
    /** @var int Số course tối đa ở bảng Theo course. */
    public const SO_COURSE = 50;

    /**
     * Bảng theo bài tập của một course: mọi bài tập có bài nộp từ lúc bật. Bài bật *Nộp theo nhóm* của Moodle chỉ có tên
     * (Fit-Nhóm không hỏi).
     *
     * @param int $courseid Course.
     * @return array[] Mỗi dòng: assignid, cmid, ten, han, nhommd (bool), nop, mot, nhom, ban, tuchoi, chuakhai, cuu.
     */
    public static function theo_bai(int $courseid): array {
        global $DB;
        $bd = phamvi::bat_dau();
        if (!$bd) {
            return [];
        }
        $bai = $DB->get_records_sql(
            "SELECT a.id, a.name, a.duedate, a.teamsubmission, COUNT(s.id) AS nop
               FROM {assign} a
               JOIN {assign_submission} s ON s.assignment = a.id AND s.latest = 1 AND s.status = 'submitted'
                    AND s.timemodified >= :bd AND (a.teamsubmission = 1 OR s.userid > 0)
              WHERE a.course = :c
           GROUP BY a.id, a.name, a.duedate, a.teamsubmission
           ORDER BY a.duedate, a.id",
            ['c' => $courseid, 'bd' => $bd]
        );
        if (!$bai) {
            return [];
        }
        // Câu khai theo bài nộp còn hiệu lực (lần mới nhất, đã gửi).
        $khai = $DB->get_records_sql(
            "SELECT k.assignid,
                    SUM(CASE WHEN k.loai = :mot THEN 1 ELSE 0 END) AS mot,
                    SUM(CASE WHEN k.loai = :nhom THEN 1 ELSE 0 END) AS nhom
               FROM {local_fitnhom_khai} k
               JOIN {assign_submission} s ON s.id = k.submissionid AND s.latest = 1 AND s.status = 'submitted'
              WHERE k.courseid = :c
           GROUP BY k.assignid",
            ['c' => $courseid, 'mot' => khai::MOT, 'nhom' => khai::NHOM]
        );
        $tv = $DB->get_records_sql(
            "SELECT k.assignid,
                    SUM(CASE WHEN t.trangthai = :kh THEN 1 ELSE 0 END) AS ban,
                    SUM(CASE WHEN t.trangthai = :tc THEN 1 ELSE 0 END) AS tuchoi
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
               JOIN {assign_submission} s ON s.id = k.submissionid AND s.latest = 1 AND s.status = 'submitted'
              WHERE k.courseid = :c
           GROUP BY k.assignid",
            ['c' => $courseid, 'kh' => khai::KHAI, 'tc' => khai::TUCHOI]
        );
        $cho = $DB->get_records_sql_menu(
            "SELECT assignid, COUNT(id) FROM {local_fitnhom_cho} WHERE courseid = :c GROUP BY assignid",
            ['c' => $courseid]
        );
        $mod = get_fast_modinfo($courseid);
        $kq = [];
        foreach ($bai as $a) {
            $cm = null;
            foreach ($mod->get_instances_of('assign') as $x) {
                if ((int) $x->instance === (int) $a->id) {
                    $cm = $x;
                    break;
                }
            }
            $d = ['assignid' => (int) $a->id, 'cmid' => $cm ? (int) $cm->id : 0, 'ten' => format_string($a->name),
                'han' => (int) $a->duedate, 'nhommd' => (bool) $a->teamsubmission, 'nop' => (int) $a->nop, 'mot' => 0,
                'nhom' => 0, 'ban' => 0, 'tuchoi' => 0, 'chuakhai' => 0, 'cuu' => 0];
            if (!$d['nhommd']) {
                $d['mot'] = (int) ($khai[$a->id]->mot ?? 0);
                $d['nhom'] = (int) ($khai[$a->id]->nhom ?? 0);
                $d['ban'] = (int) ($tv[$a->id]->ban ?? 0);
                $d['tuchoi'] = (int) ($tv[$a->id]->tuchoi ?? 0);
                $d['chuakhai'] = (int) ($cho[$a->id] ?? 0);
                $d['cuu'] = self::duoc_tinh_nho_nhom((int) $a->id);
            }
            $kq[] = $d;
        }
        return $kq;
    }

    /**
     * Số em được tính đã nộp nhờ nhóm ở một bài tập: có trong api::thanhvien() mà không có bài nộp riêng đã gửi.
     *
     * @param int $assignid Bài tập.
     * @return int
     */
    public static function duoc_tinh_nho_nhom(int $assignid): int {
        global $DB;
        $tv = api::thanhvien($assignid);
        if (!$tv) {
            return 0;
        }
        $rieng = $DB->get_fieldset_select(
            'assign_submission',
            'userid',
            "assignment = ? AND latest = 1 AND status = 'submitted' AND userid > 0",
            [$assignid]
        );
        return count(array_diff(array_keys($tv), array_map('intval', $rieng)));
    }

    /**
     * Chi tiết một bài tập: mỗi bài nộp một dòng, mới trước.
     *
     * @param int $assignid Bài tập.
     * @return array[] Mỗi dòng: userid, nguoinop ("MSSV – Họ tên"), luc, loai (MOT | NHOM | ''), ban [['ten', 'tuchoi']],
     *                 cungnhom [tên người nộp], cho (bool — đang chờ khai ở hộp nổi).
     */
    public static function chi_tiet(int $assignid): array {
        global $DB;
        $ten = 'u.id, u.idnumber, u.username, ' . implode(', ', array_map(
            fn($x) => 'u.' . $x,
            \core_user\fields::get_name_fields()
        ));
        $ds = $DB->get_records_sql(
            "SELECT s.id AS sid, s.timemodified, $ten
               FROM {assign_submission} s
               JOIN {user} u ON u.id = s.userid
              WHERE s.assignment = :a AND s.latest = 1 AND s.status = 'submitted' AND s.userid > 0
           ORDER BY s.timemodified DESC, s.id DESC",
            ['a' => $assignid]
        );
        $cho = array_flip(array_map('intval', $DB->get_fieldset_select(
            'local_fitnhom_cho',
            'submissionid',
            'assignid = ?',
            [$assignid]
        )));
        $kq = [];
        foreach ($ds as $r) {
            $d = ['userid' => (int) $r->id, 'nguoinop' => khai::nhan($r), 'luc' => (int) $r->timemodified, 'loai' => '',
                'ban' => [], 'cungnhom' => [], 'cho' => isset($cho[(int) $r->sid])];
            $k = khai::cua_bai_nop((int) $r->sid);
            if ($k) {
                $d['loai'] = $k->loai;
                foreach ($k->tv as $uid => $t) {
                    $u = \core_user::get_user($uid);
                    if ($u) {
                        $d['ban'][] = ['ten' => khai::nhan($u), 'tuchoi' => $t->trangthai === khai::TUCHOI];
                    }
                }
                foreach (khai::cung_nhom($k) as $c) {
                    $u = \core_user::get_user($c['userid']);
                    if ($u) {
                        $d['cungnhom'][] = fullname($u);
                    }
                }
            }
            $kq[] = $d;
        }
        return $kq;
    }

    /**
     * Theo course (Tình hình của quản trị): các course nhiều bài nộp nhất từ lúc bật, ở course áp dụng hay đã có câu khai.
     * Không tên sinh viên.
     *
     * @param int $danhmuc Lọc danh mục (kể cả danh mục con); 0 = mọi danh mục.
     * @return array[] Mỗi dòng: courseid, ma, ten, nop, khai, chuakhai, tuchoi, nhom, tile (phần trăm bài nhóm trên đã khai).
     */
    public static function theo_course(int $danhmuc = 0): array {
        global $DB;
        $bd = phamvi::bat_dau();
        if (!$bd) {
            return [];
        }
        $ds = $DB->get_records_sql(
            "SELECT a.course, COUNT(s.id) AS nop, COUNT(k.id) AS khai,
                    SUM(CASE WHEN k.loai = :nhom THEN 1 ELSE 0 END) AS nhom
               FROM {assign_submission} s
               JOIN {assign} a ON a.id = s.assignment
          LEFT JOIN {local_fitnhom_khai} k ON k.submissionid = s.id
              WHERE s.status = 'submitted' AND s.latest = 1 AND s.userid > 0 AND a.teamsubmission = 0 AND s.timemodified >= :bd
           GROUP BY a.course",
            ['bd' => $bd, 'nhom' => khai::NHOM]
        );
        $tuchoi = $DB->get_records_sql_menu(
            "SELECT k.courseid, COUNT(t.id)
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
              WHERE t.trangthai = :tc
           GROUP BY k.courseid",
            ['tc' => khai::TUCHOI]
        );
        $kq = [];
        foreach ($ds as $r) {
            $cid = (int) $r->course;
            if (!(int) $r->khai && !phamvi::course_ap_dung($cid)) {
                continue;
            }
            $c = $DB->get_record('course', ['id' => $cid], 'id, shortname, fullname, category');
            if (!$c) {
                continue;
            }
            if ($danhmuc) {
                $path = (string) $DB->get_field('course_categories', 'path', ['id' => $c->category]);
                if (strpos($path . '/', '/' . $danhmuc . '/') === false) {
                    continue;
                }
            }
            $kq[] = ['courseid' => $cid, 'ma' => format_string($c->shortname), 'ten' => format_string($c->fullname),
                'nop' => (int) $r->nop, 'khai' => (int) $r->khai, 'chuakhai' => (int) $r->nop - (int) $r->khai,
                'tuchoi' => (int) ($tuchoi[$cid] ?? 0), 'nhom' => (int) $r->nhom,
                'tile' => (int) $r->khai ? (int) round(100 * (int) $r->nhom / (int) $r->khai) : 0];
        }
        usort($kq, fn($x, $y) => [$y['nop'], $x['courseid']] <=> [$x['nop'], $y['courseid']]);
        return array_slice($kq, 0, self::SO_COURSE);
    }

    /**
     * Khối *Cần xem* (TL60 mục 5.3, Q9): hai dấu hiệu khai bừa. Fit-Nhóm không tự làm gì với các dòng này; tên người chỉ hiện
     * khi quản trị bấm *Xem* (khóa của dòng).
     *
     * (a) người nộp bị ≥ batthuong_tuchoi lần "Không phải em" trong NGAY_TUCHOI ngày tới $luc;
     * (b) em được khai trong ≥ batthuong_khai bài nộp của những người nộp khác nhau ở cùng một bài tập.
     *
     * @param int $luc Mốc (giây).
     * @return array ['tuchoi' => [['khoa', 'userid', 'so', 'bai' => [['ma', 'ten', 'courseid', 'assignid']]]],
     *                'khai' => [['khoa', 'userid', 'so', 'ma', 'ten', 'courseid', 'assignid']]]
     */
    public static function can_xem(int $luc): array {
        global $DB;
        $n = max(1, (int) (get_config('local_fitnhom', 'batthuong_tuchoi') ?: 3));
        $m = max(2, (int) (get_config('local_fitnhom', 'batthuong_khai') ?: 5));
        $kq = ['tuchoi' => [], 'khai' => []];
        $tu = $luc - self::NGAY_TUCHOI * DAYSECS;
        $nguoi = $DB->get_records_sql_menu(
            "SELECT k.userid, COUNT(t.id) AS so
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
              WHERE t.trangthai = :tc AND t.tuchoiluc >= :tu AND t.tuchoiluc <= :den
           GROUP BY k.userid
             HAVING COUNT(t.id) >= :n",
            ['tc' => khai::TUCHOI, 'tu' => $tu, 'den' => $luc, 'n' => $n]
        );
        foreach ($nguoi as $uid => $so) {
            $bai = $DB->get_records_sql(
                "SELECT DISTINCT a.id, a.name, a.course, c.shortname
                   FROM {local_fitnhom_tv} t
                   JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
                   JOIN {assign} a ON a.id = k.assignid
                   JOIN {course} c ON c.id = a.course
                  WHERE k.userid = :u AND t.trangthai = :tc AND t.tuchoiluc >= :tu AND t.tuchoiluc <= :den",
                ['u' => $uid, 'tc' => khai::TUCHOI, 'tu' => $tu, 'den' => $luc]
            );
            $kq['tuchoi'][] = ['khoa' => 't' . (int) $uid, 'userid' => (int) $uid, 'so' => (int) $so, 'bai' => array_values(
                array_map(fn($a) => ['ma' => format_string($a->shortname), 'ten' => format_string($a->name),
                    'courseid' => (int) $a->course, 'assignid' => (int) $a->id], $bai)
            )];
        }
        $ds = $DB->get_records_sql(
            "SELECT " . $DB->sql_concat('t.assignid', "'_'", 't.userid') . " AS khoa, t.assignid, t.userid,
                    COUNT(DISTINCT k.userid) AS so
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
               JOIN {assign_submission} s ON s.id = k.submissionid AND s.latest = 1 AND s.status = 'submitted'
              WHERE t.trangthai = :kh AND k.userid <> t.userid
           GROUP BY t.assignid, t.userid
             HAVING COUNT(DISTINCT k.userid) >= :m",
            ['kh' => khai::KHAI, 'm' => $m]
        );
        foreach ($ds as $r) {
            $a = $DB->get_record_sql(
                "SELECT a.id, a.name, a.course, c.shortname FROM {assign} a JOIN {course} c ON c.id = a.course WHERE a.id = ?",
                [$r->assignid]
            );
            if ($a) {
                $kq['khai'][] = ['khoa' => 'k' . $r->assignid . '_' . $r->userid, 'userid' => (int) $r->userid,
                    'so' => (int) $r->so, 'ma' => format_string($a->shortname), 'ten' => format_string($a->name),
                    'courseid' => (int) $a->course, 'assignid' => (int) $a->id];
            }
        }
        return $kq;
    }

    /**
     * Bảng tính cho trang giảng viên: sheet *Theo bài* và *Chi tiết* (mọi bài tập không phải nộp theo nhóm của Moodle).
     *
     * @param int $courseid Course.
     * @return array[] Danh sách sheet cho ghi_excel().
     */
    public static function bang_course(int $courseid): array {
        $fmt = get_string('strftimedatetimeshort', 'langconfig');
        $s = fn($k) => get_string($k, 'local_fitnhom');
        $bai = ['ten' => $s('sheet_theobai'), 'cot' => [$s('cot_bai'), $s('cot_han'), $s('cot_nop'), $s('cot_motminh'),
            $s('cot_nhom'), $s('cot_ban'), $s('cot_tuchoi'), $s('cot_chuakhai'), $s('cot_cuu')], 'dong' => []];
        $ct = ['ten' => $s('sheet_chitiet'), 'cot' => [$s('cot_bai'), $s('cot_nguoinop'), $s('cot_luc'), $s('cot_khai'),
            $s('cot_cacban'), $s('cungnhom_voi_cot')], 'dong' => []];
        foreach (self::theo_bai($courseid) as $b) {
            if ($b['nhommd']) {
                continue;
            }
            $bai['dong'][] = [$b['ten'], $b['han'] ? userdate($b['han'], $fmt) : '', $b['nop'], $b['mot'], $b['nhom'],
                $b['ban'], $b['tuchoi'], $b['chuakhai'], $b['cuu']];
            foreach (self::chi_tiet($b['assignid']) as $d) {
                $ct['dong'][] = [$b['ten'], $d['nguoinop'], userdate($d['luc'], $fmt), self::chu_loai($d),
                    implode(', ', array_map(fn($x) => $x['ten'] . ($x['tuchoi'] ? ' (' . $s('khongphaiem') . ')' : ''), $d['ban'])),
                    implode(', ', $d['cungnhom'])];
            }
        }
        return [$bai, $ct];
    }

    /**
     * Bảng tính *Theo course* cho quản trị.
     *
     * @param int $danhmuc Danh mục lọc (0 = mọi).
     * @return array[] Một sheet.
     */
    public static function bang_theo_course(int $danhmuc): array {
        $s = fn($k) => get_string($k, 'local_fitnhom');
        $sh = ['ten' => $s('th_theocourse'), 'cot' => [$s('cot_course'), $s('cot_nop'), $s('cot_dakhai'), $s('cot_chuakhai'),
            $s('cot_tuchoi'), $s('cot_tile')], 'dong' => []];
        foreach (self::theo_course($danhmuc) as $r) {
            $sh['dong'][] = [$r['ma'], $r['nop'], $r['khai'], $r['chuakhai'], $r['tuchoi'], $r['tile']];
        }
        return [$sh];
    }

    /**
     * Chữ "Một mình / Bài nhóm / Chưa khai" của một dòng chi tiết.
     *
     * @param array $d Dòng chi_tiet().
     * @return string
     */
    public static function chu_loai(array $d): string {
        if ($d['loai'] === khai::MOT) {
            return get_string('motminh', 'local_fitnhom');
        }
        return get_string($d['loai'] === khai::NHOM ? 'cot_nhom' : 'chuakhai', 'local_fitnhom');
    }

    /**
     * Ghi bảng tính .xlsx ra tệp. Mọi ô chữ ghi kiểu chuỗi tường minh (tên bài bắt đầu bằng "=" không thành công thức);
     * số ghi kiểu số.
     *
     * @param array[] $sheets [['ten', 'cot' => [...], 'dong' => [[...], ...]], ...]
     * @param string $duongdan Tệp ra.
     * @return void
     */
    public static function ghi_excel(array $sheets, string $duongdan): void {
        global $CFG;
        require_once($CFG->libdir . '/phpspreadsheet/vendor/autoload.php');
        $wb = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $wb->removeSheetByIndex(0);
        foreach (array_values($sheets) as $i => $sh) {
            $ws = $wb->createSheet($i);
            $ws->setTitle(\core_text::substr(preg_replace('/[\\\\\/\?\*\[\]:]/u', ' ', $sh['ten']), 0, 31));
            $hang = 1;
            foreach (array_merge([$sh['cot']], $sh['dong']) as $dong) {
                foreach (array_values($dong) as $j => $o) {
                    $ma = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j + 1) . $hang;
                    if (is_int($o) || is_float($o)) {
                        $ws->setCellValueExplicit($ma, $o, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } else {
                        $ws->setCellValueExplicit($ma, (string) $o, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
                $hang++;
            }
            $ws->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max(1, count($sh['cot']))) . '1')
                ->getFont()->setBold(true);
        }
        $wb->setActiveSheetIndex(0);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($wb))->save($duongdan);
    }

    /**
     * Gửi bảng tính về trình duyệt rồi thoát.
     *
     * @param array[] $sheets Như ghi_excel().
     * @param string $ten Tên tệp (không đuôi).
     * @return void
     */
    public static function tai_excel(array $sheets, string $ten): void {
        $tep = make_request_directory() . '/bang.xlsx';
        self::ghi_excel($sheets, $tep);
        $ten = clean_filename(preg_replace('/\s+/u', '_', $ten)) . '.xlsx';
        send_temp_file($tep, $ten);
    }
}
