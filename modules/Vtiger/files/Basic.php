<?php
/**
 * Basic class to handle files.
 *
 * @package Files
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Basic class to handle files.
 */
abstract class Vtiger_Basic_File
{
	/**
	 * Storage name.
	 *
	 * @var string
	 */
	public $storageName = '';
	/**
	 * File type.
	 *
	 * @var string
	 */
	public $fileType = '';

	/**
	 * Checking permission in get method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function getCheckPermission(App\Request $request)
	{
		if (!$request->isEmpty('record')) {
			$moduleName = $request->getModule();
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record')) || !\App\Field::getFieldPermission($moduleName, $request->getByType('field', 2))) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		return true;
	}

	/**
	 * Checking permission in post method.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function postCheckPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$field = $request->getByType('field', 'Alnum');
		if (!$request->isEmpty('record', true)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			if (!$recordModel->isEditable() || !\App\Field::getFieldPermission($moduleName, $field, false)) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		} else {
			if (!\App\Field::getFieldPermission($moduleName, $field, false) || !\App\Privilege::isPermitted($moduleName, 'CreateView')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		return true;
	}

	/**
	 * Get and save files.
	 *
	 * @param \App\Request $request
	 */
	public function post(App\Request $request)
	{
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * Function to validate request method.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function validateRequest(App\Request $request)
	{
		if (\App\Config::security('csrfActive')) {
			\CsrfMagic\Csrf::check();
		}
	}
}
