<?php
/**
 * Exchange for mails.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Exchanges;

/**
 * Class Mail
 */
class Mail extends AbstractExchange
{

	/**
	 * Returns name of queue
	 * @return string
	 */
	public function getQueueName()
	{
		return 'Mail';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'Mail';
	}
}
