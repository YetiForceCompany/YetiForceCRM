<?php
/**
 * Abstract action controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Action class.
 */
abstract class Action extends Base
{
	/**
	 * Construct.
	 *
	 * @param \App\Request $request
	 */
	public function __construct(\App\Request $request)
	{
		$this->request = $request;
		$this->init();
	}
}
