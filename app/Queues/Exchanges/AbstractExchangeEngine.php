<?php
/**
 * Base class for interface.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Exchanges;

/**
 * Class AbstractExchangeEngine
 */
abstract class AbstractExchangeEngine
{

	/**
	 * Exchange for queue
	 * @var \App\Queues\Exchanges\AbstractExchange
	 */
	protected $exchange;

	/**
	 * Sets exchange
	 * @param \App\Queues\Exchanges\AbstractExchange $exchange
	 */
	public function setExchange(\App\Queues\Exchanges\AbstractExchange $exchange)
	{
		$this->exchange = $exchange;
	}

	/**
	 * Adds message to queue
	 */
	abstract public function addMessage(): string;
}
