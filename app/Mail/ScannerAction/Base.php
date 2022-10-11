<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * Message instance.
	 *
	 * @var \App\Mail\Message\Base
	 */
	protected $message;

	/**
	 * Constructor.
	 *
	 * @param \App\Mail\Message\Base $message
	 */
	public function __construct(\App\Mail\Message\Base $message)
	{
		$this->message = $message;
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
		if ($exceptions = $this->message->getExceptions()[$type] ?? false) {
			$mailForExceptions = (0 === $this->message->getMailType()) ? $this->message->get('to_email') : [$this->message->get('from_email')];
			$return = (bool) array_intersect($exceptions, $mailForExceptions);
		}
		return $return;
	}
}
