<?php
/**
 * Example of special function
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
$permitted_modules = array('all');

/// Variable calling a function => #special_function#example#end_special_function#
/// function MUST have the same name as FILE

function example($module, $id, $templateid, $content, $tcpdf)
{
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
