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
 * Retry sending for autograding
 *
 * @package    assignsubmission_noto
 * @copyright  2024 Enovation {@link https://enovation.ie}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_noto\task;

defined('MOODLE_INTERNAL') || die();
include_once($CFG->dirroot . '/mod/assign/submission/noto/locallib.php');

class retry_autograde extends \core\task\scheduled_task {
    /** 
     * Get the name of the job
     */
    public function get_name() {
        return get_string('retry_autograde', 'assignsubmission_noto');
    }

    /**
     * Execute the job
     */
    public function execute() {
        global $DB;
        $config = get_config('assignsubmission_noto');
        if (!(isset($config->automaticgrading_gradingattempt_retries) && $config->automaticgrading_gradingattempt_retries)) {
            throw new \moodle_exception("assignsubmission_noto.automaticgrading_gradingattempt_retries is not configured");
        }
        $sql = sprintf("SELECT * FROM {assignsubmission_noto_autgrd} WHERE attempt <= ? AND timesent < ? AND status IN ('%s', '%s')",
            \assignsubmission_noto\constants::GRADINGTIMEOUT, \assignsubmission_noto\constants::PENDING);
        $entries = $DB->get_records_sql($sql, array(intval($config->automaticgrading_gradingattempt_retries), time() - intval($config->automaticgrading_job_timeout)));
        if ($entries) {
            foreach ($entries as $entry) {
                $submission = $DB->get_record('assign_submission', array('id'=>$entry->submission));
                if ($submission) {
                    $cm = get_coursemodule_from_instance('assign', $submission->assignment);
                    $modinfo = get_fast_modinfo($cm->course);
                    $cm = $modinfo->get_cm($cm->id);
                    if ($cm) {
                        mtrace(sprintf("sending for autograding submission id %d current status %s existing attempt %d", $submission->id, $entry->status, $entry->attempt));
                        $entry = \assign_submission_noto::send_to_autograde_submission($cm, $submission->id);
                        $entry->status = \assignsubmission_noto\constants::GRADINGTIMEOUT;  // this is the proper grading timeout. will be reset only by incoming grading call
                        $DB->update_record('assignsubmission_noto_autgrd', $entry);
                    }
                }
            }
        }
    }
}
