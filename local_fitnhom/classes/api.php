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
 * Hợp đồng cho plugin khác (TL60 mục 4 — nguồn duy nhất của ngữ nghĩa, bảo đảm H1–H8) — FIT-CNL và mọi plugin muốn biết "ai
 * đã nộp chung". Plugin khác **chỉ** gọi lớp này, không đọc bảng của Fit-Nhóm.
 *
 * Luật giữ hợp đồng: trong cùng HOPDONG không đổi ý nghĩa các hàm đã có. Muốn đổi thì thêm hàm mới hay tăng HOPDONG (plugin
 * dùng tự bỏ qua khi gặp số lạ). 0.2 thêm cua_nguoi(), ap_dung() — HOPDONG vẫn 1; plugin gọi kiểm method_exists() trước.
 * Kết quả không phụ thuộc chế độ / phạm vi hiện tại (H4) — bên gọi muốn tôn trọng chế độ thì tự kiểm dang_bat() / ap_dung().
 * Bài kiểm tests/hopdong_test.php giữ hợp đồng này.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {
    /** @var int Phiên bản hợp đồng. */
    public const HOPDONG = 1;

    /**
     * Fit-Nhóm đang hỏi thành viên (chế độ Thử nghiệm hay Bật).
     *
     * @return bool
     */
    public static function dang_bat(): bool {
        return phamvi::che_do() !== phamvi::TAT;
    }

    /**
     * Các bạn được khai cùng nhóm ở một bài tập — để tính "đã nộp" cho các bạn không tự nộp.
     *
     * Chỉ tính bài nộp đã gửi (status submitted, lần nộp mới nhất); bỏ bạn đã bấm "Không phải em"; không gồm chính người nộp.
     * Một bạn được nhiều bài nộp khai thì lấy bài nộp sớm nhất (TL54 mục 3a). Bạn cũng có bài nộp riêng vẫn có trong kết quả —
     * plugin dùng tự lấy lúc sớm nhất giữa bài của chính bạn và kết quả này.
     *
     * @param int $assignid assign.id
     * @return array userid => ['nguoinop' => userid người nộp, 'luc' => assign_submission.timemodified]
     */
    public static function thanhvien(int $assignid): array {
        global $DB;
        $kq = [];
        $rs = $DB->get_recordset_sql(
            "SELECT t.id, t.userid, k.userid AS nguoinop, s.timemodified AS luc
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
               JOIN {assign_submission} s ON s.id = k.submissionid
              WHERE t.assignid = :a AND t.trangthai = :kh AND s.status = :st AND s.latest = 1 AND t.userid <> k.userid
           ORDER BY s.timemodified ASC, t.id ASC",
            ['a' => $assignid, 'kh' => khai::KHAI, 'st' => 'submitted']
        );
        foreach ($rs as $r) {
            if (!isset($kq[(int) $r->userid])) {
                $kq[(int) $r->userid] = ['nguoinop' => (int) $r->nguoinop, 'luc' => (int) $r->luc];
            }
        }
        $rs->close();
        return $kq;
    }

    /**
     * Các bài tập mà em được khai cùng nộp, bài nộp có lúc nộp trong [tu, den] (đóng hai đầu) — TL60 H5. Cùng luật lọc với
     * thanhvien() (bài nộp đã gửi, lần mới nhất, câu khai bài nhóm, em chưa bấm "Không phải em", không gồm chính người nộp);
     * mỗi bài tập một dòng — bài nộp sớm nhất.
     *
     * @param int $userid Em được khai.
     * @param int $tu Từ (giây, gồm).
     * @param int $den Đến (giây, gồm).
     * @return array assignid => ['nguoinop' => userid người nộp, 'luc' => assign_submission.timemodified]
     */
    public static function cua_nguoi(int $userid, int $tu, int $den): array {
        global $DB;
        $kq = [];
        $rs = $DB->get_recordset_sql(
            "SELECT t.id, t.assignid, k.userid AS nguoinop, s.timemodified AS luc
               FROM {local_fitnhom_tv} t
               JOIN {local_fitnhom_khai} k ON k.id = t.khaiid
               JOIN {assign_submission} s ON s.id = k.submissionid
              WHERE t.userid = :u AND t.trangthai = :kh AND s.status = :st AND s.latest = 1 AND k.userid <> :u2
                    AND s.timemodified >= :tu AND s.timemodified <= :den
           ORDER BY s.timemodified ASC, t.id ASC",
            ['u' => $userid, 'kh' => khai::KHAI, 'st' => 'submitted', 'u2' => $userid, 'tu' => $tu, 'den' => $den]
        );
        foreach ($rs as $r) {
            if (!isset($kq[(int) $r->assignid])) {
                $kq[(int) $r->assignid] = ['nguoinop' => (int) $r->nguoinop, 'luc' => (int) $r->luc];
            }
        }
        $rs->close();
        return $kq;
    }

    /**
     * Course có áp dụng Fit-Nhóm không (chế độ, danh sách thử, loại trừ theo định dạng / mẫu tên / danh mục) — TL60 H6.
     *
     * @param int $courseid Course.
     * @return bool Chế độ Tắt hay SITEID → false.
     */
    public static function ap_dung(int $courseid): bool {
        return phamvi::course_ap_dung($courseid);
    }
}
