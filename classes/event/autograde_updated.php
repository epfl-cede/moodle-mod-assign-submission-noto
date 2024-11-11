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
 * The assignsubmission_noto autograde_updated event.
 *
 * @package    assignsubmission_noto
 * @copyright  2020 Enovation {@link https://enovation.ie}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_noto\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The assignsubmission_noto autograde_updated event class.
 */
class autograde_updated extends \core\event\course_module_updated {

    /**
     * Init method.
     */
    protected function init() {
        parent::init();
        $this->data['objecttable'] = 'assignsubmission_noto';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $descriptionstring = "The user with id '$this->userid' updated Noto autograde setting with action '".$this->other['action']."' in course module id '$this->contextinstanceid'";
        return $descriptionstring;
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
    }

    public static function get_objectid_mapping() {
        // No mapping available for 'assignsubmission_noto'.
        return array('db' => 'assignsubmission_noto', 'restore' => \core\event\base::NOT_MAPPED);
    }
}
