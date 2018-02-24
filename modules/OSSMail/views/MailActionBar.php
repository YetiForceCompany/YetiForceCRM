<?php

/**
 * Mail cction bar class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMail_MailActionBar_View extends Vtiger_Index_View
{
	use App\Controller\ClearProcess;

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$uid = $request->getInteger('uid');
		$folder = $request->get('folder');
		$params = null; // YTfixme - non existent

		$account = OSSMail_Record_Model::getAccountByHash($request->getForSql('rcId'));
		if (!$account) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$rcId = $account['user_id'];
		$mailViewModel = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
		$record = $mailViewModel->checkMailExist($uid, $folder, $rcId);
		if (!$record && !empty($account['actions'])) {
			$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			$mbox = \OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], $folder);
			$return = OSSMailScanner_Record_Model::executeActions($account, $mailModel->getMail($mbox, $uid), $folder, $params);
			if (!empty($return['CreatedEmail'])) {
				$record = $return['CreatedEmail']['mailViewId'];
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		if ($record) {
			$relatedRecords = $mailViewModel->getRelatedRecords($record);
			$viewer->assign('RELATED_RECORDS', $relatedRecords);
		}
		\App\ModuleHierarchy::getModulesByLevel();
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('URL', AppConfig::main('site_URL'));
		$viewer->view('MailActionBar.tpl', $moduleName);
	}
}
