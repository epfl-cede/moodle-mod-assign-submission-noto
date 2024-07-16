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
 * An event observer.
 *
 * @package    assignfeedback_editpdf
 * @copyright  2016 Damyon Wiese
 * @copyright  2024 Enovation Solution
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace assignsubmission_noto;

class observer {
    /**
     * Process the assessable_submitted event.
     * @param \mod_assign\event\assessable_submitted $event The submission created/updated event.
     */
    public static function assessable_submitted($event) {
        $submissionid = $event->get_data()['objectid'];
        $cm = $event->get_assign()->get_course_module();
        $autogradeapi = new \assignsubmission_noto\autogradeapi($cm);
        if (!($autogradeapi->is_suspended() || $autogradeapi->is_disabled())) {
            \assign_submission_noto::send_to_autograde_submission($cm, $submissionid);
        }
    }
}
