<?php

namespace App\Controller;

/**
 * Abstract action controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
