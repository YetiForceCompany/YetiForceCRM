<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): 
 *************************************************************************************************************************************/
$languageStrings = [
	'DataAccess' => 'Validation des accès',
	'LBL_NONE' => '--Aucun--',
	'Message' => 'Message',
	'Action_unique_value' => 'Valeur du champ doit être unique.',
	'Action_Desc_unique_value' => 'Vérifier que la valeur entrée est unique.',
	'Select the fields to be verified' => 'Sélectionner les champs à verifier',
	'Select a field from which the value is to be checked' => 'Sélectionner un champ dont la valeur doit être vérifiée',
	'Field value is not unique' => 'La valeur n\'est pas unique',
	'Please enter a unique value in the' => 'Merci de saisir une valeur unique dans :',
	'LBL_LOCKS_SAVE' => 'Sauvergarder le verrou',
	'LBL_LOCKS_SAVE_LABEL1' => 'Non',
	'LBL_LOCKS_SAVE_LABEL2' => 'Oui - Une condition remplie',
	'LBL_LOCKS_SAVE_LABEL3' => 'Oui - Deux conditions remplies',
	'LBL_LOCKS_SAVE_LABEL4' => 'Oui - Fenêtre modale',
	'LBL_VALIDATION_TWO_FIELDS' => 'Validation pour deux champs',
	'LBL_MESSAGE_LOCK0' => 'Message lorsque non vérouillé en écriture',
	'LBL_MESSAGE_LOCK1' => 'Message pour une condition remplie',
	'LBL_MESSAGE_LOCK2' => 'Message pour deux conditions remplies',
	//
	'Action_check_task' => 'Vérifier tâche',
	'Action_Desc_check_task' => 'Cette action vérifie que la tâche est reliée à un statut et un sujet correct. Si ce n\'est pas le cas, la saisie est bloquée.',
	'Select status' => 'Selectionner statut',
	'Subject tasks' => 'Sujet tâche',
	'Message if the task does not exist' => 'La tâche n\'existe pas.',
	//
	'Action_check_alltask' => 'Vérifier toutes les tâches',
	'Action_Desc_check_alltask' => 'Cette action vérifie que toutes les tâches de même statut sont reliées. La saisie est bloquée dans le cas contraire.',
	'Message if the task exist' => 'La tâche existe',
	//
	'Action_show_quick_create' => 'Creátion rapide',
	'Action_Desc_show_quick_create' => 'Cette action affiche la création rapide.',
	'LBL_SELECT_OPTION' => 'Sélectionner une option',
	//
	'Action_blockEditView' => 'Bloquer l\'édition',
	'Action_Desc_blockEditView' => 'Cette action bloque l\'édition (rapide et complète).',
	//
	'Action_check_taskdate' => 'Vérifier date prévue pour compléter la tâche',
	'Action_Desc_check_taskdate' => 'Valider la date prévue pour compléter la tâche.',
	'Date can not be greater' => 'La date planifiée ne peut être postérieure à la date étape',
	//
	'Action_unique_modules_value' => 'Valeur du champ est unique dans les modules',
	'Action_Desc_unique_modules_value' => 'Vérifier que la valeur du champ est unique dans les modules',
	'Check the value in the module' => 'Veifier la valeur dans le module',
	//
	'Action_check_taskstatus' => 'Vérifier toutes les tâches dépendantes',
	'Action_Desc_check_taskstatus' => 'Vérifier que toutes les tâches dépendantes ont été complétées.',
	'Subordinate tasks have not been completed yet' => 'Les tâches dépendantes ne sont pas encore terminées.',
	//
	'Action_validate_mandatory' => 'Vérifier les champs obligatoires',
	'Action_Desc_validate_mandatory' => 'Vérifier si tous les champs obligatoires en création rapide sont remplis.',
	//
	'Action_check_assigneduser' => 'Restriction sur le changement de propriétaire de l\'enregistrement',
	'Action_Desc_check_assigneduser' => 'Limite la possibilité de changer le propriétaire à une liste spécifique.',
	'LBL_SELECT_USER_OR_GROUP' => 'Utilisateurs ou groupes sélectionnés autorisés',
	'LBL_CURRENT_USER' => 'Utilisateur courant',
	//
	'Action_colorList' => 'Colorier les lignes dans la liste des enregistrements',
	'Action_Desc_colorList' => 'Cet outil permet de colorier les lignes qui remplissent les conditions correspondantes. Grâce à cela, les enregistrements avce une priorité haute sont identifiables.',
	'LBL_BACKGROUND_COLOR' => 'Couleur de fond',
	'LBL_TEXT_COLOR' => 'Couleur de texte',
	'This name already exists' => 'Ce nom existe déjà',
	'LBL_RECORD_DELETED' => 'Enregistrement supprimé',
	'Action_test' => 'Action test',
	'Action_Desc_test' => 'Description action test.',
	//
	'Action_check_day_tasks' => 'Vérifier le nombre d\'événement pour le jour sauvegardé',
	'Action_Desc_check_day_tasks' => 'Cette action vérifie le nombre d\´evénenements ce jour, et dans le cas où il excède la limite, le système bloque et informe l\'utilisateur.',
	'LBL_MAXIMUM_NUMBER_EVENTS_PER_DAY' => 'Nmbre d\'événements maximum par jour',
	//
	'Action_unique_account' => 'Vérifier les doublons dans les organisations',
	'Action_Desc_unique_account' => 'Veifier si l\'organisation est unique.',
	'LBL_DUPLICATED_FOUND' => 'Doublons trouvés',
	'LBL_DUPLICTAE_CREATION_CONFIRMATION' => 'Doublons trouvés. Etes-vous sûr de vouloir sauvegarder?',
	'LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION' => 'Une tentative de modification a été faite en mode création rapide.<br>Sélectionner les champs ci-dessous et accepter pour appliquer les changements.<br>Attention!<br>Resaisir les modifications dans l\'enregistrement.',
	'LBL_DONT_ASK_AGAIN' => 'Ne plus me demander pour cet enregistrement.',
	'LBL_SEARCH_TRASH' => 'Chercher dans la poubelle',
];
$jsLanguageStrings = [
	'DataAccess' => 'Accès aux données',
];
