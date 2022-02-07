<?php

/**
 * Companies delete action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Companies_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Block record delete if less than two defined.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if ((new \App\Db\Query())->from('s_#__companies')->count() < 2) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = ['success' => true];
		$recordModel = Settings_Companies_Record_Model::getInstance($request->getInteger('record'));
		if ($request->getBoolean('detailView') && $recordModel->delete()) {
			$result = Settings_Vtiger_Module_Model::getInstance($request->getModule(false))->getDefaultUrl();
		} elseif ($recordModel) {
			$result = ['success' => (bool) $recordModel->delete()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
