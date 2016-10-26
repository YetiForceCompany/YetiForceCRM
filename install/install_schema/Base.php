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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
				'charset' => 'utf8'
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
			'u_yf_activity_invitation' => [
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
			'u_yf_announcement' => [
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
			'u_yf_announcement_mark' => [
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
			'u_yf_announcementcf' => [
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
			'u_yf_competition' => [
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
			'u_yf_competition_address' => [
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
			'u_yf_competitioncf' => [
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
			'u_yf_crmentity_label' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
					'label' => $this->stringType(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'crmid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_yf_crmentity_last_changes' => [
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
			'u_yf_crmentity_rel_tree' => [
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
			'u_yf_crmentity_search_label' => [
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
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_yf_crmentity_showners' => [
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
			'u_yf_favorites' => [
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
			'u_yf_fbookkeeping' => [
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
			'u_yf_fbookkeepingcf' => [
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
			'u_yf_fcorectinginvoice' => [
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
			'u_yf_fcorectinginvoice_address' => [
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
			'u_yf_fcorectinginvoice_inventory' => [
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
			'u_yf_fcorectinginvoice_invfield' => [
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
			'u_yf_fcorectinginvoice_invmap' => [
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
			'u_yf_fcorectinginvoicecf' => [
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
			'u_yf_finvoice' => [
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
			'u_yf_finvoice_address' => [
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
			'u_yf_finvoice_inventory' => [
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
			'u_yf_finvoice_invfield' => [
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
			'u_yf_finvoice_invmap' => [
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
			'u_yf_finvoicecf' => [
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
			'u_yf_finvoiceproforma' => [
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
			'u_yf_finvoiceproforma_address' => [
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
			'u_yf_finvoiceproforma_inventory' => [
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
			'u_yf_finvoiceproforma_invfield' => [
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
			'u_yf_finvoiceproforma_invmap' => [
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
			'u_yf_finvoiceproformacf' => [
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
			'u_yf_github' => [
				'columns' => [
					'github_id' => $this->primaryKey()->notNull(),
					'client_id' => $this->stringType(20),
					'token' => $this->stringType(100),
					'username' => $this->stringType(32),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_yf_igdn' => [
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
			'u_yf_igdn_inventory' => [
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
			'u_yf_igdn_invfield' => [
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
			'u_yf_igdn_invmap' => [
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
			'u_yf_igdnc' => [
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
			'u_yf_igdnc_inventory' => [
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
			'u_yf_igdnc_invfield' => [
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
			'u_yf_igdnc_invmap' => [
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
			'u_yf_igdnccf' => [
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
			'u_yf_igdncf' => [
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
			'u_yf_igin' => [
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
			'u_yf_igin_inventory' => [
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
			'u_yf_igin_invfield' => [
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
			'u_yf_igin_invmap' => [
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
			'u_yf_igincf' => [
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
			'u_yf_igrn' => [
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
			'u_yf_igrn_inventory' => [
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
			'u_yf_igrn_invfield' => [
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
			'u_yf_igrn_invmap' => [
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
			'u_yf_igrnc' => [
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
			'u_yf_igrnc_inventory' => [
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
			'u_yf_igrnc_invfield' => [
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
			'u_yf_igrnc_invmap' => [
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
			'u_yf_igrnccf' => [
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
			'u_yf_igrncf' => [
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
			'u_yf_iidn' => [
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
			'u_yf_iidn_inventory' => [
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
			'u_yf_iidn_invfield' => [
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
			'u_yf_iidn_invmap' => [
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
			'u_yf_iidncf' => [
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
			'u_yf_ipreorder' => [
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
			'u_yf_ipreorder_inventory' => [
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
			'u_yf_ipreorder_invfield' => [
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
			'u_yf_ipreorder_invmap' => [
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
			'u_yf_ipreordercf' => [
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
			'u_yf_istdn' => [
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
			'u_yf_istdn_inventory' => [
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
			'u_yf_istdn_invfield' => [
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
			'u_yf_istdn_invmap' => [
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
			'u_yf_istdncf' => [
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
			'u_yf_istn' => [
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
			'u_yf_istncf' => [
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
			'u_yf_istorages' => [
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
			'u_yf_istorages_address' => [
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
			'u_yf_istorages_products' => [
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
			'u_yf_istoragescf' => [
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
			'u_yf_istrn' => [
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
			'u_yf_istrn_inventory' => [
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
			'u_yf_istrn_invfield' => [
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
			'u_yf_istrn_invmap' => [
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
			'u_yf_istrncf' => [
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
			'u_yf_knowledgebase' => [
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
			'u_yf_knowledgebasecf' => [
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
			'u_yf_mail_address_boock' => [
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
			'u_yf_mail_autologin' => [
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
			'u_yf_mail_compose_data' => [
				'columns' => [
					'userid' => $this->integer()->unsigned()->notNull(),
					'key' => $this->stringType(32)->notNull(),
					'data' => $this->text()->notNull(),
				],
				'index' => [
					['userid', ['userid', 'key'], true],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['userid', 'key']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_yf_notification' => [
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
			'u_yf_openstreetmap' => [
				'columns' => [
					'crmid' => $this->integer()->unsigned()->notNull(),
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
			'u_yf_openstreetmap_address_updater' => [
				'columns' => [
					'crmid' => $this->integer(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_yf_openstreetmap_cache' => [
				'columns' => [
					'user_id' => $this->integer()->unsigned()->notNull(),
					'module_name' => $this->stringType(50)->notNull(),
					'crmids' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['u_yf_openstreetmap_cache_user_id_module_name_idx', ['user_id', 'module_name']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'u_yf_openstreetmap_record_updater' => [
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
			'u_yf_partners' => [
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
			'u_yf_partners_address' => [
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
			'u_yf_partnerscf' => [
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
			'u_yf_recurring_info' => [
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
			'u_yf_reviewed_queue' => [
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
			'u_yf_scalculations' => [
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
			'u_yf_scalculations_inventory' => [
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
			'u_yf_scalculations_invfield' => [
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
			'u_yf_scalculations_invmap' => [
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
			'u_yf_scalculationscf' => [
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
			'u_yf_squoteenquiries' => [
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
			'u_yf_squoteenquiries_inventory' => [
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
			'u_yf_squoteenquiries_invfield' => [
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
			'u_yf_squoteenquiries_invmap' => [
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
			'u_yf_squoteenquiriescf' => [
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
			'u_yf_squotes' => [
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
			'u_yf_squotes_address' => [
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
			'u_yf_squotes_inventory' => [
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
			'u_yf_squotes_invfield' => [
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
			'u_yf_squotes_invmap' => [
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
			'u_yf_squotescf' => [
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
			'u_yf_srecurringorders' => [
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
			'u_yf_srecurringorders_address' => [
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
			'u_yf_srecurringorders_inventory' => [
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
			'u_yf_srecurringorders_invfield' => [
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
			'u_yf_srecurringorders_invmap' => [
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
			'u_yf_srecurringorderscf' => [
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
			'u_yf_srequirementscards' => [
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
			'u_yf_srequirementscards_inventory' => [
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
			'u_yf_srequirementscards_invfield' => [
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
			'u_yf_srequirementscards_invmap' => [
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
			'u_yf_srequirementscardscf' => [
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
			'u_yf_ssalesprocesses' => [
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
			'u_yf_ssalesprocessescf' => [
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
			'u_yf_ssingleorders' => [
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
			'u_yf_ssingleorders_address' => [
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
			'u_yf_ssingleorders_inventory' => [
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
			'u_yf_ssingleorders_invfield' => [
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
			'u_yf_ssingleorders_invmap' => [
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
			'u_yf_ssingleorderscf' => [
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
			'u_yf_watchdog_module' => [
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
			'u_yf_watchdog_record' => [
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
			'u_yf_watchdog_schedule' => [
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

		$this->foreignKey = [];
	}

	public function data()
	{
		$this->data = [];
	}
}
