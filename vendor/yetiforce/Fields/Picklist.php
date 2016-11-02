<?php
namespace App\Fields;

/**
 * Picklist class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Picklist
{

	/**
	 * Function to get role based picklist values
	 * @param string $fieldName
	 * @param string $roleId
	 * @return array list of role based picklist values
	 */
	public static function getRoleBasedPicklistValues($fieldName, $roleId)
	{
		$cacheKey = $fieldName . $roleId;
		$fldVal = \App\Cache::get('getRoleBasedPicklistValues', $cacheKey);
		if ($fldVal === false) {
			$dataReader = (new \App\Db\Query())->select($fieldName)
					->from("vtiger_$fieldName")
					->innerJoin('vtiger_role2picklist', "vtiger_role2picklist.picklistvalueid = vtiger_$fieldName.picklist_valueid")
					->innerJoin('vtiger_picklist', 'vtiger_picklist.picklistid = vtiger_role2picklist.picklistid')
					->where(['roleid' => $roleId])
					->orderBy('sortid')
					->createCommand()->query();
			$fldVal = [];
			while (($val = $dataReader->readColumn(0)) !== false) {
				$fldVal[] = decode_html($val);
			}
			\App\Cache::save('getRoleBasedPicklistValues', $cacheKey, $fldVal);
		}
		return $fldVal;
	}
}
