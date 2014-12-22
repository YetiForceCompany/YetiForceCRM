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

/// Variable calling a function => #special_function#example#end_special_function#
/// function MUST have the same name as FILE

function example($module, $id, $templateid, $content, $tcpdf) {
	
	$db = PearDatabase::getInstance();
	/// Sample Query
	$sql = $db->query("select accountname from vtiger_account LIMIT 5", true);

	//Build data table
	$content = '<br/><table border="1">';
	for ($i = 0; $i < $db->num_rows($sql); $i++) {
		$content .= '<tr><td align="center"> Account Name </td><td>' . $db->query_result($sql, $i, "accountname") . '</td></tr>';
	}
	$content .= '</table><br/>';
	return $content;
}
