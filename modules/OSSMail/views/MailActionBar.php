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
		$account = OSSMail_Record_Model::getAccountByHash($request->getForSql('rcId'));
		if (!$account || !\App\Record::isExists($account['crm_ma_id'], 'MailAccount') || !\App\Privilege::isPermitted('MailAccount', 'DetailView', $account['crm_ma_id'])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$mailAccount = \App\Mail\Account::getInstanceById($account['crm_ma_id']);
		try {
			$mailViewModel = OSSMailView_Record_Model::getCleanInstance('OSSMailView');
			$folderDecode = \App\Utils::convertCharacterEncoding($request->getRaw('folder'), 'UTF7-IMAP', 'UTF-8');
			$folderDecode = \App\Purifier::purifyByType($folderDecode, 'Text');
			$folderDecode = \App\Purifier::decodeHtml($folderDecode);
			$imap = $mailAccount->openImap();
			$message = $imap->getMessageByUid($folderDecode, $uid);
			$record = $message ? $message->getMailCrmIdByCid() : 0;
			if (!$record && \in_array('CreatedMail', $mailAccount->getActions()) && \in_array($folderDecode, $mailAccount->getFolders())) {
				$scanner = (new \App\Mail\Scanner())->setLimit(1);
				foreach ($mailAccount->getActions() as $action) {
					$scanner->getAction($action)->setAccount($mailAccount)->setMessage($message)->process();
				}
				$record = (int) $message->getProcessData('CreatedMail')['mailViewId'] ?? 0;
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
