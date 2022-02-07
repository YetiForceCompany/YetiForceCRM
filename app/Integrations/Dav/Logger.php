<?php
/**
 * SabreDav logger plugin file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

use Psr\Log\AbstractLogger;

/**
 * SabreDav logger plugin class.
 */
class Logger extends AbstractLogger
{
	/**
	 * Logs.
	 *
	 * @var array
	 */
	public $logs = [];

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 */
	public function log($level, $message, array $context = [])
	{
		$this->logs[] = [
			$level,
			$message,
			$context,
		];
	}
}
