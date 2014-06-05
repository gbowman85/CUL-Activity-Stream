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
 * Library function for message_ouput_culactivity_stream.
 *
 * @package    message_ouput
 * @subpackage culactivity_stream
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * message_culactivity_stream_upgrade_cron()
 * Called periodically by the Moodle cron job.
 * @return void
 */
function message_culactivity_stream_cron() {

    if (get_config('message_culactivity_stream', 'eventdata__update')) {
        if ((date('H') == '02') && (date('i') < 30)) {
            update_culactivity_stream_table();
        }
    }
}

function update_culactivity_stream_table() {
    global $CFG, $DB, $OUTPUT;

    $starttime = time();
    $dbman = $DB->get_manager();

    set_time_limit(60 * 10);

    $table = new xmldb_table('message_culactivity_stream');
    $field = new xmldb_field('eventdata');

    if ($dbman->field_exists($table, $field)) {

        $sqlselectcount = "SELECT COUNT(*) FROM {message_culactivity_stream} WHERE eventdata IS NOT NULL AND smallmessage IS NULL";
        $totalrecords = $DB->count_records_sql($sqlselectcount);

        if ($totalrecords == 0) {
            // If update complete send mail.
            $recip = get_admins();
            mtrace('Emailing admins about completed update');
            foreach ($recip as $admin) {
                // Emailing the admins directly rather than putting these through the messaging
                // system.
                email_to_user(
                    $admin,
                    generate_email_supportuser(),
                    'message_culactivity_stream_cron',
                    'Complete!'
                );

            }
            return true;
        }

        $limitnum = 1000;
        $limitnum = $limitnum < $totalrecords ? $limitnum : $totalrecords;

        mtrace("    Total records to process: {$totalrecords} Processing...");
        $updatecounts = array('succeeded' => 0, 'failed' => 0);

        foreach (range(0, $totalrecords, $limitnum) as $limitfrom) {
            set_time_limit(60 * 15);

            // Just because I'm very paranoid indeed...
            if ( !(preg_match('/\A\d+\z/', $limitfrom) && preg_match('/\A\d+\z/', $limitnum)) ) {
                mtrace("    Invalid limit value!");
                return true;
            }

            // The query looks strange, but improves LIMIT performance considerably at high offsets...
            $sqlselectnotifications = <<<EOQ
SELECT mcs.id, mcs.eventdata
FROM (SELECT id FROM {message_culactivity_stream}
    WHERE eventdata IS NOT NULL
    AND smallmessage IS NULL
    ORDER BY id LIMIT {$limitfrom}, {$limitnum}) q
    JOIN {message_culactivity_stream} mcs
        ON mcs.id = q.id;
EOQ;

            $rs = $DB->get_recordset_sql($sqlselectnotifications);

            if ($rs->valid()) {
                $limitto = $limitfrom + $limitnum;
                mtrace("    {$limitfrom}  -  {$limitto}");

                $i = 0;
                foreach ($rs as $notification) {
                    $i++;
                    if (($i % 1000) == 0) {
                        mtrace("     ---    ");
                    }

                    $eventdata = null;
                    if ($notification->eventdata != '') {
                        $eventdata = json_decode($notification->eventdata);
                        if (isset($eventdata->contexturl)) {
                            $notification->contexturl = $eventdata->contexturl;
                            unset($eventdata->contexturl);
                        }
                        if (isset($eventdata->component)) {
                            $notification->component = $eventdata->component;
                            unset($eventdata->component);
                        }
                        $notification->userfromid = $eventdata->userfrom->id;
                        if (isset($eventdata->smallmessage)) {
                            $notification->smallmessage = $eventdata->smallmessage;
                            unset($eventdata->smallmessage);
                        }
                        $notification->eventdata = null;
                        unset($eventdata);

                        if ($DB->update_record('message_culactivity_stream', $notification, true)) {
                            $updatecounts['succeeded']++;
                        } else {
                            $updatecounts['failed']++;
                            mtrace("    Failed to update record id: {$notification->id}");
                        }
                    } else {
                            $updatecounts['failed']++;
                            mtrace("    Failed to update record id: {$notification->id}");
                    }
                    unset($notification);
                }
            } else {
                mtrace("    Uh-oh - The recordset was empty!");
            }
            $rs->close();
            unset($rs);
        }

        $sqlwhere = 'WHERE (userfromid = 0 OR COALESCE(LENGTH(component), 0) = 0 OR COALESCE(LENGTH(smallmessage), 0) = 0)';
        $sqlselectcount = "SELECT COUNT(*) FROM {message_culactivity_stream} {$sqlwhere}";
        $numrecordsempty = $DB->count_records_sql($sqlselectcount);

        if ($numrecordsempty) {
            mtrace("    WARNING: {$numrecordsempty} records with empty 'userfromid', 'component' or 'smallmessage'!");
        }

        $succeededclass = empty($updatecounts['succeeded']) ? 'notifyproblem' : 'notifysuccess';
        $failedclass    = empty($updatecounts['failed']) ? 'notifysuccess' : 'notifyproblem';
        mtrace("    Update succeeded: {$updatecounts['succeeded']}");
    }

    $secstaken = time() - $starttime;
    mtrace("    Time elapsed: {$secstaken} seconds.");

    flush();
    return true;
}