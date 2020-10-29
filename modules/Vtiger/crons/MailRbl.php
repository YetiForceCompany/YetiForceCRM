<?php
/**
 * Mail Rbl cron file.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail Rbl cron class.
 */
class Vtiger_MailRbl_Cron extends \App\CronHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		\App\Mail\Rbl::sync(\App\Mail\Rbl::LIST_TYPE_PUBLIC_BLACK_LIST);
		\App\Mail\Rbl::sync(\App\Mail\Rbl::LIST_TYPE_PUBLIC_WHITE_LIST);
	}
}
