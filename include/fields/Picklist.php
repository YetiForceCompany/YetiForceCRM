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
			$dataReader = (new \App\Db\Query())->select($fieldName)
					->from("vtiger_$fieldName")
					->innerJoin('vtiger_role2picklist', "vtiger_role2picklist.picklistvalueid = vtiger_$fieldName.picklist_valueid")
					->innerJoin('vtiger_picklist', 'vtiger_picklist.picklistid = vtiger_role2picklist.picklistid')
					->where(['roleid' => $roleid])
					->orderBy('sortid')
					->createCommand()->query();
			$fldVal = [];
			while (($val = $dataReader->readColumn(0)) !== false) {
				$fldVal[] = decode_html($val);
			}
			\Vtiger_Cache::set('includes\fields\Owner', $cacheKey, $fldVal);
		}
		return $fldVal;
	}
}
