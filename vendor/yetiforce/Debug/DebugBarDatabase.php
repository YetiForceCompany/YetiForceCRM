<?php
namespace App\Debug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * Database debug bar collector class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DebugBarDatabase extends DataCollector implements Renderable
{

	/**
	 * @return array
	 */
	public function collect()
	{
		$data = [
		];
		return $data;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Database';
	}

	/**
	 * @return array
	 */
	public function getWidgets()
	{
		return array(
			"Database" => array(
				"icon" => "tags",
				"widget" => "PhpDebugBar.Widgets.VariableListWidget",
				"map" => "Database",
				"default" => "{}"
			),
			"Database:badge" => array(
				"map" => "Database.count",
				"default" => 1
			)
		);
	}
}
