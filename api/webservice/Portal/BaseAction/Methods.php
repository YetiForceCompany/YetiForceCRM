<?php
namespace Api\Portal\BaseAction;

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Methods extends \Api\Core\BaseAction
{

	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get modules list
	 * @return string[]
	 */
	public function get()
	{
		$methods = [];
		$src = 'api/webservice/Portal/';
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if (!$item->isDir()) {
				$itemPathName = explode(DIRECTORY_SEPARATOR, $iterator->getSubPathName());
				$dir = array_shift($itemPathName);
				$name = rtrim(array_shift($itemPathName), '.php');
				$className = "Api\Portal\\$dir\\$name";
				$instance = new $className();
				switch ($dir) {
					case 'BaseAction':
						break;
					case 'BaseModule': $name = "__MODULE_NAME__/$name";
						break;
					default: $name = "$dir/$name";
						break;
				}
				$methods[$dir]["api/webservice/$name"] = implode(',', $instance->allowedMethod);
				unset($instance);
			}
		}
		return $methods;
	}
}
