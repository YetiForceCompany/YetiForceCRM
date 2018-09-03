<?php

namespace App\Integrations\Pbx;

/**
 * Base PBX integrations class.
 *
 * @packasge  YetiForce.Integrations
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
