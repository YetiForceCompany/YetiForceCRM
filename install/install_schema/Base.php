<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [];
		$this->foreignKey = [];
	}

	public function data()
	{
		$this->data = [];
	}
}
