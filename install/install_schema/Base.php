<?php namespace Importers;

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
			'vtiger_account' => [
				'columns' => [
					'accountid' => $this->integer()->notNull()->defaultValue(0),
					'account_no' => $this->stringType(100)->notNull(),
					'accountname' => $this->stringType(100)->notNull(),
					'parentid' => $this->integer()->defaultValue(0),
					'account_type' => $this->stringType(200),
					'industry' => $this->stringType(200),
					'annualrevenue' => $this->decimal('25,8'),
					'ownership' => $this->stringType(50),
					'siccode' => $this->stringType(50),
					'phone' => $this->stringType(30),
					'otherphone' => $this->stringType(30),
					'email1' => $this->stringType(100),
					'email2' => $this->stringType(100),
					'website' => $this->stringType(100),
					'fax' => $this->stringType(30),
					'employees' => $this->integer(10)->defaultValue(0),
					'emailoptout' => $this->stringType(3)->defaultValue(0),
					'isconvertedfromlead' => $this->stringType(3)->defaultValue(0),
					'vat_id' => $this->stringType(30),
					'registration_number_1' => $this->stringType(30),
					'registration_number_2' => $this->stringType(30),
					'verification' => $this->text(),
					'no_approval' => $this->stringType(3)->defaultValue(0),
					'balance' => $this->decimal('25,8'),
					'payment_balance' => $this->decimal('25,8'),
					'legal_form' => $this->stringType(),
					'sum_time' => $this->decimal('10,2'),
					'inventorybalance' => $this->decimal('25,8')->defaultValue(0),
					'discount' => $this->decimal('5,2')->defaultValue(0),
					'creditlimit' => $this->integer(10),
					'products' => $this->text(),
					'services' => $this->text(),
					'last_invoice_date' => $this->date(),
					'active' => $this->boolean()->defaultValue(0),
					'accounts_status' => $this->stringType(),
				],
				'index' => [
					['account_account_type_idx', 'account_type'],
					['email_idx', ['email1', 'email2']],
					['accountname', 'accountname'],
					['parentid', 'parentid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'accountid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accountaddress' => [
				'columns' => [
					'accountaddressid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5b' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8b' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberb' => $this->stringType(100),
					'localnumberb' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'accountaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accounts_status' => [
				'columns' => [
					'accounts_statusid' => $this->primaryKey(),
					'accounts_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accountscf' => [
				'columns' => [
					'accountid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'accountid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accounttype' => [
				'columns' => [
					'accounttypeid' => $this->primaryKey(),
					'accounttype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['accounttype_accounttype_idx', 'accounttype', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_accounttype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_actionmapping' => [
				'columns' => [
					'actionid' => $this->integer()->notNull(),
					'actionname' => $this->stringType(200)->notNull(),
					'securitycheck' => $this->integer(),
				],
				'index' => [
					['actionname', 'actionname'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['actionid', 'actionname']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity' => [
				'columns' => [
					'activityid' => $this->integer()->notNull()->defaultValue(0),
					'subject' => $this->stringType(100)->notNull(),
					'semodule' => $this->stringType(20),
					'activitytype' => $this->stringType(200)->notNull(),
					'date_start' => $this->date()->notNull(),
					'due_date' => $this->date(),
					'time_start' => $this->time(),
					'time_end' => $this->time(),
					'sendnotification' => $this->stringType(3)->notNull()->defaultValue(0),
					'duration_hours' => $this->stringType(200),
					'duration_minutes' => $this->stringType(200),
					'status' => $this->stringType(200),
					'priority' => $this->stringType(200),
					'location' => $this->stringType(150),
					'notime' => $this->stringType(3)->notNull()->defaultValue(0),
					'visibility' => $this->stringType(50)->notNull()->defaultValue('all'),
					'recurringtype' => $this->stringType(200),
					'deleted' => $this->boolean()->defaultValue(0),
					'smownerid' => $this->smallInteger(19)->unsigned(),
					'allday' => $this->boolean(),
					'dav_status' => $this->boolean()->defaultValue(1),
					'state' => $this->stringType(),
					'link' => $this->integer(),
					'process' => $this->integer(),
					'subprocess' => $this->integer(),
					'followup' => $this->integer(),
				],
				'index' => [
					['activity_activityid_subject_idx', ['activityid', 'subject']],
					['activity_activitytype_date_start_idx', ['activitytype', 'date_start']],
					['activity_date_start_due_date_idx', ['date_start', 'due_date']],
					['activity_date_start_time_start_idx', ['date_start', 'time_start']],
					['activity_status_idx', 'status'],
					['activitytype_2', ['activitytype', 'date_start', 'due_date', 'time_start', 'time_end', 'deleted', 'smownerid']],
					['link', 'link'],
					['process', 'process'],
					['followup', 'followup'],
					['subprocess', 'subprocess'],
					['activitytype_3', ['activitytype', 'status']],
					['smownerid', 'smownerid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'activityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_reminder' => [
				'columns' => [
					'activity_id' => $this->integer()->notNull(),
					'reminder_time' => $this->integer()->notNull(),
					'reminder_sent' => $this->integer(2)->notNull(),
					'recurringid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['activity_id', 'recurringid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_reminder_popup' => [
				'columns' => [
					'reminderid' => $this->primaryKey(),
					'semodule' => $this->stringType(100)->notNull(),
					'recordid' => $this->integer()->notNull(),
					'date_start' => $this->date()->notNull(),
					'time_start' => $this->stringType(100)->notNull(),
					'status' => $this->integer(2)->notNull(),
				],
				'index' => [
					['recordid', 'recordid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_update_dates' => [
				'columns' => [
					'activityid' => $this->integer()->notNull(),
					'parent' => $this->integer()->notNull(),
					'task_id' => $this->integer()->notNull(),
				],
				'index' => [
					['parent', 'parent'],
					['vtiger_activity_update_dates_ibfk_1', 'task_id'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'activityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_view' => [
				'columns' => [
					'activity_viewid' => $this->primaryKey(),
					'activity_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activity_view_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitycf' => [
				'columns' => [
					'activityid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'activityid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activityproductrel' => [
				'columns' => [
					'activityid' => $this->integer()->notNull()->defaultValue(0),
					'productid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['activityproductrel_activityid_idx', 'activityid'],
					['activityproductrel_productid_idx', 'productid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['activityid', 'productid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitystatus' => [
				'columns' => [
					'activitystatusid' => $this->primaryKey(),
					'activitystatus' => $this->stringType(200),
					'presence' => $this->smallInteger(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->smallInteger(2),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitystatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitytype' => [
				'columns' => [
					'activitytypeid' => $this->primaryKey(),
					'activitytype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
					'color' => $this->stringType(25),
				],
				'index' => [
					['activitytype_activitytype_idx', 'activitytype'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_activitytype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_announcementstatus' => [
				'columns' => [
					'announcementstatusid' => $this->primaryKey(),
					'announcementstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_apiaddress' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'name' => $this->stringType()->notNull(),
					'val' => $this->stringType()->notNull(),
					'type' => $this->stringType()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assets' => [
				'columns' => [
					'assetsid' => $this->integer()->notNull(),
					'asset_no' => $this->stringType(30)->notNull(),
					'product' => $this->integer()->notNull(),
					'serialnumber' => $this->stringType(200),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'assetstatus' => $this->stringType(200)->defaultValue('PLL_DRAFT'),
					'assetname' => $this->stringType(100),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'parent_id' => $this->integer(),
					'ordertime' => $this->decimal('10,2'),
					'pscategory' => $this->stringType()->defaultValue(''),
					'ssalesprocessesid' => $this->integer(),
					'assets_renew' => $this->stringType(),
					'renewalinvoice' => $this->integer(),
				],
				'index' => [
					['parent_id', 'parent_id'],
					['product', 'product'],
					['ssalesprocessesid', 'ssalesprocessesid'],
					['renewalinvoice', 'renewalinvoice'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'assetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assets_renew' => [
				'columns' => [
					'assets_renewid' => $this->primaryKey(),
					'assets_renew' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assetscf' => [
				'columns' => [
					'assetsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'assetsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assetstatus' => [
				'columns' => [
					'assetstatusid' => $this->primaryKey(),
					'assetstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_assetstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asterisk' => [
				'columns' => [
					'server' => $this->stringType(30),
					'port' => $this->stringType(30),
					'username' => $this->stringType(50),
					'password' => $this->stringType(50),
					'version' => $this->stringType(50),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asteriskextensions' => [
				'columns' => [
					'userid' => $this->smallInteger(11)->unsigned()->notNull(),
					'asterisk_extension' => $this->stringType(50),
					'use_asterisk' => $this->stringType(3),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asteriskincomingcalls' => [
				'columns' => [
					'from_number' => $this->stringType(50),
					'from_name' => $this->stringType(50),
					'to_number' => $this->stringType(50),
					'callertype' => $this->stringType(30),
					'flag' => $this->integer(),
					'timer' => $this->integer(),
					'refuid' => $this->stringType(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_asteriskincomingevents' => [
				'columns' => [
					'uid' => $this->stringType()->notNull(),
					'channel' => $this->stringType(100),
					'from_number' => $this->bigInteger(),
					'from_name' => $this->stringType(100),
					'to_number' => $this->bigInteger(),
					'callertype' => $this->stringType(100),
					'timer' => $this->integer(),
					'flag' => $this->stringType(3),
					'pbxrecordid' => $this->integer(),
					'relcrmid' => $this->integer(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'uid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_attachments' => [
				'columns' => [
					'attachmentsid' => $this->integer()->notNull(),
					'name' => $this->stringType()->notNull(),
					'description' => $this->text(),
					'type' => $this->stringType(100),
					'path' => $this->text(),
					'subject' => $this->stringType(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'attachmentsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_audit_trial' => [
				'columns' => [
					'auditid' => $this->integer()->notNull(),
					'userid' => $this->integer(),
					'module' => $this->stringType(),
					'action' => $this->stringType(),
					'recordid' => $this->stringType(20),
					'actiondate' => $this->dateTime(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'auditid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_blocks' => [
				'columns' => [
					'blockid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'blocklabel' => $this->stringType(100)->notNull(),
					'sequence' => $this->integer(10),
					'show_title' => $this->integer(2),
					'visible' => $this->integer(2)->notNull()->defaultValue(0),
					'create_view' => $this->integer(2)->notNull()->defaultValue(0),
					'edit_view' => $this->integer(2)->notNull()->defaultValue(0),
					'detail_view' => $this->integer(2)->notNull()->defaultValue(0),
					'display_status' => $this->integer(1)->notNull()->defaultValue(1),
					'iscustom' => $this->integer(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['block_tabid_idx', 'tabid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'blockid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_blocks_hide' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'blockid' => $this->integer()->unsigned(),
					'conditions' => $this->text(),
					'enabled' => $this->boolean()->unsigned(),
					'view' => $this->stringType(100),
				],
				'index' => [
					['blockid', ['blockid', 'enabled']],
					['view', 'view'],
					['blockid_2', ['blockid', 'enabled', 'view']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_blocks_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_config' => [
				'columns' => [
					'type' => $this->stringType(10),
					'name' => $this->stringType(20),
					'label' => $this->stringType(20),
					'value' => $this->stringType(100),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_default_activitytypes' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'module' => $this->stringType(50),
					'fieldname' => $this->stringType(50),
					'defaultcolor' => $this->stringType(50),
					'active' => $this->boolean()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_default_activitytypes_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_user_activitytypes' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'defaultid' => $this->integer(),
					'userid' => $this->integer(),
					'color' => $this->stringType(50),
					'visible' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendar_user_activitytypes_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendarsharedtype' => [
				'columns' => [
					'calendarsharedtypeid' => $this->primaryKey(),
					'calendarsharedtype' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_calendarsharedtype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callduration' => [
				'columns' => [
					'calldurationid' => $this->primaryKey(),
					'callduration' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callduration_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistory' => [
				'columns' => [
					'callhistoryid' => $this->integer()->notNull(),
					'callhistorytype' => $this->stringType(),
					'from_number' => $this->stringType(30),
					'to_number' => $this->stringType(30),
					'location' => $this->stringType(200),
					'phonecallid' => $this->stringType(100),
					'duration' => $this->integer(10),
					'start_time' => $this->dateTime(),
					'end_time' => $this->dateTime(),
					'country' => $this->stringType(100),
					'imei' => $this->stringType(100),
					'ipaddress' => $this->stringType(100),
					'simserial' => $this->stringType(100),
					'subscriberid' => $this->stringType(100),
					'destination' => $this->integer(),
					'source' => $this->integer(),
				],
				'index' => [
					['source', 'source'],
					['destination', 'destination'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'callhistoryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistorycf' => [
				'columns' => [
					'callhistoryid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'callhistoryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistorytype' => [
				'columns' => [
					'callhistorytypeid' => $this->primaryKey(),
					'callhistorytype' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_callhistorytype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaign' => [
				'columns' => [
					'campaign_no' => $this->stringType(100)->notNull(),
					'campaignname' => $this->stringType(),
					'campaigntype' => $this->stringType(200),
					'campaignstatus' => $this->stringType(200),
					'expectedrevenue' => $this->decimal('25,8'),
					'budgetcost' => $this->decimal('25,8'),
					'actualcost' => $this->decimal('25,8'),
					'expectedresponse' => $this->stringType(200),
					'numsent' => $this->decimal('11,0'),
					'product_id' => $this->integer(),
					'sponsor' => $this->stringType(),
					'targetaudience' => $this->stringType(),
					'targetsize' => $this->integer(),
					'expectedresponsecount' => $this->integer(),
					'expectedsalescount' => $this->integer(),
					'expectedroi' => $this->decimal('25,8'),
					'actualresponsecount' => $this->integer(),
					'actualsalescount' => $this->integer(),
					'actualroi' => $this->decimal('25,8'),
					'campaignid' => $this->integer()->notNull(),
					'closingdate' => $this->date(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['campaign_campaignstatus_idx', 'campaignstatus'],
					['campaign_campaignname_idx', 'campaignname'],
					['campaign_campaignid_idx', 'campaignid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'campaignid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaign_records' => [
				'columns' => [
					'campaignid' => $this->integer()->notNull()->defaultValue(0),
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'campaignrelstatusid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['campaigncontrel_contractid_idx', 'crmid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['campaignid', 'crmid', 'campaignrelstatusid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignrelstatus' => [
				'columns' => [
					'campaignrelstatusid' => $this->integer(),
					'campaignrelstatus' => $this->stringType(256),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignrelstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignscf' => [
				'columns' => [
					'campaignid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'campaignid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignstatus' => [
				'columns' => [
					'campaignstatusid' => $this->primaryKey(),
					'campaignstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['campaignstatus_campaignstatus_idx', 'campaignstatus'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaignstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaigntype' => [
				'columns' => [
					'campaigntypeid' => $this->primaryKey(),
					'campaigntype' => $this->stringType(200)->notNull(),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->smallInteger(2),
				],
				'index' => [
					['campaigntype_campaigntype_idx', 'campaigntype'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_campaigntype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactaddress' => [
				'columns' => [
					'contactaddressid' => $this->integer()->notNull()->defaultValue(0),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5b' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8b' => $this->stringType(),
					'buildingnumbera' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'buildingnumberb' => $this->stringType(100),
					'localnumberb' => $this->stringType(100),
					'poboxa' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'contactaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactdetails' => [
				'columns' => [
					'contactid' => $this->integer()->notNull()->defaultValue(0),
					'contact_no' => $this->stringType(100)->notNull(),
					'parentid' => $this->integer(),
					'salutation' => $this->stringType(200),
					'firstname' => $this->stringType(40),
					'lastname' => $this->stringType(80)->notNull(),
					'email' => $this->stringType(100),
					'phone' => $this->stringType(50),
					'mobile' => $this->stringType(50),
					'reportsto' => $this->stringType(30),
					'training' => $this->stringType(50),
					'usertype' => $this->stringType(50),
					'contacttype' => $this->stringType(50),
					'otheremail' => $this->stringType(100),
					'donotcall' => $this->stringType(3),
					'emailoptout' => $this->stringType(3)->defaultValue(0),
					'imagename' => $this->stringType(150),
					'isconvertedfromlead' => $this->stringType(3)->defaultValue(0),
					'verification' => $this->text(),
					'secondary_email' => $this->stringType(50)->defaultValue(''),
					'notifilanguage' => $this->stringType(100)->defaultValue(''),
					'contactstatus' => $this->stringType()->defaultValue(''),
					'dav_status' => $this->boolean()->defaultValue(1),
					'jobtitle' => $this->stringType(100)->defaultValue(''),
					'decision_maker' => $this->boolean()->defaultValue(0),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'active' => $this->boolean()->defaultValue(0),
				],
				'index' => [
					['contactdetails_accountid_idx', 'parentid'],
					['email_idx', 'email'],
					['lastname', 'lastname'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'contactid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactscf' => [
				'columns' => [
					'contactid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'contactid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactstatus' => [
				'columns' => [
					'contactstatusid' => $this->primaryKey(),
					'contactstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contactsubdetails' => [
				'columns' => [
					'contactsubscriptionid' => $this->integer()->notNull()->defaultValue(0),
					'birthday' => $this->date(),
					'laststayintouchrequest' => $this->integer()->defaultValue(0),
					'laststayintouchsavedate' => $this->integer()->defaultValue(0),
					'leadsource' => $this->stringType(200),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'contactsubscriptionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_priority' => [
				'columns' => [
					'contract_priorityid' => $this->primaryKey(),
					'contract_priority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_priority_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_status' => [
				'columns' => [
					'contract_statusid' => $this->primaryKey(),
					'contract_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_type' => [
				'columns' => [
					'contract_typeid' => $this->primaryKey(),
					'contract_type' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_contract_type_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_convertleadmapping' => [
				'columns' => [
					'cfmid' => $this->primaryKey(),
					'leadfid' => $this->integer()->notNull(),
					'accountfid' => $this->integer(),
					'editable' => $this->integer()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_crmentity' => [
				'columns' => [
					'crmid' => $this->integer()->notNull(),
					'smcreatorid' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'smownerid' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'shownerid' => $this->boolean(),
					'modifiedby' => $this->smallInteger(5)->unsigned()->notNull()->defaultValue(0),
					'setype' => $this->stringType(30)->notNull(),
					'description' => $this->text(),
					'attention' => $this->text(),
					'createdtime' => $this->dateTime()->notNull(),
					'modifiedtime' => $this->dateTime()->notNull(),
					'viewedtime' => $this->dateTime(),
					'closedtime' => $this->dateTime(),
					'status' => $this->stringType(50),
					'version' => $this->integer()->unsigned()->notNull()->defaultValue(0),
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'deleted' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'was_read' => $this->boolean()->defaultValue(0),
					'users' => $this->text(),
				],
				'index' => [
					['crmentity_smcreatorid_idx', 'smcreatorid'],
					['crmentity_modifiedby_idx', 'modifiedby'],
					['crmentity_deleted_idx', 'deleted'],
					['crm_ownerid_del_setype_idx', ['smownerid', 'deleted', 'setype']],
					['crmid', ['crmid', 'deleted']],
					['crmid_2', ['crmid', 'setype']],
					['setypedeleted', ['setype', 'deleted']],
					['setype', 'setype'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'crmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_crmentity_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_crmentityrel' => [
				'columns' => [
					'crmid' => $this->integer()->notNull(),
					'module' => $this->stringType(25)->notNull(),
					'relcrmid' => $this->integer()->notNull(),
					'relmodule' => $this->stringType(25)->notNull(),
					'rel_created_user' => $this->integer(),
					'rel_created_time' => $this->dateTime(),
					'rel_comment' => $this->stringType(),
				],
				'index' => [
					['crmid', 'crmid'],
					['relcrmid', 'relcrmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cron_task' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(100),
					'handler_file' => $this->stringType(100),
					'frequency' => $this->integer(),
					'laststart' => $this->integer()->unsigned(),
					'lastend' => $this->integer()->unsigned(),
					'status' => $this->integer(),
					'module' => $this->stringType(100),
					'sequence' => $this->integer(),
					'description' => $this->text(),
				],
				'index' => [
					['name', 'name', true],
					['handler_file', 'handler_file', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currencies' => [
				'columns' => [
					'currencyid' => $this->primaryKey(),
					'currency_name' => $this->stringType(200),
					'currency_code' => $this->stringType(50),
					'currency_symbol' => $this->stringType(11),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currencies_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency' => [
				'columns' => [
					'currencyid' => $this->primaryKey(),
					'currency' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->smallInteger(3)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
					['currency_currency_idx', 'currency', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_decimal_separator' => [
				'columns' => [
					'currency_decimal_separatorid' => $this->primaryKey(),
					'currency_decimal_separator' => $this->stringType(2)->notNull(),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_decimal_separator_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_pattern' => [
				'columns' => [
					'currency_grouping_patternid' => $this->primaryKey(),
					'currency_grouping_pattern' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_pattern_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_separator' => [
				'columns' => [
					'currency_grouping_separatorid' => $this->primaryKey(),
					'currency_grouping_separator' => $this->stringType(2)->notNull(),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_grouping_separator_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_info' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'currency_name' => $this->stringType(100),
					'currency_code' => $this->stringType(100),
					'currency_symbol' => $this->stringType(30),
					'conversion_rate' => $this->decimal('12,5'),
					'currency_status' => $this->stringType(25),
					'defaultid' => $this->stringType(10)->notNull()->defaultValue(0),
					'deleted' => $this->integer(1)->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_info_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_symbol_placement' => [
				'columns' => [
					'currency_symbol_placementid' => $this->primaryKey(),
					'currency_symbol_placement' => $this->stringType(30)->notNull(),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_currency_symbol_placement_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customaction' => [
				'columns' => [
					'cvid' => $this->integer()->notNull(),
					'subject' => $this->stringType(250)->notNull(),
					'module' => $this->stringType(50)->notNull(),
					'content' => $this->text(),
				],
				'index' => [
					['customaction_cvid_idx', 'cvid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customerdetails' => [
				'columns' => [
					'customerid' => $this->integer()->notNull(),
					'portal' => $this->stringType(3),
					'support_start_date' => $this->date(),
					'support_end_date' => $this->date(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'customerid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customerportal_fields' => [
				'columns' => [
					'tabid' => $this->integer()->notNull(),
					'fieldid' => $this->integer(),
					'visible' => $this->integer(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customerportal_prefs' => [
				'columns' => [
					'tabid' => $this->integer()->notNull(),
					'prefkey' => $this->stringType(100)->notNull(),
					'prefvalue' => $this->integer(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['tabid', 'prefkey']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customerportal_tabs' => [
				'columns' => [
					'tabid' => $this->integer()->notNull(),
					'visible' => $this->integer(1)->defaultValue(1),
					'sequence' => $this->integer(1),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview' => [
				'columns' => [
					'cvid' => $this->integer()->notNull(),
					'viewname' => $this->stringType(100)->notNull(),
					'setdefault' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'setmetrics' => $this->boolean()->notNull()->defaultValue(0),
					'entitytype' => $this->stringType(25)->notNull(),
					'status' => $this->boolean()->notNull()->defaultValue(1),
					'userid' => $this->integer()->defaultValue(1),
					'privileges' => $this->smallInteger(2)->defaultValue(1),
					'featured' => $this->boolean()->defaultValue(0),
					'sequence' => $this->integer(),
					'presence' => $this->boolean()->defaultValue(1),
					'description' => $this->text(),
					'sort' => $this->stringType(30)->defaultValue(''),
					'color' => $this->stringType(10)->defaultValue(''),
				],
				'index' => [
					['customview_entitytype_idx', 'entitytype'],
					['setdefault', ['setdefault', 'entitytype']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'cvid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_customview_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvadvfilter' => [
				'columns' => [
					'cvid' => $this->integer()->notNull(),
					'columnindex' => $this->integer()->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'comparator' => $this->stringType(20),
					'value' => $this->stringType(512),
					'groupid' => $this->integer()->defaultValue(1),
					'column_condition' => $this->stringType()->defaultValue('and'),
				],
				'index' => [
					['cvadvfilter_cvid_idx', 'cvid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['cvid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvadvfilter_grouping' => [
				'columns' => [
					'groupid' => $this->integer()->unsigned()->notNull(),
					'cvid' => $this->integer()->unsigned()->notNull(),
					'group_condition' => $this->stringType(),
					'condition_expression' => $this->text(),
				],
				'index' => [
					['cvid', 'cvid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['groupid', 'cvid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvcolumnlist' => [
				'columns' => [
					'cvid' => $this->integer()->notNull(),
					'columnindex' => $this->integer()->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
				],
				'index' => [
					['cvcolumnlist_columnindex_idx', 'columnindex'],
					['cvcolumnlist_cvid_idx', 'cvid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['cvid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_cvstdfilter' => [
				'columns' => [
					'cvid' => $this->integer()->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'stdfilter' => $this->stringType(250)->defaultValue(''),
					'startdate' => $this->date(),
					'enddate' => $this->date(),
				],
				'index' => [
					['cvstdfilter_cvid_idx', 'cvid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'cvid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dataaccess' => [
				'columns' => [
					'dataaccessid' => $this->primaryKey(),
					'module_name' => $this->stringType(25),
					'summary' => $this->stringType()->notNull(),
					'data' => $this->text(),
					'presence' => $this->boolean()->defaultValue(1),
				],
				'index' => [
					['module_name', 'module_name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dataaccess_cnd' => [
				'columns' => [
					'dataaccess_cndid' => $this->primaryKey(),
					'dataaccessid' => $this->integer()->notNull(),
					'fieldname' => $this->stringType()->notNull(),
					'comparator' => $this->stringType()->notNull(),
					'val' => $this->stringType(),
					'required' => $this->smallInteger(2)->notNull(),
					'field_type' => $this->stringType(100)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2grp' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_groupid' => $this->integer(),
					'to_groupid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_grp2grp_share_groupid_idx', 'share_groupid'],
					['datashare_grp2grp_to_groupid_idx', 'to_groupid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2role' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_groupid' => $this->integer(),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['idx_datashare_grp2role_share_groupid', 'share_groupid'],
					['idx_datashare_grp2role_to_roleid', 'to_roleid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2rs' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_groupid' => $this->integer(),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_grp2rs_share_groupid_idx', 'share_groupid'],
					['datashare_grp2rs_to_roleandsubid_idx', 'to_roleandsubid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_grp2us' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_groupid' => $this->integer(),
					'to_userid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_grp2us_share_groupid_idx', 'share_groupid'],
					['datashare_grp2us_to_userid_idx', 'to_userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_module_rel' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'relationtype' => $this->stringType(200),
				],
				'index' => [
					['idx_datashare_module_rel_tabid', 'tabid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_relatedmodule_permission' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'datashare_relatedmodule_id' => $this->integer()->notNull(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_relatedmodule_permission_shareid_permissions_idx', ['shareid', 'permission']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['shareid', 'datashare_relatedmodule_id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_relatedmodules' => [
				'columns' => [
					'datashare_relatedmodule_id' => $this->integer()->notNull(),
					'tabid' => $this->integer(),
					'relatedto_tabid' => $this->integer(),
				],
				'index' => [
					['datashare_relatedmodules_tabid_idx', 'tabid'],
					['datashare_relatedmodules_relatedto_tabid_idx', 'relatedto_tabid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'datashare_relatedmodule_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_relatedmodules_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2group' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleid' => $this->stringType(),
					'to_groupid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['idx_datashare_role2group_share_roleid', 'share_roleid'],
					['idx_datashare_role2group_to_groupid', 'to_groupid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2role' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleid' => $this->stringType(),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_role2role_share_roleid_idx', 'share_roleid'],
					['datashare_role2role_to_roleid_idx', 'to_roleid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2rs' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleid' => $this->stringType(),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_role2s_share_roleid_idx', 'share_roleid'],
					['datashare_role2s_to_roleandsubid_idx', 'to_roleandsubid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_role2us' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleid' => $this->stringType(),
					'to_userid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_role2us_share_roleid_idx', 'share_roleid'],
					['datashare_role2us_to_userid_idx', 'to_userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2grp' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_groupid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_rs2grp_share_roleandsubid_idx', 'share_roleandsubid'],
					['datashare_rs2grp_to_groupid_idx', 'to_groupid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2role' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_rs2role_share_roleandsubid_idx', 'share_roleandsubid'],
					['datashare_rs2role_to_roleid_idx', 'to_roleid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2rs' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_rs2rs_share_roleandsubid_idx', 'share_roleandsubid'],
					['idx_datashare_rs2rs_to_roleandsubid_idx', 'to_roleandsubid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_rs2us' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_roleandsubid' => $this->stringType(),
					'to_userid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_rs2us_share_roleandsubid_idx', 'share_roleandsubid'],
					['datashare_rs2us_to_userid_idx', 'to_userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2grp' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_userid' => $this->integer(),
					'to_groupid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_us2grp_share_userid_idx', 'share_userid'],
					['datashare_us2grp_to_groupid_idx', 'to_groupid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2role' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_userid' => $this->integer(),
					'to_roleid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['idx_datashare_us2role_share_userid', 'share_userid'],
					['idx_datashare_us2role_to_roleid', 'to_roleid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2rs' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_userid' => $this->integer(),
					'to_roleandsubid' => $this->stringType(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_us2rs_share_userid_idx', 'share_userid'],
					['datashare_us2rs_to_roleandsubid_idx', 'to_roleandsubid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_datashare_us2us' => [
				'columns' => [
					'shareid' => $this->integer()->notNull(),
					'share_userid' => $this->integer(),
					'to_userid' => $this->integer(),
					'permission' => $this->integer(),
				],
				'index' => [
					['datashare_us2us_share_userid_idx', 'share_userid'],
					['datashare_us2us_to_userid_idx', 'to_userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'shareid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_date_format' => [
				'columns' => [
					'date_formatid' => $this->primaryKey(),
					'date_format' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->smallInteger(3)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_date_format_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dayoftheweek' => [
				'columns' => [
					'dayoftheweekid' => $this->primaryKey(),
					'dayoftheweek' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_dayoftheweek_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_def_org_field' => [
				'columns' => [
					'tabid' => $this->integer(10),
					'fieldid' => $this->integer()->notNull(),
					'visible' => $this->integer(),
					'readonly' => $this->integer(),
				],
				'index' => [
					['def_org_field_tabid_fieldid_idx', ['tabid', 'fieldid']],
					['def_org_field_tabid_idx', 'tabid'],
					['def_org_field_visible_fieldid_idx', ['visible', 'fieldid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_def_org_share' => [
				'columns' => [
					'ruleid' => $this->primaryKey(),
					'tabid' => $this->integer()->notNull(),
					'permission' => $this->integer(),
					'editstatus' => $this->integer(),
				],
				'index' => [
					['fk_1_vtiger_def_org_share', 'permission'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_def_org_share_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_default_record_view' => [
				'columns' => [
					'default_record_viewid' => $this->primaryKey(),
					'default_record_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_default_record_view_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaultactivitytype' => [
				'columns' => [
					'defaultactivitytypeid' => $this->primaryKey(),
					'defaultactivitytype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaultactivitytype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaultcv' => [
				'columns' => [
					'tabid' => $this->integer()->notNull(),
					'defaultviewname' => $this->stringType(50)->notNull(),
					'query' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaulteventstatus' => [
				'columns' => [
					'defaulteventstatusid' => $this->primaryKey(),
					'defaulteventstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_defaulteventstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_duration_minutes' => [
				'columns' => [
					'minutesid' => $this->primaryKey(),
					'duration_minutes' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_duration_minutes_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_durationhrs' => [
				'columns' => [
					'hrsid' => $this->primaryKey(),
					'hrs' => $this->stringType(50),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_durationmins' => [
				'columns' => [
					'minsid' => $this->primaryKey(),
					'mins' => $this->stringType(50),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_emaildetails' => [
				'columns' => [
					'emailid' => $this->integer()->notNull(),
					'from_email' => $this->stringType(50)->notNull()->defaultValue(''),
					'to_email' => $this->text(),
					'cc_email' => $this->text(),
					'bcc_email' => $this->text(),
					'assigned_user_email' => $this->stringType(50)->notNull()->defaultValue(''),
					'idlists' => $this->text(),
					'email_flag' => $this->stringType(50)->notNull()->defaultValue(''),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'emailid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_education' => [
				'columns' => [
					'employee_educationid' => $this->primaryKey(),
					'employee_education' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_education_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_status' => [
				'columns' => [
					'employee_statusid' => $this->primaryKey(),
					'employee_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_employee_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_end_hour' => [
				'columns' => [
					'end_hourid' => $this->primaryKey(),
					'end_hour' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_end_hour_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_entity_stats' => [
				'columns' => [
					'crmid' => $this->integer()->notNull(),
					'crmactivity' => $this->smallInteger(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'crmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_entityname' => [
				'columns' => [
					'tabid' => $this->integer()->notNull()->defaultValue(0),
					'modulename' => $this->stringType(25)->notNull(),
					'tablename' => $this->stringType(50)->notNull(),
					'fieldname' => $this->stringType(100)->notNull(),
					'entityidfield' => $this->stringType(30)->notNull(),
					'entityidcolumn' => $this->stringType(30)->notNull(),
					'searchcolumn' => $this->stringType(150)->notNull(),
					'turn_off' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'sequence' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['turn_off', 'turn_off'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventhandler_module' => [
				'columns' => [
					'eventhandler_module_id' => $this->primaryKey(),
					'module_name' => $this->stringType(100),
					'handler_class' => $this->stringType(100),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventhandler_module_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventhandlers' => [
				'columns' => [
					'eventhandler_id' => $this->primaryKey()->unsigned(),
					'event_name' => $this->stringType(100)->notNull(),
					'handler_path' => $this->stringType(400)->notNull(),
					'handler_class' => $this->stringType(100)->notNull(),
					'cond' => $this->text(),
					'is_active' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'dependent_on' => $this->stringType()->defaultValue('[]'),
				],
				'index' => [
					['eventhandler_idx', 'eventhandler_id', true],
				],
				// 'primaryKeys' => [
				// ['PRIMARY KEY', ['eventhandler_id', 'event_name', 'handler_class']]
				//  ], 
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventhandlers_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventstatus' => [
				'columns' => [
					'eventstatusid' => $this->primaryKey(),
					'eventstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_eventstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_expectedresponse' => [
				'columns' => [
					'expectedresponseid' => $this->primaryKey(),
					'expectedresponse' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['CampaignExpRes_UK01', 'expectedresponse', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_expectedresponse_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faq' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'faq_no' => $this->stringType(100)->notNull(),
					'product_id' => $this->stringType(100),
					'question' => $this->text(),
					'answer' => $this->text(),
					'category' => $this->stringType(200)->notNull(),
					'status' => $this->stringType(200)->notNull(),
				],
				'index' => [
					['faq_id_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcategories' => [
				'columns' => [
					'faqcategories_id' => $this->primaryKey(),
					'faqcategories' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcategories_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcf' => [
				'columns' => [
					'faqid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'faqid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqcomments' => [
				'columns' => [
					'commentid' => $this->primaryKey(),
					'faqid' => $this->integer(),
					'comments' => $this->text(),
					'createdtime' => $this->dateTime()->notNull(),
				],
				'index' => [
					['faqcomments_faqid_idx', 'faqid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqstatus' => [
				'columns' => [
					'faqstatus_id' => $this->primaryKey(),
					'faqstatus' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_faqstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fcorectinginvoice_formpayment' => [
				'columns' => [
					'fcorectinginvoice_formpaymentid' => $this->primaryKey(),
					'fcorectinginvoice_formpayment' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fcorectinginvoice_status' => [
				'columns' => [
					'fcorectinginvoice_statusid' => $this->primaryKey(),
					'fcorectinginvoice_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_feedback' => [
				'columns' => [
					'userid' => $this->integer(),
					'dontshow' => $this->stringType(19)->defaultValue('false'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field' => [
				'columns' => [
					'tabid' => $this->integer()->notNull(),
					'fieldid' => $this->primaryKey(),
					'columnname' => $this->stringType(30)->notNull(),
					'tablename' => $this->stringType(50)->notNull(),
					'generatedtype' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'uitype' => $this->smallInteger(5)->unsigned()->notNull(),
					'fieldname' => $this->stringType(50)->notNull(),
					'fieldlabel' => $this->stringType(50)->notNull(),
					'readonly' => $this->boolean()->unsigned()->notNull(),
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'defaultvalue' => $this->text(),
					'maximumlength' => $this->smallInteger(5)->unsigned()->notNull(),
					'sequence' => $this->smallInteger(5)->unsigned()->notNull(),
					'block' => $this->integer(),
					'displaytype' => $this->boolean()->unsigned()->notNull(),
					'typeofdata' => $this->stringType(100),
					'quickcreate' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'quickcreatesequence' => $this->integer(),
					'info_type' => $this->stringType(20),
					'masseditable' => $this->integer(10)->notNull()->defaultValue(1),
					'helpinfo' => $this->stringType(30)->defaultValue(''),
					'summaryfield' => $this->integer(10)->notNull()->defaultValue(0),
					'fieldparams' => $this->stringType()->defaultValue(''),
					'header_field' => $this->stringType(15),
					'maxlengthtext' => $this->smallInteger(3)->unsigned()->defaultValue(0),
					'maxwidthcolumn' => $this->smallInteger(3)->unsigned()->defaultValue(0),
				],
				'index' => [
					['field_tabid_idx', 'tabid'],
					['field_fieldname_idx', 'fieldname'],
					['field_block_idx', 'block'],
					['field_displaytype_idx', 'displaytype'],
					['tabid', ['tabid', 'tablename']],
					['quickcreate', 'quickcreate'],
					['presence', 'presence'],
					['tabid_2', ['tabid', 'fieldname']],
					['tabid_3', ['tabid', 'block']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_field_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_fieldmodulerel' => [
				'columns' => [
					'fieldid' => $this->smallInteger(11)->unsigned()->notNull(),
					'module' => $this->stringType(25)->notNull(),
					'relmodule' => $this->stringType(25)->notNull(),
					'status' => $this->stringType(10),
					'sequence' => $this->boolean()->unsigned()->defaultValue(0),
				],
				'index' => [
					['fieldid', 'fieldid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_formpayment' => [
				'columns' => [
					'finvoice_formpaymentid' => $this->primaryKey(),
					'finvoice_formpayment' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_paymentstatus' => [
				'columns' => [
					'finvoice_paymentstatusid' => $this->primaryKey(),
					'finvoice_paymentstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_status' => [
				'columns' => [
					'finvoice_statusid' => $this->primaryKey(),
					'finvoice_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoice_type' => [
				'columns' => [
					'finvoice_typeid' => $this->primaryKey(),
					'finvoice_type' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoiceproforma_formpayment' => [
				'columns' => [
					'finvoiceproforma_formpaymentid' => $this->primaryKey(),
					'finvoiceproforma_formpayment' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_finvoiceproforma_status' => [
				'columns' => [
					'finvoiceproforma_statusid' => $this->primaryKey(),
					'finvoiceproforma_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_glacct' => [
				'columns' => [
					'glacctid' => $this->primaryKey(),
					'glacct' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['glacct_glacct_idx', 'glacct', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_glacct_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2grouprel' => [
				'columns' => [
					'groupid' => $this->integer()->notNull(),
					'containsgroupid' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['containsgroupid', 'containsgroupid'],
					['groupid', 'groupid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['groupid', 'containsgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2modules' => [
				'columns' => [
					'groupid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
				],
				'index' => [
					['groupid', 'groupid'],
					['tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2role' => [
				'columns' => [
					'groupid' => $this->integer()->notNull(),
					'roleid' => $this->stringType()->notNull(),
				],
				'index' => [
					['fk_2_vtiger_group2role', 'roleid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['groupid', 'roleid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_group2rs' => [
				'columns' => [
					'groupid' => $this->integer()->unsigned()->notNull(),
					'roleandsubid' => $this->stringType()->notNull(),
				],
				'index' => [
					['fk_2_vtiger_group2rs', 'roleandsubid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['groupid', 'roleandsubid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_groups' => [
				'columns' => [
					'groupid' => $this->integer()->notNull(),
					'groupname' => $this->stringType(100),
					'description' => $this->text(),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
					'modules' => $this->stringType(),
				],
				'index' => [
					['groups_groupname_idx', 'groupname', true],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'groupid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlement' => [
				'columns' => [
					'holidaysentitlementid' => $this->integer()->notNull()->defaultValue(0),
					'holidaysentitlement_no' => $this->stringType(),
					'holidaysentitlement_year' => $this->stringType(50),
					'days' => $this->integer(3)->defaultValue(0),
					'ossemployeesid' => $this->integer(),
				],
				'index' => [
					['ossemployeesid', 'ossemployeesid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'holidaysentitlementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlement_year' => [
				'columns' => [
					'holidaysentitlement_yearid' => $this->primaryKey(),
					'holidaysentitlement_year' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlement_year_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_holidaysentitlementcf' => [
				'columns' => [
					'holidaysentitlementid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'holidaysentitlementid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_home_layout' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'layout' => $this->integer()->notNull()->defaultValue(4),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homedashbd' => [
				'columns' => [
					'stuffid' => $this->integer()->notNull()->defaultValue(0),
					'dashbdname' => $this->stringType(100),
					'dashbdtype' => $this->stringType(100),
				],
				'index' => [
					['stuff_stuffid_idx', 'stuffid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'stuffid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homedefault' => [
				'columns' => [
					'stuffid' => $this->integer()->notNull()->defaultValue(0),
					'hometype' => $this->stringType(30)->notNull(),
					'maxentries' => $this->integer(),
					'setype' => $this->stringType(30),
				],
				'index' => [
					['stuff_stuffid_idx', 'stuffid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'stuffid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homemodule' => [
				'columns' => [
					'stuffid' => $this->integer()->notNull(),
					'modulename' => $this->stringType(100),
					'maxentries' => $this->integer()->notNull(),
					'customviewid' => $this->integer()->notNull(),
					'setype' => $this->stringType(30)->notNull(),
				],
				'index' => [
					['stuff_stuffid_idx', 'stuffid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'stuffid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homemoduleflds' => [
				'columns' => [
					'stuffid' => $this->integer(),
					'fieldname' => $this->stringType(100),
				],
				'index' => [
					['stuff_stuffid_idx', 'stuffid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homereportchart' => [
				'columns' => [
					'stuffid' => $this->integer()->notNull(),
					'reportid' => $this->integer(),
					'reportcharttype' => $this->stringType(100),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'stuffid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homerss' => [
				'columns' => [
					'stuffid' => $this->integer()->notNull()->defaultValue(0),
					'url' => $this->stringType(100),
					'maxentries' => $this->integer()->notNull(),
				],
				'index' => [
					['stuff_stuffid_idx', 'stuffid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'stuffid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homestuff' => [
				'columns' => [
					'stuffid' => $this->integer()->notNull()->defaultValue(0),
					'stuffsequence' => $this->integer()->notNull()->defaultValue(0),
					'stufftype' => $this->stringType(100),
					'userid' => $this->integer()->notNull(),
					'visible' => $this->integer(10)->notNull()->defaultValue(0),
					'stufftitle' => $this->stringType(100),
				],
				'index' => [
					['stuff_stuffid_idx', 'stuffid'],
					['fk_1_vtiger_homestuff', 'userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'stuffid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_homestuff_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_hour_format' => [
				'columns' => [
					'hour_formatid' => $this->primaryKey(),
					'hour_format' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_hour_format_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideas' => [
				'columns' => [
					'ideasid' => $this->integer()->notNull()->defaultValue(0),
					'ideas_no' => $this->stringType(),
					'subject' => $this->stringType(),
					'ideasstatus' => $this->stringType()->defaultValue(''),
					'extent_description' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ideasid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideascf' => [
				'columns' => [
					'ideasid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ideasid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideasstatus' => [
				'columns' => [
					'ideasstatusid' => $this->primaryKey(),
					'ideasstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ideasstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igdn_status' => [
				'columns' => [
					'igdn_statusid' => $this->primaryKey(),
					'igdn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igdnc_status' => [
				'columns' => [
					'igdnc_statusid' => $this->primaryKey(),
					'igdnc_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igin_status' => [
				'columns' => [
					'igin_statusid' => $this->primaryKey(),
					'igin_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igrn_status' => [
				'columns' => [
					'igrn_statusid' => $this->primaryKey(),
					'igrn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_igrnc_status' => [
				'columns' => [
					'igrnc_statusid' => $this->primaryKey(),
					'igrnc_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_iidn_status' => [
				'columns' => [
					'iidn_statusid' => $this->primaryKey(),
					'iidn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_locks' => [
				'columns' => [
					'vtiger_import_lock_id' => $this->integer()->notNull(),
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'importid' => $this->integer()->notNull(),
					'locked_since' => $this->dateTime(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'vtiger_import_lock_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_maps' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(36)->notNull(),
					'module' => $this->stringType(36)->notNull(),
					'content' => $this->binary(),
					'has_header' => $this->integer(1)->notNull()->defaultValue(1),
					'deleted' => $this->integer(1)->notNull()->defaultValue(0),
					'date_entered' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
					'date_modified' => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
					'assigned_user_id' => $this->stringType(36),
					'is_published' => $this->stringType(3)->notNull()->defaultValue('no'),
				],
				'index' => [
					['import_maps_assigned_user_id_module_name_deleted_idx', ['assigned_user_id', 'module', 'name', 'deleted']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_import_queue' => [
				'columns' => [
					'importid' => $this->integer()->notNull(),
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'field_mapping' => $this->text(),
					'default_values' => $this->text(),
					'merge_type' => $this->integer(),
					'merge_fields' => $this->text(),
					'type' => $this->boolean(),
					'temp_status' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'importid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_industry' => [
				'columns' => [
					'industryid' => $this->primaryKey(),
					'industry' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['industry_industry_idx', 'industry', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_industry_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventory_tandc' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'type' => $this->stringType(30)->notNull(),
					'tandc' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventory_tandc_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventoryproductrel' => [
				'columns' => [
					'id' => $this->integer(),
					'productid' => $this->integer(),
					'sequence_no' => $this->integer(4),
					'quantity' => $this->decimal('25,3'),
					'listprice' => $this->decimal('27,8'),
					'discount_percent' => $this->decimal('7,3'),
					'discount_amount' => $this->decimal('27,8'),
					'comment' => $this->stringType(500),
					'description' => $this->text(),
					'incrementondel' => $this->integer()->notNull()->defaultValue(0),
					'lineitem_id' => $this->primaryKey(),
					'tax' => $this->stringType(10),
					'tax1' => $this->decimal('7,3'),
					'tax2' => $this->decimal('7,3'),
					'tax3' => $this->decimal('7,3'),
					'purchase' => $this->decimal('10,2'),
					'margin' => $this->decimal('10,2'),
					'marginp' => $this->decimal('10,2'),
				],
				'index' => [
					['inventoryproductrel_id_idx', 'id'],
					['inventoryproductrel_productid_idx', 'productid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventoryproductrel_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventorysubproductrel' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'sequence_no' => $this->integer(10)->notNull(),
					'productid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventorytaxinfo' => [
				'columns' => [
					'taxid' => $this->integer(3)->notNull(),
					'taxname' => $this->stringType(50),
					'taxlabel' => $this->stringType(50),
					'percentage' => $this->decimal('7,3'),
					'deleted' => $this->integer(1),
				],
				'index' => [
					['inventorytaxinfo_taxname_idx', 'taxname'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'taxid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_inventorytaxinfo_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ipreorder_status' => [
				'columns' => [
					'ipreorder_statusid' => $this->primaryKey(),
					'ipreorder_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istdn_status' => [
				'columns' => [
					'istdn_statusid' => $this->primaryKey(),
					'istdn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istn_status' => [
				'columns' => [
					'istn_statusid' => $this->primaryKey(),
					'istn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istn_type' => [
				'columns' => [
					'istn_typeid' => $this->primaryKey(),
					'istn_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_istrn_status' => [
				'columns' => [
					'istrn_statusid' => $this->primaryKey(),
					'istrn_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_knowledgebase_status' => [
				'columns' => [
					'knowledgebase_statusid' => $this->primaryKey(),
					'knowledgebase_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_knowledgebase_view' => [
				'columns' => [
					'knowledgebase_viewid' => $this->primaryKey(),
					'knowledgebase_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_language' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(50)->notNull(),
					'prefix' => $this->stringType(10)->notNull(),
					'label' => $this->stringType(30)->notNull(),
					'lastupdated' => $this->dateTime(),
					'sequence' => $this->integer(),
					'isdefault' => $this->boolean()->notNull()->defaultValue(0),
					'active' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
					['prefix', 'prefix'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_language_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_layout' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'name' => $this->stringType(50),
					'label' => $this->stringType(30),
					'lastupdated' => $this->dateTime(),
					'isdefault' => $this->boolean(),
					'active' => $this->boolean(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lead_view' => [
				'columns' => [
					'lead_viewid' => $this->primaryKey(),
					'lead_view' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->smallInteger(2)->notNull()->defaultValue(0),
					'presence' => $this->boolean()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lead_view_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadaddress' => [
				'columns' => [
					'leadaddressid' => $this->integer()->notNull()->defaultValue(0),
					'phone' => $this->stringType(50),
					'mobile' => $this->stringType(50),
					'fax' => $this->stringType(50),
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
					['PRIMARY KEY', 'leadaddressid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leaddetails' => [
				'columns' => [
					'leadid' => $this->integer()->notNull(),
					'lead_no' => $this->stringType(100)->notNull(),
					'email' => $this->stringType(100),
					'interest' => $this->stringType(50),
					'firstname' => $this->stringType(40),
					'salutation' => $this->stringType(200),
					'lastname' => $this->stringType(80)->notNull(),
					'company' => $this->stringType(100)->notNull(),
					'annualrevenue' => $this->decimal('25,8'),
					'industry' => $this->stringType(200),
					'campaign' => $this->stringType(30),
					'leadstatus' => $this->stringType(50),
					'leadsource' => $this->stringType(200),
					'converted' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'licencekeystatus' => $this->stringType(50),
					'space' => $this->stringType(250),
					'comments' => $this->text(),
					'priority' => $this->stringType(50),
					'demorequest' => $this->stringType(50),
					'partnercontact' => $this->stringType(50),
					'productversion' => $this->stringType(20),
					'product' => $this->stringType(50),
					'maildate' => $this->date(),
					'nextstepdate' => $this->date(),
					'fundingsituation' => $this->stringType(50),
					'purpose' => $this->stringType(50),
					'evaluationstatus' => $this->stringType(50),
					'transferdate' => $this->date(),
					'revenuetype' => $this->stringType(50),
					'noofemployees' => $this->integer(),
					'secondaryemail' => $this->stringType(100),
					'assignleadchk' => $this->integer(1)->defaultValue(0),
					'noapprovalcalls' => $this->stringType(3),
					'noapprovalemails' => $this->stringType(3),
					'vat_id' => $this->stringType(30),
					'registration_number_1' => $this->stringType(30),
					'registration_number_2' => $this->stringType(30),
					'verification' => $this->text(),
					'subindustry' => $this->stringType()->defaultValue(''),
					'atenttion' => $this->text(),
					'leads_relation' => $this->stringType(),
					'legal_form' => $this->stringType(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'active' => $this->boolean()->defaultValue(0),
				],
				'index' => [
					['leaddetails_converted_leadstatus_idx', ['converted', 'leadstatus']],
					['email_idx', 'email'],
					['lastname', 'lastname'],
					['converted', 'converted'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'leadid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leads_relation' => [
				'columns' => [
					'leads_relationid' => $this->primaryKey(),
					'leads_relation' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leads_relation_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadscf' => [
				'columns' => [
					'leadid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'leadid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadsource' => [
				'columns' => [
					'leadsourceid' => $this->primaryKey(),
					'leadsource' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadsource_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadstage' => [
				'columns' => [
					'leadstageid' => $this->primaryKey(),
					'stage' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
					['leadstage_stage_idx', 'stage', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadstatus' => [
				'columns' => [
					'leadstatusid' => $this->primaryKey(),
					'leadstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_leadsubdetails' => [
				'columns' => [
					'leadsubscriptionid' => $this->integer()->notNull()->defaultValue(0),
					'website' => $this->stringType(),
					'callornot' => $this->integer(1)->defaultValue(0),
					'readornot' => $this->integer(1)->defaultValue(0),
					'empct' => $this->integer(10)->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'leadsubscriptionid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_legal_form' => [
				'columns' => [
					'legal_formid' => $this->primaryKey(),
					'legal_form' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_legal_form_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersin' => [
				'columns' => [
					'lettersinid' => $this->integer()->notNull()->defaultValue(0),
					'number' => $this->stringType(),
					'title' => $this->stringType(),
					'relatedid' => $this->integer(),
					'person_receiving' => $this->integer(),
					'parentid' => $this->integer(),
					'date_adoption' => $this->date(),
					'lin_type_ship' => $this->stringType()->defaultValue(''),
					'lin_type_doc' => $this->text(),
					'lin_status' => $this->stringType()->defaultValue(''),
					'deadline_reply' => $this->date(),
					'cocument_no' => $this->stringType(100)->defaultValue(''),
					'no_internal' => $this->stringType(100)->defaultValue(''),
					'lin_dimensions' => $this->stringType()->defaultValue(''),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'lettersinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersincf' => [
				'columns' => [
					'lettersinid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'lettersinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersout' => [
				'columns' => [
					'lettersoutid' => $this->integer()->notNull()->defaultValue(0),
					'number' => $this->stringType(),
					'title' => $this->stringType(),
					'relatedid' => $this->integer(),
					'person_receiving' => $this->integer(),
					'parentid' => $this->integer(),
					'date_adoption' => $this->date(),
					'lout_type_ship' => $this->stringType()->defaultValue(''),
					'lout_type_doc' => $this->text(),
					'lout_status' => $this->stringType()->defaultValue(''),
					'deadline_reply' => $this->date(),
					'cocument_no' => $this->stringType(100)->defaultValue(''),
					'no_internal' => $this->stringType(100)->defaultValue(''),
					'lout_dimensions' => $this->stringType()->defaultValue(''),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'lettersoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lettersoutcf' => [
				'columns' => [
					'lettersoutid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'lettersoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_dimensions' => [
				'columns' => [
					'lin_dimensionsid' => $this->primaryKey(),
					'lin_dimensions' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_dimensions_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_status' => [
				'columns' => [
					'lin_statusid' => $this->primaryKey(),
					'lin_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_doc' => [
				'columns' => [
					'lin_type_docid' => $this->primaryKey(),
					'lin_type_doc' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_doc_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_ship' => [
				'columns' => [
					'lin_type_shipid' => $this->primaryKey(),
					'lin_type_ship' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lin_type_ship_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_links' => [
				'columns' => [
					'linkid' => $this->integer()->notNull(),
					'tabid' => $this->integer(),
					'linktype' => $this->stringType(50),
					'linklabel' => $this->stringType(50),
					'linkurl' => $this->stringType(),
					'linkicon' => $this->stringType(100),
					'sequence' => $this->integer(),
					'handler_path' => $this->stringType(128),
					'handler_class' => $this->stringType(50),
					'handler' => $this->stringType(50),
					'params' => $this->stringType(),
				],
				'index' => [
					['link_tabidtype_idx', ['tabid', 'linktype']],
					['linklabel', 'linklabel'],
					['linkid', ['linkid', 'tabid', 'linktype', 'linklabel']],
					['linktype', 'linktype'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'linkid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_links_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_loginhistory' => [
				'columns' => [
					'login_id' => $this->primaryKey(),
					'user_name' => $this->stringType(32),
					'user_ip' => $this->stringType(50)->notNull(),
					'logout_time' => $this->timestamp(),
					'login_time' => $this->timestamp()->notNull(),
					'status' => $this->stringType(25),
					'browser' => $this->stringType(25),
				],
				'index' => [
					['user_name', 'user_name'],
					['user_ip', ['user_ip', 'login_time', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_dimensions' => [
				'columns' => [
					'lout_dimensionsid' => $this->primaryKey(),
					'lout_dimensions' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_dimensions_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_status' => [
				'columns' => [
					'lout_statusid' => $this->primaryKey(),
					'lout_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_doc' => [
				'columns' => [
					'lout_type_docid' => $this->primaryKey(),
					'lout_type_doc' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_doc_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_ship' => [
				'columns' => [
					'lout_type_shipid' => $this->primaryKey(),
					'lout_type_ship' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_lout_type_ship_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_mail_accounts' => [
				'columns' => [
					'account_id' => $this->integer()->notNull(),
					'user_id' => $this->integer()->notNull(),
					'display_name' => $this->stringType(50),
					'mail_id' => $this->stringType(50),
					'account_name' => $this->stringType(50),
					'mail_protocol' => $this->stringType(20),
					'mail_username' => $this->stringType(50)->notNull(),
					'mail_password' => $this->stringType(250)->notNull(),
					'mail_servername' => $this->stringType(50),
					'box_refresh' => $this->integer(10),
					'mails_per_page' => $this->integer(10),
					'ssltype' => $this->stringType(50),
					'sslmeth' => $this->stringType(50),
					'int_mailer' => $this->integer(1)->defaultValue(0),
					'status' => $this->stringType(10),
					'set_default' => $this->integer(2),
					'sent_folder' => $this->stringType(50),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'account_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_manufacturer' => [
				'columns' => [
					'manufacturerid' => $this->primaryKey(),
					'manufacturer' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['manufacturer_manufacturer_idx', 'manufacturer', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_manufacturer_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modcomments' => [
				'columns' => [
					'modcommentsid' => $this->integer()->notNull(),
					'commentcontent' => $this->text(),
					'related_to' => $this->integer(),
					'parent_comments' => $this->integer(),
					'customer' => $this->stringType(100),
					'userid' => $this->integer(),
					'reasontoedit' => $this->stringType(100),
				],
				'index' => [
					['relatedto_idx', 'related_to'],
					['modcommentsid', 'modcommentsid'],
					['parent_comments', 'parent_comments'],
					['userid', 'userid'],
					['related_to', ['related_to', 'parent_comments']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'modcommentsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modcommentscf' => [
				'columns' => [
					'modcommentsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'modcommentsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modentity_num' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'tabid' => $this->smallInteger(11)->unsigned()->notNull(),
					'prefix' => $this->stringType(50)->notNull()->defaultValue(''),
					'postfix' => $this->stringType(50)->notNull()->defaultValue(''),
					'start_id' => $this->integer()->unsigned()->notNull(),
					'cur_id' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['semodule', 'cur_id'],
					['prefix', ['prefix', 'postfix', 'cur_id']],
					['tabid', 'tabid'],
					['tabid_2', ['tabid', 'cur_id']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_basic' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'crmid' => $this->integer(),
					'module' => $this->stringType(50),
					'whodid' => $this->integer(),
					'changedon' => $this->dateTime(),
					'status' => $this->integer(1)->defaultValue(0),
					'last_reviewed_users' => $this->stringType()->defaultValue(''),
				],
				'index' => [
					['crmidx', 'crmid'],
					['idx', 'id'],
					['id', ['id', 'module', 'changedon']],
					['crmid', ['crmid', 'changedon']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_detail' => [
				'columns' => [
					'id' => $this->integer(),
					'fieldname' => $this->stringType(100),
					'prevalue' => $this->text(),
					'postvalue' => $this->text(),
				],
				'index' => [
					['idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_relations' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'targetmodule' => $this->stringType(100)->notNull(),
					'targetid' => $this->integer()->notNull(),
					'changedon' => $this->dateTime(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_modtracker_tabs' => [
				'columns' => [
					'tabid' => $this->smallInteger(11)->unsigned()->notNull(),
					'visible' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['tabid', ['tabid', 'visible']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'blockid' => $this->integer()->notNull(),
					'linkid' => $this->integer(),
					'filterid' => $this->stringType(100),
					'title' => $this->stringType(100),
					'data' => $this->text(),
					'size' => $this->stringType(50),
					'limit' => $this->smallInteger(2),
					'isdefault' => $this->boolean()->notNull()->defaultValue(0),
					'owners' => $this->stringType(100),
					'cache' => $this->boolean()->defaultValue(0),
					'date' => $this->stringType(20),
				],
				'index' => [
					['vtiger_module_dashboard_ibfk_1', 'blockid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard_blocks' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'authorized' => $this->stringType(10)->notNull(),
					'tabid' => $this->smallInteger(11)->unsigned()->notNull(),
				],
				'index' => [
					['authorized', ['authorized', 'tabid']],
					['tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_module_dashboard_widgets' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'linkid' => $this->integer()->notNull(),
					'userid' => $this->integer(),
					'templateid' => $this->integer()->notNull(),
					'filterid' => $this->stringType(100),
					'title' => $this->stringType(100),
					'data' => $this->text(),
					'size' => $this->stringType(50),
					'limit' => $this->smallInteger(2),
					'position' => $this->stringType(50),
					'isdefault' => $this->boolean()->defaultValue(0),
					'active' => $this->boolean()->defaultValue(0),
					'owners' => $this->stringType(100),
					'module' => $this->integer(10)->defaultValue(0),
					'cache' => $this->boolean()->defaultValue(0),
					'date' => $this->stringType(20),
				],
				'index' => [
					['vtiger_module_dashboard_widgets_ibfk_1', 'templateid'],
					['userid', ['userid', 'active', 'module']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_no_of_currency_decimals' => [
				'columns' => [
					'no_of_currency_decimalsid' => $this->primaryKey(),
					'no_of_currency_decimals' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_no_of_currency_decimals_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notebook_contents' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'notebookid' => $this->integer()->notNull(),
					'contents' => $this->text(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notes' => [
				'columns' => [
					'notesid' => $this->integer()->notNull()->defaultValue(0),
					'note_no' => $this->stringType(100)->notNull(),
					'title' => $this->stringType(200)->notNull(),
					'filename' => $this->stringType(200),
					'notecontent' => $this->text(),
					'folderid' => $this->stringType()->notNull(),
					'filetype' => $this->stringType(100),
					'filelocationtype' => $this->stringType(5),
					'filedownloadcount' => $this->integer(),
					'filestatus' => $this->integer(),
					'filesize' => $this->integer()->notNull()->defaultValue(0),
					'fileversion' => $this->stringType(50),
					'ossdc_status' => $this->stringType(),
				],
				'index' => [
					['notes_title_idx', 'title'],
					['notes_notesid_idx', 'notesid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'notesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notescf' => [
				'columns' => [
					'notesid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'notesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notification_status' => [
				'columns' => [
					'notification_statusid' => $this->primaryKey(),
					'notification_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_notification_type' => [
				'columns' => [
					'notification_typeid' => $this->primaryKey(),
					'notification_type' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_opportunitystage' => [
				'columns' => [
					'potstageid' => $this->primaryKey(),
					'stage' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'probability' => $this->decimal('3,2')->defaultValue(0),
				],
				'index' => [
					['opportunitystage_stage_idx', 'stage', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_oproductstatus' => [
				'columns' => [
					'oproductstatusid' => $this->primaryKey(),
					'oproductstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_oproductstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_org_share_action2tab' => [
				'columns' => [
					'share_action_id' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
				],
				'index' => [
					['fk_2_vtiger_org_share_action2tab', 'tabid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['share_action_id', 'tabid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_org_share_action_mapping' => [
				'columns' => [
					'share_action_id' => $this->integer()->notNull(),
					'share_action_name' => $this->stringType(200),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'share_action_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_organizationdetails' => [
				'columns' => [
					'organization_id' => $this->smallInteger(11)->notNull(),
					'organizationname' => $this->stringType(60),
					'address' => $this->stringType(150),
					'city' => $this->stringType(100),
					'state' => $this->stringType(100),
					'country' => $this->stringType(100),
					'code' => $this->stringType(30),
					'phone' => $this->stringType(30),
					'fax' => $this->stringType(30),
					'website' => $this->stringType(100),
					'panellogoname' => $this->stringType(50),
					'height_panellogo' => $this->smallInteger(3),
					'panellogo' => $this->text(),
					'logoname' => $this->stringType(50),
					'logo' => $this->text(),
					'vatid' => $this->stringType(30),
					'id1' => $this->stringType(30),
					'id2' => $this->stringType(30),
					'email' => $this->stringType(50),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'organization_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_organizationdetails_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_oss_project_templates' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'fld_name' => $this->stringType()->notNull(),
					'fld_val' => $this->stringType()->notNull(),
					'id_tpl' => $this->integer()->notNull(),
					'parent' => $this->integer()->notNull(),
					'module' => $this->stringType()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossdc_status' => [
				'columns' => [
					'ossdc_statusid' => $this->primaryKey(),
					'ossdc_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossdc_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossdocumentcontrol' => [
				'columns' => [
					'ossdocumentcontrolid' => $this->primaryKey(),
					'module_name' => $this->stringType(),
					'summary' => $this->stringType()->notNull(),
					'doc_folder' => $this->integer(),
					'doc_name' => $this->stringType()->notNull(),
					'doc_request' => $this->boolean()->notNull(),
					'doc_order' => $this->integer()->notNull(),
				],
				'index' => [
					['ossdocumentcontrolid', 'ossdocumentcontrolid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossdocumentcontrol_cnd' => [
				'columns' => [
					'ossdocumentcontrol_cndid' => $this->primaryKey(),
					'ossdocumentcontrolid' => $this->integer()->notNull(),
					'fieldname' => $this->stringType()->notNull(),
					'comparator' => $this->stringType()->notNull(),
					'val' => $this->stringType(),
					'required' => $this->smallInteger(3)->notNull(),
					'field_type' => $this->stringType(100)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossemployees' => [
				'columns' => [
					'ossemployeesid' => $this->integer()->notNull()->defaultValue(0),
					'ossemployees_no' => $this->stringType(),
					'parentid' => $this->integer()->defaultValue(0),
					'employee_status' => $this->stringType(200),
					'name' => $this->stringType(200),
					'last_name' => $this->stringType(200),
					'pesel' => $this->stringType(20),
					'id_card' => $this->stringType(200),
					'employee_education' => $this->stringType(200),
					'birth_date' => $this->date(),
					'business_phone' => $this->stringType(20),
					'private_phone' => $this->stringType(25),
					'business_mail' => $this->stringType(200),
					'private_mail' => $this->stringType(200),
					'street' => $this->stringType(200),
					'code' => $this->stringType(200),
					'city' => $this->stringType(200),
					'state' => $this->stringType(200),
					'country' => $this->stringType(200),
					'ship_street' => $this->stringType(200),
					'ship_code' => $this->stringType(200),
					'ship_city' => $this->stringType(200),
					'ship_state' => $this->stringType(200),
					'ship_country' => $this->stringType(200),
					'dav_status' => $this->boolean()->defaultValue(1),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'secondary_phone' => $this->stringType(25),
					'position' => $this->stringType(),
					'rbh' => $this->decimal('25,8'),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossemployeesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossemployeescf' => [
				'columns' => [
					'ossemployeesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossemployeesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osservicesstatus' => [
				'columns' => [
					'osservicesstatusid' => $this->primaryKey(),
					'osservicesstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osservicesstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmails_logs' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'start_time' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
					'end_time' => $this->timestamp(),
					'action' => $this->stringType(100),
					'status' => $this->smallInteger(3),
					'user' => $this->stringType(100),
					'count' => $this->integer(10),
					'stop_user' => $this->stringType(100),
					'info' => $this->stringType(100),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailscanner_config' => [
				'columns' => [
					'conf_type' => $this->stringType(100)->notNull(),
					'parameter' => $this->stringType(100),
					'value' => $this->text(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailscanner_folders_uid' => [
				'columns' => [
					'user_id' => $this->integer(10),
					'type' => $this->stringType(50),
					'folder' => $this->stringType(100),
					'uid' => $this->integer()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailscanner_log_cron' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'created_time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
					'laststart' => $this->integer()->unsigned(),
					'status' => $this->stringType(50),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailtemplates' => [
				'columns' => [
					'ossmailtemplatesid' => $this->integer(),
					'name' => $this->stringType(),
					'sysname' => $this->stringType(50)->defaultValue(''),
					'oss_module_list' => $this->stringType()->defaultValue(''),
					'subject' => $this->stringType()->defaultValue(''),
					'content' => $this->text(),
					'ossmailtemplates_type' => $this->stringType(),
				],
				'index' => [
					['ossmailtemplatesid', 'ossmailtemplatesid'],
					['oss_module_list', 'oss_module_list'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailtemplates_type' => [
				'columns' => [
					'ossmailtemplates_typeid' => $this->primaryKey(),
					'ossmailtemplates_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailtemplates_type_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailtemplatescf' => [
				'columns' => [
					'ossmailtemplatesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossmailtemplatesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview' => [
				'columns' => [
					'ossmailviewid' => $this->integer()->notNull(),
					'ossmailview_no' => $this->stringType(50),
					'from_email' => $this->text(),
					'to_email' => $this->text(),
					'subject' => $this->text(),
					'content' => $this->text(),
					'cc_email' => $this->text(),
					'bcc_email' => $this->text(),
					'id' => $this->integer(),
					'mbox' => $this->stringType(100),
					'uid' => $this->stringType(150),
					'reply_to_email' => $this->text(),
					'ossmailview_sendtype' => $this->stringType(30),
					'attachments_exist' => $this->stringType(3)->defaultValue(0),
					'rc_user' => $this->stringType(3),
					'type' => $this->boolean(),
					'from_id' => $this->stringType(50)->notNull(),
					'to_id' => $this->stringType(100)->notNull(),
					'orginal_mail' => $this->text(),
					'verify' => $this->stringType(5)->defaultValue(0),
					'rel_mod' => $this->stringType(128),
					'date' => $this->dateTime(),
				],
				'index' => [
					['id', 'id'],
					['message_id', 'uid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossmailviewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_files' => [
				'columns' => [
					'ossmailviewid' => $this->integer()->notNull(),
					'documentsid' => $this->integer()->notNull(),
					'attachmentsid' => $this->integer()->notNull(),
				],
				'index' => [
					['fk_1_vtiger_ossmailview_files', 'ossmailviewid'],
					['documentsid', 'documentsid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_relation' => [
				'columns' => [
					'ossmailviewid' => $this->integer()->notNull(),
					'crmid' => $this->integer()->notNull(),
					'date' => $this->dateTime(),
					'deleted' => $this->boolean()->defaultValue(0),
				],
				'index' => [
					['ossmailviewid_2', ['ossmailviewid', 'crmid'], true],
					['ossmailviewid', 'ossmailviewid'],
					['crmid', ['crmid', 'deleted']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['ossmailviewid', 'crmid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_sendtype' => [
				'columns' => [
					'ossmailview_sendtypeid' => $this->primaryKey(),
					'ossmailview_sendtype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailview_sendtype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossmailviewcf' => [
				'columns' => [
					'ossmailviewid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossmailviewid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossoutsourcedservices' => [
				'columns' => [
					'ossoutsourcedservicesid' => $this->integer()->notNull()->defaultValue(0),
					'ossoutsourcedservices_no' => $this->stringType(),
					'productname' => $this->stringType(100)->defaultValue(''),
					'osservicesstatus' => $this->stringType(50),
					'pscategory' => $this->stringType(),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'wherebought' => $this->stringType(100)->defaultValue(''),
					'parent_id' => $this->integer(),
					'ssalesprocessesid' => $this->integer(),
				],
				'index' => [
					['parent_id', 'parent_id'],
					['ssalesprocessesid', 'ssalesprocessesid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossoutsourcedservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ossoutsourcedservicescf' => [
				'columns' => [
					'ossoutsourcedservicesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ossoutsourcedservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osspasswords' => [
				'columns' => [
					'osspasswordsid' => $this->integer()->notNull(),
					'osspassword_no' => $this->stringType(100)->notNull(),
					'passwordname' => $this->stringType(100)->notNull(),
					'username' => $this->stringType(100)->notNull(),
					'password' => $this->binary(200)->notNull(),
					'link_adres' => $this->stringType(),
					'linkto' => $this->stringType(100),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'osspasswordsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osspasswordscf' => [
				'columns' => [
					'osspasswordsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'osspasswordsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osssoldservices' => [
				'columns' => [
					'osssoldservicesid' => $this->integer()->notNull()->defaultValue(0),
					'osssoldservices_no' => $this->stringType(),
					'productname' => $this->stringType()->defaultValue(''),
					'ssservicesstatus' => $this->stringType(),
					'pscategory' => $this->stringType()->defaultValue(''),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'invoice' => $this->stringType()->defaultValue(''),
					'parent_id' => $this->integer(),
					'serviceid' => $this->integer(),
					'ordertime' => $this->decimal('10,2'),
					'ssalesprocessesid' => $this->integer(),
					'osssoldservices_renew' => $this->stringType(),
					'renewalinvoice' => $this->integer(),
				],
				'index' => [
					['parent_id', 'parent_id'],
					['serviceid', 'serviceid'],
					['ssalesprocessesid', 'ssalesprocessesid'],
					['renewalinvoice', 'renewalinvoice'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'osssoldservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osssoldservices_renew' => [
				'columns' => [
					'osssoldservices_renewid' => $this->primaryKey(),
					'osssoldservices_renew' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osssoldservicescf' => [
				'columns' => [
					'osssoldservicesid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'osssoldservicesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol' => [
				'columns' => [
					'osstimecontrolid' => $this->integer()->notNull()->defaultValue(0),
					'name' => $this->stringType(128),
					'osstimecontrol_no' => $this->stringType(),
					'osstimecontrol_status' => $this->stringType(128),
					'date_start' => $this->date()->notNull(),
					'time_start' => $this->stringType(50),
					'due_date' => $this->date(),
					'time_end' => $this->stringType(50),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'deleted' => $this->integer(1)->defaultValue(0),
					'timecontrol_type' => $this->stringType(),
					'process' => $this->integer(),
					'link' => $this->integer(),
					'subprocess' => $this->integer(),
				],
				'index' => [
					['on_update_cascade', 'deleted'],
					['osstimecontrol_status_9', ['osstimecontrol_status', 'deleted']],
					['osstimecontrol_status_6', 'osstimecontrol_status'],
					['subprocess', 'subprocess'],
					['link', 'link'],
					['process', 'process'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'osstimecontrolid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol_status' => [
				'columns' => [
					'osstimecontrol_statusid' => $this->primaryKey(),
					'osstimecontrol_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrol_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_osstimecontrolcf' => [
				'columns' => [
					'osstimecontrolid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'osstimecontrolid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_othereventduration' => [
				'columns' => [
					'othereventdurationid' => $this->primaryKey(),
					'othereventduration' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_othereventduration_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_outsourcedproducts' => [
				'columns' => [
					'outsourcedproductsid' => $this->integer()->notNull()->defaultValue(0),
					'asset_no' => $this->stringType(32),
					'productname' => $this->stringType(),
					'datesold' => $this->date(),
					'dateinservice' => $this->date(),
					'oproductstatus' => $this->stringType(),
					'pscategory' => $this->stringType()->defaultValue(''),
					'wherebought' => $this->stringType()->defaultValue(''),
					'prodcount' => $this->stringType()->defaultValue(''),
					'parent_id' => $this->integer(),
					'ssalesprocessesid' => $this->integer(),
				],
				'index' => [
					['parent_id', 'parent_id'],
					['ssalesprocessesid', 'ssalesprocessesid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'outsourcedproductsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_outsourcedproductscf' => [
				'columns' => [
					'outsourcedproductsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'outsourcedproductsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_password' => [
				'columns' => [
					'type' => $this->stringType(20)->notNull(),
					'val' => $this->stringType(100)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_passwords_config' => [
				'columns' => [
					'pass_length_min' => $this->integer(3)->notNull(),
					'pass_length_max' => $this->integer(3)->notNull(),
					'pass_allow_chars' => $this->stringType(200)->notNull(),
					'register_changes' => $this->boolean()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsin' => [
				'columns' => [
					'paymentsinid' => $this->integer()->notNull()->defaultValue(0),
					'paymentsvalue' => $this->decimal('25,3'),
					'paymentsno' => $this->stringType(32),
					'paymentsname' => $this->stringType(128),
					'paymentstitle' => $this->text(),
					'paymentscurrency' => $this->stringType(32),
					'bank_account' => $this->stringType(128),
					'paymentsin_status' => $this->stringType(128),
					'relatedid' => $this->integer(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'paymentsinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsin_status' => [
				'columns' => [
					'paymentsin_statusid' => $this->primaryKey(),
					'paymentsin_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsin_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsincf' => [
				'columns' => [
					'paymentsinid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'paymentsinid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsout' => [
				'columns' => [
					'paymentsoutid' => $this->integer()->notNull()->defaultValue(0),
					'paymentsvalue' => $this->decimal('25,3'),
					'paymentsno' => $this->stringType(32),
					'paymentsname' => $this->stringType(128),
					'paymentstitle' => $this->stringType(128),
					'paymentscurrency' => $this->stringType(32),
					'bank_account' => $this->stringType(128),
					'paymentsout_status' => $this->stringType(128),
					'relatedid' => $this->integer(),
					'parentid' => $this->integer(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'paymentsoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsout_status' => [
				'columns' => [
					'paymentsout_statusid' => $this->primaryKey(),
					'paymentsout_status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsout_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_paymentsoutcf' => [
				'columns' => [
					'paymentsoutid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'paymentsoutid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager' => [
				'columns' => [
					'pbxmanagerid' => $this->primaryKey(),
					'direction' => $this->stringType(10),
					'callstatus' => $this->stringType(20),
					'starttime' => $this->dateTime(),
					'endtime' => $this->dateTime(),
					'totalduration' => $this->integer(),
					'billduration' => $this->integer(),
					'recordingurl' => $this->stringType(200),
					'sourceuuid' => $this->stringType(100),
					'gateway' => $this->stringType(20),
					'customer' => $this->stringType(100),
					'user' => $this->stringType(100),
					'customernumber' => $this->stringType(100),
					'customertype' => $this->stringType(100),
				],
				'index' => [
					['index_sourceuuid', 'sourceuuid'],
					['index_pbxmanager_id', 'pbxmanagerid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager_gateway' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'gateway' => $this->stringType(20),
					'parameters' => $this->text(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanager_phonelookup' => [
				'columns' => [
					'crmid' => $this->integer(),
					'setype' => $this->stringType(30),
					'fnumber' => $this->stringType(100),
					'rnumber' => $this->stringType(100),
					'fieldname' => $this->stringType(50),
				],
				'index' => [
					['unique_key', ['crmid', 'setype', 'fieldname'], true],
					['index_phone_number', ['fnumber', 'rnumber']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pbxmanagercf' => [
				'columns' => [
					'pbxmanagerid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'pbxmanagerid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist' => [
				'columns' => [
					'picklistid' => $this->primaryKey(),
					'name' => $this->stringType(200)->notNull(),
				],
				'index' => [
					['picklist_name_idx', 'name', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist_dependency' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'sourcefield' => $this->stringType(),
					'targetfield' => $this->stringType(),
					'sourcevalue' => $this->stringType(100),
					'targetvalues' => $this->text(),
					'criteria' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist_dependency_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklist_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_picklistvalues_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_portal' => [
				'columns' => [
					'portalid' => $this->integer()->notNull(),
					'portalname' => $this->stringType(200)->notNull(),
					'portalurl' => $this->stringType()->notNull(),
					'sequence' => $this->integer(3)->notNull(),
					'setdefault' => $this->integer(3)->notNull()->defaultValue(0),
					'createdtime' => $this->dateTime(),
				],
				'index' => [
					['portal_portalname_idx', 'portalname'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'portalid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_portalinfo' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'user_name' => $this->stringType(50),
					'user_password' => $this->stringType(200),
					'type' => $this->stringType(5),
					'last_login_time' => $this->dateTime(),
					'login_time' => $this->dateTime(),
					'logout_time' => $this->dateTime(),
					'isactive' => $this->integer(1),
					'crypt_type' => $this->stringType(20),
					'password_sent' => $this->stringType()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pricebook' => [
				'columns' => [
					'pricebookid' => $this->integer()->notNull()->defaultValue(0),
					'pricebook_no' => $this->stringType(100)->notNull(),
					'bookname' => $this->stringType(100),
					'active' => $this->integer(1),
					'currency_id' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'pricebookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pricebookcf' => [
				'columns' => [
					'pricebookid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'pricebookid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_pricebookproductrel' => [
				'columns' => [
					'pricebookid' => $this->integer()->notNull(),
					'productid' => $this->integer()->notNull(),
					'listprice' => $this->decimal('27,8'),
					'usedcurrency' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
					['pricebookproductrel_pricebookid_idx', 'pricebookid'],
					['pricebookproductrel_productid_idx', 'productid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['pricebookid', 'productid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_priority' => [
				'columns' => [
					'priorityid' => $this->primaryKey(),
					'priority' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
					['priority_priority_idx', 'priority', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_productcf' => [
				'columns' => [
					'productid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'productid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_productcurrencyrel' => [
				'columns' => [
					'productid' => $this->integer()->notNull(),
					'currencyid' => $this->integer()->notNull(),
					'converted_price' => $this->decimal('28,8'),
					'actual_price' => $this->decimal('28,8'),
				],
				'index' => [
					['productid', 'productid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_products' => [
				'columns' => [
					'productid' => $this->integer()->notNull(),
					'product_no' => $this->stringType(100)->notNull(),
					'productname' => $this->stringType(100),
					'productcode' => $this->stringType(40),
					'pscategory' => $this->stringType(200),
					'manufacturer' => $this->stringType(200),
					'qty_per_unit' => $this->decimal('11,2')->defaultValue(0),
					'unit_price' => $this->decimal('25,8'),
					'weight' => $this->decimal('11,3'),
					'pack_size' => $this->integer(),
					'sales_start_date' => $this->date(),
					'sales_end_date' => $this->date(),
					'start_date' => $this->date(),
					'expiry_date' => $this->date(),
					'cost_factor' => $this->integer(),
					'commissionrate' => $this->decimal('7,3'),
					'commissionmethod' => $this->stringType(50),
					'discontinued' => $this->boolean()->notNull()->defaultValue(0),
					'usageunit' => $this->stringType(200),
					'reorderlevel' => $this->integer(),
					'website' => $this->stringType(100),
					'taxclass' => $this->stringType(200),
					'mfr_part_no' => $this->stringType(200),
					'vendor_part_no' => $this->stringType(200),
					'serialno' => $this->stringType(200),
					'qtyinstock' => $this->decimal('25,3'),
					'productsheet' => $this->stringType(200),
					'qtyindemand' => $this->integer(),
					'glacct' => $this->stringType(200),
					'vendor_id' => $this->integer(),
					'imagename' => $this->text(),
					'currency_id' => $this->integer()->notNull()->defaultValue(1),
					'taxes' => $this->stringType(50),
					'ean' => $this->stringType(30),
					'subunit' => $this->stringType()->defaultValue(''),
					'renewable' => $this->boolean()->defaultValue(0),
					'pos' => $this->stringType()->defaultValue(''),
					'category_multipicklist' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'productid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_producttaxrel' => [
				'columns' => [
					'productid' => $this->integer()->notNull(),
					'taxid' => $this->integer(3)->notNull(),
					'taxpercentage' => $this->decimal('7,3'),
				],
				'index' => [
					['producttaxrel_productid_idx', 'productid'],
					['producttaxrel_taxid_idx', 'taxid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile' => [
				'columns' => [
					'profileid' => $this->primaryKey(),
					'profilename' => $this->stringType(50)->notNull(),
					'description' => $this->text(),
					'directly_related_to_role' => $this->integer(1)->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2field' => [
				'columns' => [
					'profileid' => $this->integer()->notNull(),
					'tabid' => $this->integer(10),
					'fieldid' => $this->integer()->notNull(),
					'visible' => $this->integer(),
					'readonly' => $this->integer(),
				],
				'index' => [
					['profile2field_profileid_tabid_fieldname_idx', ['profileid', 'tabid']],
					['profile2field_tabid_profileid_idx', ['tabid', 'profileid']],
					['profile2field_visible_profileid_idx', ['visible', 'profileid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['profileid', 'fieldid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2globalpermissions' => [
				'columns' => [
					'profileid' => $this->integer()->notNull(),
					'globalactionid' => $this->integer()->notNull(),
					'globalactionpermission' => $this->integer(),
				],
				'index' => [
					['idx_profile2globalpermissions', ['profileid', 'globalactionid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['profileid', 'globalactionid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2standardpermissions' => [
				'columns' => [
					'profileid' => $this->smallInteger(11)->unsigned()->notNull(),
					'tabid' => $this->smallInteger(10)->unsigned()->notNull(),
					'operation' => $this->smallInteger(10)->unsigned()->notNull(),
					'permissions' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['profile2standardpermissions_profileid_tabid_operation_idx', ['profileid', 'tabid', 'operation']],
					['profileid', ['profileid', 'tabid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['profileid', 'tabid', 'operation']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2tab' => [
				'columns' => [
					'profileid' => $this->integer(),
					'tabid' => $this->integer(10),
					'permissions' => $this->integer(10)->notNull()->defaultValue(0),
				],
				'index' => [
					['profile2tab_profileid_tabid_idx', ['profileid', 'tabid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile2utility' => [
				'columns' => [
					'profileid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'activityid' => $this->integer()->notNull(),
					'permission' => $this->integer(1),
				],
				'index' => [
					['profile2utility_tabid_activityid_idx', ['tabid', 'activityid']],
					['profile2utility_profileid', 'profileid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['profileid', 'tabid', 'activityid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_profile_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_progress' => [
				'columns' => [
					'progressid' => $this->primaryKey(),
					'progress' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_progress_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_project' => [
				'columns' => [
					'projectid' => $this->integer()->notNull(),
					'projectname' => $this->stringType(),
					'project_no' => $this->stringType(100),
					'startdate' => $this->date(),
					'targetenddate' => $this->date(),
					'actualenddate' => $this->date(),
					'targetbudget' => $this->stringType(),
					'projecturl' => $this->stringType(),
					'projectstatus' => $this->stringType(100),
					'projectpriority' => $this->stringType(100),
					'projecttype' => $this->stringType(100),
					'progress' => $this->stringType(100),
					'linktoaccountscontacts' => $this->integer(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'servicecontractsid' => $this->integer(),
					'ssalesprocessesid' => $this->integer(),
				],
				'index' => [
					['servicecontractsid', 'servicecontractsid'],
					['linktoaccountscontacts', 'linktoaccountscontacts'],
					['projectname', 'projectname'],
					['ssalesprocessesid', 'ssalesprocessesid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'projectid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectcf' => [
				'columns' => [
					'projectid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'projectid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone' => [
				'columns' => [
					'projectmilestoneid' => $this->integer()->notNull(),
					'projectmilestonename' => $this->stringType(),
					'projectmilestone_no' => $this->stringType(100),
					'projectmilestonedate' => $this->stringType(),
					'projectid' => $this->integer(),
					'projectmilestonetype' => $this->stringType(100),
					'projectmilestone_priority' => $this->stringType(),
					'projectmilestone_progress' => $this->stringType(10),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['projectid', 'projectid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'projectmilestoneid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone_priority' => [
				'columns' => [
					'projectmilestone_priorityid' => $this->primaryKey(),
					'projectmilestone_priority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestone_priority_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestonecf' => [
				'columns' => [
					'projectmilestoneid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'projectmilestoneid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestonetype' => [
				'columns' => [
					'projectmilestonetypeid' => $this->primaryKey(),
					'projectmilestonetype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectmilestonetype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectpriority' => [
				'columns' => [
					'projectpriorityid' => $this->primaryKey(),
					'projectpriority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectpriority_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectstatus' => [
				'columns' => [
					'projectstatusid' => $this->primaryKey(),
					'projectstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projectstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttask' => [
				'columns' => [
					'projecttaskid' => $this->integer()->notNull(),
					'projecttaskname' => $this->stringType(),
					'projecttask_no' => $this->stringType(100),
					'projecttasktype' => $this->stringType(100),
					'projecttaskpriority' => $this->stringType(100),
					'projecttaskprogress' => $this->stringType(100),
					'startdate' => $this->date(),
					'enddate' => $this->date(),
					'projectid' => $this->integer(),
					'projecttasknumber' => $this->integer(10),
					'projecttaskstatus' => $this->stringType(100),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'parentid' => $this->integer(),
					'projectmilestoneid' => $this->integer(),
					'targetenddate' => $this->date(),
					'estimated_work_time' => $this->decimal('8,2'),
				],
				'index' => [
					['parentid', 'parentid'],
					['projectmilestoneid', 'projectmilestoneid'],
					['projectid', 'projectid'],
					['projecttaskname', 'projecttaskname'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'projecttaskid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskcf' => [
				'columns' => [
					'projecttaskid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'projecttaskid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskpriority' => [
				'columns' => [
					'projecttaskpriorityid' => $this->primaryKey(),
					'projecttaskpriority' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskpriority_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskprogress' => [
				'columns' => [
					'projecttaskprogressid' => $this->primaryKey(),
					'projecttaskprogress' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskprogress_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskstatus' => [
				'columns' => [
					'projecttaskstatusid' => $this->primaryKey(),
					'projecttaskstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttaskstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttasktype' => [
				'columns' => [
					'projecttasktypeid' => $this->primaryKey(),
					'projecttasktype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttasktype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttype' => [
				'columns' => [
					'projecttypeid' => $this->primaryKey(),
					'projecttype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_projecttype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_publicholiday' => [
				'columns' => [
					'publicholidayid' => $this->primaryKey()->unsigned(),
					'holidaydate' => $this->date()->notNull(),
					'holidayname' => $this->stringType()->notNull(),
					'holidaytype' => $this->stringType(25),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_realization_process' => [
				'columns' => [
					'module_id' => $this->integer()->notNull(),
					'status_indicate_closing' => $this->stringType(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurring_frequency' => [
				'columns' => [
					'recurring_frequency_id' => $this->integer(),
					'recurring_frequency' => $this->stringType(200),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurring_frequency_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurringevents' => [
				'columns' => [
					'recurringid' => $this->primaryKey(),
					'activityid' => $this->integer()->notNull(),
					'recurringdate' => $this->date(),
					'recurringtype' => $this->stringType(30),
					'recurringfreq' => $this->integer(),
					'recurringinfo' => $this->stringType(50),
					'recurringenddate' => $this->date(),
				],
				'index' => [
					['fk_1_vtiger_recurringevents', 'activityid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurringtype' => [
				'columns' => [
					'recurringeventid' => $this->primaryKey(),
					'recurringtype' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
					['recurringtype_status_idx', 'recurringtype', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_recurringtype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rel_mod' => [
				'columns' => [
					'rel_modid' => $this->primaryKey(),
					'rel_mod' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rel_mod_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists' => [
				'columns' => [
					'relation_id' => $this->smallInteger(19)->unsigned()->notNull(),
					'tabid' => $this->smallInteger(10)->unsigned()->notNull(),
					'related_tabid' => $this->smallInteger(10)->unsigned()->notNull(),
					'name' => $this->stringType(50),
					'sequence' => $this->smallInteger(5)->unsigned()->notNull(),
					'label' => $this->stringType(50)->notNull(),
					'presence' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'actions' => $this->stringType(50)->notNull()->defaultValue(''),
					'favorites' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'creator_detail' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'relation_comment' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['tabid', 'tabid'],
					['related_tabid', 'related_tabid'],
					['tabid_2', ['tabid', 'related_tabid']],
					['tabid_3', ['tabid', 'related_tabid', 'label']],
					['tabid_4', ['tabid', 'related_tabid', 'presence']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'relation_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_fields' => [
				'columns' => [
					'relation_id' => $this->integer(),
					'fieldid' => $this->integer(),
					'fieldname' => $this->stringType(30),
					'sequence' => $this->integer(10),
				],
				'index' => [
					['relation_id', 'relation_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_rb' => [
				'columns' => [
					'entityid' => $this->integer(),
					'action' => $this->stringType(50),
					'rel_table' => $this->stringType(200),
					'rel_column' => $this->stringType(200),
					'ref_column' => $this->stringType(200),
					'related_crm_ids' => $this->text(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relatedlists_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relcriteria' => [
				'columns' => [
					'queryid' => $this->integer()->notNull(),
					'columnindex' => $this->integer()->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'comparator' => $this->stringType(20),
					'value' => $this->stringType(512),
					'groupid' => $this->integer()->defaultValue(1),
					'column_condition' => $this->stringType(256)->defaultValue('and'),
				],
				'index' => [
					['relcriteria_queryid_idx', 'queryid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['queryid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_relcriteria_grouping' => [
				'columns' => [
					'groupid' => $this->integer()->notNull(),
					'queryid' => $this->integer()->notNull(),
					'group_condition' => $this->stringType(256),
					'condition_expression' => $this->text(),
				],
				'index' => [
					['queryid', 'queryid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['groupid', 'queryid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reminder_interval' => [
				'columns' => [
					'reminder_intervalid' => $this->primaryKey(),
					'reminder_interval' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull(),
					'presence' => $this->integer(1)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reminder_interval_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_report' => [
				'columns' => [
					'reportid' => $this->integer()->notNull(),
					'folderid' => $this->integer()->notNull(),
					'reportname' => $this->stringType(100)->defaultValue(''),
					'description' => $this->stringType(250)->defaultValue(''),
					'reporttype' => $this->stringType(50)->defaultValue(''),
					'queryid' => $this->integer()->notNull()->defaultValue(0),
					'state' => $this->stringType(50)->defaultValue('SAVED'),
					'customizable' => $this->integer(1)->defaultValue(1),
					'category' => $this->integer()->defaultValue(1),
					'owner' => $this->integer()->defaultValue(1),
					'sharingtype' => $this->stringType(200)->defaultValue('Private'),
				],
				'index' => [
					['report_queryid_idx', 'queryid'],
					['report_folderid_idx', 'folderid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'reportid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportdatefilter' => [
				'columns' => [
					'datefilterid' => $this->integer()->notNull(),
					'datecolumnname' => $this->stringType(250)->defaultValue(''),
					'datefilter' => $this->stringType(250)->defaultValue(''),
					'startdate' => $this->date(),
					'enddate' => $this->date(),
				],
				'index' => [
					['reportdatefilter_datefilterid_idx', 'datefilterid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'datefilterid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportfilters' => [
				'columns' => [
					'filterid' => $this->integer()->notNull(),
					'name' => $this->stringType(200)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportfolder' => [
				'columns' => [
					'folderid' => $this->primaryKey(),
					'foldername' => $this->stringType(100)->notNull()->defaultValue(''),
					'description' => $this->stringType(250)->defaultValue(''),
					'state' => $this->stringType(50)->defaultValue('SAVED'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportgroupbycolumn' => [
				'columns' => [
					'reportid' => $this->integer(),
					'sortid' => $this->integer(),
					'sortcolname' => $this->stringType(250),
					'dategroupbycriteria' => $this->stringType(250),
				],
				'index' => [
					['fk_1_vtiger_reportgroupbycolumn', 'reportid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportmodules' => [
				'columns' => [
					'reportmodulesid' => $this->integer()->notNull(),
					'primarymodule' => $this->stringType(50)->notNull()->defaultValue(''),
					'secondarymodules' => $this->stringType(250)->defaultValue(''),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'reportmodulesid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportsharing' => [
				'columns' => [
					'reportid' => $this->integer()->notNull(),
					'shareid' => $this->integer()->notNull(),
					'setype' => $this->stringType(200)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportsortcol' => [
				'columns' => [
					'sortcolid' => $this->integer()->notNull(),
					'reportid' => $this->integer()->notNull(),
					'columnname' => $this->stringType(250)->defaultValue(''),
					'sortorder' => $this->stringType(250)->defaultValue('Asc'),
				],
				'index' => [
					['fk_1_vtiger_reportsortcol', 'reportid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['sortcolid', 'reportid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reportsummary' => [
				'columns' => [
					'reportsummaryid' => $this->integer()->notNull(),
					'summarytype' => $this->integer()->notNull(),
					'columnname' => $this->stringType(250)->notNull()->defaultValue(''),
				],
				'index' => [
					['reportsummary_reportsummaryid_idx', 'reportsummaryid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['reportsummaryid', 'summarytype', 'columnname']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reporttype' => [
				'columns' => [
					'reportid' => $this->integer(10)->notNull(),
					'data' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'reportid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations' => [
				'columns' => [
					'reservationsid' => $this->integer()->notNull()->defaultValue(0),
					'title' => $this->stringType(128),
					'reservations_no' => $this->stringType(),
					'reservations_status' => $this->stringType(128),
					'date_start' => $this->date()->notNull(),
					'time_start' => $this->stringType(50),
					'due_date' => $this->date(),
					'time_end' => $this->stringType(50),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'relatedida' => $this->integer()->defaultValue(0),
					'relatedidb' => $this->integer()->defaultValue(0),
					'deleted' => $this->integer(1)->defaultValue(0),
					'type' => $this->stringType(128),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'reservationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations_status' => [
				'columns' => [
					'reservations_statusid' => $this->primaryKey(),
					'reservations_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservations_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_reservationscf' => [
				'columns' => [
					'reservationsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'reservationsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role' => [
				'columns' => [
					'roleid' => $this->stringType()->notNull(),
					'rolename' => $this->stringType(200),
					'parentrole' => $this->stringType(),
					'depth' => $this->smallInteger(11)->unsigned()->notNull()->defaultValue(0),
					'allowassignedrecordsto' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'changeowner' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'searchunpriv' => $this->text(),
					'clendarallorecords' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'listrelatedrecord' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'previewrelatedrecord' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'editrelatedrecord' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'permissionsrelatedfield' => $this->stringType(10)->notNull()->defaultValue(0),
					'globalsearchadv' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'assignedmultiowner' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['parentrole', 'parentrole'],
					['parentrole_2', ['parentrole', 'depth']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'roleid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role2picklist' => [
				'columns' => [
					'roleid' => $this->stringType()->notNull(),
					'picklistvalueid' => $this->integer()->notNull(),
					'picklistid' => $this->integer()->notNull(),
					'sortid' => $this->integer(),
				],
				'index' => [
					['role2picklist_roleid_picklistid_idx', ['roleid', 'picklistid', 'picklistvalueid']],
					['fk_2_vtiger_role2picklist', 'picklistid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['roleid', 'picklistvalueid', 'picklistid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role2profile' => [
				'columns' => [
					'roleid' => $this->stringType()->notNull(),
					'profileid' => $this->integer()->notNull(),
				],
				'index' => [
					['role2profile_roleid_profileid_idx', ['roleid', 'profileid']],
					['roleid', 'roleid'],
					['profileid', 'profileid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['roleid', 'profileid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_role_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rowheight' => [
				'columns' => [
					'rowheightid' => $this->primaryKey(),
					'rowheight' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rowheight_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_rss' => [
				'columns' => [
					'rssid' => $this->integer()->notNull(),
					'rssurl' => $this->stringType(200)->notNull()->defaultValue(''),
					'rsstitle' => $this->stringType(200),
					'rsstype' => $this->integer(10)->defaultValue(0),
					'starred' => $this->integer(1)->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'rssid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salesmanactivityrel' => [
				'columns' => [
					'smid' => $this->integer()->notNull()->defaultValue(0),
					'activityid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['salesmanactivityrel_activityid_idx', 'activityid'],
					['salesmanactivityrel_smid_idx', 'smid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['smid', 'activityid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salesmanattachmentsrel' => [
				'columns' => [
					'smid' => $this->integer()->notNull()->defaultValue(0),
					'attachmentsid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['salesmanattachmentsrel_smid_idx', 'smid'],
					['salesmanattachmentsrel_attachmentsid_idx', 'attachmentsid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['smid', 'attachmentsid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salesmanticketrel' => [
				'columns' => [
					'smid' => $this->integer()->notNull()->defaultValue(0),
					'id' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['salesmanticketrel_smid_idx', 'smid'],
					['salesmanticketrel_id_idx', 'id'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['smid', 'id']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salutationtype' => [
				'columns' => [
					'salutationid' => $this->primaryKey(),
					'salutationtype' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_salutationtype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_scalculations_status' => [
				'columns' => [
					'scalculations_statusid' => $this->primaryKey(),
					'scalculations_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_scheduled_reports' => [
				'columns' => [
					'reportid' => $this->integer()->notNull(),
					'recipients' => $this->text(),
					'schedule' => $this->text(),
					'format' => $this->stringType(10),
					'next_trigger_time' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'reportid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_schedulereports' => [
				'columns' => [
					'reportid' => $this->integer(10),
					'scheduleid' => $this->integer(3),
					'recipients' => $this->text(),
					'schdate' => $this->stringType(20),
					'schtime' => $this->time(),
					'schdayoftheweek' => $this->stringType(100),
					'schdayofthemonth' => $this->stringType(100),
					'schannualdates' => $this->stringType(500),
					'specificemails' => $this->stringType(500),
					'next_trigger_time' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
					'filetype' => $this->stringType(20),
				],
				'index' => [
					['reportid', 'reportid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_seattachmentsrel' => [
				'columns' => [
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'attachmentsid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['seattachmentsrel_attachmentsid_idx', 'attachmentsid'],
					['seattachmentsrel_crmid_idx', 'crmid'],
					['seattachmentsrel_attachmentsid_crmid_idx', ['attachmentsid', 'crmid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['crmid', 'attachmentsid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_selectcolumn' => [
				'columns' => [
					'queryid' => $this->integer()->notNull(),
					'columnindex' => $this->integer()->notNull()->defaultValue(0),
					'columnname' => $this->stringType(250)->defaultValue(''),
				],
				'index' => [
					['selectcolumn_queryid_idx', 'queryid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['queryid', 'columnindex']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_selectquery' => [
				'columns' => [
					'queryid' => $this->integer()->notNull(),
					'startindex' => $this->integer()->defaultValue(0),
					'numofobjects' => $this->integer()->defaultValue(0),
				],
				'index' => [
					['selectquery_queryid_idx', 'queryid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'queryid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_selectquery_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_senotesrel' => [
				'columns' => [
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'notesid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['senotesrel_notesid_idx', 'notesid'],
					['senotesrel_crmid_idx', 'crmid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['crmid', 'notesid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_seproductsrel' => [
				'columns' => [
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'productid' => $this->integer()->notNull()->defaultValue(0),
					'setype' => $this->stringType(30)->notNull(),
					'rel_created_user' => $this->integer()->notNull(),
					'rel_created_time' => $this->dateTime()->notNull(),
					'rel_comment' => $this->stringType(),
				],
				'index' => [
					['seproductsrel_productid_idx', 'productid'],
					['seproductrel_crmid_idx', 'crmid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['crmid', 'productid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_service' => [
				'columns' => [
					'serviceid' => $this->integer()->notNull(),
					'service_no' => $this->stringType(100)->notNull(),
					'servicename' => $this->stringType(50)->notNull(),
					'pscategory' => $this->stringType(200),
					'qty_per_unit' => $this->decimal('11,2')->defaultValue(0),
					'unit_price' => $this->decimal('25,8'),
					'sales_start_date' => $this->date(),
					'sales_end_date' => $this->date(),
					'start_date' => $this->date(),
					'expiry_date' => $this->date(),
					'discontinued' => $this->boolean()->notNull()->defaultValue(0),
					'service_usageunit' => $this->stringType(200),
					'website' => $this->stringType(100),
					'taxclass' => $this->stringType(200),
					'currency_id' => $this->integer()->notNull()->defaultValue(1),
					'commissionrate' => $this->decimal('7,3'),
					'renewable' => $this->boolean()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'serviceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_service_usageunit' => [
				'columns' => [
					'service_usageunitid' => $this->primaryKey(),
					'service_usageunit' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_service_usageunit_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_servicecf' => [
				'columns' => [
					'serviceid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'serviceid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_servicecontracts' => [
				'columns' => [
					'servicecontractsid' => $this->integer()->notNull(),
					'start_date' => $this->date(),
					'end_date' => $this->date(),
					'sc_related_to' => $this->integer(),
					'tracking_unit' => $this->stringType(100),
					'total_units' => $this->decimal('5,2'),
					'used_units' => $this->decimal('5,2'),
					'subject' => $this->stringType(100),
					'due_date' => $this->date(),
					'planned_duration' => $this->stringType(256),
					'actual_duration' => $this->stringType(256),
					'contract_status' => $this->stringType(200),
					'priority' => $this->stringType(200),
					'contract_type' => $this->stringType(200),
					'progress' => $this->decimal('5,2'),
					'contract_no' => $this->stringType(100),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
				],
				'index' => [
					['sc_related_to', 'sc_related_to'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'servicecontractsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_servicecontractscf' => [
				'columns' => [
					'servicecontractsid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'servicecontractsid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_seticketsrel' => [
				'columns' => [
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'ticketid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['seticketsrel_crmid_idx', 'crmid'],
					['seticketsrel_ticketid_idx', 'ticketid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['crmid', 'ticketid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_blocks' => [
				'columns' => [
					'blockid' => $this->integer()->notNull(),
					'label' => $this->stringType(250),
					'sequence' => $this->integer(),
					'icon' => $this->stringType(),
					'type' => $this->boolean(),
					'linkto' => $this->text(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'blockid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_blocks_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_field' => [
				'columns' => [
					'fieldid' => $this->integer()->notNull(),
					'blockid' => $this->integer(),
					'name' => $this->stringType(250),
					'iconpath' => $this->stringType(300),
					'description' => $this->stringType(250),
					'linkto' => $this->text(),
					'sequence' => $this->integer(),
					'active' => $this->integer()->defaultValue(0),
					'pinned' => $this->integer(1)->defaultValue(0),
				],
				'index' => [
					['fk_1_vtiger_settings_field', 'blockid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'fieldid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_settings_field_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_sharedcalendar' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'sharedid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'sharedid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_shareduserinfo' => [
				'columns' => [
					'userid' => $this->integer()->notNull()->defaultValue(0),
					'shareduserid' => $this->integer()->notNull()->defaultValue(0),
					'color' => $this->stringType(50),
					'visible' => $this->integer()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_shorturls' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'uid' => $this->stringType(50),
					'handler_path' => $this->stringType(400),
					'handler_class' => $this->stringType(100),
					'handler_function' => $this->stringType(100),
					'handler_data' => $this->stringType(),
					'onetime' => $this->integer(5),
				],
				'index' => [
					['uid', 'uid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_smsnotifier' => [
				'columns' => [
					'smsnotifierid' => $this->integer()->notNull(),
					'message' => $this->text(),
					'status' => $this->stringType(100),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'smsnotifierid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_smsnotifier_servers' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'password' => $this->stringType(),
					'isactive' => $this->integer(1),
					'providertype' => $this->stringType(50),
					'username' => $this->stringType(),
					'parameters' => $this->text(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_smsnotifier_status' => [
				'columns' => [
					'smsnotifierid' => $this->integer(),
					'tonumber' => $this->stringType(20),
					'status' => $this->stringType(10),
					'smsmessageid' => $this->stringType(50),
					'needlookup' => $this->integer(1)->defaultValue(1),
					'statusid' => $this->primaryKey(),
					'statusmessage' => $this->stringType(100),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_smsnotifiercf' => [
				'columns' => [
					'smsnotifierid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'smsnotifierid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_soapservice' => [
				'columns' => [
					'id' => $this->integer(),
					'type' => $this->stringType(25),
					'sessionid' => $this->stringType(100),
					'lang' => $this->stringType(10),
				],
				'index' => [
					['id', ['id', 'type']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_squoteenquiries_status' => [
				'columns' => [
					'squoteenquiries_statusid' => $this->primaryKey(),
					'squoteenquiries_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_squotes_status' => [
				'columns' => [
					'squotes_statusid' => $this->primaryKey(),
					'squotes_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_srecurringorders_status' => [
				'columns' => [
					'srecurringorders_statusid' => $this->primaryKey(),
					'srecurringorders_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_srequirementscards_status' => [
				'columns' => [
					'srequirementscards_statusid' => $this->primaryKey(),
					'srequirementscards_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssalesprocesses_source' => [
				'columns' => [
					'ssalesprocesses_sourceid' => $this->primaryKey(),
					'ssalesprocesses_source' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssalesprocesses_status' => [
				'columns' => [
					'ssalesprocesses_statusid' => $this->primaryKey(),
					'ssalesprocesses_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssalesprocesses_type' => [
				'columns' => [
					'ssalesprocesses_typeid' => $this->primaryKey(),
					'ssalesprocesses_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssingleorders_source' => [
				'columns' => [
					'ssingleorders_sourceid' => $this->primaryKey(),
					'ssingleorders_source' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssingleorders_status' => [
				'columns' => [
					'ssingleorders_statusid' => $this->primaryKey(),
					'ssingleorders_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssservicesstatus' => [
				'columns' => [
					'ssservicesstatusid' => $this->primaryKey(),
					'ssservicesstatus' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ssservicesstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_start_hour' => [
				'columns' => [
					'start_hourid' => $this->primaryKey(),
					'start_hour' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_start_hour_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_state' => [
				'columns' => [
					'stateid' => $this->primaryKey(),
					'state' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_state_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_status' => [
				'columns' => [
					'statusid' => $this->primaryKey(),
					'status' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_status_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_storage_status' => [
				'columns' => [
					'storage_statusid' => $this->primaryKey(),
					'storage_status' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_storage_type' => [
				'columns' => [
					'storage_typeid' => $this->primaryKey(),
					'storage_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_subindustry' => [
				'columns' => [
					'subindustryid' => $this->primaryKey(),
					'subindustry' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_subindustry_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_subunit' => [
				'columns' => [
					'subunitid' => $this->primaryKey(),
					'subunit' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_support_processes' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'ticket_status_indicate_closing' => $this->stringType()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_systems' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
					'server' => $this->stringType(100),
					'server_port' => $this->integer(),
					'server_username' => $this->stringType(100),
					'server_password' => $this->stringType(100),
					'server_type' => $this->stringType(20),
					'smtp_auth' => $this->stringType(5),
					'server_path' => $this->stringType(256),
					'from_email_field' => $this->stringType(50),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tab' => [
				'columns' => [
					'tabid' => $this->integer()->notNull()->defaultValue(0),
					'name' => $this->stringType(25)->notNull(),
					'presence' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'tabsequence' => $this->smallInteger(5)->notNull()->defaultValue(0),
					'tablabel' => $this->stringType(25)->notNull(),
					'modifiedby' => $this->smallInteger(5),
					'modifiedtime' => $this->integer(),
					'customized' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'ownedby' => $this->boolean()->notNull()->defaultValue(0),
					'isentitytype' => $this->boolean()->notNull()->defaultValue(1),
					'version' => $this->stringType(10),
					'parent' => $this->stringType(30),
					'color' => $this->stringType(30),
					'coloractive' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'type' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
				],
				'index' => [
					['tab_name_idx', 'name', true],
					['tab_modifiedby_idx', 'modifiedby'],
					['tab_tabid_idx', 'tabid'],
					['name', ['name', 'presence']],
					['presence', 'presence'],
					['name_2', ['name', 'presence', 'type']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tab_info' => [
				'columns' => [
					'tabid' => $this->integer(),
					'prefname' => $this->stringType(256),
					'prefvalue' => $this->stringType(256),
				],
				'index' => [
					['fk_1_vtiger_tab_info', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_taskpriority' => [
				'columns' => [
					'taskpriorityid' => $this->primaryKey(),
					'taskpriority' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_taskpriority_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_taxclass' => [
				'columns' => [
					'taxclassid' => $this->primaryKey(),
					'taxclass' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
					['taxclass_carrier_idx', 'taxclass', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_taxclass_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketcf' => [
				'columns' => [
					'ticketid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ticketid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketpriorities' => [
				'columns' => [
					'ticketpriorities_id' => $this->primaryKey(),
					'ticketpriorities' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(0),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
					'color' => $this->stringType(25)->defaultValue('	#E6FAD8'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketpriorities_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketseverities' => [
				'columns' => [
					'ticketseverities_id' => $this->primaryKey(),
					'ticketseverities' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(0),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketseverities_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketstatus' => [
				'columns' => [
					'ticketstatus_id' => $this->primaryKey(),
					'ticketstatus' => $this->stringType(200),
					'presence' => $this->integer(1)->notNull()->defaultValue(0),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ticketstatus_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_time_zone' => [
				'columns' => [
					'time_zoneid' => $this->primaryKey(),
					'time_zone' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_time_zone_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_timecontrol_type' => [
				'columns' => [
					'timecontrol_typeid' => $this->primaryKey(),
					'timecontrol_type' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer(),
					'presence' => $this->integer()->notNull()->defaultValue(1),
					'color' => $this->stringType(25)->defaultValue('#E6FAD8'),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_timecontrol_type_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_group_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'relatedtabid' => $this->integer()->notNull(),
					'sharedgroupid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_read_group_rel_sharing_per_userid_sharedgroupid_tabid', ['userid', 'sharedgroupid', 'tabid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'relatedtabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_group_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'sharedgroupid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_read_group_sharing_per_userid_sharedgroupid_idx', ['userid', 'sharedgroupid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_user_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'relatedtabid' => $this->integer()->notNull(),
					'shareduserid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_read_user_rel_sharing_per_userid_shared_reltabid_idx', ['userid', 'shareduserid', 'relatedtabid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'relatedtabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_read_user_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'shareduserid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_read_user_sharing_per_userid_shareduserid_idx', ['userid', 'shareduserid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_group_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'relatedtabid' => $this->integer()->notNull(),
					'sharedgroupid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_write_group_rel_sharing_per_userid_sharedgroupid_tabid_idx', ['userid', 'sharedgroupid', 'tabid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'relatedtabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_group_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'sharedgroupid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_write_group_sharing_per_UK1', ['userid', 'sharedgroupid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'sharedgroupid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_user_rel_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'relatedtabid' => $this->integer()->notNull(),
					'shareduserid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_write_user_rel_sharing_per_userid_sharduserid_tabid_idx', ['userid', 'shareduserid', 'tabid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'relatedtabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tmp_write_user_sharing_per' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'tabid' => $this->integer()->notNull(),
					'shareduserid' => $this->integer()->notNull(),
				],
				'index' => [
					['tmp_write_user_sharing_per_userid_shareduserid_idx', ['userid', 'shareduserid']],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid', 'shareduserid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tracker' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'user_id' => $this->stringType(36),
					'module_name' => $this->stringType(25),
					'item_id' => $this->stringType(36),
					'item_summary' => $this->stringType(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tracking_unit' => [
				'columns' => [
					'tracking_unitid' => $this->primaryKey(),
					'tracking_unit' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_tracking_unit_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_trees_templates' => [
				'columns' => [
					'templateid' => $this->primaryKey(),
					'name' => $this->stringType(),
					'module' => $this->integer(),
					'access' => $this->integer(1)->defaultValue(1),
				],
				'index' => [
					['module', 'module'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_trees_templates_data' => [
				'columns' => [
					'templateid' => $this->smallInteger(5)->unsigned()->notNull(),
					'name' => $this->stringType()->notNull(),
					'tree' => $this->stringType()->notNull(),
					'parenttrre' => $this->stringType()->notNull(),
					'depth' => $this->smallInteger(3)->unsigned()->notNull(),
					'label' => $this->stringType()->notNull(),
					'state' => $this->stringType(10)->notNull()->defaultValue(''),
					'icon' => $this->stringType()->notNull()->defaultValue(''),
				],
				'index' => [
					['id', 'templateid'],
					['parenttrre', ['parenttrre', 'templateid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_troubletickets' => [
				'columns' => [
					'ticketid' => $this->integer()->notNull(),
					'ticket_no' => $this->stringType(100)->notNull(),
					'groupname' => $this->stringType(100),
					'parent_id' => $this->integer(),
					'product_id' => $this->integer(),
					'priority' => $this->stringType(200),
					'severity' => $this->stringType(200),
					'status' => $this->stringType(200),
					'category' => $this->stringType(200),
					'title' => $this->stringType()->notNull(),
					'solution' => $this->text(),
					'update_log' => $this->text(),
					'version_id' => $this->integer(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'servicecontractsid' => $this->integer(),
					'attention' => $this->text(),
					'pssold_id' => $this->integer(),
					'ordertime' => $this->decimal('10,2'),
					'from_portal' => $this->stringType(3),
					'contract_type' => $this->stringType(),
					'contracts_end_date' => $this->date(),
					'report_time' => $this->integer(10),
					'response_time' => $this->dateTime(),
				],
				'index' => [
					['troubletickets_ticketid_idx', 'ticketid'],
					['troubletickets_status_idx', 'status'],
					['parent_id', 'parent_id'],
					['product_id', 'product_id'],
					['servicecontractsid', 'servicecontractsid'],
					['pssold_id', 'pssold_id'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'ticketid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_usageunit' => [
				'columns' => [
					'usageunitid' => $this->primaryKey(),
					'usageunit' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer(),
				],
				'index' => [
					['usageunit_usageunit_idx', 'usageunit', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_usageunit_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user2mergefields' => [
				'columns' => [
					'userid' => $this->integer(),
					'tabid' => $this->integer(),
					'fieldid' => $this->integer(),
					'visible' => $this->integer(2),
				],
				'index' => [
					['userid_tabid_idx', ['userid', 'tabid']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user2role' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'roleid' => $this->stringType()->notNull(),
				],
				'index' => [
					['user2role_roleid_idx', 'roleid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_user_module_preferences' => [
				'columns' => [
					'userid' => $this->stringType(30)->notNull(),
					'tabid' => $this->integer()->notNull(),
					'default_cvid' => $this->integer()->notNull(),
				],
				'index' => [
					['fk_2_vtiger_user_module_preferences', 'tabid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'tabid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'user_name' => $this->stringType(32),
					'user_password' => $this->stringType(200),
					'user_hash' => $this->stringType(32),
					'cal_color' => $this->stringType(25)->defaultValue('#E6FAD8'),
					'first_name' => $this->stringType(30),
					'last_name' => $this->stringType(30),
					'reports_to_id' => $this->integer()->unsigned(),
					'is_admin' => $this->stringType(3)->defaultValue(0),
					'currency_id' => $this->integer()->notNull()->defaultValue(1),
					'description' => $this->text(),
					'date_entered' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
					'date_modified' => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
					'modified_user_id' => $this->stringType(36),
					'email1' => $this->stringType(100),
					'status' => $this->stringType(25),
					'user_preferences' => $this->text(),
					'tz' => $this->stringType(30),
					'holidays' => $this->stringType(60),
					'namedays' => $this->stringType(60),
					'workdays' => $this->stringType(30),
					'weekstart' => $this->integer(),
					'date_format' => $this->stringType(200),
					'hour_format' => $this->stringType(30)->defaultValue('am/pm'),
					'start_hour' => $this->stringType(30)->defaultValue('10:00'),
					'end_hour' => $this->stringType(30)->defaultValue('23:00'),
					'activity_view' => $this->stringType(200)->defaultValue('Today'),
					'lead_view' => $this->stringType(200)->defaultValue('Today'),
					'imagename' => $this->stringType(250),
					'deleted' => $this->boolean()->unsigned()->notNull()->defaultValue(0),
					'confirm_password' => $this->stringType(300),
					'internal_mailer' => $this->boolean()->unsigned()->notNull()->defaultValue(1),
					'reminder_interval' => $this->stringType(100),
					'reminder_next_time' => $this->stringType(100),
					'crypt_type' => $this->stringType(20)->notNull()->defaultValue('MD5'),
					'accesskey' => $this->stringType(36),
					'theme' => $this->stringType(100),
					'language' => $this->stringType(36),
					'time_zone' => $this->stringType(200),
					'currency_grouping_pattern' => $this->stringType(100),
					'currency_decimal_separator' => $this->stringType(2),
					'currency_grouping_separator' => $this->stringType(2),
					'currency_symbol_placement' => $this->stringType(20),
					'phone_crm_extension' => $this->stringType(100),
					'no_of_currency_decimals' => $this->boolean()->unsigned(),
					'truncate_trailing_zeros' => $this->boolean()->unsigned(),
					'dayoftheweek' => $this->stringType(100),
					'callduration' => $this->smallInteger(3)->unsigned(),
					'othereventduration' => $this->smallInteger(3)->unsigned(),
					'calendarsharedtype' => $this->stringType(100),
					'default_record_view' => $this->stringType(10),
					'leftpanelhide' => $this->smallInteger(3)->unsigned(),
					'rowheight' => $this->stringType(10),
					'defaulteventstatus' => $this->stringType(50),
					'defaultactivitytype' => $this->stringType(50),
					'is_owner' => $this->stringType(5),
					'emailoptout' => $this->smallInteger(3)->unsigned()->notNull()->defaultValue(1),
				],
				'index' => [
					['email1', 'email1', true],
					['user_user_name_idx', 'user_name'],
					['user_user_password_idx', 'user_password'],
					['status', 'status'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users2group' => [
				'columns' => [
					'groupid' => $this->integer()->notNull(),
					'userid' => $this->integer()->notNull(),
				],
				'index' => [
					['users2group_groupname_uerid_idx', ['groupid', 'userid']],
					['fk_2_vtiger_users2group', 'userid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['groupid', 'userid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users_last_import' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'assigned_user_id' => $this->stringType(36),
					'bean_type' => $this->stringType(36),
					'bean_id' => $this->stringType(36),
					'deleted' => $this->integer(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['idx_user_id', 'assigned_user_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_users_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_userscf' => [
				'columns' => [
					'usersid' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'usersid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendor' => [
				'columns' => [
					'vendorid' => $this->integer()->notNull()->defaultValue(0),
					'vendor_no' => $this->stringType(100)->notNull(),
					'vendorname' => $this->stringType(100),
					'phone' => $this->stringType(100),
					'email' => $this->stringType(100),
					'website' => $this->stringType(100),
					'glacct' => $this->stringType(200),
					'category' => $this->stringType(50),
					'description' => $this->text(),
					'vat_id' => $this->stringType(30),
					'registration_number_1' => $this->stringType(30),
					'registration_number_2' => $this->stringType(30),
					'verification' => $this->text(),
					'sum_time' => $this->decimal('10,2')->defaultValue(0),
					'active' => $this->boolean()->defaultValue(0),
				],
				'index' => [
					['vendorname', 'vendorname'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'vendorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendoraddress' => [
				'columns' => [
					'vendorid' => $this->integer()->notNull(),
					'addresslevel1a' => $this->stringType(),
					'addresslevel1b' => $this->stringType(),
					'addresslevel1c' => $this->stringType(),
					'addresslevel2a' => $this->stringType(),
					'addresslevel2b' => $this->stringType(),
					'addresslevel2c' => $this->stringType(),
					'addresslevel3a' => $this->stringType(),
					'addresslevel3b' => $this->stringType(),
					'addresslevel3c' => $this->stringType(),
					'addresslevel4a' => $this->stringType(),
					'addresslevel4b' => $this->stringType(),
					'addresslevel4c' => $this->stringType(),
					'addresslevel5a' => $this->stringType(),
					'addresslevel5b' => $this->stringType(),
					'addresslevel5c' => $this->stringType(),
					'addresslevel6a' => $this->stringType(),
					'addresslevel6b' => $this->stringType(),
					'addresslevel6c' => $this->stringType(),
					'addresslevel7a' => $this->stringType(),
					'addresslevel7b' => $this->stringType(),
					'addresslevel7c' => $this->stringType(),
					'addresslevel8a' => $this->stringType(),
					'addresslevel8b' => $this->stringType(),
					'addresslevel8c' => $this->stringType(),
					'poboxa' => $this->stringType(50),
					'poboxb' => $this->stringType(50),
					'poboxc' => $this->stringType(50),
					'buildingnumbera' => $this->stringType(100),
					'buildingnumberb' => $this->stringType(100),
					'buildingnumberc' => $this->stringType(100),
					'localnumbera' => $this->stringType(100),
					'localnumberb' => $this->stringType(100),
					'localnumberc' => $this->stringType(100),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'vendorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendorcf' => [
				'columns' => [
					'vendorid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'vendorid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_vendorcontactrel' => [
				'columns' => [
					'vendorid' => $this->integer()->notNull()->defaultValue(0),
					'contactid' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['vendorcontactrel_vendorid_idx', 'vendorid'],
					['vendorcontactrel_contact_idx', 'contactid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['vendorid', 'contactid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_verification' => [
				'columns' => [
					'verificationid' => $this->primaryKey(),
					'verification' => $this->stringType(200)->notNull(),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
					'picklist_valueid' => $this->integer()->notNull()->defaultValue(0),
					'sortorderid' => $this->integer()->defaultValue(0),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_verification_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_version' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'old_version' => $this->stringType(30),
					'current_version' => $this->stringType(30),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_version_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_visibility' => [
				'columns' => [
					'visibilityid' => $this->primaryKey(),
					'visibility' => $this->stringType(200)->notNull(),
					'sortorderid' => $this->integer()->notNull()->defaultValue(0),
					'presence' => $this->integer(1)->notNull()->defaultValue(1),
				],
				'index' => [
					['visibility_visibility_idx', 'visibility', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_visibility_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_webforms' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(100)->notNull(),
					'publicid' => $this->stringType(100)->notNull(),
					'enabled' => $this->integer(1)->notNull()->defaultValue(1),
					'targetmodule' => $this->stringType(50)->notNull(),
					'description' => $this->text(),
					'ownerid' => $this->integer()->notNull(),
					'returnurl' => $this->stringType(250),
					'captcha' => $this->integer(1)->notNull()->defaultValue(0),
					'roundrobin' => $this->integer(1)->notNull()->defaultValue(0),
					'roundrobin_userid' => $this->stringType(256),
					'roundrobin_logic' => $this->integer()->notNull()->defaultValue(0),
				],
				'index' => [
					['webformname', 'name', true],
					['publicid', 'id', true],
					['webforms_webforms_id_idx', 'id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_webforms_field' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'webformid' => $this->integer()->notNull(),
					'fieldname' => $this->stringType(50)->notNull(),
					'neutralizedfield' => $this->stringType(50)->notNull(),
					'defaultvalue' => $this->stringType(200),
					'required' => $this->integer(10)->notNull()->defaultValue(0),
					'sequence' => $this->integer(10),
					'hidden' => $this->integer(10),
				],
				'index' => [
					['webforms_webforms_field_idx', 'id'],
					['fk_1_vtiger_webforms_field', 'webformid'],
					['fk_2_vtiger_webforms_field', 'fieldname'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_widgets' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'tabid' => $this->integer(),
					'type' => $this->stringType(30),
					'label' => $this->stringType(100),
					'wcol' => $this->boolean()->defaultValue(1),
					'sequence' => $this->smallInteger(2),
					'nomargin' => $this->boolean()->defaultValue(0),
					'data' => $this->text(),
				],
				'index' => [
					['tabid', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(25)->notNull(),
					'handler_path' => $this->stringType()->notNull(),
					'handler_class' => $this->stringType(64)->notNull(),
					'ismodule' => $this->integer(3)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_fieldtype' => [
				'columns' => [
					'fieldtypeid' => $this->primaryKey(),
					'table_name' => $this->stringType(50)->notNull(),
					'field_name' => $this->stringType(50)->notNull(),
					'fieldtype' => $this->stringType(200)->notNull(),
				],
				'index' => [
					['vtiger_idx_1_tablename_fieldname', ['table_name', 'field_name'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_fieldtype_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_name' => [
				'columns' => [
					'entity_id' => $this->integer()->notNull(),
					'name_fields' => $this->stringType(50)->notNull(),
					'index_field' => $this->stringType(50)->notNull(),
					'table_name' => $this->stringType(50)->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'entity_id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_referencetype' => [
				'columns' => [
					'fieldtypeid' => $this->integer()->notNull(),
					'type' => $this->stringType(25)->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['fieldtypeid', 'type']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_entity_tables' => [
				'columns' => [
					'webservice_entity_id' => $this->integer()->notNull(),
					'table_name' => $this->stringType(50)->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['webservice_entity_id', 'table_name']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_fieldinfo' => [
				'columns' => [
					'id' => $this->stringType(64)->notNull(),
					'property_name' => $this->stringType(32),
					'property_value' => $this->stringType(64),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_fieldtype' => [
				'columns' => [
					'fieldtypeid' => $this->primaryKey(),
					'uitype' => $this->stringType(30)->notNull(),
					'fieldtype' => $this->stringType(200)->notNull(),
				],
				'index' => [
					['uitype_idx', 'uitype', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_operation' => [
				'columns' => [
					'operationid' => $this->primaryKey(),
					'name' => $this->stringType(128)->notNull(),
					'handler_path' => $this->stringType()->notNull(),
					'handler_method' => $this->stringType(64)->notNull(),
					'type' => $this->stringType(8)->notNull(),
					'prelogin' => $this->integer(3)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_operation_parameters' => [
				'columns' => [
					'operationid' => $this->primaryKey(),
					'name' => $this->stringType(128)->notNull(),
					'type' => $this->stringType(64)->notNull(),
					'sequence' => $this->integer()->notNull(),
				],
				'index' => [
				],
				// 'primaryKeys' => [
				//  ['PRIMARY KEY', 'name']
				//  ], 
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_operation_seq' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_referencetype' => [
				'columns' => [
					'fieldtypeid' => $this->integer()->notNull(),
					'type' => $this->stringType(25)->notNull(),
				],
				'index' => [
					['fieldtypeid', 'fieldtypeid'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['fieldtypeid', 'type']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_ws_userauthtoken' => [
				'columns' => [
					'userid' => $this->integer()->notNull(),
					'token' => $this->stringType(36)->notNull(),
					'expiretime' => $this->integer()->notNull(),
				],
				'index' => [
					['userid_idx', 'userid', true],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'expiretime']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_wsapp' => [
				'columns' => [
					'appid' => $this->primaryKey(),
					'name' => $this->stringType(200)->notNull(),
					'appkey' => $this->stringType(),
					'type' => $this->stringType(100),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_wsapp_handlerdetails' => [
				'columns' => [
					'type' => $this->stringType(200)->notNull(),
					'handlerclass' => $this->stringType(100),
					'handlerpath' => $this->stringType(300),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_wsapp_queuerecords' => [
				'columns' => [
					'syncserverid' => $this->integer(),
					'details' => $this->stringType(300),
					'flag' => $this->stringType(100),
					'appid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_wsapp_recordmapping' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'serverid' => $this->stringType(10),
					'clientid' => $this->stringType(),
					'clientmodifiedtime' => $this->dateTime(),
					'appid' => $this->integer(),
					'servermodifiedtime' => $this->dateTime(),
					'serverappid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'vtiger_wsapp_sync_state' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(200),
					'stateencodedvalues' => $this->stringType(300)->notNull(),
					'userid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__portal_users' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'server_id' => $this->integer(10),
					'status' => $this->boolean()->defaultValue(0),
					'user_name' => $this->stringType(50)->notNull(),
					'password_h' => $this->stringType(200),
					'password_t' => $this->stringType(200),
					'type' => $this->stringType(30),
					'parent_id' => $this->integer(),
					'login_time' => $this->dateTime(),
					'logout_time' => $this->dateTime(),
					'first_name' => $this->stringType(200),
					'last_name' => $this->stringType(200),
					'language' => $this->stringType(10),
				],
				'index' => [
					['user_name', 'user_name', true],
					['user_name_2', ['user_name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__pos_actions' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'label' => $this->stringType(),
					'name' => $this->stringType(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__pos_users' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'user_name' => $this->stringType(50)->notNull(),
					'user_id' => $this->integer()->notNull(),
					'pass' => $this->stringType()->notNull(),
					'action' => $this->stringType(),
					'server_id' => $this->integer()->notNull(),
					'status' => $this->boolean()->defaultValue(0),
					'last_name' => $this->stringType(),
					'first_name' => $this->stringType(),
					'email' => $this->stringType(),
					'login_time' => $this->dateTime(),
				],
				'index' => [
					['user_name', ['user_name', 'status']],
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
					'status' => $this->boolean()->notNull()->defaultValue(0),
					'api_key' => $this->stringType(100)->notNull(),
					'type' => $this->stringType(40)->notNull(),
					'accounts_id' => $this->integer(),
				],
				'index' => [
					['name', ['name', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'w_#__sessions' => [
				'columns' => [
					'id' => $this->stringType(32)->notNull(),
					'user_id' => $this->integer(),
					'language' => $this->stringType(10),
					'created' => $this->dateTime(),
					'changed' => $this->dateTime(),
					'ip' => $this->stringType(100),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_auth' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'type' => $this->stringType(20),
					'param' => $this->stringType(20),
					'value' => $this->text(),
				],
				'index' => [
					['type', ['type', 'param'], true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_currencyupdate' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'currency_id' => $this->integer()->notNull(),
					'fetch_date' => $this->date()->notNull(),
					'exchange_date' => $this->date()->notNull(),
					'exchange' => $this->decimal('10,4')->notNull(),
					'bank_id' => $this->integer()->notNull(),
				],
				'index' => [
					['fetchdate_currencyid_unique', ['currency_id', 'exchange_date', 'bank_id'], true],
					['fk_1_vtiger_osscurrencies', 'currency_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_currencyupdate_banks' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'bank_name' => $this->stringType()->notNull(),
					'active' => $this->integer(1)->notNull(),
				],
				'index' => [
					['unique_bankname', 'bank_name', true],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_mail_config' => [
				'columns' => [
					'type' => $this->stringType(50),
					'name' => $this->stringType(50),
					'value' => $this->text(),
				],
				'index' => [
					['type', ['type', 'name'], true],
					['type_2', 'type'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_mail_quantities' => [
				'columns' => [
					'userid' => $this->integer(10)->unsigned()->notNull(),
					'num' => $this->integer(10)->unsigned()->defaultValue(0),
					'status' => $this->boolean()->defaultValue(0),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'userid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_menu' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'role' => $this->integer(),
					'parentid' => $this->integer()->defaultValue(0),
					'type' => $this->boolean(),
					'sequence' => $this->integer(3),
					'module' => $this->integer(),
					'label' => $this->stringType(100),
					'newwindow' => $this->boolean()->defaultValue(0),
					'dataurl' => $this->text(),
					'showicon' => $this->boolean()->defaultValue(0),
					'icon' => $this->stringType(),
					'sizeicon' => $this->stringType(),
					'hotkey' => $this->stringType(30),
					'filters' => $this->stringType(),
				],
				'index' => [
					['parent_id', 'parentid'],
					['role', 'role'],
					['module', 'module'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_mobile_keys' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'user' => $this->smallInteger(19)->unsigned()->notNull(),
					'service' => $this->stringType(50)->notNull(),
					'key' => $this->stringType(30)->notNull(),
					'privileges_users' => $this->text(),
				],
				'index' => [
					['user', ['user', 'service']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_mobile_pushcall' => [
				'columns' => [
					'user' => $this->integer()->notNull(),
					'number' => $this->stringType(20),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'user']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_proc_marketing' => [
				'columns' => [
					'type' => $this->stringType(30),
					'param' => $this->stringType(30),
					'value' => $this->stringType(200),
				],
				'index' => [
					['type', ['type', 'param']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_proc_sales' => [
				'columns' => [
					'type' => $this->stringType(30),
					'param' => $this->stringType(30),
					'value' => $this->stringType(200),
				],
				'index' => [
					['type', ['type', 'param']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_proc_tc' => [
				'columns' => [
					'type' => $this->stringType(30),
					'param' => $this->stringType(30),
					'value' => $this->stringType(200),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'yetiforce_updates' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'time' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
					'user' => $this->stringType(50),
					'name' => $this->stringType(100),
					'from_version' => $this->stringType(10),
					'to_version' => $this->stringType(10),
					'result' => $this->boolean(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			]
		];

		$this->foreignKey = [
			//['dav_addressbooks_ibfk_1', 'dav_addressbooks', 'principaluri', 'dav_principals', 'uri', 'CASCADE', 'RESTRICT'],
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
			['fk_1_u_#__announcement', 'u_#__announcement', 'announcementid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__announcement_mark_ibfk_1', 'u_#__announcement_mark', 'announcementid', 'u_#__announcement', 'announcementid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__announcementcf', 'u_#__announcementcf', 'announcementid', 'u_#__announcement', 'announcementid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__competition', 'u_#__competition', 'competitionid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__competition_address_ibfk_1', 'u_#__competition_address', 'competitionaddressid', 'u_#__competition', 'competitionid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__competitioncf', 'u_#__competitioncf', 'competitionid', 'u_#__competition', 'competitionid', 'CASCADE', 'RESTRICT'],
			['u_#__crmentity_last_changes_ibfk_1', 'u_#__crmentity_last_changes', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__crmentity_showners', 'u_#__crmentity_showners', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__favorites', 'u_#__favorites', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_u_#__favorites', 'u_#__favorites', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fbookkeeping_ibfk_1', 'u_#__fbookkeeping', 'fbookkeepingid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fbookkeepingcf_ibfk_1', 'u_#__fbookkeepingcf', 'fbookkeepingid', 'u_#__fbookkeeping', 'fbookkeepingid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_fcorectinginvoice', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__fcorectinginvoice_address_ibfk_1', 'u_#__fcorectinginvoice_address', 'fcorectinginvoiceaddressid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__fcorectinginvoice_inventory', 'u_#__fcorectinginvoice_inventory', 'id', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__fcorectinginvoicecf', 'u_#__fcorectinginvoicecf', 'fcorectinginvoiceid', 'u_#__fcorectinginvoice', 'fcorectinginvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoice', 'u_#__finvoice', 'finvoiceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__finvoice_address_ibfk_1', 'u_#__finvoice_address', 'finvoiceaddressid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoice_inventory', 'u_#__finvoice_inventory', 'id', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoicecf', 'u_#__finvoicecf', 'finvoiceid', 'u_#__finvoice', 'finvoiceid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoiceproforma', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__finvoiceproforma_inventory', 'u_#__finvoiceproforma_inventory', 'id', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_finvoiceproformacf', 'u_#__finvoiceproformacf', 'finvoiceproformaid', 'u_#__finvoiceproforma', 'finvoiceproformaid', 'CASCADE', 'RESTRICT'],
			['u_#__igdn_ibfk_1', 'u_#__igdn', 'igdnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igdn_inventory', 'u_#__igdn_inventory', 'id', 'u_#__igdn', 'igdnid', 'CASCADE', 'RESTRICT'],
			['u_#__igdnc_ibfk_1', 'u_#__igdnc', 'igdncid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igdnc_inventory', 'u_#__igdnc_inventory', 'id', 'u_#__igdnc', 'igdncid', 'CASCADE', 'RESTRICT'],
			['u_#__igdnccf_ibfk_1', 'u_#__igdnccf', 'igdncid', 'u_#__igdnc', 'igdncid', 'CASCADE', 'RESTRICT'],
			['u_#__igdncf_ibfk_1', 'u_#__igdncf', 'igdnid', 'u_#__igdn', 'igdnid', 'CASCADE', 'RESTRICT'],
			['u_#__igin_ibfk_1', 'u_#__igin', 'iginid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igin_inventory', 'u_#__igin_inventory', 'id', 'u_#__igin', 'iginid', 'CASCADE', 'RESTRICT'],
			['u_#__igincf_ibfk_1', 'u_#__igincf', 'iginid', 'u_#__igin', 'iginid', 'CASCADE', 'RESTRICT'],
			['u_#__igrn_ibfk_1', 'u_#__igrn', 'igrnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igrn_inventory', 'u_#__igrn_inventory', 'id', 'u_#__igrn', 'igrnid', 'CASCADE', 'RESTRICT'],
			['u_#__igrnc_ibfk_1', 'u_#__igrnc', 'igrncid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__igrnc_inventory', 'u_#__igrnc_inventory', 'id', 'u_#__igrnc', 'igrncid', 'CASCADE', 'RESTRICT'],
			['u_#__igrnccf_ibfk_1', 'u_#__igrnccf', 'igrncid', 'u_#__igrnc', 'igrncid', 'CASCADE', 'RESTRICT'],
			['u_#__igrncf_ibfk_1', 'u_#__igrncf', 'igrnid', 'u_#__igrn', 'igrnid', 'CASCADE', 'RESTRICT'],
			['u_#__iidn_ibfk_1', 'u_#__iidn', 'iidnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__iidn_inventory', 'u_#__iidn_inventory', 'id', 'u_#__iidn', 'iidnid', 'CASCADE', 'RESTRICT'],
			['u_#__iidncf_ibfk_1', 'u_#__iidncf', 'iidnid', 'u_#__iidn', 'iidnid', 'CASCADE', 'RESTRICT'],
			['u_#__ipreorder_ibfk_1', 'u_#__ipreorder', 'ipreorderid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ipreorder_inventory', 'u_#__ipreorder_inventory', 'id', 'u_#__ipreorder', 'ipreorderid', 'CASCADE', 'RESTRICT'],
			['u_#__ipreordercf_ibfk_1', 'u_#__ipreordercf', 'ipreorderid', 'u_#__ipreorder', 'ipreorderid', 'CASCADE', 'RESTRICT'],
			['u_#__istdn_ibfk_1', 'u_#__istdn', 'istdnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__istdn_inventory', 'u_#__istdn_inventory', 'id', 'u_#__istdn', 'istdnid', 'CASCADE', 'RESTRICT'],
			['u_#__istdncf_ibfk_1', 'u_#__istdncf', 'istdnid', 'u_#__istdn', 'istdnid', 'CASCADE', 'RESTRICT'],
			['u_#__istn_ibfk_1', 'u_#__istn', 'istnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istncf_ibfk_1', 'u_#__istncf', 'istnid', 'u_#__istn', 'istnid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_ibfk_1', 'u_#__istorages', 'istorageid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_address_ibfk_1', 'u_#__istorages_address', 'istorageaddressid', 'u_#__istorages', 'istorageid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_products_ibfk_1', 'u_#__istorages_products', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istorages_products_ibfk_2', 'u_#__istorages_products', 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__istoragescf_ibfk_1', 'u_#__istoragescf', 'istorageid', 'u_#__istorages', 'istorageid', 'CASCADE', 'RESTRICT'],
			['u_#__istrn_ibfk_1', 'u_#__istrn', 'istrnid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__istrn_inventory', 'u_#__istrn_inventory', 'id', 'u_#__istrn', 'istrnid', 'CASCADE', 'RESTRICT'],
			['u_#__istrncf_ibfk_1', 'u_#__istrncf', 'istrnid', 'u_#__istrn', 'istrnid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_knowledgebase', 'u_#__knowledgebase', 'knowledgebaseid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_knowledgebasecf', 'u_#__knowledgebasecf', 'knowledgebaseid', 'u_#__knowledgebase', 'knowledgebaseid', 'CASCADE', 'RESTRICT'],
			['u_#__mail_address_boock_ibfk_1', 'u_#__mail_address_boock', 'id', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__partners', 'u_#__partners', 'partnersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__partners_address_ibfk_1', 'u_#__partners_address', 'partneraddressid', 'u_#__partners', 'partnersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__partnerscf', 'u_#__partnerscf', 'partnersid', 'u_#__partners', 'partnersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__reviewed_queue', 'u_#__reviewed_queue', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculations', 'u_#__scalculations', 'scalculationsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculations_inventory', 'u_#__scalculations_inventory', 'id', 'u_#__scalculations', 'scalculationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__scalculationscf', 'u_#__scalculationscf', 'scalculationsid', 'u_#__scalculations', 'scalculationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiries', 'u_#__squoteenquiries', 'squoteenquiriesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiries_inventory', 'u_#__squoteenquiries_inventory', 'id', 'u_#__squoteenquiries', 'squoteenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squoteenquiriescf', 'u_#__squoteenquiriescf', 'squoteenquiriesid', 'u_#__squoteenquiries', 'squoteenquiriesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotes', 'u_#__squotes', 'squotesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__squotes_address_ibfk_1', 'u_#__squotes_address', 'squotesaddressid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotes_inventory', 'u_#__squotes_inventory', 'id', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__squotescf', 'u_#__squotescf', 'squotesid', 'u_#__squotes', 'squotesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorders', 'u_#__srecurringorders', 'srecurringordersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__srecurringorders_address_ibfk_1', 'u_#__srecurringorders_address', 'srecurringordersaddressid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorders_inventory', 'u_#__srecurringorders_inventory', 'id', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srecurringorderscf', 'u_#__srecurringorderscf', 'srecurringordersid', 'u_#__srecurringorders', 'srecurringordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscards', 'u_#__srequirementscards', 'srequirementscardsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscards_inventory', 'u_#__srequirementscards_inventory', 'id', 'u_#__srequirementscards', 'srequirementscardsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__srequirementscardscf', 'u_#__srequirementscardscf', 'srequirementscardsid', 'u_#__srequirementscards', 'srequirementscardsid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssalesprocesses', 'u_#__ssalesprocesses', 'ssalesprocessesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssalesprocessescf', 'u_#__ssalesprocessescf', 'ssalesprocessesid', 'u_#__ssalesprocesses', 'ssalesprocessesid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorders', 'u_#__ssingleorders', 'ssingleordersid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__ssingleorders_address_ibfk_1', 'u_#__ssingleorders_address', 'ssingleordersaddressid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorders_inventory', 'u_#__ssingleorders_inventory', 'id', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['fk_1_u_#__ssingleorderscf', 'u_#__ssingleorderscf', 'ssingleordersid', 'u_#__ssingleorders', 'ssingleordersid', 'CASCADE', 'RESTRICT'],
			['u_#__watchdog_record_ibfk_1', 'u_#__watchdog_record', 'record', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['u_#__watchdog_schedule_ibfk_1', 'u_#__watchdog_schedule', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_account', 'vtiger_account', 'accountid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_accountaddress_ibfk_1', 'vtiger_accountaddress', 'accountaddressid', 'vtiger_account', 'accountid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_accountscf', 'vtiger_accountscf', 'accountid', 'vtiger_account', 'accountid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_activity', 'vtiger_activity', 'activityid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_reminder_ibfk_1', 'vtiger_activity_reminder', 'activity_id', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_reminder_popup_ibfk_1', 'vtiger_activity_reminder_popup', 'recordid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_update_dates_ibfk_1', 'vtiger_activity_update_dates', 'task_id', 'com_vtiger_workflowtasks', 'task_id', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_update_dates_ibfk_2', 'vtiger_activity_update_dates', 'parent', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_activity_update_dates_ibfk_3', 'vtiger_activity_update_dates', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['vtiger_activitycf_ibfk_1', 'vtiger_activitycf', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_activityproductrel', 'vtiger_activityproductrel', 'productid', 'vtiger_products', 'productid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_assets', 'vtiger_assets', 'assetsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_assetscf_ibfk_1', 'vtiger_assetscf', 'assetsid', 'vtiger_assets', 'assetsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_attachments', 'vtiger_attachments', 'attachmentsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_blocks', 'vtiger_blocks', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['vtiger_callhistory_ibfk_1', 'vtiger_callhistory', 'callhistoryid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_callhistorycf_ibfk_1', 'vtiger_callhistorycf', 'callhistoryid', 'vtiger_callhistory', 'callhistoryid', 'CASCADE', 'RESTRICT'],
			['fk_vtiger_crmentity', 'vtiger_campaign_records', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_campaignscf', 'vtiger_campaignscf', 'campaignid', 'vtiger_campaign', 'campaignid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactaddress', 'vtiger_contactaddress', 'contactaddressid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactdetails', 'vtiger_contactdetails', 'contactid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactscf', 'vtiger_contactscf', 'contactid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_contactsubdetails', 'vtiger_contactsubdetails', 'contactsubscriptionid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_customaction', 'vtiger_customaction', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_customerdetails', 'vtiger_customerdetails', 'customerid', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_customview', 'vtiger_customview', 'entitytype', 'vtiger_tab', 'name', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cvadvfilter', 'vtiger_cvadvfilter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cvcolumnlist', 'vtiger_cvcolumnlist', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_cvstdfilter', 'vtiger_cvstdfilter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_grp2grp', 'vtiger_datashare_grp2grp', 'to_groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_grp2role', 'vtiger_datashare_grp2role', 'to_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_grp2rs', 'vtiger_datashare_grp2rs', 'to_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_datashare_module_rel', 'vtiger_datashare_module_rel', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_datashare_relatedmodules', 'vtiger_datashare_relatedmodules', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_role2group', 'vtiger_datashare_role2group', 'share_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_role2role', 'vtiger_datashare_role2role', 'to_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_role2rs', 'vtiger_datashare_role2rs', 'to_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_rs2grp', 'vtiger_datashare_rs2grp', 'share_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_rs2role', 'vtiger_datashare_rs2role', 'to_roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_datashare_rs2rs', 'vtiger_datashare_rs2rs', 'to_roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_def_org_share', 'vtiger_def_org_share', 'permission', 'vtiger_org_share_action_mapping', 'share_action_id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_defaultcv', 'vtiger_defaultcv', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_entity_stats', 'vtiger_entity_stats', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_entityname', 'vtiger_entityname', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_faq', 'vtiger_faq', 'id', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_faqcf', 'vtiger_faqcf', 'faqid', 'vtiger_faq', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_faqcomments', 'vtiger_faqcomments', 'faqid', 'vtiger_faq', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_field', 'vtiger_field', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_group2grouprel', 'vtiger_group2grouprel', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'CASCADE'],
			['vtiger_group2modules_ibfk_1', 'vtiger_group2modules', 'groupid', 'vtiger_groups', 'groupid', 'CASCADE', 'RESTRICT'],
			['vtiger_group2modules_ibfk_2', 'vtiger_group2modules', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_group2role', 'vtiger_group2role', 'roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_group2rs', 'vtiger_group2rs', 'roleandsubid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_holidaysentitlement', 'vtiger_holidaysentitlement', 'holidaysentitlementid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_holidaysentitlementcf', 'vtiger_holidaysentitlementcf', 'holidaysentitlementid', 'vtiger_holidaysentitlement', 'holidaysentitlementid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_homedashbd', 'vtiger_homedashbd', 'stuffid', 'vtiger_homestuff', 'stuffid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_homedefault', 'vtiger_homedefault', 'stuffid', 'vtiger_homestuff', 'stuffid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_homemodule', 'vtiger_homemodule', 'stuffid', 'vtiger_homestuff', 'stuffid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_homemoduleflds', 'vtiger_homemoduleflds', 'stuffid', 'vtiger_homemodule', 'stuffid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_homerss', 'vtiger_homerss', 'stuffid', 'vtiger_homestuff', 'stuffid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_homestuff', 'vtiger_homestuff', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ideas', 'vtiger_ideas', 'ideasid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ideascf', 'vtiger_ideascf', 'ideasid', 'vtiger_ideas', 'ideasid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leadaddress', 'vtiger_leadaddress', 'leadaddressid', 'vtiger_leaddetails', 'leadid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leaddetails', 'vtiger_leaddetails', 'leadid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leadscf', 'vtiger_leadscf', 'leadid', 'vtiger_leaddetails', 'leadid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_leadsubdetails', 'vtiger_leadsubdetails', 'leadsubscriptionid', 'vtiger_leaddetails', 'leadid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersin', 'vtiger_lettersin', 'lettersinid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersincf', 'vtiger_lettersincf', 'lettersinid', 'vtiger_lettersin', 'lettersinid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersout', 'vtiger_lettersout', 'lettersoutid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_lettersoutcf', 'vtiger_lettersoutcf', 'lettersoutid', 'vtiger_lettersout', 'lettersoutid', 'CASCADE', 'RESTRICT'],
			['vtiger_modcomments_ibfk_1', 'vtiger_modcomments', 'related_to', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_modcommentscf_ibfk_1', 'vtiger_modcommentscf', 'modcommentsid', 'vtiger_modcomments', 'modcommentsid', 'CASCADE', 'RESTRICT'],
			['vtiger_module_dashboard_widgets_ibfk_1', 'vtiger_module_dashboard_widgets', 'templateid', 'vtiger_module_dashboard', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_notes', 'vtiger_notes', 'notesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_notescf_ibfk_1', 'vtiger_notescf', 'notesid', 'vtiger_notes', 'notesid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_org_share_action2tab', 'vtiger_org_share_action2tab', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossemployees', 'vtiger_ossemployees', 'ossemployeesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossemployeescf', 'vtiger_ossemployeescf', 'ossemployeesid', 'vtiger_ossemployees', 'ossemployeesid', 'CASCADE', 'RESTRICT'],
			['vtiger_ossmailtemplates_ibfk_1', 'vtiger_ossmailtemplates', 'ossmailtemplatesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_ossmailtemplatescf_ibfk_1', 'vtiger_ossmailtemplatescf', 'ossmailtemplatesid', 'vtiger_ossmailtemplates', 'ossmailtemplatesid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossmailview', 'vtiger_ossmailview', 'ossmailviewid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossmailview_files', 'vtiger_ossmailview_files', 'ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid', 'CASCADE', 'RESTRICT'],
			['vtiger_ossmailview_relation_ibfk_1', 'vtiger_ossmailview_relation', 'ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossmailviewcf', 'vtiger_ossmailviewcf', 'ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossoutsourcedservices', 'vtiger_ossoutsourcedservices', 'ossoutsourcedservicesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ossoutsourcedservicescf', 'vtiger_ossoutsourcedservicescf', 'ossoutsourcedservicesid', 'vtiger_ossoutsourcedservices', 'ossoutsourcedservicesid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osspasswords', 'vtiger_osspasswords', 'osspasswordsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osspasswordscf', 'vtiger_osspasswordscf', 'osspasswordsid', 'vtiger_osspasswords', 'osspasswordsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osssoldservices', 'vtiger_osssoldservices', 'osssoldservicesid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osssoldservicescf', 'vtiger_osssoldservicescf', 'osssoldservicesid', 'vtiger_osssoldservices', 'osssoldservicesid', 'CASCADE', 'RESTRICT'],
			['vtiger_osstimecontrol', 'vtiger_osstimecontrol', 'osstimecontrolid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_osstimecontrolcf', 'vtiger_osstimecontrolcf', 'osstimecontrolid', 'vtiger_osstimecontrol', 'osstimecontrolid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_outsourcedproducts', 'vtiger_outsourcedproducts', 'outsourcedproductsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_outsourcedproductscf', 'vtiger_outsourcedproductscf', 'outsourcedproductsid', 'vtiger_outsourcedproducts', 'outsourcedproductsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsin', 'vtiger_paymentsin', 'paymentsinid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsincf', 'vtiger_paymentsincf', 'paymentsinid', 'vtiger_paymentsin', 'paymentsinid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsout', 'vtiger_paymentsout', 'paymentsoutid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_paymentsoutcf', 'vtiger_paymentsoutcf', 'paymentsoutid', 'vtiger_paymentsout', 'paymentsoutid', 'CASCADE', 'RESTRICT'],
			['vtiger_pbxmanager_ibfk_1', 'vtiger_pbxmanager', 'pbxmanagerid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_pbxmanager_phonelookup_ibfk_1', 'vtiger_pbxmanager_phonelookup', 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_pbxmanagercf_ibfk_1', 'vtiger_pbxmanagercf', 'pbxmanagerid', 'vtiger_pbxmanager', 'pbxmanagerid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_portalinfo', 'vtiger_portalinfo', 'id', 'vtiger_contactdetails', 'contactid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_pricebook', 'vtiger_pricebook', 'pricebookid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_pricebookcf', 'vtiger_pricebookcf', 'pricebookid', 'vtiger_pricebook', 'pricebookid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_pricebookproductrel', 'vtiger_pricebookproductrel', 'pricebookid', 'vtiger_pricebook', 'pricebookid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_productcf', 'vtiger_productcf', 'productid', 'vtiger_products', 'productid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_products', 'vtiger_products', 'productid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_profile2field_ibfk_1', 'vtiger_profile2field', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_profile2globalpermissions', 'vtiger_profile2globalpermissions', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['vtiger_profile2tab_ibfk_1', 'vtiger_profile2tab', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['vtiger_profile2utility_ibfk_1', 'vtiger_profile2utility', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['vtiger_project_ibfk_1', 'vtiger_project', 'projectid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_projectcf_ibfk_1', 'vtiger_projectcf', 'projectid', 'vtiger_project', 'projectid', 'CASCADE', 'RESTRICT'],
			['vtiger_projectmilestone_ibfk_1', 'vtiger_projectmilestone', 'projectmilestoneid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_projectmilestonecf_ibfk_1', 'vtiger_projectmilestonecf', 'projectmilestoneid', 'vtiger_projectmilestone', 'projectmilestoneid', 'CASCADE', 'RESTRICT'],
			['vtiger_projecttask_ibfk_1', 'vtiger_projecttask', 'projecttaskid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_projecttaskcf_ibfk_1', 'vtiger_projecttaskcf', 'projecttaskid', 'vtiger_projecttask', 'projecttaskid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_recurringevents', 'vtiger_recurringevents', 'activityid', 'vtiger_activity', 'activityid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_relcriteria', 'vtiger_relcriteria', 'queryid', 'vtiger_selectquery', 'queryid', 'CASCADE', 'RESTRICT'],
			['vtiger_relcriteria_grouping_ibfk_1', 'vtiger_relcriteria_grouping', 'queryid', 'vtiger_relcriteria', 'queryid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_report', 'vtiger_report', 'queryid', 'vtiger_selectquery', 'queryid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportdatefilter', 'vtiger_reportdatefilter', 'datefilterid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportgroupbycolumn', 'vtiger_reportgroupbycolumn', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportmodules', 'vtiger_reportmodules', 'reportmodulesid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportsortcol', 'vtiger_reportsortcol', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_reportsummary', 'vtiger_reportsummary', 'reportsummaryid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['vtiger_reservations', 'vtiger_reservations', 'reservationsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_reservationscf', 'vtiger_reservationscf', 'reservationsid', 'vtiger_reservations', 'reservationsid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_role2picklist', 'vtiger_role2picklist', 'roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_role2picklist', 'vtiger_role2picklist', 'picklistid', 'vtiger_picklist', 'picklistid', 'CASCADE', 'RESTRICT'],
			['vtiger_role2profile_ibfk_1', 'vtiger_role2profile', 'roleid', 'vtiger_role', 'roleid', 'CASCADE', 'RESTRICT'],
			['vtiger_role2profile_ibfk_2', 'vtiger_role2profile', 'profileid', 'vtiger_profile', 'profileid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_salesmanactivityrel', 'vtiger_salesmanactivityrel', 'smid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_salesmanattachmentsrel', 'vtiger_salesmanattachmentsrel', 'attachmentsid', 'vtiger_attachments', 'attachmentsid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_salesmanticketrel', 'vtiger_salesmanticketrel', 'smid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['vtiger_scheduled_reports_ibfk_1', 'vtiger_scheduled_reports', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['vtiger_schedulereports_ibfk_1', 'vtiger_schedulereports', 'reportid', 'vtiger_report', 'reportid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_selectcolumn', 'vtiger_selectcolumn', 'queryid', 'vtiger_selectquery', 'queryid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_senotesrel', 'vtiger_senotesrel', 'notesid', 'vtiger_notes', 'notesid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_seproductsrel', 'vtiger_seproductsrel', 'productid', 'vtiger_products', 'productid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_service', 'vtiger_service', 'serviceid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_servicecf_ibfk_1', 'vtiger_servicecf', 'serviceid', 'vtiger_service', 'serviceid', 'CASCADE', 'RESTRICT'],
			['vtiger_servicecontracts_ibfk_1', 'vtiger_servicecontracts', 'servicecontractsid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_servicecontractscf_ibfk_1', 'vtiger_servicecontractscf', 'servicecontractsid', 'vtiger_servicecontracts', 'servicecontractsid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_seticketsrel', 'vtiger_seticketsrel', 'ticketid', 'vtiger_troubletickets', 'ticketid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_settings_field', 'vtiger_settings_field', 'blockid', 'vtiger_settings_blocks', 'blockid', 'CASCADE', 'RESTRICT'],
			['vtiger_smsnotifier_ibfk_1', 'vtiger_smsnotifier', 'smsnotifierid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_smsnotifiercf_ibfk_1', 'vtiger_smsnotifiercf', 'smsnotifierid', 'vtiger_smsnotifier', 'smsnotifierid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_tab_info', 'vtiger_tab_info', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'CASCADE'],
			['fk_1_vtiger_ticketcf', 'vtiger_ticketcf', 'ticketid', 'vtiger_troubletickets', 'ticketid', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_read_group_rel_sharing_per', 'vtiger_tmp_read_group_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_read_group_sharing_per', 'vtiger_tmp_read_group_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_read_user_rel_sharing_per', 'vtiger_tmp_read_user_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_read_user_sharing_per', 'vtiger_tmp_read_user_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_write_group_rel_sharing_per', 'vtiger_tmp_write_group_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_write_group_sharing_per', 'vtiger_tmp_write_group_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_4_vtiger_tmp_write_user_rel_sharing_per', 'vtiger_tmp_write_user_rel_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_tmp_write_user_sharing_per', 'vtiger_tmp_write_user_sharing_per', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_troubletickets', 'vtiger_troubletickets', 'ticketid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_user2role', 'vtiger_user2role', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_user_module_preferences', 'vtiger_user_module_preferences', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'CASCADE'],
			['fk_2_vtiger_users2group', 'vtiger_users2group', 'userid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['vtiger_userscf_ibfk_1', 'vtiger_userscf', 'usersid', 'vtiger_users', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_vendor', 'vtiger_vendor', 'vendorid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'],
			['vtiger_vendoraddress_ibfk_1', 'vtiger_vendoraddress', 'vendorid', 'vtiger_vendor', 'vendorid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_vendorcf', 'vtiger_vendorcf', 'vendorid', 'vtiger_vendor', 'vendorid', 'CASCADE', 'RESTRICT'],
			['fk_2_vtiger_vendorcontactrel', 'vtiger_vendorcontactrel', 'vendorid', 'vtiger_vendor', 'vendorid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_webforms_field', 'vtiger_webforms_field', 'webformid', 'vtiger_webforms', 'id', 'CASCADE', 'RESTRICT'],
			['fk_3_vtiger_webforms_field', 'vtiger_webforms_field', 'fieldname', 'vtiger_field', 'fieldname', 'CASCADE', 'RESTRICT'],
			['vtiger_widgets_ibfk_1', 'vtiger_widgets', 'tabid', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
			['vtiger_fk_1_actors_referencetype', 'vtiger_ws_entity_referencetype', 'fieldtypeid', 'vtiger_ws_entity_fieldtype', 'fieldtypeid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_ws_actor_tables', 'vtiger_ws_entity_tables', 'webservice_entity_id', 'vtiger_ws_entity', 'id', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_referencetype', 'vtiger_ws_referencetype', 'fieldtypeid', 'vtiger_ws_fieldtype', 'fieldtypeid', 'CASCADE', 'RESTRICT'],
			['fk_1_vtiger_osscurrencies', 'yetiforce_currencyupdate', 'currency_id', 'vtiger_currency_info', 'id', 'CASCADE', 'RESTRICT'],
			['yetiforce_mail_quantities_ibfk_1', 'yetiforce_mail_quantities', 'userid', 'roundcube_users', 'user_id', 'CASCADE', 'RESTRICT'],
			['yetiforce_menu_ibfk_1', 'yetiforce_menu', 'module', 'vtiger_tab', 'tabid', 'CASCADE', 'RESTRICT'],
		];
	}

	public function data()
	{
		$this->data = [];
	}
}
