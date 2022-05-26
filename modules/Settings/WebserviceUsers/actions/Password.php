<?php

/**
 * Password Action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <s.rembiesa@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Password Action class.
 */
class Settings_WebserviceUsers_Password_Action extends \Vtiger_Password_Action
{
	/** {@inheritdoc} */
	protected $fieldModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$recordModel = Settings_WebserviceUsers_Record_Model::getCleanInstance($request->getByType('typeApi', 'Alnum'));
		$this->fieldModel = $recordModel->getFieldInstanceByName($request->getByType('field', 2));
		if (!$this->fieldModel || \App\Encryption::getInstance(\App\Module::getModuleId($request->getModule()))->isRunning()) {
			throw new \App\Exceptions\NoPermitted('ERR_NO_PERMISSIONS_TO_FIELD', 406);
		}
	}
}
