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
 * plugin-wide constants
 *
 * @package assignsubmission_noto
 * @copyright 2024 Enovation {@link https://enovation.ie}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_noto;

defined('MOODLE_INTERNAL') || die();

class constants {
    const FILEAREA = 'noto_zips';
    const AUTOGRADEDIR = 'autograder';
    const AUTOGRADEZIP = 'notebook_autograde_assignment.zip';
    const SEEDZIP = 'notebook_seed_assignment.zip';
    const RESULTZIP = 'notebook_autograde_result.zip';
    const RESULTSJSON = 'results.json';
    const RESULTSPDF = 'results.pdf';

    const OK = 'ok';
    const NOTGRADED = 'NOTGRADED';  # not used
    const PENDING = 'PENDING';  # submission successfully sent to autogtading
    const GRADED = 'GRADED';
    const GRADINGTIMEOUT = 'GRADINGTIMEOUT';
    const GRADINGFAILED = 'GRADINGFAILED';  # received failure from the autograding endpoint
    public static $rc = array(
        '1' => "MULTIPLE_NOTEBOOKS",
        '2' => 'NO_NOTEBOOK',
        '3' => 'NO_RESULTS',
        '4' => 'NO_JSON',
        '5' => 'NO_PDF',
        '6' => 'PERMISSION_DENIED',
        '97' => 'NO_GRADING_CONFIGURED',
        '98' => 'NO_MARKING_ENABLED',
        '99' => 'OTHER_ERROR',
    );
}
