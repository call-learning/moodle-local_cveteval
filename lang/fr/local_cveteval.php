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
$string['appraiser'] = 'Observateur';
$string['actions'] = 'Actions';
$string['add'] = 'Ajouter';
$string['appraisal:student'] = 'Nom de l\'étudiant';
$string['appraisal:count'] = 'Observation #';
$string['appraisal:appraiser'] = 'Nom de l\'observateur';
$string['appraisal:comment'] = 'Commentaire';
$string['appraisal:context'] = 'Contexte';
$string['appraisal:modificationdate'] = 'Modifié';
$string['appraisalcriterion:grade'] = 'Note finale';
$string['assess'] = 'Evaluation de "{$a}"';
$string['assessment'] = 'Evaluation';
$string['cachedef_appraisals'] = 'Cache pour la récupération d\'observations';
$string['context'] = 'Contexte';
$string['contextformat'] = 'Format du texte';
$string['comment'] = 'Commentaire';
$string['comments'] = 'Tous les commentaires';
$string['commentformat'] = 'Format du text';
$string['cvetevalappservicename'] = 'CompetVetEval Application Service';
$string['settings:manage_situations'] = 'Gère les situations';
$string['settings:manage_evaluation_templates'] = 'Gère les modèles d\'évaluation';
$string['settings:grade_scale'] = 'Echelle de notes';
$string['settings:general'] = 'General settings';
$string['criterion:label'] = 'Libéllé';
$string['criterion:comment'] = 'Commentaire sur le critère';
$string['csvdelimiter'] = 'CSV Delimiter';
$string['description'] = 'Description';
$string['descriptionformat'] = 'Format de la description';
$string['delete'] = 'Effacer';
$string['edit'] = 'Editer';
$string['evaluation_gridfile'] = 'Critères de la grille d\'évaluation';
$string['evaluation_gridfile_help'] =  '(Optional)Une liste de critères d\'évaluation en format CSV. 
Le fichier doit contenir les entêtes suivants:
<pre>"Evaluation Grid Id";"Criterion Id";"Criterion Parent Id";"Criterion Label"</pre>';
$string['enablecompetveteval'] = 'Activer CompetVetEval';
$string['evaluation_grid:entity'] = 'Grille d\'évaluation';
$string['evaluation_grid:plural'] = 'Grilles d\'évaluation';
$string['evaluation_grid:name'] = 'Nom de l\'évaluation';
$string['evaluation_grid:idnumber'] = 'Identifiant de l\'évaluation';
$string['evaluation_template:version'] = 'Version de l\'évaluation';
$string['evaluation:date'] = "Date";
$string['evaluation:comment'] = "Commentaire";
$string['evaluation:assessor'] = "Evaluateur final";
$string['evaluation:hasgrade'] = 'Note?';
$string['evaluation:grade'] = 'Note';
$string['evaluationgrid:default'] = 'Grille d\'évaluation par défaut';
$string['encoding'] = 'Encoding';
$string['fullname'] = 'Nom complet';
$string['groupingfile'] = 'Liste de groupe d\'étudiants';
$string['groupingfile_help'] = 'Une liste de groupes en format CSV. Le fichier doit contenir les entêtes suivants:
<pre>"Nom de l\'étudiant";"Prénom";"Identifiant";"Groupement 1"</pre>';
$string['grade:defaultscale'] = 'Echelle de note par défault (CompetvetEval)';
$string['grade:defaultscale:description'] = 'Echelle de note par défault pour CompetvetEval';
$string['idnumber'] = 'Identifiant';
$string['id'] = 'Identifiant';
$string['import'] = 'Importation';
$string['headingfile'] = 'Importer les fichiers';
$string['headingfile_help']= 'Vous pouvez importer des situation, planning and groups ici. Les fichiers de situation, planning et groupes 
sont obligatoires, le fichier de critères d\'évaluation.<br>
Attention les fichiers doivent bien correspondre les uns avec les autres. Par exemple:
<ul>
<li>(Situation) - GrilleEval => (Grille Evaluation) - Evaluation Grid Id</li>
<li>(Planning) - Groupe XXX => (Groupes) - Un des groupes dans les colonnes de Groupement</li>
<li>(Planning) - Situation (nom court) => (Situation) - Nom court</li>
</ul>
Des problèmes de correspondance peuvent se traduirent plus tard par des dysfonctionnements. 
';
$string['import:heading:process'] = 'Importation des fichiers';
$string['import:heading:parameters'] = 'Paramètres importation';
$string['import:importviacron'] = 'Importer par CRON';
$string['import:cleanupbefore'] = 'Vider les tables avant...';
$string['import:start'] = 'Démarrer l\'import';
$string['import:planning'] = 'Planning';
$string['import:situation'] = 'Situation';
$string['import:grouping'] = 'Grouping';
$string['import:evaluation_grid'] = 'Grille d\'évaluation';
$string['import:importing'] = 'Importation de {$a}';
$string['import:imported'] = 'Importation {$a->rowcount}/{$a->totalrows}';
$string['import:logs'] = 'Logs d\'importation';
$string['list'] = 'Liste';
$string['cveteval:managesituations'] = 'Gère les situations';
$string['cveteval:manageevaluationtemplate'] = 'Gère les modèles d\'évaluation';
$string['cveteval:managecriteriontemplate'] = 'Gère les modèles de critères';
$string['cveteval:viewfiles'] = 'Voir les fichiers';
$string['cveteval:viewallcriteria'] = 'Voir tous les criteria';
$string['cveteval:viewallsituations'] = 'Voir toutes les situations';
$string['cveteval:import'] = 'Importation';
$string['cveteval:mobileaccess'] = 'Accès au API mobiles';
$string['import:dateformat'] = 'd/m/Y';
$string['mystudents'] = 'Mes étudiants';
$string['mysituations'] = 'Mes situations';
$string['mysituations:intro'] = 'Ceci est la liste des situation que vous êtes en mesure d\'évaluer.
Cliquez sur une ligne pour voir la liste des observations/étudiant students à évaluer.';
$string['name'] = 'Nom complet';
$string['planningfile'] = 'Liste des plannings';
$string['planningfile_help'] = 'Une liste de planning en format CSV. Le fichier doit contenir les entêtes suivants:
<pre>"Date début";"Date fin";"Groupe XX";"Groupe YY"</pre>';
$string['planning:entity'] = 'Planning des évaluations';
$string['planning:plural'] = 'Plannings des évaluations';
$string['planning:title'] = 'Titre';
$string['planning:groupid'] = 'Identifiant de groupe';
$string['planning:groupname'] = 'Nom de groupe';
$string['planning:starttime'] = 'Date début';
$string['planning:endtime'] = 'Date fin';
$string['planning:studentid'] = 'Identifiant étudiant';
$string['planning:clsituationid'] = 'Situation clinique';
$string['planning:requiredappraisals'] = 'Nombre évaluation requis #';
$string['pluginname'] = 'Plugin CompetVetEval';
$string['otherstudents'] = 'Autres étudiants';
$string['allsituations'] = 'Toutes les situations';
$string['saved'] = 'Sauvegardé';
$string['student'] = 'Etudiant';
$string['situation:entity'] = 'Situation clinique';
$string['situation:plural'] = 'Situations cliniques';
$string['situation:title'] = 'Titre';
$string['situation:expectedevalsnb'] = '#Nb Evaluations';
$string['situation:evalgridid'] = 'Grille d\'évaluation liée';
$string['situation:groupname'] = 'Nom du groupe';
$string['situationfile'] = 'Liste des situations';
$string['situationfile_help'] = 'Une liste de situations en format CSV. Le fichier doit contenir les entêtes suivants:
<pre>"Description";"Nom";"Nom court";"ResponsableUE";"Responsable";"Evaluateurs";"Observateurs";"Appreciations";
"GrilleEval";"Etiquettes"</pre>';
$string['title'] = 'Titre';
$string['thissituation'] = 'Cette situation';
$string['view'] = 'Voir';
