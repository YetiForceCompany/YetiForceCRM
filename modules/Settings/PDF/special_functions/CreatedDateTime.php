<?php
/**
 * Special function displaying creation date time of the record
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
$permittedModules = ['all'];

if (!function_exists('CreatedDateTime')) {

	function CreatedDateTime($module, $id) // unused -> , $templateid, $content, $tcpdf)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT `createdtime` FROM `vtiger_crmentity` WHERE `crmid` = ? LIMIT 1;';
		$result = $db->pquery($query, [$id]);
		$createdTime = $db->getSingleValue($result);

		$current_user = vglobal('current_user');
		$dat_fmt = $current_user->date_format;
		if ($dat_fmt == '') {
			$dat_fmt = 'yyyy-mm-dd';
		}
		$date = new DateTimeField($createdTime);

		return $date->getDisplayDateTimeValue();
	}
}
