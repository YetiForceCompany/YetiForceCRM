<?php
/**
 * Abstract action controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Abstract action controller class.
 */
abstract class Action extends Base
{
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
