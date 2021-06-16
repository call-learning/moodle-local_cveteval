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
$string['appraiser'] = 'Appraiser';
$string['actions'] = 'Actions';
$string['add'] = 'Add';
$string['appraisal:student'] = 'Student name';
$string['appraisal:count'] = 'Appraisal #';
$string['appraisal:appraiser'] = 'Appraiser name';
$string['appraisal:comment'] = 'Comment';
$string['appraisal:context'] = 'Context';
$string['appraisal:modificationdate'] = 'Modified';
$string['appraisalcriterion:grade'] = 'Final Grade';
$string['assess'] = 'Assess "{$a}"';
$string['assessment'] = 'Assessment';
$string['cachedef_appraisals'] = 'Cache for appraisal retrieval';
$string['context'] = 'Context';
$string['cleanup'] = 'Cleanup all CompetVetEval Data';
$string['cleanup:confirm'] = 'This will cleanup all table for CompetVetEval, like situations list but also all apraisal done so far
and data will not be recoverable. Are you sure ?';
$string['contextformat'] = 'Context Format';
$string['comment'] = 'Comment';
$string['commentformat'] = 'Comment Format';
$string['comments'] = 'All comments';
$string['cvetevalappservicename'] = 'CompetVetEval Application Service';
$string['settings:manage_entities'] = 'Manage entities';
$string['settings:manage_situations'] = 'Manage situations';
$string['settings:manage_evaluation_templates'] = 'Manage evaluation templates';
$string['settings:grade_scale'] = 'Grade Scales';
$string['settings:general'] = 'Paramètres généraux';
$string['criterion:label'] = 'Label';
$string['criterion:comment'] = 'Criterion comment';
$string['csvdelimiter'] = 'CSV Delimiter';
$string['description'] = 'Description';
$string['descriptionformat'] = 'Description Format';
$string['delete'] = 'Delete';
$string['encoding'] = 'Encoding';
$string['edit'] = 'Edit';
$string['enablecompetveteval'] = 'Enable cveteval';
$string['evaluation_gridfile'] = 'Evaluation grid';
$string['evaluation_gridfile_help'] = '(Optional)A list of evaluation criteria in CSV format. The file must contain the following headers
<pre>"Evaluation Grid Id";"Criterion Id";"Criterion Parent Id";"Criterion Label"</pre>';
$string['evaluation_grid:entity'] = 'Evaluation Grid';
$string['evaluation_grid:plural'] = 'Evaluation Grids';
$string['evaluation_grid:name'] = 'Evaluation Name';
$string['evaluation_grid:idnumber'] = 'Evaluation Identifier';
$string['evaluation_template:version'] = 'Evaluation Version';
$string['evaluation:date'] = "Evaluation Date";
$string['evaluation:comment'] = "Comment";
$string['evaluation:assessor'] = "Assessor";
$string['evaluation:hasgrade'] = 'Has grade?';
$string['evaluation:hasgrade'] = 'Has grade?';
$string['evaluation:grade'] = 'Grade';
$string['evaluationgrid:default'] = 'Default Evaluation Grid';
$string['fullname'] = 'Full Name';
$string['groupingfile'] = 'List of student grouping';
$string['groupingfile_help'] = 'A list of grouping in CSV format. The file must contain the following headers
<pre>"Nom de l\'étudiant";"Prénom";"Identifiant";"Groupement 1"</pre>';
$string['grade:defaultscale'] = 'Default scale (CompetvetEval)';
$string['grade:defaultscale:description'] = 'Default scale for CompetvetEval';
$string['grade:value'] = 'Grade: {$a}';
$string['grades:export'] = 'Export Grades';
$string['idnumber'] = 'ID Number';
$string['id'] = 'Id';
$string['import:dateformat'] = 'd/m/Y';
$string['import'] = 'Import';
$string['headingfile'] = 'Import files';
$string['headingfile_help'] = 'You can import situation, plannning and groups here. All files except the evaluation grid
 are manadatory here .<br>
Beware all files must match. For example:
<ul>
<li>(Situation) - GrilleEval => (Grille Evaluation) - Evaluation Grid Id</li>
<li>(Planning) - Groupe XXX => (Groupes) - Un des groupes dans les colonnes de Groupement</li>
<li>(Planning) - Situation (nom court) => (Situation) - Nom court</li>
</ul>
Any mismatch in the files can lead to malfunctionning applications.';
$string['import:heading:process'] = 'Process files';
$string['import:heading:parameters'] = 'Importation parameters';
$string['import:importviacron'] = 'Import via CRON';
$string['import:cleanupbefore'] = 'Vider les tables avant...';
$string['import:start'] = 'Start import';
$string['import:planning'] = 'Planning';
$string['import:situation'] = 'Situation';
$string['import:grouping'] = 'Grouping';
$string['import:evaluation_grid'] = 'Evaluation grid';
$string['import:importing'] = 'Importing {$a}';
$string['import:imported'] = 'Imported {$a->rowcount}/{$a->totalrows}';
$string['import:logs'] = 'Import logs';
$string['list'] = 'List';
$string['cveteval:cleanupdata'] = 'Cleanup data';
$string['cveteval:managesituations'] = 'Manage situations';
$string['cveteval:manageevaluationtemplate'] = 'Manage evaluations templates';
$string['cveteval:managecriteriontemplate'] = 'Manage criterion templates';
$string['cveteval:viewfiles'] = 'View files';
$string['cveteval:viewallcriteria'] = 'View all criteria';
$string['cveteval:viewallsituations'] = 'View all situations';
$string['cveteval:import'] = 'Import';
$string['cveteval:mobileaccess'] = 'Mobile Access';
$string['mystudents'] = 'My students';
$string['mysituations'] = 'My situations';
$string['mysituations:intro'] = 'This is the list of situations you should be able to evaluate. Please
click on a row to see a list of appraisals / students to assess/evaluate.';
$string['name'] = 'Full Name';
$string['planningfile'] = 'List of planning';
$string['planningfile_help'] = 'A list of planning in CSV format. The file must contain the following headers
<pre>"Date début";"Date fin";"Groupe XX";"Groupe YY"</pre>';
$string['planning:entity'] = 'Evaluation Planning';
$string['planning:plural'] = 'Evaluations Planning';
$string['planning:title'] = 'Title';
$string['planning:groupid'] = 'Group ID';
$string['planning:groupname'] = 'Group Name';
$string['planning:starttime'] = 'Start time';
$string['planning:endtime'] = 'End time';
$string['planning:studentid'] = 'Student';
$string['planning:clsituationid'] = 'Clinical situation';
$string['planning:requiredappraisals'] = 'Required Appraisal #';
$string['pluginname'] = 'CompetVet Eval plugin';
$string['otherstudents'] = 'Other students';
$string['allsituations'] = 'All situations';
$string['role:entity'] = 'Roles';
$string['saved'] = 'Saved';
$string['student'] = 'Student';
$string['situation:entity'] = 'Clinical situation';
$string['situation:plural'] = 'Clinical situations';
$string['situation:title'] = 'Title';
$string['situation:expectedevalsnb'] = '#Eval';
$string['situation:evalgridid'] = 'Linked Evaluation Grid';
$string['situation:groupname'] = 'Group name';
$string['situationfile'] = 'List of situations';
$string['situationfile_help'] = 'A list of situation in CSV format. The file must contain the following headers
<pre>"Description";"Nom";"Nom court";"ResponsableUE";"Responsable";"Evaluateurs";"Observateurs";"Appreciations";
"GrilleEval";"Etiquettes"</pre>';
$string['title'] = 'Title';
$string['thissituation'] = 'This situation';
$string['view'] = 'View';

