<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Settings_OSSMailScanner_Index_View extends Settings_Vtiger_Index_View
{

	private $prefixesForModules = ['Contacts', 'Leads', 'Potentials', 'Project', 'HelpDesk', 'Accounts', 'Campaigns'];

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$OSSMail_active = Vtiger_Functions::getModuleId('OSSMail');
		if ($OSSMail_active) {
			$mailRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			$accountsList = $mailRecordModel->getAccountsList();
			$mailboxes = $mailRecordModel->get_default_mailboxes();
		}

		$mailScannerRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$identityList = [];
		if ($accountsList) {
			foreach ($accountsList as $key => $account) {
				$identityList[$account['user_id']] = $mailScannerRecordModel->getIdentities($account['user_id']);
				$mbox = $mailRecordModel->imapConnect($account['username'], $account['password'], $account['mail_host'], 'INBOX', false);
				$accountsList[$key]['status'] = $mbox ? 'LBL_ACTIVE_MAIL' : 'LBL_INACTIVE_MAIL';
			}
		}

		$EmailActionsList = $mailScannerRecordModel->getEmailActionsList();
		$EmailActionsListName = $mailScannerRecordModel->getEmailActionsListName($EmailActionsList);
		$ConfigFolderList = $mailScannerRecordModel->getConfigFolderList();
		$EmailSearch = $mailScannerRecordModel->getEmailSearch();
		$EmailSearchList = $mailScannerRecordModel->getEmailSearchList();
		$widgetCfg = $mailScannerRecordModel->getConfig(false);
		$supportedModules = Settings_Vtiger_CustomRecordNumberingModule_Model::getSupportedModules();

		foreach ($supportedModules as $supportedModule) {
			if (in_array($supportedModule->name, $this->prefixesForModules)) {
				$moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($supportedModule->name);
				$moduleData = $moduleModel->getModuleCustomNumberingData();
				$RecordNumbering[$supportedModule->name] = $moduleData;
			}
		}

		$check_cron = $mailScannerRecordModel->get_cron();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $mailScannerRecordModel);
		$viewer->assign('ACCOUNTLIST', $accountsList);
		$viewer->assign('EMAILACTIONSLIST', $EmailActionsList);
		$viewer->assign('EMAILACTIONSLISTNAME', $EmailActionsListName);
		$viewer->assign('FOLDERMAILBOXES', $mailboxes);
		$viewer->assign('CONFIGFOLDERLIST', $ConfigFolderList);
		$viewer->assign('WIDGET_CFG', $widgetCfg);
		$viewer->assign('EMAILSEARCH', $EmailSearch);
		$viewer->assign('EMAILSEARCHLIST', $EmailSearchList);
		$viewer->assign('RECORDNUMBERING', $RecordNumbering);
		$viewer->assign('ERRORNOMODULE', !$OSSMail_active);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('IDENTITYLIST', $identityList);
		$viewer->assign('CHECKCRON', $check_cron);
		echo $viewer->view('Index.tpl', $request->getModule(false), true);
	}
}
