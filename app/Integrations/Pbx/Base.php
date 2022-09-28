<?php
/**
 * Base PBX integrations file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Pbx;

/**
 * Base PBX integrations class.
 */
abstract class Base
{
	/** @var string Class name */
	public $name = '';

	/** @var string[] Values to configure. */
	public $configFields = [];

	/**
	 * Perform phone call.
	 *
	 * @param \App\Integrations\Pbx $pbx
	 *
	 * @return array
	 */
	abstract public function performCall(\App\Integrations\Pbx $pbx): array;

	/**
	 * Save phone calls.
	 *
	 * @param \App\Integrations\Pbx $pbx
	 * @param \App\Request          $request
	 *
	 * @return array
	 */
	public function saveCalls(\App\Integrations\Pbx $pbx, \App\Request $request): array
	{
		throw new \App\Exceptions\AppException('Method not supported');
	}

	/**
	 * Save settings.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function saveSettings(array $data): void
	{
	}
}
