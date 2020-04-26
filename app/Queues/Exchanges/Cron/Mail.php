<?php
/**
 * Exchange fo mails by cron.
 *
 * @package   App\Queues\Workers
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Exchanges\Cron;

/**
 * Class Mail
 */
class Mail extends \App\Queues\Exchanges\AbstractExchangeEngine
{

	/**
	 * {@inheritdoc}
	 */
	public function addMessage(): string
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()->insert('s_yf_mail_queue', $this->exchange->getData())->execute();
		return (string) $db->getLastInsertID('s_yf_mail_queue_id_seq');
	}
}
