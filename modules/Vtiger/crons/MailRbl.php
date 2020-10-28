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
		\App\Mail\Rbl::sync('black');
		\App\Mail\Rbl::sync('white');
	}
}
