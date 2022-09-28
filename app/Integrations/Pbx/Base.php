<?php
/**
 * Base PBX driver integrations file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Pbx;

/**
 * Base PBX driver integrations class.
 */
abstract class Base
{
	/** @var string Class name */
	public $name = '';

	/** @var string[] Values to configure. */
	public $configFields = [];

	/** @var \App\Integrations\Pbx PBX main integration instance. */
	protected $pbx;

	/**
	 * Base PBX driver constructor.
	 *
	 * @param \App\Integrations\Pbx $pbx
	 */
	public function __construct(\App\Integrations\Pbx $pbx)
	{
		$this->pbx = $pbx;
	}

	/**
	 * Perform phone call.
	 *
	 * @return array
	 */
	abstract public function performCall(): array;

	/**
	 * Save phone calls.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function saveCalls(\App\Request $request): array
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
