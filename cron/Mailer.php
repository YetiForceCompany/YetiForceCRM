<?php
/**
 * Mailer cron
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$db = \App\Db::getInstance('admin');
$dataReader = (new \App\Db\Query())->from('s_#__mail_queue')
		->where(['status' => 1])
		->orderBy(['priority' => SORT_DESC, 'date' => SORT_ASC])
		->limit(AppConfig::performance('CRON_MAX_NUMERS_SENDING_MAILS'))
		->createCommand($db)->query();
while ($rowQueue = $dataReader->read()) {
	$status = \App\Mailer::sendByRowQueue($rowQueue);
	if ($status) {
		$db->createCommand()->delete('s_#__mail_queue', ['id' => $rowQueue['id']])->execute();
	} else {
		$db->createCommand()->update('s_#__mail_queue', ['status' => 2], ['id' => $rowQueue['id']])->execute();
	}
}
