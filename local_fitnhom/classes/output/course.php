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

namespace local_fitnhom\output;

use local_fitnhom\baocao;
use local_fitnhom\khai;
use local_fitnhom\phamvi;

/**
 * Trang *Thành viên nhóm* của một course cho người chấm được bài (giảng viên, trợ giảng — TL60 mục 5.1, Q8): bảng theo bài
 * tập, chi tiết một bài, nút Tải Excel. Không có nút nhắc, không sửa câu khai hộ.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course implements \renderable, \templatable {
    /** @var int Course. */
    private $courseid;
    /** @var int Bài tập xem chi tiết (0 = bảng theo bài). */
    private $assignid;

    /**
     * Khởi tạo.
     *
     * @param int $courseid Course.
     * @param int $assignid Bài tập (0 = bảng theo bài).
     */
    public function __construct(int $courseid, int $assignid = 0) {
        $this->courseid = $courseid;
        $this->assignid = $assignid;
    }

    /**
     * Kiểm quyền vào trang: chấm được bài trong course (mod/assign:grade ở ngữ cảnh course). Sinh viên → ngoại lệ quyền.
     *
     * @param int $courseid Course.
     * @return \context_course
     */
    public static function kiem_quyen(int $courseid): \context_course {
        $ctx = \context_course::instance($courseid);
        require_capability('mod/assign:grade', $ctx);
        return $ctx;
    }

    /**
     * Bài tập xem chi tiết có thuộc course này và không phải nộp theo nhóm của Moodle không.
     *
     * @return \stdClass|null Bản ghi assign.
     */
    private function bai(): ?\stdClass {
        global $DB;
        if (!$this->assignid) {
            return null;
        }
        $a = $DB->get_record('assign', ['id' => $this->assignid, 'course' => $this->courseid], 'id, name, duedate, teamsubmission');
        return $a ?: null;
    }

    /**
     * Dữ liệu mẫu.
     *
     * @param \renderer_base $output Bộ vẽ.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        $fmt = get_string('strftimedatetimeshort', 'langconfig');
        $ly = phamvi::ly_do($this->courseid);
        $d = ['chedo' => get_string('chedo_' . phamvi::che_do(), 'local_fitnhom'), 'apdung' => $ly === '',
            'lydo' => $ly === '' ? '' : get_string('course_lydo_' . \core_text::strtolower($ly), 'local_fitnhom'),
            'urlve' => (new \moodle_url('/local/fitnhom/course.php', ['id' => $this->courseid]))->out(false),
            'urltai' => (new \moodle_url('/local/fitnhom/course.php', ['id' => $this->courseid, 'tai' => 1,
                'sesskey' => sesskey()]))->out(false),
            'coban' => false, 'cochitiet' => false, 'bai' => [], 'chitiet' => null];
        if (!$d['apdung']) {
            return $d;
        }
        $a = $this->bai();
        if ($a && !$a->teamsubmission) {
            $dong = [];
            foreach (baocao::chi_tiet((int) $a->id) as $r) {
                $dong[] = ['nguoinop' => $r['nguoinop'], 'luc' => userdate($r['luc'], $fmt), 'loai' => baocao::chu_loai($r),
                    'chuakhai' => $r['loai'] === '', 'ban' => $r['ban'], 'coban' => (bool) $r['ban'],
                    'cungnhom' => $r['cungnhom'] ? get_string('cungnhom_voi', 'local_fitnhom', implode(', ', $r['cungnhom'])) : ''];
            }
            $d['cochitiet'] = true;
            $d['chitiet'] = ['ten' => format_string($a->name), 'han' => $a->duedate ? userdate($a->duedate, $fmt) : '',
                'dong' => $dong, 'codong' => (bool) $dong];
            return $d;
        }
        foreach (baocao::theo_bai($this->courseid) as $b) {
            $b['han'] = $b['han'] ? userdate($b['han'], $fmt) : '';
            $b['url'] = (new \moodle_url('/local/fitnhom/course.php', ['id' => $this->courseid, 'assign' => $b['assignid']]))
                ->out(false);
            $b['urlcham'] = $b['cmid'] ? (new \moodle_url('/mod/assign/view.php', ['id' => $b['cmid'], 'action' => 'grading']))
                ->out(false) : '';
            $d['bai'][] = $b;
        }
        $d['coban'] = (bool) $d['bai'];
        return $d;
    }
}
