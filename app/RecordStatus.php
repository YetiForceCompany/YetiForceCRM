<?php
/**
 * Record status service file.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Record status service class.
 */
class RecordStatus
{
	/**
	 * Activate of the record status mechanism.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public static function activate(string $moduleName, string $fieldName): bool
	{
		$field = (new \App\Db\Query())
			->from('vtiger_field')
			->where(['tabid' => Module::getModuleId($moduleName), 'fieldname' => $fieldName])
			->one();
		if (!$field) {
			return false;
		}
		if ($fieldModel = \Vtiger_Field_Model::getInstance($fieldName, \Vtiger_Module_Model::getInstance($moduleName))) {
			$fieldModel->set('fieldparams', \App\Json::encode(['isProcessStatusField' => true]));
			$fieldModel->save();
		}
		return $fieldModel ? true : false;
	}
}
