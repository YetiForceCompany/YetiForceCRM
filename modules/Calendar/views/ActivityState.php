<?php

/**
 * @package   YetiForce.View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_ActivityState_View extends Vtiger_BasicModal_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordInstance = Vtiger_Record_Model::getInstanceById(
			$request->getInteger('record'),
			$moduleName
		);
		$viewer = $this->getViewer($request);
		$viewer->assign('PERMISSION_TO_SENDE_MAIL',
			\App\Module::isModuleActive('OSSMail') && \App\Privilege::isPermitted('OSSMail')
		);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordInstance);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getScripts($request));
		$viewer->view('Extended/ActivityState.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getScripts(\App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'modules.' . $request->getModule() . '.resources.ActivityStateModal'
		]);
	}
}
