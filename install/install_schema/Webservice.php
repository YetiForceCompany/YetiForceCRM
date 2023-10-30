<?php

namespace Importers;

/**
 * Class that imports admin database.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Webservice extends \App\Db\Importers\Base
{
	public $dbType = 'webservice';

	public function scheme()
	{
		$this->tables = [
			'w_#__api_session' => [
				'columns' => [
					'id' => $this->char(64)->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'language' => $this->stringType(10),
					'created' => $this->dateTime()->notNull(),
					'changed' => $this->dateTime(),
					'params' => $this->text(),
					'ip' => $this->stringType(100)->notNull(),
					'last_method' => $this->stringType(100),
					'agent' => $this->stringType(100)->notNull(),
				],
				'index' => [
					['user_id', 'user_id'],
				],
				'primaryKeys' => [
					['api_session_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__api_user' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'server_id' => $this->integer(10)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'user_name' => $this->stringType(100)->notNull(),
					'password' => $this->stringType(500),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'login_time' => $this->dateTime(),
					'crmid' => $this->integer(10),
					'user_id' => $this->integer(10),
					'login_method' => $this->stringType(30)->notNull()->defaultValue('PLL_PASSWORD'),
					'auth' => $this->stringType(500),
					'custom_params' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['user_name', 'user_name'],
					['user_name_status', ['user_name', 'status']],
					['server_id', 'server_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__manage_consents_user' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'server_id' => $this->integer(10)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'token' => $this->char(64)->notNull(),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'login_time' => $this->dateTime(),
					'language' => $this->stringType(10),
					'user_id' => $this->integer(10),
					'custom_params' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['user_uq', ['server_id', 'token']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_session' => [
				'columns' => [
					'id' => $this->char(64)->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'language' => $this->stringType(10),
					'created' => $this->dateTime()->notNull(),
					'changed' => $this->dateTime(),
					'params' => $this->text(),
					'ip' => $this->stringType(100)->notNull(),
					'last_method' => $this->stringType(100),
					'agent' => $this->stringType(100)->notNull(),
				],
				'index' => [
					['user_id', 'user_id'],
				],
				'primaryKeys' => [
					['portal_session_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_user' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'server_id' => $this->integer(10)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'user_name' => $this->stringType(100)->notNull(),
					'password' => $this->stringType(500),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'login_time' => $this->dateTime(),
					'crmid' => $this->integer(10),
					'user_id' => $this->integer(10),
					'istorage' => $this->integer(10),
					'login_method' => $this->stringType(30)->notNull()->defaultValue('PLL_PASSWORD'),
					'auth' => $this->stringType(500),
					'custom_params' => $this->text(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'type' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['user_name', 'user_name'],
					['user_name_2', ['user_name', 'status']],
					['server_id', 'server_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__servers' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'type' => $this->stringType(40)->notNull(),
					'name' => $this->stringType(100)->notNull(),
					'pass' => $this->stringType(500)->notNull(),
					'api_key' => $this->stringType(500)->notNull(),
					'ips' => $this->stringType(),
					'url' => $this->stringType(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['name', ['name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['w_#__api_session_ibfk_1', 'w_#__api_session', 'user_id', 'w_#__api_user', 'id', 'CASCADE', NULL],
			['w_#__api_user_ibfk_1', 'w_#__api_user', 'server_id', 'w_#__servers', 'id', 'CASCADE', NULL],
			['w_#__manage_consents_user_fk1', 'w_#__manage_consents_user', 'server_id', 'w_#__servers', 'id', 'CASCADE', NULL],
			['w_#__portal_session_ibfk_1', 'w_#__portal_session', 'user_id', 'w_#__portal_user', 'id', 'CASCADE', NULL],
			['w_#__portal_user_ibfk_1', 'w_#__portal_user', 'server_id', 'w_#__servers', 'id', 'CASCADE', NULL],
		];
	}

	public function data()
	{
		$this->data = [
		];
	}
}
