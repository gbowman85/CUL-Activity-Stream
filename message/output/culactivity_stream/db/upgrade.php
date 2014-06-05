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
 * Upgrade code for the CUL Activity Stream
 *
 * @package    message
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * Upgrade code for the local_culactivity_stream plugin
 *
 * @param int $oldversion The version that we are upgrading from
 */
function xmldb_message_culactivity_stream_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    if ($oldversion < 2014022803) { // TODO default values + add modulename and contexturlname.

        $starttime = time();
        $dbman = $DB->get_manager();

        // Create new table to queue course update messages.
        $table = new xmldb_table('message_culactivity_stream_q');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        $table->add_field('sent', XMLDB_TYPE_INTEGER, '2',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, null);

        $table->add_field('userfromid', XMLDB_TYPE_INTEGER, '18',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, null);

        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '18',
            XMLDB_UNSIGNED, null, null, 0, null);

        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '18',
            XMLDB_UNSIGNED, null, null, 0, null);

        $table->add_field('smallmessage', XMLDB_TYPE_TEXT, 'big',
            null, null, null, null);

        $table->add_field('component', XMLDB_TYPE_CHAR, '',
            null, XMLDB_NOTNULL, null, null);

        $table->add_field('modulename', XMLDB_TYPE_CHAR, '',
            null, XMLDB_NOTNULL, null, null);

        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '18',
            XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, null);

        $table->add_field('contexturl', XMLDB_TYPE_CHAR, '',
            null, XMLDB_NOTNULL, null, null);

        $table->add_field('contexturlname', XMLDB_TYPE_CHAR, '',
            null, XMLDB_NOTNULL, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }
    return true;
}
