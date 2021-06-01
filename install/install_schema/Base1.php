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
			'com_vtiger_workflow_activatedonce' => [
				'columns' => [
					'workflow_id' => $this->integer(10)->notNull(),
					'entity_id' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['workflow_activatedonce_pk', ['workflow_id', 'entity_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_tasktypes' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'tasktypename' => $this->stringType()->notNull(),
					'label' => $this->stringType(),
					'classname' => $this->stringType(),
					'classpath' => $this->stringType(),
					'templatepath' => $this->stringType(),
					'modules' => $this->stringType(500),
					'sourcemodule' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflows' => [
				'columns' => [
					'workflow_id' => $this->primaryKey(10),
					'module_name' => $this->stringType(100),
					'summary' => $this->stringType(400)->notNull(),
					'test' => $this->text(),
					'execution_condition' => $this->integer(10)->notNull(),
					'defaultworkflow' => $this->integer(1),
					'type' => $this->stringType(),
					'filtersavedinnew' => $this->integer(1),
					'schtypeid' => $this->integer(10),
					'schdayofmonth' => $this->stringType(100),
					'schdayofweek' => $this->stringType(100),
					'schannualdates' => $this->stringType(100),
					'schtime' => $this->stringType(50),
					'nexttrigger_time' => $this->dateTime(),
					'params' => $this->text(),
				],
				'index' => [
					['com_vtiger_workflows_idx', 'workflow_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtask_queue' => [
				'columns' => [
					'task_id' => $this->integer(10),
					'entity_id' => $this->stringType(100),
					'do_after' => $this->integer(10),
					'task_contents' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks' => [
				'columns' => [
					'task_id' => $this->primaryKey(10),
					'workflow_id' => $this->integer(10),
					'summary' => $this->stringType(400)->notNull(),
					'task' => $this->text(),
				],
				'index' => [
					['workflow_id', 'workflow_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_entitymethod' => [
				'columns' => [
					'workflowtasks_entitymethod_id' => $this->primaryKey(10),
					'module_name' => $this->stringType(100),
					'method_name' => $this->stringType(100),
					'function_path' => $this->stringType(400),
					'function_name' => $this->stringType(100),
				],
				'index' => [
					['com_vtiger_workflowtasks_entitymethod_idx', 'workflowtasks_entitymethod_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtemplates' => [
				'columns' => [
					'template_id' => $this->primaryKey(10),
					'module_name' => $this->stringType(100),
					'title' => $this->stringType(400),
					'template' => $this->text(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_addressbookchanges' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'synctoken' => $this->integer(10)->unsigned()->notNull(),
					'addressbookid' => $this->integer(10)->unsigned()->notNull(),
					'operation' => $this->smallInteger(1)->notNull(),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'operation' => $this->tinyInteger(1)->notNull(),
				],
				'index' => [
					['addressbookid_synctoken', ['addressbookid', 'synctoken']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_addressbooks' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'principaluri' => $this->stringType(),
					'displayname' => $this->stringType(),
					'uri' => $this->stringType(200),
					'description' => $this->text(),
					'synctoken' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(),
					'uri' => $this->varbinary(200),
				],
				'index' => [
					['principaluri', ['principaluri', 'uri']],
					['dav_addressbooks_idx', 'principaluri'],
				],
				'index_mysql' => [
					['principaluri', ['principaluri(100)', 'uri(100)'], true],
					['dav_addressbooks_idx', 'principaluri(100)'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarchanges' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'synctoken' => $this->integer(10)->unsigned()->notNull(),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'operation' => $this->smallInteger(1)->notNull(),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'operation' => $this->tinyInteger(1)->notNull(),
				],
				'index' => [
					['calendarid_synctoken', ['calendarid', 'synctoken']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarinstances' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'principaluri' => $this->stringType(100),
					'access' => $this->smallInteger(1)->notNull()->defaultValue(1),
					'displayname' => $this->stringType(100),
					'uri' => $this->stringType(200),
					'description' => $this->text(),
					'calendarorder' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'timezone' => $this->text(),
					'transparent' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'share_href' => $this->stringType(100),
					'share_displayname' => $this->stringType(100),
					'share_invitestatus' => $this->smallInteger(1)->notNull()->defaultValue(2),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(100),
					'access' => $this->tinyInteger(1)->notNull()->defaultValue(1),
					'uri' => $this->varbinary(200),
					'calendarcolor' => $this->varbinary(10),
					'transparent' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'share_href' => $this->varbinary(100),
					'share_invitestatus' => $this->tinyInteger(1)->notNull()->defaultValue(2),
				],
				'index' => [
					['principaluri', ['principaluri', 'uri']],
					['calendarid', ['calendarid', 'principaluri']],
					['calendarid_2', ['calendarid', 'share_href']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarobjects' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'calendardata' => $this->binary(),
					'uri' => $this->stringType(200),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'lastmodified' => $this->integer(10)->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer(10)->unsigned()->notNull(),
					'componenttype' => $this->stringType(8),
					'firstoccurence' => $this->integer(10)->unsigned(),
					'lastoccurence' => $this->integer(10)->unsigned(),
					'uid' => $this->stringType(200),
					'crmid' => $this->integer(10),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200),
					'etag' => $this->varbinary(32),
					'componenttype' => $this->varbinary(8),
					'uid' => $this->varbinary(200),
				],
				'index' => [
					['calendarid', ['calendarid', 'uri']],
					['uri', 'uri'],
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendars' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'synctoken' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'components' => $this->stringType(21),
				],
				'columns_mysql' => [
					'components' => $this->varbinary(21),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_calendarsubscriptions' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'principaluri' => $this->stringType(100)->notNull(),
					'source' => $this->text(),
					'displayname' => $this->stringType(100),
					'refreshrate' => $this->stringType(10),
					'calendarorder' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->stringType(10),
					'striptodos' => $this->smallInteger(1),
					'stripalarms' => $this->smallInteger(1),
					'stripattachments' => $this->smallInteger(1),
					'lastmodified' => $this->integer(10)->unsigned(),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'principaluri' => $this->varbinary(100)->notNull(),
					'calendarcolor' => $this->varbinary(10),
					'striptodos' => $this->tinyInteger(1),
					'stripalarms' => $this->tinyInteger(1),
					'stripattachments' => $this->tinyInteger(1),
				],
				'index' => [
					['principaluri', ['principaluri', 'uri']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_cards' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'addressbookid' => $this->integer(10)->unsigned()->notNull(),
					'carddata' => $this->binary(),
					'uri' => $this->stringType(200),
					'lastmodified' => $this->integer(10)->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer(10)->unsigned()->notNull(),
					'crmid' => $this->integer(10)->defaultValue(0),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200),
					'etag' => $this->varbinary(32),
				],
				'index' => [
					['addressbookid', 'addressbookid'],
					['crmid', 'crmid'],
					['uri', 'uri'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_groupmembers' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'principal_id' => $this->integer(10)->unsigned()->notNull(),
					'member_id' => $this->integer(10)->unsigned()->notNull(),
				],
				'index' => [
					['principal_id', ['principal_id', 'member_id']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_principals' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'uri' => $this->stringType(200)->notNull(),
					'email' => $this->stringType(80),
					'displayname' => $this->stringType(80),
					'userid' => $this->integer(10),
				],
				'columns_mysql' => [
					'uri' => $this->varbinary(200)->notNull(),
					'email' => $this->varbinary(80),
				],
				'index' => [
					['uri', 'uri'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_propertystorage' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'path' => $this->stringType(1024)->notNull(),
					'name' => $this->stringType(100)->notNull(),
					'valuetype' => $this->integer(10)->unsigned(),
					'value' => $this->binary(),
				],
				'columns_mysql' => [
					'path' => $this->varbinary(1024)->notNull(),
					'name' => $this->varbinary(100)->notNull(),
				],
				'index' => [
					['path_property', ['path', 'name']],
				],
				'index_mysql' => [
					['path_property', ['path(600)', 'name(100)'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_schedulingobjects' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'principaluri' => $this->stringType(),
					'calendardata' => $this->binary(),
					'uri' => $this->stringType(200),
					'lastmodified' => $this->integer(10)->unsigned(),
					'etag' => $this->stringType(32),
					'size' => $this->integer(10)->unsigned()->notNull(),
				],
				'columns_mysql' => [
					'principaluri' => $this->varbinary(),
					'uri' => $this->varbinary(200),
					'etag' => $this->varbinary(32),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_users' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'username' => $this->stringType(50),
					'digesta1' => $this->stringType(32),
					'userid' => $this->integer(10)->unsigned(),
					'key' => $this->stringType(50),
				],
				'columns_mysql' => [
					'username' => $this->varbinary(50),
					'digesta1' => $this->varbinary(32),
				],
				'index' => [
					['username', 'username'],
					['userid', 'userid'],
				],
				'index_mysql' => [
					['username', 'username(50)', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'i_#__magento_config' => [
				'columns' => [
					'name' => $this->stringType(15),
					'value' => $this->stringType(50),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'i_#__magento_record' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->stringType(25)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
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
					['expires_index', 'expires'],
					['user_cache_index', ['user_id', 'cache_key']],
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
				'columns_mysql' => [
					'valid' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['expires_index', 'expires'],
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
					'uid' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
					'expires' => $this->dateTime(),
					'data' => $this->text()->notNull(),
					'flags' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'index' => [
					['expires_index', 'expires'],
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
					['expires_index', 'expires'],
					['cache_key_index', 'cache_key'],
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
					['expires_index', 'expires'],
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
					['roundcube_contactgroupmembers_contact_index', 'contact_id'],
				],
				'primaryKeys' => [
					['roundcube_contactgroupmembers_pk', ['contactgroup_id', 'contact_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contactgroups' => [
				'columns' => [
					'contactgroup_id' => $this->primaryKey(10)->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull()->defaultValue(''),
				],
				'columns_mysql' => [
					'del' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['roundcube_contactgroups_user_index', ['user_id', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contacts' => [
				'columns' => [
					'contact_id' => $this->primaryKey(10)->unsigned(),
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
				'columns_mysql' => [
					'del' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['roundcube_user_contacts_index', ['user_id', 'del']],
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
					['uniqueness', ['user_id', 'language']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_identities' => [
				'columns' => [
					'identity_id' => $this->primaryKey(10)->unsigned(),
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
				'columns_mysql' => [
					'del' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'standard' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'html_signature' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['user_identities_index', ['user_id', 'del']],
					['email_identities_index', ['email', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_searches' => [
				'columns' => [
					'search_id' => $this->primaryKey(10)->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->integer(3)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull(),
					'data' => $this->text(),
				],
				'index' => [
					['uniqueness', ['user_id', 'type', 'name']],
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
					'vars' => $this->mediumText()->notNull(),
				],
				'index' => [
					['changed_index', 'changed'],
				],
				'primaryKeys' => [
					['roundcube_session_pk', 'sess_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users' => [
				'columns' => [
					'user_id' => $this->primaryKey(10)->unsigned(),
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
					'crm_user_id' => $this->integer(10)->defaultValue(0),
				],
				'index' => [
					['username', ['username', 'mail_host']],
					['crm_user_id', 'crm_user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_users_autologin' => [
				'columns' => [
					'rcuser_id' => $this->integer(10)->unsigned()->notNull(),
					'crmuser_id' => $this->integer(10)->notNull(),
					'active' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['rcuser_id', 'rcuser_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activity_invitation' => [
				'columns' => [
					'inviteesid' => $this->primaryKey(10)->unsigned(),
					'activityid' => $this->integer(10)->notNull(),
					'crmid' => $this->integer(10)->notNull()->defaultValue(0),
					'email' => $this->stringType(100)->notNull()->defaultValue(''),
					'name' => $this->stringType(500),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'time' => $this->dateTime(),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['activityid', 'activityid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activityregister' => [
				'columns' => [
					'activityregisterid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'activityregister_status' => $this->stringType()->defaultValue(''),
					'datasetregisterid' => $this->integer()->unsigned()->defaultValue(0),
					'start_date' => $this->date(),
					'end_date' => $this->date(),
					'activity_type' => $this->text(),
					'parent_id' => $this->integer(10),
				],
				'index' => [
					['u_yf_activityregister_datasetregisterid_idx', 'datasetregisterid'],
					['u_yf_activityregister_parent_id_idx', 'parent_id'],
				],
				'primaryKeys' => [
					['activityregister_pk', 'activityregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__activityregistercf' => [
				'columns' => [
					'activityregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['activityregistercf_pk', 'activityregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement' => [
				'columns' => [
					'announcementid' => $this->integer(10)->notNull(),
					'title' => $this->stringType(),
					'announcement_no' => $this->stringType(),
					'subject' => $this->stringType(),
					'announcementstatus' => $this->stringType()->notNull()->defaultValue(''),
					'interval' => $this->smallInteger(5),
					'is_mandatory' => $this->smallInteger(5),
				],
				'index' => [
					['announcementstatus', 'announcementstatus'],
				],
				'primaryKeys' => [
					['announcement_pk', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement_mark' => [
				'columns' => [
					'announcementid' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['userid', ['userid', 'status']],
					['announcementid', ['announcementid', 'userid', 'date', 'status']],
				],
				'primaryKeys' => [
					['announcement_mark_pk', ['announcementid', 'userid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcementcf' => [
				'columns' => [
					'announcementid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['announcementcf_pk', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__approvals' => [
				'columns' => [
					'approvalsid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'description' => $this->text(),
					'approvals_status' => $this->stringType()->defaultValue(''),
				],
				'primaryKeys' => [
					['approvals_pk', 'approvalsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__approvalsregister' => [
				'columns' => [
					'approvalsregisterid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'approvalsid' => $this->integer()->unsigned()->defaultValue(0),
					'contactid' => $this->integer()->unsigned()->defaultValue(0),
					'approvals_register_status' => $this->stringType()->defaultValue(''),
					'approvals_register_type' => $this->stringType()->defaultValue(''),
					'registration_date' => $this->dateTime(),
				],
				'index' => [
					['u_yf_approvalsregister_approvalsid_idx', 'approvalsid'],
					['u_yf_approvalsregister_contactid_idx', 'contactid'],
				],
				'primaryKeys' => [
					['approvalsregister_pk', 'approvalsregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__auditregister' => [
				'columns' => [
					'auditregisterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'locationregisterid' => $this->integer()->unsigned()->defaultValue(0),
					'datasetregisterid' => $this->integer()->unsigned()->defaultValue(0),
					'auditregister_status' => $this->stringType()->defaultValue(''),
					'auditregister_type' => $this->stringType()->defaultValue(''),
				],
				'index' => [
					['u_yf_auditregister_locationregisterid_idx', 'locationregisterid'],
					['u_yf_auditregister_datasetregisterid_idx', 'datasetregisterid'],
				],
				'primaryKeys' => [
					['auditregister_pk', 'auditregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__auditregistercf' => [
				'columns' => [
					'auditregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['auditregistercf_pk', 'auditregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__browsinghistory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'userid' => $this->integer(10)->notNull(),
					'date' => $this->dateTime(),
					'title' => $this->stringType(),
					'url' => $this->text(),
				],
				'index' => [
					['browsinghistory_user_idx', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cfixedassets' => [
				'columns' => [
					'cfixedassetsid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'fixed_assets_type' => $this->stringType(),
					'fixed_assets_status' => $this->stringType(),
					'producent_designation' => $this->stringType(),
					'additional_designation' => $this->stringType(),
					'internal_designation' => $this->stringType(),
					'date_production' => $this->date(),
					'date_acquisition' => $this->date(),
					'purchase_price' => $this->decimal('28,8'),
					'actual_price' => $this->decimal('28,8'),
					'reservation' => $this->smallInteger(1),
					'pscategory' => $this->stringType(),
					'fixed_assets_fuel_type' => $this->stringType(),
					'timing_change' => $this->integer(10)->defaultValue(0),
					'oil_change' => $this->integer(10),
					'fuel_consumption' => $this->integer(10),
					'current_odometer_reading' => $this->integer(10),
					'number_repair' => $this->smallInteger(5),
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
					'cfixedassetsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cfixedassetscf_pk', 'cfixedassetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_global' => [
				'columns' => [
					'global_room_id' => $this->primaryKey(10)->unsigned(),
					'name' => $this->stringType()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_crm' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'crmid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'index' => [
					['room_crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_global' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'globalid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'index' => [
					['globalid', 'globalid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_group' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'groupid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'index' => [
					['room_groupid', 'groupid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_private' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'privateid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_messages_user' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'roomid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'created' => $this->dateTime(),
					'messages' => $this->stringType(500)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_private' => [
				'columns' => [
					'private_room_id' => $this->primaryKey(10)->unsigned(),
					'name' => $this->stringType()->notNull(),
					'creatorid' => $this->integer(10)->notNull(),
					'created' => $this->dateTime(),
					'archived' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'archived' => $this->tinyInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'type' => $this->stringType(50),
					'sequence' => $this->smallInteger(4),
					'active' => $this->smallInteger(1)->defaultValue(1),
				],
				'columns_mysql' => [
					'sequence' => $this->tinyInteger(4),
					'active' => $this->tinyInteger(1)->defaultValue(1),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_crm' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'crmid' => $this->integer(10),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'index' => [
					['u_yf_chat_rooms_crm_userid_idx', 'userid'],
					['u_yf_chat_rooms_crm_crmid_idx', 'crmid'],
					['u_yf_chat_rooms_crm_last_message_idx', 'last_message'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_global' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'global_room_id' => $this->integer(10)->unsigned()->notNull(),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'index' => [
					['global_room_id', 'global_room_id'],
					['userid', 'userid'],
					['last_message', 'last_message'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_group' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'groupid' => $this->integer(10)->notNull(),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'index' => [
					['userid', 'userid'],
					['u_yf_chat_rooms_group_groupid_idx', 'groupid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_private' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'private_room_id' => $this->integer(10)->unsigned()->notNull(),
					'last_message' => $this->integer(10)->unsigned(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_rooms_user' => [
				'columns' => [
					'roomid' => $this->integer(10)->unsigned()->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'last_message' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__chat_user' => [
				'columns' => [
					'roomid' => $this->primaryKey(10)->unsigned(),
					'userid' => $this->integer(10)->notNull(),
					'reluserid' => $this->integer(10)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cinternaltickets' => [
				'columns' => [
					'cinternalticketsid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(100),
					'cinternaltickets_no' => $this->stringType(32),
					'internal_tickets_status' => $this->stringType(150),
					'resolution' => $this->text(),
				],
				'primaryKeys' => [
					['cinternaltickets_pk', 'cinternalticketsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cinternalticketscf' => [
				'columns' => [
					'cinternalticketsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cinternalticketscf_pk', 'cinternalticketsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cmileagelogbook' => [
				'columns' => [
					'cmileagelogbookid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'cmileage_logbook_status' => $this->stringType(150),
					'number_kilometers' => $this->decimal('13,2'),
				],
				'primaryKeys' => [
					['cmileagelogbook_pk', 'cmileagelogbookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cmileagelogbookcf' => [
				'columns' => [
					'cmileagelogbookid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['cmileagelogbookcf_pk', 'cmileagelogbookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition' => [
				'columns' => [
					'competitionid' => $this->integer(10)->notNull()->defaultValue(0),
					'competition_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'vat_id' => $this->stringType(30),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'email' => $this->stringType(100)->defaultValue(''),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'parent_id' => $this->integer()->unsigned()->defaultValue(0),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['u_yf_competition_parent_id_idx', 'parent_id'],
				],
				'primaryKeys' => [
					['competition_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competition_address' => [
				'columns' => [
					'competitionaddressid' => $this->integer(10)->notNull(),
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
					'competitionid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['competitioncf_pk', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__countries' => [
				'columns' => [
					'id' => $this->primaryKey(5)->unsigned(),
					'name' => $this->stringType(50)->notNull(),
					'code' => $this->char(2)->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->defaultValue(0),
					'sortorderid' => $this->smallInteger(5)->unsigned()->notNull(),
					'phone' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'uitype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->unsigned()->defaultValue(0),
					'phone' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'uitype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['code', 'code'],
					['phone', ['status', 'phone']],
					['uitype', ['status', 'uitype']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_label' => [
				'columns' => [
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'label' => $this->stringType(),
				],
				'index' => [
					['crmentity_label', 'label'],
					['crmentity_label_fulltext', 'label'],
				],
				'primaryKeys' => [
					['crmentity_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_last_changes' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'fieldname' => $this->stringType(50)->notNull(),
					'user_id' => $this->integer(10)->notNull(),
					'date_updated' => $this->dateTime()->notNull(),
				],
				'index' => [
					['crmid', ['crmid', 'fieldname']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_rel_tree' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'module' => $this->integer(10)->notNull(),
					'tree' => $this->stringType(50)->notNull(),
					'relmodule' => $this->integer(10)->notNull(),
					'rel_created_user' => $this->integer(10)->notNull(),
					'rel_created_time' => $this->dateTime()->notNull(),
					'rel_comment' => $this->stringType(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_search_label' => [
				'columns' => [
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'searchlabel' => $this->stringType()->notNull(),
					'setype' => $this->stringType(30)->notNull(),
					'userid' => $this->text(),
				],
				'index' => [
					['crmentity_searchlabel_setype', ['searchlabel', 'setype']],
					['crmentity_searchlabel_fulltext', 'searchlabel'],
				],
				'primaryKeys' => [
					['crmentity_search_label_pk', 'crmid']
				],
				'engine' => 'MyISAM',
				'charset' => 'utf8'
			],
			'u_#__crmentity_showners' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
				],
				'index' => [
					['mix', ['crmid', 'userid']],
					['crmid', 'crmid'],
					['userid', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_condition' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'group_id' => $this->integer(10)->unsigned(),
					'field_name' => $this->stringType(50),
					'module_name' => $this->stringType(25),
					'source_field_name' => $this->stringType(50),
					'operator' => $this->stringType(20),
					'value' => $this->text(),
					'index' => $this->smallInteger(5),
				],
				'columns_mysql' => [
					'index' => $this->tinyInteger(5),
				],
				'index' => [
					['u_yf_cv_condition_fk', 'group_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_condition_group' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'cvid' => $this->integer(10),
					'condition' => $this->stringType(3),
					'parent_id' => $this->integer(10),
					'index' => $this->smallInteger(5),
				],
				'columns_mysql' => [
					'index' => $this->tinyInteger(5),
				],
				'index' => [
					['u_yf_cv_condition_group_cvid_idx', 'cvid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__cv_duplicates' => [
				'columns' => [
					'cvid' => $this->integer(10),
					'fieldid' => $this->integer(10),
					'ignore' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'ignore' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['u_yf_cv_duplicates_cvid_idx', 'cvid'],
					['u_yf_cv_duplicates_fieldid_idx', 'fieldid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__dashboard_type' => [
				'columns' => [
					'dashboard_id' => $this->primaryKey(10)->unsigned(),
					'name' => $this->stringType()->notNull(),
					'system' => $this->smallInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__datasetregister' => [
				'columns' => [
					'datasetregisterid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'datasetregister_status' => $this->stringType()->defaultValue(''),
					'legal_basis' => $this->text(),
					'scope_data' => $this->text(),
					'registered_dpo' => $this->smallInteger(1)->defaultValue(0),
					'data_submitted' => $this->smallInteger(1)->defaultValue(0),
					'internal_register' => $this->smallInteger(1)->defaultValue(0),
					'data_set_shared' => $this->smallInteger(1)->defaultValue(0),
					'added_to_register' => $this->date(),
					'removed_from_register' => $this->date(),
					'parent_id' => $this->integer(10)->notNull(),
				],
				'columns_mysql' => [
					'registered_dpo' => $this->tinyInteger(1)->defaultValue(0),
					'data_submitted' => $this->tinyInteger(1)->defaultValue(0),
					'internal_register' => $this->tinyInteger(1)->defaultValue(0),
					'data_set_shared' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['u_yf_datasetregister_parent_id_idx', 'parent_id'],
				],
				'primaryKeys' => [
					['datasetregister_pk', 'datasetregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__datasetregistercf' => [
				'columns' => [
					'datasetregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['datasetregistercf_pk', 'datasetregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__documents_emailtemplates' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'relcrmid' => $this->integer(10),
				],
				'index' => [
					['u_yf_documents_emailtemplates_crmid_idx', 'crmid'],
					['u_yf_documents_emailtemplates_relcrmid_idx', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__emailtemplates' => [
				'columns' => [
					'emailtemplatesid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'email_template_type' => $this->stringType(50),
					'module' => $this->stringType(50),
					'subject' => $this->stringType(),
					'content' => $this->mediumText(),
					'sys_name' => $this->stringType(50),
					'email_template_priority' => $this->stringType(1)->defaultValue(1),
					'smtp_id' => $this->integer()->unsigned(),
				],
				'index' => [
					['sys_name', 'sys_name'],
				],
				'primaryKeys' => [
					['emailtemplates_pk', 'emailtemplatesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__faq_faq' => [
				'columns' => [
					'crmid' => $this->integer(),
					'relcrmid' => $this->integer(),
				],
				'index' => [
					['u_yf_faq_faq_crmid_idx', 'crmid'],
					['u_yf_faq_faq_relcrmid_idx', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorite_owners' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'ownerid' => $this->integer(10)->notNull(),
				],
				'index' => [
					['u_yf_favorite_owners_tabid_idx', 'tabid'],
					['u_yf_favorite_owners_userid_idx', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorite_shared_owners' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'ownerid' => $this->integer(10)->notNull(),
				],
				'index' => [
					['u_yf_favorite_shared_owners_tabid_idx', 'tabid'],
					['u_yf_favorite_shared_owners_userid_idx', 'userid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__favorites' => [
				'columns' => [
					'crmid' => $this->integer(10),
					'module' => $this->stringType(30),
					'relcrmid' => $this->integer(10),
					'relmodule' => $this->stringType(30),
					'userid' => $this->integer(10),
					'data' => $this->timestamp()->null(),
				],
				'index' => [
					['crmid', 'crmid'],
					['relcrmid', 'relcrmid'],
					['mix', ['crmid', 'module', 'relcrmid', 'relmodule', 'userid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeeping' => [
				'columns' => [
					'fbookkeepingid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'related_to' => $this->integer(10),
				],
				'index' => [
					['related_to', 'related_to'],
				],
				'primaryKeys' => [
					['fbookkeeping_pk', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeepingcf' => [
				'columns' => [
					'fbookkeepingid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['fbookkeepingcf_pk', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice' => [
				'columns' => [
					'fcorectinginvoiceid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(10),
					'fcorectinginvoice_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'fcorectinginvoice_status' => $this->stringType()->defaultValue(''),
					'finvoiceid' => $this->integer(10),
					'externalcomment' => $this->text(),
					'internalcomment' => $this->text(),
				],
				'index' => [
					['accountid', 'accountid'],
					['finvoiceid', 'finvoiceid'],
				],
				'primaryKeys' => [
					['fcorectinginvoice_pk', 'fcorectinginvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_address' => [
				'columns' => [
					'fcorectinginvoiceaddressid' => $this->integer(10)->notNull(),
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
				],
				'primaryKeys' => [
					['fcorectinginvoice_address_pk', 'fcorectinginvoiceaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['u_yf_fcorectinginvoice_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoice_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'fcorectinginvoiceid' => $this->integer(10)->notNull(),
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
					'cvid' => $this->integer(10)->notNull(),
				],
				'index' => [
					['cvid', 'cvid'],
					['user', 'user'],
				],
				'primaryKeys' => [
					['featured_filter_pk', ['user', 'cvid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__file_upload_temp' => [
				'columns' => [
					'id' => $this->primaryKey(10)->unsigned(),
					'name' => $this->stringType()->notNull(),
					'type' => $this->stringType(100),
					'path' => $this->text()->notNull(),
					'status' => $this->smallInteger(1)->defaultValue(0),
					'fieldname' => $this->stringType(50),
					'crmid' => $this->integer(10),
					'createdtime' => $this->dateTime(),
					'key' => $this->stringType(100),
				],
				'columns_mysql' => [
					'status' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['key', 'key'],
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice' => [
				'columns' => [
					'finvoiceid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(10),
					'finvoice_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'finvoice_status' => $this->stringType()->defaultValue(''),
					'finvoice_type' => $this->stringType(),
					'pscategory' => $this->stringType(100),
					'issue_time' => $this->date(),
					'ssalesprocessesid' => $this->integer(10),
					'projectid' => $this->integer(10),
					'payment_status' => $this->stringType(),
				],
				'index' => [
					['accountid', 'accountid'],
					['u_yf_finvoice_ssalesprocessesid_idx', 'ssalesprocessesid'],
					['u_yf_finvoice_projectid_idx', 'projectid'],
				],
				'primaryKeys' => [
					['finvoice_pk', 'finvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_address' => [
				'columns' => [
					'finvoiceaddressid' => $this->integer(10)->notNull(),
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
				],
				'primaryKeys' => [
					['finvoice_address_pk', 'finvoiceaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['u_yf_finvoice_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoice_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'finvoiceid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['finvoicecf_pk', 'finvoiceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost' => [
				'columns' => [
					'finvoicecostid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'finvoicecost_formpayment' => $this->stringType()->defaultValue(''),
					'sum_total' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'finvoicecost_status' => $this->stringType()->defaultValue(''),
					'finvoicecost_paymentstatus' => $this->stringType(),
					'pscategory' => $this->stringType(50),
				],
				'primaryKeys' => [
					['finvoicecost_pk', 'finvoicecostid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_address' => [
				'columns' => [
					'finvoicecostaddressid' => $this->integer(10)->notNull(),
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
				],
				'primaryKeys' => [
					['finvoicecost_address_pk', 'finvoicecostaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->defaultValue(0),
					'qty' => $this->decimal('25,3')->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'comment1' => $this->text(),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->defaultValue(0),
					'price' => $this->decimal('28,8')->defaultValue(0),
					'gross' => $this->decimal('28,8')->defaultValue(0),
					'net' => $this->decimal('28,8')->defaultValue(0),
					'tax' => $this->decimal('28,8')->defaultValue(0),
					'taxparam' => $this->stringType(),
					'total' => $this->decimal('28,8')->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_finvoicecost_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecost_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
					'finvoicecostid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['finvoicecostcf_pk', 'finvoicecostid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'paymentdate' => $this->date(),
					'saledate' => $this->date(),
					'accountid' => $this->integer(10),
					'finvoiceproforma_formpayment' => $this->stringType(),
					'sum_total' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'finvoiceproforma_status' => $this->stringType(),
				],
				'index' => [
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['finvoiceproforma_pk', 'finvoiceproformaid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_address' => [
				'columns' => [
					'finvoiceproformaaddressid' => $this->integer(10)->notNull(),
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
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['u_yf_finvoiceproforma_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproforma_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					['finvoiceproforma_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproformacf' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['finvoiceproformacf_pk', 'finvoiceproformaid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn' => [
				'columns' => [
					'igdnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igdn_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
					'accountid' => $this->integer(10),
					'ssingleordersid' => $this->integer(10),
				],
				'index' => [
					['storageid', 'storageid'],
					['accountid', 'accountid'],
					['ssingleordersid', 'ssingleordersid'],
				],
				'primaryKeys' => [
					['igdn_pk', 'igdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_igdn_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'igdncid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igdnc_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
					'accountid' => $this->integer(10),
					'igdnid' => $this->integer(10),
				],
				'index' => [
					['storageid', 'storageid'],
					['accountid', 'accountid'],
					['igdnid', 'igdnid'],
				],
				'primaryKeys' => [
					['igdnc_pk', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_igdnc_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnc_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					['igdnc_invmap_pk', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnccf' => [
				'columns' => [
					'igdncid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igdnccf_pk', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdncf' => [
				'columns' => [
					'igdnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igdncf_pk', 'igdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin' => [
				'columns' => [
					'iginid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igin_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
				],
				'index' => [
					['storageid', 'storageid'],
				],
				'primaryKeys' => [
					['igin_pk', 'iginid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_igin_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igin_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'iginid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igincf_pk', 'iginid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn' => [
				'columns' => [
					'igrnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igrn_status' => $this->stringType(),
					'vendorid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
				],
				'index' => [
					['storageid', 'storageid'],
					['vendorid', 'vendorid'],
				],
				'primaryKeys' => [
					['igrn_pk', 'igrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_igrn_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'igrncid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'igrnc_status' => $this->stringType(),
					'vendorid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'igrnid' => $this->integer(10),
				],
				'index' => [
					['storageid', 'storageid'],
					['vendorid', 'vendorid'],
					['igrnid', 'igrnid'],
				],
				'primaryKeys' => [
					['igrnc_pk', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_igrnc_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnc_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'igrncid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igrnccf_pk', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrncf' => [
				'columns' => [
					'igrnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['igrncf_pk', 'igrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn' => [
				'columns' => [
					'iidnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'iidn_status' => $this->stringType(),
					'acceptance_date' => $this->date(),
				],
				'index' => [
					['storageid', 'storageid'],
				],
				'primaryKeys' => [
					['iidn_pk', 'iidnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(200),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_iidn_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'iidnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['iidncf_pk', 'iidnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__incidentregister' => [
				'columns' => [
					'incidentregisterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'locationregisterid' => $this->integer()->unsigned()->defaultValue(0),
					'datasetregisterid' => $this->integer()->unsigned()->defaultValue(0),
					'incidentregister_status' => $this->stringType()->defaultValue(''),
					'incidentregister_type' => $this->stringType()->defaultValue(''),
					'incident_date' => $this->date(),
					'discovery_date' => $this->date(),
					'incident_report_date' => $this->date(),
					'incident_publication_date' => $this->date(),
					'peoplne_number' => $this->integer(9)->defaultValue(0),
					'breach_circumstances' => $this->text(),
					'breach_nature' => $this->text(),
					'possible_consequences' => $this->text(),
					'security_measures' => $this->text(),
				],
				'index' => [
					['u_yf_incidentregister_locationregisterid_idx', 'locationregisterid'],
					['u_yf_incidentregister_datasetregisterid_idx', 'datasetregisterid'],
				],
				'primaryKeys' => [
					['incidentregister_pk', 'incidentregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__incidentregistercf' => [
				'columns' => [
					'incidentregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['incidentregistercf_pk', 'incidentregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder' => [
				'columns' => [
					'ipreorderid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'ipreorder_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'acceptance_date' => $this->date(),
				],
				'index' => [
					['storageid', 'storageid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['ipreorder_pk', 'ipreorderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_ipreorder_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreorder_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'ipreorderid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ipreordercf_pk', 'ipreorderid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn' => [
				'columns' => [
					'istdnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'istdn_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
				],
				'index' => [
					['storageid', 'storageid'],
					['accountid', 'accountid'],
					['process', 'process'],
					['subprocess', 'subprocess'],
				],
				'primaryKeys' => [
					['istdn_pk', 'istdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_istdn_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'istdnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istdncf_pk', 'istdnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istn' => [
				'columns' => [
					'istnid' => $this->integer(10)->notNull(),
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
					'istnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istncf_pk', 'istnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages' => [
				'columns' => [
					'istorageid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'storage_status' => $this->stringType()->defaultValue(''),
					'storage_type' => $this->stringType()->defaultValue(''),
					'parentid' => $this->integer(10),
				],
				'index' => [
					['parentid', 'parentid'],
				],
				'primaryKeys' => [
					['istorages_pk', 'istorageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istorages_address' => [
				'columns' => [
					'istorageaddressid' => $this->integer(10)->notNull(),
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
					'crmid' => $this->integer(10),
					'relcrmid' => $this->integer(10),
					'qtyinstock' => $this->decimal('25,3'),
				],
				'index' => [
					['crmid', 'crmid'],
					['relcrmid', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istoragescf' => [
				'columns' => [
					'istorageid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istoragescf_pk', 'istorageid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn' => [
				'columns' => [
					'istrnid' => $this->integer(10)->notNull(),
					'number' => $this->stringType(32),
					'subject' => $this->stringType(),
					'storageid' => $this->integer(10),
					'istrn_status' => $this->stringType(),
					'vendorid' => $this->integer(10),
					'acceptance_date' => $this->date(),
					'sum_total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
				],
				'index' => [
					['storageid', 'storageid'],
					['vendorid', 'vendorid'],
					['process', 'process'],
					['subprocess', 'subprocess'],
				],
				'primaryKeys' => [
					['istrn_pk', 'istrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_istrn_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrn_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'istrnid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['istrncf_pk', 'istrnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebase' => [
				'columns' => [
					'knowledgebaseid' => $this->integer(10)->notNull(),
					'subject' => $this->stringType(),
					'number' => $this->stringType(32),
					'content' => $this->mediumText(),
					'category' => $this->stringType(200),
					'knowledgebase_view' => $this->stringType(),
					'knowledgebase_status' => $this->stringType()->defaultValue(''),
					'featured' => $this->smallInteger(1)->defaultValue(0),
					'introduction' => $this->text(),
				],
				'columns_mysql' => [
					'featured' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['search', ['subject', 'content', 'introduction']],
				],
				'primaryKeys' => [
					['knowledgebase_pk', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebase_knowledgebase' => [
				'columns' => [
					'crmid' => $this->integer(),
					'relcrmid' => $this->integer(),
				],
				'index' => [
					['u_yf_knowledgebase_knowledgebase_crmid_idx', 'crmid'],
					['u_yf_knowledgebase_knowledgebase_relcrmid_idx', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebasecf' => [
				'columns' => [
					'knowledgebaseid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['knowledgebasecf_pk', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__locationregister' => [
				'columns' => [
					'locationregisterid' => $this->integer(10)->notNull(),
					'name' => $this->stringType(),
					'number' => $this->stringType(32),
					'parent_id' => $this->integer()->unsigned()->defaultValue(0),
					'locationregister_status' => $this->stringType()->defaultValue(''),
					'security_type' => $this->text(),
					'building_number' => $this->stringType(10)->defaultValue(''),
					'street' => $this->stringType()->defaultValue(''),
					'district' => $this->stringType()->defaultValue(''),
					'township' => $this->stringType()->defaultValue(''),
					'state' => $this->stringType()->defaultValue(''),
					'pobox' => $this->stringType(100)->defaultValue(''),
					'local_number' => $this->stringType(20)->defaultValue(''),
					'post_code' => $this->stringType(20)->defaultValue(''),
					'city' => $this->stringType(150)->defaultValue(''),
					'county' => $this->stringType(150)->defaultValue(''),
					'country' => $this->stringType(150)->defaultValue(''),
				],
				'index' => [
					['u_yf_locationregister_parent_id_idx', 'parent_id'],
				],
				'primaryKeys' => [
					['locationregister_pk', 'locationregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__locationregistercf' => [
				'columns' => [
					'locationregisterid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['locationregistercf_pk', 'locationregisterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_address_book' => [
				'columns' => [
					'id' => $this->integer(10)->notNull(),
					'email' => $this->stringType(100)->notNull(),
					'name' => $this->stringType()->notNull(),
					'users' => $this->text()->notNull(),
				],
				'index' => [
					['email', ['email', 'name']],
					['id', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_autologin' => [
				'columns' => [
					'ruid' => $this->smallInteger(5)->unsigned()->notNull(),
					'key' => $this->stringType(50)->notNull(),
					'cuid' => $this->smallInteger(5)->unsigned()->notNull(),
					'params' => $this->text()->notNull(),
				],
				'index' => [
					['ruid', 'ruid'],
					['cuid', 'cuid'],
					['key', 'key'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__mail_compose_data' => [
				'columns' => [
					'userid' => $this->smallInteger(5)->unsigned()->notNull(),
					'key' => $this->stringType(32)->notNull(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['userid', ['userid', 'key']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__modentity_sequences' => [
				'columns' => [
					'tabid' => $this->smallInteger(5)->notNull(),
					'value' => $this->stringType(),
					'cur_id' => $this->integer(10)->unsigned()->defaultValue(0),
				],
				'index' => [
					['u_yf_modentity_sequences_tabid_fk', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__modtracker_inv' => [
				'columns' => [
					'id' => $this->integer(10)->unsigned()->notNull(),
					'changes' => $this->text()->notNull(),
				],
				'index' => [
					['u_yf_modtracker_inv_id_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__multicompany' => [
				'columns' => [
					'multicompanyid' => $this->integer()->notNull(),
					'company_name' => $this->stringType(),
					'parent_id' => $this->integer(10),
					'number' => $this->stringType(32),
					'mulcomp_status' => $this->stringType(),
					'email1' => $this->stringType(100),
					'email2' => $this->stringType(100),
					'phone' => $this->stringType(30),
					'phone_extra' => $this->stringType(100),
					'mobile' => $this->stringType(30),
					'mobile_extra' => $this->stringType(100),
					'fax' => $this->stringType(30),
					'fax_extra' => $this->stringType(100),
					'vat' => $this->stringType(),
					'companyid1' => $this->stringType(),
					'companyid2' => $this->stringType(),
					'buildingnumbera' => $this->stringType(50),
					'localnumbera' => $this->stringType(50),
					'addresslevel8a' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel1a' => $this->stringType(),
					'poboxa' => $this->stringType(50),
					'website' => $this->stringType()->defaultValue(''),
					'logo' => $this->text(),
				],
				'index' => [
					['multicompany_parent_id_idx', 'parent_id'],
				],
				'primaryKeys' => [
					['multicompany_pk', 'multicompanyid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__multicompanycf' => [
				'columns' => [
					'multicompanyid' => $this->integer()->notNull(),
					'public_notes' => $this->text(),
					'internal_notes' => $this->text(),
				],
				'primaryKeys' => [
					['multicompanycf_pk', 'multicompanyid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__notification' => [
				'columns' => [
					'notificationid' => $this->integer(10)->notNull(),
					'title' => $this->stringType(),
					'number' => $this->stringType(50),
					'notification_status' => $this->stringType(),
					'notification_type' => $this->stringType()->defaultValue(''),
					'link' => $this->integer(10),
					'process' => $this->integer(10),
					'subprocess' => $this->integer(10),
					'linkextend' => $this->integer(10),
					'category' => $this->stringType(30)->defaultValue(''),
				],
				'index' => [
					['link', 'link'],
					['process', 'process'],
					['subprocess', 'subprocess'],
					['linkextend', 'linkextend'],
				],
				'primaryKeys' => [
					['notification_pk', 'notificationid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap' => [
				'columns' => [
					'crmid' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->char()->notNull(),
					'lat' => $this->decimal('10,7'),
					'lon' => $this->decimal('10,7'),
				],
				'index' => [
					['u_yf_openstreetmap_lat_lon', ['lat', 'lon']],
					['crmid_type', ['crmid', 'type']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_address_updater' => [
				'columns' => [
					'crmid' => $this->integer(10),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_cache' => [
				'columns' => [
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'module_name' => $this->stringType(50)->notNull(),
					'crmids' => $this->integer(10)->unsigned()->notNull(),
				],
				'index' => [
					['u_yf_openstreetmap_cache_user_id_module_name_idx', ['user_id', 'module_name']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_record_updater' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'type' => $this->char()->notNull(),
					'address' => $this->text()->notNull(),
				],
				'index' => [
					['crmid', ['crmid', 'type']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners' => [
				'columns' => [
					'partnersid' => $this->integer(10)->notNull()->defaultValue(0),
					'partners_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'vat_id' => $this->stringType(30),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'email' => $this->stringType(100)->defaultValue(''),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'category' => $this->stringType()->defaultValue(''),
				],
				'columns_mysql' => [
					'active' => $this->tinyInteger(1)->defaultValue(0),
				],
				'primaryKeys' => [
					['partners_pk', 'partnersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partners_address' => [
				'columns' => [
					'partneraddressid' => $this->integer(10)->notNull(),
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
					'partnersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['partnerscf_pk', 'partnersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__pdf_inv_scheme' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'columns' => $this->text(),
				],
				'index' => [
					['crmid', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__picklist_close_state' => [
				'columns' => [
					'valueid' => $this->integer()->notNull(),
					'fieldid' => $this->integer()->notNull(),
					'value' => $this->stringType()->notNull(),
				],
				'index' => [
					['fieldid', 'fieldid'],
				],
				'primaryKeys' => [
					['picklist_close_state_pk', 'valueid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__recurring_info' => [
				'columns' => [
					'srecurringordersid' => $this->integer(10)->notNull()->defaultValue(0),
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
					'id' => $this->integer(10)->notNull(),
					'userid' => $this->integer(10)->notNull(),
					'tabid' => $this->smallInteger(5),
					'data' => $this->text(),
					'time' => $this->dateTime(),
				],
				'index' => [
					['userid', 'userid'],
				],
				'primaryKeys' => [
					['reviewed_queue_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations' => [
				'columns' => [
					'scalculationsid' => $this->integer(10)->notNull()->defaultValue(0),
					'scalculations_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'srequirementscardsid' => $this->integer(10),
					'category' => $this->stringType(),
					'scalculations_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['accountid', 'accountid'],
					['srequirementscardsid', 'srequirementscardsid'],
				],
				'primaryKeys' => [
					['scalculations_pk', 'scalculationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_scalculations_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculations_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'scalculationsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['scalculationscf_pk', 'scalculationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__servicecontracts_sla_policy' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'crmid' => $this->integer()->notNull(),
					'policy_type' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'sla_policy_id' => $this->integer(),
					'tabid' => $this->smallInteger(5)->notNull(),
					'conditions' => $this->text()->notNull(),
					'reaction_time' => $this->stringType(20)->notNull()->defaultValue('0:H'),
					'idle_time' => $this->stringType(20)->notNull()->defaultValue('0:H'),
					'resolve_time' => $this->stringType(20)->notNull()->defaultValue('0:H'),
					'business_hours' => $this->text()->notNull(),
				],
				'columns_mysql' => [
					'policy_type' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['fk_crmid_idx', 'crmid'],
					['fk_sla_policy_idx', 'sla_policy_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__social_media_config' => [
				'columns' => [
					'id' => $this->primaryKey(20),
					'name' => $this->stringType(100)->notNull(),
					'value' => $this->text(),
					'type' => $this->stringType(100)->notNull(),
				],
				'index' => [
					['name_type_unique', ['name', 'type']],
					['type', 'type'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__social_media_twitter' => [
				'columns' => [
					'id' => $this->primaryKey(20),
					'twitter_login' => $this->stringType(15)->notNull(),
					'id_twitter' => $this->stringType(32),
					'message' => $this->text(),
					'created' => $this->dateTime(),
					'twitter_name' => $this->stringType(50),
					'reply' => $this->integer(),
					'retweet' => $this->integer(),
					'favorite' => $this->integer(),
				],
				'index' => [
					['twitter_login', 'twitter_login'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries' => [
				'columns' => [
					'squoteenquiriesid' => $this->integer(10)->notNull()->defaultValue(0),
					'squoteenquiries_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'category' => $this->stringType(),
					'squoteenquiries_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'campaign_id' => $this->integer(10),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['accountid', 'accountid'],
					['u_yf_squoteenquiries_campaign_id_idx', 'campaign_id'],
				],
				'primaryKeys' => [
					['squoteenquiries_pk', 'squoteenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_squoteenquiries_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiries_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'squoteenquiriesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['squoteenquiriescf_pk', 'squoteenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes' => [
				'columns' => [
					'squotesid' => $this->integer(10)->notNull()->defaultValue(0),
					'squotes_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'scalculationsid' => $this->integer(10),
					'category' => $this->stringType(),
					'squotes_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'sum_discount' => $this->decimal('28,8'),
					'valid_until' => $this->date(),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['scalculationsid', 'scalculationsid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['squotes_pk', 'squotesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_address' => [
				'columns' => [
					'squotesaddressid' => $this->integer(10)->notNull(),
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
					['squotes_address_pk', 'squotesaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'net' => $this->decimal('28,8')->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
				],
				'columns_mysql' => [
					'discountmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
					'taxmode' => $this->tinyInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['u_yf_squotes_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotes_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'squotesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['squotescf_pk', 'squotesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders' => [
				'columns' => [
					'srecurringordersid' => $this->integer(10)->notNull()->defaultValue(0),
					'srecurringorders_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'squotesid' => $this->integer(10),
					'category' => $this->stringType(),
					'srecurringorders_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'duedate' => $this->date(),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['squotesid', 'squotesid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['srecurringorders_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_address' => [
				'columns' => [
					'srecurringordersaddressid' => $this->integer(10)->notNull(),
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
					['srecurringorders_address_pk', 'srecurringordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->defaultValue(0),
					'qty' => $this->decimal('25,3')->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'tax' => $this->decimal('28,8')->defaultValue(0),
					'taxparam' => $this->stringType(),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->defaultValue(0),
					'total' => $this->decimal('28,8')->defaultValue(0),
					'net' => $this->decimal('28,8')->defaultValue(0),
					'purchase' => $this->decimal('28,8')->defaultValue(0),
					'gross' => $this->decimal('28,8')->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->defaultValue(0),
					'currency' => $this->integer(),
					'currencyparam' => $this->stringType(1024),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_srecurringorders_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorders_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'srecurringordersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['srecurringorderscf_pk', 'srecurringordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards' => [
				'columns' => [
					'srequirementscardsid' => $this->integer(10)->notNull()->defaultValue(0),
					'srequirementscards_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'quoteenquiryid' => $this->integer(10),
					'category' => $this->stringType(),
					'srequirementscards_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['accountid', 'accountid'],
					['quoteenquiryid', 'quoteenquiryid'],
				],
				'primaryKeys' => [
					['srequirementscards_pk', 'srequirementscardsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'comment1' => $this->text(),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_srequirementscards_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscards_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'srequirementscardsid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['srequirementscardscf_pk', 'srequirementscardsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocesses' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer(10)->notNull()->defaultValue(0),
					'ssalesprocesses_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'category' => $this->stringType(),
					'related_to' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'estimated' => $this->decimal('28,8'),
					'actual_sale' => $this->decimal('28,8'),
					'estimated_date' => $this->date(),
					'actual_date' => $this->date(),
					'probability' => $this->decimal('5,2'),
					'ssalesprocesses_source' => $this->stringType(),
					'ssalesprocesses_type' => $this->stringType(),
					'ssalesprocesses_status' => $this->stringType(),
					'campaignid' => $this->integer(10),
					'parentid' => $this->integer(10)->defaultValue(0),
					'startdate' => $this->date(),
				],
				'index' => [
					['related_to', 'related_to'],
					['campaignid', 'campaignid'],
					['parentid', 'parentid'],
					['ssalesprocesses_no', 'ssalesprocesses_no'],
				],
				'primaryKeys' => [
					['ssalesprocesses_pk', 'ssalesprocessesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocessescf' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ssalesprocessescf_pk', 'ssalesprocessesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders' => [
				'columns' => [
					'ssingleordersid' => $this->integer(10)->notNull()->defaultValue(0),
					'ssingleorders_no' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'squotesid' => $this->integer(10),
					'category' => $this->stringType(),
					'ssingleorders_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'date_start' => $this->date(),
					'date_end' => $this->date(),
					'duedate' => $this->date(),
					'company' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
					'sum_gross' => $this->decimal('28,8'),
					'sum_discount' => $this->decimal('28,8'),
					'ssingleorders_source' => $this->stringType()->defaultValue(''),
					'istorageaddressid' => $this->integer(10),
					'ssingleorders_method_payments' => $this->stringType(),
					'payment_status' => $this->stringType(),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['squotesid', 'squotesid'],
					['accountid', 'accountid'],
					['u_yf_ssingleorders_istorageaddressid_idx', 'istorageaddressid'],
				],
				'primaryKeys' => [
					['ssingleorders_pk', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_address' => [
				'columns' => [
					'ssingleordersaddressid' => $this->integer(10)->notNull(),
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
					['ssingleorders_address_pk', 'ssingleordersaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_inventory' => [
				'columns' => [
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->defaultValue(0),
					'qty' => $this->decimal('25,3')->defaultValue(0),
					'discount' => $this->decimal('28,8')->defaultValue(0),
					'discountparam' => $this->stringType(),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'tax' => $this->decimal('28,8')->defaultValue(0),
					'taxparam' => $this->stringType(),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->defaultValue(0),
					'total' => $this->decimal('28,8')->defaultValue(0),
					'net' => $this->decimal('28,8')->defaultValue(0),
					'purchase' => $this->decimal('28,8')->defaultValue(0),
					'gross' => $this->decimal('28,8')->defaultValue(0),
					'discountmode' => $this->smallInteger(1)->defaultValue(0),
					'taxmode' => $this->smallInteger(1)->defaultValue(0),
					'currency' => $this->integer(),
					'currencyparam' => $this->stringType(1024),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_ssingleorders_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorders_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
				'columns_mysql' => [
					'presence' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
					'block' => $this->tinyInteger(1)->unsigned()->notNull(),
					'displaytype' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
					'colspan' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
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
					'ssingleordersid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['ssingleorderscf_pk', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries' => [
				'columns' => [
					'svendorenquiriesid' => $this->integer(10)->notNull()->defaultValue(0),
					'svendorenquiries_no' => $this->stringType(50)->defaultValue(''),
					'subject' => $this->stringType(),
					'salesprocessid' => $this->integer(10),
					'category' => $this->stringType(30),
					'svendorenquiries_status' => $this->stringType(),
					'accountid' => $this->integer(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'sum_total' => $this->decimal('28,8'),
					'sum_marginp' => $this->decimal('10,2'),
					'sum_margin' => $this->decimal('28,8'),
					'vendorid' => $this->integer(10),
					'scalculationsid' => $this->integer(10),
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
					'id' => $this->primaryKey(10),
					'crmid' => $this->integer(10),
					'seq' => $this->integer(10),
					'name' => $this->integer(10)->defaultValue(0),
					'qty' => $this->decimal('25,3')->defaultValue(0),
					'comment1' => $this->text(),
					'price' => $this->decimal('28,8')->defaultValue(0),
					'total' => $this->decimal('28,8')->defaultValue(0),
					'purchase' => $this->decimal('28,8')->defaultValue(0),
					'marginp' => $this->decimal('28,8')->defaultValue(0),
					'margin' => $this->decimal('28,8')->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['u_yf_svendorenquiries_inventory_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__svendorenquiries_invfield' => [
				'columns' => [
					'id' => $this->primaryKey(10),
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
					'svendorenquiriesid' => $this->integer(10)->notNull(),
				],
				'primaryKeys' => [
					['svendorenquiriescf_pk', 'svendorenquiriesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__timeline' => [
				'columns' => [
					'crmid' => $this->integer(10)->notNull(),
					'type' => $this->stringType(50),
					'userid' => $this->integer(10)->notNull(),
				],
				'index' => [
					['timeline_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__users_pinned' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'owner_id' => $this->integer()->notNull(),
					'fav_element_id' => $this->integer()->notNull(),
				],
				'index' => [
					['u_yf_users_pinned', 'owner_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_module' => [
				'columns' => [
					'member' => $this->stringType(50)->notNull(),
					'module' => $this->integer(10)->unsigned()->notNull(),
					'lock' => $this->smallInteger(1)->defaultValue(0),
					'exceptions' => $this->text(),
				],
				'columns_mysql' => [
					'lock' => $this->tinyInteger(1)->defaultValue(0),
				],
				'index' => [
					['userid', 'member'],
				],
				'primaryKeys' => [
					['watchdog_module_pk', ['member', 'module']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_record' => [
				'columns' => [
					'userid' => $this->integer(10)->unsigned()->notNull(),
					'record' => $this->integer(10)->notNull(),
					'state' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'columns_mysql' => [
					'state' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['userid', 'userid'],
					['record', 'record'],
					['userid_2', ['userid', 'record', 'state']],
				],
				'primaryKeys' => [
					['watchdog_record_pk', ['userid', 'record']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_schedule' => [
				'columns' => [
					'userid' => $this->integer(10)->notNull(),
					'frequency' => $this->smallInteger(5)->notNull(),
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
			['com_vtiger_workflowtasks_ibfk_1', 'com_vtiger_workflowtasks', 'workflow_id', 'com_vtiger_workflows', 'workflow_id', 'CASCADE', 'RESTRICT'],
			['dav_addressbooks_ibfk_1', 'dav_addressbooks', 'principaluri', 'dav_principals', 'uri', 'CASCADE', 'RESTRICT'],
			['dav_calendarobjects_ibfk_1', 'dav_calendarobjects', 'calendarid', 'dav_calendars', 'id', 'CASCADE', 'RESTRICT'],
			['dav_cards_ibfk_1', 'dav_cards', 'addressbookid', 'dav_addressbooks', 'id', 'CASCADE', 'RESTRICT'],
			['roundcube_user_id_fk_cache', 'roundcube_cache', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_cache_index', 'roundcube_cache_index', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_cache_messages', 'roundcube_cache_messages', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_cache_thread', 'roundcube_cache_thread', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_contact_id_fk_contacts', 'roundcube_contactgroupmembers', 'contact_id', 'roundcube_contacts', 'contact_id', 'CASCADE', 'CASCADE'],
			['roundcube_contactgroup_id_fk_contactgroups', 'roundcube_contactgroupmembers', 'contactgroup_id', 'roundcube_contactgroups', 'contactgroup_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_contactgroups', 'roundcube_contactgroups', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_contacts', 'roundcube_contacts', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_dictionary', 'roundcube_dictionary', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_identities', 'roundcube_identities', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_user_id_fk_searches', 'roundcube_searches', 'user_id', 'roundcube_users', 'user_id', 'CASCADE', 'CASCADE'],
			['roundcube_users_autologin_ibfk_1', 'roundcube_users_autologin', 'rcuser_id', 'roundcube_users', 'user_id', 'CASCADE', 'RESTRICT'],
			['u_#__activity_invitation_ibfk_1', 'u_#__activity_invitation', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__activityregisteractivityregisterid', 'u_#__activityregister', 'activityregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__activityregistercfactivityregisterid', 'u_#__activityregistercf', 'activityregisterid', 'u_#__activityregister', 'activityregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__announcement', 'u_#__announcement', 'announcementid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__announcement_mark_ibfk_1', 'u_#__announcement_mark', 'announcementid', 'u_#__announcement', 'announcementid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__announcementcf', 'u_#__announcementcf', 'announcementid', 'u_#__announcement', 'announcementid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__approvalsapprovalsid', 'u_#__approvals', 'approvalsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__approvalsregisterapprovalsregisterid', 'u_#__approvalsregister', 'approvalsregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__auditregisterauditregisterid', 'u_#__auditregister', 'auditregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__auditregistercfauditregisterid', 'u_#__auditregistercf', 'auditregisterid', 'u_#__auditregister', 'auditregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cfixedassetscfixedassetsid', 'u_#__cfixedassets', 'cfixedassetsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cfixedassetscfcfixedassetsid', 'u_#__cfixedassetscf', 'cfixedassetsid', 'u_#__cfixedassets', 'cfixedassetsid', 'CASCADE', 'RESTRICT'],
			['fk_chat_messages', 'u_#__chat_messages_crm', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__chat_messages_global_ibfk_1', 'u_#__chat_messages_global', 'globalid', 'u_#__chat_global', 'global_room_id', 'CASCADE', 'RESTRICT'],
			['fk_chat_group_messages', 'u_#__chat_messages_group', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_crm_crm', 'u_#__chat_rooms_crm', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_crm_users', 'u_#__chat_rooms_crm', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_global_global', 'u_#__chat_rooms_global', 'global_room_id', 'u_#__chat_global', 'global_room_id', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_global_users', 'u_#__chat_rooms_global', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_group', 'u_#__chat_rooms_group', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__chat_rooms_group_users', 'u_#__chat_rooms_group', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cinternalticketscinternalticketsid', 'u_#__cinternaltickets', 'cinternalticketsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cinternalticketscfcinternalticketsid', 'u_#__cinternalticketscf', 'cinternalticketsid', 'u_#__cinternaltickets', 'cinternalticketsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cmileagelogbookcmileagelogbookid', 'u_#__cmileagelogbook', 'cmileagelogbookid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cmileagelogbookcfcmileagelogbookid', 'u_#__cmileagelogbookcf', 'cmileagelogbookid', 'u_#__cmileagelogbook', 'cmileagelogbookid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__competition', 'u_#__competition', 'competitionid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__competition_address_ibfk_1', 'u_#__competition_address', 'competitionaddressid', 'u_#__competition', 'competitionid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__competitioncf', 'u_#__competitioncf', 'competitionid', 'u_#__competition', 'competitionid', 'CASCADE', 'RESTRICT'],
			['u_#__crmentity_last_changes_ibfk_1', 'u_#__crmentity_last_changes', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__crmentity_showners', 'u_#__crmentity_showners', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__cv_condition_fk', 'u_#__cv_condition', 'group_id', 'u_#__cv_condition_group', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__cv_condition_group_fk', 'u_#__cv_condition_group', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['u_#__cv_duplicates_cvid_fk', 'u_#__cv_duplicates', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['u_#__cv_duplicates_fieldid_fk', 'u_#__cv_duplicates', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__datasetregisterdatasetregisterid', 'u_#__datasetregister', 'datasetregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__datasetregistercfdatasetregisterid', 'u_#__datasetregistercf', 'datasetregisterid', 'u_#__datasetregister', 'datasetregisterid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__documents_emailtemplates', 'u_#__documents_emailtemplates', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_2_u_#__documents_emailtemplates', 'u_#__documents_emailtemplates', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_emailtemplatesemailtemplatesid', 'u_#__emailtemplates', 'emailtemplatesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__faq_faq', 'u_#__faq_faq', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_2_u_#__faq_faq', 'u_#__faq_faq', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_owners_tabid_fk', 'u_#__favorite_owners', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_owners_userid_fk', 'u_#__favorite_owners', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_shared_owners_tabid_fk', 'u_#__favorite_shared_owners', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['u_#__favorite_shared_owners_userid_fk', 'u_#__favorite_shared_owners', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__favorites', 'u_#__favorites', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__favorites', 'u_#__favorites', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fbookkeeping_ibfk_1', 'u_#__fbookkeeping', 'fbookkeepingid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fbookkeepingcf_ibfk_1', 'u_#__fbookkeepingcf', 'fbookkeepingid', 'u_#__fbookkeeping', 'fbookkeepingid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_fcorectinginvoice', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fcorectinginvoice_address_ibfk_1', 'u_#__fcorectinginvoice_address', 'fcorectinginvoiceaddressid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__fcorectinginvoice_inventory', 'u_#__fcorectinginvoice_inventory', 'crmid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__fcorectinginvoicecf', 'u_#__fcorectinginvoicecf', 'fcorectinginvoiceid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['u_#__featured_filter_ibfk_1', 'u_#__featured_filter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoice', 'u_#__finvoice', 'finvoiceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__finvoice_address_ibfk_1', 'u_#__finvoice_address', 'finvoiceaddressid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoice_inventory', 'u_#__finvoice_inventory', 'crmid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoicecf', 'u_#__finvoicecf', 'finvoiceid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoicecost', 'u_#__finvoicecost', 'finvoicecostid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__finvoicecost_address_ibfk_1', 'u_#__finvoicecost_address', 'finvoicecostaddressid', 'u_#__finvoicecost', 'finvoicecostid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoicecost_inventory', 'u_#__finvoicecost_inventory', 'crmid', 'u_#__finvoicecost', 'finvoicecostid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoicecostcf', 'u_#__finvoicecostcf', 'finvoicecostid', 'u_#__finvoicecost', 'finvoicecostid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoiceproforma', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoiceproforma_inventory', 'u_#__finvoiceproforma_inventory', 'crmid', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoiceproformacf', 'u_#__finvoiceproformacf', 'finvoiceproformaid', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', 'RESTRICT'],
			['u_#__igdn_ibfk_1', 'u_#__igdn', 'igdnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igdn_inventory', 'u_#__igdn_inventory', 'crmid', 'u_#__igdn', 'igdnid', 'CASCADE', 'RESTRICT'],
			['u_#__igdnc_ibfk_1', 'u_#__igdnc', 'igdncid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igdnc_inventory', 'u_#__igdnc_inventory', 'crmid', 'u_#__igdnc', 'igdncid', 'CASCADE', 'RESTRICT'],
			['u_#__igdnccf_ibfk_1', 'u_#__igdnccf', 'igdncid', 'u_#__igdnc', 'igdncid', 'CASCADE', 'RESTRICT'],
			['u_#__igdncf_ibfk_1', 'u_#__igdncf', 'igdnid', 'u_#__igdn', 'igdnid', 'CASCADE', 'RESTRICT'],
			['u_#__igin_ibfk_1', 'u_#__igin', 'iginid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igin_inventory', 'u_#__igin_inventory', 'crmid', 'u_#__igin', 'iginid', 'CASCADE', 'RESTRICT'],
			['u_#__igincf_ibfk_1', 'u_#__igincf', 'iginid', 'u_#__igin', 'iginid', 'CASCADE', 'RESTRICT'],
			['u_#__igrn_ibfk_1', 'u_#__igrn', 'igrnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igrn_inventory', 'u_#__igrn_inventory', 'crmid', 'u_#__igrn', 'igrnid', 'CASCADE', 'RESTRICT'],
			['u_#__igrnc_ibfk_1', 'u_#__igrnc', 'igrncid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igrnc_inventory', 'u_#__igrnc_inventory', 'crmid', 'u_#__igrnc', 'igrncid', 'CASCADE', 'RESTRICT'],
			['u_#__igrnccf_ibfk_1', 'u_#__igrnccf', 'igrncid', 'u_#__igrnc', 'igrncid', 'CASCADE', 'RESTRICT'],
			['u_#__igrncf_ibfk_1', 'u_#__igrncf', 'igrnid', 'u_#__igrn', 'igrnid', 'CASCADE', 'RESTRICT'],
			['u_#__iidn_ibfk_1', 'u_#__iidn', 'iidnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__iidn_inventory', 'u_#__iidn_inventory', 'crmid', 'u_#__iidn', 'iidnid', 'CASCADE', 'RESTRICT'],
			['u_#__iidncf_ibfk_1', 'u_#__iidncf', 'iidnid', 'u_#__iidn', 'iidnid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__incidentregisterincidentregisterid', 'u_#__incidentregister', 'incidentregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__incidentregistercfincidentregisterid', 'u_#__incidentregistercf', 'incidentregisterid', 'u_#__incidentregister', 'incidentregisterid', 'CASCADE', 'RESTRICT'],
			['u_#__ipreorder_ibfk_1', 'u_#__ipreorder', 'ipreorderid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ipreorder_inventory', 'u_#__ipreorder_inventory', 'crmid', 'u_#__ipreorder', 'ipreorderid', 'CASCADE', 'RESTRICT'],
			['u_#__ipreordercf_ibfk_1', 'u_#__ipreordercf', 'ipreorderid', 'u_#__ipreorder', 'ipreorderid', 'CASCADE', 'RESTRICT'],
			['u_#__istdn_ibfk_1', 'u_#__istdn', 'istdnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__istdn_inventory', 'u_#__istdn_inventory', 'crmid', 'u_#__istdn', 'istdnid', 'CASCADE', 'RESTRICT'],
			['u_#__istdncf_ibfk_1', 'u_#__istdncf', 'istdnid', 'u_#__istdn', 'istdnid', 'CASCADE', 'RESTRICT'],
			['u_#__istn_ibfk_1', 'u_#__istn', 'istnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istncf_ibfk_1', 'u_#__istncf', 'istnid', 'u_#__istn', 'istnid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_ibfk_1', 'u_#__istorages', 'istorageid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_address_ibfk_1', 'u_#__istorages_address', 'istorageaddressid', 'u_#__istorages', 'istorageid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_products_ibfk_1', 'u_#__istorages_products', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_products_ibfk_2', 'u_#__istorages_products', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istoragescf_ibfk_1', 'u_#__istoragescf', 'istorageid', 'u_#__istorages', 'istorageid', 'CASCADE', 'RESTRICT'],
			['u_#__istrn_ibfk_1', 'u_#__istrn', 'istrnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__istrn_inventory', 'u_#__istrn_inventory', 'crmid', 'u_#__istrn', 'istrnid', 'CASCADE', 'RESTRICT'],
			['u_#__istrncf_ibfk_1', 'u_#__istrncf', 'istrnid', 'u_#__istrn', 'istrnid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_knowledgebase', 'u_#__knowledgebase', 'knowledgebaseid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__knowledgebase_knowledgebase', 'u_#__knowledgebase_knowledgebase', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_2_u_#__knowledgebase_knowledgebase', 'u_#__knowledgebase_knowledgebase', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_knowledgebasecf', 'u_#__knowledgebasecf', 'knowledgebaseid', 'u_#__knowledgebase', 'knowledgebaseid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__locationregisterlocationregisterid', 'u_#__locationregister', 'locationregisterid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__locationregistercflocationregisterid', 'u_#__locationregistercf', 'locationregisterid', 'u_#__locationregister', 'locationregisterid', 'CASCADE', 'RESTRICT'],
			['u_#__mail_address_book_ibfk_1', 'u_#__mail_address_book', 'id', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__modentity_sequences_tabid_fk', 'u_#__modentity_sequences', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['u_#__modtracker_inv_id_fk', 'u_#__modtracker_inv', 'id', 'vtiger_modtracker_basic', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__multicompanymulticompanyid', 'u_#__multicompany', 'multicompanyid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__multicompanycfmulticompanyid', 'u_#__multicompanycf', 'multicompanyid', 'u_#__multicompany', 'multicompanyid', 'CASCADE', 'RESTRICT'],
			['fk_1_notification', 'u_#__notification', 'notificationid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__partners', 'u_#__partners', 'partnersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__partners_address_ibfk_1', 'u_#__partners_address', 'partneraddressid', 'u_#__partners', 'partnersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__partnerscf', 'u_#__partnerscf', 'partnersid', 'u_#__partners', 'partnersid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__pdf_inv_scheme_crmid', 'u_#__pdf_inv_scheme', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__picklist_close_state', 'u_#__picklist_close_state', 'fieldid', 'vtiger_field', 'fieldid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__reviewed_queue', 'u_#__reviewed_queue', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculations', 'u_#__scalculations', 'scalculationsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculations_inventory', 'u_#__scalculations_inventory', 'crmid', 'u_#__scalculations', 'scalculationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculationscf', 'u_#__scalculationscf', 'scalculationsid', 'u_#__scalculations', 'scalculationsid', 'CASCADE', 'RESTRICT'],
			['fk_crmid_idx', 'u_#__servicecontracts_sla_policy', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_sla_policy_idx', 'u_#__servicecontracts_sla_policy', 'sla_policy_id', 's_#__sla_policy', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiries', 'u_#__squoteenquiries', 'squoteenquiriesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiries_inventory', 'u_#__squoteenquiries_inventory', 'crmid', 'u_#__squoteenquiries', 'squoteenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiriescf', 'u_#__squoteenquiriescf', 'squoteenquiriesid', 'u_#__squoteenquiries', 'squoteenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotes', 'u_#__squotes', 'squotesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__squotes_address_ibfk_1', 'u_#__squotes_address', 'squotesaddressid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotes_inventory', 'u_#__squotes_inventory', 'crmid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotescf', 'u_#__squotescf', 'squotesid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorders', 'u_#__srecurringorders', 'srecurringordersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__srecurringorders_address_ibfk_1', 'u_#__srecurringorders_address', 'srecurringordersaddressid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorders_inventory', 'u_#__srecurringorders_inventory', 'crmid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorderscf', 'u_#__srecurringorderscf', 'srecurringordersid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscards', 'u_#__srequirementscards', 'srequirementscardsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscards_inventory', 'u_#__srequirementscards_inventory', 'crmid', 'u_#__srequirementscards', 'srequirementscardsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscardscf', 'u_#__srequirementscardscf', 'srequirementscardsid', 'u_#__srequirementscards', 'srequirementscardsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssalesprocesses', 'u_#__ssalesprocesses', 'ssalesprocessesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssalesprocessescf', 'u_#__ssalesprocessescf', 'ssalesprocessesid', 'u_#__ssalesprocesses', 'ssalesprocessesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorders', 'u_#__ssingleorders', 'ssingleordersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__ssingleorders_address_ibfk_1', 'u_#__ssingleorders_address', 'ssingleordersaddressid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorders_inventory', 'u_#__ssingleorders_inventory', 'crmid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorderscf', 'u_#__ssingleorderscf', 'ssingleordersid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__svendorenquiries', 'u_#__svendorenquiries', 'svendorenquiriesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__svendorenquiries_inventory', 'u_#__svendorenquiries_inventory', 'crmid', 'u_#__svendorenquiries', 'svendorenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__svendorenquiriescf', 'u_#__svendorenquiriescf', 'svendorenquiriesid', 'u_#__svendorenquiries', 'svendorenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__timeline', 'u_#__timeline', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__users_pinned_fk_1', 'u_#__users_pinned', 'owner_id', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['u_#__watchdog_record_ibfk_1', 'u_#__watchdog_record', 'record', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__watchdog_schedule_ibfk_1', 'u_#__watchdog_schedule', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
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
					[8, 'VTEmailTemplateTask', 'Email Template Task', 'VTEmailTemplateTask', 'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.php', 'com_vtiger_workflow/taskforms/VTEmailTemplateTask.tpl', '{"include":[],"exclude":[]}', null],
					[9, 'VTSendPdf', 'Send Pdf', 'VTSendPdf', 'modules/com_vtiger_workflow/tasks/VTSendPdf.php', 'com_vtiger_workflow/taskforms/VTSendPdf.tpl', '{"include":[],"exclude":[]}', null],
					[10, 'VTUpdateClosedTime', 'Update Closed Time', 'VTUpdateClosedTime', 'modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.php', 'com_vtiger_workflow/taskforms/VTUpdateClosedTime.tpl', '{"include":[],"exclude":[]}', null],
					[11, 'VTSendNotificationTask', 'Send Notification', 'VTSendNotificationTask', 'modules/com_vtiger_workflow/tasks/VTSendNotificationTask.php', 'com_vtiger_workflow/taskforms/VTSendNotificationTask.tpl', '{"include":["Calendar","Events"],"exclude":[]}', null],
					[12, 'VTAddressBookTask', 'Create Address Book', 'VTAddressBookTask', 'modules/com_vtiger_workflow/tasks/VTAddressBookTask.php', 'com_vtiger_workflow/taskforms/VTAddressBookTask.tpl', '{"include":["Contacts","OSSEmployees","Accounts","Leads","Vendors"],"exclude":[]}', null],
					[13, 'VTUpdateCalendarDates', 'LBL_UPDATE_DATES_CREATED_EVENTS_AUTOMATICALLY', 'VTUpdateCalendarDates', 'modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.php', 'com_vtiger_workflow/taskforms/VTUpdateCalendarDates.tpl', '{"include":["Accounts","Contacts","Leads","OSSEmployees","Vendors","Campaigns","HelpDesk","Project","ServiceContracts"],"exclude":["Calendar","FAQ","Events"]}', null],
					[14, 'VTUpdateWorkTime', 'LBL_UPDATE_WORK_TIME_AUTOMATICALLY', 'VTUpdateWorkTime', 'modules/com_vtiger_workflow/tasks/VTUpdateWorkTime.php', 'com_vtiger_workflow/taskforms/VTUpdateWorkTime.tpl', '{"include":["OSSTimeControl"],"exclude":[]}', null],
					[15, 'VTUpdateRelatedFieldTask', 'LBL_UPDATE_RELATED_FIELD', 'VTUpdateRelatedFieldTask', 'modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.php', 'com_vtiger_workflow/taskforms/VTUpdateRelatedFieldTask.tpl', '{"include":[],"exclude":[]}', ''],
					[16, 'VTWatchdog', 'LBL_NOTIFICATIONS', 'VTWatchdog', 'modules/com_vtiger_workflow/tasks/VTWatchdog.php', 'com_vtiger_workflow/taskforms/VTWatchdog.tpl', '{"include":[],"exclude":[]}', null],
					[17, 'VTAutoAssign', 'LBL_AUTO_ASSIGN', 'VTAutoAssign', 'modules/com_vtiger_workflow/tasks/VTAutoAssign.php', 'com_vtiger_workflow/taskforms/VTAutoAssign.tpl', '{"include":[],"exclude":[]}', null],
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
					[13, 'Events', 'Workflow for Events when Send Notification is True', '[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, 1, 'basic', 6, null, null, null, null, null, null],
					[14, 'Calendar', 'Workflow for Calendar Todos when Send Notification is True', '[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, 1, 'basic', 6, null, null, null, null, null, null],
					[25, 'HelpDesk', 'Ticket change: Send Email to Record Owner', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[26, 'HelpDesk', 'Ticket change: Send Email to Record Contact', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[27, 'HelpDesk', 'Ticket change: Send Email to Record Account', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[28, 'HelpDesk', 'Ticket Closed: Send Email to Record Owner', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[29, 'HelpDesk', 'Ticket Closed: Send Email to Record Contact', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[30, 'HelpDesk', 'Ticket Closed: Send Email to Record Account', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[31, 'HelpDesk', 'Ticket Creation: Send Email to Record Owner', '[]', 1, null, 'basic', 6, null, null, null, null, null, null],
					[33, 'HelpDesk', 'Ticket Creation: Send Email to Record Account', '[{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, null, 'basic', 6, null, null, null, null, null, null],
					[53, 'Contacts', 'Send Customer Login Details', '[{"fieldname":"emailoptout","operation":"is","value":"1","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, null, 'basic', 6, null, null, null, null, null, null],
					[54, 'HelpDesk', 'Update Closed Time', '[{"fieldname":"ticketstatus","operation":"is","value":"Rejected","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 2, null, 'basic', 6, null, null, null, null, null, null],
					[55, 'Contacts', 'Generate mail address book', '[]', 3, null, 'basic', 6, null, null, null, null, null, null],
					[57, 'ModComments', 'New comment added to ticket - Owner', '[{"fieldname":"customer","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, null, 'basic', 6, null, null, null, null, null, null],
					[58, 'ModComments', 'New comment added to ticket - account', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, null, 'basic', 6, null, null, null, null, null, null],
					[59, 'ModComments', 'New comment added to ticket - contact', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, null, 'basic', 6, null, null, null, null, null, null],
					[63, 'SQuoteEnquiries', 'Block edition', '[{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, null, 'basic', 6, 0, '', '', '', '', null],
					[64, 'SRequirementsCards', 'Block edition', '[{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, null, 'basic', 6, 0, '', '', '', '', null],
					[65, 'SCalculations', 'Block edition', '[{"fieldname":"scalculations_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"scalculations_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, null, 'basic', 6, 0, '', '', '', '', null],
					[66, 'SQuotes', 'Block edition', '[{"fieldname":"squotes_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squotes_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, null, 'basic', 6, 0, '', '', '', '', null],
					[67, 'SSingleOrders', 'Block edition', '[{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, null, 'basic', 6, 0, '', '', '', '', null],
					[68, 'SRecurringOrders', 'Block edition', '[{"fieldname":"srecurringorders_status","operation":"is","value":"PLL_UNREALIZED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srecurringorders_status","operation":"is","value":"PLL_REALIZED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, null, 'basic', 6, 0, '', '', '', '', null],
					[69, 'OSSTimeControl', 'LBL_UPDATE_WORK_TIME', '[]', 7, null, 'basic', 6, null, null, null, null, null, null],
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
					[133, 26, 'Notify Contact On Ticket Change', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"26";s:7:"summary";s:31:"Notify Contact On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"helpDeskChangeNotifyContacts";s:2:"id";i:133;}'],
					[134, 29, 'Notify contacts about closing of ticket.', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"29";s:7:"summary";s:40:"Notify contacts about closing of ticket.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"helpDeskClosedNotifyContacts";s:2:"id";i:134;}'],
					[135, 59, 'Notify Contact On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:59;s:7:"summary";s:45:"Notify Contact On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:26:"helpDeskNewCommentContacts";s:2:"id";i:135;}'],
					[136, 58, 'Notify Account On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:58;s:7:"summary";s:45:"Notify Account On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:25:"helpDeskNewCommentAccount";s:2:"id";i:136;}'],
					[137, 57, 'Notify Owner On new comment added to ticket from portal', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:57;s:7:"summary";s:55:"Notify Owner On new comment added to ticket from portal";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:23:"helpDeskNewCommentOwner";s:2:"id";i:137;}'],
					[138, 69, 'Update working time', 'O:16:"VTUpdateWorkTime":6:{s:18:"executeImmediately";b:0;s:10:"workflowId";i:69;s:7:"summary";s:19:"Update working time";s:6:"active";b:1;s:7:"trigger";N;s:2:"id";i:138;}'],
				]
			],
			'com_vtiger_workflowtasks_entitymethod' => [
				'columns' => ['workflowtasks_entitymethod_id', 'module_name', 'method_name', 'function_path', 'function_name'],
				'values' => [
					[8, 'ModComments', 'helpDeskNewCommentAccount', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HelpDeskWorkflow'],
					[9, 'ModComments', 'helpDeskNewCommentContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HelpDeskWorkflow'],
					[15, 'HelpDesk', 'helpDeskChangeNotifyContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HelpDeskWorkflow'],
					[16, 'HelpDesk', 'helpDeskClosedNotifyContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HelpDeskWorkflow'],
					[17, 'ModComments', 'helpDeskNewCommentOwner', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HelpDeskWorkflow'],
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
		</tr></table>', null, 1],
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
		</tr></table>', null, 1],
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
		</tr></table>', null, 1],
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
		</tr></table>', null, 1],
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
			<br /><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
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
		</tr></table>', null, 1],
					[43, 'Notify Owner On Ticket Create', 'N9', 'PLL_RECORD', 'HelpDesk', '$(translate : HelpDesk|LBL_NOTICE_CREATE)$ $(record : ticket_no)$:$(record : ticket_title)$', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">
						<div><i>$(translate : HelpDesk|SINGLE_HelpDesk)$ $(translate : HelpDesk|LBL_NOTICE_CREATED)$ </i></div>
						</td>
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;">$(record : modifiedby)$. <a href="$(record%20%3A%20CrmDetailViewURL)$"> $(record : ticket_no)$:$(record : ticket_title)$</a> $(record : ChangesListChanges)$</td>
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
			<br /><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>

		</tr></table>', null, 1],
					[44, 'Customer Portal Login Details', 'N10', 'PLL_RECORD', 'Contacts', 'Customer Portal Login Details', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(record : first_name)$ $(record : last_name)$,
<br />
Created for your account in the customer portal, below sending data access<br />
						Login: $(record : email)$<br />
						Password:
						</td>

					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<br /><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', null, 1],
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
						<td style="padding-bottom:10px;font-family:Arial, \'Sans-serif\';font-size:13px;color:#222;"><span><span>$(record : location)$</span><span> (<a href="https://maps.google.pl/maps?q=$(record%20:%20location)$" rel="noreferrer noopener">mapa</a>)</span></span></td>
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
		</tr></table>', null, 1],
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
		</tr></table>', null, 1],
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
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(record : first_name)$ $(record : last_name)$,<br />
						This is a test mail sent to confirm if a mail is actually being sent through the smtp server that you have configured. Feel free to delete this mail. CRM address: $(general : SiteUrl)$</td>
					</tr></table></div>
			</td>
		</tr><tr><td style="background-color:#f6f6f6;color:#888;border-top:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<div style="float:right;">$(organization : mailLogo)$</div>
			<br /><p><span style="font-size:12px;">$(translate : LBL_EMAIL_TEMPLATE_FOOTER)$</span></p>
			</td>
		</tr></table>', 'TestMailAboutTheMailServerConfiguration', 1],
					[103, 'ForgotPassword', 'N16', 'PLL_RECORD', 'Users', 'Request: ForgotPassword', '<table border="0" style="width:100%;font-family:Arial, \'Sans-serif\';border:1px solid #ccc;border-width:1px 2px 2px 1px;background-color:#fff;"><tr><td style="background-color:#f6f6f6;color:#888;border-bottom:1px solid #ccc;font-family:Arial, \'Sans-serif\';font-size:11px;">
			<h3 style="padding:0 0 6px 0;margin:0;font-family:Arial, \'Sans-serif\';font-size:16px;font-weight:bold;color:#222;"><span>$(translate : HelpDesk|LBL_NOTICE_WELCOME)$ YetiForce Sp. z o.o. </span></h3>
			</td>
		</tr><tr><td>
			<div style="padding:2px;">
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear user,<br />
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
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear $(record : first_name)$ $(record : last_name)$,<br />
						You recently requested a reminder of your access data for the YetiForce Portal.<br />
						You can login by entering the following data:<br /><br />
						Your username: $(record : email)$<br />
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
			<table border="0"><tr><td style="padding:0 1em 10px 0;font-family:Arial, \'Sans-serif\';font-size:13px;color:#888;white-space:nowrap;">Dear user,<br />
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
					[112, 'System warnings', 'N24', 'PLL_RECORD', 'Users', 'System warnings', '$(params : warnings)$', 'SystemWarnings', 7],
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
					[13, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 7],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 7],
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
					[5, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, null, 7],
					[6, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 13, 0, 0, null, 7],
					[7, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 14, 0, 0, null, 7],
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, null, 7],
					[9, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, null, 7],
					[10, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, null, 7],
					[12, 'tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, null, 7],
					[13, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, null, 7],
					[14, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 7],
					[15, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 7],
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
					[13, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 10],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 10],
				]
			],
			'u_#__finvoiceproforma_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
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
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, null, 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, null, 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, null, 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[11, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, null, 5],
					[12, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, null, 12],
					[13, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, null, 15],
					[14, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[6, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, null, 5],
					[7, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, null, 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, null, 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[4, 'seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, null, 5],
					[5, 'unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, null, 12],
					[6, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					[7, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					[8, 'ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, null, 15],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[10, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, null, 10],
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
					[5, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, null, 10],
					[6, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, null, 10],
					[7, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 6, 1, 0, null, 10],
					[8, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 7, 1, 0, null, 10],
					[9, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 8, 1, 0, null, 10],
					[10, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 10],
					[11, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 10],
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
					[4, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 10],
					[5, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 10],
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
					[1, 'name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 25],
					[2, 'qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 6],
					[3, 'discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 6],
					[4, 'marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 9, 1, 0, '{}', 10],
					[5, 'margin', 'LBL_MARGIN', 'Margin', 0, '0', 10, 1, 0, '{}', 6],
					[6, 'comment1', 'LBL_COMMENT', 'Comment', 0, '', 6, 2, 0, '{}', 0],
					[7, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, null, 6],
					[8, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, null, 6],
					[9, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 8, 1, 0, null, 6],
					[10, 'tax', 'LBL_TAX', 'Tax', 0, '0', 11, 1, 0, null, 6],
					[11, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 12, 1, 0, null, 6],
					[12, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 10, 0, 0, null, 1],
					[13, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 11, 0, 0, null, 1],
					[14, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, null, 1],
					[15, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, null, 6],
					[16, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 6],
					[17, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 6],
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
					[8, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 10],
					[9, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 10],
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
					[4, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 10],
					[5, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 10],
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
					[8, 'price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, null, 7],
					[9, 'total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, null, 7],
					[10, 'net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, null, 7],
					[11, 'purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 8, 1, 0, null, 7],
					[12, 'gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 12, 1, 0, null, 7],
					[13, 'discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 11, 0, 0, null, 1],
					[14, 'taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 12, 0, 0, null, 1],
					[15, 'currency', 'LBL_CURRENCY', 'Currency', 0, '', 13, 0, 0, null, 1],
					[16, 'unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, null, 7],
					[17, 'subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, null, 7],
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
