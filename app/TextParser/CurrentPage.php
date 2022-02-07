<?php

namespace App\TextParser;

/**
 * Current Page class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń  <a.kon@yetiforce.com>
 */
class CurrentPage extends Base
{
	/** @var string */
	public $name = 'LBL_CURRENT_PAGE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		return '{p}';
	}
}
