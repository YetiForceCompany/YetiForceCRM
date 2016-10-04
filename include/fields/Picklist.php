<?php namespace includes\fields;

/**
 * Picklist class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Picklist
{

	/**
	 * Function to get role based picklist values
	 * @param <String> $fieldName
	 * @param <String> $roleId
	 * @return <Array> list of role based picklist values
	 */
	public static function getRoleBasedPicklistValues($fieldName, $roleid)
	{
		$cacheKey = $fieldName . $roleid;
		$fldVal = \Vtiger_Cache::get('includes\fields\Picklist', $cacheKey);
		if ($fldVal === false) {
			$db = \PearDatabase::getInstance();
			$fldVal = [];
			$query = "SELECT $fieldName FROM vtiger_$fieldName
				INNER JOIN vtiger_role2picklist ON vtiger_role2picklist.picklistvalueid = vtiger_$fieldName.picklist_valueid 
				INNER JOIN vtiger_picklist ON vtiger_picklist.`picklistid` = vtiger_role2picklist.`picklistid`
				WHERE roleid = ? ORDER BY sortid;";
			$result = $db->pquery($query, [$roleid]);
			while (($val = $db->getSingleValue($result)) !== false) {
				$fldVal[] = decode_html($val);
			}
			\Vtiger_Cache::set('includes\fields\Owner', $cacheKey, $fldVal);
		}
		return $fldVal;
	}
}
