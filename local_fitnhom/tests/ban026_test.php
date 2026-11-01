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
 * Bản 0.2.6 (chủ dự án 01/11/2026 16:43 — giảng viên hỏi sao có thêm thư mục fitnhom khi tải bài): *Tải toàn bộ bài nộp* không
 * còn tệp ghi chú Thanh-vien-nhom.txt (Q2 như ban đầu); chọn *Bài nhóm* thì hiện dòng nhắc ghi đủ thông tin nhóm vào bài nộp.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_fitnhom\khai
 * @covers     \assign_submission_fitnhom
 */
final class ban026_test extends \advanced_testcase {
    use \local_fitnhom_dung;

    /**
     * Gói tải chỉ có tệp em nộp: không thư mục …_assignsubmission_fitnhom, kể cả bài nhóm, bài một mình; plugin không trả tệp
     * nào (Chú thích PDF, gói phản hồi cũng không thấy).
     */
    public function test_goi_tai_khong_co_tep_fitnhom(): void {
        $this->resetAfterTest();
        $this->che_do(phamvi::BAT);
        [, $a, $sv, $gv] = $this->bai_tap(3);
        $this->nop($sv[0], $a, ['fitnhom_loai' => khai::NHOM, 'fitnhom_tv' => [$sv[1]->id]]);
        $this->nop($sv[2], $a, ['fitnhom_loai' => khai::MOT]);
        $this->setUser($gv);

        $d = new \mod_assign\downloader($a);
        $d->load_filelist();
        $tep = array_keys((new \ReflectionProperty($d, 'filesforzipping'))->getValue($d));
        $this->assertNotEmpty($tep, 'Gói có bài trực tuyến của em');
        $this->assertSame([], array_values(array_filter($tep, fn($t) => str_contains($t, 'fitnhom'))), implode(' | ', $tep));

        $p = $a->get_submission_plugin_by_type('fitnhom');
        $sub = $this->bai_nop($a, $sv[0]);
        $sub->exportfullpath = true;
        $this->assertSame([], $p->get_files($sub, $sv[0]));
    }

    /**
     * Dòng nhắc "Lưu ý: chọn bạn ở đây không thay cho báo cáo — … MSSV, họ tên …" (chủ dự án 16:46) nằm ngay sau ô các bạn,
     * ẩn khi nút *Bài nhóm* chưa được chọn (cùng điều kiện với ô các bạn).
     */
    public function test_dong_nhac_ghi_du_thong_tin(): void {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        $this->resetAfterTest();
        $m = new \MoodleQuickForm('thu', 'post', '');
        khai::them_cau_hoi($m, [], '', true);
        $this->assertTrue($m->elementExists('fitnhom_nhac'));
        $ten = array_map(fn($e) => $e->getName(), $m->_elements);
        $this->assertSame(array_search('fitnhom_tv', $ten) + 1, array_search('fitnhom_nhac', $ten), 'Ngay sau ô các bạn');
        $this->assertStringContainsString(get_string('nhac_ghidu', 'local_fitnhom'), $m->toHtml());
        $an = (new \ReflectionProperty($m, '_hideifs'))->getValue($m);
        $this->assertContains('fitnhom_nhac', $an['fitnhom_loai']['notchecked'][khai::NHOM]);

        $string = [];
        include(__DIR__ . '/../lang/vi/local_fitnhom.php');
        $this->assertStringStartsWith('Lưu ý:', $string['nhac_ghidu']);
        foreach (['không thay cho báo cáo', 'MSSV', 'họ tên'] as $y) {
            $this->assertStringContainsString($y, $string['nhac_ghidu'], 'Em không hiểu là chọn bạn rồi thì báo cáo khỏi ghi');
        }
        $this->assertSame([], array_filter(array_keys($string), fn($k) => str_starts_with($k, 'ghichu_')));
    }

    /**
     * Chữ sinh viên thấy (trang *Không phải em*, hộp nổi, dải nhắc, trang trả lời cho bài nộp qua ứng dụng, thông báo lỗi) không
     * còn "khai" — thống nhất với thư: bạn được nhắc tới thì "liệt kê em", người nộp thì "cho biết thành viên nhóm" (chủ dự án
     * 01/11/2026 16:51). Chữ của giảng viên / quản trị giữ nguyên.
     */
    public function test_chu_cua_sinh_vien_khong_con_khai(): void {
        $string = [];
        include(__DIR__ . '/../lang/vi/local_fitnhom.php');
        $sv = ['dai_chu', 'dai_nut', 'dakhai', 'hopnoi_giaithich', 'hopnoi_tieude', 'khongconbai', 'loi_khongphaicuaem',
            'loi_thamso', 'trangkhai', 'tuchoi_hoi', 'tuchoi_da', 'tuchoi_khongcon', 'goiy', 'nhac_ghidu', 'cauhoi', 'chon_mot',
            'chon_nhom', 'cacban', 'cacban_go', 'cacban_chua', 'loi_chuachon', 'khongphaiem', 'hoantac'];
        foreach ($sv as $khoa) {
            $this->assertArrayHasKey($khoa, $string);
            $this->assertStringNotContainsStringIgnoringCase('khai', $string[$khoa], $khoa);
        }
        $this->assertStringContainsString('liệt kê em', $string['tuchoi_hoi']);
        $this->assertStringContainsString('chưa cho biết thành viên nhóm', $string['hopnoi_tieude']);
    }
}
