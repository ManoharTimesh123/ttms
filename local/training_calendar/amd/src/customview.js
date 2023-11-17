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
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the core Moodle code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'local_training_calendar/dataTables.responsive',
    'core/str',
    'core/modal_factory',
    'core/modal_events',
    'core/ajax',
    'jquery',
    'jqueryui'
], function(dataTable, /*coFilter,*/ Str, ModalFactory, ModalEvents, Ajax, $) {

      var courseenrol;
    return courseenrol = {
        init: function(args) {
            
        },
        load:function(){
            
        },
        viewCalendarCoursesJSDatatable: function(args){
            $('#viewCalendarCoursesTable').DataTable({
                "searching": true,
                // "responsive": false,
                // "processing": true,
                "lengthMenu": [[10, 25,50,100, -1], [10,25, 50,100, "All"]],
                "bServerSide": true,
                "pageLength": 10,
                "aaSorting": [],
                "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [ 0,1,2,3,4,5,6,7 ] }],
                language: {
                    emptyTable: "No data available in table",
                    search: "_INPUT_",
                    searchPlaceholder: "Search",
                    "paginate": {
                        "next": ">",
                        "previous": "<"
                    },
                    "sProcessing": "<img src= "+ M.cfg.wwwroot + "/local/training_calendar/pix/ajax-loader.svg />"
                },
            "sAjaxSource": M.cfg.wwwroot + "/local/training_calendar/rendertr_courses.php?param="+args,
            });
        },//end of viewCalendarCoursesJSDatatable
    };
});
