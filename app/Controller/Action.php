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
	 * Response instance.
	 *
	 * @var \App\Response
	 */
	public $response;
	/**
	 * Method call protocol
	 * Values: http, socket, mix.
	 *
	 * @var string
	 */
	public $protocol = 'http';

	/**
	 * Construct.
	 *
	 * @param \App\Request $request
	 */
	public function __construct(\App\Request $request, \App\Response  $response)
	{
		$this->request = $request;
		$this->response = $response;
		$this->init();
	}

	/**
	 * Emit data function.
	 *
	 * @return void
	 */
	public function emit()
	{
		$this->response->setEnv(\App\Config::getJsEnv());
		$this->response->emit();
	}

	/**
	 * Validate request function.
	 *
	 * @return void
	 */
	public function validateRequest()
	{
		$this->request->validateWriteAccess();
	}
}
