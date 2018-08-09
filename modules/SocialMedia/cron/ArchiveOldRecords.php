<?php
/**
 * Cron task to archive old records.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
(new \App\Db\Query())
	->from('u_#__social_media_twitter')
	->where(['<', 'created_at', (new DateTime('NOW - 12 months'))->format('Y-m-d')]);
