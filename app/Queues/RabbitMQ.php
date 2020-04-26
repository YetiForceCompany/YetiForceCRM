<?php
/**
 * RabbitMQ.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues;

/**
 * Class RabbitMQ
 */
class RabbitMQ extends AbstractEngine
{

	private $connection;
	private $channel;

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'RabbitMQ';
	}

	/**
	 * Returns channel
	 * @return \PhpAmqpLib\Channel\AMQPChannel
	 */
	public function getChannel(): \PhpAmqpLib\Channel\AMQPChannel
	{
		if (isset($this->channel)) {
			return $this->channel;
		}
		$this->channel = $this->getConnection()->channel();
		return $this->channel;
	}

	/**
	 * Returns connection to RabbitMQ
	 * @return \PhpAmqpLib\Connection\AMQPStreamConnection
	 */
	public function getConnection(): \PhpAmqpLib\Connection\AMQPStreamConnection
	{
		if (isset($this->connection)) {
			return $this->connection;
		}
		$this->connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(\Config\Components\RabbitMQ::$host, \Config\Components\RabbitMQ::$port, \Config\Components\RabbitMQ::$username, \Config\Components\RabbitMQ::$password);
		return $this->connection;
	}

	/**
	 * Close connection
	 */
	public function connectionClose()
	{
		if (isset($this->connection)) {
			$this->connection->close();
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->connectionClose();
	}
}
