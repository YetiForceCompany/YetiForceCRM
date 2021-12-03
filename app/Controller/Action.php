<?php
/**
 * Abstract action controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller;

/**
 * Abstract action controller class.
 */
abstract class Action extends Base
{
	/** {@inheritdoc} */
	public $csrfActive = false;

	/**
	 * Process action.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		return true;
	}

	/** {@inheritdoc} */
	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
