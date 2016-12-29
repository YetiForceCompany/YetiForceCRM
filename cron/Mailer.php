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
	//var_dump(App\Json::encode(['name' => 'test', 'email' => 'opensaastest@gmail.com']));
	///*
	$mailer = (new \App\Mailer())
		->loadSmtpByID($rowQueue['smtp_id'])
		->subject($rowQueue['subject'])
		->content($rowQueue['content']);
	if ($rowQueue['from']) {
		$from = App\Json::decode($rowQueue['from']);
		$mailer->from($from['email'], $from['name']);
	}
	if ($rowQueue['cc']) {
		foreach (App\Json::decode($rowQueue['cc']) as $row) {
			$mailer->cc($row['email'], $row['name']);
		}
	}
	if ($rowQueue['bcc']) {
		foreach (App\Json::decode($rowQueue['bcc']) as $row) {
			$mailer->bcc($row['email'], $row['name']);
		}
	}
	if ($rowQueue['attachments']) {
		foreach (App\Json::decode($rowQueue['attachments']) as $row) {
			$mailer->attachment($row['path'], $row['name']);
		}
	}
	if ($mailer->getSmtp('individual_delivery')) {
		foreach (App\Json::decode($rowQueue['to']) as $row) {
			$separateMailer = clone $mailer;
			$separateMailer->to($row['email'], $row['name']);
			$status = $separateMailer->send();
			if (!$status) {
				break;
			}
		}
	} else {
		foreach (App\Json::decode($rowQueue['to']) as $row) {
			$mailer->to($row['email'], $row['name']);
		}
		$status = $mailer->send();
	}

	if ($status) {
		//$db->createCommand()->delete('s_#__mail_queue', ['id' => $rowQueue['id']])->execute();
	} else {
		$db->createCommand()->update('s_#__mail_queue', ['status' => 2], ['id' => $rowQueue['id']])->execute();
	}
	// */
}
