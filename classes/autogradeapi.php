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
 * This file contains autograde methods. the autograde DB should not be accessed directly from code
 *
 * @package assignsubmission_noto
 * @copyright 2024 Enovation {@link https://enovation.ie}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_noto;

defined('MOODLE_INTERNAL') || die();

class autogradeapi {
    private $autograde_record = array();
    private $event = array();
    private $cm = null;
    private \stdClass $modconfig;
    private $curloptions = [];
    function __construct(\cm_info $cm) {
        global $DB;
        $this->cm = $cm;
        $autograde_record = $DB->get_record('assignsubmission_noto_assign', array('assignment'=>$cm->instance));
        if (!$autograde_record) {
            $autograde_record = new \stdClass();
            $autograde_record->assignment = $cm->instance;
            // new record - check if the autograder zip is present
            $file_record = array(
                'contextid' => $cm->context->id,
                'component' => 'assignsubmission_noto',
                'filearea' => \assignsubmission_noto\constants::FILEAREA,
                'itemid' => $cm->instance,
                'filepath' => '/',
                'filename' => \assignsubmission_noto\constants::AUTOGRADEZIP,
            );
            $fs = get_file_storage();
            $afile = $fs->get_file($file_record['contextid'], $file_record['component'], $file_record['filearea'], $file_record['itemid'],
                    $file_record['filepath'], $file_record['filename']);
            if (!$afile) {
                $autograde_record->autograde_disabled = true;
            }
            $autograde_record->id = $DB->insert_record('assignsubmission_noto_assign', $autograde_record);
        }
        $this->autograde_record = $DB->get_record('assignsubmission_noto_assign', array('id'=>$autograde_record->id));
        // pre-populate event
        $this->event['context'] = $cm->context;
        $this->event['objectid'] = $cm->id;
        $this->event['other']['instanceid'] = $cm->instance;
        $this->event['other']['name'] = '';
        $this->event['other']['modulename'] = 'assign';
        $this->event['other']['action'] = '';

        $this->modconfig = get_config('assignsubmission_noto');
        if (!empty($this->modconfig->connectiontimeout)) {
            $this->curloptions['CURLOPT_CONNECTTIMEOUT'] = $this->modconfig->connectiontimeout;
        }
        if (!empty($this->modconfig->executiontimeout)) {
            $this->curloptions['CURLOPT_TIMEOUT'] = $this->modconfig->executiontimeout;
        }
    }
    public function suspend () {
        global $DB;
        $this->autograde_record->autograde_suspended = true;
        $DB->update_record('assignsubmission_noto_assign', $this->autograde_record);
        $this->event['other']['action'] = 'suspend';
        \assignsubmission_noto\event\autograde_updated::create($this->event)->trigger();
    }
    public function unsuspend () {
        global $DB;
        $this->autograde_record->autograde_suspended = false;
        $DB->update_record('assignsubmission_noto_assign', $this->autograde_record);
        $this->event['other']['action'] = 'unsuspend';
        \assignsubmission_noto\event\autograde_updated::create($this->event)->trigger();
    }
    public function disable () {
        global $DB;
        $this->autograde_record->autograde_disabled = true;
        $DB->update_record('assignsubmission_noto_assign', $this->autograde_record);
        $this->event['other']['action'] = 'disable';
        \assignsubmission_noto\event\autograde_updated::create($this->event)->trigger();
    }
    public function is_suspended () {
        return $this->autograde_record->autograde_suspended;
    }
    public function is_disabled () {
        return $this->autograde_record->autograde_disabled;
    }
    public function send_for_autograde (int $submission_id): string {
        global $DB, $USER;
        $submission = $DB->get_record('assign_submission', array('id'=>$submission_id));
        # 1. get the autograde seed - from moodle filesystem
        $file_record = array(
            'contextid' => (\context_module::instance($this->cm->id))->id,
            'component' => 'assignsubmission_noto',
            'filearea' => \assignsubmission_noto\constants::FILEAREA,
            'itemid' => $this->cm->instance,
            'filepath' => '/',
            'filename' => \assignsubmission_noto\constants::AUTOGRADEZIP,
        );
        $fs = get_file_storage();
        $atograde_zip = $fs->get_file($file_record['contextid'], $file_record['component'], $file_record['filearea'], $file_record['itemid'],
            $file_record['filepath'], $file_record['filename']);
        if (!$atograde_zip) {
            throw new \moodle_exception('Attempt to autograde within no-autograde assignment');
        }
        $autogarde_content = base64_encode($atograde_zip->get_content());
        // now adapt $file_record for the submission zip
        $noto_name = \assign_submission_noto::get_noto_config_name($this->cm->instance);
        $file_record['filename'] = sprintf($noto_name.'_user%s.zip', $submission->userid);
        $file_record['userid'] = $submission->userid;
        $submission_zip = $fs->get_file($file_record['contextid'], $file_record['component'], $file_record['filearea'], $file_record['itemid'], $file_record['filepath'], $file_record['filename']);
        if (!$submission_zip) {
            throw new \moodle_exception("Submission zip not found");
        }
        $submission_content = base64_encode($submission_zip->get_content());
        $data = array(
            'solution'=>$autogarde_content,
            'submission'=>$submission_content,
            'submission_id'=>$submission_id,
            'course_id'=>$this->cm->course,
            'timestamp'=>0,
            'rc'=>0,
        );
        $url = new \moodle_url($this->modconfig->automaticgrading_url . '/push/'); # trailing slash mandatory
        $jsondata = json_encode($data);
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsondata),
            'Accept: application/json',
            'Access-token: ' . $this->modconfig->automaticgrading_token_send,
        );
        $curl = new \curl();
        $curl->setHeader($header);
        $jsonresult = $curl->post($url, $jsondata, $this->curloptions);
        $result = json_decode($jsonresult);

        if ($result && isset($result->return->status) && $result->return->status == \assignsubmission_noto\constants::OK) {
            return \assignsubmission_noto\constants::OK;
        } else {
            if (isset($result->detail[0]->msg)) {
                return $result->detail[0]->msg;
            } else if (empty($result)) {    // timeout
                return '';
            } else {
                error_log("Autograding call failed. Return: " . $jsonresult);
                return get_string('unspecified_error', 'assignsubmission_noto');
            }
        }
    }

    /** update or create upgrade_autograding_record record
     * @param int $submission_id
     * @param array $parameters: array of parameters "paramname"=>"paramvalue" where paramname one of columns od upgrade_autograding_record
     * @return void
     * @throws coding_exception, SQLException
     */
    public static function upgrade_autograding_record (int $submission_id, array $parameters): void {
        global $DB;
        if (!$submission_id) {
            throw new \coding_exception('upgrade_autograding_record: no submission id');
        }
        $record = $DB->get_record('assignsubmission_noto_autgrd', array('submission'=>$submission_id));
        $newrecord = false;
        if (!$record) {
            $record = new \stdClass();
            $record->submission = $submission_id;
            $record->status = \assignsubmission_noto\constants::NOTGRADED;
            $record->timesent = time();
            $record->attempt = 0;
            $newrecord = true;
        }
        foreach ($parameters as $k=>$v) {
            $record->$k = $v;
        }
        if ($newrecord) {
            $DB->insert_record('assignsubmission_noto_autgrd', $record);
        } else {
            $DB->update_record('assignsubmission_noto_autgrd', $record);
        }
    }
}
