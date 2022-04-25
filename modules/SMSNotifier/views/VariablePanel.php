<?php

/**
 * Variable panel view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class SMSNotifier_VariablePanel_View extends Vtiger_VariablePanel_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('record') && !\App\Privilege::isPermitted($moduleName, 'EditView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->isEmpty('sourceRecord') || !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$selectedModule = $request->getByType('selectedModule', \App\Purifier::STANDARD);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		if ($request->isEmpty('selectedModule')) {
			$viewer->assign('SELECTED_MODULE', '');
		} else {
			$viewer->assign('SELECTED_MODULE', $request->getByType('selectedModule', 2));
		}
		$viewer->assign('TEXT_PARSER', \App\TextParser::getInstance($selectedModule)->setType('sms'));
		$viewer->assign('PARSER_TYPE', $request->getByType('type', 1));
		$viewer->assign('RELATED_LISTS', []);
		$viewer->assign('BASE_LISTS', []);
		$viewer->view('VariablePanel.tpl', $moduleName);
	}
}
