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
 * Choose Trainings
 * @package    block_choose_trainings
 */



function render_block_choose_trainings($traininginfo){
    global $DB, $CFG;
    
    $choosetrainings = '
        <div class="ccnDashBl choose-training-wrap text-center px-5 pb-3 pt-0">
            <h1>' . get_string('choosetrainingtype', 'block_choose_trainings') . '</h1>
            <div class="row">
                <div class="col-lg-6 person-training-box p-0 pt-4">
                    <div class="d-flex flex-column h-100">
                        <div>
                            <img src="'.$CFG->wwwroot.'/blocks/choose_trainings/pix/person-training.png" class="training-img">
                            <h2 class="py-4">' . $traininginfo->titleinperson . '</h2>
                        </div>
                        <div class="person-training-box content-box text-right p-4 d-flex flex-column h-100 justify-content-between">
                            <p class="text-white text-left pb-2">' . $traininginfo->inperson . '</p>
                            <a class="bg-white px-3 py-2 rounded-pill h4 font-weight-normal d-table ml-auto" href=' . new moodle_url($CFG->wwwroot . '/local/batching/create_training.php?modality=offline') . '> ' . get_string('start', 'block_choose_trainings') . ' <i class="fa fa-arrow-right"> </i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 online-training-box p-0 pt-4">
                    <div class="d-flex flex-column h-100">
                        <div>
                            <img src="'.$CFG->wwwroot.'/blocks/choose_trainings/pix/online-training.png" class="training-img">  
                            <h2  class="py-4">' . $traininginfo->titleonline . '</h2>
                        </div>
                        <div class="online-training-box content-box text-right p-4 d-flex flex-column h-100 justify-content-between">
                            <p class="text-white text-left pb-2">'
                               . $traininginfo->online .
                            '</p>
                            <a class="bg-white px-3 py-2 rounded-pill h4 font-weight-normal d-table ml-auto" href=' . new moodle_url($CFG->wwwroot . '/local/batching/create_training.php?modality=online') . '>' . get_string('start', 'block_choose_trainings') . ' <i class="fa fa-arrow-right"> </i></a>
                        </div>
                    </div>
                </div>
            </div>
         </div>';

    return $choosetrainings;
}
