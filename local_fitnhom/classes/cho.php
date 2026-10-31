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
 * Bài nộp chưa có câu khai (TL54 mục 5, Q7): bắt lúc nộp (observer) hay khi admin bấm Quét ngay; hỏi người nộp ở hộp nổi.
 * Không khai bổ sung bài nộp trước lúc bật, không thư nhắc, không tác vụ đêm.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cho {
    /**
     * Đưa một bài nộp vào danh sách chờ nếu cần: đã gửi, của một người, bài tập áp dụng, chưa có câu khai.
     *
     * @param int $submissionid Bài nộp.
     * @param int $luc Lúc.
     * @return bool Đã thêm.
     */
    public static function them(int $submissionid, int $luc): bool {
        global $DB;
        $s = $DB->get_record('assign_submission', ['id' => $submissionid], 'id, assignment, userid, status, latest');
        if (!$s || !(int) $s->userid || $s->status !== 'submitted' || !(int) $s->latest) {
            return false;
        }
        $a = $DB->get_record('assign', ['id' => $s->assignment], 'id, course, teamsubmission');
        if (!$a || !phamvi::assign_ap_dung($a)) {
            return false;
        }
        if (
            $DB->record_exists('local_fitnhom_khai', ['submissionid' => $s->id])
                || $DB->record_exists('local_fitnhom_cho', ['submissionid' => $s->id])
        ) {
            return false;
        }
        $DB->insert_record('local_fitnhom_cho', (object) ['submissionid' => $s->id, 'assignid' => $a->id,
            'courseid' => $a->course, 'userid' => $s->userid, 'timecreated' => $luc]);
        self::xoa_dem((int) $s->userid);
        return true;
    }

    /**
     * Người có dòng chờ khai không (TL60 mục 5.5) — kiểm nhanh đầu mỗi trang: bộ đệm MUC theo người, hụt thì một truy vấn theo
     * chỉ mục userid_ix (không join). Có dòng chờ chưa chắc còn bài để hỏi (course có thể vừa bị loại trừ) — cua() lọc tiếp.
     *
     * @param int $userid Người.
     * @return bool
     */
    public static function co_cho(int $userid): bool {
        global $DB;
        $dem = \cache::make('local_fitnhom', 'cho');
        $v = $dem->get($userid);
        if ($v === false) {
            $v = $DB->record_exists('local_fitnhom_cho', ['userid' => $userid]) ? 1 : 0;
            $dem->set($userid, $v);
        }
        return (bool) $v;
    }

    /**
     * Xóa bộ đệm co_cho() của một người (thêm / khai / xóa dòng chờ) hay của mọi người (0: xóa bài tập, đổi chế độ).
     *
     * @param int $userid Người; 0 = mọi người.
     * @return void
     */
    public static function xoa_dem(int $userid = 0): void {
        $dem = \cache::make('local_fitnhom', 'cho');
        if ($userid > 0) {
            $dem->delete($userid);
        } else {
            $dem->purge();
        }
    }

    /**
     * Quét ngay — giữ chữ ký 0.1 (trả số bài vừa thêm) cho bài kiểm cũ; trang Tình hình dùng quet_xem() (TL60 mục 13, 0.2).
     *
     * @param int $luc Lúc quét.
     * @param int $userid Người bấm.
     * @return int Số bài vừa đưa vào danh sách chờ.
     */
    public static function quet(int $luc, int $userid): int {
        return self::quet_xem($luc, $userid)['them'];
    }

    /**
     * Quét ngay (trang quản trị): bài nộp đã gửi từ lúc bật, chưa có câu khai, chưa chờ, ở course áp dụng. Ghi số bài đã xem
     * và số bài thêm (quet_so_xem, quet_so_them — hiện ở Tình hình, TL60 mục 5.3).
     *
     * @param int $luc Lúc quét.
     * @param int $userid Người bấm.
     * @return array ['xem' => số bài nộp đã xét, 'them' => số bài vừa đưa vào danh sách chờ]
     */
    public static function quet_xem(int $luc, int $userid): array {
        global $DB;
        $so = 0;
        $xem = 0;
        $batdau = phamvi::bat_dau();
        if (phamvi::che_do() !== phamvi::TAT && $batdau) {
            $rs = $DB->get_recordset_sql(
                "SELECT s.id, a.course
                   FROM {assign_submission} s
                   JOIN {assign} a ON a.id = s.assignment
                  WHERE s.status = 'submitted' AND s.latest = 1 AND s.userid > 0 AND a.teamsubmission = 0
                        AND s.timemodified >= :bd
                        AND NOT EXISTS (SELECT 1 FROM {local_fitnhom_khai} k WHERE k.submissionid = s.id)
                        AND NOT EXISTS (SELECT 1 FROM {local_fitnhom_cho} c WHERE c.submissionid = s.id)",
                ['bd' => $batdau]
            );
            foreach ($rs as $r) {
                $xem++;
                if (phamvi::course_ap_dung((int) $r->course) && self::them((int) $r->id, $luc)) {
                    $so++;
                }
            }
            $rs->close();
        }
        set_config('quetluc', $luc, 'local_fitnhom');
        set_config('quet_so_xem', $xem, 'local_fitnhom');
        set_config('quet_so_them', $so, 'local_fitnhom');
        event\quet_ngay::create(['context' => \context_system::instance(), 'userid' => $userid,
            'other' => ['so' => $so, 'soxem' => $xem]])->trigger();
        return ['xem' => $xem, 'them' => $so];
    }

    /**
     * Danh sách chờ của một người (bài tập còn áp dụng), mới trước.
     *
     * @param int $userid Người nộp.
     * @return array [['submissionid', 'cmid', 'bai', 'mon', 'luc']]
     */
    public static function cua(int $userid): array {
        global $DB;
        $kq = [];
        $ds = $DB->get_records_sql(
            "SELECT c.id, c.submissionid, a.id AS assignid, a.name, a.course, a.teamsubmission, co.shortname, s.timemodified
               FROM {local_fitnhom_cho} c
               JOIN {assign} a ON a.id = c.assignid
               JOIN {course} co ON co.id = a.course
               JOIN {assign_submission} s ON s.id = c.submissionid
              WHERE c.userid = :u
           ORDER BY s.timemodified DESC",
            ['u' => $userid]
        );
        foreach ($ds as $r) {
            if (!phamvi::assign_ap_dung((object) ['course' => $r->course, 'teamsubmission' => $r->teamsubmission])) {
                continue;
            }
            $cm = get_coursemodule_from_instance('assign', $r->assignid, $r->course);
            if (!$cm) {
                continue;
            }
            $kq[] = ['submissionid' => (int) $r->submissionid, 'cmid' => (int) $cm->id, 'bai' => format_string($r->name),
                'mon' => format_string($r->shortname), 'luc' => (int) $r->timemodified];
        }
        return $kq;
    }
}
