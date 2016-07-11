<?php namespace includes\fields;

/**
 * Tools for email class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{

	public function findCrmidByPrefix($value, $moduleName)
	{
		$moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($moduleName);
		$moduleData = $moduleModel->getModuleCustomNumberingData();
		$redex = '/\[' . $moduleData['prefix'] . '([0-9]*)\]/';
		preg_match($redex, $value, $match);
		if (!empty($match)) {
			return $match[1];
		} else {
			return false;
		}
	}
}
