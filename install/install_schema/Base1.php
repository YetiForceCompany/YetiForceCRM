<?php namespace Importers;

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
					['userName', 'userName'],
					['dateTime', 'dateTime'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userID']
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
					['dateTime', 'dateTime'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userID', 'channel']]
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
					['message_condition', ['id', 'channel', 'dateTime']],
					['dateTime', 'dateTime'],
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
					['userName', 'userName'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userID']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_activatedonce' => [
				'columns' => [
					'workflow_id' => $this->integer()->notNull(),
					'entity_id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['workflow_id', 'entity_id']]
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
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflow_tasktypes_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
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
			'com_vtiger_workflows_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
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
				'index' => [
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
					['com_vtiger_workflowtasks_entitymethod_idx', 'workflowtasks_entitymethod_id', true],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'workflowtasks_entitymethod_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_entitymethod_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'com_vtiger_workflowtasks_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
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
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'dav_addressbookchanges' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->binary(200)->notNull(),
					'synctoken' => $this->integer()->unsigned()->notNull(),
					'addressbookid' => $this->integer()->unsigned()->notNull(),
					'operation' => $this->boolean()->notNull(),
				],
				'index' => [
					['addressbookid_synctoken', ['addressbookid', 'synctoken']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_addressbooks' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principaluri' => $this->binary(255),
					'displayname' => $this->stringType(),
					'uri' => $this->binary(200),
					'description' => $this->text(),
					'synctoken' => $this->integer()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['principaluri', ['principaluri(100)', 'uri(100)'], true],
					['dav_addressbooks_ibfk_1', 'principaluri(100)'],
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
					'operation' => $this->boolean()->notNull(),
				],
				'index' => [
					['calendarid_synctoken', ['calendarid', 'synctoken']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarobjects' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'calendardata' => $this->binary(),
					'uri' => $this->binary(200),
					'calendarid' => $this->integer(10)->unsigned()->notNull(),
					'lastmodified' => $this->integer()->unsigned(),
					'etag' => $this->binary(32),
					'size' => $this->integer()->unsigned()->notNull(),
					'componenttype' => $this->binary(8),
					'firstoccurence' => $this->integer()->unsigned(),
					'lastoccurence' => $this->integer()->unsigned(),
					'uid' => $this->binary(200),
					'crmid' => $this->integer(19),
				],
				'index' => [
					['calendarid', ['calendarid', 'uri(100)'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendars' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principaluri' => $this->binary(100),
					'displayname' => $this->stringType(100),
					'uri' => $this->binary(200),
					'synctoken' => $this->integer(10)->unsigned()->notNull()->defaultValue(1),
					'description' => $this->text(),
					'calendarorder' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->binary(10),
					'timezone' => $this->text(),
					'components' => $this->binary(21),
					'transparent' => $this->boolean()->notNull()->defaultValue(0),
				],
				'index' => [
					['principaluri', ['principaluri(100)', 'uri(100)']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_calendarsubscriptions' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->binary(200)->notNull(),
					'principaluri' => $this->binary(100)->notNull(),
					'source' => $this->text(),
					'displayname' => $this->stringType(100),
					'refreshrate' => $this->stringType(10),
					'calendarorder' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'calendarcolor' => $this->binary(10),
					'striptodos' => $this->boolean(),
					'stripalarms' => $this->boolean(),
					'stripattachments' => $this->boolean(),
					'lastmodified' => $this->integer()->unsigned(),
				],
				'index' => [
					['principaluri', ['principaluri(100)', 'uri(100)'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_cards' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'addressbookid' => $this->integer()->unsigned()->notNull(),
					'carddata' => $this->binary(),
					'uri' => $this->binary(200),
					'lastmodified' => $this->integer()->unsigned(),
					'etag' => $this->binary(32),
					'size' => $this->integer()->unsigned()->notNull(),
					'crmid' => $this->integer(19)->defaultValue(0),
				],
				'index' => [
					['addressbookid', ['addressbookid', 'crmid']],
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
					['principal_id', ['principal_id', 'member_id'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_principals' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'uri' => $this->binary(200)->notNull(),
					'email' => $this->binary(80),
					'displayname' => $this->stringType(80),
					'userid' => $this->integer(19),
				],
				'index' => [
					['uri', 'uri(100)', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_propertystorage' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'path' => $this->binary(1024)->notNull(),
					'name' => $this->binary(100)->notNull(),
					'valuetype' => $this->integer(10)->unsigned(),
					'value' => $this->binary(),
				],
				'index' => [
					['path_property', ['path(600)', 'name(100)'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_schedulingobjects' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'principaluri' => $this->binary(255),
					'calendardata' => $this->binary(),
					'uri' => $this->binary(200),
					'lastmodified' => $this->integer()->unsigned(),
					'etag' => $this->binary(32),
					'size' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8mb4'
			],
			'dav_users' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'username' => $this->binary(50),
					'digesta1' => $this->binary(32),
					'userid' => $this->integer(19)->unsigned(),
					'key' => $this->stringType(50),
				],
				'index' => [
					['username', 'username(50)', true],
					['userid', 'userid', true],
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
					'valid' => $this->boolean()->notNull()->defaultValue(0),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['expires_index', 'expires'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['user_id', 'mailbox']]
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
					['expires_index', 'expires'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['user_id', 'mailbox', 'uid']]
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
					['PRIMARY KEY', ['user_id', 'mailbox']]
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
					['PRIMARY KEY', ['contactgroup_id', 'contact_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contactgroups' => [
				'columns' => [
					'contactgroup_id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->boolean()->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull()->defaultValue(''),
				],
				'index' => [
					['roundcube_contactgroups_user_index', ['user_id', 'del']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_contacts' => [
				'columns' => [
					'contact_id' => $this->primaryKey()->unsigned(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->boolean()->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull()->defaultValue(''),
					'email' => $this->text()->notNull(),
					'firstname' => $this->stringType(128)->notNull()->defaultValue(''),
					'surname' => $this->stringType(128)->notNull()->defaultValue(''),
					'vcard' => $this->text(),
					'words' => $this->text(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
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
					['uniqueness', ['user_id', 'language'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_identities' => [
				'columns' => [
					'identity_id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'changed' => $this->dateTime()->notNull()->defaultValue('1000-01-01 00:00:00'),
					'del' => $this->boolean()->notNull()->defaultValue(0),
					'standard' => $this->boolean()->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull(),
					'organization' => $this->stringType(128)->notNull()->defaultValue(''),
					'email' => $this->stringType(128)->notNull(),
					'reply-to' => $this->stringType(128)->notNull()->defaultValue(''),
					'bcc' => $this->stringType(128)->notNull()->defaultValue(''),
					'signature' => $this->text(),
					'html_signature' => $this->boolean()->notNull()->defaultValue(0),
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
					'search_id' => $this->primaryKey()->unsigned(),
					'user_id' => $this->integer(10)->unsigned()->notNull(),
					'type' => $this->integer(3)->notNull()->defaultValue(0),
					'name' => $this->stringType(128)->notNull(),
					'data' => $this->text(),
				],
				'index' => [
					['uniqueness', ['user_id', 'type', 'name'], true],
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
					['changed_index', 'changed'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'sess_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'roundcube_system' => [
				'columns' => [
					'name' => $this->stringType(64)->notNull(),
					'value' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'name']
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
					'language' => $this->stringType(5),
					'preferences' => $this->text(),
					'actions' => $this->text(),
					'password' => $this->stringType(200),
					'crm_user_id' => $this->integer(19)->defaultValue(0),
				],
				'index' => [
					['username', ['username', 'mail_host'], true],
					['crm_user_id', 'crm_user_id'],
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
					['rcuser_id', 'rcuser_id'],
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
					'status' => $this->boolean()->defaultValue(0),
					'time' => $this->dateTime(),
				],
				'index' => [
					['activityid', 'activityid'],
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
					['announcementstatus', 'announcementstatus'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'announcementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcement_mark' => [
				'columns' => [
					'announcementid' => $this->integer()->notNull(),
					'userid' => $this->integer()->notNull(),
					'date' => $this->dateTime()->notNull(),
					'status' => $this->boolean()->notNull()->defaultValue(0),
				],
				'index' => [
					['userid', ['userid', 'status']],
					['announcementid', ['announcementid', 'userid', 'date', 'status']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['announcementid', 'userid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__announcementcf' => [
				'columns' => [
					'announcementid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'announcementid']
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
					'email' => $this->stringType(50)->defaultValue(''),
					'active' => $this->boolean()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'competitionid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'competitionaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__competitioncf' => [
				'columns' => [
					'competitionid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'competitionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_label' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
					'label' => $this->stringType(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'crmid']
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
					['crmid', ['crmid', 'fieldname']],
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
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__crmentity_search_label' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
					'searchlabel' => $this->stringType()->notNull(),
					'setype' => $this->stringType(30)->notNull(),
					'userid' => $this->text()->notNull(),
				],
				'index' => [
					['searchlabel', 'searchlabel'],
					['searchlabel_2', ['searchlabel', 'setype']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'crmid']
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
					['mix', ['crmid', 'userid'], true],
					['crmid', 'crmid'],
					['userid', 'userid'],
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
					['crmid', 'crmid'],
					['relcrmid', 'relcrmid'],
					['mix', ['crmid', 'module', 'relcrmid', 'relmodule', 'userid']],
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
					['related_to', 'related_to'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fbookkeepingid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fbookkeepingcf' => [
				'columns' => [
					'fbookkeepingid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fbookkeepingid']
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
					['accountid', 'accountid'],
					['finvoiceid', 'finvoiceid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fcorectinginvoiceid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fcorectinginvoiceaddressid']
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
					'discountparam' => $this->stringType()->notNull(),
					'comment1' => $this->stringType(500),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'discountmode' => $this->boolean()->notNull()->defaultValue(0),
					'taxmode' => $this->boolean()->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__fcorectinginvoicecf' => [
				'columns' => [
					'fcorectinginvoiceid' => $this->integer(19)->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fcorectinginvoiceid']
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
				],
				'index' => [
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'finvoiceid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'finvoiceaddressid']
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
					'discountmode' => $this->boolean()->notNull()->defaultValue(0),
					'taxmode' => $this->boolean()->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'discountparam' => $this->stringType()->notNull(),
					'comment1' => $this->stringType(500),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoicecf' => [
				'columns' => [
					'finvoiceid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'finvoiceid']
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
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'finvoiceproformaid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'finvoiceproformaaddressid']
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
					'discountmode' => $this->boolean()->notNull()->defaultValue(0),
					'taxmode' => $this->boolean()->notNull()->defaultValue(0),
					'name' => $this->integer()->notNull()->defaultValue(0),
					'qty' => $this->decimal('25,3')->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discount' => $this->decimal('27,8')->defaultValue(0),
					'discountparam' => $this->stringType()->notNull(),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->stringType(500),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__finvoiceproformacf' => [
				'columns' => [
					'finvoiceproformaid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'finvoiceproformaid']
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
					['storageid', 'storageid'],
					['accountid', 'accountid'],
					['ssingleordersid', 'ssingleordersid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igdnid']
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
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
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
					['storageid', 'storageid'],
					['accountid', 'accountid'],
					['igdnid', 'igdnid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igdncid']
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
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdnccf' => [
				'columns' => [
					'igdncid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igdncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igdncf' => [
				'columns' => [
					'igdnid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igdnid']
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
					['storageid', 'storageid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'iginid']
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
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igincf' => [
				'columns' => [
					'iginid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'iginid']
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
					['storageid', 'storageid'],
					['vendorid', 'vendorid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igrnid']
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
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(200),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
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
					['storageid', 'storageid'],
					['vendorid', 'vendorid'],
					['igrnid', 'igrnid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igrncid']
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
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrnccf' => [
				'columns' => [
					'igrncid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igrncid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__igrncf' => [
				'columns' => [
					'igrnid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'igrnid']
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
					['storageid', 'storageid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'iidnid']
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
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(200),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__iidncf' => [
				'columns' => [
					'iidnid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'iidnid']
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
					['storageid', 'storageid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ipreorderid']
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
					'comment1' => $this->stringType(500),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ipreordercf' => [
				'columns' => [
					'ipreorderid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ipreorderid']
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
					['storageid', 'storageid'],
					['accountid', 'accountid'],
					['process', 'process'],
					['subprocess', 'subprocess'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istdnid']
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
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istdncf' => [
				'columns' => [
					'istdnid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istdnid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istnid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istncf' => [
				'columns' => [
					'istnid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istnid']
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
					'pos' => $this->stringType()->defaultValue(''),
				],
				'index' => [
					['parentid', 'parentid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istorageid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istorageaddressid']
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
					['crmid', 'crmid'],
					['relcrmid', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istoragescf' => [
				'columns' => [
					'istorageid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istorageid']
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
					['storageid', 'storageid'],
					['vendorid', 'vendorid'],
					['process', 'process'],
					['subprocess', 'subprocess'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istrnid']
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
					'comment1' => $this->stringType(500),
					'unit' => $this->stringType(),
					'ean' => $this->stringType(),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__istrncf' => [
				'columns' => [
					'istrnid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'istrnid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'knowledgebaseid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__knowledgebasecf' => [
				'columns' => [
					'knowledgebaseid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'knowledgebaseid']
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
					['email', ['email', 'name']],
					['id', 'id'],
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
					['ruid', 'ruid'],
					['cuid', 'cuid'],
					['key', 'key'],
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
					['userid', ['userid', 'key'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__notification' => [
				'columns' => [
					'id' => $this->integer()->unsigned()->notNull(),
					'title' => $this->stringType(),
					'number' => $this->stringType(50),
					'notification_status' => $this->stringType(),
					'notification_type' => $this->stringType()->defaultValue(''),
					'link' => $this->integer(),
					'process' => $this->integer(),
					'subprocess' => $this->integer(),
				],
				'index' => [
					['relatedid', 'link'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
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
					['u_#__openstreetmap_lat_lon', ['lat', 'lon']],
					['crmid_type', ['crmid', 'type']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__openstreetmap_address_updater' => [
				'columns' => [
					'crmid' => $this->integer(),
				],
				'index' => [
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
					['u_#__openstreetmap_cache_user_id_module_name_idx', ['user_id', 'module_name']],
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
					['crmid', ['crmid', 'type']],
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
					'email' => $this->stringType(50)->defaultValue(''),
					'active' => $this->boolean()->defaultValue(0),
					'category' => $this->stringType()->defaultValue(''),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'partnersid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'partneraddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__partnerscf' => [
				'columns' => [
					'partnersid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'partnersid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'srecurringordersid']
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
					['userid', 'userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
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
					['salesprocessid', 'salesprocessid'],
					['accountid', 'accountid'],
					['srequirementscardsid', 'srequirementscardsid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'scalculationsid']
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
					'comment1' => $this->stringType(500),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__scalculationscf' => [
				'columns' => [
					'scalculationsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'scalculationsid']
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
					['salesprocessid', 'salesprocessid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'squoteenquiriesid']
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
					'comment1' => $this->stringType(500),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squoteenquiriescf' => [
				'columns' => [
					'squoteenquiriesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'squoteenquiriesid']
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
					['salesprocessid', 'salesprocessid'],
					['scalculationsid', 'scalculationsid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'squotesid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'squotesaddressid']
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
					'discountparam' => $this->stringType()->notNull(),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'comment1' => $this->stringType(500),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discountmode' => $this->boolean()->notNull()->defaultValue(0),
					'taxmode' => $this->boolean()->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__squotescf' => [
				'columns' => [
					'squotesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'squotesid']
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
					['salesprocessid', 'salesprocessid'],
					['squotesid', 'squotesid'],
					['accountid', 'accountid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'srecurringordersid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'srecurringordersaddressid']
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
					'discountparam' => $this->stringType()->notNull(),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'comment1' => $this->stringType(500),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srecurringorderscf' => [
				'columns' => [
					'srecurringordersid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'srecurringordersid']
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
					['salesprocessid', 'salesprocessid'],
					['accountid', 'accountid'],
					['quoteenquiryid', 'quoteenquiryid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'srequirementscardsid']
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
					'comment1' => $this->stringType(500),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__srequirementscardscf' => [
				'columns' => [
					'srequirementscardsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'srequirementscardsid']
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
				],
				'index' => [
					['related_to', 'related_to'],
					['campaignid', 'campaignid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ssalesprocessesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssalesprocessescf' => [
				'columns' => [
					'ssalesprocessesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ssalesprocessesid']
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
					'pos' => $this->stringType(100)->defaultValue(''),
					'istoragesid' => $this->integer(),
					'table' => $this->stringType(20),
					'seat' => $this->stringType(20),
					'ssingleorders_source' => $this->stringType()->defaultValue(''),
				],
				'index' => [
					['salesprocessid', 'salesprocessid'],
					['squotesid', 'squotesid'],
					['accountid', 'accountid'],
					['istoragesid', 'istoragesid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ssingleordersid']
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ssingleordersaddressid']
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
					'discountparam' => $this->stringType()->notNull(),
					'marginp' => $this->decimal('27,8')->defaultValue(0),
					'margin' => $this->decimal('27,8')->defaultValue(0),
					'tax' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'taxparam' => $this->stringType()->notNull(),
					'comment1' => $this->stringType(500),
					'price' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'total' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'net' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'purchase' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'gross' => $this->decimal('27,8')->notNull()->defaultValue(0),
					'discountmode' => $this->boolean()->notNull()->defaultValue(0),
					'taxmode' => $this->boolean()->notNull()->defaultValue(0),
					'currency' => $this->integer(10),
					'currencyparam' => $this->stringType(1024),
					'qtyparam' => $this->boolean()->notNull()->defaultValue(0),
					'unit' => $this->stringType(),
					'subunit' => $this->stringType(),
				],
				'index' => [
					['id', 'id'],
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
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'defaultvalue' => $this->stringType(),
					'sequence' => $this->integer(10)->unsigned()->notNull(),
					'block' => $this->boolean()->unsigned()->notNull(),
					'displaytype' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text(),
					'colspan' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
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
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['module', 'field', 'tofield']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__ssingleorderscf' => [
				'columns' => [
					'ssingleordersid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ssingleordersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_module' => [
				'columns' => [
					'userid' => $this->integer()->unsigned()->notNull(),
					'module' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['userid', 'userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'module']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_record' => [
				'columns' => [
					'userid' => $this->integer()->unsigned()->notNull(),
					'record' => $this->integer()->notNull(),
					'state' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['userid', 'userid'],
					['record', 'record'],
					['userid_2', ['userid', 'record', 'state']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'record']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_#__watchdog_schedule' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'frequency' => $this->smallInteger()->notNull(),
					'last_execution' => $this->dateTime(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userid']
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
					[1, 'VTEmailTask', 'Send Mail', 'VTEmailTask', 'modules/com_vtiger_workflow/tasks/VTEmailTask.inc', 'com_vtiger_workflow/taskforms/VTEmailTask.tpl', '{"include":[],"exclude":[]}', ''],
					[2, 'VTEntityMethodTask', 'Invoke Custom Function', 'VTEntityMethodTask', 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc', 'com_vtiger_workflow/taskforms/VTEntityMethodTask.tpl', '{"include":[],"exclude":[]}', ''],
					[3, 'VTCreateTodoTask', 'Create Todo', 'VTCreateTodoTask', 'modules/com_vtiger_workflow/tasks/VTCreateTodoTask.inc', 'com_vtiger_workflow/taskforms/VTCreateTodoTask.tpl', '{"include":["Accounts","Leads","Contacts","HelpDesk","Campaigns","Project","ServiceContracts","Vendors","Partners","Competition","OSSEmployees","SSalesProcesses","SQuoteEnquiries","SRequirementsCards","SCalculations","SQuotes","SSingleOrders","SRecurringOrders"],"exclude":["Calendar","FAQ","Events"]}', ''],
					[4, 'VTCreateEventTask', 'Create Event', 'VTCreateEventTask', 'modules/com_vtiger_workflow/tasks/VTCreateEventTask.inc', 'com_vtiger_workflow/taskforms/VTCreateEventTask.tpl', '{"include":["Accounts","Leads","Contacts","HelpDesk","Campaigns","Project","ServiceContracts","Vendors","Partners","Competition","OSSEmployees","SSalesProcesses","SQuoteEnquiries","SRequirementsCards","SCalculations","SQuotes","SSingleOrders","SRecurringOrders"],"exclude":["Calendar","FAQ","Events"]}', ''],
					[5, 'VTUpdateFieldsTask', 'Update Fields', 'VTUpdateFieldsTask', 'modules/com_vtiger_workflow/tasks/VTUpdateFieldsTask.inc', 'com_vtiger_workflow/taskforms/VTUpdateFieldsTask.tpl', '{"include":[],"exclude":[]}', ''],
					[6, 'VTCreateEntityTask', 'Create Entity', 'VTCreateEntityTask', 'modules/com_vtiger_workflow/tasks/VTCreateEntityTask.inc', 'com_vtiger_workflow/taskforms/VTCreateEntityTask.tpl', '{"include":[],"exclude":[]}', ''],
					[7, 'VTSMSTask', 'SMS Task', 'VTSMSTask', 'modules/com_vtiger_workflow/tasks/VTSMSTask.inc', 'com_vtiger_workflow/taskforms/VTSMSTask.tpl', '{"include":[],"exclude":[]}', 'SMSNotifier'],
					[8, 'VTEmailTemplateTask', 'Email Template Task', 'VTEmailTemplateTask', 'modules/com_vtiger_workflow/tasks/VTEmailTemplateTask.inc', 'com_vtiger_workflow/taskforms/VTEmailTemplateTask.tpl', '{"include":[],"exclude":[]}', NULL],
					[9, 'VTSendPdf', 'Send Pdf', 'VTSendPdf', 'modules/com_vtiger_workflow/tasks/VTSendPdf.inc', 'com_vtiger_workflow/taskforms/VTSendPdf.tpl', '{"include":[],"exclude":[]}', NULL],
					[10, 'VTUpdateClosedTime', 'Update Closed Time', 'VTUpdateClosedTime', 'modules/com_vtiger_workflow/tasks/VTUpdateClosedTime.inc', 'com_vtiger_workflow/taskforms/VTUpdateClosedTime.tpl', '{"include":[],"exclude":[]}', NULL],
					[11, 'VTSendNotificationTask', 'Send Notification', 'VTSendNotificationTask', 'modules/com_vtiger_workflow/tasks/VTSendNotificationTask.inc', 'com_vtiger_workflow/taskforms/VTSendNotificationTask.tpl', '{"include":["Calendar","Events"],"exclude":[]}', NULL],
					[12, 'VTAddressBookTask', 'Create Address Book', 'VTAddressBookTask', 'modules/com_vtiger_workflow/tasks/VTAddressBookTask.inc', 'com_vtiger_workflow/taskforms/VTAddressBookTask.tpl', '{"include":["Contacts","OSSEmployees","Accounts","Leads","Vendors"],"exclude":[]}', NULL],
					[13, 'VTUpdateCalendarDates', 'LBL_UPDATE_DATES_CREATED_EVENTS_AUTOMATICALLY', 'VTUpdateCalendarDates', 'modules/com_vtiger_workflow/tasks/VTUpdateCalendarDates.inc', 'com_vtiger_workflow/taskforms/VTUpdateCalendarDates.tpl', '{"include":["Accounts","Contacts","Leads","OSSEmployees","Vendors","Campaigns","HelpDesk","Project","ServiceContracts"],"exclude":["Calendar","FAQ","Events"]}', NULL],
					[14, 'VTUpdateWorkTime', 'LBL_UPDATE_WORK_TIME_AUTOMATICALLY', 'VTUpdateWorkTime', 'modules/com_vtiger_workflow/tasks/VTUpdateWorkTime.inc', 'com_vtiger_workflow/taskforms/VTUpdateWorkTime.tpl', '{"include":["OSSTimeControl"],"exclude":[]}', NULL],
					[15, 'VTUpdateRelatedFieldTask', 'LBL_UPDATE_RELATED_FIELD', 'VTUpdateRelatedFieldTask', 'modules/com_vtiger_workflow/tasks/VTUpdateRelatedFieldTask.inc', 'com_vtiger_workflow/taskforms/VTUpdateRelatedFieldTask.tpl', '{"include":[],"exclude":[]}', ''],
					[16, 'VTWatchdog', 'LBL_NOTIFICATIONS', 'VTWatchdog', 'modules/com_vtiger_workflow/tasks/VTWatchdog.inc', 'com_vtiger_workflow/taskforms/VTWatchdog.tpl', '{"include":[],"exclude":[]}', NULL],
				]
			],
			'com_vtiger_workflow_tasktypes_seq' => [
				'columns' => ['id'],
				'values' => [
					[16],
				]
			],
			'com_vtiger_workflows' => [
				'columns' => ['module_name', 'summary', 'test', 'execution_condition', 'defaultworkflow', 'type', 'filtersavedinnew', 'schtypeid', 'schdayofmonth', 'schdayofweek', 'schannualdates', 'schtime', 'nexttrigger_time'],
				'values' => [
					['Events', 'Workflow for Events when Send Notification is True', '[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, 1, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Calendar', 'Workflow for Calendar Todos when Send Notification is True', '[{"fieldname":"sendnotification","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, 1, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket change: Send Email to Record Owner', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket change: Send Email to Record Contact', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket change: Send Email to Record Account', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is not","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket Closed: Send Email to Record Owner', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(assigned_user_id : (Users) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket Closed: Send Email to Record Contact', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket Closed: Send Email to Record Account', '[{"fieldname":"ticketstatus","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket Creation: Send Email to Record Owner', '[]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Ticket Creation: Send Email to Record Account', '[{"fieldname":"(parent_id : (Accounts) emailoptout)","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Leads', 'Marketing process - Data Verification', '[{"fieldname":"leadstatus","operation":"is","value":"LBL_REQUIRES_VERIFICATION","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Leads', 'Marketing process - Preliminary analysis', '[{"fieldname":"leadstatus","operation":"is","value":"LBL_PRELIMINARY_ANALYSIS_OF","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Leads', 'Marketing process - Advanced Analysis', '[{"fieldname":"leadstatus","operation":"is","value":"LBL_ADVANCED_ANALYSIS","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Leads', 'Marketing process - Initial acquisition', '[{"fieldname":"leadstatus","operation":"is","value":"LBL_INITIAL_ACQUISITION","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Leads', 'Proces marketingowy - Kontakt w przyszłości', '[{"fieldname":"leadstatus","operation":"is","value":"LBL_CONTACTS_IN_THE_FUTURE","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Contacts', 'Generate Customer Login Details', '[{"fieldname":"portal","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Contacts', 'Send Customer Login Details', '[{"fieldname":"emailoptout","operation":"is","value":"1","valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"has changed","value":null,"valuetype":"rawtext","joincondition":"and","groupjoin":"and","groupid":"0"},{"fieldname":"portal","operation":"is","value":"1","valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 4, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['HelpDesk', 'Update Closed Time', '[{"fieldname":"ticketstatus","operation":"is","value":"Rejected","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ticketstatus","operation":"is","value":"Closed","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 2, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['Contacts', 'Generate mail address book', '[]', 3, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['ModComments', 'New comment added to ticket - Owner', '[{"fieldname":"customer","operation":"is not empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['ModComments', 'New comment added to ticket - account', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['ModComments', 'New comment added to ticket - contact', '[{"fieldname":"customer","operation":"is empty","value":null,"valuetype":"rawtext","joincondition":"","groupjoin":"and","groupid":"0"}]', 1, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
					['SQuoteEnquiries', 'Block edition', '[{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squoteenquiries_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', '0000-00-00 00:00:00'],
					['SRequirementsCards', 'Block edition', '[{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srequirementscards_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', '0000-00-00 00:00:00'],
					['SCalculations', 'Block edition', '[{"fieldname":"scalculations_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"scalculations_status","operation":"is","value":"PLL_COMPLETED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', '0000-00-00 00:00:00'],
					['SQuotes', 'Block edition', '[{"fieldname":"squotes_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"squotes_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', '0000-00-00 00:00:00'],
					['SSingleOrders', 'Block edition', '[{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_CANCELLED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"ssingleorders_status","operation":"is","value":"PLL_ACCEPTED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', '0000-00-00 00:00:00'],
					['SRecurringOrders', 'Block edition', '[{"fieldname":"srecurringorders_status","operation":"is","value":"PLL_UNREALIZED","valuetype":"rawtext","joincondition":"or","groupjoin":null,"groupid":"1"},{"fieldname":"srecurringorders_status","operation":"is","value":"PLL_REALIZED","valuetype":"rawtext","joincondition":"","groupjoin":null,"groupid":"1"}]', 9, NULL, 'basic', 6, 0, '', '', '', '', '0000-00-00 00:00:00'],
					['OSSTimeControl', 'LBL_UPDATE_WORK_TIME', '[]', 7, NULL, 'basic', 6, NULL, NULL, NULL, NULL, NULL, NULL],
				]
			],
			'com_vtiger_workflows_seq' => [
				'columns' => ['id'],
				'values' => [
					[69],
				]
			],
			'com_vtiger_workflowtasks' => [
				'columns' => ['workflow_id', 'summary', 'task'],
				'values' => [
					[47, 'Data verification', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"47";s:7:"summary";s:17:"Data verification";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:17:"Data verification";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:91;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[48, 'Red news on the website', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"48";s:7:"summary";s:23:"Red news on the website";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:23:"Red news on the website";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:92;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[48, 'Read social networking news  ', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"48";s:7:"summary";s:29:"Read social networking news  ";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Read social networking news  ";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:93;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Mail or call', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:12:"Mail or call";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:12:"Mail or call";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:94;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Determine the decision maker ', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:29:"Determine the decision maker ";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Determine the decision maker ";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:95;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Present experience of company', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:29:"Present experience of company";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Present experience of company";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:96;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Present products and services', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:29:"Present products and services";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Present products and services";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:97;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Preliminary analysis of the client\'s needs', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:42:"Preliminary analysis of the client\'s needs";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:42:"Preliminary analysis of the client\'s needs";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:98;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Update: \'Outsourced services\'', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:29:"Update: \'Outsourced services\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Update: \'Outsourced services\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:99;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Update: \'Outsourced products\'', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:29:"Update: \'Outsourced products\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:29:"Update: \'Outsourced products\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:100;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[49, 'Update preliminary agreements in the system', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"49";s:7:"summary";s:43:"Update preliminary agreements in the system";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:43:"Update preliminary agreements in the system";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:101;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[50, 'Specify client\'s needs', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"50";s:7:"summary";s:22:"Specify client\'s needs";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:22:"Specify client\'s needs";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:102;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[50, 'Update information on: \'Interested in services\' ', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"50";s:7:"summary";s:48:"Update information on: \'Interested in services\' ";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:48:"Update information on: \'Interested in services\' ";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:103;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[50, 'Update information on: \'Interested in products\'', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"50";s:7:"summary";s:47:"Update information on: \'Interested in products\'";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:47:"Update information on: \'Interested in products\'";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:104;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[51, 'Mail or call', 'O:16:"VTCreateTodoTask":26:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"51";s:7:"summary";s:12:"Mail or call";s:6:"active";b:0;s:7:"trigger";N;s:4:"todo";s:12:"Mail or call";s:11:"description";s:0:"";s:16:"sendNotification";s:0:"";s:4:"time";s:5:"08:00";s:4:"date";s:0:"";s:6:"status";s:0:"";s:8:"priority";s:6:"Medium";s:4:"days";s:0:"";s:9:"direction";s:5:"after";s:9:"datefield";s:12:"modifiedtime";s:16:"assigned_user_id";s:15:"copyParentOwner";s:2:"id";i:105;s:10:"days_start";s:1:"2";s:8:"days_end";s:1:"3";s:15:"direction_start";s:5:"after";s:15:"datefield_start";s:12:"modifiedtime";s:13:"direction_end";s:5:"after";s:13:"datefield_end";s:12:"modifiedtime";s:14:"doNotDuplicate";s:0:"";s:15:"duplicateStatus";s:0:"";s:11:"updateDates";s:0:"";}'],
					[33, 'Notify Account On Ticket Create', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"33";s:7:"summary";s:31:"Notify Account On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"40";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:106;}'],
					[31, 'Notify Owner On Ticket Create', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"31";s:7:"summary";s:29:"Notify Owner On Ticket Create";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"43";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:108;}'],
					[30, 'Notify Account On Ticket Closed', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"30";s:7:"summary";s:31:"Notify Account On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"38";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:109;}'],
					[28, 'Notify Owner On Ticket Closed', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"28";s:7:"summary";s:29:"Notify Owner On Ticket Closed";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"42";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:111;}'],
					[27, 'Notify Account On Ticket Change', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"27";s:7:"summary";s:31:"Notify Account On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"36";s:11:"attachments";s:0:"";s:5:"email";s:25:"parent_id=Accounts=email1";s:10:"copy_email";s:0:"";s:2:"id";i:112;}'],
					[25, 'Notify Owner On Ticket Change', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"25";s:7:"summary";s:29:"Notify Owner On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"35";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:114;}'],
					[52, 'Create Portal Login Details', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"52";s:7:"summary";s:27:"Create Portal Login Details";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:24:"CreatePortalLoginDetails";s:2:"id";i:116;}'],
					[14, 'Notification Email to Record Owner', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"14";s:7:"summary";s:34:"Notification Email to Record Owner";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"46";s:11:"attachments";s:0:"";s:5:"email";s:29:"assigned_user_id=Users=email1";s:10:"copy_email";s:0:"";s:2:"id";i:119;}'],
					[53, 'Send Customer Login Details', 'O:19:"VTEmailTemplateTask":10:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"53";s:7:"summary";s:27:"Send Customer Login Details";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"44";s:11:"attachments";s:0:"";s:5:"email";s:5:"email";s:10:"copy_email";s:0:"";s:2:"id";i:120;}'],
					[54, 'Update Closed Time', 'O:18:"VTUpdateClosedTime":6:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"54";s:7:"summary";s:18:"Update Closed Time";s:6:"active";b:0;s:7:"trigger";N;s:2:"id";i:121;}'],
					[13, 'Send invitations', 'O:22:"VTSendNotificationTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"13";s:7:"summary";s:16:"Send invitations";s:6:"active";b:0;s:7:"trigger";N;s:8:"template";s:2:"45";s:2:"id";i:122;}'],
					[55, 'Generate mail address book', 'O:17:"VTAddressBookTask":7:{s:18:"executeImmediately";b:0;s:10:"workflowId";s:2:"55";s:7:"summary";s:26:"Generate mail address book";s:6:"active";b:1;s:7:"trigger";N;s:4:"test";s:0:"";s:2:"id";i:123;}'],
					[53, 'Mark portal users password as sent.', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"53";s:7:"summary";s:35:"Mark portal users password as sent.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:16:"MarkPasswordSent";s:2:"id";i:128;}'],
					[26, 'Notify Contact On Ticket Change', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"26";s:7:"summary";s:31:"Notify Contact On Ticket Change";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"HeldDeskChangeNotifyContacts";s:2:"id";i:133;}'],
					[29, 'Notify contacts about closing of ticket.', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";s:2:"29";s:7:"summary";s:40:"Notify contacts about closing of ticket.";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:28:"HeldDeskClosedNotifyContacts";s:2:"id";i:134;}'],
					[59, 'Notify Contact On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:59;s:7:"summary";s:45:"Notify Contact On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:26:"HeldDeskNewCommentContacts";s:2:"id";i:135;}'],
					[58, 'Notify Account On New comment added to ticket', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:58;s:7:"summary";s:45:"Notify Account On New comment added to ticket";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:25:"HeldDeskNewCommentAccount";s:2:"id";i:136;}'],
					[57, 'Notify Owner On new comment added to ticket from portal', 'O:18:"VTEntityMethodTask":7:{s:18:"executeImmediately";b:1;s:10:"workflowId";i:57;s:7:"summary";s:55:"Notify Owner On new comment added to ticket from portal";s:6:"active";b:0;s:7:"trigger";N;s:10:"methodName";s:23:"HeldDeskNewCommentOwner";s:2:"id";i:137;}'],
					[69, 'Update working time', 'O:16:"VTUpdateWorkTime":6:{s:18:"executeImmediately";b:0;s:10:"workflowId";i:69;s:7:"summary";s:19:"Update working time";s:6:"active";b:1;s:7:"trigger";N;s:2:"id";i:138;}'],
				]
			],
			'com_vtiger_workflowtasks_entitymethod' => [
				'columns' => ['workflowtasks_entitymethod_id', 'module_name', 'method_name', 'function_path', 'function_name'],
				'values' => [
					[3, 'Contacts', 'CreatePortalLoginDetails', 'modules/Contacts/handlers/ContactsHandler.php', 'Contacts_createPortalLoginDetails'],
					[8, 'ModComments', 'HeldDeskNewCommentAccount', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskNewCommentAccount'],
					[9, 'ModComments', 'HeldDeskNewCommentContacts', 'modules/HelpDesk/workflows/HelpDeskWorkflow.php', 'HeldDeskNewCommentContacts'],
					[11, 'Contacts', 'MarkPasswordSent', 'modules/Contacts/handlers/ContactsHandler.php', 'Contacts_markPasswordSent'],
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
			'u_yf_fcorectinginvoice_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 20],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					['discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 11, 2, 0, '{}', 0],
					['currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, '', 7],
					['discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 13, 0, 0, '', 7],
					['taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 14, 0, 0, '', 7],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, '', 7],
					['gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, '', 7],
					['net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, '', 7],
					['tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, '', 7],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, '', 7],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
				]
			],
			'u_yf_fcorectinginvoice_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_finvoice_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 20],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					['discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 11, 2, 0, '{}', 0],
					['currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, NULL, 7],
					['discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 13, 0, 0, NULL, 7],
					['taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 14, 0, 0, NULL, 7],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 7],
					['gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, NULL, 7],
					['net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, NULL, 7],
					['tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, NULL, 7],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 7],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
				]
			],
			'u_yf_finvoice_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_finvoiceproforma_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['currency', 'LBL_CURRENCY', 'Currency', 0, '', 1, 0, 0, '', 1],
					['discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 2, 0, 0, '', 1],
					['taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 3, 0, 0, '', 1],
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 30],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, '{}', 10],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, '{}', 10],
					['discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 10],
					['net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, '{}', 10],
					['tax', 'LBL_TAX', 'Tax', 0, '0', 8, 1, 0, '{}', 10],
					['gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 9, 1, 0, '{}', 10],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 12, 2, 0, '{}', 0],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_yf_finvoiceproforma_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_github' => [
				'columns' => ['client_id', 'token', 'username'],
				'values' => [
					['', '', ''],
				]
			],
			'u_yf_igdn_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_igdn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_igdnc_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, '', 10],
				]
			],
			'u_yf_igdnc_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_igin_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_igin_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_igrn_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_igrn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_igrnc_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, '', 10],
				]
			],
			'u_yf_igrnc_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_iidn_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, NULL, 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, NULL, 12],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, NULL, 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_iidn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_ipreorder_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '{}', 5],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '{}', 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '{}', 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_ipreorder_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_istdn_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_istdn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_istrn_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 1, 1, 0, '{"modules":"Products","limit":" "}', 29],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 5, 1, 0, '{}', 15],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 6, 1, 0, '{}', 12],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 7, 1, 0, '{}', 12],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 5, 2, 0, '{}', 0],
					['seq', 'LBL_ITEM_NUMBER', 'ItemNumber', 0, '', 0, 1, 0, '', 5],
					['unit', 'LBL_UNIT', 'Value', 0, '', 3, 1, 10, '', 12],
					['ean', 'LBL_EAN', 'Value', 0, '', 2, 1, 10, '', 15],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 4, 1, 10, NULL, 10],
				]
			],
			'u_yf_istrn_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'ean', 'ean'],
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_openstreetmap_address_updater' => [
				'columns' => ['crmid'],
				'values' => [
					[0],
				]
			],
			'u_yf_scalculations_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 40],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 10],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 10],
					['purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 6, 1, 0, NULL, 10],
					['marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 7, 1, 0, NULL, 10],
					['margin', 'LBL_MARGIN', 'Margin', 0, '0', 8, 1, 0, NULL, 10],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_yf_scalculations_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_squoteenquiries_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 50],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 30],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_yf_squoteenquiries_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_squotes_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 10],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					['discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					['marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 9, 1, 0, '{}', 10],
					['margin', 'LBL_MARGIN', 'Margin', 0, '0', 10, 1, 0, '{}', 7],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 6, 2, 0, '{}', 0],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 7],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 7],
					['purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 8, 1, 0, NULL, 7],
					['tax', 'LBL_TAX', 'Tax', 0, '0', 11, 1, 0, NULL, 7],
					['gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 12, 1, 0, NULL, 7],
					['discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 10, 0, 0, NULL, 1],
					['taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 11, 0, 0, NULL, 1],
					['currency', 'LBL_CURRENCY', 'Currency', 0, '', 12, 0, 0, NULL, 1],
					['net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, NULL, 7],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
				]
			],
			'u_yf_squotes_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_srecurringorders_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 30],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 10],
					['discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 4, 1, 0, '{}', 10],
					['marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 5, 1, 0, '{}', 10],
					['margin', 'LBL_MARGIN', 'Margin', 0, '0', 6, 1, 0, '{}', 10],
					['tax', 'LBL_TAX', 'Tax', 0, '0', 7, 1, 0, '{}', 10],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 7, 2, 0, '{}', 0],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_yf_srecurringorders_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_srequirementscards_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 50],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 30],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 3, 2, 0, '{}', 0],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 10],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 10],
				]
			],
			'u_yf_srequirementscards_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
			'u_yf_ssingleorders_invfield' => [
				'columns' => ['columnname', 'label', 'invtype', 'presence', 'defaultvalue', 'sequence', 'block', 'displaytype', 'params', 'colspan'],
				'values' => [
					['name', 'LBL_ITEM_NAME', 'Name', 0, '', 0, 1, 0, '{"modules":["Products","Services"],"limit":" "}', 15],
					['qty', 'LBL_QUANTITY', 'Quantity', 0, '1', 3, 1, 0, '{}', 7],
					['discount', 'LBL_DISCOUNT', 'Discount', 0, '0', 6, 1, 0, '{}', 7],
					['marginp', 'LBL_MARGIN_PERCENT', 'MarginP', 0, '0', 9, 1, 0, '{}', 10],
					['margin', 'LBL_MARGIN', 'Margin', 0, '0', 10, 1, 0, '{}', 7],
					['tax', 'LBL_TAX', 'Tax', 0, '0', 11, 1, 0, '{}', 7],
					['comment1', 'LBL_COMMENT', 'Comment', 0, '', 7, 2, 0, '{}', 0],
					['price', 'LBL_UNIT_PRICE', 'UnitPrice', 0, '0', 4, 1, 0, NULL, 7],
					['total', 'LBL_TOTAL_PRICE', 'TotalPrice', 0, '0', 5, 1, 0, NULL, 7],
					['net', 'LBL_DISCOUNT_PRICE', 'NetPrice', 0, '0', 7, 1, 0, NULL, 7],
					['purchase', 'LBL_PURCHASE', 'Purchase', 0, '0', 8, 1, 0, NULL, 7],
					['gross', 'LBL_GROSS_PRICE', 'GrossPrice', 0, '0', 12, 1, 0, NULL, 7],
					['discountmode', 'LBL_DISCOUNT_MODE', 'DiscountMode', 0, '0', 11, 0, 0, NULL, 1],
					['taxmode', 'LBL_TAX_MODE', 'TaxMode', 0, '0', 12, 0, 0, NULL, 1],
					['currency', 'LBL_CURRENCY', 'Currency', 0, '', 13, 0, 0, NULL, 1],
					['unit', 'LBL_UNIT', 'Value', 0, '', 1, 1, 10, NULL, 7],
					['subunit', 'FL_SUBUNIT', 'Value', 0, '', 2, 1, 10, NULL, 7],
				]
			],
			'u_yf_ssingleorders_invmap' => [
				'columns' => ['module', 'field', 'tofield'],
				'values' => [
					['Products', 'subunit', 'subunit'],
					['Products', 'usageunit', 'unit'],
				]
			],
		];
	}
}
