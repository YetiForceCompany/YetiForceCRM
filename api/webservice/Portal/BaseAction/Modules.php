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

	/** @var string[] Request methods */
	protected $requestMethod = ['GET'];

	/**
	 * Get modules list
	 * @return string[]
	 */
	public function get()
	{
		$modules = [];
		foreach (\vtlib\Functions::getAllModules(true, false, 0) as $key => $value) {
			if (\App\Privilege::isPermitted($value['name'])) {
				$modules[$value['name']] = \App\Language::translate($value['name'], $value['name']);
			}
		}
		return $modules;
	}
}
