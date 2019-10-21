<?php
/**
 * Cron.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App;

/**
 * Class to execute task.
 */
abstract class CronHandler
{
	/**
	 * Main function to execute task.
	 *
	 * @return void
	 */
	abstract public function process();
}
