<?php
/**
 * Mail account verification cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Mail account verification cron class.
 */
class MailAccount_Verification_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$pauser = \App\Pauser::getInstance('MailAccountVerification');
		$lastId = (int) $pauser->getValue();
		$deactivationTime = \App\Mail::getConfig('scanner', 'deactivation_time');

		$queryGenerator = (new \App\QueryGenerator('MailAccount'))
			->setFields(['id'])
			->addCondition('mailaccount_status', \App\Mail\Account::STATUS_LOCKED, 'e');
		if ($lastId) {
			$queryGenerator->addCondition('id', $lastId, 'a');
		}
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$count = $dataReader->count();
		while ($recordId = $dataReader->readColumn(0)) {
			$pauser->setValue((string) $recordId);
			$mailAccount = \App\Mail\Account::getInstanceById($recordId);
			try {
				$mailbox = $mailAccount->openImap();
				if ($mailbox->isConnected()) {
					$mailAccount->unlock();
				}
			} catch (\Throwable $th) {
				$lastLogin = $mailAccount->getSource()->get('last_login') ?: $mailAccount->getSource()->get('createdtime');
				$hours = \App\Fields\DateTime::getDiff($lastLogin, date('Y-m-d H:i:s'), 'hours');
				if ((int) $hours >= (int) $deactivationTime) {
					$mailAccount->deactivate($th->getMessage());
				}
			}
			if ($this->checkTimeout()) {
				break;
			}
		}
		if (!$lastId || !$count) {
			$pauser->destroy();
		}
	}
}
