<?php

/**
 * ActivityStateModal view Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_ActivityStateModal_View extends Vtiger_BasicModal_View
{
	/**
	 * Get tpl path file.
	 *
	 * @return string
	 */
	protected function getTpl()
	{
		return 'ActivityStateModal.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true) || !\App\Privilege::isPermitted($request->getModule(), 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('PERMISSION_TO_SENDE_MAIL', \App\Privilege::isPermitted('OSSMail'));
		$viewer->assign('RECORD', Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName));
		$viewer->assign('SCRIPTS', $this->getScripts($request));
		$viewer->view($this->getTpl(), $moduleName);
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
