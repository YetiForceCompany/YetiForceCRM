<?php
/**
 * Abstract Worker.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues\Workers;

/**
 * Base class for workers
 */
abstract class AbstractWorker
{

	/**
	 * Data
	 * @var array
	 */
	protected $data;

	/**
	 * Sets data
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Main function to work
	 */
	abstract public function process(): bool;
}
