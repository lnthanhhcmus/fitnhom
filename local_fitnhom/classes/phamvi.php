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
 * Chế độ và phạm vi áp dụng (TL54 mục 4): Tắt / Thử nghiệm (chỉ các course chọn) / Bật toàn trang; course không áp dụng theo
 * danh mục hay mẫu tên rút gọn; bài đã bật "Nộp theo nhóm" của Moodle không hỏi.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phamvi {
    /** @var string Tắt — không hỏi gì (mặc định khi mới cài). */
    public const TAT = 'TAT';
    /** @var string Thử nghiệm — chỉ các course trong danh sách thử. */
    public const THU = 'THU';
    /** @var string Bật toàn trang. */
    public const BAT = 'BAT';

    /** @var array Đệm trong một lượt chạy: courseid => bool. */
    private static $dem = [];

    /**
     * Chế độ hiện tại.
     *
     * @return string TAT | THU | BAT
     */
    public static function che_do(): string {
        $c = (string) get_config('local_fitnhom', 'chedo');
        return in_array($c, [self::THU, self::BAT], true) ? $c : self::TAT;
    }

    /**
     * Lúc bật lần đầu (chuyển khỏi Tắt) — Quét ngay chỉ xét bài nộp từ lúc này.
     *
     * @return int 0 = chưa bật lần nào.
     */
    public static function bat_dau(): int {
        return (int) get_config('local_fitnhom', 'batdau');
    }

    /**
     * Xóa đệm (sau khi đổi cấu hình trong cùng lượt chạy, bài kiểm).
     *
     * @return void
     */
    public static function xoa_dem(): void {
        self::$dem = [];
    }

    /**
     * Các dòng (đã cắt khoảng trắng, bỏ dòng trống) của một cấu hình nhiều dòng.
     *
     * @param string $ten Tên cấu hình.
     * @return string[]
     */
    private static function dong(string $ten): array {
        $kq = [];
        foreach (preg_split('/\R/', (string) get_config('local_fitnhom', $ten)) as $d) {
            $d = trim($d);
            if ($d !== '') {
                $kq[] = $d;
            }
        }
        return $kq;
    }

    /**
     * Mã rút gọn có khớp một mẫu kiểu SQL LIKE (% nhiều ký tự, _ một ký tự) không — không phân biệt hoa thường.
     *
     * @param string $ma Mã rút gọn.
     * @param string $mau Mẫu.
     * @return bool
     */
    public static function khop(string $ma, string $mau): bool {
        $re = strtr(preg_quote($mau, '/'), ['%' => '.*', '_' => '.']);
        return (bool) preg_match('/^' . $re . '$/iu', $ma);
    }

    /**
     * Course có áp dụng Fit-Nhóm không (theo chế độ, danh sách thử, loại trừ).
     *
     * @param int $courseid Course.
     * @return bool
     */
    public static function course_ap_dung(int $courseid): bool {
        global $DB;
        $chedo = self::che_do();
        if ($chedo === self::TAT || $courseid <= SITEID) {
            return false;
        }
        if (array_key_exists($courseid, self::$dem)) {
            return self::$dem[$courseid];
        }
        $c = $DB->get_record('course', ['id' => $courseid], 'id, shortname, category');
        $kq = (bool) $c;
        if ($kq && $chedo === self::THU) {
            $thu = array_map(fn($s) => \core_text::strtolower($s), self::dong('khoathu'));
            $kq = in_array(\core_text::strtolower((string) $c->shortname), $thu, true);
        }
        if ($kq) {
            foreach (self::dong('loaitru_mau') as $mau) {
                if (self::khop((string) $c->shortname, $mau)) {
                    $kq = false;
                    break;
                }
            }
        }
        $dm = array_filter(array_map('intval', explode(',', (string) get_config('local_fitnhom', 'loaitru_danhmuc'))));
        if ($kq && $dm) {
            $path = (string) $DB->get_field('course_categories', 'path', ['id' => $c->category]);
            foreach ($dm as $id) {
                if (strpos($path . '/', '/' . $id . '/') !== false) {
                    $kq = false;
                    break;
                }
            }
        }
        return self::$dem[$courseid] = $kq;
    }

    /**
     * Bài tập có hỏi thành viên nhóm không: course áp dụng và bài không bật "Nộp theo nhóm" của Moodle.
     *
     * @param \stdClass $assign Bản ghi assign (course, teamsubmission).
     * @return bool
     */
    public static function assign_ap_dung(\stdClass $assign): bool {
        return empty($assign->teamsubmission) && self::course_ap_dung((int) $assign->course);
    }
}
