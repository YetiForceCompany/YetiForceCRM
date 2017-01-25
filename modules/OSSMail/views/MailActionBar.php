<?php

/**
 * Mail cction bar class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_MailActionBar_View extends Vtiger_Index_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		
	}

	public function postProcess(Vtiger_Request $request)
	{
		
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$uid = $request->get('uid');
		$folder = $request->get('folder');
		$rcId = $request->get('rcId');

		$account = OSSMail_Record_Model::getAccountByHash($rcId);
		if (!$account) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$rcId = $account['user_id'];
		$mailViewModel = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
		$record = $mailViewModel->checkMailExist($uid, $folder, $rcId);
		if (!$record && !empty($account['actions'])) {
			$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			$mbox = $mailModel->imapConnect($account['username'], $account['password'], $account['mail_host'], $folder);
			$return = OSSMailScanner_Record_Model::executeActions($account, $mailModel->getMail($mbox, $uid), $folder, $params);
			if (isset($return['CreatedEmail'])) {
				$record = $return['CreatedEmail'];
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		if ($record) {
			$reletedRecords = $mailViewModel->getReletedRecords($record);
			$viewer->assign('RELETED_RECORDS', $reletedRecords);
		}
		\App\ModuleHierarchy::getModulesByLevel();
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('URL', AppConfig::main('site_URL'));
		$viewer->view('MailActionBar.tpl', $moduleName);
	}
}
