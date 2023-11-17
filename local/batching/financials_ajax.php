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
 * The batching Management
 *
 * @package local_batching
 * @author  Tarun Upadhyay
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2018 Moodle Limited
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/local/batching/locallib.php');

global $DB;

$categoryid = $_POST['category_id'];
$batching = $_POST['batching'];

$data = $DB->get_record('local_financial_categories', ['id' => $categoryid]);
$categorycode = $data->code;

$dependencydata = $DB->get_records('local_financial_details', ['category' => $categoryid]);

$participants = get_participants_from_temp($batching, null, 'all');

$financialdata = [];

$batches = get_batches_by_baching_id($batching);
$corordinators = get_co_ordinators_by_batching($batching);
$facilitators = get_facilitators_by_batching($batching);
$filters = get_filters($batching);

if (array_key_exists('participantsperbatch', $filters)) {
    $participantsperbatch = $filters['participantsperbatch'];
}
if (array_key_exists('trainingnoofdays', $filters)) {
    $trainingnoofdays = $filters['trainingnoofdays'];
}
if (array_key_exists('sessiontime', $filters)) {
    $sessiontime = $filters['sessiontime'];
}
if (array_key_exists('trainingnoofsessions', $filters)) {
    $trainingnoofsessions = $filters['trainingnoofsessions'];
}

$name = $data->name;
$price = '';
$unit = '';
$description = '';

if ($_REQUEST['task'] == 'getlunchdetail') {

    $lunchtype = $_POST['lunch_type'];
    $lunchtea = $_POST['lunch_tea'];
    $lunchwater = $_POST['lunch_water'];

    // 10 : Lunch.
    if ($categorycode == 'lunchrefreshment') {
        $totalamountlunch = 0;
        $nameforfield = $name . ": \n";
        $unit = $participants = count($participants);
        if (!empty($dependencydata)) {
            $matched = 0;
            foreach ($dependencydata as $dependency) {
                if ($dependency->dependenton === 'trainingnoofdays') {
                    if (
                        $trainingnoofdays >= $dependency->fromvalue &&
                        $trainingnoofdays <= $dependency->tovalue &&
                        (
                            $lunchtype == $dependency->lunchtype ||
                            $lunchtea == $dependency->lunchtype ||
                            $lunchwater == $dependency->lunchtype
                        )
                    ) {

                        $lunchtype = $DB->get_record('local_financial_lunch_types', ['id' => $dependency->lunchtype]);

                        $matched++;
                        $price = $dependency->value * $participants * $trainingnoofdays;
                        $totalamountlunch = $totalamountlunch + $price;
                        $participantsinfo = [
                                    'participants' => $participants,
                                    'dependencyvalue' => $dependency->value,
                                    'lunchtypename' => $lunchtype->name,
                                    'price' => $price,
                                    'matched' => $matched
                                ];
                                
                        $description .= get_string('foundparticipant', 'local_batching', $participantsinfo);
                        $description .= get_string('lunchestimatedcostdescription', 'local_batching', $participantsinfo);

                        $nameforfield .= $matched . ': Cost of ' . $lunchtype->name . ' = ' . $dependency->value . '*' . $participants . ' = ' . $price . "\n";
                        
                    }
                }
            }
            if ($matched == 0) {
                $financialdata['unit'] = 0;
                $description = get_string('nopredefinerule', 'local_batching');
            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }
    // Calculation for stationary ends.

    $financialdata['name'] = $nameforfield;
    $financialdata['price'] = $totalamountlunch;
    $financialdata['unit'] = $unit;
    $financialdata['category_details'] = $description;

    echo json_encode($financialdata);
    exit;
}
// Lunch Logi.

if ($_REQUEST['task'] == 'category') {

    if ($DB->record_exists('local_batching_financials', ['category' => $categoryid, 'batching' => $batching])) {
        $description = get_string('categoryadded', 'local_batching');
        $financialdata['category_details'] = $description;
        echo json_encode($financialdata);
        exit;
    }

    // 1, 2 : Calculation for stationary start.
    if (
        $categorycode == 'stationary' ||
        $categorycode == 'tlm'
    ) {

        $unit = $participants = count($participants);
        if (!empty($dependencydata)) {
            foreach ($dependencydata as $dependency) {
                if ($dependency->dependenton === 'trainingnoofdays') {
                    if (
                        $trainingnoofdays >= $dependency->fromvalue &&
                        $trainingnoofdays <= $dependency->tovalue
                    ) {
                        $price = $dependency->value * $participants;
                       
                        $participantsinfo = ['participants' => $participants, 'dependencyvalue' => $dependency->value, 'name' => $name];
                        $description .= get_string( 'foundparticipant', 'local_batching', $participantsinfo );

                        $description .= get_string('estimatedcostdescription', 'local_batching', $participantsinfo);
                    }
                }
            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }
    // Calculation for stationary ends.

    // 3 : Calculation for Renumeration to Resource Person start.
    if ($categorycode == 'renumeratiiontoresource') {

        $grades = [1, 2, 3, 4, 5];

        $unit = $totalfacilitator = count($facilitators);

        if (!empty($facilitators)) {
            $facilitatorsinfo = ['facilitatorscount' => $totalfacilitator];

            $description .= get_string('facilitatorscountinfo', 'local_batching', $facilitatorsinfo );
            $description .= get_string('facilitatorestimatedcost', 'local_batching');

            $price = 0;
            foreach ($facilitators as $facilitator) {

                $facilitatorprice = 0;

                // Get facilitators grade.
                // TODO: This will come from profile later on.
                $facilitatorgrade = $grades[rand(0, 4)];

                $sql = <<<SQL
                        SELECT c.* FROM {local_batching_facilitators} c
                        JOIN {user} u ON u.id = c.user
                        WHERE c.batching = :batching
                        AND c.user = :facilitator
                        SQL;

                $params = [
                    'batching' => $batching,
                    'facilitator' => $facilitator->user,
                ];

                $facilitatorbatches = $DB->get_records_sql($sql, $params);

                $facilitatorsessions = 0;

                if (!empty($facilitatorbatches)) {
                    foreach ($facilitatorbatches as $facilitatorbatch) {

                        $sql = <<<SQL
                        SELECT c.id FROM {local_batching_facilitators} c
                        WHERE c.batch = :batch
                        SQL;

                        $params = [
                            'batch' => $facilitatorbatch->batch,
                        ];

                        $facilitatorinbatch = $DB->get_records_sql($sql, $params);
                        $facilitatorinbatchcount = count($facilitatorinbatch);
                        $facilitatorsessions = $facilitatorsessions + round($trainingnoofsessions / $facilitatorinbatchcount, 2);
                    }
                }

                foreach ($dependencydata as $dependency) {
                    if ($dependency->dependenton === 'sessiontime') {
                        if (
                            $sessiontime >= $dependency->fromvalue &&
                            $sessiontime <= $dependency->tovalue &&
                            $facilitatorgrade == $dependency->grade
                        ) {
                            $facilitatorprice = round($facilitatorsessions * $dependency->value , 2);
                            $price = $price + $facilitatorprice;
                        }
                    }
                }

                $facilitatordetails = [
                                    'firstname' => $facilitator->firstname,
                                    'lastname' => $facilitator->lastname,
                                    'sessions' => $facilitatorsessions,
                                    'dependencyvalue' => $dependency->value,
                                    'price' => $facilitatorprice,
                                ];
                $description .= get_string('facilitatordetails', 'local_batching', $facilitatordetails);

            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }
    // Calculation for Renumeration to Resource Person ends.

    // 4 : Conveyance charges of Resource Person.
    if ($categorycode == 'conveyancechargesofresource') {
        if (!empty($dependencydata)) {
            // Do nothing.
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }

    // 5 : Renumeration to Admin Co-ordinator (venue other than SCERT/DIETS).
    if ($categorycode == 'renumerationtoadmincoordinator') {

        $corordinatorcount = count($corordinators);
        $unit = $corordinatorcount;

        if (!empty($dependencydata)) {
            foreach ($dependencydata as $dependency) {
                if ($dependency->dependenton === 'perdaypricing') {
                    $price = $dependency->value * $trainingnoofdays * $corordinatorcount;
                    $traininginfo = [
                        'trainingnoofdays' => $trainingnoofdays,
                        'corordinatorcount' => $corordinatorcount,
                        'name' => $name,
                        'dependencyvalue' => $dependency->value,
                    ];
                    $description .= get_string('trainingdayandcoordinators', 'local_batching', $traininginfo);
                    $description .= get_string('coordinatorestimatedcost', 'local_batching', $traininginfo);
                }
            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }

    // 6 : Renumeration to Assistant Coordinator.
    if ($categorycode == 'renumerationtoassistantcordinator') {
        $corordinatorcount = count($corordinators);
        $batchescount = count($batches);
        $unit = $corordinatorcount;

        if (!empty($dependencydata)) {
            foreach ($dependencydata as $dependency) {
                if ($dependency->dependenton === 'batchescount') {
                    if (
                        $batchescount >= $dependency->fromvalue &&
                        $batchescount <= $dependency->tovalue
                    ) {
                        $price = $dependency->value * $corordinatorcount * $trainingnoofdays;
                        $batchesdetails = [
                            'batchescount' => $batchescount,
                            'corordinatorcount' => $corordinatorcount,
                            'trainingnoofdays' => $trainingnoofdays,
                            'name' => $name,
                            'dependencyvalue' => $dependency->value,
                        ];
                        $description .= get_string('foundbatchesintraining', 'local_batching', $batchesdetails);
                        $description .= get_string('foundbatchesestimatedcost', 'local_batching', $batchesdetails);
                    }
                }
            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }

    // 8 : Contingency.
    if ($categorycode == 'contingency') {

        $unit = $batchescount = count($batches);

        if (!empty($dependencydata)) {
            foreach ($dependencydata as $dependency) {
                if ($dependency->dependenton === 'trainingnoofdays') {
                    if (
                        $trainingnoofdays >= $dependency->fromvalue &&
                        $trainingnoofdays <= $dependency->tovalue
                    ) {
                        $price = $dependency->value * $batchescount;
                        $batchinfo = [
                            'batchescount' => $batchescount,
                            'dependencyvalue' => $dependency->value,
                            'name' => $name,
                        ];
                        $description .= get_string('foundgroupbatchestraining', 'local_batching', $batchinfo);                       
                        $description .= get_string('foundgroupbatchestrainingestimatedcost', 'local_batching', $batchinfo);
                    }
                }
            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }

    // 13 : Mobiletelephone.
    if ($categorycode == 'mobiletelephone') {

        $unit = $trainingnoofdays;

        if (!empty($dependencydata)) {
            foreach ($dependencydata as $dependency) {
                if ($dependency->dependenton === 'trainingnoofdays') {
                    if (
                        $trainingnoofdays >= $dependency->fromvalue &&
                        $trainingnoofdays <= $dependency->tovalue
                    ) {
                        $price = $dependency->value * $trainingnoofdays;
                        $mobiletelephonetrainingdetails = [
                            'trainingnoofdays' => $trainingnoofdays,
                            'dependencyvalue' => $dependency->value,
                            'name' => $name,
                        ];
                        $description .= get_string('foundmobiletelephonetraining', 'local_batching', $mobiletelephonetrainingdetails);
                        $description .= get_string('foundmobiletelephonetrainingestimatedcost', 'local_batching', $mobiletelephonetrainingdetails);
                    }
                }
            }
        } else {
            $description = get_string('nopredefinerule', 'local_batching');
        }
    }

    $financialdata['name'] = $name;
    $financialdata['price'] = $price;
    $financialdata['unit'] = $unit;
    $financialdata['category_details'] = $description;

    echo json_encode($financialdata);
}
exit;
