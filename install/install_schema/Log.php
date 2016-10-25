<?php
namespace Importers;

/**
 * Class that imports log database
 * @package YetiForce.Install
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends \App\Db\Importers\Base
{

	public $dbType = 'log';

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
