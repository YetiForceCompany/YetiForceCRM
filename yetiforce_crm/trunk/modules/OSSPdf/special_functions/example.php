<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
$permitted_modules = array( 'all' );
/// ZNACZNIK WYWOLUJACY TE FUNKCJE => #special_function#example#end_special_function#
/// funkcja MUSI miec taką samą nazwe jak PLIK
function example( $module, $id, $templateid ,$content, $tcpdf) {
{
        $db =  PearDatabase::getInstance();
	/// przykladowe zapytanie
	$zapytanie = $db->query( "select accountname from vtiger_account LIMIT 5", true );
	
	$tresc = '';
	//poczatek tabeli
	$tresc .= '<br/><table border="1">';
	// dla pobranych zapytaniem rekordów - tworz kolejne wiersze
	for( $i = 0; $i < $db->num_rows( $zapytanie ); $i++ )
	{
		$tresc .= '<tr><td align="center"> Nazwa </td><td>'.$db->query_result( $zapytanie, $i, "accountname" ).'</td></tr>';
	}
	//koniec tabeli
	$tresc .= '</table><br/>';
	//zwracamy wynik
	return $tresc;
}
?>