<?php

namespace App\Dav;

use Psr\Log\AbstractLogger;

/**
 * Dav class Logger.
 */
class Logger extends AbstractLogger
{
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
			$context
		];
	}
}
