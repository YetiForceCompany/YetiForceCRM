<?php
/**
 * Queues.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App;

class Queues
{

	const MAILER = '\App\Queues\Exchanges\Mail';
	const CRON_ENGINE = 'Cron';

	public static function addToQueues(string $exchangeClass, array $data, string $nameEngine = ''): string
	{
		if (empty($nameEngine)) {
			$nameEngine = \Config\Performance::$engineQueues;
		}
		$exchange = new $exchangeClass();
		if (!$exchange instanceof Queues\Exchanges\AbstractExchange) {
			throw new \App\Exceptions\AppException('ERR_CLASS_MUST_BE||' . Queues\Exchanges\AbstractExchange::class);
		}
		$exchange->setData($data);
		$engineClass = '\\App\\Queues\\' . $nameEngine;
		$engine = new $engineClass();
		if (!$engine instanceof Queues\AbstractEngine) {
			throw new \App\Exceptions\AppException('ERR_CLASS_MUST_BE||' . Queues\AbstractEngine::class);
		}
		$exchange->setEngine($engine);
		return $exchange->addMessage();
	}
}
