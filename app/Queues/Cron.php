<?php
/**
 * Cron engine.
 *
 * @package   App\Queues
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <tomek.kur14@gmail.com>
 */
namespace App\Queues;

/**
 * Class Cron
 */
class Cron extends AbstractEngine
{

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'Cron';
	}
}
