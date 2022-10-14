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
	/** @var int Action priority. */
	public static $priority = 9;

	/** @var string[] Scope of availability. */
	public static $available = ['Users', 'MailAccount'];

	/** @var string Action label */
	protected $label;

	/** @var \App\Mail\Message\Base Message instance. */
	protected $message;

	/**
	 * Get action name.
	 * Action name | File name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return basename(str_replace('\\', '/', static::class));
	}

	/**
	 * Main function to execute action.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Set mail account.
	 *
	 * @param \App\Mail\Account $account
	 *
	 * @return $this
	 */
	public function setAccount(\App\Mail\Account $account)
	{
		$this->account = $account;
		return $this;
	}

	public function setMessage(\App\Mail\Message\Base $message)
	{
		$this->message = $message;
		return $this;
	}
}
