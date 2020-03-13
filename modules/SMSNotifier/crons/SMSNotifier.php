<?php
/**
 * SMSNotifier cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * SMSNotifier_SMSNotifier_Cron class.
 */
class SMSNotifier_SMSNotifier_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		if (SMSNotifier_Module_Model::checkServer()) {
			$db = \App\Db::getInstance('admin');
			$dataReader = (new \App\Db\Query())->from('s_#__smsnotifier_queue')
				->orderBy(['id' => SORT_ASC])
				->limit(App\Config::performance('CRON_MAX_NUMBERS_SENDING_SMS'))
				->createCommand($db)->query();
			while ($rowQueue = $dataReader->read()) {
				SMSNotifier_Record_Model::sendSMS($rowQueue['message'], $rowQueue['tonumbers'], explode(',', $rowQueue['records']), $rowQueue['module']);
				$db->createCommand()->delete('s_#__smsnotifier_queue', ['id' => $rowQueue['id']])->execute();
			}
			$dataReader->close();
		}
	}
}
