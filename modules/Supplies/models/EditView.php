<?php

/**
 * Supplies DetailView Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_EditView_Model extends Vtiger_EditView_Model
{

	public function isWysiwygType($moduleName)
	{
		$cache = Vtiger_Cache::get('SuppliesisWysiwygType', $moduleName);
		if ($cache) {
			return $cache;
		}
		$return = 0;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fieldModel = Vtiger_Field_Model::getInstance('description', $moduleModel);
		if ($fieldModel && $fieldModel->get('uitype') == '300') {
			$return = 1;
		}
		Vtiger_Cache::set('SuppliesisWysiwygType', $moduleName, $return);
		return $return;
	}

	public function getTaxField($moduleName)
	{
		$cache = Vtiger_Cache::get('SuppliesisGetTaxField', $moduleName);
		if ($cache) {
			return $cache;
		}
		$return = false;
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		foreach ($moduleModel->getFields() as $fieldName => $fieldModel) {
			if ($fieldModel->get('uitype') == 303) {
				$return = $fieldName;
				continue;
			}
		}

		Vtiger_Cache::set('SuppliesisGetTaxField', $moduleName, $return);
		return $return;
	}
}
