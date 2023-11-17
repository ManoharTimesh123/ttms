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
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2018 Moodle Limited
 */

$string['pluginname'] = 'Batching Management';
$string['trainingname'] = 'Name/Title';
$string['all'] = 'All';
$string['trainingtype'] = 'Training Type';
$string['trainingfrom'] = 'Tentative Start Date';
$string['trainingto'] = 'Tentative End Date';
$string['numberofbatches'] = 'Number of batches';
$string['numberofcycles'] = 'Number of cycles';
$string['createdby'] = 'Created By';
$string['updatedby'] = 'Updated By';
$string['batchinglist'] = 'List of planned Trainings';
$string['batchedtraininglist'] = 'List of Batched Trainings';
$string['batchingplanning'] = 'Plan Batching';
$string['batchingcreate_training'] = 'Create New Training';
$string['batchingfilters'] = 'Filter Users';
$string['batchingvenues'] = 'Choose Venue';
$string['batchingdistributions'] = 'Distribute Users';
$string['subjects'] = 'Subjects';
$string['grades'] = 'Grades';
$string['posts'] = 'Posts';
$string['dojstartdate'] = 'Date of Joining Starts';
$string['dojenddate'] = 'Date of Joining Ends';
$string['roles'] = 'Roles';
$string['zones'] = 'Zones';
$string['diets'] = 'DIET\'s';
$string['venues'] = 'Venue';
$string['facilitator'] = 'Facilitator';
$string['coordinator'] = 'Coordinator';
$string['observer'] = 'Observer';
$string['percentage'] = 'Percentage of participants from each school';
$string['participantsperbatch'] = 'Participants per batch';
$string['trainingnoofdays'] = 'Number of days this training will take?';
$string['trainingnoofsessions'] = 'Number of sessions in each day?';
$string['sessiontime'] = 'Time of each session in minutes?';
$string['savechanges'] = 'Save Changes';
$string['approveproposal'] = 'Approve Proposal';
$string['rejectproposal'] = 'Reject Proposal';
$string['addendumproposal'] = 'Addendum Proposal';
$string['corrigendumproposal'] = 'Corrigendum Proposal';
$string['batched'] = 'batched';
$string['approved'] = 'approved';
$string['corrigendum'] = 'corrigendum';
$string['addendum'] = 'addendum';
$string['rejected'] = 'rejected';
$string['action'] = 'Action';
$string['view'] = 'View';
$string['status'] = 'Status';
$string['batchingfinancials'] = 'Batching Financials';
$string['itemtitle'] = 'Title';
$string['itemcost'] = 'Total Cost';
$string['itemunit'] = 'Unit';
$string['itemcosterrormessage'] = 'Only number and float value allowed';
$string['itemuniterrormessage'] = 'Unit must be grater than 0';
$string['batchingproposals'] = 'Proposal';
$string['batchinglaunched'] = 'Launch Training';
$string['nodalofficer'] = 'Nodal Officers';
$string['diethead'] = 'DIET\'s Heads';
$string['golivesubmitbutton'] = 'Launch Training';
$string['nodelofficerassigned'] = 'NO Assigned';
$string['dietadminassigned'] = 'DIET\'s Admin Assigned';
$string['launchtraining'] = 'Are you sure you want to launch this training?';
$string['batching:propose'] = 'Propose training';
$string['serialnumber'] = 'Serial number';
$string['participantname'] = 'Participant';
$string['schoolname'] = 'School';
$string['fullname_help'] = 'Please enter training name/title.';
$string['startdate_help'] = 'Please enter training start date.';
$string['enddate_help'] = 'Please enter training end date.';
$string['summary_help'] = 'Please enter training summary.';
$string['nodalofficers_help'] = 'Please enter select nodal officers for training.';
$string['dietheads_help'] = 'Please enter DIET\'s heads for training.';
$string['roles_help'] = 'Please select user role.';
$string['zones_help'] = 'Please select training zones.';
$string['diets_help'] = 'Please select training diets.';
$string['subjects_help'] = 'Please select training subjects.';
$string['grades_help'] = 'Please select training grades.';
$string['posts_help'] = 'Please select training posts.';
$string['trainingnoofdays_help'] = 'Please enter number of days this training will take.';;
$string['trainingnoofsessions_help'] = 'Please enter number of sessions in each day.';;
$string['sessiontime_help'] = 'Please enter time of each session in minutes.';
$string['percentage_help'] = 'Please enter percentage of participants from each school.';
$string['participantsperbatch_help'] = 'Please enter participants per batch.';
$string['trainingstartdate_help'] = 'Training start date.';
$string['trainingenddate_help'] = 'Training end date.';
$string['venues_help'] = 'Please select training venues.';
$string['facilitators_help'] = 'Please select training facilitators.';
$string['observers_help'] = 'Please select training observers.';
$string['category_help'] = 'Please select training category.';
$string['itemtitle_help'] = 'Please Enter item title.';
$string['itemunit_help'] = 'Please Enter item unit.';
$string['itemcost_help'] = 'Please Enter item cost.';
$string['filenumber'] = 'File Number';
$string['addcomment'] = 'Add Comment';
$string['commentcharacterlimit'] = 'Characters should not be more than 500 (characters)';
$string['corrigendumwarningmessage'] = 'You can only edit items';
$string['addendumwarningmessage'] = 'You can only add items';
$string['trainingstartdatevalidationerror'] = 'Start date should be current date or future date';
$string['trainingenddatevalidationerror'] = 'End date should be greater than start date';
$string['certificatetemplate'] = 'Certificate Template';
$string['batching:perform'] = 'Add/view training';
$string['batching:approve'] = 'Approved training';
$string['batching:reject'] = 'Reject training';
$string['batching:launch'] = 'Launch training';
$string['approvedtraininglist'] = 'List of approved trainings';
$string['trainingshortname'] = 'Short Name';
$string['courseimage'] = 'Image';
$string['shortname_help'] = 'Please enter training short name.';
$string['trainingimage_help'] = 'Please enter training image.';
$string['shortnametaken'] = 'Shortname is already taken.';
$string['nopermission'] = 'You do not have permission to access this page.';
$string['nodataavailable'] = 'No data available';
$string['financials'] = 'Financials';
$string['participants'] = 'Participants';
$string['close'] = 'Close';
$string['nopredefinerule'] = 'No pre defined rule found. Please enter manually.';
$string['categoryadded'] = 'This category is already added in this batching.';
$string['courseselection'] = 'Following course section would be created. Once training gets live';
$string['trainingday'] = 'Training Day';
$string['morningattendance'] = 'Morning attendance';
$string['pretest'] = 'Pre test';
$string['studymaterial'] = 'Study material.';
$string['eveningattendance'] = 'Evening attendance.';
$string['posttest'] = 'Post test.';
$string['feedbackforms'] = 'Feedback forms.';
$string['certificate'] = 'Certificate.';
$string['launchtraining'] = 'Launch Training';
$string['statecouncil'] = 'STATE COUNCIL OF EDUCATIONAL RESEARCH & TRAINING';
$string['autonomousorganization'] = 'An Autonomous Organization of Education Department, Govt. of NCT of Delhi.';
$string['organizationaddress'] = 'VARUN MARG, DEFENCE COLONY, NEW DELHI-110024';
$string['date'] = 'Date: _____/______/_______.';
$string['filenumber'] = 'File Number : ';
$string['subject'] = 'Subject';
$string['objective'] = 'Objective';
$string['scheduletraining'] = 'Schedule of training programs';
$string['cycle'] = 'Cycle';
$string['startdate'] = 'Start Date';
$string['starttime'] = 'Start Time';
$string['endtime'] = 'End Time';
$string['venuedescription'] = 'The venue of each batch will be communicated through circular of respective DIET.';
$string['zone'] = 'ZONE';
$string['noofcycle'] = 'No of Cycle';
$string['noofbatchesineachcycle'] = 'No of batches in each cycle';
$string['totalbatches'] = 'Total batches';
$string['participantsorbeneficiaries'] = 'Participants/Beneficiaries';
$string['financialimplications'] = 'Financial Implications (Per head/venue)';
$string['placedopposite'] = 'Placed Opposite';
$string['teaandlunchorganised'] = 'Tea and lunch will be organised by respective DIET\'s';
$string['requiredstationaryandtlm'] = 'The stationary and TLM required for the session(chart,paper and sketch pen) to be managed by respective DIET\'s.';
$string['coordinatorassigned'] = 'Coordinator will be assigned by respective DIET\'s as per allocation.';
$string['filesettledatdiets'] = 'File will be settled at respective DIET\'s';
$string['category'] = 'Category';
$string['item'] = 'Item';
$string['unit'] = 'Unit';
$string['cost'] = 'Cost';
$string['total'] = 'TOTAL';
$string['annexure_a'] = 'Annexure A';
$string['forcycle'] = ' for cycle ';
$string['employeeid'] = 'Employee ID';
$string['employeename'] = 'Employee Name';
$string['schoolid'] = 'School ID';
$string['schoolidstr'] = 'SchoolID';
$string['schoolname'] = 'School Name';
$string['line'] = '___________________________';
$string['branchname'] = 'S.O. Acedemic Branch';
$string['dateformat'] = 'Date: _____/______/_______';
$string['errormessage'] = 'Something looks wrong. Please contact admin.';
$string['approvedstr'] = 'Approved';
$string['pending'] = 'Pending';
$string['eveningattendance'] = ' Evening Attendance';
$string['warningmsg'] = 'error occured: no participant per batch found.';
$string['proposed'] = 'proposed';
$string['administrativeapproval'] = 'Administrative approval of the Director,SCERT is hereby conveyed for  {$a->custommoney} + applicable taxes in connection wih Capacity Building Programme on {$a->proposalsname}  to be held on as per following schedule at various venues under over all coordinatorship of {$a->nodalofficerslist}, SCERT as per delails given below.';
$string['sno'] = 'Sno';
$string['district'] = 'District';
$string['capacity'] = 'Capacity';
$string['selectall'] = 'Select All';
$string['pendingapproval'] = 'Pending Approval';
$string['userapprovalmsg'] = 'Note: You can proceed further only when "Approved" venues are more than equal to batches and "Participant after adjustment" are between 35 and 55.';
$string['downloadproposal'] = 'Download Proposal';
$string['downloadcircular'] = 'Download Circular';
$string['batchedstr'] = 'Batched';
$string['readonly'] = 'readonly';
$string['scheduletrainingprogram'] = 'Schedule of training programs';
$string['financialsaftercorrigendum'] = 'Financials after corrigendum';
$string['financialsafteraddendum'] = 'Financials after addendum';
$string['hyphen'] = '-';
$string['trainingsuccessfullylaunched'] = 'Training has successfully launched. Please click below button to view the training.';
$string['viewtraining'] = 'View Training';
$string['launched'] = 'launched';
$string['select'] = '--Select--';
$string['createproposal'] = 'Create Proposal';
$string['finishedaddingfinancials'] = 'Finished with adding financials?';
$string['sessiontimestr'] = 'sessiontime';
$string['trainingnoofsessionsstr'] = 'trainingnoofsessions';
$string['foundparticipant'] = 'Found  <b>{$a->participants}</b>  participants in this training <br/>';
$string['estimatedcostdescription'] = 'Hence the estimated cost of <b> {$a->name} </b> would be <b> {$a->dependencyvalue} * {$a->participants} </b>.<br />';
$string['lunchestimatedcostdescription'] =  'Hence the estimated cost of <b> {$a->lunchtypename} </b> would be <b> {$a->dependencyvalue} * {$a->participants} </b> = <b> {$a->price}</b>.<br />';
$string['facilitatorscountinfo'] =  'Found <b> {$a->facilitatorscount} </b> participants in this training <br />';
$string['facilitatorestimatedcost'] = 'Hence, the estimated cost of each facilitator would be: <br /><br />';
$string['facilitatordetails'] = '
                    <b> {$a->firstname} {$a->lastname} </b> will be taking <b> {$a->sessions}
                    </b> sessions and his/her payment would be <b> {$a->sessions} *  {$a->dependencyvalue} </b>
                    = <b> {$a->price} </b>.<br />';
$string['trainingdayandcoordinators'] = 'Found <b> {$a->trainingnoofdays} </b> training days and <b> {$a->corordinatorcount} </b> coordinators <br />';
$string['coordinatorestimatedcost'] = 'Hence the estimated cost of <b> {$a->name} </b> would be <b> { $a->dependencyvalue} * {$a->trainingnoofdays} * {$a->corordinatorcount} </b>.<br />';
$string['foundbatchesintraining'] = 'Found  <b> {$a->batchescount} </b> batches in this training and <b> {$a->corordinatorcount} </b> co-ordinatorand <b> {$a->trainingnoofdays} </b> training days.<br />';
$string['foundbatchesestimatedcost'] = 'Hence the estimated cost of <b> {$a->name} </b> would be <b> {$a->dependencyvalue} * {$a->corordinatorcount} * {$a->trainingnoofdays} </b>.<br />';
$string['foundgroupbatchestraining'] = 'Found  <b> {$a->batchescount} </b> batches in this training and per group amount is <b> {$a->dependencyvalue} </b><br />';
$string['foundgroupbatchestrainingestimatedcost'] = 'Hence the estimated cost of <b> {$a->name} </b> would be <b> {$a->dependencyvalue} * {$a->batchescount} </b>.<br />';
$string['foundmobiletelephonetraining'] = 'Found  <b> {$a->trainingnoofdays} </b> number of days in this training and per day amount is <b> {$a->dependencyvalue}</b><br />';
$string['foundmobiletelephonetrainingestimatedcost'] = 'Hence the estimated cost of <b> {$a->name} </b> would be <b> {$a->dependencyvalue} * {$a->trainingnoofdays} </b>.<br />';
$string['batchingvenues'] = 'Venues in your batching.';
$string['schoolsimpacted'] = 'Schools impacted';
$string['cycles'] = 'Cycles';
$string['Batchesheading'] = 'Batches ';
$string['participantperbatch'] = 'Participant per batch ';
$string['participantafteradjustment'] = 'Participant after adjustment';
$string['participantpicked'] = 'Participant picked';
$string['availablevenuesfilter'] = 'Available Venues as per filter';
$string['proceeddistribution'] = 'Proceed with the distribution of users';
$string['cycleid'] = 'Cycle ID';
$string['batchid'] = 'Batch ID';
$string['participantsfeedback'] = 'Participants Feedback';
$string['facilitatorfeedback'] = 'Facilitator Feedback';
$string['coordinatorfeedback'] = 'Coordinator Feedback';
$string['trainingdetail'] = 'Training Detail';
$string['alreadyexists'] = 'This file number has already been used by another proposal';
$string['enddate'] = 'End date';
$string['schooladdress'] = 'Address';
$string['notavailable'] = 'Not Available';


