<?php
/**
 * Mail Rbl cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail Rbl cron class.
 */
class Vtiger_MailRbl_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		if (\App\YetiForce\Shop::check('YetiForceRbl')) {
			\App\Mail\Rbl::sync(\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST);
			\App\Mail\Rbl::sync(\App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST);
		}
	}
}
