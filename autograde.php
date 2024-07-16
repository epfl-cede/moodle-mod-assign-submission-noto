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
 * Disable, suspend or unsuspend autograding in an assignment
 *
 * @package   assignsubmission_noto
 * @copyright 2024 Enovation Solutions
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__). '/../../../../config.php');

$cmid = required_param('cmid', PARAM_INT);
$action = required_param('action', PARAM_TEXT);
if (!($action == 'disable' || $action == 'suspend' || $action == 'unsuspend' || $action == 'disable_confirmed')) {
    throw new moodle_exception('wrong action: ' . $action);
}
require_login();

list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'assign');
if (!has_capability('mod/assign:addinstance', $cm->context)) {
    \core\notification::error(get_string('automaticgrading_noatutorized', 'assignsubmission_noto'));
    redirect(new moodle_url('/mod/assign/view.php', array('id'=>$cmid)));
}

$autogradeapi = new \assignsubmission_noto\autogradeapi($cm);
if ($action == 'suspend') {
    $autogradeapi->suspend();
}
if ($action == 'unsuspend') {
    $autogradeapi->unsuspend();
}
if ($action == 'disable') {
    $message = get_string('disableautogradingconfirmation', 'assignsubmission_noto');

    $continueurl = new moodle_url('/mod/assign/submission/noto/autograde.php', array('cmid'=>$cmid, 'action' => 'disable_confirmed'));
    $continuebutton = new single_button($continueurl, get_string('disable', 'assignsubmission_noto'), '');
    $cancelurl = new moodle_url('/mod/assign/view.php', array('id'=>$cmid));
    $cancelbutton = new single_button($cancelurl, get_string('cancel'), '');
    $PAGE->set_pagelayout('popup');
    $PAGE->set_context($cm->context);
    $PAGE->set_cm($cm);
    $PAGE->set_url(new moodle_url('/mod/assign/submission/noto/autograde.php', array('cmid'=>$cmid, 'action'=>'disable')));
    echo $OUTPUT->header();
    echo $OUTPUT->confirm($message, $continuebutton, $cancelurl);
    echo $OUTPUT->footer();
    exit();
}
if ($action == 'disable_confirmed') {
    $autogradeapi->disable();
}
redirect(new moodle_url('/mod/assign/view.php', array('id'=>$cmid)));
