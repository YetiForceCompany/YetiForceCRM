<?php namespace includes;

/**
 * Record basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Record
{

	public static function getLabel($recordId)
	{
		$label = \Vtiger_Cache::get('includes\Record', $recordId);
		if ($label === false) {
			$adb = \PearDatabase::getInstance();

			$result = $adb->pquery('SELECT label FROM `vtiger_crmentity_label` WHERE crmid = ? LIMIT 1', $recordId);
			$label = $db->getSingleValue($result);
			\Vtiger_Cache::set('includes\fields\Owner', $recordId, $label);
		}
		return $label;
	}
}
