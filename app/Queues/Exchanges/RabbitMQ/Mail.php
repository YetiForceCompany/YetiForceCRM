<?php
/**
 * Exchange fo mails by RabbitMQ.
 *
 * @package   App\Queues\Workers
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Exchanges\RabbitMQ;

/**
 * Class Mail
 */
class Mail extends \App\Queues\Exchanges\AbstractExchangeEngine
{

	/**
	 * Add queue to server
	 */
	public function declareQueue()
	{
		$maxPriorities = (new \App\Db\Query())->from('vtiger_email_template_priority')->max('email_template_priority');
		$engine = $this->exchange->getEngine();
		$channel = $engine->getChannel();
		$channel->queue_declare($this->exchange->getQueueName(), false, true, false, false, false, ['x-max-priority' => ['I', $maxPriorities]]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function addMessage(): string
	{
		$data = $this->exchange->getData();
		if (!isset($data['id'])) {
			$db = \App\Db::getInstance('admin');
			$db->createCommand()->insert('s_#__mail_queue', $data)->execute();
			$id = $db->getLastInsertID('s_#__mail_queue_id_seq');
			if ($data['status'] !== 1) {
				return '';
			}
		} else {
			$id = $data['id'];
		}
		$priority = $data['priority'];
		$engine = $this->exchange->getEngine();
		$channel = $engine->getChannel();
		$this->declareQueue();
		$msg = new \PhpAmqpLib\Message\AMQPMessage(\App\Json::encode(['id' => $id]), [
			'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
			'priority' => $priority
		]);
		$channel->basic_publish($msg, '', $this->exchange->getQueueName());
		$channel->close();
		return '';
	}
}
