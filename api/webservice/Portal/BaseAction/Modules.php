<?php
namespace Api\Portal\BaseAction;

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
