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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/helper.php');

/**
 * Bản 0.2.1 (góp ý chủ dự án 01/11/2026): dòng báo đỏ khi chưa chọn, bạn đã nộp có liệt kê mình, tệp ghi chú trong *Tải toàn
 * bộ bài nộp*, thư thông báo (người gửi FIT-MOODLE, câu chào, "liệt kê", dòng ký).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\khai
 * @covers     \local_fitnhom\tomtat
 * @covers     \local_fitnhom\thongbao
 * @covers     \assign_submission_fitnhom
 */
final class ban021_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Hai nút Một mình / Bài nhóm nằm trong một nhóm cùng tên: chưa chọn thì biểu mẫu không qua, lỗi gắn vào nhóm (khung nhóm
     * có chỗ hiện dòng đỏ); chọn rồi thì qua. Trước 0.2.1 hai nút rời — lỗi không có chỗ hiện.
     */
    public function test_chua_chon_co_dong_do(): void {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        $this->resetAfterTest();
        $m = new \MoodleQuickForm('thu', 'post', '');
        khai::them_cau_hoi($m, [], '', true);
        $this->assertSame('group', $m->getElement('fitnhom_loai')->getType());
        $this->assertTrue($m->isElementRequired('fitnhom_loai'));
        $m->updateSubmission(['fitnhom_tv' => []], []);
        $this->assertFalse($m->validate());
        $this->assertSame(get_string('loi_chuachon', 'local_fitnhom'), $m->getElementError('fitnhom_loai'));
        $html = $m->toHtml();
        $this->assertStringContainsString('fgroup_id_error_fitnhom_loai', $html);
        $this->assertStringContainsString(get_string('loi_chuachon', 'local_fitnhom'), html_entity_decode($html, ENT_QUOTES));
        $this->assertStringContainsString(get_string('cauhoi', 'local_fitnhom'), $html);

        $m2 = new \MoodleQuickForm('thu2', 'post', '');
        khai::them_cau_hoi($m2, [], '', true);
        $m2->updateSubmission(['fitnhom_loai' => khai::MOT], []);
        $this->assertTrue($m2->validate());
        $this->assertSame(khai::MOT, $m2->exportValues()['fitnhom_loai']);
        // Giảng viên nộp thay: không bắt buộc.
        $m3 = new \MoodleQuickForm('thu3', 'post', '');
        khai::them_cau_hoi($m3, [], '', false);
        $m3->updateSubmission(['fitnhom_tv' => []], []);
        $this->assertTrue($m3->validate());
    }

    /**
     * Ô chọn bạn: sau tên bạn đã nộp có liệt kê mình ghi "đã nộp lúc …, có tên em trong nhóm". Bạn đã nộp mà không liệt kê mình
     * thì không ghi gì (không dò được ai đã nộp). Mình bấm "Không phải em" thì hết ghi.
     */
    public function test_ban_da_nop_co_ten_em(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(4);
        $aid = (int) $a->get_instance()->id;
        $cmid = (int) $a->get_course_module()->id;
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $this->nop($sv[2], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[3]->id]]);
        $nhan = get_string('ungvien_danop', 'local_fitnhom', '');
        $nhan = trim(substr($nhan, strpos($nhan, ',')));

        $b = khai::ung_vien_kem($cmid, $aid, (int) $sv[1]->id);
        $this->assertStringContainsString($nhan, $b[(int) $sv[0]->id]);
        $this->assertStringNotContainsString($nhan, $b[(int) $sv[2]->id], 'C đã nộp nhưng không liệt kê B — không lộ');
        $this->assertSame(khai::ung_vien($cmid, (int) $sv[1]->id)[(int) $sv[2]->id], $b[(int) $sv[2]->id]);
        $this->assertSame([(int) $sv[0]->id], array_keys(khai::ban_da_nop_co_em($aid, (int) $sv[1]->id)));

        $tvid = $this->tv_id((int) $sv[0]->id, (int) $sv[1]->id, $aid);
        $sink = $this->redirectMessages();
        khai::tu_choi($tvid, (int) $sv[1]->id, false, time());
        $sink->close();
        $this->assertSame([], khai::ban_da_nop_co_em($aid, (int) $sv[1]->id));
        $this->assertStringNotContainsString($nhan, khai::ung_vien_kem($cmid, $aid, (int) $sv[1]->id)[(int) $sv[0]->id]);
    }

    // Hai bài test_tep_ghi_chu, test_goi_phan_hoi_khong_nhan_tep_ghi_chu (0.2.1) bỏ ở 0.2.6: chủ dự án bỏ tệp ghi chú khỏi
    // *Tải toàn bộ bài nộp* (01/11/2026 16:43 — TL60 mục 13); ban026_test khẳng định gói tải không còn thư mục
    // …_assignsubmission_fitnhom.

    /**
     * Thư cho bạn được liệt kê: người gửi FIT-MOODLE (đổi được ở Cài đặt; trống thì như Moodle), câu chào, "liệt kê" thay "khai",
     * liên kết báo lại ghi đầy đủ, dòng ký "Hệ thống FIT-MOODLE". Thư cho người nộp khi bạn báo không cùng làm cũng có chào và ký.
     */
    public function test_thu_thong_bao(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        set_config('nguoigui', 'FIT-MOODLE', 'local_fitnhom');
        [, $a, $sv] = $this->bai_tap(3);
        $this->assertSame('FIT-MOODLE', trim(fullname(thongbao::nguoi_gui())));
        $this->assertSame(\core_user::NOREPLY_USER, (int) thongbao::nguoi_gui()->id);

        $sink = $this->redirectMessages();
        $this->setUser($sv[0]);
        $loi = [];
        $a->save_submission((object) ['userid' => $sv[0]->id, 'onlinetext_editor' => ['itemid' => file_get_unused_draft_itemid(),
            'text' => 'Bài', 'format' => FORMAT_HTML], 'fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]], $loi);
        $tb = array_values(array_filter($sink->get_messages(), fn($m) => $m->component === 'local_fitnhom'));
        $sink->close();
        $this->assertCount(1, $tb);
        $m = $tb[0];
        $this->assertSame(\core_user::NOREPLY_USER, (int) $m->useridfrom);
        $chao = strtok(get_string('tb_duockhai', 'local_fitnhom'), "\n");
        $ky = get_string('tb_kyten', 'local_fitnhom', 'FIT-MOODLE');
        $this->assertStringStartsWith($chao, $m->fullmessage);
        $this->assertStringStartsWith('<p>' . $chao . '</p>', $m->fullmessagehtml);
        foreach ([$m->fullmessage, $m->fullmessagehtml] as $chu) {
            $this->assertStringContainsString($ky, $chu);
            $this->assertStringContainsString('/local/fitnhom/tuchoi.php', $chu);
        }
        $this->assertStringContainsString('>' . get_string('tb_baolai', 'local_fitnhom') . '</a>', $m->fullmessagehtml);
        // Chữ tiếng Việt của thư (máy kiểm chạy tiếng Anh): có chào, "liệt kê", không còn "khai".
        $string = [];
        include(__DIR__ . '/../lang/vi/local_fitnhom.php');
        foreach (['tb_duockhai', 'tb_duockhai_html', 'tb_tuchoi', 'tb_tuchoi_html'] as $khoa) {
            $this->assertStringStartsWith(str_contains($khoa, 'html') ? '<p>Chào em,</p>' : 'Chào em,', $string[$khoa]);
            $this->assertStringContainsString('{$a->ky}', $string[$khoa]);
        }
        $lietke = ['tb_duockhai', 'tb_duockhai_html', 'tb_duockhai_tieude', 'tb_duockhai_ngan', 'messageprovider:duockhai'];
        foreach ($lietke as $khoa) {
            $this->assertStringContainsString('liệt kê', $string[$khoa]);
            $this->assertStringNotContainsString('khai', $string[$khoa]);
        }
        $this->assertSame('Hệ thống {$a}', $string['tb_kyten']);

        // Thư thật (bộ gửi email của Moodle): tên người gửi.
        $mail = $this->redirectEmails();
        $assign = (object) ['id' => $a->get_instance()->id, 'course' => $a->get_course()->id, 'name' => 'Bài X'];
        $k = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        $cm = (object) ['id' => $a->get_course_module()->id];
        thongbao::duoc_khai((int) $k->id, (int) $sv[1]->id, $assign, $cm, (int) $sv[0]->id);
        set_config('nguoigui', '', 'local_fitnhom');
        thongbao::duoc_khai((int) $k->id, (int) $sv[1]->id, $assign, $cm, (int) $sv[0]->id);
        $thu = $mail->get_messages();
        $mail->close();
        $this->assertCount(2, $thu);
        $tu = fn($t) => preg_match('/^From: (.*)$/m', $t->header, $x) ? $x[1] : '';
        $this->assertStringStartsWith('"FIT-MOODLE', $tu($thu[0]));
        $this->assertStringNotContainsString(get_string('noreplyname'), $tu($thu[0]));
        $this->assertStringContainsString(get_string('noreplyname'), $tu($thu[1]), 'Để trống: như Moodle');

        // Thư cho người nộp khi bạn báo không cùng làm.
        set_config('nguoigui', 'FIT-MOODLE', 'local_fitnhom');
        $sink = $this->redirectMessages();
        khai::tu_choi($this->tv_id((int) $sv[0]->id, (int) $sv[1]->id, (int) $assign->id), (int) $sv[1]->id, false, time());
        $tc = array_values(array_filter($sink->get_messages(), fn($m) => $m->component === 'local_fitnhom'));
        $sink->close();
        $this->assertCount(1, $tc);
        $chaotc = '<p>' . strtok(get_string('tb_tuchoi', 'local_fitnhom'), "\n") . '</p>';
        $this->assertStringStartsWith($chaotc, $tc[0]->fullmessagehtml);
        $this->assertStringContainsString($ky, $tc[0]->fullmessage);
    }

    /**
     * Dòng local_fitnhom_tv của bạn $uid trong câu khai của người nộp $nguoinop.
     *
     * @param int $nguoinop Người nộp.
     * @param int $uid Bạn được liệt kê.
     * @param int $aid Bài tập.
     * @return int
     */
    private function tv_id(int $nguoinop, int $uid, int $aid): int {
        global $DB;
        $kid = $DB->get_field('local_fitnhom_khai', 'id', ['assignid' => $aid, 'userid' => $nguoinop], MUST_EXIST);
        return (int) $DB->get_field('local_fitnhom_tv', 'id', ['khaiid' => $kid, 'userid' => $uid], MUST_EXIST);
    }
}
