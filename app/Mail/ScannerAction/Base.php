<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Base mail scanner action class.
 */
abstract class Base
{
	/**
	 * Action priority.
	 *
	 * @var int
	 */
	public static $priority = 9;
	/**
	 * Scanner engine instance.
	 *
	 * @var \App\Mail\ScannerEngine\Base
	 */
	protected $scannerEngine;

	/**
	 * Constructor.
	 *
	 * @param \App\Mail\ScannerEngine\Base $scannerEngine
	 */
	public function __construct(\App\Mail\ScannerEngine\Base $scannerEngine)
	{
		$this->scannerEngine = $scannerEngine;
	}

	/**
	 * Main function to execute action.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Check exceptions.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function checkExceptions(string $type): bool
	{
		$return = false;
		if ($exceptions = $this->scannerEngine->getExceptions()[$type] ?? false) {
			$mailForExceptions = (0 === $this->scannerEngine->getMailType()) ? $this->scannerEngine->get('to_email') : [$this->scannerEngine->get('from_email')];
			$return = (bool) array_intersect($exceptions, $mailForExceptions);
		}
		return $return;
	}
}
