<?php
/**
 * Variable panel view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Variable panel view class.
 */
class SMSTemplates_VariablePanel_View extends Vtiger_VariablePanel_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$selectedModule = $request->getByType('selectedModule', \App\Purifier::STANDARD);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SELECTED_MODULE', $selectedModule);
		$viewer->assign('TEXT_PARSER', \App\TextParser::getInstance($selectedModule)->setType('sms'));
		$viewer->assign('PARSER_TYPE', 'sms');
		$viewer->assign('RELATED_LISTS', []);
		$viewer->assign('BASE_LISTS', []);
		$viewer->view('VariablePanel.tpl', $moduleName);
	}
}
