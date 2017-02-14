<?php
namespace Importers;

/**
 * Class that imports base database
 * @package YetiForce.Install
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base1 extends \App\Db\Importers\Base
{

	public $dbType = 'base';

	public function scheme()
	{
		$this->tables = [
			'chat_bans' => [
				'columns' => [
					'userID' => $this->integer()->notNull(),
					'userName' => $this->stringType(64)->notNull(),
					'dateTime' => $this->dateTime()->notNull(),
					'ip' => $this->binary(16)->notNull(),
				],
				'index' => [
					['chat_bans_user_idx', 'userName'],
					['chat_bans_date_idx', 'dateTime'],
				],
				'primaryKeys' => [
					['chat_bans_pk', 'userID']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'chat_invitations' => [
				'columns' => [
					'userID' => $this->integer()->notNull(),
					'channel' => $this->integer()->notNull(),
					'dateTime' => $this->dateTime()->notNull(),
				],
				'index' => [
					['chat_invitations_time_idx', 'dateTime'],
				],
				'primaryKeys' => [
					['chat_invitations_pk', ['userID', 'channel']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'chat_messages' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'userID' => $this->integer()->notNull(),
					'userName' => $this->stringType(64)->notNull(),
					'userRole' => $this->integer(1)->notNull(),
					'channel' => $this->integer()->notNull(),
					'dateTime' => $this->dateTime()->notNull(),
					'ip' => $this->binary(16)->notNull(),
					'text' => $this->text(),
				],
				'index' => [
					['chat_messages_id_idx', ['id', 'channel', 'dateTime']],
					['chat_messages_time_idx', 'dateTime'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'chat_online' => [
				'columns' => [
					'userID' => $this->integer()->notNull(),
					'userName' => $this->stringType(64)->notNull(),
					'userRole' => $this->integer(1)->notNull(),
					'channel' => $this->integer()->notNull(),
					'dateTime' => $this->dateTime()->notNull(),
					'ip' => $this->binary(16)->notNull(),
				],
				'index' => [
					['chat_online_idx', 'userName'],
				],
				'primaryKeys' => [
					['chat_online_pk', 'userID']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_activatedonce' => [
				'columns' => [
					'workflow_id' => $this->integer()->notNull(),
					'entity_id' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['workflow_activatedonce_pk', ['workflow_id', 'entity_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_tasktypes' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'tasktypename' => $this->stringType()->notNull(),
					'label' => $this->stringType(),
					'classname' => $this->stringType(),
					'classpath' => $this->stringType(),
					'templatepath' => $this->stringType(),
					'modules' => $this->stringType(500),
					'sourcemodule' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_tasktypes_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflows' => [
				'columns' => [
					'workflow_id' => $this->primaryKey(),
					'module_name' => $this->stringType(100),
					'summary' => $this->stringType(400)->notNull(),
					'test' => $this->text(),
					'execution_condition' => $this->integer()->notNull(),
					'defaultworkflow' => $this->integer(1),
					'type' => $this->stringType(),
					'filtersavedinnew' => $this->integer(1),
					'schtypeid' => $this->integer(10),
					'schdayofmonth' => $this->stringType(100),
					'schdayofweek' => $this->stringType(100),
					'schannualdates' => $this->stringType(100),
					'schtime' => $this->stringType(50),
					'nexttrigger_time' => $this->dateTime(),
				],
				'index' => [
					['com_vtiger_workflows_idx', 'workflow_id', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtask_queue' => [
				'columns' => [
					'task_id' => $this->integer(),
					'entity_id' => $this->stringType(100),
					'do_after' => $this->integer(),
					'task_contents' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks' => [
				'columns' => [
					'task_id' => $this->primaryKey(),
					'workflow_id' => $this->integer(),
					'summary' => $this->stringType(400)->notNull(),
					'task' => $this->text(),
				],
				'index' => [
					['com_vtiger_workflowtasks_idx', 'task_id', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_entitymethod' => [
				'columns' => [
					'workflowtasks_entitymethod_id' => $this->integer()->notNull(),
					'module_name' => $this->stringType(100),
					'method_name' => $this->stringType(100),
					'function_path' => $this->stringType(400),
					'function_name' => $this->stringType(100),
				],
				'index' => [
					['workflowtasks_entitymethod_idx', 'workflowtasks_entitymethod_id', true],
				],
				'primaryKeys' => [
					['workflowtasks_entitymethod_pk', 'workflowtasks_entitymethod_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_entitymethod_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtemplates' => [
				'columns' => [
					'template_id' => $this->primaryKey(),
					'module_name' => $this->stringType(100),
					'title' => $this->stringType(400),
					'template' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_addressbookchanges' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'synctoken' => $this->integer()->unsigned()->notNull(),
					'addressbookid' => $this->integer()->unsigned()->notNull(),
					'operation' => $this->smallInteger(1)->notNull(),
				],
				'columns_mysql' => [
					'uri' => 'varbinary(200) NOT NULL'
				],
				'index' => [
					['dav_addressbookchanges_idx', ['addressbookid', 'synctoken']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_addressbooks' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principaluri' => $this->stringType(),
					'displayname' => $this->stringType(),
					'uri' => $this->stringType(),
					'description' => $this->text(),
					'synctoken' => $this->integer()->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'principaluri' => 'varbinary(255) DEFAULT NULL',
					'uri' => 'varbinary(255) DEFAULT NULL'
				],
				'index' => [
					['dav_addressbooks_uri_idx', ['principaluri', 'uri'], true],
					['dav_addressbooks_pri_idx', 'principaluri'],
				],
				'index_mysql' => [
					['dav_addressbooks_uri_idx', ['principaluri(100)', 'uri(100)'], true],
					['dav_addressbooks_pri_idx', 'principaluri(100)'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarchanges' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->binary(200)->notNull(),
					'synctoken' => $this->integer()->unsigned()->notNull(),
					'calendarid' => $this->integer()->unsigned()->notNull(),
					'operation' => $this->smallInteger(1)->notNull(),
				],
				'columns_mysql' => [
					'uri' => 'varbinary(200) NOT NULL',
				],
				'index' => [
					['dav_calendarchanges_idx', ['calendarid', 'synctoken']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarobjects' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'calendardata' => $this->binary(),
					'uri' => $this->stringType(200),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'lastmodified' => $this->integer()->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer()->unsigned()->notNull(),
					'componenttype' => $this->stringType(8),
					'firstoccurence' => $this->integer()->unsigned(),
					'lastoccurence' => $this->integer()->unsigned(),
					'uid' => $this->stringType(200),
					'crmid' => $this->integer(19),
				],
				'columns_mysql' => [
					'uri' => 'varbinary(200) DEFAULT NULL',
					'etag' => 'varbinary(32) DEFAULT NULL',
					'componenttype' => 'varbinary(8) DEFAULT NULL',
					'uid' => 'varbinary(200) DEFAULT NULL'
				],
				'index' => [
					['dav_calendarobjects_cal_idx', ['calendarid', 'uri'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendars' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principaluri' => $this->stringType(100),
					'displayname' => $this->stringType(100),
					'uri' => $this->stringType(200),
					'synctoken' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'description' => $this->text(),
					'calendarorder' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'timezone' => $this->text(),
					'components' => $this->stringType(21),
					'transparent' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'principaluri' => 'varbinary(100) DEFAULT NULL',
					'uri' => 'varbinary(200) DEFAULT NULL',
					'calendarcolor' => 'varbinary(10) DEFAULT NULL',
					'components' => 'varbinary(21) DEFAULT NULL',
				],
				'index' => [
					['dav_calendars_uri_idx', ['principaluri', 'uri'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarsubscriptions' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'principaluri' => $this->stringType(100)->notNull(),
					'source' => $this->text(),
					'displayname' => $this->stringType(100),
					'refreshrate' => $this->stringType(10),
					'calendarorder' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'striptodos' => $this->smallInteger(1),
					'stripalarms' => $this->smallInteger(1),
					'stripattachments' => $this->smallInteger(1),
					'lastmodified' => $this->integer()->unsigned(),
				],
				'columns_mysql' => [
					'uri' => 'varbinary(200) NOT NULL',
					'principaluri' => 'varbinary(100) NOT NULL',
					'calendarcolor' => 'varbinary(10) DEFAULT NULL',
				],
				'index' => [
					['dav_calendarsubscriptions_uri_idx', ['principaluri', 'uri'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_cards' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'addressbookid' => $this->integer()->unsigned()->notNull(),
					'carddata' => $this->binary(),
					'uri' => $this->stringType(200),
					'lastmodified' => $this->integer()->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer()->unsigned()->notNull(),
					'crmid' => $this->integer(19)->defaultValue(0),
				],
				'columns_mysql' => [
					'uri' => 'varbinary(255) DEFAULT NULL',
					'etag' => 'varbinary(32) DEFAULT NULL'
				],
				'index' => [
					['dav_cards_address_idx', ['addressbookid', 'crmid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_groupmembers' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principal_id' => $this->integer(10)->unsigned()->notNull(),
					'member_id' => $this->integer(10)->unsigned()->notNull(),
				],
				'index' => [
					['dav_groupmembers_pri_idx', ['principal_id', 'member_id'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_principals' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'email' => $this->stringType(80),
					'displayname' => $this->stringType(80),
					'userid' => $this->integer(),
				],
				'columns_mysql' => [
					'uri' => 'varbinary(200) NOT NULL',
					'email' => 'varbinary(80)',
				],
				'index' => [
					['dav_principals_uri_idx', 'uri', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_propertystorage' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'path' => $this->stringType(1024)->notNull(),
					'name' => $this->stringType(100)->notNull(),
					'valuetype' => $this->integer(10)->unsigned(),
					'value' => $this->binary(),
				],
				'columns_mysql' => [
					'path' => 'varbinary(1024) NOT NULL',
					'name' => 'varbinary(100) NOT NULL',
				],
				'index' => [
					['dav_propertystorage_path_idx', ['path', 'name'], true],
				],
				'index_mysql' => [
					['dav_propertystorage_path_idx', ['path(600)', 'name(100)'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_schedulingobjects' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principaluri' => $this->stringType(),
					'calendardata' => $this->binary(),
					'uri' => $this->stringType(200),
					'lastmodified' => $this->integer()->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer()->unsigned()->notNull(),
				],
				'columns_mysql' => [
					'principaluri' => 'varbinary(255) DEFAULT NULL',
					'uri' => 'varbinary(255) DEFAULT NULL',
					'etag' => 'varbinary(32) DEFAULT NULL'
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_users' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'username' => $this->stringType(50),
					'digesta1' => $this->stringType(32),
					'userid' => $this->integer(11)->unsigned(),
					'key' => $this->stringType(50),
				],
				'columns_mysql' => [
					'username' => 'varbinary(50) DEFAULT NULL',
					'digesta1' => 'varbinary(32) DEFAULT NULL',
				],
				'index' => [
					['dav_users_name_idx', 'username', true],
					['dav_users_user_id_idx', 'userid', true],
				],
				'index_mysql' => [
					['dav_users_name_idx', 'username(50)', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'roundcube_cache' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'cache_key' => $this->stringType(128)->notNull(),
					'created' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'expires' => $this->dateTime(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['roundcube_cache_expires_idx', 'expires'],
					['roundcube_cache_id_idx', ['user_id', 'cache_key']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_cache_index' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'mailbox' => $this->stringType()->notNull(),
					'expires' => $this->dateTime(),
					'valid' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['roundcube_index_exp_idx', 'expires'],
				],
				'primaryKeys' => [
					['roundcube_cache_index_pk', ['user_id', 'mailbox']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_cache_messages' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'mailbox' => $this->stringType()->notNull(),
					'uid' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'expires' => $this->dateTime(),
					'data' => $this->text()->notNull(),
					'flags' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['cache_messages_exp_idx', 'expires'],
				],
				'primaryKeys' => [
					['roundcube_cache_messages_pk', ['user_id', 'mailbox', 'uid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_cache_shared' => [
				'columns' => [
					'cache_key' => $this->stringType()->notNull(),
					'created' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'expires' => $this->dateTime(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['cache_shared_exp_idx', 'expires'],
					['cache_shared_key_idx', 'cache_key'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_cache_thread' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'mailbox' => $this->stringType()->notNull(),
					'expires' => $this->dateTime(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['cache_thread_exp_idx', 'expires'],
				],
				'primaryKeys' => [
					['roundcube_cache_thread_pk', ['user_id', 'mailbox']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contactgroupmembers' => [
				'columns' => [
					'contactgroup_id' => $this->integer(10)->unsigned()->notNull(),
					'contact_id' => $this->integer(10)->unsigned()->notNull(),
					'created' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
				],
				'index' => [
					['r_contactgroupmembers_idx', 'contact_id'],
				],
				'primaryKeys' => [
					['r_contactgroupmembers_pk', ['contactgroup_id', 'contact_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contactgroups' => [
				'columns' => [
					'contactgroup_id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull()->defaultValue(''),
				],
				'index' => [
					['roundcube_contactgroups_idx', ['user_id', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contacts' => [
				'columns' => [
					'contact_id' => $this->primaryKey()->unsigned(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull()->defaultValue(''),
					'email' => $this->text()->notNull(),
					'firstname' => $this->stringType(128)->notNull()->defaultValue(''),
					'surname' => $this->stringType(128)->notNull()->defaultValue(''),
					'vcard' => $this->text(),
					'words' => $this->text(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
				],
				'index' => [
					['roundcube_user_contacts_idx', ['user_id', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_dictionary' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned(),
					'language' => $this->stringType(5)->notNull(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['roundcube_dictionary_idx', ['user_id', 'language'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_identities' => [
				'columns' => [
					'identity_id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'standard' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull(),
					'organization' => $this->stringType(128)->notNull()->defaultValue(''),
					'email' => $this->stringType(128)->notNull(),
					'reply-to' => $this->stringType(128)->notNull()->defaultValue(''),
					'bcc' => $this->stringType(128)->notNull()->defaultValue(''),
					'signature' => $this->text(),
					'html_signature' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['roundcube_identities_user_idx', ['user_id', 'del']],
					['roundcube_identities_email_idx', ['email', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_searches' => [
				'columns' => [
					'search_id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->integer(3)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull(),
					'data' => $this->text(),
				],
				'index' => [
					['roundcube_searches_user_idx', ['user_id', 'type', 'name'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_session' => [
				'columns' => [
					'sess_id' => $this->stringType(128)->notNull(),
					'created' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'ip' => $this->stringType(40)->notNull(),
					'vars' => $this->text()->notNull(),
				],
				'index' => [
					['roundcube_session_idx', 'changed'],
				],
				'primaryKeys' => [
					['roundcube_session_pk', 'sess_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_system' => [
				'columns' => [
					'name' => $this->stringType(64)->notNull(),
					'value' => $this->text(),
				],
				'primaryKeys' => [
					['roundcube_system_pk', 'name']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users' => [
				'columns' => [
					'user_id' => $this->primaryKey()->unsigned(),
					'username' => $this->stringType(128)->notNull(),
					'mail_host' => $this->stringType(128)->notNull(),
					'created' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'last_login' => $this->dateTime(),
					'failed_login' => $this->dateTime(),
					'failed_login_counter' => $this->integer(10)->unsigned(),
					'language' => $this->stringType(5),
					'preferences' => $this->text(),
					'actions' => $this->text(),
					'password' => $this->stringType(200),
					'crm_user_id' => $this->integer(19)->defaultValue(0),
				],
				'index' => [
					['roundcube_users_idx', ['username', 'mail_host'], true],
					['roundcube_users_crm_id_idx', 'crm_user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users_autologin' => [
				'columns' => [
					'rcuser_id' => $this->integer(10)->unsigned()->notNull(),
					'crmuser_id' => $this->integer(19)->notNull(),
				],
				'index' => [
					['roundcube_users_autologin_idx', 'rcuser_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activity_invitation' => [
				'columns' => [
					'inviteesid' => $this->primaryKey()->unsigned(),
					'activityid' => $this->integer()->notNull(),
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'email' => $this->stringType(100)->notNull()->defaultValue(''),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'time' => $this->dateTime(),
				],
				'index' => [
					['activity_invitation_idx', 'activityid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement' => [
				'columns' => [
					'announcementid' => $this->integer()->notNull(),
					'title' => $this->stringType(),
					'announcement_no' => $this->stringType(),
					'subject' => $this->stringType(),
					'announcementstatus' => $this->stringType()->notNull()->defaultValue(''),
					'interval' => $this->smallInteger(5),
				],
				'index' => [
					['announcement_idx', 'announcementstatus'],
				],
				'primaryKeys' => [
					['announcement_pk', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement_mark' => [
				'columns' => [
					'announcementid' => $this->integer()->notNull(),
					'userid' => $this->integer()->notNull(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['announcement_mark_user_idx', ['userid', 'status']],
					['announcement_mark_ann_idx', ['announcementid', 'userid', 'date', 'status']],
				],
				'primaryKeys' => [
					['announcement_mark_pk', ['announcementid', 'userid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcementcf' => [
				'columns' => [
					'announcementid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['announcementcf_pk', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cfixedassets' => [
				'columns' => [
					'cfixedassetsid' => $this->integer()->notNull()->defaultValue(0),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'fixed_assets_type' => $this->stringType(),
					'fixed_assets_status' => $this->stringType(),
					'producent_designation' => $this->stringType(),
					'additional_designation' => $this->stringType(),
					'internal_designation' => $this->stringType(),
					'date_production' => $this->date(),
					'date_acquisition' => $this->date(),
					'purchase_price' => $this->decimal('25,8'),
					'actual_price' => $this->decimal('25,8'),
					'reservation' => $this->smallInteger(1),
					'pscategory' => $this->stringType(),
					'fixed_assets_fuel_type' => $this->stringType(),
					'timing_change' => $this->integer()->defaultValue(0),
					'oil_change' => $this->integer(),
					'fuel_consumption' => $this->integer(),
					'current_odometer_reading' => $this->integer(),
					'number_repair' => $this->smallInteger(),
					'date_last_repair' => $this->date(),
				],
				'primaryKeys' => [
					['cfixedassets_pk', 'cfixedassetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cfixedassetscf' => [
				'columns' => [
					'cfixedassetsid' => $this->integer()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['cfixedassetscf_pk', 'cfixedassetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cinternaltickets' => [
				'columns' => [
					'cinternalticketsid' => $this->integer()->notNull()->defaultValue(0),
					'subject' => $this->stringType(100),
					'cinternaltickets_no' => $this->stringType(32),
					'internal_tickets_status' => $this->stringType(150),
					'resolution' => $this->text()
				],
				'primaryKeys' => [
					['cinternalticketsid_pk', 'cinternalticketsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cinternalticketscf' => [
				'columns' => [
					'cinternalticketsid' => $this->integer()->notNull()->defaultValue(0),
				],
				'primaryKeys' => [
					['cinternalticketscf_pk', 'cinternalticketsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cmileagelogbook' => [
				'columns' => [
					'cmileagelogbookid' => $this->integer()->notNull()->defaultValue(0),
					'number' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'cmileage_logbook_status' => $this->stringType(150),
					'number_kilometers' => $this->decimal('13,2')->defaultValue(0),
				],
				'primaryKeys' => [
					['cmileagelogbook_pk', 'cmileagelogbookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cmileagelogbookcf' => [
				'columns' => [
					'cmileagelogbookid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['cmileagelogbookcf_pk', 'cmileagelogbookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition' => [
				'columns' => [
					'competitionid' => $this->integer()->notNull()->defaultValue(0),
					'competition_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'vat_id' => $this->stringType(30),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'email' => $this->stringType(100)->defaultValue(''),
					'active' => $this->smallInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['competition_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition_address' => [
				'columns' => [
					'competitionaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['competition_address_pk', 'competitionaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competitioncf' => [
				'columns' => [
					'competitionid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['competitioncf_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_label' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
					'label' => $this->stringType(),
				],
				'primaryKeys' => [
					['crmentity_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_last_changes' => [
				'columns' => [
					'crmid' => $this->integer()->notNull(),
					'fieldname' => $this->stringType(50)->notNull(),
					'user_id' => $this->integer()->notNull(),
					'date_updated' => $this->dateTime()->notNull(),
				],
				'index' => [
					['crmentity_last_changes_idx', ['crmid', 'fieldname']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_rel_tree' => [
				'columns' => [
					'crmid' => $this->integer()->notNull(),
					'module' => $this->integer()->notNull(),
					'tree' => $this->stringType(50)->notNull(),
					'relmodule' => $this->integer()->notNull(),
					'rel_created_user' => $this->integer()->notNull(),
					'rel_created_time' => $this->dateTime()->notNull(),
					'rel_comment' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_search_label' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
					'searchlabel' => $this->stringType()->notNull(),
					'setype' => $this->stringType(30)->notNull(),
					'userid' => $this->text()->null(),
				],
				'index' => [
					['crmentity_label_idx', 'searchlabel'],
					['crmentity_search_idx', ['searchlabel', 'setype']],
				],
				'primaryKeys' => [
					['crmentity_search_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_showners' => [
				'columns' => [
					'crmid' => $this->integer(19),
					'userid' => $this->smallInteger(11)->unsigned()->notNull(),
				],
				'index' => [
					['crmentity_showners_max_idx', ['crmid', 'userid'], true],
					['crmentity_showners_crmid_idx', 'crmid'],
					['crmentity_showners_userid_idx', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__dashboard_type' => [
				'columns' => [
					'dashboard_id' => $this->primaryKey(),
					'name' => $this->stringType()->notNull(),
					'system' => $this->smallInteger(1)->defaultValue(0)
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__emailtemplates' => [
				'columns' => [
					'emailtemplatesid' => $this->integer()->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'email_template_type' => $this->stringType(50),
					'module' => $this->stringType(50),
					'subject' => $this->stringType(),
					'content' => $this->text(),
					'sys_name' => $this->stringType(50),
					'email_template_priority' => $this->smallInteger(1)->defaultValue(1),
					'companyid' => $this->smallInteger(5)
				],
				'index' => [
					['emailtemplates_sys_name_idx', 'sys_name'],
				],
				'primaryKeys' => [
					['emailtemplates_pk', 'emailtemplatesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorites' => [
				'columns' => [
					'crmid' => $this->integer(),
					'module' => $this->stringType(30),
					'relcrmid' => $this->integer(),
					'relmodule' => $this->stringType(30),
					'userid' => $this->integer(),
					'data' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
				],
				'index' => [
					['favorites_crmid_idx', 'crmid'],
					['favorites_relcrmid_idx', 'relcrmid'],
					['favorites_idx', ['crmid', 'module', 'relcrmid', 'relmodule', 'userid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeeping' => [
				'columns' => [
					'fbookkeepingid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'related_to' => $this->integer(),
				],
				'index' => [
					['fbookkeeping_rel_idx', 'related_to'],
				],
				'primaryKeys' => [
					['fbookkeeping_pk', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeepingcf' => [
				'columns' => [
					'fbookkeepingid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['fbookkeepingcf_pk', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice' => [
				'columns' => [
					'fcorectinginvoiceid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(19),
					'fcorectinginvoice_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('16,5'),
					'sum_gross' => $this->decimal('16,5'),
					'fcorectinginvoice_status' => $this->stringType()->defaultValue(''),
					'finvoiceid' => $this->integer(19),
				],
				'index' => [
					['fcorectinginvoice_acc_idx', 'accountid'],
					['fcorectinginvoice_inv_idx', 'finvoiceid'],
				],
				'primaryKeys' => [
					['fcorectinginvoice_pk', 'fcorectinginvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_address' => [
				'columns' => [
					'fcorectinginvoiceaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['fcorectinginvoice_address_pk', 'fcorectinginvoiceaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['fcorectinginvoice_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['fcorectinginvoice_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoicecf' => [
				'columns' => [
					'fcorectinginvoiceid' => $this->integer(19)->notNull(),
				],
				'primaryKeys' => [
					['fcorectinginvoicecf_pk', 'fcorectinginvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__featured_filter' => [
				'columns' => [
					'user' => $this->stringType(30)->notNull(),
					'cvid' => $this->integer()->notNull(),
				],
				'index' => [
					['featured_filter_cvid_idx', 'cvid'],
					['featured_filter_user_idx', 'user'],
				],
				'primaryKeys' => [
					['featured_filter_pk', ['user', 'cvid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice' => [
				'columns' => [
					'finvoiceid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(),
					'finvoice_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('16,5'),
					'sum_gross' => $this->decimal('16,5'),
					'finvoice_status' => $this->stringType()->defaultValue(''),
					'finvoice_paymentstatus' => $this->stringType(),
					'finvoice_type' => $this->stringType(),
					'pscategory' => $this->stringType(100),
				],
				'index' => [
					['finvoice_idx', 'accountid'],
				],
				'primaryKeys' => [
					['finvoice_pk', 'finvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_address' => [
				'columns' => [
					'finvoiceaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoice_address_pk', 'finvoiceaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['finvoice_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['finvoice_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecf' => [
				'columns' => [
					'finvoiceid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['finvoicecf_pk', 'finvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost' => [
				'columns' => [
					'finvoicecostid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(),
					'finvoicecost_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('16,5'),
					'sum_gross' => $this->decimal('16,5'),
					'finvoicecost_status' => $this->stringType()->defaultValue(''),
					'finvoicecost_paymentstatus' => $this->stringType(),
					'pscategory' => $this->stringType(50),
				],
				'index' => [
				],
				'primaryKeys' => [
					['finvoicecost_pk', 'finvoicecostid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_address' => [
				'columns' => [
					'finvoicecostaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoicecost_address_pk', 'finvoicecostaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['finvoicecost_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['finvoicecost_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecostcf' => [
				'columns' => [
					'finvoicecostid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['finvoicecostcf_pk', 'finvoicecostid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(),
					'finvoiceproforma_formpayment' => $this->stringType(),
					'sum_total' => $this->decimal('15,2'),
					'sum_gross' => $this->decimal('13,2'),
					'finvoiceproforma_status' => $this->stringType(),
				],
				'index' => [
					['finvoiceproforma_idx', 'accountid'],
				],
				'primaryKeys' => [
					['finvoiceproforma_pk', 'finvoiceproformaid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_address' => [
				'columns' => [
					'finvoiceproformaaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'poboxa' => $this->stringType(50),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumberc' => $this->stringType(),
					'localnumberc' => $this->stringType(),
					'poboxc' => $this->stringType(),
				],
				'primaryKeys' => [
					['finvoiceproforma_address_pk', 'finvoiceproformaaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['finvoiceproforma_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['finvoiceproforma_invfield_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproformacf' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['finvoiceproformacf_pk', 'finvoiceproformaid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__github' => [
				'columns' => [
					'github_id' => $this->primaryKey()->notNull(),
					'client_id' => $this->stringType(20),
					'token' => $this->stringType(100),
					'username' => $this->stringType(32),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn' => [
				'columns' => [
					'igdnid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'igdn_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
					'accountid' => $this->integer(),
					'ssingleordersid' => $this->integer(),
				],
				'index' => [
					['igdn_storage_idx', 'storageid'],
					['igdn_accountid_idx', 'accountid'],
					['igdn_ssingleordersid_idx', 'ssingleordersid'],
				],
				'primaryKeys' => [
					['igdn_idx', 'igdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['igdn_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['igdn_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc' => [
				'columns' => [
					'igdncid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'igdnc_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
					'accountid' => $this->integer(),
					'igdnid' => $this->integer(),
				],
				'index' => [
					['igdnc_storageid_idx', 'storageid'],
					['igdnc_accountid_idx', 'accountid'],
					['igdnc_igdnid_idx', 'igdnid'],
				],
				'primaryKeys' => [
					['igdnc_pk', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['igdnc_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['igdnc_invfield_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnccf' => [
				'columns' => [
					'igdncid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['igdnccf_pk', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdncf' => [
				'columns' => [
					'igdnid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['igdncf_pk', 'igdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin' => [
				'columns' => [
					'iginid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'igin_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
				],
				'index' => [
					['igin_storageid_idx', 'storageid'],
				],
				'primaryKeys' => [
					['igin_pk', 'iginid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['igin_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['igin_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igincf' => [
				'columns' => [
					'iginid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['igincf_pk', 'iginid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn' => [
				'columns' => [
					'igrnid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'igrn_status' => $this->stringType(),
					'vendorid' => $this->integer(),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('27,8')->notNull()->defaultValue(0),
				],
				'index' => [
					['igrn_storageid_idx', 'storageid'],
					['igrn_vendorid_idx', 'vendorid'],
				],
				'primaryKeys' => [
					['igrn_pk', 'igrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['igrn_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['igrn_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc' => [
				'columns' => [
					'igrncid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'igrnc_status' => $this->stringType(),
					'vendorid' => $this->integer(),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'igrnid' => $this->integer(),
				],
				'index' => [
					['igrnc_storageid_idx', 'storageid'],
					['igrnc_vendorid_idx', 'vendorid'],
					['igrnc_igrnid_idx', 'igrnid'],
				],
				'primaryKeys' => [
					['igrnc_pk', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['igrnc_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['igrnc_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnccf' => [
				'columns' => [
					'igrncid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['igrnccf_pk', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrncf' => [
				'columns' => [
					'igrnid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['igrncf_pk', 'igrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn' => [
				'columns' => [
					'iidnid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'iidn_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
				],
				'index' => [
					['iidn_idx', 'storageid'],
				],
				'primaryKeys' => [
					['iidn_pk', 'iidnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['iidn_inventory_pk', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['iidn_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidncf' => [
				'columns' => [
					'iidnid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['iidncf_pk', 'iidnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder' => [
				'columns' => [
					'ipreorderid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'ipreorder_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'acceptance_date' => $this->date(),
				],
				'index' => [
					['ipreorder_storageid_idx', 'storageid'],
					['ipreorder_accountid_idx', 'accountid'],
				],
				'primaryKeys' => [
					['ipreorder_pk', 'ipreorderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['ipreorder_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['ipreorder_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreordercf' => [
				'columns' => [
					'ipreorderid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['ipreordercf_pk', 'ipreorderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn' => [
				'columns' => [
					'istdnid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'istdn_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'process' => $this->integer(),
					'subprocess' => $this->integer(),
				],
				'index' => [
					['istdn_storageid_idx', 'storageid'],
					['istdn_accountid_idx', 'accountid'],
					['istdn_process_idx', 'process'],
					['istdn_subprocess_idx', 'subprocess'],
				],
				'primaryKeys' => [
					['istdn_pk', 'istdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['istdn_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['istdn_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdncf' => [
				'columns' => [
					'istdnid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['istdncf_pk', 'istdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istn' => [
				'columns' => [
					'istnid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'istn_status' => $this->stringType(),
					'estimated_date' => $this->date(),
					'istn_type' => $this->stringType(),
				],
				'primaryKeys' => [
					['istn_pk', 'istnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istncf' => [
				'columns' => [
					'istnid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['istncf_pk', 'istnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages' => [
				'columns' => [
					'istorageid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'storage_status' => $this->stringType()->defaultValue(''),
					'storage_type' => $this->stringType()->defaultValue(''),
					'parentid' => $this->integer(),
				],
				'index' => [
					['istorages_idx', 'parentid'],
				],
				'primaryKeys' => [
					['istorages_pk', 'istorageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages_address' => [
				'columns' => [
					'istorageaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['istorages_address_pk', 'istorageaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages_products' => [
				'columns' => [
					'crmid' => $this->integer(),
					'relcrmid' => $this->integer(),
					'qtyinstock' => $this->decimal('25,3'),
				],
				'index' => [
					['istorages_products_crmid_idx', 'crmid'],
					['istorages_products_rel_idx', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istoragescf' => [
				'columns' => [
					'istorageid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['istoragescf_idx', 'istorageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn' => [
				'columns' => [
					'istrnid' => $this->integer()->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(),
					'istrn_status' => $this->stringType(),
					'vendorid' => $this->integer(),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'process' => $this->integer(),
					'subprocess' => $this->integer(),
				],
				'index' => [
					['istrn_storageid_idx', 'storageid'],
					['istrn_vendorid_idx', 'vendorid'],
					['istrn_process_idx', 'process'],
					['istrn_subprocess_idx', 'subprocess'],
				],
				'primaryKeys' => [
					['istrn_pk', 'istrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['istrn_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['istrn_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrncf' => [
				'columns' => [
					'istrnid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['istrncf_pk', 'istrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebase' => [
				'columns' => [
					'knowledgebaseid' => $this->integer()->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'content' => $this->text(),
					'category' => $this->stringType(200),
					'knowledgebase_view' => $this->stringType(),
					'knowledgebase_status' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['knowledgebase_pk', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebasecf' => [
				'columns' => [
					'knowledgebaseid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['knowledgebasecf_pk', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_address_boock' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'email' => $this->stringType(100)->notNull(),
					'name' => $this->stringType()->notNull(),
					'users' => $this->text()->notNull(),
				],
				'index' => [
					['mail_address_boock_email_idx', ['email', 'name']],
					['mail_address_boock_id_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_autologin' => [
				'columns' => [
					'ruid' => $this->integer()->unsigned()->notNull(),
					'key' => $this->stringType(50)->notNull(),
					'cuid' => $this->smallInteger(11)->unsigned()->notNull(),
					'params' => $this->text()->notNull(),
				],
				'index' => [
					['mail_autologin_ruid_idx', 'ruid'],
					['mail_autologin_cuid_idx', 'cuid'],
					['mail_autologin_key_idx', 'key'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_compose_data' => [
				'columns' => [
					'userid' => $this->integer()->unsigned()->notNull(),
					'key' => $this->stringType(32)->notNull(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['mail_compose_data_idx', ['userid', 'key'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__notification' => [
				'columns' => [
					'notificationid' => $this->integer()->notNull(),
					'title' => $this->stringType(),
					'number' => $this->stringType(50),
					'notification_status' => $this->stringType(),
					'notification_type' => $this->stringType()->defaultValue(''),
					'link' => $this->integer(),
					'process' => $this->integer(),
					'subprocess' => $this->integer(),
				],
				'index' => [
					['notification_link_idx', 'link'],
					['notification_process_idx', 'process'],
					['notification_subprocess_idx', 'subprocess'],
				],
				'primaryKeys' => [
					['notification_pk', 'notificationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
					'type' => $this->char()->notNull(),
					'lat' => $this->decimal('10,7'),
					'lon' => $this->decimal('10,7'),
				],
				'index' => [
					['openstreetmap_lat_idx', ['lat', 'lon']],
					['openstreetmap_crmid_idx', ['crmid', 'type']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_address_updater' => [
				'columns' => [
					'crmid' => $this->integer(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_cache' => [
				'columns' => [
					'user_id' => $this->integer()->unsigned()->notNull(),
					'module_name' => $this->stringType(50)->notNull(),
					'crmids' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['openstreetmap_cache_idx', ['user_id', 'module_name']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_record_updater' => [
				'columns' => [
					'crmid' => $this->integer()->notNull(),
					'type' => $this->char()->notNull(),
					'address' => $this->text()->notNull(),
				],
				'index' => [
					['openstreetmap_record_updater_idx', ['crmid', 'type']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners' => [
				'columns' => [
					'partnersid' => $this->integer()->notNull()->defaultValue(0),
					'partners_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'vat_id' => $this->stringType(30),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'email' => $this->stringType(100)->defaultValue(''),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'category' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['partners_pk', 'partnersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners_address' => [
				'columns' => [
					'partneraddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
				],
				'primaryKeys' => [
					['partners_address_pk', 'partneraddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partnerscf' => [
				'columns' => [
					'partnersid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['partnerscf_pk', 'partnersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__recurring_info' => [
				'columns' => [
					'srecurringordersid' => $this->integer()->notNull()->defaultValue(0),
					'target_module' => $this->stringType(25),
					'recurring_frequency' => $this->stringType(100),
					'start_period' => $this->date(),
					'end_period' => $this->date(),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'last_recurring_date' => $this->date(),
				],
				'primaryKeys' => [
					['recurring_info_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__reviewed_queue' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer(),
					'data' => $this->text(),
					'time' => $this->dateTime(),
				],
				'index' => [
					['reviewed_queue_idx', 'userid'],
				],
				'primaryKeys' => [
					['reviewed_queue_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations' => [
				'columns' => [
					'scalculationsid' => $this->integer()->notNull()->defaultValue(0),
					'scalculations_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'srequirementscardsid' => $this->integer(),
					'category' => $this->stringType(),
					'scalculations_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('27,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('27,8'),
				],
				'index' => [
					['scalculations_salesprocessid_idx', 'salesprocessid'],
					['scalculations_accountid_idx', 'accountid'],
					['scalculations_srequirementscardsid_idx', 'srequirementscardsid'],
				],
				'primaryKeys' => [
					['scalculations_pk', 'scalculationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['scalculations_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['scalculations_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculationscf' => [
				'columns' => [
					'scalculationsid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['scalculationscf_pk', 'scalculationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries' => [
				'columns' => [
					'squoteenquiriesid' => $this->integer()->notNull()->defaultValue(0),
					'squoteenquiries_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'category' => $this->stringType(),
					'squoteenquiries_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['squoteenquiries_salesprocessid_idx', 'salesprocessid'],
					['squoteenquiries_accountid_idx', 'accountid'],
				],
				'primaryKeys' => [
					['squoteenquiries_pk', 'squoteenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['squoteenquiries_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['squoteenquiries_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiriescf' => [
				'columns' => [
					'squoteenquiriesid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['squoteenquiriescf_pk', 'squoteenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes' => [
				'columns' => [
					'squotesid' => $this->integer()->notNull()->defaultValue(0),
					'squotes_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'scalculationsid' => $this->integer(),
					'category' => $this->stringType(),
					'squotes_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('27,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('27,8'),
					'sum_gross' => $this->decimal('27,8'),
					'sum_discount' => $this->decimal('27,8'),
					'valid_until' => $this->date(),
				],
				'index' => [
					['squotes_salesprocessid_idx', 'salesprocessid'],
					['squotes_scalculationsid_idx', 'scalculationsid'],
					['squotes_accountid_idx', 'accountid'],
				],
				'primaryKeys' => [
					['squotes_pk', 'squotesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_address' => [
				'columns' => [
					'squotesaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['squotes_address_pk', 'squotesaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['squotes_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['squotes_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotescf' => [
				'columns' => [
					'squotesid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['squotescf_pk', 'squotesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders' => [
				'columns' => [
					'srecurringordersid' => $this->integer()->notNull()->defaultValue(0),
					'srecurringorders_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'squotesid' => $this->integer(),
					'category' => $this->stringType(),
					'srecurringorders_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'duedate' => $this->date(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['srecurringorders_salesprocessid_idx', 'salesprocessid'],
					['srecurringorders_squotesid_idx', 'squotesid'],
					['srecurringorders_accountid_idx', 'accountid'],
				],
				'primaryKeys' => [
					['srecurringorders_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_address' => [
				'columns' => [
					'srecurringordersaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['srecurringorders_address_pk', 'srecurringordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['srecurringorders_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['srecurringorders_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorderscf' => [
				'columns' => [
					'srecurringordersid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['srecurringorderscf_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards' => [
				'columns' => [
					'srequirementscardsid' => $this->integer()->notNull()->defaultValue(0),
					'srequirementscards_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'quoteenquiryid' => $this->integer(),
					'category' => $this->stringType(),
					'srequirementscards_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['srequirementscards_salesprocessid_idx', 'salesprocessid'],
					['srequirementscards_accountid_idx', 'accountid'],
					['srequirementscards_quoteenquiryid_idx', 'quoteenquiryid'],
				],
				'primaryKeys' => [
					['srequirementscards_pk', 'srequirementscardsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['srequirementscards_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['srequirementscards_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscardscf' => [
				'columns' => [
					'srequirementscardsid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['srequirementscardscf_pk', 'srequirementscardsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocesses' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer()->notNull()->defaultValue(0),
					'ssalesprocesses_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'category' => $this->stringType(),
					'related_to' => $this->integer(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'estimated' => $this->decimal('25,8'),
					'actual_sale' => $this->decimal('25,8'),
					'estimated_date' => $this->date(),
					'actual_date' => $this->date(),
					'probability' => $this->decimal('5,2'),
					'ssalesprocesses_source' => $this->stringType(),
					'ssalesprocesses_type' => $this->stringType(),
					'ssalesprocesses_status' => $this->stringType(),
					'campaignid' => $this->integer(),
					'parentid' => $this->integer(),
					'startdate' => $this->date(),
				],
				'index' => [
					['ssalesprocesses_rel_idx', 'related_to'],
					['ssalesprocesses_cam_idx', 'campaignid'],
					['ssalesprocesses_parent_idx', 'parentid'],
				],
				'primaryKeys' => [
					['ssalesprocesses_pk', 'ssalesprocessesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocessescf' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['ssalesprocessescf_pk', 'ssalesprocessesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders' => [
				'columns' => [
					'ssingleordersid' => $this->integer()->notNull()->defaultValue(0),
					'ssingleorders_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'squotesid' => $this->integer(),
					'category' => $this->stringType(),
					'ssingleorders_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'duedate' => $this->date(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('27,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('27,8'),
					'sum_gross' => $this->decimal('27,8'),
					'sum_discount' => $this->decimal('27,8'),
					'ssingleorders_source' => $this->stringType()->defaultValue(''),
				],
				'index' => [
					['ssingleorders_salesprocessid_idx', 'salesprocessid'],
					['ssingleorders_squotesid_idx', 'squotesid'],
					['ssingleorders_accountid_idx', 'accountid'],
				],
				'primaryKeys' => [
					['ssingleorders_pk', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_address' => [
				'columns' => [
					'ssingleordersaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'primaryKeys' => [
					['ssingleorders_address_pk', 'ssingleordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'comment1' => $this->text(),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['ssingleorders_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['ssingleorders_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorderscf' => [
				'columns' => [
					'ssingleordersid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['ssingleorderscf_pk', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries' => [
				'columns' => [
					'svendorenquiriesid' => $this->integer()->notNull()->defaultValue(0),
					'svendorenquiries_no' => $this->stringType(50)->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(),
					'category' => $this->stringType(30),
					'svendorenquiries_status' => $this->stringType(),
					'accountid' => $this->integer(),
					'response_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('27,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'vendorid' => $this->integer(),
					'scalculationsid' => $this->integer(),
				],
				'index' => [
					['svendorenquiries_salesprocessid_idx', 'salesprocessid'],
					['svendorenquiries_accountid_idx', 'accountid'],
					['svendorenquiries_vendorid_idx', 'vendorid'],
					['svendorenquiries_scalculationsid_idx', 'scalculationsid'],
				],
				'primaryKeys' => [
					['svendorenquiries_pk', 'svendorenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries_inventory' => [
				'columns' => [
					'id' => $this->integer(),
					'seq' => $this->integer(10),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'qtyparam' => $this->smallInteger(1)->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['svendorenquiries_inventory_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'invtype' => $this->stringType(30)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->smallInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries_invmap' => [
				'columns' => [
					'module' => $this->stringType(50)->notNull(),
					'field' => $this->stringType(50)->notNull(),
					'tofield' => $this->stringType(50)->notNull(),
				],
				'primaryKeys' => [
					['svendorenquiries_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiriescf' => [
				'columns' => [
					'svendorenquiriesid' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['svendorenquiriescf_pk', 'svendorenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__timeline' => [
				'columns' => [
					'crmid' => $this->integer(11)->notNull(),
					'type' => $this->stringType(50),
					'type' => $this->integer(11)->notNull(),
				],
				'index' => [
					['timeline_crmid_idx', 'crmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_module' => [
				'columns' => [
					'member' => $this->stringType(50)->notNull(),
					'module' => $this->integer()->unsigned()->notNull(),
					'lock' => $this->smallInteger(1)->defaultValue(0),
					'exceptions' => $this->text(),
				],
				'index' => [
					['watchdog_module_idx', 'member'],
				],
				'primaryKeys' => [
					['watchdog_module_pk', ['member', 'module']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_record' => [
				'columns' => [
					'userid' => $this->integer()->unsigned()->notNull(),
					'record' => $this->integer()->notNull(),
					'state' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['watchdog_record_userid_idx', 'userid'],
					['watchdog_record_record_idx', 'record'],
					['watchdog_record_state_idx', ['userid', 'record', 'state']],
				],
				'primaryKeys' => [
					['watchdog_record_pk', ['userid', 'record']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_schedule' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'frequency' => $this->smallInteger()->notNull(),
					'last_execution' => $this->dateTime(),
					'modules' => $this->text(),
				],
				'primaryKeys' => [
					['watchdog_schedule_pk', 'userid']
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
			'com_vtiger_workflow_tasktypes' => [
				'columns' => ['id', 'tasktypename', 'label', 'classname', 'classpath', 'templatepath', 'modules', 'sourcemodule'],
				'values' => [
					[1, 'VTEmailTask', 'Send Mail', 'VTEmailTask', 'modules/com_vtiger_workflow/tasks/VTEmailTask.php', 'com_vtiger_workflow/taskforms/VTEmailTask.tpl', '{"include":[],"exclude":[]}', ''],
					[2, 'VTEntityMethodTask', 'Invoke Custom Function', 'VTEntityMethodTask', 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php', 'com_vtiger_workflow/taskforms/VTEntityMethodTask.tpl', '{"include":[],"exclude":[]}', ''],
					[3, 'VTCreateTodoTask', 'Create Todo', 'VTCreateTodoTask', 'modules/com_vtiger_workflow/tasks/VTCreateTodoTask.php', 'com_vtiger_workflow/taskforms/VTCreateTodoTask.tpl', '{"include":["Accounts","Leads","Contacts","HelpDesk","Campaigns","Project","ServiceContracts","Vendors","Partners","Competition","OSSEmployees","SSalesProcesses","SQuoteEnquiries","SRequirementsCards","SCalculations","SQuotes","SSingleOrders","SRecurringOrders"],"exclude":["Calendar","FAQ","Events"]}', ''],
					[4, 'VTCreateEventTask', 'Create Event', 'VTCreateEventTask', 'modules/com_vtiger_workflow/tasks/VTCreateEventTask.php', 'com_vtiger_workflow/taskforms/VTCreateEventTask.tpl', '{"include":["Accounts","Leads","Contacts","HelpDesk","Campaigns","Project","ServiceContracts","Vendors","Partners","Competition","OSSEmployees","SSalesProcesses","SQuoteEnquiries","SRequirementsCards","SCalculations","SQuotes","SSingleOrders","SRecurringOrders"],"exclude":["Calendar","FAQ","Events"]}', ''],
					[5, 'VTUpdateFieldsTask', 'Update Fields', 'VTUpdateFieldsTask', 'modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.php', 'com_vtiger_workflow/taskforms/VTUpdateFieldsTask.tpl', '{"include":[],"exclude":[]}', ''],
					[6, 'VTCreateEntityTask', 'Create Entity', 'VTCreateEntityTask', 'modules/com_vtiger_workflow/tasks/VTCreateEntityTask.php', 'com_vtiger_workflow/taskforms/VTCreateEntityTask.tpl', '{"include":[],"exclude":[]}', ''],
					[7, 'VTSMSTask', 'SMS Task', 'VTSMSTask', 'modules/com_vtiger_workflow/tasks/VTSMSTask.php', 'com_vtiger_workflow/taskforms/VTSMSTask.tpl', '{"include":[],"exclude":[]}', 'SMSNotifier'],
					[8, 'VTEmailTemplateTask', 'Email Template Task', 'VTEmailTemplateTask', 'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.php', 'com_vtiger_workflow/taskforms/VTEmailTemplateTask.tpl', '{"include":[],"exclude":[]}', NULL],
					[9, 'VTSendPdf', 'Send Pdf', 'VTSendPdf', 'modules/com_vtiger_workflow/tasks/VTSendPdf.php', 'com_vtiger_workflow/taskforms/VTSendPdf.tpl', '{"include":[],"exclude":[]}', NULL],
					[10, 'VTUpdateClosedTime', 'Update Closed Time', 'VTUpdateClosedTime', 'modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.php', 'com_vtiger_workflow/taskforms/VTUpdateClosedTime.tpl', '{"include":[],"exclude":[]}', NULL],
					[11, 'VTSendNotificationTask', 'Send Notification', 'VTSendNotificationTask', 'modules/com_vtiger_workflow/tasks/VTSendNotificationTask.php', 'com_vtiger_workflow/taskforms/VTSendNotificationTask.tpl', '{"include":["Calendar","Events"],"exclude":[]}', NULL],
					[12, 'VTAddressBookTask', 'Create Address Book', 'VTAddressBookTask', 'modules/com_vtiger_workflow/tasks/VTAddressBookTask.php', 'com_vtiger_workflow/taskforms/VTAddressBookTask.tpl', '{"include":["Contacts","OSSEmployees","Accounts","Leads","Vendors"],"exclude":[]}', NULL],
					[13, 'VTUpdateCalendarDates', 'LBL_UPDATE_DATES_CREATED_EVENTS_AUTOMATICALLY', 'VTUpdateCalendarDates', 'modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.php', 'com_vtiger_workflow/taskforms/VTUpdateCalendarDates.tpl', '{"include":["Accounts","Contacts","Leads","OSSEmployees","Vendors","Campaigns","HelpDesk","Project","ServiceContracts"],"exclude":["Calendar","FAQ","Events"]}', NULL],
					[14, 'VTUpdateWorkTime', 'LBL_UPDATE_WORK_TIME_AUTOMATICALLY', 'VTUpdateWorkTime', 'modules/com_vtiger_workflow/tasks/VTUpdateWorkTime.php', 'com_vtiger_workflow/taskforms/VTUpdateWorkTime.tpl', '{"include":["OSSTimeControl"],"exclude":[]}', NULL],
					[15, 'VTUpdateRelatedFieldTask', 'LBL_UPDATE_RELATED_FIELD', 'VTUpdateRelatedFieldTask', 'modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.php', 'com_vtiger_workflow/taskforms/VTUpdateRelatedFieldTask.tpl', '{"include":[],"exclude":[]}', ''],
					[16, 'VTWatchdog', 'LBL_NOTIFICATIONS', 'VTWatchdog', 'modules/com_vtiger_workflow/tasks/VTWatchdog.php', 'com_vtiger_workflow/taskforms/VTWatchdog.tpl', '{"include":[],"exclude":[]}', NULL],
					[17, 'VTAutoAssign', 'LBL_AUTO_ASSIGN', 'VTAutoAssign', 'modules/com_vtiger_workflow/tasks/VTAutoAssign.php', 'com_vtiger_workflow/taskforms/VTAutoAssign.tpl', '{"include":[],"exclude":[]}', NULL],
				]
			],
			'com_vtiger_workflow_tasktypes_seq' => [
				'columns' => ['id'],
				'values' => [
					[17],
				]
			],
			'com_vtiger_workflows' => [
				'columns' => ['workflow_id', 'module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew', 'schtypeid', 'schdayofmonth', 'schdayofweek', 'schannualdates', 'schtime', 'nexttrigger_time'],
				'values' => [
					[13, 'Events', 'Workflow for Events when Send Notification is True', '[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, 1, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[14, 'Calendar', 'Workflow for Calendar Todos when Send Notification is True', '[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, 1, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[25, 'HelpDesk', 'Ticket change: Send Email to Record Owner', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[26, 'HelpDesk', 'Ticket change: Send Email to Record Contact', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[27, 'HelpDesk', 'Ticket change: Send Email to Record Account', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[28, 'HelpDesk', 'Ticket Closed: Send Email to Record Owner', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[29, 'HelpDesk', 'Ticket Closed: Send Email to Record Contact', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[30, 'HelpDesk', 'Ticket Closed: Send Email to Record Account', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[31, 'HelpDesk', 'Ticket Creation: Send Email to Record Owner', '[]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[33, 'HelpDesk', 'Ticket Creation: Send Email to Record Account', '[{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[53, 'Contacts', 'Send Customer Login Details', '[{"fieldname":"emailoptout","operation":"is","value":"1","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[54, 'HelpDesk', 'Update Closed Time', '[{"fieldname":"ticketstatus","operation":"is","value":"Rejected","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[55, 'Contacts', 'Generate mail address book', '[]', 3, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[57, 'ModComments', 'New comment added to ticket - Owner', '[{"fieldname":"customer","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[58, 'ModComments', 'New comment added to ticket - account', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[59, 'ModComments', 'New comment added to ticket - contact', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					[63, 'SQuoteEnquiries', 'Block edition', '[{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', NULL],
					[64, 'SRequirementsCards', 'Block edition', '[{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', NULL],
					[65, 'SCalculations', 'Block edition', '[{"fieldname":"scalculations_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"scalculations_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', NULL],
					[66, 'SQuotes', 'Block edition', '[{"fieldname":"squotes_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squotes_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', NULL],
					[67, 'SSingleOrders', 'Block edition', '[{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', NULL],
					[68, 'SRecurringOrders', 'Block edition', '[{"fieldname":"srecurringorders_status","operation":"is","value":"PLL_UNREALIZED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srecurringorders_status","operation":"is","value":"PLL_REALIZED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', NULL],
					[69, 'OSSTimeControl', 'LBL_UPDATE_WORK_TIME', '[]', 7, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
				]
			],
			'com_vtiger_workflowtasks' => [
				'columns' => ['task_id', 'workflow_id', 'summary', 'task'],
				'values' => [
					[106, 33, 'Notify Account On Ticket Create', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"33";s:7:"summary";s:31:"Notify Account On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"40";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:106;}'],
					[108, 31, 'Notify Owner On Ticket Create', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"31";s:7:"summary";s:29:"Notify Owner On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"43";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:108;}'],
					[109, 30, 'Notify Account On Ticket Closed', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"30";s:7:"summary";s:31:"Notify Account On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"38";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:109;}'],
					[111, 28, 'Notify Owner On Ticket Closed', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"28";s:7:"summary";s:29:"Notify Owner On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"42";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:111;}'],
					[112, 27, 'Notify Account On Ticket Change', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"27";s:7:"summary";s:31:"Notify Account On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"36";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:112;}'],
					[114, 25, 'Notify Owner On Ticket Change', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"25";s:7:"summary";s:29:"Notify Owner On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"35";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:114;}'],
					[119, 14, 'Notification Email to Record Owner', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"14";s:7:"summary";s:34:"Notification Email to Record Owner";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"46";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:119;}'],
					[120, 53, 'Send Customer Login Details', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"53";s:7:"summary";s:27:"Send Customer Login Details";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"44";s:11:"attachments";s:0:"";s:5:"email";s:5:"email";s:10:"copy_email";s:0:"";s:2:"id";i:120;}'],
					[121, 54, 'Update Closed Time', 'O:18:"VTUpdateClosedTime":6:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"54";s:7:"summary";s:18:"Update Closed Time";s:6:"active";b:0;s:7:"trigger";N;s:2:"id";i:121;}'],
					[122, 13, 'Send invitations', 'O:22:"VTSendNotificationTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"13";s:7:"summary";s:16:"Send invitations";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"45";s:2:"id";i:122;}'],
					[123, 55, 'Generate mail address book', 'O:17:"VTAddressBookTask":7:{s:18:"executeImmediately";b:0;s:10:"workflowId";s:2:"55";s:7:"summary";s:26:"Generate mail address book";s:6:"active";b:1;s:7:"trigger";N;s:4:"test";s:0:"";s:2:"id";i:123;}'],
					[133, 26, 'Notify Contact On Ticket Change', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"26";s:7:"summary";s:31:"Notify Contact On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"HeldDeskChangeNotifyContacts";s:2:"id";i:133;}'],
					[134, 29, 'Notify contacts about closing of ticket.', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"29";s:7:"summary";s:40:"Notify contacts about closing of ticket.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"HeldDeskClosedNotifyContacts";s:2:"id";i:134;}'],
					[135, 59, 'Notify Contact On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:59;s:7:"summary";s:45:"Notify Contact On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:26:"HeldDeskNewCommentContacts";s:2:"id";i:135;}'],
					[136, 58, 'Notify Account On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:58;s:7:"summary";s:45:"Notify Account On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:25:"HeldDeskNewCommentAccount";s:2:"id";i:136;}'],
					[137, 57, 'Notify Owner On new comment added to ticket from portal', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:57;s:7:"summary";s:55:"Notify Owner On new comment added to ticket from portal";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:23:"HeldDeskNewCommentOwner";s:2:"id";i:137;}'],
					[138, 69, 'Update working time', 'O:16:"VTUpdateWorkTime":6:{s:18:"executeImmediately";b:0;s:10:"workflowId";i:69;s:7:"summary";s:19:"Update working time";s:6:"active";b:1;s:7:"trigger";N;s:2:"id";i:138;}'],
				]
			],
			'com_vtiger_workflowtasks_entitymethod' => [
				'columns' => ['workflowtasks_entitymethod_id', 'module_name', 'method_name', 'function_path', 'function_name'],
				'values' => [
					[8, 'ModComments', 'HeldDeskNewCommentAccount', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskNewCommentAccount'],
					[9, 'ModComments', 'HeldDeskNewCommentContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskNewCommentContacts'],
					[15, 'HelpDesk', 'HeldDeskChangeNotifyContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskChangeNotifyContacts'],
					[16, 'HelpDesk', 'HeldDeskClosedNotifyContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskClosedNotifyContacts'],
					[17, 'ModComments', 'HeldDeskNewCommentOwner', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskNewCommentOwner'],
				]
			],
			'com_vtiger_workflowtasks_entitymethod_seq' => [
				'columns' => ['id'],
				'values' => [
					[17],
				]
			],
			'com_vtiger_workflowtasks_seq' => [
				'columns' => ['id'],
				'values' => [
					[138],
				]
			],
			'roundcube_system' => [
				'columns' => ['name', 'value'],
				'values' => [
					['roundcube-version', '2016011900'],
				]
			],
			'u_#__dashboard_type' => [
				'columns' => ['dashboard_id', 'name', 'system'],
				'values' => [
					[1, 'LBL_MAIN_PAGE', 1],
				]
			],
			'u_#__emailtemplates' => [
				'columns' => ['emailtemplatesid', 'name', 'number', 'email_template_type', 'module', 'subject', 'content', 'sys_name', 'email_template_priority'],
				'values' => [
					[35, 'Notify Owner On Ticket Change', 'N1', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticket_no)$:$(record : ticket_title)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(translate : HelpDesk|LBL_NOTICE_UPDATED)$ $(record : modifiedby)$ $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Solution)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : solution)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[36, 'Notify Account On Ticket Change', 'N2', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_COPY_BILLING_ADDRESS)$  $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|LBL_NOTICE_UPDATED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$. $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;"><a href="$(record%20%3A%20PortalDetailViewURL)$">$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$</a></td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Solution)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : solution)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 <p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[37, 'Notify Contact On Ticket Closed', 'N3', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CLOSE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|LBL_NOTICE_CLOSED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$ <a href="$(record%20%3A%20PortalDetailViewURL)$">$(record : ticket_no)$:$(record : ticket_title)$</a> . $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Solution)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : solution)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'NotifyContactOnTicketClosed', 1],
					[38, 'Notify Account On Ticket Closed', 'N4', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CLOSE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_CLOSED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;"><a href="$(record%20%3A%20PortalDetailViewURL)$">$(record : ticket_no)$:$(record : ticket_title)$</a> $(record : modifiedby)$. $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Solution)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : solution)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[39, 'Notify Contact On Ticket Create', 'N5', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_CREATE)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">.<a href="$(record%20%3A%20PortalDetailViewURL)$"> $(record : ticket_no)$:$(record : ticket_title)$</a></td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'NotifyContactOnTicketCreate', 1],
					[40, 'Notify Account On Ticket Create', 'N6', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_CREATE)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$ <a href="$(record%20%3A%20PortalDetailViewURL)$">$(record : ticket_no)$:$(record : ticket_title)$</a></td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[41, 'Notify Contact On Ticket Change', 'N7', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_MODIFICATION)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_UPDATED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$ <a href="$(record%20%3A%20PortalDetailViewURL)$">$(record : ticket_no)$:$(record : ticket_title)$</a> $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Solution)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : solution)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<br><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'NotifyContactOnTicketChange', 1],
					[42, 'Notify Owner On Ticket Closed', 'N8', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CLOSE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_CLOSED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$<a href="$(record%20%3A%20CrmDetailViewURL)$"> $(record : ticket_no)$:$(record : ticket_title)$</a> $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Solution)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : solution)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[43, 'Notify Owner On Ticket Create', 'N9', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_CREATED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$. <a href="$(record%20%3A%20PortalDetailViewURL)$"> $(record : ticket_no)$:$(record : ticket_title)$</a> $(record : ChangesListChanges)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketstatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : ticketpriorities)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Related To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : parent_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<br><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>

		</tr></table>', NULL, 1],
					[44, 'Customer Portal Login Details', 'N10', 'PLL_RECORD', 'Contacts', 'Customer Portal Login Details', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(record : first_name)$ $(record : last_name)$,
<br>
Created for your account in the customer portal, below sending data access<br>
						Login: $(record : email)$<br>
						Password: 
						</td>
			
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<br><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[45, 'Send invitations', 'N11', 'PLL_RECORD', 'Events', '$(record : activitytype)$ $(record : subject)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(record : subject)$</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Events|</i>Start Date & Time<i>)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : date_start)$ $(record : time_start)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|End Time)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : due_date)$ $(record : time_end)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_PROCESS)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : process)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_RELATION)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : link)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Activity Type)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : activitytype)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : taskpriority)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Location)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;"><span><span>$(record : location)$</span><span> (<a href="https://maps.google.pl/maps?q=$(record%20:%20location)$">mapa</a>)</span></span></td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : activitystatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Assigned To)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : assigned_user_id)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[46, 'Send Notification Email to Record Owner', 'N12', 'PLL_RECORD', 'Calendar', 'Task : $(record : subject)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>Activity Notification Details</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Subject)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : subject)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Start Date & Time)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : date_start)$ $(record : time_start)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|End Time)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : due_date) $(record : time_end)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Status)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : activitystatus)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Priority)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : taskpriority)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_PROCESS)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : process)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_RELATION)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : link)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Location)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : location)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', NULL, 1],
					[93, 'Activity Reminder Notification', 'N13', 'PLL_RECORD', 'Calendar', 'Reminder:  $(record : subject)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>This is a reminder notification for the Activity</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Subject)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : subject)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Start Date & Time)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : date_start)$ $(record : time_start)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_RELATION)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : link)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_PROCESS)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : process)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'ActivityReminderNotificationTask', 1],
					[94, 'Activity Reminder Notification', 'N14', 'PLL_RECORD', 'Events', 'Reminder: $(record : subject)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>This is a reminder notification for the Activity</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Subject)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : subject)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Start Date & Time)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : date_start)$ $(record : time_start)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_RELATION)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : link)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|FL_PROCESS)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : process)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Calendar|Description)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : description)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'ActivityReminderNotificationEvents', 1],
					[95, 'Test mail about the mail server configuration.', 'N15', 'PLL_RECORD', 'Users', 'Test mail about the mail server configuration.', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(record : first_name)$ $(record : last_name)$,<br>
						This is a test mail sent to confirm if a mail is actually being sent through the smtp server that you have configured. Feel free to delete this mail. CRM address: $(general : SiteUrl)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<br><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'TestMailAboutTheMailServerConfiguration', 1],
					[103, 'ForgotPassword', 'N16', 'PLL_RECORD', 'Users', 'Request: ForgotPassword', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear user,<br>
						You recently requested a password reset for your YetiForce CRM. To create a new password, click on the link $(custom : UsersLinkToForgotPassword|Users)$ This request was made on $(general : CurrentDate)$ $(general : CurrentTime)$ and will expire in next 24 hours.</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'UsersForgotPassword', 1],
					[104, 'Customer Portal - ForgotPassword', 'N17', 'PLL_RECORD', 'Contacts', 'Request: ForgotPassword', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(record : first_name)$ $(record : last_name)$,<br>
						You recently requested a reminder of your access data for the YetiForce Portal.<br>
						You can login by entering the following data:<br><br>
						Your username: $(record : email)$<br>
						Your password: </td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'YetiPortalForgotPassword', 1],
					[105, 'Notify Owner On new comment added to ticket from portal', 'N18', 'PLL_RECORD', 'ModComments', '$(translate : ModComments|LBL_ADDED_COMMENT_TO_TICKET)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : ModComments|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o.</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : ModComments|LBL_NEW_COMMENT_FOR_TICKET)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(translate : ModComments|LBL_NOTICE_CREATED)$ $(record : customer)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div>$(translate : ModComments|Comment)$</div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : commentcontent)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'NewCommentAddedToTicketOwner', 1],
					[106, 'Notify Contact On New comment added to ticket', 'N19', 'PLL_RECORD', 'ModComments', '$(translate : ModComments|LBL_ADDED_COMMENT_TO_TICKET)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : ModComments|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o.</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : ModComments|LBL_NEW_COMMENT_FOR_TICKET)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(translate : ModComments|LBL_NOTICE_CREATED)$ $(record : created_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : ModComments|Comment)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : commentcontent)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'NewCommentAddedToTicketContact', 1],
					[107, 'Security risk has been detected - Brute Force', 'N20', 'PLL_RECORD', 'Contacts', 'Security risk has been detected', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear user,<br>
						Failed login attempts have been detected.</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'BruteForceSecurityRiskHasBeenDetected', 1],
					[109, 'Notify Account On New comment added to ticket', 'N21', 'PLL_RECORD', 'ModComments', '$(translate : ModComments|LBL_ADDED_COMMENT_TO_TICKET)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : ModComments|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o.</span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : ModComments|LBL_NEW_COMMENT_FOR_TICKET)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(translate : ModComments|LBL_NOTICE_CREATED)$ $(record : created_user_id)$</td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : ModComments|Comment)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : commentcontent)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'NewCommentAddedToTicketAccount', 1],
					[110, 'Send notifications', 'N22', 'PLL_RECORD', 'Notification', 'Notifications $(general : CurrentDate)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(custom : Notifications|Notification)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'SendNotificationsViaMail', 1],
					[111, 'Schedule Reprots', 'N23', 'PLL_RECORD', 'Reports', '$(params : reportName)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;">$(translate : Reports|LBL_AUTO_GENERATED_REPORT_EMAIL)$</h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Reports|LBL_REPORT_NAME)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;"><a href="$(params%20%3A%20reportUrl)$">$(params : reportName)$</a></td>
					</tr><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : Reports|LBL_DESCRIPTION)$</i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(params : reportDescritpion)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			 

			<p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'ScheduleReprots', 1],
				]
			],
			'u_#__fcorectinginvoice_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 20],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					[4, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 11, 2, 0, '{}', 0],
					[5, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, '', 7],
					[6, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 13, 0, 0, '', 7],
					[7, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 14, 0, 0, '', 7],
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, '', 7],
					[9, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, '', 7],
					[10, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, '', 7],
					[11, 'tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, '', 7],
					[12, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, '', 7],
					[13, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
				]
			],
			'u_#__fcorectinginvoice_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__finvoice_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 20],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					[4, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 11, 2, 0, '{}', 0],
					[5, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, NULL, 7],
					[6, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 13, 0, 0, NULL, 7],
					[7, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 14, 0, 0, NULL, 7],
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 7],
					[9, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, NULL, 7],
					[10, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, NULL, 7],
					[12, 'tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, NULL, 7],
					[13, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 7],
					[14, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
					[15, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
				]
			],
			'u_#__finvoice_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__finvoicecost_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 20],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					[4, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 11, 2, 0, '{}', 0],
					[5, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, '', 7],
					[6, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 13, 0, 0, '', 7],
					[7, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 14, 0, 0, '', 7],
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, '', 7],
					[9, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, '', 7],
					[10, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, '', 7],
					[11, 'tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, '', 7],
					[12, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, '', 7],
					[13, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, '', 7],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, '', 7],
				]
			],
			'u_#__finvoiceproforma_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 1, 0, 0, '', 1],
					[2, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 2, 0, 0, '', 1],
					[3, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 3, 0, 0, '', 1],
					[4, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 30],
					[5, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					[6, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, '{}', 10],
					[7, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, '{}', 10],
					[8, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 10],
					[9, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, '{}', 10],
					[10, 'tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, '{}', 10],
					[11, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, '{}', 10],
					[12, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 12, 2, 0, '{}', 0],
					[13, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_#__finvoiceproforma_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__github' => [
				'columns' => ['github_id', 'client_id', 'token', 'username'],
				'values' => [
					[1, '', '', ''],
				]
			],
			'u_#__igdn_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[4, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[5, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__igdn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__igdnc_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[4, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[5, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, '', 10],
				]
			],
			'u_#__igdnc_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__igin_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[6, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[7, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[9, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[10, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[11, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					[12, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					[13, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__igin_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__igrn_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[4, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[5, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__igrn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__igrnc_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[4, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[5, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, '', 10],
				]
			],
			'u_#__igrnc_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__iidn_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[4, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					[5, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					[6, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[7, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__iidn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__ipreorder_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[4, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '{}', 5],
					[6, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[7, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[8, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '{}', 12],
					[9, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '{}', 15],
					[10, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__ipreorder_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__istdn_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[4, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[5, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__istdn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__istrn_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					[3, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[4, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[5, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_#__istrn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__openstreetmap_address_updater' => [
				'columns' => ['crmid'],
				'values' => [
					[0],
				]
			],
			'u_#__scalculations_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 40],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					[4, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					[5, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 10],
					[6, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 10],
					[7, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 6, 1, 0, NULL, 10],
					[8, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 7, 1, 0, NULL, 10],
					[9, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 8, 1, 0, NULL, 10],
					[10, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					[11, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_#__scalculations_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__squoteenquiries_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 50],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 30],
					[3, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					[4, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					[5, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_#__squoteenquiries_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__squotes_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 10],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					[4, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 9, 1, 0, '{}', 10],
					[5, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 10, 1, 0, '{}', 7],
					[6, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 6, 2, 0, '{}', 0],
					[7, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 7],
					[8, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 7],
					[9, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 8, 1, 0, NULL, 7],
					[10, 'tax', 'LBL_TAX', 'Tax', 0, '0', 11, 1, 0, NULL, 7],
					[11, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 12, 1, 0, NULL, 7],
					[12, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 10, 0, 0, NULL, 1],
					[13, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 11, 0, 0, NULL, 1],
					[14, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, NULL, 1],
					[15, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, NULL, 7],
					[16, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
					[17, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
				]
			],
			'u_#__squotes_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__srecurringorders_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 30],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 4, 1, 0, '{}', 10],
					[4, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 5, 1, 0, '{}', 10],
					[5, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 6, 1, 0, '{}', 10],
					[6, 'tax', 'LBL_TAX', 'Tax', 0, '0', 7, 1, 0, '{}', 10],
					[7, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 7, 2, 0, '{}', 0],
					[8, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_#__srecurringorders_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__srequirementscards_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 50],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 30],
					[3, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					[4, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					[5, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_#__srequirementscards_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__ssingleorders_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 15],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					[4, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 9, 1, 0, '{}', 10],
					[5, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 10, 1, 0, '{}', 7],
					[6, 'tax', 'LBL_TAX', 'Tax', 0, '0', 11, 1, 0, '{}', 7],
					[7, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 7, 2, 0, '{}', 0],
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 7],
					[9, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 7],
					[10, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, NULL, 7],
					[11, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 8, 1, 0, NULL, 7],
					[12, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 12, 1, 0, NULL, 7],
					[13, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 11, 0, 0, NULL, 1],
					[14, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 12, 0, 0, NULL, 1],
					[15, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 13, 0, 0, NULL, 1],
					[16, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
					[17, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
				]
			],
			'u_#__ssingleorders_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_#__svendorenquiries_invfield' => [
				'columns' => ['id', 'columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 40],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					[3, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					[4, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, '', 10],
					[5, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, '', 10],
					[6, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 6, 1, 0, '', 10],
					[7, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 7, 1, 0, '', 10],
					[8, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 8, 1, 0, '', 10],
					[9, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, '', 10],
					[10, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, '', 10],
				]
			],
		];
	}
}
