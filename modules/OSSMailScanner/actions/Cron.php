<?php

/**
 * OSSMailScanner cron action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailScanner_Cron_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(App\Request $request)
	{
		$scanner = new \App\Mail\Scanner();
		$scanner->setLimit(\App\Mail::getConfig('scanner', 'limit'));
		$messages = 'ok';
		if ($scanner->isReady()) {
			$executeTime = time() + 30;
			$queryGenerator = (new \App\QueryGenerator('MailAccount'));
			$queryGenerator->setFields(['id'])->addCondition('mailaccount_status', \App\Mail\Account::STATUS_ACTIVE, 'e');
			$dataReader = $queryGenerator->createQuery()->createCommand()->query();

			while ($recordId = $dataReader->readColumn(0)) {
				$mailAccount = \App\Mail\Account::getInstanceById($recordId);
				$scanner->setAccount($mailAccount);
				$scanner->run(fn () => time() > $executeTime);
				if (time() > $executeTime) {
					break;
				}
			}
		} else {
			$messages = \App\Log::warning(\App\Language::translate('ERROR_ACTIVE_CRON', 'OSSMailScanner'));
		}

		$response = new Vtiger_Response();
		$response->setResult($messages);
		$response->emit();
	}
}
