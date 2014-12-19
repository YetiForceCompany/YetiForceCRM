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
$permitted_modules = array('all');

/// ZNACZNIK WYWOLUJACY TE FUNKCJE => #special_function#example#end_special_function#
/// funkcja MUSI miec taką samą nazwe jak PLIK

function example($module, $id, $templateid, $content, $tcpdf) {
	
	$db = PearDatabase::getInstance();
	/// przykladowe zapytanie
	$sql = $db->query("select accountname from vtiger_account LIMIT 5", true);
	
	//poczatek tabeli
	$content = '<br/><table border="1">';
	// dla pobranych zapytaniem rekordów - tworz kolejne wiersze
	for ($i = 0; $i < $db->num_rows($sql); $i++) {
		$content .= '<tr><td align="center"> Nazwa </td><td>' . $db->query_result($sql, $i, "accountname") . '</td></tr>';
	}
	//koniec tabeli
	$content .= '</table><br/>';
	//zwracamy wynik
	return $content;
}
