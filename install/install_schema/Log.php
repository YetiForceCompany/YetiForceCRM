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
		$this->tables = [
			'l_#__profile' => [
				'columns' => [
					'id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'category' => $this->stringType()->notNull(),
					'info' => $this->text(),
					'log_time' => $this->stringType(20)->notNull(),
					'trace' => $this->text(),
					'level' => $this->stringType(),
					'duration' => $this->decimal('3,3')->notNull(),
				],
				'index' => [
					['profile_id_idx', 'id'],
					['profile_category_idx', 'category'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__settings_tracker_basic' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer()->unsigned()->notNull(),
					'type' => $this->smallInteger(1)->notNull(),
					'action' => $this->stringType(50)->notNull(),
					'record_id' => $this->integer(),
					'module_name' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__settings_tracker_detail' => [
				'columns' => [
					'id' => $this->integer()->unsigned()->notNull(),
					'prev_value' => $this->stringType()->notNull()->defaultValue(''),
					'post_value' => $this->stringType()->notNull()->defaultValue(''),
					'field' => $this->stringType()->notNull(),
				],
				'index' => [
					['settings_tracker_detail_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__sqltime' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'type' => $this->stringType(20),
					'content' => $this->text(),
					'date' => $this->dateTime(),
					'qtime' => $this->decimal('20,3'),
					'group' => $this->integer(),
				],
				'index' => [
					['sqltime_id_idx', 'id'],
					['sqltime_type_idx', 'type'],
					['sqltime_group_idx', 'group'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'l_#__switch_users' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->stringType(10)->notNull(),
					'baseid' => $this->integer()->notNull(),
					'destid' => $this->integer()->notNull(),
					'busername' => $this->stringType(50)->notNull(),
					'dusername' => $this->stringType(50)->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'agent' => $this->stringType()->notNull(),
				],
				'index' => [
					['switch_users_baseid_idx', 'baseid'],
					['switch_users_destid_idx', 'destid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'o_#__access_for_admin' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
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
					'id' => $this->primaryKey()->unsigned(),
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
					'id' => $this->primaryKey()->unsigned(),
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
					'id' => $this->primaryKey()->unsigned(),
					'username' => $this->stringType(50)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'ip' => $this->stringType(100)->notNull(),
					'record' => $this->integer()->notNull(),
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
					'id' => $this->primaryKey()->unsigned(),
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
		$this->foreignKey = [];
	}

	public function data()
	{
		$this->data = [
		];
	}
}
