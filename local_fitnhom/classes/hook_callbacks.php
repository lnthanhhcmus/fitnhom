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
 * Đầu mỗi trang: người còn bài nộp chưa khai thấy dải nhắc; hộp nổi một lần mỗi ngày (TL54 mục 5).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /** @var string Tùy chọn người dùng: ngày (Ymd) đã bấm "Để sau". */
    public const DESAU = 'local_fitnhom_desau';

    /**
     * Dải nhắc + hộp nổi.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook Móc.
     * @return void
     */
    public static function dau_trang(\core\hook\output\before_standard_top_of_body_html_generation $hook): void {
        global $PAGE, $USER, $OUTPUT;
        try {
            if (
                during_initial_install() || CLI_SCRIPT || AJAX_SCRIPT || !isloggedin() || isguestuser()
                    || \core\session\manager::is_loggedinas() || phamvi::che_do() === phamvi::TAT
            ) {
                return;
            }
            if (
                in_array($PAGE->pagelayout, ['login', 'maintenance', 'redirect', 'embedded', 'popup', 'print', 'frametop'], true)
                    || strpos($PAGE->url->get_path(), '/local/fitnhom/') !== false
            ) {
                return;
            }
            $ds = cho::cua((int) $USER->id);
            if (!$ds) {
                return;
            }
            $hook->add_html($OUTPUT->render_from_template('local_fitnhom/dai', self::du_lieu($ds)));
            if (get_user_preferences(self::DESAU, '') !== userdate(time(), '%Y%m%d', 99, false)) {
                $PAGE->requires->js_call_amd('local_fitnhom/hopnoi', 'init');
            }
        } catch (\Throwable $e) {
            // Không làm hỏng trang của Moodle.
            debugging('local_fitnhom: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * Dữ liệu mẫu dải nhắc / hộp nổi / trang khai.
     *
     * @param array $ds cho::cua().
     * @return array
     */
    public static function du_lieu(array $ds): array {
        global $PAGE;
        $ve = $PAGE->has_set_url() ? $PAGE->url->out_as_local_url(false) : '/';
        $dong = [];
        foreach ($ds as $d) {
            $dong[] = ['submissionid' => $d['submissionid'], 'bai' => $d['bai'], 'mon' => $d['mon'],
                'luc' => userdate($d['luc'], get_string('strftimedatetimeshort', 'langconfig')),
                'urlnhom' => (new \moodle_url('/local/fitnhom/khai.php', ['id' => $d['submissionid']]))->out(false),
                'urlbai' => (new \moodle_url('/mod/assign/view.php', ['id' => $d['cmid']]))->out(false)];
        }
        return ['so' => count($ds), 'dong' => $dong, 'sesskey' => sesskey(), 've' => $ve,
            'urlkhai' => (new \moodle_url('/local/fitnhom/khai.php'))->out(false)];
    }
}
