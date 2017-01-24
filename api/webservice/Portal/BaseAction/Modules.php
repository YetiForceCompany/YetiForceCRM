<?php
namespace Api\Portal\BaseAction;

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Modules extends \Api\Core\BaseAction
{

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get modules list
	 * @return string[]
	 */
	public function get()
	{
		return \Api\Core\Module::getPermittedModules();
	}
}
