<?php

namespace App\Debug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * Database debug bar collector class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DebugBarDatabase extends DataCollector implements Renderable
{
	/**
	 * @return array
	 */
	public function collect()
	{
		return [
		];
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
		return [
			'Database' => [
				'icon' => 'tags',
				'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
				'map' => 'Database',
				'default' => '{}',
			],
			'Database:badge' => [
				'map' => 'Database.count',
				'default' => 1,
			],
		];
	}
}
