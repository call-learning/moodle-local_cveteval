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
 * You may localized strings in your plugin
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['actions'] = 'Actions';
$string['add'] = 'Add';
$string['allsituations'] = 'All situations';
$string['appraisal:appraiser'] = 'Appraiser name';
$string['appraisal:comment'] = 'Comment';
$string['appraisal:comment:header'] = 'Appraisal Comment';
$string['appraisal:context'] = 'Context';
$string['appraisal:count'] = 'Appraisal #';
$string['appraisalcriterion:grade'] = 'Final Grade';
$string['appraisal:entity'] = 'Appraisal';
$string['appraisal:grade'] = 'Grade';
$string['appraisal:modificationdate'] = 'Modified';
$string['appraisal:planning'] = 'Evaluation plan';
$string['appraisal:student'] = 'Student name';
$string['appraiser'] = 'Appraiser';
$string['assess'] = 'Assess "{$a}"';
$string['assessment'] = 'Assessment';
$string['cachedef_appraisals'] = 'Cache for appraisal retrieval';
$string['cleanup:confirm:model'] = 'This will cleanup all table for CompetVetEval, like situations list but also all apppraisal done so far and data will not be recoverable. Are you sure ?';
$string['cleanup:confirm:userdata'] = 'This will cleanup all apppraisals done so far (user data) but keep the model. Data will not be recoverable. Are you sure ?';
$string['cleanup:details']  = 'Cleanup the import {$a->idnumber} : {$a->comments}';
$string['cleanup:model'] = 'Remove a specific model';
$string['cleanup:selectimportid']  = 'Select Import to cleanup';
$string['cleanup:userdata'] = 'Cleanup user data from a specific import';
$string['comment'] = 'Comment';
$string['commentformat'] = 'Comment Format';
$string['comments'] = 'All comments';
$string['context'] = 'Context';
$string['contextformat'] = 'Context Format';
$string['criterion:comment'] = 'Comment (Criteria)';
$string['criterion:entity'] = 'Criterion';
$string['criterion:label'] = 'Label';
$string['criterion:comment:header'] = 'Criterion\'s comments';
$string['csvdelimiter'] = 'CSV Delimiter';
$string['cvetevalappservicename'] = 'CompetVetEval Application Service';
$string['cveteval:cleanupdata'] = 'Can cleanup data';
$string['cveteval:datamigration'] = 'Can execute data Migration';
$string['cveteval:exportgrades'] = 'Can export grades';
$string['cveteval:manageentities'] = 'Can Manage entities';
$string['cveteval:managecriteriontemplate'] = 'Manage criterion';
$string['cveteval:manageevaluationtemplate'] = 'Manage evaluation';
$string['cveteval:manageimport'] = 'Can manage importation';
$string['cveteval:exportall'] = 'Can export all data';
$string['cveteval:mobileaccess'] = 'Has mobile Access';
$string['cveteval:viewallcriteria'] = 'Can view all criteria';
$string['cveteval:viewallsituations'] = 'Can view all situations';
$string['cveteval:viewfiles'] = 'Can view files';
$string['datamigration'] = 'Data migration';
$string['datamigration_help'] = 'Data migration between different importations';
$string['datamigrationstep'] = 'Data Migration / {$a}';
$string['defaulthistoryidnumber'] = 'Import:{$a}';
$string['delete'] = 'Delete';
$string['description'] = 'Description';
$string['descriptionformat'] = 'Description Format';
$string['dmc:matchedentities'] = 'Entities present in both models';
$string['dmc:expired'] = 'The session containing the migration data has expired. You will need to restart the process';
$string['dmc:matched'] = 'Matched';
$string['dmc:orphanedentities'] = 'Entities present only in the old model';
$string['dmcstep:choosehistory'] = 'Choose models';
$string['dmcstep:diffmodelsmodifications'] = 'Model modifications';
$string['dmcstep:diffmodels'] = 'Show differences';
$string['dmcstep:init'] = 'Start data migration';
$string['dmcstep:final'] = 'Final step';
$string['dmcstep:diffmodels'] = 'Show differences';
$string['dmcstep:userdatamigration'] = 'Migration of user data';
$string['dmc:unmatchedentities'] = 'Entities present only in the new model';
$string['dmc:unmatched'] = 'Unmatched';
$string['dmc:welcomemessage'] = 'Welcome to the data migration wizard. This will help you to copy user data from one model (or importation) to the other. Some data will be easily matched but some will need you to decide what to do.';
$string['dmc:congratsmessage'] = 'The migration is finished. Click on continue to start another one.';
$string['download:model'] = 'Download Model';
$string['download:userdata'] = 'Download User data';
$string['edit'] = 'Edit';
$string['enablecompetveteval'] = 'Enable cveteval';
$string['encoding'] = 'Encoding';
$string['evaluation:assessor'] = "Assessor";
$string['evaluation:comment'] = "Comment";
$string['evaluation:date'] = "Evaluation Date";
$string['evaluation:grade'] = 'Grade';
$string['evaluationgrid:default'] = 'Default Evaluation Grid';
$string['evaluation_grid:entity'] = 'Evaluation Grid';
$string['evaluation_gridfile'] = 'Evaluation grid';
$string['evaluation_gridfile_help'] = '(Optional)A list of evaluation criteria in CSV format. The file must contain the following headers <pre>"Evaluation Grid Id";"Criterion Id";"Criterion Parent Id";"Criterion Label"</pre>';
$string['evaluation_grid:idnumber'] = 'Evaluation Identifier';
$string['evaluation_grid:name'] = 'Evaluation Name';
$string['evaluation_grid:plural'] = 'Evaluation Grids';
$string['evaluationgrid:stats'] = 'Imported criteria: {$a->criterions}';
$string['evaluation:waiting'] = "Waiting...";
$string['evaluation:hasgrade'] = 'Has grade?';
$string['evaluation_template:version'] = 'Evaluation Version';
$string['export:dateformat'] = '%d/%m/%Y';
$string['final_evaluation:entity'] = 'Final evaluation';
$string['fullname'] = 'Full Name';
$string['grade:defaultscale'] = 'Default scale (CompetvetEval)';
$string['grade:defaultscale:description'] = 'Default scale for CompetvetEval';
$string['grades:export'] = 'Export Grades';
$string['grade:value'] = 'Grade: {$a}';
$string['group_assignment:entity'] = 'Group Assignment';
$string['group:entity'] = 'Group';
$string['groupingfile_help'] = 'A list of grouping in CSV format. The file must contain the following headers <pre>"Nom de l\'étudiant";"Prénom";"Identifiant";"Groupement 1"</pre>';
$string['groupingfile'] = 'List of student grouping';
$string['grouping:stats'] = 'Imported group : {$a->groups}, group assignments: {$a->groupassignments}';
$string['grouping:usernotfound'] = 'Grouping user not found {$a}';
$string['headingfile_help'] = 'You can import situation, plannning and groups here. All files except the evaluation grid are manadatory here .<br>Beware all files must match. For example: <ul> <li>(Situation) - GrilleEval => (Grille Evaluation) - Evaluation Grid Id</li> <li>(Planning) - Groupe XXX => (Groupes) - Un des groupes dans les colonnes de Groupement</li> <li>(Planning) - Situation (nom court) => (Situation) - Nom court</li> </ul> Any mismatch in the files can lead to malfunctionning applications.';
$string['headingfile'] = 'Import files';
$string['headingfile_link'] = "http://pedagogie.vetagro-sup.fr/Pages/CompetVet/co/App_Moodle.html";
$string['history:tools'] = 'Tools';
$string['id'] = 'Id';
$string['idnumber'] = 'ID Number';
$string['import:cleanupbefore'] = 'Vider les tables avant...';
$string['import:cleanup'] = 'Cleanup and restart';
$string['import:comment_help'] = 'Import comments, used internally, not required';
$string['import:comment'] = 'Import comments';
$string['import:dateformat'] = 'd/m/Y';
$string['import:destimportid'] = 'Model: destination';
$string['import:downloadfile'] = 'Download {$a}';
$string['import:download'] = 'Previous import download';
$string['import:error:idnumberexists'] = 'L\'identifiant d\'importation existe déjà {$a}';
$string['import:evaluation_grid'] = 'Evaluation grid';
$string['import:failed'] = 'The import has failed. Please follow the link below to cleanup the current data and start again.';
$string['import:grouping'] = 'Grouping';
$string['import:heading:parameters'] = 'Importation parameters';
$string['import:heading:process'] = 'Process files';
$string['import:idnumber_help'] = 'Import Identifier, must be unique';
$string['import:idnumber'] = 'Import Identifier';
$string['import'] = 'Import';
$string['import:imported'] = 'Imported {$a->rowcount}/{$a->totalrows}';
$string['import:importing'] = 'Importing {$a}';
$string['import:importviacron'] = 'Import via CRON';
$string['import:listall'] = 'All Imports List';
$string['import:list'] = 'Import list';
$string['import:logs'] = 'Import logs';
$string['import:new'] = 'New Import';
$string['import:origindestmustdiffer'] = 'Origin and destination must differ';
$string['import:originimportid'] = 'Model: origin';
$string['import:planning'] = 'Planning';
$string['import:selectimport'] = 'Select and import';
$string['import:situation'] = 'Situation';
$string['import:start'] = 'Start import';
$string['list'] = 'List';
$string['log:fieldname'] =  'Field';
$string['log:importid'] =  'ID';
$string['log:information'] =  'Information';
$string['log:level:error'] = 'Error';
$string['log:level:info'] = 'Info';
$string['log:level'] =  'Severity';
$string['log:level:warning'] = 'Warning';
$string['log:linenumber'] =  'Line';
$string['log:origin'] =  'File';
$string['mysituations:intro'] = 'This is the list of situations you should be able to evaluate. Please click on a row to see a list of appraisals / students to assess/evaluate.';
$string['mysituations'] = 'My situations';
$string['mystudents'] = 'My students';
$string['name'] = 'Full Name';
$string['otherstudents'] = 'Other students';
$string['planning:clsituationid'] = 'Clinical situation';
$string['planning:dateoverlaps'] = 'Planning date overlaps : on line ({$a->prevrowindex}) the range {$a->previousstartdate} - {$a->previousenddate} matches current {$a->currentstartdate} - {$a->currentenddate}.';
$string['planning:endtime'] = 'End time';
$string['planning:entity'] = 'Evaluation Planning';
$string['planningfile_help'] = 'A list of planning in CSV format. The file must contain the following headers <pre>"Date début";"Date fin";"Groupe XX";"Groupe YY"</pre>';
$string['planningfile'] = 'List of planning';
$string['planning:groupdoesnotexist'] = 'Group {$a} does not exist.';
$string['planning:groupid'] = 'Group ID';
$string['planning:groupname'] = 'Group Name';
$string['planning:invalidstarttime'] = 'Date format error for Start date ({$a})';
$string['planning:invalidendtime'] = 'Date format error for End date ({$a})';
$string['planning:nogroupdefined'] = 'No group defined in planning.';
$string['planning:plural'] = 'Evaluations Planning';
$string['planning:requiredappraisals'] = 'Required Appraisal #';
$string['planning:situationnotfound'] = 'Situation {$a} not found when importing planning.';
$string['planning:starttime'] = 'Start time';
$string['planning:stats'] = 'Imported plans: {$a->plannings}, planning events : {$a->planningevents}';
$string['planning:studentid'] = 'Student';
$string['planning:title'] = 'Title';
$string['pluginname'] = 'CompetVet Eval plugin';
$string['role:entity'] = 'Roles';
$string['role:type:appraiser'] = 'Appraiser';
$string['role:type:assessor'] = 'Assessor';
$string['role:type:student'] = 'Student';
$string['saved'] = 'Saved';
$string['settings:general'] = 'Paramètres généraux';
$string['settings:grade_scale'] = 'Grade Scales';
$string['settings:manage_evaluation_templates'] = 'Manage evaluation templates';
$string['settings:manage_situations'] = 'Manage situations';
$string['situation:entity'] = 'Clinical situation';
$string['situation:evalgridid'] = 'Linked Evaluation Grid';
$string['situation:expectedevalsnb'] = '#Eval';
$string['situationfile_help'] = 'A list of situation in CSV format. The file must contain the following headers <pre>"Description";"Nom";"Nom court";"ResponsableUE";"Responsable";"Evaluateurs";"Observateurs";"Appreciations"; "GrilleEval";"Etiquettes"</pre>';
$string['situationfile'] = 'List of situations';
$string['situation:gridnotfound'] = 'Grid not found {$a}.';
$string['situation:groupname'] = 'Group name';
$string['situation:plural'] = 'Clinical situations';
$string['situation:stats'] = 'Imported roles: {$a->roles}, planning situations : {$a->situations}';
$string['situation:title'] = 'Title';
$string['situation:usernotfound'] = 'User not found {$a}.';
$string['student'] = 'Student';
$string['thissituation'] = 'This situation';
$string['title'] = 'Title';
$string['userdatamigration:student'] = 'Student';
$string['userdatamigration:criterion'] = 'Criterion';
$string['userdatamigration:appraiser'] = 'Observer';
$string['userdatamigration:assessor'] = 'Assessor';
$string['userdatamigration:grade'] = 'Grade';
$string['userdatamigration:planning'] = 'Plan';
$string['userdatamigration:situation'] = 'Situation';
$string['view'] = 'View';

