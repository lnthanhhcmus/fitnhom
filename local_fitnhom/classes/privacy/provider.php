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

namespace local_fitnhom\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Riêng tư: câu khai (người nộp), các bạn được khai, danh sách chờ — theo ngữ cảnh bài tập.
 *
 * @package    local_fitnhom
 * @copyright  2026 Khoa Công nghệ Thông tin, Trường Đại học Khoa học Tự nhiên, ĐHQG-HCM
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\user_preference_provider {
    /**
     * Dữ liệu lưu.
     *
     * @param collection $c Bộ sưu tập.
     * @return collection
     */
    public static function get_metadata(collection $c): collection {
        $c->add_database_table(
            'local_fitnhom_khai',
            ['assignid' => 'privacy:metadata:assignid',
            'submissionid' => 'privacy:metadata:submissionid', 'userid' => 'privacy:metadata:khai:userid',
            'loai' => 'privacy:metadata:khai:loai', 'nguon' => 'privacy:metadata:khai:nguon',
            'usermodified' => 'privacy:metadata:khai:usermodified', 'timemodified' => 'privacy:metadata:timemodified'],
            'privacy:metadata:khai'
        );
        $c->add_database_table(
            'local_fitnhom_tv',
            ['userid' => 'privacy:metadata:tv:userid',
            'trangthai' => 'privacy:metadata:tv:trangthai', 'tuchoiluc' => 'privacy:metadata:tv:tuchoiluc'],
            'privacy:metadata:tv'
        );
        $c->add_database_table(
            'local_fitnhom_cho',
            ['userid' => 'privacy:metadata:cho:userid',
            'submissionid' => 'privacy:metadata:submissionid', 'timecreated' => 'privacy:metadata:timemodified'],
            'privacy:metadata:cho'
        );
        $c->add_user_preference(\local_fitnhom\hook_callbacks::DESAU, 'privacy:metadata:pref_desau');
        $c->add_subsystem_link('core_message', [], 'privacy:metadata:message');
        return $c;
    }

    /**
     * Phần nối từ bảng (bí danh x, cột assignid) sang ngữ cảnh module của bài tập.
     *
     * @return string
     */
    private static function noi_ctx(): string {
        return "JOIN {course_modules} cm ON cm.instance = x.assignid
                JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
                JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :lv";
    }

    /**
     * Ngữ cảnh có dữ liệu của một người.
     *
     * @param int $userid Người.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $l = new contextlist();
        foreach (['local_fitnhom_khai', 'local_fitnhom_tv', 'local_fitnhom_cho'] as $b) {
            $l->add_from_sql(
                "SELECT ctx.id FROM {" . $b . "} x " . self::noi_ctx() . " WHERE x.userid = :u",
                ['lv' => CONTEXT_MODULE, 'u' => $userid]
            );
        }
        return $l;
    }

    /**
     * Người có dữ liệu trong một ngữ cảnh.
     *
     * @param userlist $l Danh sách.
     * @return void
     */
    public static function get_users_in_context(userlist $l) {
        $ctx = $l->get_context();
        if (!$ctx instanceof \context_module) {
            return;
        }
        foreach (['local_fitnhom_khai', 'local_fitnhom_tv', 'local_fitnhom_cho'] as $b) {
            $l->add_from_sql(
                'userid',
                "SELECT x.userid FROM {" . $b . "} x " . self::noi_ctx() . " WHERE ctx.id = :c",
                ['lv' => CONTEXT_MODULE, 'c' => $ctx->id]
            );
        }
    }

    /**
     * assign.id của một ngữ cảnh module bài tập (0 nếu không phải).
     *
     * @param \context $ctx Ngữ cảnh.
     * @return int
     */
    private static function assign_cua(\context $ctx): int {
        if (!$ctx instanceof \context_module) {
            return 0;
        }
        $cm = get_coursemodule_from_id('assign', $ctx->instanceid);
        return $cm ? (int) $cm->instance : 0;
    }

    /**
     * Xuất dữ liệu.
     *
     * @param approved_contextlist $l Ngữ cảnh.
     * @return void
     */
    public static function export_user_data(approved_contextlist $l) {
        global $DB;
        $u = (int) $l->get_user()->id;
        foreach ($l->get_contexts() as $ctx) {
            $aid = self::assign_cua($ctx);
            if (!$aid) {
                continue;
            }
            $d = ['khai' => [], 'duockhai' => []];
            foreach ($DB->get_records('local_fitnhom_khai', ['assignid' => $aid, 'userid' => $u]) as $k) {
                $ban = [];
                foreach ($DB->get_records('local_fitnhom_tv', ['khaiid' => $k->id]) as $t) {
                    $x = \core_user::get_user($t->userid);
                    $ban[] = ($x ? fullname($x) : $t->userid) . ' (' . $t->trangthai . ')';
                }
                $d['khai'][] = ['loai' => $k->loai, 'nguon' => $k->nguon, 'ban' => $ban,
                    'timemodified' => \core_privacy\local\request\transform::datetime($k->timemodified)];
            }
            foreach ($DB->get_records('local_fitnhom_tv', ['assignid' => $aid, 'userid' => $u]) as $t) {
                $nop = (int) $DB->get_field('local_fitnhom_khai', 'userid', ['id' => $t->khaiid]);
                $x = \core_user::get_user($nop);
                $d['duockhai'][] = ['nguoinop' => $x ? fullname($x) : $nop, 'trangthai' => $t->trangthai,
                    'tuchoiluc' => $t->tuchoiluc ? \core_privacy\local\request\transform::datetime($t->tuchoiluc) : ''];
            }
            if ($d['khai'] || $d['duockhai']) {
                writer::with_context($ctx)->export_data([get_string('pluginname', 'local_fitnhom')], (object) $d);
            }
        }
    }

    /**
     * Xuất tùy chọn người dùng.
     *
     * @param int $userid Người.
     * @return void
     */
    public static function export_user_preferences(int $userid) {
        $v = get_user_preferences(\local_fitnhom\hook_callbacks::DESAU, null, $userid);
        if ($v !== null) {
            writer::export_user_preference(
                'local_fitnhom',
                \local_fitnhom\hook_callbacks::DESAU,
                $v,
                get_string('privacy:metadata:pref_desau', 'local_fitnhom')
            );
        }
    }

    /**
     * Xóa mọi dữ liệu trong một ngữ cảnh.
     *
     * @param \context $ctx Ngữ cảnh.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $ctx) {
        $aid = self::assign_cua($ctx);
        if ($aid) {
            \local_fitnhom\khai::xoa_bai_tap($aid);
        }
    }

    /**
     * Xóa dữ liệu của một người ở một bài tập.
     *
     * @param int $aid Bài tập.
     * @param int $u Người.
     * @return void
     */
    private static function xoa(int $aid, int $u): void {
        global $DB;
        foreach ($DB->get_records('local_fitnhom_khai', ['assignid' => $aid, 'userid' => $u]) as $k) {
            $DB->delete_records('local_fitnhom_tv', ['khaiid' => $k->id]);
            $DB->delete_records('local_fitnhom_khai', ['id' => $k->id]);
        }
        $DB->delete_records('local_fitnhom_tv', ['assignid' => $aid, 'userid' => $u]);
        $DB->delete_records('local_fitnhom_cho', ['assignid' => $aid, 'userid' => $u]);
    }

    /**
     * Xóa dữ liệu của một người.
     *
     * @param approved_contextlist $l Ngữ cảnh.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $l) {
        foreach ($l->get_contexts() as $ctx) {
            if ($aid = self::assign_cua($ctx)) {
                self::xoa($aid, (int) $l->get_user()->id);
            }
        }
    }

    /**
     * Xóa dữ liệu của nhiều người trong một ngữ cảnh.
     *
     * @param approved_userlist $l Danh sách.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $l) {
        if ($aid = self::assign_cua($l->get_context())) {
            foreach ($l->get_userids() as $u) {
                self::xoa($aid, (int) $u);
            }
        }
    }
}
