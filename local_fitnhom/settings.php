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
 * Trang quản trị Fit-Nhóm (chỉ admin): Quản trị trang › Plugin › Mô-đun cục bộ › Fit-Nhóm.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_fitnhom_muc', new lang_string('pluginname', 'local_fitnhom')));
    $s = new admin_settingpage('local_fitnhom', new lang_string('caidat', 'local_fitnhom'));
    if ($ADMIN->fulltree) {
        $s->add(new admin_setting_heading(
            'local_fitnhom/mucdich',
            new lang_string('mucdich_tieude', 'local_fitnhom'),
            new lang_string('mucdich', 'local_fitnhom')
        ));
        $c = new admin_setting_configselect(
            'local_fitnhom/chedo',
            new lang_string('chedo', 'local_fitnhom'),
            new lang_string('chedo_help', 'local_fitnhom'),
            'TAT',
            [
                'TAT' => new lang_string('chedo_TAT', 'local_fitnhom'),
                'THU' => new lang_string('chedo_THU', 'local_fitnhom'),
                'BAT' => new lang_string('chedo_BAT', 'local_fitnhom'),
            ]
        );
        $c->set_updatedcallback('local_fitnhom_chedo_doi');
        $s->add($c);
        $s->add(new admin_setting_configtextarea(
            'local_fitnhom/khoathu',
            new lang_string('khoathu', 'local_fitnhom'),
            new lang_string('khoathu_help', 'local_fitnhom'),
            '',
            PARAM_RAW,
            40,
            4
        ));
        $f = new admin_setting_configtextarea(
            'local_fitnhom/loaitru_format',
            new lang_string('caidat_loaitru_format', 'local_fitnhom'),
            new lang_string('caidat_loaitru_format_mota', 'local_fitnhom'),
            'fitcnl',
            PARAM_RAW,
            40,
            3
        );
        $f->set_updatedcallback('local_fitnhom_phamvi_doi');
        $s->add($f);
        $s->add(new admin_setting_configtextarea(
            'local_fitnhom/loaitru_mau',
            new lang_string('loaitru_mau', 'local_fitnhom'),
            new lang_string('loaitru_mau_help', 'local_fitnhom'),
            'FITCNL:%',
            PARAM_RAW,
            40,
            4
        ));
        $s->add(new admin_setting_configmultiselect(
            'local_fitnhom/loaitru_danhmuc',
            new lang_string('loaitru_danhmuc', 'local_fitnhom'),
            new lang_string('loaitru_danhmuc_help', 'local_fitnhom'),
            [],
            core_course_category::make_categories_list()
        ));
        $s->add(new admin_setting_configtext(
            'local_fitnhom/toida',
            new lang_string('toida', 'local_fitnhom'),
            new lang_string('toida_help', 'local_fitnhom'),
            10,
            PARAM_INT
        ));
        $s->add(new admin_setting_configtext(
            'local_fitnhom/batthuong_tuchoi',
            new lang_string('caidat_batthuong_tuchoi', 'local_fitnhom'),
            new lang_string('caidat_batthuong_tuchoi_mota', 'local_fitnhom'),
            3,
            PARAM_INT
        ));
        $s->add(new admin_setting_configtext(
            'local_fitnhom/batthuong_khai',
            new lang_string('caidat_batthuong_khai', 'local_fitnhom'),
            new lang_string('caidat_batthuong_khai_mota', 'local_fitnhom'),
            5,
            PARAM_INT
        ));
        $s->add(new admin_setting_configselect(
            'local_fitnhom/truongma',
            new lang_string('truongma', 'local_fitnhom'),
            new lang_string('truongma_help', 'local_fitnhom'),
            'idnumber',
            [
                'idnumber' => new lang_string('idnumber'),
                'username' => new lang_string('username'),
            ]
        ));
    }
    $ADMIN->add('local_fitnhom_muc', $s);
    $ADMIN->add('local_fitnhom_muc', new admin_externalpage(
        'local_fitnhom_tinhhinh',
        new lang_string('tinhhinh', 'local_fitnhom'),
        new moodle_url('/local/fitnhom/tinhhinh.php')
    ));
}
