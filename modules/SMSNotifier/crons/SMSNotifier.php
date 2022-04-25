<?php
/**
 * SMS Notifier cron.
 *
 * @package Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * SMS Notifier Cron class.
 */
class SMSNotifier_SMSNotifier_Cron extends \App\CronHandler
{
	/** @var string Status */
	private const STATUS_QUEUE = 'PLL_QUEUE';
	/** @var string Module name */
	private $moduelName = 'SMSNotifier';

	/** {@inheritdoc} */
	public function process()
	{
		if (\App\Integrations\SMSProvider::isActiveProvider()) {
			$queryGenerator = new \App\QueryGenerator($this->moduelName);
			$dataReader = $queryGenerator->setFields(['id'])
				->addCondition('smsnotifier_status', static::STATUS_QUEUE, 'e')
				->setLimit(\App\Config::module($this->moduelName, 'maxCronSentSMS'))
				->createQuery()
				->createCommand()->query();
			while ($recordId = $dataReader->readColumn(0)) {
				$recordModel = \SMSNotifier_Record_Model::getInstanceById($recordId, $this->moduelName);
				$recordModel->send();
				$this->updateLastActionTime();
				if ($this->checkTimeout()) {
					break;
				}
			}
			$dataReader->close();
		}
	}
}
