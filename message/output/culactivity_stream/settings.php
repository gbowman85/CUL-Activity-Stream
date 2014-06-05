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
 * @package message
 * @subpackage culactivity_stream
 * @copyright 2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>, Tim Gagen <tim.gagen.1@city.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    /*----------------------------
     * Monster Table Update settings
     *---------------------------*/
    $settings->add(new admin_setting_heading('message_culactivity_stream/eventdata_heading',
                                             get_string('eventdata', 'message_culactivity_stream'),
                                             ''));

    // Add a checkbox to enable/disable monster table update (cron).
    $settings->add(new admin_setting_configcheckbox('message_culactivity_stream/eventdata__update',
                                                    get_string('update', 'message_culactivity_stream'),
                                                    get_string('eventdata_update_desc', 'message_culactivity_stream'),
                                                    0));

}
