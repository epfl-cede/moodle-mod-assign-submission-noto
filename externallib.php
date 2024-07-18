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
 * External assignsubmission_noto API
 *
 * @package    assignsubmission_noto
 * @since      Moodle 3.9
 * @copyright  2021 Enovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("locallib.php");
require_once($CFG->dirroot.'/mod/assign/locallib.php');


/**
 * Assign submission noto functions
 * @copyright 2021 Enovation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignsubmission_noto_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since  Moodle 3.9
     */
    public static function get_jstree_json_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Assignment course id')
            )
        );
    }

    /**
     * @param int $courseid a required course id parameter
     * @return string with jstree HTML
     * @since  Moodle 3.9
     */
    public static function get_jstree_json($courseid) {

        $params = self::validate_parameters(
            self::get_jstree_json_parameters(),
            array(
                'courseid' => $courseid
            )
        );
        $treejson = 'null';
        $warnings = array();
        $config = get_config('assignsubmission_noto');
        $maxdepth = assignsubmission_noto\notoapi::MAXDEPTH;
        if (isset($config->maxdepth) && $config->maxdepth) {
            $maxdepth = $config->maxdepth;
        }
        $notoapi = new assignsubmission_noto\notoapi($courseid);
        $dirlist_top = new stdClass();
        $dirlist_top->name = assignsubmission_noto\notoapi::STARTPOINT;
        $dirlist_top->type = 'directory';
        try {
            $dirlist_top->children = $notoapi->lof(assignsubmission_noto\notoapi::STARTPOINT);
            $treestructure = assignsubmission_noto\nototreerenderer::display_lof_recursive($dirlist_top, assignsubmission_noto\notoapi::STARTPOINT, 0, $maxdepth);
            $treejson = json_encode($treestructure, JSON_UNESCAPED_SLASHES);
        } catch (\moodle_exception $e) {
            if ($e->errorcode == assign_submission_noto::E404) {
                $warnings[] = ['warningcode' => 404,
                    'message' => get_string('notoaccount_notfound', 'assignsubmission_noto')];
            } else {
                $warnings[] = ['warningcode' => 400,
                    'message' =>$e->getMessage()];
            }
        }
        $result = array(
            'result' => $treejson,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the return value for assignsubmission_noto
     *
     * @return external_single_structure
     * @since Moodle 3.9
     */
    public static function get_jstree_json_returns() {
        return new external_single_structure(
            array(
                'result' => new external_value(PARAM_RAW, 'jstree data json'),
                'warnings'  => new external_warnings('item can be \'course\' (errorcode 1 or 2) or \'module\' (errorcode 1)',
                    'When item is a course then itemid is a course id. When the item is a module then itemid is a module id',
                    'errorcode can be 1 (no access rights) or 2 (not enrolled or no permissions)')
            )
        );
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since  Moodle 4.3
     */
    public static function autograde_parameters() {
        return new external_function_parameters(
            array(
                'input' => new external_value(PARAM_TEXT, 'Input JSON')
            )
        );
    }

    /**
     * Describes the return value for assignsubmission_noto
     *
     * @return external_single_structure
     * @since Moodle 4.3
     */
    public static function autograde_returns() {
        return new external_single_structure(
            array(
                'status'  => new external_value(PARAM_TEXT, 'Always OK')
            )
        );
    }

    /**
     * @param text $input required JSON-encoded input
     * @return array
     * @since  Moodle 4.3
     */
    public static function autograde ($input) {
        global $DB;

        $headers = array_change_key_case(getallheaders());
        if (!isset($headers['access-token'])) {
            throw new \moodle_exception('No "Access-token" header (case insensitive)');
        }
        $config = get_config('assignsubmission_noto', 'automaticgrading_token_receive');
        if (!$config) {
            throw new \moodle_exception('assignsubmission_noto plugin misconfigured: missing "automaticgrading_token_receive"');
        }
        if ($headers['access-token'] != $config) {
            throw new \moodle_exception('Wrong access token');
        }

        if (empty($input)) {
            throw new \moodle_exception('Input empty');
        }
        $params = self::validate_parameters(
            self::autograde_parameters(),
            array(
                'input' => $input,
            )
        );
        if (empty($params['input'])) {
            throw new \moodle_exception('Input empty after validation');
        }
        if (!is_string($params['input']) && !is_integer($params['input'])) {
            throw new \moodle_exception("Unexpected JSON data type: " . gettype($params['input']));
        }
        $json = json_decode(urldecode($params['input']));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \moodle_exception("JSON decoding error: " . json_last_error_msg() . ' (code: ' . json_last_error() . ')');
        }

        if (empty($json->submission_id)) {
            throw new invalid_parameter_exception('Missing required key in single structure: submission_id');
        }

        // course_id not important

        $submission = $DB->get_record('assign_submission', array('id'=>$json->submission_id));
        if (!$submission) {
            throw new \moodle_exception('Wrong submission id');
        }
        list($course, $cm) = get_course_and_cm_from_instance($submission->assignment, 'assign');
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);

        if (isset($json->rc) && $json->rc) {   // non-zero "rc"
            $rc = $json->rc;
            if (isset(\assignsubmission_noto\constants::$rc[$json->rc])) {
                $rc = \assignsubmission_noto\constants::$rc[$json->rc];
            }
            \assignsubmission_noto\autogradeapi::upgrade_autograding_record($submission->id, array(
                'status' => \assignsubmission_noto\constants::GRADINGFAILED,
                'timesent' => time(),
                'exttext' => $rc,
            ));
            return array('status'=>'OK');
        }

        // unzip the solution
        $requestdir = make_request_directory();
        #$requestdir = make_temp_directory('autograde_' . time());    // dev use: this leaves the directory available for inspection
        $requestfilepath = $requestdir.'/'.\assignsubmission_noto\constants::RESULTZIP;
        file_put_contents($requestfilepath, base64_decode($json->results));
        $za = new ZipArchive();
        $zo = $za->open($requestfilepath);
        if ($zo === true) {
            $afiles = array();
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                if (strpos($stat['name'], \assignsubmission_noto\constants::RESULTSJSON ) !== false) {
                    $afiles['json'] = $stat['name'];
                }
                if (strpos($stat['name'], \assignsubmission_noto\constants::RESULTSPDF ) !== false) {
                    $afiles['pdf'] = $stat['name'];
                }
            }
            if ($za->extractTo($requestdir, array_values($afiles))) {   // filelist cannot have non-numerkcal keys, while we need them
                $json_file = $requestdir.'/'.$afiles['json'];
                $pdf_file = $requestdir.'/'.$afiles['pdf'];
                if (!file_exists($json_file)) {
                    throw new \moodle_exception('No '.\assignsubmission_noto\constants::RESULTSJSON.' file in results');
                }

                if (is_file($json_file) && is_readable($json_file)) {
                    $json_content = file_get_contents($json_file);
                    $json_decoded = json_decode($json_content);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \moodle_exception("Cannot decode JSON " . json_last_error_msg() . ' (code: ' . json_last_error() . ')');
                    }
                    if (empty($json_decoded)) {
                        throw new \moodle_exception("Empty JSON after decoding");
                    }
                    if (empty($json_decoded->tests)) {
                        throw new \moodle_exception("No 'tests' in JSON");
                    }
                    $score = 0;
                    $max_score = 0;
                    foreach ($json_decoded->tests as $test) {
                        if (!empty($test->score)) {
                            $score += $test->score;
                        }
                        if (!empty($test->max_score)) {
                            $max_score += $test->max_score;
                        }
                    }
                    $gradingmanager = get_grading_manager($context, 'mod_assign', 'submissions');
                    $gradingmethod = $gradingmanager->get_active_method();
                    if (!$gradingmethod) {
                        \assignsubmission_noto\autogradeapi::upgrade_autograding_record($submission->id, array(
                            'status' => \assignsubmission_noto\constants::GRADINGFAILED,
                            'timesent' => time(),
                            'exttext' => \assignsubmission_noto\constants::$rc[97],    // synthetic NO_GRADING_CONFIGURED, added on request
                        ));
                        throw new \moodle_exception('Empty grading method - is grading configured correctly for cmid ' . $cm->id);
                    }
                    $controller = $gradingmanager->get_controller($gradingmethod);
                    if ($controller->is_form_available()) {

                        $grademenu = make_grades_menu($assign->get_grade_item()->grademax);
                        $allowgradedecimals = $assign->get_grade_item()->grademax > 0;
                        $controller->set_grade_range($grademenu, $allowgradedecimals);  // these 3 lines are garbage, but mandatory

                        $grade = $assign->get_user_grade($submission->userid, true);    // 3rd param $attemptnumber == NULL for the latest attempt
                        $itemid = null;
                        if ($grade) {
                            $itemid = $grade->id;
                        }

                        $gradinginstance = $controller->get_or_create_instance(0, $submission->userid, $itemid);

                        $criteria = $controller->get_definition()->guide_criteria;
                        $currentguide = $gradinginstance->get_guide_filling();
                        $first_criterion_index = 0;

                        // $criteria is the array of the marking guide criteria. The first criterion (min id) is assumed the automated grading
                        if ($criteria && $j = min(array_keys($criteria))) {
                            $first_criterion_index = $j;
                        } else {
                            \assignsubmission_noto\autogradeapi::upgrade_autograding_record($submission->id, array(
                                'status' => \assignsubmission_noto\constants::GRADINGFAILED,
                                'timesent' => time(),
                                'exttext' => \assignsubmission_noto\constants::$rc[98],    // synthetic NO_MARKING_ENABLED, added on request
                            ));
                            throw new \moodle_exception(sprintf('Marking guide does not seem enabled or configured for assignment cmid %d', $cm->id));
                        }
                        $currentguide['criteria'][$first_criterion_index]['score'] = $score;    // modify or create the first criterion's score
                        $grade->grade = $gradinginstance->submit_and_get_grade($currentguide, $grade->id);
                        $assign->update_grade($grade);  // this recalculates the final grade
                    } else {
                        \assignsubmission_noto\autogradeapi::upgrade_autograding_record($submission->id, array(
                            'status' => \assignsubmission_noto\constants::GRADINGFAILED,
                            'timesent' => time(),
                            'exttext' => \assignsubmission_noto\constants::$rc[97],    // synthetic NO_GRADING_CONFIGURED, added on request
                        ));
                        throw new \coding_exception("Controller's form not available"); // should never happen
                    }
                } else {
                    throw new \coding_exception('Cannot read '.\assignsubmission_noto\constants::RESULTSJSON);
                }
                $fs = get_file_storage();
                $file_record = array(
                    'contextid'=>$context->id,
                    'component'=>'assignsubmission_noto',
                    'filearea'=>\assignsubmission_noto\constants::FILEAREA,
                    'itemid'=>$submission->id,
                    'userid'=>$submission->userid,
                    'filepath'=>'/',
                    'filename'=>\assignsubmission_noto\constants::RESULTSJSON,
                );
                $file = $fs->get_file($file_record['contextid'], $file_record['component'], $file_record['filearea'], $file_record['itemid'], $file_record['filepath'], $file_record['filename']);
                if ($file) {
                    $file->delete();
                }
                $fs->create_file_from_pathname($file_record, $json_file);
                if (file_exists($pdf_file) && is_file($pdf_file) && is_readable($pdf_file)) {
                    $file_record['filename'] = \assignsubmission_noto\constants::RESULTSPDF;
                    $file = $fs->get_file($file_record['contextid'], $file_record['component'], $file_record['filearea'], $file_record['itemid'], $file_record['filepath'], $file_record['filename']);
                    if ($file) {
                        $file->delete();
                    }
                    $fs->create_file_from_pathname($file_record, $pdf_file);
                }
            }
            \assignsubmission_noto\autogradeapi::upgrade_autograding_record($submission->id, array(
                'status' => \assignsubmission_noto\constants::GRADED,
                'timesent' => time(),    // With GRADED, timesent is the timestamp of the grading
            ));
        } else {
            throw new \moodle_exception("Cannot open the result zip. Code: " . $zo);    // see https://www.php.net/manual/en/ziparchive.open.php (comments) for codes
        }

        return array('status'=>'OK');
    }

}
