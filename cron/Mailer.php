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
	$mailer = (new \App\Mailer())
		->loadSmtpByID($rowQueue['smtp_id'])
		->subject($rowQueue['subject'])
		->content($rowQueue['content']);
	if ($rowQueue['from']) {
		$from = App\Json::decode($rowQueue['from']);
		$mailer->from($from['email'], $from['name']);
	}
	if ($rowQueue['cc']) {
		foreach (App\Json::decode($rowQueue['cc']) as $email => $name) {
			if (is_numeric($email)) {
				$email = $name;
				$name = '';
			}
			$mailer->cc($email, $name);
		}
	}
	if ($rowQueue['bcc']) {
		foreach (App\Json::decode($rowQueue['bcc']) as $email => $name) {
			if (is_numeric($email)) {
				$email = $name;
				$name = '';
			}
			$mailer->bcc($email, $name);
		}
	}
	if ($rowQueue['attachments']) {
		foreach (App\Json::decode($rowQueue['attachments']) as $path => $name) {
			if (is_numeric($path)) {
				$path = $name;
				$name = '';
			}
			$mailer->attachment($path, $name);
		}
	}
	if ($mailer->getSmtp('individual_delivery')) {
		foreach (App\Json::decode($rowQueue['to']) as $email => $name) {
			$separateMailer = clone $mailer;
			if (is_numeric($email)) {
				$email = $name;
				$name = '';
			}
			$separateMailer->to($email, $name);
			$status = $separateMailer->send();
			if (!$status) {
				break;
			}
		}
	} else {
		foreach (App\Json::decode($rowQueue['to']) as $email => $name) {
			if (is_numeric($email)) {
				$email = $name;
				$name = '';
			}
			$mailer->to($email, $name);
		}
		$status = $mailer->send();
	}
	if ($status) {
		$db->createCommand()->delete('s_#__mail_queue', ['id' => $rowQueue['id']])->execute();
	} else {
		$db->createCommand()->update('s_#__mail_queue', ['status' => 2], ['id' => $rowQueue['id']])->execute();
	}
}
