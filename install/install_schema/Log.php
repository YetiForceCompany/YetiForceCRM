<?php

namespace Importers;

/**
 * Class that imports log database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Log extends \App\Db\Importers\Base
{
	public $dbType = 'log';

	public function scheme()
	{
		$this->tables = [
			'l_#__batchmethod' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'method' => $this->stringType(50)->notNull(),
					'params' => $this->text(),
					'status' => $this->smallInteger(1)->unsigned()->notNull(),
					'userid' => $this->integer(),
					'date' => $this->date(),
					'message' => $this->text()
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->notNull()
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__profile' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'category' => $this->stringType()->notNull(),
					'info' => $this->text(),
					'log_time' => $this->stringType(20)->notNull(),
					'trace' => $this->text(),
					'level' => $this->stringType(),
					'duration' => $this->decimal('7,3')->notNull(),
				],
				'index' => [
						['id', 'id'],
						['category', 'category'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__settings_tracker_basic' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'user_id' => $this->integer(10)->unsigned(),
					'type' => $this->smallInteger(1)->notNull(),
					'action' => $this->stringType(50)->notNull(),
					'record_id' => $this->integer(10),
					'module_name' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
				],
				'columns_mysql' => [
					'type' => $this->tinyInteger(1)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__settings_tracker_detail' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'prev_value' => $this->stringType()->notNull()->defaultValue(''),
					'post_value' => $this->stringType()->notNull()->defaultValue(''),
					'field' => $this->stringType()->notNull(),
				],
				'index' => [
						['id', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__sqltime' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'type' => $this->stringType(20),
					'content' => $this->text(),
					'date' => $this->dateTime(),
					'qtime' => $this->decimal('20,3'),
					'group' => $this->integer(10),
				],
				'index' => [
						['id', 'id'],
						['type', 'type'],
						['group', 'group'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__switch_users' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->stringType(10)->notNull(),
					'baseid' => $this->integer(10)->notNull(),
					'destid' => $this->integer(10)->notNull(),
					'busername' => $this->stringType(50)->notNull(),
					'dusername' => $this->stringType(50)->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'agent' => $this->stringType()->notNull(),
				],
				'index' => [
						['baseid', 'baseid'],
						['destid', 'destid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__username_history' => [
				'columns' => [
					'user_name' => $this->stringType(32),
					'user_id' => $this->integer(10)->unsigned(),
					'date' => $this->dateTime(),
				],
				'index' => [
						['user_id', 'user_id'],
						['user_name', 'user_name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__userpass_history' => [
				'columns' => [
					'pass' => $this->stringType(200)->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'date' => $this->dateTime()->notNull(),
				],
				'index' => [
						['user_id', ['user_id', 'pass']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_admin' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'module' => $this->stringType(30)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
					'request' => $this->stringType(300)->notNull(),
					'referer' => $this->stringType(300),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_api' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
					'request' => $this->stringType(300)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_user' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100),
					'module' => $this->stringType(30)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType(),
					'request' => $this->stringType(300)->notNull(),
					'referer' => $this->stringType(300),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_to_record' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'record' => $this->integer(10)->notNull(),
					'module' => $this->stringType(30)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
					'request' => $this->stringType(300)->notNull(),
					'referer' => $this->stringType(300),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__csrf' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'referer' => $this->stringType(300)->notNull(),
					'url' => $this->stringType(300)->notNull(),
					'agent' => $this->stringType()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
		];
	}

	public function data()
	{
		$this->data = [
		];
	}
}
