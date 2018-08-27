<?php
/**
 * Cron for downloading messages from social media.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
$db = \App\Db::getInstance();
foreach (\Vtiger_SocialMedia_Helper::getSocialMediaAccount() as $twitterLogin) {
	$res = (new \Vtiger_SocialMedia_Helper())->getUserTimeline($twitterLogin);
	foreach ($res as $rowTwitter) {
		if (!(new \App\Db\Query())->from('u_#__social_media_twitter')->where(['id_twitter' => $rowTwitter['id']])->exists()) {
			$db->createCommand()->insert('u_#__social_media_twitter', [
				'id_twitter' => $rowTwitter['id'],
				'twitter_login' => $twitterLogin,
				'message' => $rowTwitter['text'],
				'created' => (new \DateTime($rowTwitter['created_at']))->format('Y-m-d H:i:sP'),
			])->execute();
		}
	}
}
