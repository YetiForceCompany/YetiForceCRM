<?php
/**
 * Mail outlook message file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerEngine;

/**
 * Mail outlook message class.
 */
abstract class Base extends \App\Base
{
	/**
	 * Process data.
	 *
	 * @var array
	 */
	public $processData = [];

	/**
	 * Main function to execute scanner engine actions.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Get scanner actions.
	 *
	 * @return array
	 */
	abstract public function getActions(): array;

	/**
	 * Get mail crm id.
	 *
	 * @return array
	 */
	abstract public function getMailCrmId();

	/**
	 * Get user id.
	 *
	 * @return int
	 */
	abstract public function getUserId(): int;

	/**
	 * Generation crm unique id.
	 *
	 * @return string
	 */
	public function getCid(): string
	{
		if ($this->has('cid')) {
			return $this->get('cid');
		}
		$cid = hash('sha256', $this->get('from_email') . '|' . $this->get('date') . '|' . $this->get('subject') . '|' . $this->get('message_id'));
		$this->set('cid', $cid);
		return $cid;
	}
}
