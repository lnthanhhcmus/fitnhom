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

use core_privacy\local\request\writer;
use local_fitnhom\privacy\provider;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/helper.php');

/**
 * Bản 0.2 (TL60 mục 5.10): trang *Thành viên nhóm* của giảng viên, định dạng course loại trừ, hai hàm hợp đồng mới, Cần xem,
 * Quét ngay đếm bài đã xem, hộp nổi nhẹ (bộ đệm), nộp lần hai, Excel, privacy xuất danh sách chờ, chi phí thanhvien().
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\baocao
 * @covers     \local_fitnhom\output\course
 * @covers     \local_fitnhom\phamvi
 * @covers     \local_fitnhom\api
 * @covers     \local_fitnhom\cho
 * @covers     \local_fitnhom\hook_callbacks
 * @covers     \local_fitnhom\tomtat
 * @covers     \local_fitnhom\privacy\provider
 */
final class ban02_test extends \core_privacy\tests\provider_testcase {
    use \local_fitnhom_dung;

    /**
     * Dựng ca chuẩn của mục 5.10 ca 2: A khai B (bài nhóm), C một mình, D nộp không có câu khai (vào hộp nổi).
     *
     * @return array [course, assign, sinh viên[], giảng viên]
     */
    private function ca_chuan(): array {
        $this->che_do(phamvi::BAT);
        [$course, $a, $sv, $gv] = $this->bai_tap(5);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $this->nop($sv[2], $a, ['fitnhom_loai' => khai::MOT]);
        $this->nop($sv[3], $a);
        return [$course, $a, $sv, $gv];
    }

    /**
     * Ca 1: trang course.php — sinh viên bị từ chối; giảng viên và trợ giảng (vai teacher) vào được; course không áp dụng chỉ
     * có câu lý do. Mục điều hướng course và liên kết ở bảng chấm chỉ cho người chấm được bài.
     */
    public function test_trang_giang_vien_quyen(): void {
        global $CFG, $OUTPUT;
        require_once($CFG->dirroot . '/local/fitnhom/lib.php');
        $this->resetAfterTest();
        [$course, $a, $sv, $gv] = $this->ca_chuan();
        $tg = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->setUser($sv[0]);
        try {
            output\course::kiem_quyen((int) $course->id);
            $this->fail('Sinh viên không được vào trang Thành viên nhóm');
        } catch (\required_capability_exception $e) {
            $this->assertSame('nopermissions', $e->errorcode);
        }
        $ctx = \context_course::instance($course->id);
        $nav = \navigation_node::create('x');
        local_fitnhom_extend_navigation_course($nav, $course, $ctx);
        $this->assertFalse($nav->find('local_fitnhom_course', \navigation_node::TYPE_SETTING));
        foreach ([$gv, $tg] as $u) {
            $this->setUser($u);
            $this->assertSame((int) $ctx->id, (int) output\course::kiem_quyen((int) $course->id)->id);
            $d = (new output\course((int) $course->id))->export_for_template($OUTPUT);
            $this->assertTrue($d['apdung']);
            $this->assertTrue($d['coban']);
            $html = $OUTPUT->render_from_template('local_fitnhom/course', $d);
            $this->assertStringContainsString('local-fitnhom-theobai', $html);
            $nav = \navigation_node::create('x');
            local_fitnhom_extend_navigation_course($nav, $course, $ctx);
            $this->assertNotEmpty($nav->find('local_fitnhom_course', \navigation_node::TYPE_SETTING));
        }
        // Liên kết ở cột bảng chấm: một lần mỗi lượt, chỉ người chấm được.
        tomtat::dat_lai();
        $this->assertStringContainsString('/local/fitnhom/course.php', tomtat::lien_ket((int) $course->id));
        $this->assertSame('', tomtat::lien_ket((int) $course->id));
        tomtat::dat_lai();
        $this->setUser($sv[1]);
        $this->assertSame('', tomtat::lien_ket((int) $course->id));
        // Course không áp dụng: chỉ câu lý do, không bảng.
        $this->setUser($gv);
        $this->che_do(phamvi::TAT);
        $d = (new output\course((int) $course->id))->export_for_template($OUTPUT);
        $this->assertFalse($d['apdung']);
        $this->assertSame(get_string('course_lydo_tat', 'local_fitnhom'), $d['lydo']);
        $this->assertSame([], $d['bai']);
        $html = $OUTPUT->render_from_template('local_fitnhom/course', $d);
        $this->assertStringContainsString(get_string('course_lydo_tat', 'local_fitnhom'), $html);
        $this->assertStringNotContainsString('<table', $html);
    }

    /**
     * Ca 2: số liệu theo bài khớp dữ liệu dựng.
     */
    public function test_so_lieu_theo_bai(): void {
        $this->resetAfterTest();
        [$course, $a] = $this->ca_chuan();
        $ds = baocao::theo_bai((int) $course->id);
        $this->assertCount(1, $ds);
        $b = $ds[0];
        $this->assertSame((int) $a->get_instance()->id, $b['assignid']);
        $this->assertFalse($b['nhommd']);
        $this->assertSame(
            ['nop' => 3, 'mot' => 1, 'nhom' => 1, 'ban' => 1, 'tuchoi' => 0, 'chuakhai' => 1, 'cuu' => 1],
            array_intersect_key($b, array_flip(['nop', 'mot', 'nhom', 'ban', 'tuchoi', 'chuakhai', 'cuu']))
        );
        // Bài bật "Nộp theo nhóm" của Moodle: có tên, không số.
        $nhom = $this->create_instance($course, ['teamsubmission' => 1, 'assignsubmission_onlinetext_enabled' => 1,
            'submissiondrafts' => 0]);
        $this->nop($this->getDataGenerator()->create_and_enrol($course, 'student'), $nhom);
        $ds = baocao::theo_bai((int) $course->id);
        $this->assertCount(2, $ds);
        $this->assertTrue($ds[1]['nhommd']);
        $this->assertSame(0, $ds[1]['cuu']);
    }

    /**
     * Ca 3: chi tiết một bài — dòng của A có B; B bấm "Không phải em" → gạch ngang và "được tính nhờ nhóm" về 0.
     */
    public function test_chi_tiet_bai(): void {
        global $OUTPUT;
        $this->resetAfterTest();
        [$course, $a, $sv, $gv] = $this->ca_chuan();
        $aid = (int) $a->get_instance()->id;
        $ct = baocao::chi_tiet($aid);
        $this->assertCount(3, $ct);
        $donga = array_values(array_filter($ct, fn($d) => $d['userid'] === (int) $sv[0]->id))[0];
        $this->assertSame(khai::NHOM, $donga['loai']);
        $this->assertSame([['ten' => khai::nhan($sv[1]), 'tuchoi' => false]], $donga['ban']);
        $dongd = array_values(array_filter($ct, fn($d) => $d['userid'] === (int) $sv[3]->id))[0];
        $this->assertSame('', $dongd['loai']);
        $this->assertTrue($dongd['cho']);
        $k = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        khai::tu_choi((int) $k->tv[(int) $sv[1]->id]->id, (int) $sv[1]->id, false, time());
        $donga = array_values(array_filter(baocao::chi_tiet($aid), fn($d) => $d['userid'] === (int) $sv[0]->id))[0];
        $this->assertTrue($donga['ban'][0]['tuchoi']);
        $this->assertSame(0, baocao::theo_bai((int) $course->id)[0]['cuu']);
        $this->setUser($gv);
        $html = $OUTPUT->render_from_template('local_fitnhom/course', (new output\course((int) $course->id, $aid))
            ->export_for_template($OUTPUT));
        $this->assertStringContainsString('<del>' . s(khai::nhan($sv[1])) . '</del>', $html);
        $this->assertStringContainsString(get_string('chuakhai', 'local_fitnhom'), $html);
    }

    /**
     * Ca 4: course định dạng fitcnl không áp dụng dù tên không khớp mẫu; bỏ định dạng khỏi danh sách thì áp dụng.
     */
    public function test_loai_tru_dinh_dang(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        set_config('loaitru_format', 'fitcnl', 'local_fitnhom');
        $c = $this->getDataGenerator()->create_course(['shortname' => 'Bat ky']);
        $DB->set_field('course', 'format', 'fitcnl', ['id' => $c->id]);
        phamvi::xoa_dem();
        $this->assertFalse(phamvi::course_ap_dung((int) $c->id));
        $this->assertSame(phamvi::LY_FORMAT, phamvi::ly_do((int) $c->id));
        set_config('loaitru_format', '', 'local_fitnhom');
        phamvi::xoa_dem();
        $this->assertTrue(phamvi::course_ap_dung((int) $c->id));
        $this->assertSame('', phamvi::ly_do((int) $c->id));
        // Các lý do khác.
        $this->che_do(phamvi::THU, 'KHAC');
        $this->assertSame(phamvi::LY_KHONG_THU, phamvi::ly_do((int) $c->id));
        $this->che_do(phamvi::BAT);
        $c2 = $this->getDataGenerator()->create_course(['shortname' => 'FITCNL:K25-1']);
        $this->assertSame(phamvi::LY_MAU, phamvi::ly_do((int) $c2->id));
        $this->che_do(phamvi::TAT);
        $this->assertSame(phamvi::LY_TAT, phamvi::ly_do((int) $c->id));
    }

    /**
     * Ca 5: cua_nguoi() (H5) — khoảng đóng hai đầu, mỗi bài tập một dòng sớm nhất, bỏ bài em đã bấm "Không phải em".
     */
    public function test_cua_nguoi(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [$course, $x, $sv] = $this->bai_tap(4);
        $y = $this->create_instance($course, ['assignsubmission_onlinetext_enabled' => 1, 'submissiondrafts' => 0]);
        $b = (int) $sv[1]->id;
        // X: A (1000) và D (1500) cùng khai B; Y: C (5000) khai B.
        $this->nop($sv[0], $x, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$b]]);
        $DB->set_field('assign_submission', 'timemodified', 1000, ['id' => $this->bai_nop($x, $sv[0])->id]);
        $this->nop($sv[3], $x, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$b]]);
        $DB->set_field('assign_submission', 'timemodified', 1500, ['id' => $this->bai_nop($x, $sv[3])->id]);
        $this->nop($sv[2], $y, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$b]]);
        $DB->set_field('assign_submission', 'timemodified', 5000, ['id' => $this->bai_nop($y, $sv[2])->id]);
        $xid = (int) $x->get_instance()->id;
        $yid = (int) $y->get_instance()->id;
        $this->assertSame([$xid => ['nguoinop' => (int) $sv[0]->id, 'luc' => 1000]], api::cua_nguoi($b, 0, 2000));
        $this->assertSame([$xid => ['nguoinop' => (int) $sv[0]->id, 'luc' => 1000]], api::cua_nguoi($b, 1000, 1000));
        $this->assertSame([$xid => ['nguoinop' => (int) $sv[3]->id, 'luc' => 1500]], api::cua_nguoi($b, 1001, 1500));
        $this->assertSame([$yid => ['nguoinop' => (int) $sv[2]->id, 'luc' => 5000]], api::cua_nguoi($b, 5000, 9999));
        $this->assertSame([], api::cua_nguoi((int) $sv[0]->id, 0, 9999), 'Người nộp không có trong kết quả của chính mình');
        // B bấm "Không phải em" ở cả hai bài nộp của X → chỉ còn Y.
        foreach ([$sv[0], $sv[3]] as $u) {
            $k = khai::cua_bai_nop((int) $this->bai_nop($x, $u)->id);
            khai::tu_choi((int) $k->tv[$b]->id, $b, false, time());
        }
        $this->assertSame([$yid], array_keys(api::cua_nguoi($b, 0, 9999)));
    }

    /**
     * Ca 6: ap_dung() theo chế độ (H6).
     */
    public function test_ap_dung(): void {
        $this->resetAfterTest();
        $c1 = $this->getDataGenerator()->create_course(['shortname' => 'THU-1']);
        $c2 = $this->getDataGenerator()->create_course(['shortname' => 'KHAC-1']);
        $this->assertFalse(api::ap_dung((int) $c1->id), 'Mới cài: Tắt');
        $this->che_do(phamvi::THU, "THU-1");
        $this->assertTrue(api::ap_dung((int) $c1->id));
        $this->assertFalse(api::ap_dung((int) $c2->id));
        $this->che_do(phamvi::BAT);
        $this->assertTrue(api::ap_dung((int) $c2->id));
        $this->assertFalse(api::ap_dung(SITEID));
        $this->che_do(phamvi::TAT);
        $this->assertFalse(api::ap_dung((int) $c1->id));
    }

    /**
     * Ca 7: Cần xem — 3 lần "Không phải em" trong 60 ngày → có dòng, 2 lần → không; 5 người nộp khác nhau khai cùng em → có.
     */
    public function test_can_xem(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        set_config('batthuong_tuchoi', 3, 'local_fitnhom');
        set_config('batthuong_khai', 5, 'local_fitnhom');
        [, $a, $sv] = $this->bai_tap(7);
        $luc = time();
        $this->assertSame(['tuchoi' => [], 'khai' => []], baocao::can_xem($luc));
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id, $sv[2]->id, $sv[3]->id]]);
        $k = khai::cua_bai_nop((int) $this->bai_nop($a, $sv[0])->id);
        foreach ([1, 2] as $i) {
            khai::tu_choi((int) $k->tv[(int) $sv[$i]->id]->id, (int) $sv[$i]->id, false, $luc - DAYSECS);
        }
        $this->assertSame([], baocao::can_xem($luc)['tuchoi'], '2 lần: chưa đủ');
        khai::tu_choi((int) $k->tv[(int) $sv[3]->id]->id, (int) $sv[3]->id, false, $luc - DAYSECS);
        $cx = baocao::can_xem($luc);
        $this->assertCount(1, $cx['tuchoi']);
        $this->assertSame((int) $sv[0]->id, $cx['tuchoi'][0]['userid']);
        $this->assertSame(3, $cx['tuchoi'][0]['so']);
        $this->assertSame((int) $a->get_instance()->id, $cx['tuchoi'][0]['bai'][0]['assignid']);
        $this->assertSame([], baocao::can_xem($luc + 61 * DAYSECS)['tuchoi'], 'Quá 60 ngày không tính');
        // Năm người nộp khác nhau (sv1..sv5 — sv1, sv2, sv3 đã từ chối bài của A nhưng tự nộp được) khai cùng sv6.
        foreach ([1, 2, 3, 4] as $i) {
            $this->nop($sv[$i], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[6]->id]]);
        }
        $this->assertSame([], baocao::can_xem($luc)['khai'], '4 người: chưa đủ');
        $this->nop($sv[5], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[6]->id]]);
        $cx = baocao::can_xem($luc);
        $this->assertCount(1, $cx['khai']);
        $this->assertSame((int) $sv[6]->id, $cx['khai'][0]['userid']);
        $this->assertSame(5, $cx['khai'][0]['so']);
    }

    /**
     * Ca 8: Quét ngay trả số bài đã xem và số bài thêm; sự kiện có soxem; cấu hình lưu để hiện ở Tình hình.
     */
    public function test_quet_xem(): void {
        $this->resetAfterTest();
        [, $a, $sv] = $this->bai_tap(3);
        $this->che_do(phamvi::BAT);
        $this->che_do(phamvi::TAT);
        $this->nop($sv[0], $a);
        $this->nop($sv[1], $a);
        $this->che_do(phamvi::BAT);
        $ev = $this->redirectEvents();
        $this->assertSame(['xem' => 2, 'them' => 2], cho::quet_xem(time(), 2));
        $e = array_values(array_filter($ev->get_events(), fn($x) => $x instanceof event\quet_ngay))[0];
        $ev->close();
        $this->assertSame(2, $e->other['soxem']);
        $this->assertSame(2, $e->other['so']);
        $this->assertStringContainsString('xét 2 bài nộp', $e->get_description());
        $this->assertSame(['xem' => 0, 'them' => 0], cho::quet_xem(time(), 2), 'Bài đã chờ không xét lại');
        $t = tinhhinh::so_lieu();
        $this->assertSame(0, $t['quetxem']);
        $this->assertSame(0, $t['quetthem']);
        $this->assertSame(0, cho::quet(time(), 2), 'quet() giữ chữ ký 0.1');
    }

    /**
     * Ca 9: hộp nổi nhẹ — không có dòng chờ thì không thêm gì (và không dựng danh sách); có dòng chờ thì có dải và gọi JS;
     * đã bấm "Để sau" hôm nay thì có dải, không gọi JS; bộ đệm bị xóa đúng khi thêm và khi khai.
     */
    public function test_hop_noi_nhe(): void {
        global $PAGE;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv] = $this->bai_tap(2);
        $ve = $PAGE->get_renderer('core');
        $u = (int) $sv[0]->id;
        $dem = \cache::make('local_fitnhom', 'cho');
        $PAGE->set_url('/my/');
        $js = fn() => count((new \ReflectionProperty($PAGE->requires, 'amdjscode'))->getValue($PAGE->requires));
        $this->setUser($sv[0]);
        $hook = new \core\hook\output\before_standard_top_of_body_html_generation($ve);
        hook_callbacks::them_vao($hook, $u);
        $this->assertSame('', $hook->get_output());
        $this->assertSame(0, $dem->get($u), 'Đã ghi đệm 0 sau lần kiểm');
        $this->nop($sv[0], $a);
        $this->assertFalse($dem->get($u), 'cho::them xóa khóa đệm');
        $this->assertTrue(cho::co_cho($u));
        $truoc = $js();
        $hook = new \core\hook\output\before_standard_top_of_body_html_generation($ve);
        hook_callbacks::them_vao($hook, $u);
        $this->assertStringContainsString('local-fitnhom-dai', $hook->get_output());
        $this->assertSame($truoc + 1, $js(), 'Gọi hộp nổi');
        set_user_preference(hook_callbacks::DESAU, userdate(time(), '%Y%m%d', 99, false), $u);
        $hook = new \core\hook\output\before_standard_top_of_body_html_generation($ve);
        hook_callbacks::them_vao($hook, $u);
        $this->assertStringContainsString('local-fitnhom-dai', $hook->get_output(), 'Để sau: vẫn có dải');
        $this->assertSame($truoc + 1, $js(), 'Để sau: không gọi hộp nổi');
        khai::luu($this->bai_nop($a, $sv[0]), khai::MOT, [], khai::HOPNOI, time(), $u);
        $this->assertFalse($dem->get($u), 'khai::luu xóa khóa đệm');
        $this->assertFalse(cho::co_cho($u));
        // Khi đổi chế độ thì xóa sạch đệm.
        $dem->set($u, 1);
        $this->che_do(phamvi::THU, 'X');
        $this->assertFalse($dem->get($u));
    }

    /**
     * Ca 10: nộp lần hai (giảng viên mở lại lần nộp, em tạo lần mới từ lần trước) — câu khai được chép sang bài nộp mới.
     */
    public function test_nop_lan_hai(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv, $gv] = $this->bai_tap(3, ['attemptreopenmethod' => ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL, 'maxattempts' => -1]);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $cu = $this->bai_nop($a, $sv[0]);
        $this->setUser($gv);
        $_POST['sesskey'] = sesskey();
        $a->testable_process_add_attempt((int) $sv[0]->id);
        $this->setUser($sv[0]);
        $loi = [];
        $sink = $this->redirectMessages();
        $this->assertTrue($a->copy_previous_attempt($loi), implode('; ', $loi));
        $sink->close();
        $moi = $this->bai_nop($a, $sv[0]);
        $this->assertNotEquals((int) $cu->id, (int) $moi->id);
        $k = khai::cua_bai_nop((int) $moi->id);
        $this->assertNotNull($k, 'Câu khai được chép sang lần nộp mới');
        $this->assertSame(khai::NHOM, $k->loai);
        $this->assertSame([(int) $sv[1]->id], array_keys($k->tv));
        $tv = api::thanhvien((int) $a->get_instance()->id);
        $this->assertSame((int) $sv[0]->id, $tv[(int) $sv[1]->id]['nguoinop']);
    }

    /**
     * Ca 11: Excel mở được, hai sheet; tên bài bắt đầu bằng "=" là chuỗi (không thành công thức).
     */
    public function test_excel(): void {
        global $CFG;
        require_once($CFG->libdir . '/phpspreadsheet/vendor/autoload.php');
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [$course, $a, $sv] = $this->bai_tap(3, ['name' => '=1+1']);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $tep = make_request_directory() . '/t.xlsx';
        baocao::ghi_excel(baocao::bang_course((int) $course->id), $tep);
        $wb = \PhpOffice\PhpSpreadsheet\IOFactory::load($tep);
        $this->assertSame(
            [get_string('sheet_theobai', 'local_fitnhom'), get_string('sheet_chitiet', 'local_fitnhom')],
            $wb->getSheetNames()
        );
        $o = $wb->getSheet(0)->getCell('A2');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING, $o->getDataType());
        $this->assertSame('=1+1', $o->getValue());
        $this->assertEquals(1, $wb->getSheet(0)->getCell('C2')->getValue(), 'Số bài nộp là số');
        $this->assertSame(\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC, $wb->getSheet(0)->getCell('C2')->getDataType());
        $this->assertSame(khai::nhan($sv[0]), $wb->getSheet(1)->getCell('B2')->getValue());
        $this->assertStringContainsString(khai::nhan($sv[1]), $wb->getSheet(1)->getCell('E2')->getValue());
        // Bảng theo course của quản trị.
        $tep2 = make_request_directory() . '/c.xlsx';
        baocao::ghi_excel(baocao::bang_theo_course(0), $tep2);
        $wb2 = \PhpOffice\PhpSpreadsheet\IOFactory::load($tep2);
        $this->assertSame($course->shortname, $wb2->getSheet(0)->getCell('A2')->getValue());
    }

    /**
     * Ca 12: privacy — xuất dữ liệu của người có bài chờ khai có danh sách chờ; xóa người đó xóa đệm.
     */
    public function test_privacy_xuat_cho(): void {
        $this->resetAfterTest();
        [, $a, $sv] = $this->ca_chuan();
        $d = $sv[3];
        $ctx = $a->get_context();
        $this->assertContains('local_fitnhom_cho', array_map(
            fn($x) => $x->get_name(),
            provider::get_metadata(new \core_privacy\local\metadata\collection('local_fitnhom'))->get_collection()
        ));
        $this->export_context_data_for_user((int) $d->id, $ctx, 'local_fitnhom');
        $x = writer::with_context($ctx)->get_data([get_string('pluginname', 'local_fitnhom')]);
        $this->assertCount(1, $x->cho);
        $this->assertSame((int) $a->get_instance()->id, $x->cho[0]['assignid']);
        $this->assertSame((int) $this->bai_nop($a, $d)->id, $x->cho[0]['submissionid']);
        $this->assertTrue(cho::co_cho((int) $d->id));
        $ds = new \core_privacy\local\request\approved_contextlist($d, 'local_fitnhom', [(int) $ctx->id]);
        provider::delete_data_for_user($ds);
        $this->assertFalse(cho::co_cho((int) $d->id));
    }

    /**
     * TL60 mục 11: thanhvien() một truy vấn với bài tập có 300 bài nộp (dữ liệu chèn thẳng), chạy nhanh.
     */
    public function test_thanhvien_300_bai_nop(): void {
        global $DB;
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [$course, $a] = $this->bai_tap(1);
        $aid = (int) $a->get_instance()->id;
        $g = $this->getDataGenerator();
        $ban = $g->create_user();
        for ($i = 0; $i < 300; $i++) {
            $u = $g->create_user();
            $sid = $DB->insert_record('assign_submission', (object) ['assignment' => $aid, 'userid' => $u->id,
                'status' => 'submitted', 'latest' => 1, 'attemptnumber' => 0, 'groupid' => 0, 'timecreated' => 1000 + $i,
                'timemodified' => 1000 + $i]);
            $kid = $DB->insert_record('local_fitnhom_khai', (object) ['assignid' => $aid, 'submissionid' => $sid,
                'courseid' => $course->id, 'userid' => $u->id, 'loai' => khai::NHOM, 'nguon' => khai::FORM,
                'usermodified' => $u->id, 'timecreated' => 1000 + $i, 'timemodified' => 1000 + $i]);
            $DB->insert_record('local_fitnhom_tv', (object) ['khaiid' => $kid, 'assignid' => $aid, 'userid' => $ban->id,
                'trangthai' => khai::KHAI, 'timecreated' => 1000 + $i]);
        }
        $truoc = $DB->perf_get_reads();
        $t = microtime(true);
        $tv = api::thanhvien($aid);
        $giay = microtime(true) - $t;
        $this->assertSame(1, $DB->perf_get_reads() - $truoc, 'Một truy vấn');
        $this->assertSame(1000, $tv[(int) $ban->id]['luc'], 'Sớm nhất');
        $this->assertLessThan(0.25, $giay);
    }
}
