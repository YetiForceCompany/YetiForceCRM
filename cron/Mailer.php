<?php
/**
 * Mailer cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$dataReader = (new \App\Db\Query())->from('s_#__mail_queue')
	->where(['status' => 1])
	->orderBy(['priority' => SORT_DESC, 'date' => SORT_ASC])
	->limit(AppConfig::performance('CRON_MAX_NUMBERS_SENDING_MAILS'))
	->createCommand(\App\Db::getInstance('admin'))->query();
while ($rowQueue = $dataReader->read()) {
	\App\Mailer::sendByRowQueue($rowQueue);
}
$dataReader->close();
