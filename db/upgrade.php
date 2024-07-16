<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade code for install
 *
 * @package   assignsubmission_noto
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignsubmission_noto_upgrade($oldversion) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.9.0 release upgrade line.
    // Put any upgrade step following this.
    if ($oldversion < 2024050200) {

        // Define table assignsubmission_noto_assign to be created.
        $table = new xmldb_table('assignsubmission_noto_assign');

        // Adding fields to table assignsubmission_noto_assign.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('autograde_suspended', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('autograde_disabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('ext_int1', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('ext_int2', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('ext_char1', XMLDB_TYPE_CHAR, '256', null, null, null, null);

        // Adding keys to table assignsubmission_noto_assign.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('noto_assignment_assignment_uix', XMLDB_KEY_UNIQUE, ['assignment']);

        // Conditionally launch create table for assignsubmission_noto_assign.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Noto savepoint reached.
        upgrade_plugin_savepoint(true, 2024050200, 'assignsubmission', 'noto');
    }
    if ($oldversion < 2024050202) {

        // Define table assignsubmission_noto_autgrd to be created.
        $table = new xmldb_table('assignsubmission_noto_autgrd');

        // Adding fields to table assignsubmission_noto_autgrd.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('submission', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timesent', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('attempt', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '14', null, null, null, null);
        $table->add_field('extint', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('exttext', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table assignsubmission_noto_autgrd.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table assignsubmission_noto_autgrd.
        $table->add_index('noto_autograde_submission_uix', XMLDB_INDEX_UNIQUE, ['submission']);

        // Conditionally launch create table for assignsubmission_noto_autgrd.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Noto savepoint reached.
        upgrade_plugin_savepoint(true, 2024050202, 'assignsubmission', 'noto');
    }
    return true;
}
