<?php
/**
 * Worker to send mails.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Workers;

/**
 * Class Mail
 */
class Mail extends AbstractWorker
{

	/**
	 * {@inheritdoc}
	 */
	public function process(): bool
	{
		return \App\Mailer::sendByRowQueue($this->data);
	}
}
