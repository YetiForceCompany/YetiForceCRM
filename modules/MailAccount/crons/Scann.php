<?php
/**
 * Cron for mail scanner.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * MailAccount_Scann_Cron class.
 */
class MailAccount_Scann_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$scanner = new \App\Mail\Scanner();
		$scanner->setLimit(\App\Config::performance('NUMBERS_EMAILS_DOWNLOADED_DURING_ONE_SCANNING', 100));

		$queryGenerator = (new \App\QueryGenerator('MailAccount'));
		$queryGenerator->permissions = false;
		$queryGenerator->setFields(['id']);
		$queryGenerator->addCondition('mailaccount_status', 'PLL_ACTIVE', 'e');
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($recordId = $dataReader->readColumn(0)) {
			$mailAccount = \App\Mail\Account::getInstanceById($recordId);
			$scanner->setAccount($mailAccount);
			$scanner->run(fn () => $this->checkTimeout());
			if ($this->checkTimeout()) {
				break;
			}
		}
	}
}
