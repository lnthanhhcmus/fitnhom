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
 * Câu khai (TL54 mục 2, 3, 3a): lưu qua trang nộp bài, kiểm bạn, thông báo, "Không phải em", gợi ý, cùng nhóm.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\khai
 * @covers     \assign_submission_fitnhom
 */
final class khai_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Nộp bài kèm câu khai nhóm: lưu đúng, hai bạn nhận thông báo (có liên kết Không phải em), sự kiện không chép tên.
     */
    public function test_nop_bai_nhom(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(4);
        $sink = $this->redirectMessages();
        $ev = $this->redirectEvents();
        $this->setUser($sv[0]);
        $loi = [];
        $bai = ['itemid' => file_get_unused_draft_itemid(), 'text' => 'Bài', 'format' => FORMAT_HTML];
        $ok = $a->save_submission((object) ['userid' => $sv[0]->id, 'onlinetext_editor' => $bai, 'fitnhom_loai' => khai::NHOM,
            'fitnhom_tv' => [$sv[1]->id, $sv[2]->id]], $loi);
        $this->assertTrue($ok, implode(';', $loi));
        $k = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        $this->assertSame(khai::NHOM, $k->loai);
        $this->assertSame(khai::FORM, $k->nguon);
        $this->assertEqualsCanonicalizing([(int) $sv[1]->id, (int) $sv[2]->id], array_keys($k->tv));
        $tb = array_values(array_filter($sink->get_messages(), fn($m) => $m->component === 'local_fitnhom'));
        $sink->close();
        $this->assertCount(2, $tb);
        $this->assertEqualsCanonicalizing([(int) $sv[1]->id, (int) $sv[2]->id], array_map(fn($m) => (int) $m->useridto, $tb));
        $this->assertStringContainsString('/local/fitnhom/tuchoi.php', $tb[0]->contexturl);
        $e = array_values(array_filter($ev->get_events(), fn($x) => $x instanceof event\khai_luu));
        $ev->close();
        $this->assertCount(1, $e);
        $this->assertStringNotContainsString(fullname($sv[1]), $e[0]->get_description());
        $this->assertFalse($DB->record_exists('local_fitnhom_cho', []), 'Có câu khai thì không vào hộp nổi');
    }

    /**
     * Câu khai không tính là bài nộp; thiếu câu trả lời / bài nhóm không chọn bạn / bạn ngoài lớp / quá số bạn → không lưu.
     */
    public function test_kiem_loi(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        set_config('toida', 2, 'local_fitnhom');
        [, $a, $sv] = $this->bai_tap(4);
        $ngoai = $this->getDataGenerator()->create_user();
        $this->setUser($sv[0]);
        $loi = [];
        $this->assertFalse(
            $a->save_submission((object) ['userid' => $sv[0]->id, 'fitnhom_loai' => khai::MOT], $loi),
            'Chỉ có câu khai, không có bài'
        );
        foreach (
            [
            [['fitnhom_loai' => ''], 'loi_chuachon'],
            [['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => []], 'loi_chuachonban'],
            [['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$ngoai->id]], 'loi_khongtronglop'],
            [['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id, $sv[2]->id, $sv[3]->id]], 'loi_quanhieu'],
            ] as [$k, $ma]
        ) {
            [$ok, $loi] = $this->nop($sv[0], $a, $k);
            $this->assertFalse($ok, $ma);
            $this->assertStringContainsString(get_string($ma, 'local_fitnhom', 2), implode(' ', $loi), $ma);
        }
        $this->assertSame(0, $DB->count_records('local_fitnhom_khai'));
        // Khai chính mình vào nhóm: bỏ qua, còn bạn kia.
        [$ok] = $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[0]->id, $sv[1]->id]]);
        $this->assertTrue($ok);
        $this->assertSame([(int) $sv[1]->id], array_keys(khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id)->tv));
    }

    /**
     * Sửa câu khai: bạn bị bỏ thì xóa; bạn mới được báo, bạn cũ không báo lại; bạn đã "Không phải em" vẫn giữ từ chối.
     * Đổi sang một mình xóa hết bạn.
     */
    public function test_sua_cau_khai(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(4);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id, $sv[2]->id]]);
        $sub = $this->bai_nop($a, $sv[0]);
        $k = khai::cua_bai_nop((int) $sub->id);
        khai::tu_choi((int) $k->tv[(int) $sv[2]->id]->id, (int) $sv[2]->id, false, time());
        $sink = $this->redirectMessages();
        khai::luu($sub, khai::NHOM, [$sv[2]->id, $sv[3]->id], khai::FORM, time(), (int) $sv[0]->id);
        $tb = array_filter($sink->get_messages(), fn($m) => $m->component === 'local_fitnhom');
        $sink->close();
        $this->assertSame([(int) $sv[3]->id], array_values(array_map(fn($m) => (int) $m->useridto, $tb)));
        $k = khai::cua_bai_nop((int) $sub->id);
        $this->assertEqualsCanonicalizing([(int) $sv[2]->id, (int) $sv[3]->id], array_keys($k->tv));
        $this->assertSame(khai::TUCHOI, $k->tv[(int) $sv[2]->id]->trangthai);
        khai::luu($sub, khai::MOT, [$sv[3]->id], khai::FORM, time(), (int) $sv[0]->id);
        $k = khai::cua_bai_nop((int) $sub->id);
        $this->assertSame(khai::MOT, $k->loai);
        $this->assertSame([], $k->tv);
    }

    /**
     * "Không phải em": chỉ chính bạn được khai; người nộp được báo; hoàn tác trong 7 ngày, quá hạn thì lỗi.
     */
    public function test_khong_phai_em(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(3);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $k = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        $tvid = (int) $k->tv[(int) $sv[1]->id]->id;
        try {
            khai::tu_choi($tvid, (int) $sv[2]->id, false, time());
            $this->fail('Người khác không bấm thay được');
        } catch (\moodle_exception $e) {
            $this->assertSame('loi_khongphaicuaem', $e->errorcode);
        }
        $sink = $this->redirectMessages();
        $luc = time();
        khai::tu_choi($tvid, (int) $sv[1]->id, false, $luc);
        $tb = array_values(array_filter($sink->get_messages(), fn($m) => $m->component === 'local_fitnhom'));
        $sink->close();
        $this->assertCount(1, $tb);
        $this->assertSame((int) $sv[0]->id, (int) $tb[0]->useridto);
        $this->assertArrayNotHasKey((int) $sv[1]->id, api::thanhvien((int) $a->get_instance()->id));
        khai::tu_choi($tvid, (int) $sv[1]->id, true, $luc + 6 * DAYSECS);
        $this->assertArrayHasKey((int) $sv[1]->id, api::thanhvien((int) $a->get_instance()->id));
        khai::tu_choi($tvid, (int) $sv[1]->id, false, $luc);
        $this->expectException(\moodle_exception::class);
        khai::tu_choi($tvid, (int) $sv[1]->id, true, $luc + 8 * DAYSECS);
    }

    /**
     * TL54 mục 3a: B mở trang nộp khi A đã nộp và khai B → gợi ý (A + bạn khác của A); B nộp bản cập nhật → hai câu khai,
     * bảng chấm ghi "Cùng nhóm với bài nộp của A"; tóm tắt gạch tên bạn đã từ chối.
     */
    public function test_cung_nhom(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(4);
        $aid = (int) $a->get_instance()->id;
        $this->assertNull(khai::goi_y($aid, (int) $sv[1]->id));
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id, $sv[2]->id]]);
        $this->setUser($sv[1]);
        $this->assertStringContainsString(
            get_string('cotrongnhom', 'local_fitnhom', fullname($sv[0])),
            khai::tom_tat($a->get_user_submission($sv[1]->id, true)),
            'Bảng chấm, dòng của B chưa nộp'
        );
        $this->assertSame('', khai::tom_tat($a->get_user_submission($sv[3]->id, true)), 'Chưa nộp, không ai khai: trống');
        $g = khai::goi_y($aid, (int) $sv[1]->id);
        $this->assertSame((int) $sv[0]->id, $g['nguoinop']);
        $this->assertEqualsCanonicalizing([(int) $sv[0]->id, (int) $sv[2]->id], $g['ban']);
        $this->assertStringContainsString(fullname($sv[0]), khai::chu_goi_y($g));
        $this->nop($sv[1], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => $g['ban']]);
        $kb = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[1])->id);
        $this->assertSame([(int) $sv[0]->id], array_column(khai::cung_nhom($kb), 'userid'));
        $tt = khai::tom_tat($this->bai_nop($a, $sv[0]));
        $this->assertStringContainsString(get_string('cungnhomvoi', 'local_fitnhom', ''), $tt);
        $this->assertStringContainsString(fullname($sv[1]), $tt);
        $ka = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        khai::tu_choi((int) $ka->tv[(int) $sv[2]->id]->id, (int) $sv[2]->id, false, time());
        $this->assertStringContainsString('<del', khai::tom_tat($this->bai_nop($a, $sv[0])));
        $this->assertStringContainsString(get_string('motminh', 'local_fitnhom'), (function () use ($a, $sv) {
            $this->nop($sv[3], $a, ['fitnhom_loai' => khai::MOT]);
            return khai::tom_tat($this->bai_nop($a, $sv[3]));
        })());
    }

    /**
     * Người có quyền sửa bài nộp của em (giảng viên / quản lý) nộp thay: không bắt buộc câu khai; bài tập Nộp theo nhóm /
     * course không áp dụng / chế độ Tắt: plugin tắt.
     */
    public function test_giang_vien_va_pham_vi(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [$course, $a, $sv] = $this->bai_tap(2);
        $this->setAdminUser();
        $loi = [];
        $this->assertTrue($a->save_submission((object) ['userid' => $sv[0]->id, 'onlinetext_editor' => [
            'itemid' => file_get_unused_draft_itemid(), 'text' => 'Nộp thay', 'format' => FORMAT_HTML]], $loi), implode(';', $loi));
        $this->assertSame(0, $DB->count_records('local_fitnhom_khai'));
        $p = $a->get_submission_plugin_by_type('fitnhom');
        $this->assertFalse($p->is_configurable(), 'Giảng viên không có ô bật / tắt');
        $this->assertTrue($p->is_enabled());
        $this->assertTrue($p->is_empty($this->bai_nop($a, $sv[0])));
        [, $nhom] = $this->bai_tap(1, ['teamsubmission' => 1]);
        $this->assertFalse($nhom->get_submission_plugin_by_type('fitnhom')->is_enabled());
        [, $cnl] = $this->bai_tap(1, [], ['shortname' => 'FITCNL:LOP1']);
        $this->assertFalse($cnl->get_submission_plugin_by_type('fitnhom')->is_enabled());
        $this->che_do(phamvi::TAT);
        $a2 = new \mod_assign_testable_assign($a->get_context(), $a->get_course_module(), $course);
        $this->assertFalse($a2->get_submission_plugin_by_type('fitnhom')->is_enabled());
    }

    /**
     * Chép câu khai sang lần nộp mới; xóa bài nộp / bài tập dọn dữ liệu.
     */
    public function test_sao_chep_va_xoa(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(3);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $s = $this->bai_nop($a, $sv[0]);
        khai::sao_chep((int) $s->id, 999999);
        $this->assertSame([(int) $sv[1]->id], array_keys(khai::cua_bai_nop(999999)->tv));
        khai::xoa_bai_nop(999999);
        $this->assertNull(khai::cua_bai_nop(999999));
        course_delete_module((int) $a->get_course_module()->id);
        $this->assertSame(0, $DB->count_records('local_fitnhom_khai'));
        $this->assertSame(0, $DB->count_records('local_fitnhom_tv'));
    }
}
