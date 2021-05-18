<?php

namespace Importers;

/**
 * Class that imports base database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base1 extends \App\Db\Importers\Base
{
	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'a_#__relatedlists_widgets' => [
				'columns' => [
					'wcol' => 'tinyint(1) DEFAULT 1',
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}
}
