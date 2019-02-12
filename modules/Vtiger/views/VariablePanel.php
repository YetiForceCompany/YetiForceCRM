<?php

/**
 * Variable panel view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_VariablePanel_View extends \App\Controller\View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('record') && !\App\Privilege::isPermitted($moduleName, 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		if ($request->isEmpty('selectedModule')) {
			$viewer->assign('SELECTED_MODULE', '');
		} else {
			$viewer->assign('SELECTED_MODULE', $request->getByType('selectedModule', 2));
		}
		$viewer->assign('PARSER_TYPE', $request->getByType('type', 1));
		$viewer->assign('GRAY', true);
		$viewer->view('VariablePanel.tpl', $moduleName);
	}
}
