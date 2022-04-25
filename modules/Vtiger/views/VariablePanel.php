<?php

/**
 * Variable panel view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_VariablePanel_View extends \App\Controller\View\Page
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
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
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->isEmpty('selectedModule') ? '' : $request->getByType('selectedModule', \App\Purifier::ALNUM);
		$type = $request->getByType('type');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SELECTED_MODULE', $sourceModule);
		$viewer->assign('TEXT_PARSER', \App\TextParser::getInstance($sourceModule)->setType($type));
		$viewer->assign('PARSE_TYPE', $type);
		$viewer->view('VariablePanel.tpl', $moduleName);
	}
}
