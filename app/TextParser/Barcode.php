<?php

namespace App\TextParser;

/**
 * Display bar code class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon  <a.kon@yetiforce.com>
 */
class Barcode extends Base
{
	/** @var string */
	public $name = 'LBL_DISPLAY_BARCODE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
	}
}
