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
 * Chuỗi ngôn ngữ (en).
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cacban'] = 'Group members';
$string['cacban_chua'] = 'No members selected';
$string['cacban_go'] = 'Type a student code or name…';
$string['caidat'] = 'Settings';
$string['cauhoi'] = 'How did you do this work?';
$string['cauhoi_vi'] = 'So that your group members are not counted as not submitted.';
$string['chedo'] = 'Mode';
$string['chedo_BAT'] = 'On — whole site';
$string['chedo_TAT'] = 'Off';
$string['chedo_THU'] = 'Pilot — selected courses only';
$string['chedo_help'] = 'Off: never ask. Pilot: ask only in the pilot courses. On: ask in every assignment of every course except excluded courses. The first switch away from Off is recorded as the start time — submissions before it are never asked.';
$string['chon_mot'] = 'I worked alone';
$string['chon_nhom'] = 'Group work — choose the members';
$string['chuakhai'] = 'Not declared';
$string['cotrongnhom'] = 'Included in the group submission of: {$a}';
$string['cungnhomvoi'] = 'Same group as the submission of: {$a}';
$string['dahoantac'] = 'Undone — you are counted as a member again.';
$string['dai_chu'] = 'You have {$a} submission(s) needing a group declaration.';
$string['dai_nut'] = 'Declare now';
$string['dakhai'] = 'Declaration saved.';
$string['dakhongphai'] = 'This student answered "Not me"';
$string['danop'] = 'submitted {$a}';
$string['datuchoi'] = 'Recorded: you did not work on this. The submitter has been notified.';
$string['desau'] = 'Later';
$string['ev_khai_luu'] = 'Group declaration saved';
$string['ev_quet_ngay'] = 'Scan for undeclared submissions';
$string['ev_thanhvien_tuchoi'] = 'Named member answered "Not me"';
$string['goiy'] = '{$a->ban} submitted this work at {$a->luc} and named you as a member — you do not need to submit again. If you submit an update, choose the members below (prefilled from {$a->ban}).';
$string['hoantac'] = 'Undo';
$string['hopnoi_giaithich'] = 'You submitted the work below without saying whether it was individual or group work (usually through the mobile app). Declare it so your group members are not counted as not submitted.';
$string['hopnoi_tieude'] = '{$a} submission(s) need a group declaration';
$string['khoathu'] = 'Pilot courses';
$string['khoathu_help'] = 'Short names of the pilot courses, one per line. Used in Pilot mode only.';
$string['khongconbai'] = 'You have no submissions awaiting a declaration.';
$string['khongphaiem'] = 'Not me';
$string['loaitru_danhmuc'] = 'Excluded courses — categories';
$string['loaitru_danhmuc_help'] = 'Courses in these categories (including subcategories) are never asked.';
$string['loaitru_mau'] = 'Excluded courses — short name patterns';
$string['loaitru_mau_help'] = 'One SQL LIKE-style pattern per line (% any text, _ one character), case-insensitive. For homeroom, general activity and administrative courses. Default FITCNL:% (FIT-CNL homeroom courses).';
$string['loi_chuachon'] = 'Choose "I worked alone" or "Group work".';
$string['loi_chuachonban'] = 'Group work: choose at least one member.';
$string['loi_hethoantac'] = 'More than 7 days have passed; it cannot be undone.';
$string['loi_khongphaicuaem'] = 'This is not your submission / declaration.';
$string['loi_khongtronglop'] = 'Some members are not participants of this assignment.';
$string['loi_quanhieu'] = 'Choose at most {$a} members.';
$string['loi_thamso'] = 'Invalid declaration.';
$string['luu'] = 'Save';
$string['messageprovider:duockhai'] = 'You were named as a group member of a submission';
$string['messageprovider:tuchoi'] = 'A named member answered "Not me"';
$string['motminh'] = 'Worked alone';
$string['motminh_nut'] = 'I worked alone';
$string['mucdich'] = 'Many assignments are group work but only one student submits, so Moodle (and systems reading Moodle data such as FIT-CNL) treat the other members as not having submitted. Fit-Nhom adds a required question to the submission page: <em>I worked alone</em> or <em>Group work with…</em> (chosen from a list). Named members are notified and can answer <em>Not me</em>. Teachers do nothing extra; downloading submissions and the gradebook are unchanged; the grading table gets a <em>Group members</em> column. Design: TL54, repository github.com/lnthanhhcmus/fitnhom.';
$string['mucdich_tieude'] = 'What Fit-Nhom is for';
$string['nhom_nut'] = 'Group work…';
$string['nhomvoi'] = 'Group work:';
$string['pluginname'] = 'Fit-Nhom (group members)';
$string['privacy:metadata:assignid'] = 'Assignment';
$string['privacy:metadata:cho'] = 'Submissions awaiting declaration in the pop-up';
$string['privacy:metadata:cho:userid'] = 'Submitter';
$string['privacy:metadata:khai'] = 'Declaration of a submission: individual or group work';
$string['privacy:metadata:khai:loai'] = 'Alone / group';
$string['privacy:metadata:khai:nguon'] = 'Declared on the submission page or the pop-up';
$string['privacy:metadata:khai:userid'] = 'Submitter';
$string['privacy:metadata:khai:usermodified'] = 'User who saved it';
$string['privacy:metadata:message'] = 'Notifications about being named and "Not me" are sent through Moodle messaging';
$string['privacy:metadata:pref_desau'] = 'Day "Later" was chosen in the pop-up';
$string['privacy:metadata:submissionid'] = 'Submission';
$string['privacy:metadata:timemodified'] = 'Time';
$string['privacy:metadata:tv'] = 'Named group members';
$string['privacy:metadata:tv:trangthai'] = 'Named / answered Not me';
$string['privacy:metadata:tv:tuchoiluc'] = 'Time of Not me';
$string['privacy:metadata:tv:userid'] = 'Named member';
$string['quet_xong'] = 'Scan finished: {$a} submissions added to the pop-up list.';
$string['quetngay'] = 'Scan now';
$string['quetngay_help'] = 'Find submissions sent since the start time in applicable courses without a declaration (mobile app, web services, or while switched off) and add them to the submitter\'s pop-up. No e-mail is sent.';
$string['tb_duockhai'] = '{$a->ban} submitted "{$a->bai}" ({$a->mon}) and named you as a group member. You do not need to submit again. If this is wrong, open {$a->url} and choose "Not me".';
$string['tb_duockhai_html'] = '<p><strong>{$a->ban}</strong> submitted <a href="{$a->urlbai}">{$a->bai}</a> ({$a->mon}) and named you as a group member. You do not need to submit again.</p><p>If this is wrong: <a href="{$a->url}">Not me</a>.</p>';
$string['tb_duockhai_ngan'] = '{$a->ban} named you in "{$a->bai}".';
$string['tb_duockhai_tieude'] = '{$a->ban} named you as a member of "{$a->bai}"';
$string['tb_tuchoi'] = '{$a->ban} answered "Not me" for "{$a->bai}" that you submitted, so they are not counted as a member. If this is a mistake, edit your declaration on the submission page: {$a->url}';
$string['tb_tuchoi_tieude'] = '{$a->ban} says they did not work on "{$a->bai}"';
$string['th_bainop'] = 'Submissions since start (applicable courses)';
$string['th_ban'] = 'Members named';
$string['th_batdau'] = 'Started';
$string['th_cho'] = 'Submissions awaiting declaration (pop-up)';
$string['th_chuabat'] = 'Never switched on';
$string['th_chuachon'] = 'no course selected';
$string['th_chuasubplugin'] = 'Submission plugin assignsubmission_fitnhom is not installed or is hidden — the submission page will not ask.';
$string['th_khongco'] = 'no such course';
$string['th_loaitru'] = 'currently excluded';
$string['th_mot'] = '— declared alone';
$string['th_nhom'] = '— declared group work';
$string['th_quetluc'] = 'Last scan';
$string['th_tuchoi'] = '"Not me" answers';
$string['tinhhinh'] = 'Status and scan now';
$string['toida'] = 'Maximum members';
$string['toida_help'] = 'Maximum number of group members in one declaration.';
$string['trangkhai'] = 'Group declaration';
$string['truongma'] = 'Student code shown from';
$string['truongma_help'] = 'Profile field shown next to the name in the picker (username when idnumber is empty).';
$string['tuchoi_da'] = 'You said you did not work on "{$a->bai}" ({$a->mon}) submitted by {$a->ban}.';
$string['tuchoi_hoi'] = '{$a->ban} submitted "{$a->bai}" ({$a->mon}) and named you as a member. If you did not work on it, choose "Not me" — you will not be counted as submitted through this work, and {$a->ban} will be notified.';
