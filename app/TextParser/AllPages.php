<?php

namespace App\TextParser;

/**
 * All pages class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń  <a.kon@yetiforce.com>
 */
class AllPages extends Base
{
	/** @var string */
	public $name = 'LBL_ALL_PAGES';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		return '{a}';
	}
}
