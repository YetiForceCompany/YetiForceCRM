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
class Outlook extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		foreach ($this->getActions() as $action) {
			$class = "App\\Mail\\ScannerAction\\{$action}";
			(new $class($this))->process();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getActions(): array
	{
		return ['CreatedMail', 'LinkByFields'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMailCrmId()
	{
		if ($this->has('mailCrmId')) {
			return $this->get('mailCrmId');
		}
		$mailCrmId = \App\Mail\Message::findByCid($this->getCid());
		$this->set('mailCrmId', $mailCrmId);
		return  $mailCrmId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUserId(): int
	{
		return \App\User::getCurrentUserRealId();
	}
}
