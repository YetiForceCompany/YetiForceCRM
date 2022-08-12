<?php

/**
 * Mail cction bar class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSMail_MailActionBar_View extends Vtiger_Index_View
{
	use App\Controller\ClearProcess;

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$uid = $request->getInteger('uid');
		$params = null;
		$account = OSSMail_Record_Model::getAccountByHash($request->getForSql('rcId'));
		if (!$account) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (OSSMail_Record_Model::MAIL_BOX_STATUS_DISABLED == $account['crm_status']) {
			return;
		}

		$rcId = $account['user_id'];
		if (OSSMail_Record_Model::MAIL_BOX_STATUS_BLOCKED_TEMP == $account['crm_status'] || OSSMail_Record_Model::MAIL_BOX_STATUS_BLOCKED_PERM == $account['crm_status']) {
			OSSMail_Record_Model::setAccountUserData($rcId, ['crm_status' => OSSMail_Record_Model::MAIL_BOX_STATUS_ACTIVE]);
			$account['crm_status'] = OSSMail_Record_Model::MAIL_BOX_STATUS_ACTIVE;
		}
		try {
			$mailViewModel = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
			$folderDecode = \App\Utils::convertCharacterEncoding($request->getRaw('folder'), 'UTF7-IMAP', 'UTF-8');
			$folderDecode = \App\Purifier::purifyByType($folderDecode, 'Text');
			$folderDecode = \App\Purifier::decodeHtml($folderDecode);
			$modelMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$folder = \App\Utils::convertCharacterEncoding($folderDecode, 'UTF-8', 'UTF7-IMAP');
			$mbox = \OSSMail_Record_Model::imapConnect($account['username'], \App\Encryption::getInstance()->decrypt($account['password']), $account['mail_host'], $folder, true, [], $account);
			$record = $mailViewModel->checkMailExist($uid, $folderDecode, $rcId, $mbox);
			if (!($record) && !empty($account['actions']) && false !== strpos($account['actions'], 'CreatedEmail')
		&& isset(array_column($modelMailScanner->getFolders($rcId), 'folder', 'folder')[$folderDecode])
	) {
				if ($mail = OSSMail_Record_Model::getMail($mbox, $uid)) {
					$return = OSSMailScanner_Record_Model::executeActions($account, $mail, $folderDecode, $params);
					if (!empty($return['CreatedEmail'])) {
						$record = $return['CreatedEmail']['mailViewId'];
					}
				} else {
					App\Log::warning("Email not found. username: {$account['username']}, folder: $folder, uid: $uid ", __METHOD__);
				}
			} elseif ($record && !\App\Privilege::isPermitted('OSSMailView', 'DetailView', $record)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($record, $mailViewModel->getModule());
				$sharedOwner = $recordModel->isEmpty('shownerid') ? [] : explode(',', $recordModel->get('shownerid'));
				$sharedOwner[] = \App\User::getCurrentUserId();
				$recordModel->set('shownerid', implode(',', $sharedOwner))->save();
			}
			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $record);
			if ($record) {
				$relatedRecords = $mailViewModel->getRelatedRecords($record);
				$viewer->assign('RELATED_RECORDS', $relatedRecords);
			}
			\App\ModuleHierarchy::getModulesByLevel(0);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('URL', App\Config::main('site_URL'));
			$viewer->view('MailActionBar.tpl', $moduleName);
		} catch (\Throwable $e) {
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString(), 'OSSMail');
		}
	}
}
