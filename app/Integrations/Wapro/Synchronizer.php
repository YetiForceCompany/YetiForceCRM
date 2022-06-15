<?php
/**
 * WAPRO ERP base synchronizer file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Wapro;

/**
 * WAPRO ERP base synchronizer class.
 */
abstract class Synchronizer extends \App\Base
{
	/** @var string Provider name | File name. */
	protected $name;

	/**
	 * Function to get provider name.
	 *
	 * @return string provider name
	 */
	public function getName(): string
	{
		return $this->name;
	}
}
