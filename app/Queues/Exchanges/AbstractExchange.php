<?php
/**
 * Base class for exchanges
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Exchanges;

/**
 * Class AbstractExchange
 */
abstract class AbstractExchange
{

	/**
	 * Data
	 * @var array
	 */
	protected $data;

	/**
	 * Engine for queue
	 * @var \App\Queues\AbstractEngine
	 */
	protected $engine;

	/**
	 * Name Exchange
	 */
	abstract public function getName();

	/**
	 * Sets data
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Returns data
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}

	/**
	 * Sets engine
	 * @param \App\Queues\AbstractEngine $engine
	 */
	public function setEngine(\App\Queues\AbstractEngine $engine)
	{
		$this->engine = $engine;
	}

	/**
	 * Returns engine
	 * @return \App\Queues\AbstractEngine
	 */
	public function getEngine(): \App\Queues\AbstractEngine
	{
		return $this->engine;
	}

	/**
	 * Returns special interface exchange for engine
	 * @return \App\Queues\Exchanges\AbstractExchangeEngine
	 * @throws \App\Exceptions\AppException
	 */
	public function getExchangeEngine(): \App\Queues\Exchanges\AbstractExchangeEngine
	{
		$exchangeEngineClass = '\\App\\Queues\\Exchanges\\' . $this->engine->getName() . '\\' . $this->getName();
		$exchangeEngine = new $exchangeEngineClass();
		if (!$exchangeEngine instanceof \App\Queues\Exchanges\AbstractExchangeEngine) {
			throw new \App\Exceptions\AppException('ERR_CLASS_MUST_BE||' . \App\Queues\Exchanges\AbstractExchangeEngine::class);
		}
		$exchangeEngine->setExchange($this);
		return $exchangeEngine;
	}

	/**
	 * Adds message to queue, Returns id of message.
	 * @return string
	 */
	public function addMessage(): string
	{
		return $this->getExchangeEngine()->addMessage();
	}
}
