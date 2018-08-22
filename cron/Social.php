<?php
/**
 * Cron task to archive old records.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
$configTwitter = (new \Settings_SocialMedia_Config_Model('twitter'));
$days = $configTwitter->get('archiving_records_number_of_days');
if (empty($days)) {
	\App\Log::info('Number of days is empty');
} else {
	$db = \App\Db::getInstance();
	$dataReader = (new \App\Db\Query())
		->from('u_#__social_media_twitter')
		->where(['<', 'created', (new \DateTime('NOW - ' . $days . ' days'))->format('Y-m-d')])
		->createCommand()
		->query();
	while (($row = $dataReader->read())) {
		$db->createCommand()->insert('b_#__social_media_twitter', $row)->execute();
		$db->createCommand()->delete('u_#__social_media_twitter', ['id' => $row['id']])->execute();
	}
	$dataReader->close();
}
