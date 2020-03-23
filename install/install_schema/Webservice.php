<?php

namespace Importers;

/**
 * Class that imports admin database.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Webservice extends \App\Db\Importers\Base
{
	public $dbType = 'webservice';

	public function scheme()
	{
		$this->tables = [
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
					'id' => $this->char(40)->notNull(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'language' => $this->stringType(10),
					'created' => $this->dateTime(),
					'changed' => $this->dateTime(),
					'params' => $this->text(),
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
					'user_name' => $this->stringType(50)->notNull(),
					'password_h' => $this->stringType(200),
					'password_t' => $this->stringType(200),
					'type' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'login_time' => $this->dateTime(),
					'logout_time' => $this->dateTime(),
					'language' => $this->stringType(10),
					'crmid' => $this->integer(10),
					'user_id' => $this->integer(10),
					'istorage' => $this->integer(10),
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
					'name' => $this->stringType(100)->notNull(),
					'pass' => $this->stringType(100),
					'acceptable_url' => $this->stringType(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'api_key' => $this->stringType(100)->notNull(),
					'type' => $this->stringType(40)->notNull(),
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
			['w_#__manage_consents_user_fk1', 'w_#__manage_consents_user', 'server_id', 'w_#__servers', 'id', 'CASCADE', 'RESTRICT'],
			['w_#__portal_session_ibfk_1', 'w_#__portal_session', 'user_id', 'w_#__portal_user', 'id', 'CASCADE', 'RESTRICT'],
			['w_#__portal_user_ibfk_1', 'w_#__portal_user', 'server_id', 'w_#__servers', 'id', 'CASCADE', 'RESTRICT'],
		];
	}

	public function data()
	{
		$this->data = [
		];
	}
}
