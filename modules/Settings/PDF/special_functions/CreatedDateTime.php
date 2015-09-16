<?php
/**
 * Special function displaying creation date time of the record
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
$permitted_modules = array('all');

function CreatedDateTime($module, $id, $templateid, $content, $tcpdf)
{
	$db = PearDatabase::getInstance();
	$query = $db->query("select createdtime from vtiger_crmentity WHERE crmid = '$id'");
	$createdtime = $db->query_result($query, 0, "createdtime");
	return date('Y/m/d H:i:s', strtotime($createdtime));
}
