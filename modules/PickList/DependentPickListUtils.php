<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ******************************************************************************* */

require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/PickList/PickListUtils.php';

class Vtiger_DependencyPicklist
{
	/**
	 * Returns information about dependent picklists.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public static function getDependentPicklistFields($module = '')
	{
		// dorzudistinct(cić trzecią wartość ale jezeli jej nie uzywam?
		// jak przeniose to do s_yf_picklist_dependency to po prostu pobiorę
		$query = (new \App\Db\Query())->select(['id', 'tabid', 'source_field', 'second_field', 'third_field'])
			->from('s_yf_picklist_dependency');
		if (!empty($module)) {
			$query->where(['tabid' => \App\Module::getModuleId($module)]);
		}
		$dataReader = $query->distinct()->createCommand()->query();
		$dependentPicklists = [];
		while ($row = $dataReader->read()) {
			$sourceField = $row['source_field'];
			$targetField = $row['second_field'];
			$thirdField = $row['third_field'];

			$moduleModel = Vtiger_Module_Model::getInstance($row['tabid']);
			$sourceFieldModel = Vtiger_Field_Model::getInstance($sourceField, $moduleModel);
			$targetFieldModel = Vtiger_Field_Model::getInstance($targetField, $moduleModel);
			//	$thirdFieldModel = Vtiger_Field_Model::getInstance($row['thirdfield'], $moduleModel);
			if (!$sourceFieldModel || !$targetFieldModel) {
				//	continue;
			}
			$sourceFieldLabel = $sourceFieldModel->getFieldLabel();
			$targetFieldLabel = $targetFieldModel->getFieldLabel();
			$moduleName = $moduleModel->getName();
			$dependentPicklists[] = [
				'id' => $row['id'],
				'sourcefield' => $sourceField,
				'sourcefieldlabel' => \App\Language::translate($sourceFieldLabel, $moduleName),
				'targetfield' => $targetField,
				'targetfieldlabel' => \App\Language::translate($targetFieldLabel, $moduleName),
				'thirdField' => 'thirdfield',
				'module' => $moduleName,
			];
		}
		$dataReader->close();

		return $dependentPicklists;
	}

	public static function getJSPicklistDependencyDatasource($module)
	{
		$picklistDependencyDatasource = \App\Fields\Picklist::getPicklistDependencyDatasource($module);

		return \App\Json::encode($picklistDependencyDatasource);
	}
}
