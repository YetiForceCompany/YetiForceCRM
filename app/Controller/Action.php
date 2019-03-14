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
	 * Main WebApi controller instance.
	 *
	 * @var \App\Controller\WebApi
	 */
	protected $controller;

	/**
	 * Construct.
	 *
	 * @param WebApi $controller
	 */
	public function __construct(WebApi $controller)
	{
		$this->controller = $controller;
		$this->request = $controller->request;
		$this->init();
	}

	/**
	 * Process action.
	 *
	 * @param \App\Request $request
	 */
	public function process()
	{
	}
}
