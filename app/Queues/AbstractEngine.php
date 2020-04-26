<?php
/**
 * Abstract class of engine for queue.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues;

/**
 * Class AbstractEngine
 */
abstract class AbstractEngine
{

	/**
	 * Returns name of worker
	 */
	abstract public function getName(): string;
}
