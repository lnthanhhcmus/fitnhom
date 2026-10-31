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
 * Số liệu trang Tình hình (TL54 mục 4): chế độ, lúc bật, bài nộp từ lúc bật ở course áp dụng, đã khai, chờ, từ chối.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tinhhinh {
    /**
     * Số liệu.
     *
     * @return array
     */
    public static function so_lieu(): array {
        global $DB;
        $bd = phamvi::bat_dau();
        $kq = ['chedo' => phamvi::che_do(), 'batdau' => $bd, 'quetluc' => (int) get_config('local_fitnhom', 'quetluc'),
            'quetxem' => (int) get_config('local_fitnhom', 'quet_so_xem'),
            'quetthem' => (int) get_config('local_fitnhom', 'quet_so_them'),
            'bainop' => 0, 'mot' => 0, 'nhom' => 0, 'ban' => 0, 'tuchoi' => 0, 'cho' => 0, 'khoathu' => [],
            'subplugin' => \core_plugin_manager::instance()->get_plugin_info('assignsubmission_fitnhom') !== null
                && !get_config('assignsubmission_fitnhom', 'disabled')];
        foreach (preg_split('/\R/', (string) get_config('local_fitnhom', 'khoathu')) as $ma) {
            $ma = trim($ma);
            if ($ma !== '') {
                $c = $DB->get_record('course', ['shortname' => $ma], 'id, fullname');
                $kq['khoathu'][] = ['ma' => $ma, 'co' => (bool) $c, 'ten' => $c ? format_string($c->fullname) : '',
                    'apdung' => $c && phamvi::course_ap_dung((int) $c->id)];
            }
        }
        if (!$bd) {
            return $kq;
        }
        $rs = $DB->get_recordset_sql(
            "SELECT s.id, a.course, k.loai
               FROM {assign_submission} s
               JOIN {assign} a ON a.id = s.assignment
          LEFT JOIN {local_fitnhom_khai} k ON k.submissionid = s.id
              WHERE s.status = 'submitted' AND s.latest = 1 AND s.userid > 0 AND a.teamsubmission = 0 AND s.timemodified >= :bd",
            ['bd' => $bd]
        );
        foreach ($rs as $r) {
            if (!$r->loai && !phamvi::course_ap_dung((int) $r->course)) {
                continue;
            }
            $kq['bainop']++;
            if ($r->loai === khai::MOT) {
                $kq['mot']++;
            } else if ($r->loai === khai::NHOM) {
                $kq['nhom']++;
            }
        }
        $rs->close();
        $kq['ban'] = $DB->count_records('local_fitnhom_tv', ['trangthai' => khai::KHAI]);
        $kq['tuchoi'] = $DB->count_records('local_fitnhom_tv', ['trangthai' => khai::TUCHOI]);
        $kq['cho'] = $DB->count_records('local_fitnhom_cho');
        return $kq;
    }
}
