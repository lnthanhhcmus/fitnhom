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

/**
 * Câu hỏi "Bài này em làm thế nào?" trên trang nộp bài (TL54 mục 2). Dữ liệu, chế độ, phạm vi ở local_fitnhom.
 *
 * Không cấu hình được (giảng viên không có ô bật / tắt — chủ dự án Q2); bật hay không theo local_fitnhom\phamvi. Câu khai
 * không tính là bài nộp (is_empty luôn đúng); không có tệp (Tải toàn bộ bài nộp không đổi).
 *
 * @package    assignsubmission_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Plugin nộp bài Fit-Nhóm.
 *
 * @package    assignsubmission_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_fitnhom extends assign_submission_plugin {
    /**
     * Tên (cột ở bảng chấm).
     *
     * @return string
     */
    public function get_name() {
        return get_string('tencot', 'assignsubmission_fitnhom');
    }

    /**
     * Giảng viên không bật / tắt được ở cài đặt bài tập.
     *
     * @return bool
     */
    public function is_configurable() {
        return false;
    }

    /**
     * Có local_fitnhom (lớp phạm vi) không.
     *
     * @return bool
     */
    private static function co_loi(): bool {
        return class_exists('\local_fitnhom\phamvi') && class_exists('\local_fitnhom\khai');
    }

    /**
     * Bật theo chế độ / phạm vi của local_fitnhom (bỏ qua cấu hình 'enabled' từng bài).
     *
     * @return bool
     */
    public function is_enabled() {
        if (!self::co_loi()) {
            return false;
        }
        try {
            if ($this->assignment->has_instance()) {
                return \local_fitnhom\phamvi::assign_ap_dung($this->assignment->get_instance());
            }
            $c = $this->assignment->get_course();
            return $c ? \local_fitnhom\phamvi::course_ap_dung((int) $c->id) : false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Câu hỏi trên trang nộp bài. Bắt buộc khi chính em nộp; giảng viên nộp thay thì không bắt buộc.
     *
     * @param mixed $submission Bài nộp.
     * @param MoodleQuickForm $mform Biểu mẫu.
     * @param stdClass $data Dữ liệu biểu mẫu.
     * @param int $userid Người nộp.
     * @return bool
     */
    public function get_form_elements_for_user($submission, MoodleQuickForm $mform, stdClass $data, $userid) {
        global $USER;
        $userid = (int) ($userid ?: $USER->id);
        $cm = $this->assignment->get_course_module();
        $k = $submission ? \local_fitnhom\khai::cua_bai_nop((int) $submission->id) : null;
        $g = $k ? null : \local_fitnhom\khai::goi_y((int) $this->assignment->get_instance()->id, $userid);
        \local_fitnhom\khai::them_cau_hoi(
            $mform,
            \local_fitnhom\khai::ung_vien((int) $cm->id, $userid),
            \local_fitnhom\khai::chu_goi_y($g),
            $userid === (int) $USER->id
        );
        if ($k) {
            $data->fitnhom_loai = $k->loai;
            $data->fitnhom_tv = array_keys($k->tv);
        } else if ($g) {
            $data->fitnhom_loai = \local_fitnhom\khai::NHOM;
            $data->fitnhom_tv = $g['ban'];
        }
        return true;
    }

    /**
     * Lưu câu khai. Không có trường của Fit-Nhóm (ứng dụng điện thoại, dịch vụ web) thì bỏ qua — bài đi vào hộp nổi.
     *
     * @param stdClass $submission Bài nộp.
     * @param stdClass $data Dữ liệu.
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER;
        $loai = isset($data->fitnhom_loai) ? (string) $data->fitnhom_loai : '';
        if ($loai === '') {
            if (property_exists($data, 'fitnhom_loai') && (int) $submission->userid === (int) $USER->id) {
                $this->set_error(get_string('loi_chuachon', 'local_fitnhom'));
                return false;
            }
            return true;
        }
        try {
            \local_fitnhom\khai::luu(
                $submission,
                $loai,
                (array) ($data->fitnhom_tv ?? []),
                \local_fitnhom\khai::FORM,
                time(),
                (int) $USER->id
            );
        } catch (\moodle_exception $e) {
            $this->set_error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Câu khai không tính là bài nộp.
     *
     * @param stdClass $submission Bài nộp.
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        return true;
    }

    /**
     * Câu khai không tính là bài nộp.
     *
     * @param stdClass $data Dữ liệu.
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        return true;
    }

    /**
     * Cột "Thành viên nhóm" ở bảng chấm / trạng thái bài nộp.
     *
     * @param stdClass $submission Bài nộp.
     * @param bool $showviewlink Có liên kết xem đầy đủ không.
     * @return string
     */
    public function view_summary(stdClass $submission, &$showviewlink) {
        $showviewlink = false;
        $lk = class_exists('\\local_fitnhom\\tomtat')
            ? \local_fitnhom\tomtat::lien_ket((int) $this->assignment->get_course()->id) : '';
        return $lk . \local_fitnhom\khai::tom_tat($submission);
    }

    /**
     * Xem đầy đủ (như tóm tắt).
     *
     * @param stdClass $submission Bài nộp.
     * @return string
     */
    public function view(stdClass $submission) {
        return \local_fitnhom\khai::tom_tat($submission);
    }

    /**
     * Lần nộp mới chép câu khai của lần trước.
     *
     * @param stdClass $source Bài nộp cũ.
     * @param stdClass $dest Bài nộp mới.
     * @return bool
     */
    public function copy_submission(stdClass $source, stdClass $dest) {
        if (self::co_loi()) {
            \local_fitnhom\khai::sao_chep((int) $source->id, (int) $dest->id);
        }
        return true;
    }

    /**
     * Bài nộp bị xóa nội dung.
     *
     * @param stdClass $submission Bài nộp.
     * @return bool
     */
    public function remove(stdClass $submission) {
        if (self::co_loi()) {
            \local_fitnhom\khai::xoa_bai_nop((int) $submission->id);
        }
        return true;
    }

    /**
     * Bài tập bị xóa.
     *
     * @return bool
     */
    public function delete_instance() {
        if (self::co_loi() && $this->assignment->has_instance()) {
            \local_fitnhom\khai::xoa_bai_tap((int) $this->assignment->get_instance()->id);
        }
        return true;
    }

    /**
     * Không có vùng tệp.
     *
     * @return array
     */
    public function get_file_areas() {
        return [];
    }
}
