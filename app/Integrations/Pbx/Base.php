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
	/**
	 * @var string Class name
	 */
	public $name = '';

	/**
	 * Values to configure.
	 *
	 * @var string[]
	 */
	public $configFields = [];

	/**
	 * Perform phone call.
	 *
	 * @param \App\Integrations\Pbx $pbx
	 */
	abstract public function performCall(\App\Integrations\Pbx $pbx);
}
