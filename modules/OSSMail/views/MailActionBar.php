<?php

/**
 * Mail cction bar class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSMail_MailActionBar_View extends Vtiger_Index_View
{
	use App\Controller\ClearProcess;

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$uid = $request->getInteger('uid');
		$params = null;
		$account = OSSMail_Record_Model::getAccountByHash($request->getForSql('rcId'));
		if (!$account) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$rcId = $account['user_id'];
		$mailViewModel = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
		$folderDecode = \App\Utils::convertCharacterEncoding($request->getRaw('folder'), 'UTF7-IMAP', 'UTF-8');
		$folderDecode = \App\Purifier::purifyByType($folderDecode, 'Text');
		$folderDecode = \App\Purifier::decodeHtml($folderDecode);
		$record = $mailViewModel->checkMailExist($uid, $folderDecode, $rcId);
		$modelMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		if (!($record) && !empty($account['actions']) && strpos($account['actions'], 'CreatedEmail') !== false &&
			isset(array_column($modelMailScanner->getFolders($rcId), 'folder', 'folder')[$folderDecode])
		) {
			$folder = \App\Utils::convertCharacterEncoding($folderDecode, 'UTF-8', 'UTF7-IMAP');
			$mailModel = Vtiger_Record_Model::getCleanInstance('OSSMail');
			$mbox = \OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], $folder);
			$return = OSSMailScanner_Record_Model::executeActions($account, $mailModel->getMail($mbox, $uid), $folderDecode, $params);
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
