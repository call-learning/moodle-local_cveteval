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
$string['add'] = 'Ajouter';
$string['allsituations'] = 'Toutes les situations';
$string['appraisal:appraiser'] = 'Nom de l\'observateur';
$string['appraisal:comment'] = 'Commentaire';
$string['appraisal:comment:header'] = 'Commentaires généraux observation';
$string['appraisal:context'] = 'Contexte';
$string['appraisal:count'] = 'Observation #';
$string['appraisalcriterion:grade'] = 'Note finale';
$string['appraisal:entity'] = 'Observation';
$string['appraisal:grade'] = 'Grade';
$string['appraisal:modificationdate'] = 'Modifié';
$string['appraisal:planning'] = 'Evaluation plan';
$string['appraisal:student'] = 'Nom de l\'étudiant';
$string['appraiser'] = 'Appraiser';
$string['assess'] = 'Evaluation de "{$a}"';
$string['assessment'] = 'Evaluation';
$string['cachedef_appraisals'] = 'Cache pour la récupération d\'observations';
$string['cleanup:confirm:model'] =
        'Cette opération va vider toutes les tables de CompetVetEval, les situations mais aussi les observations faites jusqu\'à présent. Les données ne sont pas récupérables. Etes vous sûr ?';
$string['cleanup:confirm:userdata'] =
        'Cette opération va vider toutes les observations faites jusqu\'à présent. Les données ne sont pas récupérables. Etes vous sûr ?';
$string['cleanup:details'] = 'Effacer l\'importation {$a->idnumber} : {$a->comments}';
$string['cleanup:model'] = 'Effacer un modèle importé';
$string['cleanup:selectimportid'] = 'Selectionnez l\'importation a effacer';
$string['cleanup:userdata'] = 'Effacer les données utilisateurs d\'un modèle importé';
$string['comment'] = 'Commentaire';
$string['commentformat'] = 'Format du texte';
$string['comments'] = 'Tous les commentaires';
$string['context'] = 'Contexte';
$string['contextformat'] = 'Format du texte';
$string['criterion:comment'] = 'Commentaire (Critère)';
$string['criterion:entity'] = 'Critère';
$string['criterion:label'] = 'Libellé';
$string['criterion:comment:header'] = 'Commentaires critère';
$string['csvdelimiter'] = 'CSV Delimiter';
$string['cvetevalappservicename'] = 'CompetVetEval Application Service';
$string['cveteval:cleanupdata'] = 'Peut vider les données CompetVetEval';
$string['cveteval:datamigration'] = 'Peut migrer des données entre importations';
$string['cveteval:exportgrades'] = 'Peut exporter les notes';
$string['cveteval:import'] = 'Peut effectuer des importations';
$string['cveteval:manageentities'] = 'Peut gérer les entités';
$string['cveteval:managecriteriontemplate'] = 'Peut gérer les critères';
$string['cveteval:manageevaluationtemplate'] = 'Peut gérer les modèles d\'évaluation';
$string['cveteval:manageimport'] = 'Peut gérer les importations';
$string['cveteval:exportall'] = 'Peut exporter toutes les données';
$string['cveteval:mobileaccess'] = 'A accès au API mobiles';
$string['cveteval:viewallcriteria'] = 'Peut voir tous les criteria';
$string['cveteval:viewallsituations'] = 'Peut voir toutes les situations';
$string['cveteval:viewfiles'] = 'Peut voir les fichiers';
$string['datamigration'] = 'Migration des données';
$string['datamigration_help'] = 'Migration des données entre différents importations';
$string['datamigrationstep'] = 'Migration / {$a}';
$string['defaulthistoryidnumber'] = 'Import:{$a}';
$string['delete'] = 'Effacer';
$string['description'] = 'Description';
$string['descriptionformat'] = 'Format de la description';
$string['dmc:matchedentities'] = 'Entités présentes dans les deux modèles';
$string['dmc:matched'] = 'Matched';
$string['dmc:orphanedentities'] = 'Entités présentes seulement dans l\'ancien modèle';
$string['dmcstep:choosehistory'] = 'Choisir les modèles';
$string['dmcstep:diffmodelsmodifications'] = 'Model modifications';
$string['dmcstep:diffmodels'] = 'Show differences';
$string['dmcstep:init'] = 'Démarrage';
$string['dmcstep:final'] = 'Fin';
$string['dmcstep:userdatamigration'] = 'Migration des données utilisateur';
$string['dmc:unmatchedentities'] = 'Entités présentes seulement dans le nouveau modèle';
$string['dmc:unmatched'] = 'Unmatched';
$string['dmc:expired'] = 'La session contenant les données de migration a expiré. Vous devez recommencer le processus.';
$string['dmc:congratsmessage'] = 'La migration est terminée. Cliquez sur "Continuer" pour en redémarrer une autre.';
$string['dmc:welcomemessage'] =
        'Bienvenue à l\'outil de migration des données. Cela vous aidera à copier les données utilisateurs d\'un modèle (ou historique) vers un autre. Certaines données vont être automatiquement reconcilées, pour d\'autre vous devrer décider de l\'action à effectuer.';
$string['download:model'] = 'Télécharger le modèle';
$string['download:userdata'] = 'Télécharger les données utilisateur';
$string['edit'] = 'Editer';
$string['enablecompetveteval'] = 'Activer CompetVetEval';
$string['encoding'] = 'Encoding';
$string['evaluation:assessor'] = "Evaluateur final";
$string['evaluation:comment'] = "Commentaire";
$string['evaluation:date'] = "Date évaluation";
$string['evaluation:grade'] = 'Note';
$string['evaluationgrid:default'] = 'Grille d\'évaluation par défaut';
$string['evaluation_grid:entity'] = 'Grille d\'évaluation';
$string['evaluation_gridfile'] = 'Critères de la grille d\'évaluation';
$string['evaluation_gridfile_help'] =
        '(Optional)Une liste de critères d\'évaluation en format CSV. Le fichier doit contenir les entêtes suivants: <pre>"Evaluation Grid Id";"Criterion Id";"Criterion Parent Id";"Criterion Label"</pre>';
$string['evaluation_grid:idnumber'] = 'Identifiant de la grille d\'évaluation';
$string['evaluation_grid:name'] = 'Nom de la grille d\'évaluation';
$string['evaluation_grid:plural'] = 'Grilles d\'évaluation';
$string['evaluationgrid:stats'] = 'Critères importés: {$a->criterions}';
$string['evaluation:waiting'] = "En attente...";
$string['evaluation:hasgrade'] = 'Note?';
$string['evaluation_template:version'] = 'Version de l\'évaluation';
$string['export:dateformat'] = '%d/%m/%Y';
$string['final_evaluation:entity'] = 'Evaluation finale';
$string['fullname'] = 'Nom complet';
$string['grade:defaultscale:description'] = 'Echelle de note par défault pour CompetvetEval';
$string['grade:defaultscale'] = 'Echelle de note par défault (CompetvetEval)';
$string['grades:export'] = 'Exporter les notes';
$string['grade:value'] = 'Note: {$a}';
$string['group_assignment:entity'] = 'Affectation au groupe';
$string['group_assignment:group'] = 'Groupe';
$string['group_assignment:student'] = 'Etudiant';
$string['group:entity'] = 'Groupe';
$string['groupingfile_help'] =
        'Une liste de groupes en format CSV. Le fichier doit contenir les entêtes suivants: <pre>"Nom de l\'étudiant";"Prénom";"Identifiant";"Groupement 1"</pre>';
$string['groupingfile'] = 'Liste de groupe d\'étudiants';
$string['grouping:stats'] = 'Group importés : {$a->groups}, group assignés: {$a->groupassignments}';
$string['grouping:usernotfound'] = 'Grouping user not found {$a}';
$string['headingfile_help'] =
        'Vous pouvez importer des situation, planning and groups ici. Les fichiers de situation, planning et groupes sont obligatoires, le fichier de critères d\'évaluation.<br> Attention les fichiers doivent bien correspondre les uns avec les autres. Par exemple: <ul> <li>(Situation) - GrilleEval => (Grille Evaluation) - Evaluation Grid Id</li> <li>(Planning) - Groupe XXX => (Groupes) - Un des groupes dans les colonnes de Groupement</li> <li>(Planning) - Situation (nom court) => (Situation) - Nom court</li> </ul> Des problèmes de correspondance peuvent se traduirent plus tard par des dysfonctionnements.';
$string['headingfile'] = 'Importer les fichiers';
$string['headingfile_link'] = "http://pedagogie.vetagro-sup.fr/Pages/CompetVet/co/App_Moodle.html";
$string['history:tools'] = 'Outils';
$string['id'] = 'Identifiant';
$string['idnumber'] = 'Identifiant';
$string['import:cleanupbefore'] = 'Vider les tables avant...';
$string['import:cleanup'] = 'Nettoyer et recommencer';
$string['import:comment'] = 'Commentaires sur l\'importation';
$string['import:comment_help'] = 'Commentaires, utilisés en internes, non requis.';
$string['import:dateformat'] = 'd/m/Y';
$string['import:destimportid'] = 'Modèle destination';
$string['import:downloadfile'] = 'Télécharger {$a}';
$string['import:download'] = 'Téléchargement d\'importation précédentes';
$string['import:error:idnumberexists'] = 'L\'identifiant d\'importation existe déjà {$a}';
$string['import:evaluation_grid'] = 'Grille d\'évaluation';
$string['import:failed'] =
        'L\'importation a échoué, veuillez cliquer sur le bouton ci-dessous pour effacer les données erronées et recommencer.';
$string['import:grouping'] = 'Grouping';
$string['import:heading:parameters'] = 'Paramètres importation';
$string['import:heading:process'] = 'Importation des fichiers';
$string['import:idnumber_help'] = 'Identifiant Importation, doit être unique.';
$string['import:idnumber'] = 'Identifiant Importation';
$string['import'] = 'Importation';
$string['import:imported'] = 'Importation {$a->rowcount}/{$a->totalrows}';
$string['import:importing'] = 'Importation de {$a}';
$string['import:importviacron'] = 'Importer par CRON';
$string['import:listall'] = 'Liste des importations effectuées';
$string['import:list'] = 'Liste importation';
$string['import:logs'] = 'Logs d\'importation';
$string['import:new'] = 'Nouvelle importation';
$string['import:origindestmustdiffer'] = 'Le modèle d\'origine et de destination doivent être différents';
$string['import:originimportid'] = 'Modèle d\'origine';
$string['import:planning'] = 'Planning';
$string['import:selectimport'] = 'Sélectionne et continue';
$string['import:situation'] = 'Situation';
$string['import:start'] = 'Démarrer l\'import';
$string['list'] = 'Liste';
$string['log:fieldname'] = 'Colonne';
$string['log:importid'] = 'ID';
$string['log:information'] = 'Information';
$string['log:level:error'] = 'Erreur';
$string['log:level:info'] = 'Info';
$string['log:level'] = 'Sévérité';
$string['log:level:warning'] = 'Attention';
$string['log:linenumber'] = 'Ligne';
$string['log:origin'] = 'Fichier';
$string['mysituations:intro'] =
        'Ceci est la liste des situation que vous êtes en mesure d\'évaluer. Cliquez sur une ligne pour voir la liste des observations/étudiant students à évaluer.';
$string['mysituations'] = 'Mes situations';
$string['mystudents'] = 'Mes étudiants';
$string['name'] = 'Nom complet';
$string['otherstudents'] = 'Autres étudiants';
$string['planning:clsituationid'] = 'Situation clinique';
$string['planning:dateoverlaps'] =
        'Les dates se recouvrent : ligne ({$a->prevrowindex}) les dates {$a->previousstartdate} - {$a->previousenddate} correspondent à {$a->currentstartdate} - {$a->currentenddate}.';
$string['planning:endtime'] = 'Date fin';
$string['planning:entity'] = 'Planning des évaluations';
$string['planningfile_help'] =
        'Une liste de planning en format CSV. Le fichier doit contenir les entêtes suivants:<pre>"Date début";"Date fin";"Groupe XX";"Groupe YY"</pre>';
$string['planningfile'] = 'Liste des plannings';
$string['planning:exist'] = 'Planning exits {$a}';
$string['planning:groupdoesnotexist'] = 'Le groupe {$a} n\'existe pas.';
$string['planning:groupid'] = 'Identifiant de groupe';
$string['planning:groupname'] = 'Nom de groupe';
$string['planning:invalidstarttime'] = 'Format de date erroné pour la date de début ({$a})';
$string['planning:invalidendtime'] = 'Format de date erroné pour la date de fin ({$a})';
$string['planning:nogroupdefined'] = 'Pas de groupes définis dans le planning.';
$string['planning:plural'] = 'Plannings des évaluations';
$string['planning:requiredappraisals'] = 'Nombre évaluation requis #';
$string['planning:situationnotfound'] = 'Situation {$a} not found when importing planning.';
$string['planning:starttime'] = 'Date début';
$string['planning:stats'] = 'Plans importés: {$a->plannings}, évènement de planning : {$a->planningevents}';
$string['planning:studentid'] = 'Identifiant étudiant';
$string['planning:title'] = 'Titre';
$string['pluginname'] = 'Plugin CompetVetEval';
$string['reset:confirm'] = 'Confirmez que vous souhaitez que toutes les données soit réinitialisées ?';
$string['role:entity'] = 'Roles';
$string['role:userid'] = 'Utilisateur';
$string['role:clsituationid'] = 'Situation';
$string['role:type'] = 'Type de role';
$string['role:type:appraiser'] = 'Appraiser';
$string['role:type:assessor'] = 'Assessor';
$string['role:type:student'] = 'Student';
$string['saved'] = 'Sauvegardé';
$string['settings:general'] = 'General settings';
$string['settings:grade_scale'] = 'Echelle de notes';
$string['settings:manage_evaluation_templates'] = 'Gère les modèles d\'évaluation';
$string['settings:manage_situations'] = 'Gère les situations';
$string['situation:action'] = 'Actions';
$string['situation:entity'] = 'Situation clinique';
$string['situation:evalgridid'] = 'Grille d\'évaluation';
$string['situation:expectedevalsnb'] = '#Nb Evaluations';
$string['situation:description'] = 'Description';
$string['situation:title'] = 'Titre';
$string['situation:idnumber'] = 'ID';
$string['situationfile_help'] =
        'Une liste de situations en format CSV. Le fichier doit contenir les entêtes suivants: <pre>"Description";"Nom";"Nom court";"ResponsableUE";"Responsable";"Evaluateurs";"Observateurs";"Appreciations"; "GrilleEval";"Etiquettes"</pre>';
$string['situationfile'] = 'Liste des situations';
$string['situation:gridnotfound'] = 'Grille non trouvée {$a}.';
$string['situation:groupname'] = 'Nom du groupe';
$string['situation:plural'] = 'Situations cliniques';
$string['situation:stats'] = 'Roles: {$a->roles}, Situations : {$a->situations}';
$string['situation:usernotfound'] = 'Utilisateur {$a} non trouvé.';
$string['situation:multipleuserfound'] = 'Multiple user found {$a}';
$string['student'] = 'Etudiant';
$string['thissituation'] = 'Cette situation';
$string['title'] = 'Titre';
$string['userdatamigration:student'] = 'Etudiant';
$string['userdatamigration:criterion'] = 'Critère';
$string['userdatamigration:appraiser'] = 'Observateur';
$string['userdatamigration:assessor'] = 'Evaluateur';
$string['userdatamigration:grade'] = 'Note';
$string['userdatamigration:planning'] = 'Plan';
$string['userdatamigration:situation'] = 'Situation';
$string['view'] = 'Voir';
$string['userview'] = 'Vue utilisateur';
