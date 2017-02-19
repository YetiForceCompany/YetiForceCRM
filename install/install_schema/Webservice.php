<?php
namespace Importers;

/**
 * Class that imports admin database
 * @package YetiForce.Install
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Webservice extends \App\Db\Importers\Base
{

	public $dbType = 'webservice';

	public function scheme()
	{
		$this->tables = [
			'w_#__portal_session' => [
				'columns' => [
					'id' => $this->stringType(32)->notNull(),
					'user_id' => $this->integer(),
					'language' => $this->stringType(10),
					'created' => $this->dateTime(),
					'changed' => $this->dateTime(),
					'params' => $this->text(),
				],
				'primaryKeys' => [
					['sessions_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_user' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'server_id' => $this->integer(10),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'user_name' => $this->stringType(50)->notNull(),
					'password_h' => $this->stringType(200),
					'password_t' => $this->stringType(200),
					'type' => $this->smallInteger(1),
					'login_time' => $this->dateTime(),
					'logout_time' => $this->dateTime(),
					'language' => $this->stringType(10),
					'crmid' => $this->integer(),
					'user_id' => $this->integer(),
				],
				'columns_mysql' => [
					'status' => "tinyint(1) DEFAULT '0'",
					'type' => "tinyint(1) DEFAULT '1'"
				],
				'index' => [
					['portal_users_idx', 'user_name', true],
					['portal_users_status_idx', ['user_name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__servers' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(100)->notNull(),
					'pass' => $this->stringType(100),
					'acceptable_url' => $this->stringType(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'api_key' => $this->stringType(100)->notNull(),
					'type' => $this->stringType(40)->notNull(),
					'accounts_id' => $this->integer(),
				],
				'index' => [
					['servers_idx', ['name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
	}

	public function data()
	{
		$this->data = [
		];
	}
}
