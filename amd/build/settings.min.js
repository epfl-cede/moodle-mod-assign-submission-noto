//
// This file is part of SCORM module for moodle
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
//
//
// SCORM video JS file
//
// @package    mod_scorm
// @copyright  2021 Enovation
//
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.

define(['jquery'], function($) {

    function show_kubernetes_mode() {
        $('#admin-apiserver:has(label[for*="noto"])').hide();
        $('#admin-apiwspath:has(label[for*="noto"])').hide();
        $('#admin-apinotebookpath:has(label[for*="noto"])').hide();
        $('#admin-apiuser:has(label[for*="noto"])').hide();
        $('#admin-apikey:has(label[for*="noto"])').hide();
        $('#admin-maxdepth:has(label[for*="noto"])').hide();
        $('#admin-userprofilepassword:has(label[for*="noto"])').hide();
        $('#admin-authmethod:has(label[for*="noto"])').hide();
        $('#admin-apiurl:has(label[for*="noto"])').show();
        $('#admin-apiusername:has(label[for*="noto"])').show();
        $('#admin-apisecretkey:has(label[for*="noto"])').show();
        $('#admin-apiusernameparam:has(label[for*="noto"])').show();
        $('#admin-apiusernameparamprefix:has(label[for*="noto"])').show();
    }

    function hide_kubernetes_mode() {
        $('#admin-apiserver:has(label[for*="noto"])').show();
        $('#admin-apiwspath:has(label[for*="noto"])').show();
        $('#admin-apinotebookpath:has(label[for*="noto"])').show();
        $('#admin-apiuser:has(label[for*="noto"])').show();
        $('#admin-apikey:has(label[for*="noto"])').show();
        $('#admin-maxdepth:has(label[for*="noto"])').show();
        $('#admin-userprofilepassword:has(label[for*="noto"])').show();
        $('#admin-authmethod:has(label[for*="noto"])').show();
        $('#admin-apiurl:has(label[for*="noto"])').hide();
        $('#admin-apiusername:has(label[for*="noto"])').hide();
        $('#admin-apisecretkey:has(label[for*="noto"])').hide();
        $('#admin-apiusernameparam:has(label[for*="noto"])').hide();
        $('#admin-apiusernameparamprefix:has(label[for*="noto"])').hide();
    }

    function check_kubernetes_mode() {
        if ($('input[name=s_assignsubmission_noto_kubernetes_mode]').is(':checked')) {
            show_kubernetes_mode();
        } else {
            hide_kubernetes_mode();
        }
    }

    return {
        init: function() {
            $(document).ready(function($) {
                check_kubernetes_mode();
                $('input[name=s_assignsubmission_noto_kubernetes_mode]').on('change', function() {
                    check_kubernetes_mode();
                });
            });
        }
    };
});
