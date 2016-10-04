<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
$languageStrings = [
	'DataAccess' => 'Zugriffsberechtigung',
	'LBL_NONE' => '--Keine--',
	'Message' => 'Nachricht',
	'Action_unique_value' => 'Feld Wert ist einzigartig.',
	'Action_Desc_unique_value' => 'Prüfen ob der Feld Wert einzigartig ist.',
	'Select the fields to be verified' => 'Die zu überprüfenden Felder auswählen',
	'Select a field from which the value is to be checked' => 'Feld auswählen, dessen Wert überprüft werden soll',
	'Field value is not unique' => 'Feldwert ist nicht einzigartig',
	'Please enter a unique value in the' => 'Bitte geben Sie einen einzigartigen Wert ein:',
	'LBL_LOCKS_SAVE' => 'Sperre speichern',
	'LBL_LOCKS_SAVE_LABEL1' => 'Nein',
	'LBL_LOCKS_SAVE_LABEL2' => 'Ja - Eine Bedingung erfüllt',
	'LBL_LOCKS_SAVE_LABEL3' => 'Ja - Zwei Bedingungen erfüllt',
	'LBL_LOCKS_SAVE_LABEL4' => 'Ja - Modales Fenster',
	'LBL_VALIDATION_TWO_FIELDS' => 'Zwei Felder bestätigen',
	'LBL_MESSAGE_LOCK0' => 'Nachricht, wenn Schrieben nicht gesperrt ist',
	'LBL_MESSAGE_LOCK1' => 'Nachricht für eine erfüllte Bedingung',
	'LBL_MESSAGE_LOCK2' => 'Nachricht für zwei erfüllte Bedingung',
	//
	'Action_check_task' => 'Aufgabe prüfen',
	'Action_Desc_check_task' => 'Diese Aktion überprüft, ob die Aufgabe in Beziehung mit dem entsprechenden Thema und Status steht. Wenn sie es nicht ist, wird der Eintritt blockiert.',
	'Select status' => 'Status wählen',
	'Subject tasks' => 'Aufgabenthema',
	'Message if the task does not exist' => 'Nachricht, wenn die Aufgabe nicht existiert',
	//
	'Action_check_alltask' => 'Alle Aufgaben prüfen',
	'Action_Desc_check_alltask' => 'Diese Aktion überprüft, ob alle Aufgaben mit dem gleichen Status zusammengehören. Eintrag ist gesperrt, wenn sie miteinander verbunden sind.',
	'Message if the task exist' => 'Nachricht, wenn die Aufgabe existiert',
	//
	'Action_show_quick_create' => 'Schnellerfassung',
	'Action_Desc_show_quick_create' => 'Diese Aktion zeigt die Schnellerfassung.',
	'LBL_SELECT_OPTION' => 'Option auswählen',
	//
	'Action_blockEditView' => 'Bearbeitungsansicht blockieren',
	'Action_Desc_blockEditView' => 'Diese Aktion blockiert Bearbeitungsansicht (Schnell und vollständiges Bearbeiten).',
	//
	'Action_check_taskdate' => 'Überprüfen Sie den geplanten Termin für den Abschluss der Projektaufgabe',
	'Action_Desc_check_taskdate' => 'Bestätigen Sie den geplanten Termin für den Abschluss der Projektaufgabe.',
	'Date can not be greater' => 'Geplanter Termin kann nicht größer als Bühne Datum sein',
	//
	'Action_unique_modules_value' => 'Wert des Feldes ist einzigartig in Modulen',
	'Action_Desc_unique_modules_value' => 'Überprüfen Sie, ob der Eingabe Wert des Feldes einzigartig in den Modulen ist',
	'Check the value in the module' => 'Überprüfen Sie den Wert in dem Modul',
	//
	'Action_check_taskstatus' => 'Überprüfen Sie alle untergeordneten Aufgaben',
	'Action_Desc_check_taskstatus' => 'Überprüfen Sie, ob alle untergeordneten Aufgaben abgeschlossen sind.',
	'Subordinate tasks have not been completed yet' => 'Untergeordnete Aufgaben sind noch nicht abgeschlossen.',
	//
	'Action_validate_mandatory' => 'Überprüfen Sie Pflichtfelder',
	'Action_Desc_validate_mandatory' => 'Prüfen, ob alle Pflichtfelder in der Schnellerfassung ausgefüllt sind.',
	//
	'Action_check_assigneduser' => 'Einschränkung Datensatz Benutzerwechsel',
	'Action_Desc_check_assigneduser' => 'Aktion beschränkt die Möglichkeit, Benutzer einer bestimmten Liste zu ändern.',
	'LBL_SELECT_USER_OR_GROUP' => 'Wählen Sie die erlaubten Benutzer oder Gruppen',
	'LBL_CURRENT_USER' => 'Aktueller Benutzer',
	//
	'Action_colorList' => 'Einfärben von Zeilen in Datensatz-Liste',
	'Action_Desc_colorList' => 'Diese Werkzeug färbt die Datensätze zeilenweise ein, die entsprechenden Bedingungen erfüllen. So sind Datensätze mit einer höheren Priorität leichter zu unterscheiden.',
	'LBL_BACKGROUND_COLOR' => 'Hintergrund Farbe',
	'LBL_TEXT_COLOR' => 'Text Farbe',
	'This name already exists' => 'Der Name existiert schon',
	'LBL_RECORD_DELETED' => 'Datensatz gelöscht',
	'Action_test' => 'Testaktion',
	'Action_Desc_test' => 'Testaktion Beschreibung.',
	//
	'Action_check_day_tasks' => 'Anzahl der Ereignisse des gesicherten Tages prüfen',
	'Action_Desc_check_day_tasks' => 'Diese Aktion prüft die Anzahl der Ereignisse für das Ereigniss Start-Datum, falls das Limit überschritten wurde blockt/informiert das System den Benutzer.',
	'LBL_MAXIMUM_NUMBER_EVENTS_PER_DAY' => 'Maximale Anzahl der Ereignisse pro Tag',
	'LBL_SELECT_OPTION_TO_SEARCH' => 'Option zur Suche von Ereignissen setzen',
	'LBL_SET_CUSTOM_CONDITIONS' => 'Kundeneigene Bedingungen setzen',
	'LBL_CURRENT_EVENTS' => 'Aktuelle Ereignisse',
	'LBL_PAST_EVENTS' => 'Vergangene Ereignisse',
	//
	'Action_unique_account' => 'Auf Dubletten (Organisationen) prüfen',
	'Action_Desc_unique_account' => 'Prüfe ob die Organisation einmalig ist im Modul.',
	'LBL_DUPLICATED_FOUND' => 'Dubletten gefunden',
	'LBL_DUPLICTAE_CREATION_CONFIRMATION' => 'Dubletten gefunden. Wollen Sie speichern?',
	'LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION' => 'Es wurde versucht im Schnellerfassungsmodus etwas zu ändern. <br>Wählen Sie die Änderungen aus, welche Sie übernehmen möchten.<br>Achtung!<br>Nimmt Änderungen in diesem Datensatz zurück.',
	'LBL_DONT_ASK_AGAIN' => 'Nicht nochmal für diesen Datensatz nachfragen.',
	'LBL_SEARCH_TRASH' => 'Papierkorb durchsuchen',
];
$jsLanguageStrings = [
	'DataAccess' => 'Datenzugriff',
];
