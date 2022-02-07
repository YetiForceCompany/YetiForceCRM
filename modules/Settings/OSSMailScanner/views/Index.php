<?php

/**
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_OSSMailScanner_Index_View extends Settings_Vtiger_Index_View
{
	private $prefixesForModules = ['Project', 'HelpDesk', 'SSalesProcesses', 'Campaigns'];

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~layouts/resources/libraries/jstree.checkbox.js',
		]));
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$mailModuleActive = \App\Module::getModuleId('OSSMail');
		$mailScannerRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$identityList = [];
		if ($mailModuleActive) {
			$accountsList = OSSMail_Record_Model::getAccountsList(false, false, false, false);
			foreach ($accountsList as $account) {
				$identityList[$account['user_id']] = OSSMailScanner_Record_Model::getIdentities($account['user_id']);
			}
		}
		$actionsList = OSSMailScanner_Record_Model::getActionsList();
		$ConfigFolderList = OSSMailScanner_Record_Model::getConfigFolderList();
		$emailSearch = OSSMailScanner_Record_Model::getEmailSearch(false, false);
		$emailSearchList = OSSMailScanner_Record_Model::getEmailSearchList();
		$widgetCfg = OSSMailScanner_Record_Model::getConfig(false);
		$supportedModules = Settings_RecordNumbering_Module_Model::getSupportedModules();
		foreach ($supportedModules as $supportedModule) {
			if (\in_array($supportedModule->name, $this->prefixesForModules)) {
				$numbering[$supportedModule->name] = \App\Fields\RecordNumber::getInstance($supportedModule->name);
			}
		}
		$checkCron = OSSMailScanner_Record_Model::getCron();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $mailScannerRecordModel);
		$viewer->assign('ACCOUNTS_LIST', $accountsList);
		$viewer->assign('ACTIONS_LIST', $actionsList);
		$viewer->assign('CONFIGFOLDERLIST', $ConfigFolderList);
		$viewer->assign('WIDGET_CFG', $widgetCfg);
		$viewer->assign('EMAILSEARCH', $emailSearch);
		$viewer->assign('EMAILSEARCHLIST', $emailSearchList);
		$viewer->assign('RECORDNUMBERING', $numbering ?? []);
		$viewer->assign('ERRORNOMODULE', !$mailModuleActive);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IDENTITYLIST', $identityList);
		$viewer->assign('CHECKCRON', $checkCron);
		echo $viewer->view('Index.tpl', $request->getModule(false), true);
	}
}
