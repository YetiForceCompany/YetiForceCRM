<?php
namespace Importers;

/**
 * Class that imports admin database
 * @package YetiForce.Install
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Admin extends \App\Db\Importers\Base
{

	public $dbType = 'admin';

	public function scheme()
	{
		$this->tables = [
			'a_#__adv_permission' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'name' => $this->stringType()->notNull(),
					'tabid' => $this->integer()->null(),
					'status' => $this->smallInteger(1)->unsigned()->notNull(),
					'action' => $this->smallInteger(1)->unsigned()->notNull(),
					'conditions' => $this->text()->null(),
					'members' => $this->text()->notNull(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull(),
				],
				'index' => [
					['adv_permission_idx', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce' => [
				'columns' => [
					'attempsnumber' => $this->smallInteger(2)->notNull(),
					'timelock' => $this->smallInteger(5)->notNull(),
					'active' => $this->smallInteger(1)->defaultValue(0),
					'sent' => $this->smallInteger(1)->defaultValue(0),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce_blocked' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'ip' => $this->stringType(50)->notNull(),
					'time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->null(),
					'attempts' => $this->smallInteger(2)->defaultValue(0),
					'blocked' => $this->smallInteger(1)->defaultValue(0),
					'userid' => $this->integer(),
				],
				'index' => [
					['bruteforce_blocked_idx', ['ip', 'time', 'blocked']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce_users' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'primaryKeys' => [
					['bruteforce_users_pk', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__discounts_config' => [
				'columns' => [
					'param' => $this->stringType(30)->notNull(),
					'value' => $this->stringType()->notNull()
				],
				'primaryKeys' => [
					['discounts_config_pk', 'param']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__discounts_global' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->defaultValue(0)->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->defaultValue(1)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__encryption' => [
				'columns' => [
					'method' => $this->stringType(40)->notNull(),
					'pass' => $this->stringType(16)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__inventory_limits' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'status' => $this->smallInteger(1)->defaultValue(0)->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->integer(10)->unsigned()->notNull(),
				],
				'index' => [
					['inventory_limits_idx', 'status'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__mapped_config' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'tabid' => $this->smallInteger()->unsigned()->notNull(),
					'reltabid' => $this->smallInteger()->unsigned()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->defaultValue(0),
					'conditions' => $this->text(),
					'permissions' => $this->stringType(),
					'params' => $this->stringType(),
				],
				'index' => [
					['mapped_config_tabid_idx', 'tabid'],
					['mapped_config_reltabid_idx', 'reltabid'],
					['mapped_config_status_idx', ['tabid', 'status']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__mapped_fields' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'mappedid' => $this->integer(),
					'type' => $this->stringType(30),
					'source' => $this->stringType(30),
					'target' => $this->stringType(30),
					'default' => $this->stringType(),
				],
				'index' => [
					['mapped_fields_idx', 'mappedid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__pdf' => [
				'columns' => [
					'pdfid' => $this->primaryKey(),
					'module_name' => $this->stringType(25)->notNull(),
					'header_content' => $this->text()->notNull(),
					'body_content' => $this->text()->notNull(),
					'footer_content' => $this->text()->notNull(),
					'status' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'primary_name' => $this->stringType()->notNull(),
					'secondary_name' => $this->stringType()->notNull(),
					'meta_author' => $this->stringType()->notNull(),
					'meta_creator' => $this->stringType()->notNull(),
					'meta_keywords' => $this->stringType()->notNull(),
					'metatags_status' => $this->smallInteger(1)->notNull(),
					'meta_subject' => $this->stringType()->notNull(),
					'meta_title' => $this->stringType()->notNull(),
					'page_format' => $this->stringType()->notNull(),
					'margin_chkbox' => $this->smallInteger(1),
					'margin_top' => $this->smallInteger(2)->notNull()->unsigned(),
					'margin_bottom' => $this->smallInteger(2)->notNull()->unsigned(),
					'margin_left' => $this->smallInteger(2)->notNull()->unsigned(),
					'margin_right' => $this->smallInteger(2)->notNull()->unsigned(),
					'header_height' => $this->smallInteger(2)->notNull()->unsigned(),
					'footer_height' => $this->smallInteger(2)->notNull()->unsigned(),
					'page_orientation' => $this->stringType(30)->notNull(),
					'language' => $this->stringType(7)->notNull(),
					'filename' => $this->stringType()->notNull(),
					'visibility' => $this->stringType(200)->notNull(),
					'default' => $this->smallInteger(1),
					'conditions' => $this->text()->notNull(),
					'watermark_type' => $this->smallInteger(1)->notNull()->defaultValue(0),
					'watermark_text' => $this->stringType()->notNull(),
					'watermark_size' => $this->smallInteger(2)->notNull()->unsigned(),
					'watermark_angle' => $this->smallInteger(3)->notNull()->unsigned(),
					'watermark_image' => $this->stringType()->notNull(),
					'template_members' => $this->text()->notNull(),
					'one_pdf' => $this->smallInteger(1),
				],
				'index' => [
					['pdf_module_status_idx', ['module_name', 'status']],
					['pdf_module_idx', 'module_name'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__relatedlists_inv_fields' => [
				'columns' => [
					'relation_id' => $this->integer(),
					'fieldname' => $this->stringType(30),
					'sequence' => $this->smallInteger(1),
				],
				'index' => [
					['relatedlists_inv_fields_id_idx', 'relation_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__taxes_config' => [
				'columns' => [
					'param' => $this->stringType(30)->notNull(),
					'value' => $this->stringType()->notNull(),
				],
				'primaryKeys' => [
					['taxes_config_pk', 'param']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__taxes_global' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->defaultValue(0)->notNull()->unsigned(),
					'status' => $this->smallInteger(1)->defaultValue(1)->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__automatic_assignment' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned()->notNull(),
					'tabid' => $this->integer(11)->unsigned()->notNull(),
					'field' => $this->stringType(30)->notNull(),
					'value' => $this->stringType(),
					'roles' => $this->text(),
					'smowners' => $this->text(),
					'assign' => $this->smallInteger(5),
					'active' => $this->smallInteger(1)->defaultValue(1),
					'conditions' => $this->text(),
					'user_limit' => $this->smallInteger(1),
					'roleid' => $this->stringType(200)
				],
				'index' => [
					['automatic_assignment_idx', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__companies' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned()->notNull(),
					'name' => $this->stringType(100),
					'short_name' => $this->stringType(100),
					'default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'industry' => $this->stringType(50),
					'street' => $this->stringType(150),
					'city' => $this->stringType(100),
					'code' => $this->stringType(30),
					'state' => $this->stringType(100),
					'country' => $this->stringType(100),
					'phone' => $this->stringType(30),
					'fax' => $this->stringType(30),
					'website' => $this->stringType(100),
					'vatid' => $this->stringType(30),
					'id1' => $this->stringType(30),
					'id2' => $this->stringType(30),
					'email' => $this->stringType(50),
					'logo_login' => $this->stringType(50),
					'logo_login_height' => $this->smallInteger(3)->unsigned(),
					'logo_main' => $this->stringType(50),
					'logo_main_height' => $this->smallInteger(3)->unsigned(),
					'logo_mail' => $this->stringType(50),
					'logo_mail_height' => $this->smallInteger(3)->unsigned(),
				],
				'columns_mysql' => [
					'default' => "tinyint(1) unsigned NOT NULL DEFAULT '0'",
					'logo_login_height' => 'tinyint(3) unsigned DEFAULT NULL',
					'logo_main_height' => 'tinyint(3) unsigned DEFAULT NULL',
					'logo_mail_height' => 'tinyint(3) unsigned DEFAULT NULL',
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_queue' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned()->notNull(),
					'smtp_id' => $this->integer(6)->unsigned()->notNull()->defaultValue(1),
					'date' => $this->dateTime()->notNull(),
					'owner' => $this->integer()->notNull(),
					'status' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'from' => $this->text()->notNull(),
					'subject' => $this->text(),
					'to' => $this->text()->notNull(),
					'content' => $this->text(),
					'cc' => $this->text(),
					'bcc' => $this->text(),
					'attachments' => $this->text(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(1),
					'params' => $this->text()
				],
				'columns_mysql' => [
					'status' => "tinyint(1) unsigned NOT NULL DEFAULT '0'",
					'priority' => "tinyint(1) unsigned NOT NULL DEFAULT '1'",
				],
				'index' => [
					['mail_queue_smtp_id_idx', 'smtp_id'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_relation_updater' => [
				'columns' => [
					'tabid' => $this->integer()->unsigned()->notNull(),
					'crmid' => $this->integer()->unsigned()->notNull(),
				],
				'index' => [
					['mail_updater_idx', 'tabid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__mail_smtp' => [
				'columns' => [
					'id' => $this->primaryKey(6)->unsigned()->notNull(),
					'mailer_type' => $this->stringType()->defaultValue('smtp'),
					'default' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'name' => $this->stringType()->notNull(),
					'host' => $this->stringType()->notNull(),
					'port' => $this->smallInteger(6)->unsigned(),
					'username' => $this->stringType(),
					'password' => $this->stringType(),
					'authentication' => $this->smallInteger(1)->defaultValue(1),
					'secure' => $this->stringType(10),
					'options' => $this->text(),
					'from_email' => $this->stringType(),
					'from_name' => $this->stringType(),
					'replay_to' => $this->stringType(),
					'individual_delivery' => $this->smallInteger(1)->defaultValue(0),
				],
				'columns_mysql' => [
					'default' => "tinyint(1) unsigned NOT NULL DEFAULT '0'",
					'authentication' => "tinyint(1) unsigned NOT NULL DEFAULT '1'",
					'individual_delivery' => "tinyint(1) unsigned NOT NULL DEFAULT '0'",
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__multireference' => [
				'columns' => [
					'source_module' => $this->stringType(50)->notNull(),
					'dest_module' => $this->stringType(50)->notNull(),
					'lastid' => $this->integer(11)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['multireference_idx', ['source_module', 'dest_module']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__privileges_updater' => [
				'columns' => [
					'module' => $this->stringType(30)->notNull()->defaultValue(''),
					'crmid' => $this->integer()->notNull()->defaultValue(0),
					'priority' => $this->smallInteger(1)->unsigned()->notNull()->defaultValue(0),
					'type' => $this->smallInteger(1)->notNull()->defaultValue(0),
				],
				'index' => [
					['privileges_updater_module_idx', ['module', 'crmid', 'type'], true],
					['privileges_updater_crmid_idx', 'crmid'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			's_#__handler_updater' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'tabid' => $this->smallInteger()->notNull()->defaultValue(0)->unsigned(),
					'crmid' => $this->integer()->notNull()->defaultValue(0)->unsigned(),
					'userid' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0)->unsigned(),
					'handler_name' => $this->stringType(50)->notNull(),
					'class' => $this->stringType(50)->notNull(),
					'params' => $this->text()->notNull(),
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['a_#__mapped_fields_ibfk_1', 'a_#__mapped_fields', 'mappedid', 'a_#__mapped_config', 'id', 'CASCADE', 'RESTRICT'],
		];
	}

	public function data()
	{
		$this->data = [
			'a_#__bruteforce' => [
				'columns' => ['attempsnumber', 'timelock', 'active', 'sent'],
				'values' => [
					[10, 15, 1, 0],
				]
			],
			'a_#__discounts_config' => [
				'columns' => ['param', 'value'],
				'values' => [
					['active', '0'],
					['aggregation', '0'],
					['discounts', '0,1,2'],
				]
			],
			'a_#__mapped_config' => [
				'columns' => ['id', 'tabid', 'reltabid', 'status', 'conditions', 'permissions', 'params'],
				'values' => [
					[1, 104, 106, 1, '[]', '', '{"autofill":"on"}'],
					[2, 6, 18, 1, '[]', '', '{"autofill":"on"}'],
				]
			],
			'a_#__mapped_fields' => [
				'columns' => ['id', 'mappedid', 'type', 'source', 'target', 'default'],
				'values' => [
					[1, 1, 'INVENTORY', 'name', 'name', ''],
					[2, 1, 'INVENTORY', 'ean', 'ean', ''],
					[3, 1, 'INVENTORY', 'unit', 'unit', ''],
					[4, 1, 'INVENTORY', 'qty', 'qty', ''],
					[5, 1, 'INVENTORY', 'price', 'price', ''],
					[6, 1, 'INVENTORY', 'comment1', 'comment1', ''],
					[7, 1, 'INVENTORY', 'total', 'total', ''],
					[8, 1, 'V', '2226', '2250', ''],
					[9, 1, 'SELF', 'id', '2262', ''],
					[10, 2, 'V', '1', '288', ''],
					[11, 2, 'E', '9', '291', ''],
				]
			],
			'a_#__pdf' => [
				'columns' => ['pdfid', 'module_name', 'header_content', 'body_content', 'footer_content', 'status', 'primary_name', 'secondary_name', 'meta_author', 'meta_creator', 'meta_keywords', 'metatags_status', 'meta_subject', 'meta_title', 'page_format', 'margin_chkbox', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'header_height', 'footer_height', 'page_orientation', 'language', 'filename', 'visibility', 'default', 'conditions', 'watermark_type', 'watermark_text', 'watermark_size', 'watermark_angle', 'watermark_image', 'template_members', 'one_pdf'],
				'values' => [
					[1, 'SCalculations', '', '<table border="0" style="margin:0 auto;" width="100%"><tr><td height="25" width="5%"> </td>
			<td height="25" width="65%"> </td>
			<td height="25" width="25%"> </td>
			<td height="25" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="15%"><img alt="$(translate : LBL_COMPANY_LOGO)$" src="$(organization%20%3A%20logo_login)$" style="height:80px;float:left;"></td>
			<td>
			<p style="text-transform:uppercase;color:#fff;float:left;">SPRZEDAWCA:<br><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$</p>
			</td>
			<td height="150" width="45%">
			<p style="color:#A42022;font-size:14px;">$(general : CurrentDate)$,$(organization : city)$</p>
			 

			<p style="color:#A42022;font-size:20px;">FAKTURA VAT NR<br>
			$(record : subject)$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="60%">
			<p style="text-transform:uppercase;">Termin płatności: 2015-02-12<br>
			Data wykonania: $(general : CurrentDate)$<br>
			Forma płatności: PRZELEW<br><br>
			Nr konta bankowego:<br>
			BZWBK: 69 1090 1056 0000 0001 2602 4598</p>
			</td>
			<td height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;">NABYWCA:<br><br>
			$(reletedRecord : parent_id|accountname|Accounts)$<br>
			$(reletedRecord : parent_id|addresslevel8a|Accounts)$ $(reletedRecord : parent_id|buildingnumbera|Accounts)$<br>
			$(reletedRecord : parent_id|addresslevel7a|Accounts)$ $(reletedRecord : parent_id|addresslevel5a|Accounts)$(reletedRecord : parent_id|addresslevel7b|Accounts)$ $(reletedRecord : parent_id|addresslevel5b|Accounts)$<br>
			$(translate : Accounts|vat_id)$ $(reletedRecord : parent_id|vat_id|Accounts)$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></table>
 

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="90%">$(custom : ProductsTableNew)$</td>
			<td height="150" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td style="font-size:13px;height:100px;" width="45%">$(translate : SCalculations|Attention)$<br><br>
			$(record : attention)$</td>
			<td width="45%">$(custom : TableTaxSummary)$</td>
			<td height="150" width="5%"> </td>
		</tr></table>
 

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td style="font-size:13px;border:1px solid #ddd;" width="45%">Wystawił:<br><br>
			$(reletedRecord : assigned_user_id|first_name|Users)$ $(reletedRecord : assigned_user_id|last_name|Users)$</td>
			<td width="45%"> </td>
			<td height="150" width="5%"> </td>
		</tr></table>', '<div style="text-align:center;">{nb} z {PAGENO}</div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center" bgcolor="#13181A">
			<p style="color:#fff;font-size:10px;">WE CREATED AN INNOACTIVE CRM SYSTEM THAT IS OPEN AND ADAPTS PERFECTLY TO YOUR BUSINESS.<br />
			FIND OUT ABOUT ITS CAPABILITIES BY DOWNLOADING IT OR TESTING. CHANGE YOUR SYSTEM TO YETIFORCE!</p>
			</td>
		</tr></tbody></table>', 1, 'Kalkulacje', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'kalkulacje', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[2, 'SQuotes', '', '<table border="0" style="margin:0 auto;" width="100%"><tr><td height="25" width="5%"> </td>
			<td height="25" width="65%"> </td>
			<td height="25" width="25%"> </td>
			<td height="25" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="15%"><img alt="$(translate : LBL_COMPANY_LOGO)$" src="$(organization%20%3A%20logo_login)$" style="height:80px;float:left;"></td>
			<td>
			<p style="text-transform:uppercase;color:#fff;float:left;">SPRZEDAWCA:<br><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br>
			$(translate : Vat ID)$ $(organization : vatid)$</p>
			</td>
			<td height="150" width="45%">
			<p style="color:#A42022;font-size:14px;">$(general : CurrentDate)$,$(organization : city)$</p>
			 

			<p style="color:#A42022;font-size:20px;"><br>
			$(record : subject)$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="60%">Data wykonania: $(general : CurrentDate)$<br><br>
			Nr konta bankowego:<br>
			BZWBK: 69 1090 1056 0000 0001 2602 4598
			<p> </p>
			</td>
			<td height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;">NABYWCA:<br><br>
			$(reletedRecord : parent_id|accountname|Accounts)$<br>
			$(reletedRecord : parent_id|addresslevel8a|Accounts)$ $(reletedRecord : parent_id|buildingnumbera|Accounts)$<br>
			$(reletedRecord : parent_id|addresslevel7a|Accounts)$ $(reletedRecord : parent_id|addresslevel5a|Accounts)$(reletedRecord : parent_id|addresslevel7b|Accounts)$ $(reletedRecord : parent_id|addresslevel5b|Accounts)$<br>
			$(translate : Accounts|vat_id)$ $(reletedRecord : parent_id|vat_id|Accounts)$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></table><div style="padding-left:50px;padding-right:50px;">$(custom : ProductsTableNew)$</div>

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td style="font-size:13px;height:100px;" width="45%">$(translate : SQuotes|Attention)$<br><br>
			$(record : attention)$</td>
			<td width="45%">$(custom : TableTaxSummary)$</td>
			<td height="150" width="5%"> </td>
		</tr></table>
 

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td style="font-size:13px;border:1px solid #ddd;" width="45%">Wystawił:<br><br>
			$(reletedRecord : assigned_user_id|first_name|Users)$ $(reletedRecord : assigned_user_id|last_name|Users)$</td>
			<td width="45%">$(custom : TableDiscountSummary)$</td>
			<td height="150" width="5%"> </td>
		</tr></table>', '<div style="text-align:center;"> </div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center" bgcolor="#13181A">
			<p style="color:#fff;font-size:10px;">WE CREATED AN INNOACTIVE CRM SYSTEM THAT IS OPEN AND ADAPTS PERFECTLY TO YOUR BUSINESS.<br />
			FIND OUT ABOUT ITS CAPABILITIES BY DOWNLOADING IT OR TESTING. CHANGE YOUR SYSTEM TO YETIFORCE!</p>
			</td>
		</tr></tbody></table>', 1, 'Oferty', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'oferty', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[3, 'SSingleOrders', '', '<table border="0" style="margin:0 auto;" width="100%"><tr><td height="25" width="5%"> </td>
			<td height="25" width="65%"> </td>
			<td height="25" width="25%"> </td>
			<td height="25" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="15%"><img alt="$(translate : LBL_COMPANY_LOGO)$" src="$(organization%20%3A%20logo_login)$" style="height:80px;float:left;"></td>
			<td>
			<p style="text-transform:uppercase;color:#fff;float:left;">SPRZEDAWCA:<br><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br>
			$(translate : Vat ID)$ $(organization : vatid)$</p>
			</td>
			<td height="150" width="45%">
			<p style="color:#A42022;font-size:14px;">$(general : CurrentDate)$,$(organization : city)$</p>
			 

			<p style="color:#A42022;font-size:20px;"><br>
			$(record : subject)$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td height="150" width="60%">Data wykonania: $(general : CurrentDate)$<br><br>
			Nr konta bankowego:<br>
			BZWBK: 69 1090 1056 0000 0001 2602 4598
			<p> </p>
			</td>
			<td height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;">NABYWCA:<br><br>
			$(reletedRecord : parent_id|accountname|Accounts)$<br>
			$(reletedRecord : parent_id|addresslevel8a|Accounts)$ $(reletedRecord : parent_id|buildingnumbera|Accounts)$<br>
			$(reletedRecord : parent_id|addresslevel7a|Accounts)$ $(reletedRecord : parent_id|addresslevel5a|Accounts)$(reletedRecord : parent_id|addresslevel7b|Accounts)$ $(reletedRecord : parent_id|addresslevel5b|Accounts)$<br>
			$(translate : Accounts|vat_id)$ $(reletedRecord : parent_id|vat_id|Accounts)$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></table>
 

<div style="padding-left:50px;padding-right:50px;">$(custom : ProductsTableNew)$</div>

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td style="font-size:13px;height:100px;" width="45%">$(translate : SSingleOrders|Attention)$<br><br>
			$(record : attention)$</td>
			<td width="45%">$(custom : TableTaxSummary)$</td>
			<td height="150" width="5%"> </td>
		</tr></table>
 

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="5%"> </td>
			<td style="font-size:13px;border:1px solid #ddd;" width="45%">Wystawił:<br><br>
			$(reletedRecord : assigned_user_id|first_name|Users)$ $(reletedRecord : assigned_user_id|last_name|Users)$</td>
			<td width="45%">$(custom : TableDiscountSummary)$</td>
			<td height="150" width="5%"> </td>
		</tr></table>', '<div style="text-align:center;"> </div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center" bgcolor="#13181A">
			<p style="color:#fff;font-size:10px;">WE CREATED AN INNOACTIVE CRM SYSTEM THAT IS OPEN AND ADAPTS PERFECTLY TO YOUR BUSINESS.<br />
			FIND OUT ABOUT ITS CAPABILITIES BY DOWNLOADING IT OR TESTING. CHANGE YOUR SYSTEM TO YETIFORCE!</p>
			</td>
		</tr></tbody></table>', 1, 'Zapytanie jednorazowe', '*', '', '', '', 1, '', '', 'A4', NULL, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'zapytanie_jednorazowe', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, 'null', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[4, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>ARKUSZ KONTROLNY STANÓW MAGAZYNOWYCH</b></td>
		</tr></table><hr><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;">$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br><strong>$(translate : Vat ID)$ $(organization : vatid)$<br><strong>$(translate : Registration number 2)$:</strong>$(organization : id1)$</strong></td>
			<td style="padding:5px;">
			<div style="text-align:right;"><b>$(translate : IStorages|FL_NUMBER)$</b> $(record : number)$<br><b>$(translate : IStorages|FL_SUBJECT)$</b> $(record : subject)$<br><strong>Data wygenerowania</strong> $(general : CurrentTime)$</div>
			<br>
			 </td>
		</tr></table></div>
<br>
$(custom : ProductsControlTable|IStorages)$', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Arkusz kontrolny stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'arkusz_kont_stanow_magazynowych', 'PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[5, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>RAPORT STANÓW MAGAZYNOWYCH</b></td>
		</tr></table><hr><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;">$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br><strong>$(translate : Vat ID)$ $(organization : vatid)$<br><strong>$(translate : Registration number 2)$:</strong>$(organization : id1)$</strong></td>
			<td style="padding:5px;">
			<div style="text-align:right;"><b>$(translate : IStorages|FL_NUMBER)$</b> $(record : number)$<br><b>$(translate : IStorages|FL_SUBJECT)$</b> $(record : subject)$<br><strong>Data wygenerowania</strong> $(general : CurrentTime)$</div>
			<br>
			 </td>
		</tr></table></div>
<br>
$(custom : ProductsTable|IStorages)$', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Raport stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'raport_stanow_magazynowych', 'PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[6, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>RAPORT WARTOŚCIOWY STANÓW MAGAZYNOWYCH</b></td>
		</tr></table><hr><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;">$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br><strong>$(translate : Vat ID)$ $(organization : vatid)$<br><strong>$(translate : Registration number 2)$:</strong>$(organization : id1)$</strong></td>
			<td style="padding:5px;">
			<div style="text-align:right;"><b>$(translate : IStorages|FL_NUMBER)$</b> $(record : number)$<br><b>$(translate : IStorages|FL_SUBJECT)$</b> $(record : subject)$<br><strong>Data wygenerowania</strong> $(general : CurrentTime)$</div>
			<br>
			 </td>
		</tr></table></div>
<br>
$(custom : ProductsValueTable|IStorages)$', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Raport wartościowy stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'raport_wart_stanow_magazynowych', 'PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[7, 'IIDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Przyjęcie wewnętrzne</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IIDN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IIDN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', '<br /> < br /><table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><b>FIRMA</b><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br>
			$(translate : Vat ID)$ $(organization : vatid)$<br><b>$(translate : Registration number 2)$ : </b>$(organization : id1)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(record : RecordId)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$ <br>
$(translate : IIDN|Description)$: $(record : description)$<br>
$(translate : IIDN|Attention)$: $(record : attention)$<br>
 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Przyjęcie wewnętrzne', '*', '', '', '', 1, '', '', 'A4', 1, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'przyjecie_wewnetrzne', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[8, 'IGRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Przyjęcie z zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGRN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGRN|Assigned To)$:</strong> $(translate : IGRN|Assigned To)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>DOSTAWCA</strong><br>
			$(reletedRecord : storageid|RecordId)$<br>
			$(reletedRecord : vendorid|addresslevel8a|Vendors)$ $(reletedRecord : vendorid|addresslevel8a|Vendors)$ $(reletedRecord : vendorid|localnumbera|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel7a|Vendors)$<span style="font-size:10px;">,$(reletedRecord : vendorid|addresslevel5a|Vendors)$<br><strong>$(translate : Vendors|Vat ID)$:</strong> $(reletedRecord : vendorid|vat_id|Vendors)$<br><strong>$(translate : Vendors|Registration number 2)$: </strong>$(reletedRecord : vendorid|registration_number_2|Vendors)$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IGRN|Description)$: $(record : description)$<br>
$(translate : IGRN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Przyjęcie zewnętrzne', '*', '', '', '', 1, '', '', 'A4', 1, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'przyjecie_zewnetrzne', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[9, 'IGIN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Rozchód wewnętrzny</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGIN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGIN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><b>FIRMA</b><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br>
			$(translate : Vat ID)$ $(organization : vatid)$<br><b>$(translate : Registration number 2)$ : </b>$(organization : id1)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(record : RecordId)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
#ShowDescription#<br>
 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Rozchód wewnętrzny', '*', '', '', '', 1, '', '', 'A4', 1, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'rozchod_wewnetrzny', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[10, 'IGDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Wydanie na zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGDN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGDN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(organization : code)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(translate : Accounts|Registration number 2)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|RecordId)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IGDN|Description)$: $(record : description)$<br>
$(translate : IGDN|Attention)$: $(record : attention)$<br>
 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Wydanie na zewnątrz', '*', '', '', '', 1, '', '', 'A4', 1, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'wydanie_na_zewnatrz', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[11, 'ISTRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Przyjęcie magazynowe</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : ISTRN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : ISTRN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>DOSTAWCA</strong><br>
			$(reletedRecord : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel8a|Vendors)$ $(reletedRecord : vendorid|buildingnumbera|Vendors)$ $(reletedRecord : vendorid|localnumbera|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel7a|Vendors)$ $(reletedRecord : vendorid|addresslevel5a|Vendors)$<br><strong>$(translate : Vendors|Vat ID)$:</strong> $(reletedRecord : vendorid|vat_id|Vendors)$<br><strong>$(translate : Vendors|Registration number 2)$: </strong>$(reletedRecord : vendorid|registration_number_2|Vendors)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|RecordId)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : ISTRN|Description)$: $(record : description)$<br>
$(translate : ISTRN|Attention)$: $(record : attention)$<br>
 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Przyjęcie magazynowe', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'przyjecie_magazynowe', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[12, 'IPreOrder', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;line-height:25.6px;"><b>Rezerwacja magazynowa</b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IPreOrder|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IPreOrder|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(organization : code)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(translate : Accounts|Registration number 2)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IPreOrder|Description)$: $(record : description)$<br>
$(translate : IPreOrder|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Rezerwacja magazynowa', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'rezerwacja_magazynowa', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[13, 'ISTDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Wydanie magazynowe</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : ISTDN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : ISTDN|Assigned To)$ :</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel7a|Accounts)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(translate : Accounts|Registration number 2)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : ISTDN|Description)$: $(record : description)$<br>
$(translate : ISTDN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;">PRZYGOTOWAŁ<br><br><br><br>
			.............................................</td>
			<td style="width:50%;text-align:right;">ZAAKCEPTOWAŁ<br><br><br><br>
			.............................................</td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Wydanie magazynowe', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'wydanie_magazynowe', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[14, 'IIDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Internal Delivery Notes</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IIDN|FL_ACCEPTANCE_DATE)$:</strong> $(translate : IIDN|FL_ACCEPTANCE_DATE)$</span><br /><span style="font-size:10px;"><strong>$(translate : IIDN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><b>COMPANY</b><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br>
			$(translate : Vat ID)$ $(organization : vatid)$<br><b>$(translate : Registration number 2)$ : </b>$(organization : id1)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|RecordId)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IIDN|Description)$: $(record : description)$<br>
$(translate : IIDN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Internal Delivery Notes', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'internal_delivery_notes', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[15, 'IGRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Goods Received Note</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGRN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGRN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(translate : IGRN|Assigned To)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><b>VENDOR</b><br>
			$(reletedRecord : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel8a|Vendors)$ $(reletedRecord : vendorid|buildingnumbera|Vendors)$ $(localnumbera : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel7a|Vendors)$ $(reletedRecord : vendorid|addresslevel5a|Vendors)$<br><strong>$(translate : Vendors|Vat ID)$:</strong> $(reletedRecord : vendorid|vat_id|Vendors)$<br><strong>$(translate : Vendors|Registration number 2)$: </strong>$(reletedRecord : vendorid|registration_number_2|Vendors)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IGRN|Description)$: $(record : description)$<br>
$(translate : IGRN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Goods Received Note', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'goods_received_note', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[16, 'IGIN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Goods Issued Note</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGIN|FL_ACCEPTANCE_DATE)$:</strong> $(translate : IGIN|FL_ACCEPTANCE_DATE)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGIN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><b>COMPANY</b><br>
			$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : code)$ $(organization : city)$<br>
			$(translate : Vat ID)$ $(organization : vatid)$<br><b>$(translate : Registration number 2)$ : </b>$(organization : id1)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IGIN|Description)$: $(record : description)$<br>
$(translate : IGIN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Goods Issued Note', '*', '', '', '', 1, '', '', 'A4', 1, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'goods_issued_note', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[17, 'IGDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Goods Dispatched Note</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGDN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGDN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>RECIPIENT</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(organization : code)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(reletedRecord : accountid|registration_number_2|Accounts)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IGDN|Description)$: $(record : description)$<br>
$(translate : IGDN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;">PRZYGOTOWAŁ<br><br><br><br>
			.............................................</td>
			<td style="width:50%;text-align:right;">ZAAKCEPTOWAŁ<br><br><br><br>
			.............................................</td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Goods Dispatched Note', '*', '', '', '', 1, '', '', 'A4', 1, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'goods_dispatched_note', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[18, 'ISTRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Storage Transfer Received Notes</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : ISTRN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : ISTRN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>VENDOR</strong><br>
			$(reletedRecord : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel8a|Vendors)$ $(reletedRecord : vendorid|buildingnumbera|Vendors)$ $(localnumbera : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel7a|Vendors)$<span style="font-size:10px;">, $(reletedRecord : vendorid|addresslevel5a|Vendors)$<br><strong>$(translate : Vendors|Vat ID)$:</strong> $(reletedRecord : vendorid|vat_id|Vendors)$<br><strong>$(translate : Vendors|Registration number 2)$: </strong>$(reletedRecord : vendorid|registration_number_2|Vendors)$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : ISTRN|Description)$: $(record : description)$<br>
$(translate : ISTRN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Storage Transfer Received Notes', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'storage_transfer_received_notes', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[19, 'IPreOrder', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Pre-order</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IPreOrder|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IPreOrder|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>RECIPIENT</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(organization : code)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(reletedRecord : accountid|registration_number_2|Accounts)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br>
$(custom : ProductsTableNew)$<br>
$(translate : IPreOrder|Description)$: $(record : description)$<br>
$(translate : IPreOrder|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br><br><br><br>
			............................................. </b></span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Pre-order', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'pre_order', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[20, 'ISTDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Storage Transfer Dispatched Notes</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : ISTDN|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : ISTDN|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $(record : number)$</span></td>
		</tr></tbody></table><hr />', ' 
<table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>RECIPIENT</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(organization : code)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(reletedRecord : accountid|registration_number_2|Accounts)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><br> 
$(custom : ProductsTableNew)$<br>
$(translate : ISTDN|Description)$: $(record : description)$<br>
$(translate : ISTDN|Attention)$: $(record : attention)$<br>

 
<table style="width:100%;"><tr><td style="width:50%;">PRZYGOTOWAŁ<br><br><br><br>
			.............................................</td>
			<td style="width:50%;text-align:right;">ZAAKCEPTOWAŁ<br><br><br><br>
			.............................................</td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Storage Transfer Dispatched Notes', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'storage_transfer_dispatched_notes', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[21, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>RAPORT CAŁKOWITY STANÓW MAGAZYNOWYCH</b></td>
		</tr></table><hr><div style="width:50%;float:left;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;">$(organization : name)$<br>
			$(organization : street)$<br>
			$(organization : city)$, $(organization : code)$<br><strong>$(translate : Vat ID)$ :</strong> $(organization : vatid)$<br><strong>$(translate : Registration number 2)$:</strong> $(organization : id1)$<br><b>$(translate : IStorages|FL_NUMBER)$</b> $(record : number)$<br><b>$(translate : IStorages|FL_SUBJECT)$</b> $(record : subject)$<br><strong>Data wygenerowania</strong> $(general : CurrentDate)$</td>
		</tr></table></div>
$(custom : ProductsTableHierarchy|IStorages)$', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Raport całkowity stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'raport_stanow_magazynowych', 'PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[22, 'IGRNC', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Korekta przyjęcia z zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGRNC|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGRNC|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$<br /><strong>Data wystawienia:</strong> $(general : CurrentDate)$</span></td>
		</tr></tbody></table><hr />', '<br><br><table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>DOSTAWCA</strong><br>
			$(reletedRecord : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel8a|Vendors)$ $(reletedRecord : vendorid|buildingnumbera|Vendors)$ $(localnumbera : vendorid|vendorname|Vendors)$<br>
			$(reletedRecord : vendorid|addresslevel7a|Vendors)$ $(reletedRecord : vendorid|addresslevel5a|Vendors)$<br><strong>$(translate : Vendors|Vat ID)$:</strong> $(reletedRecord : vendorid|vat_id|Vendors)$<br><strong>$(translate : Vendors|Registration number 2)$: </strong>$(reletedRecord : vendorid|registration_number_2|Vendors)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><hr><strong><span style="font-size:16px;">Było</span></strong><br><br>
$(custom : ProductsTableRelatedModule)$
<hr><strong><span style="font-size:16px;">Winno być</span></strong><br><br>
$(custom : ProductsTableNew)$<br>
 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;">PRZYGOTOWAŁ<br><br><br><br>
			.............................................</span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;">ZAAKCEPTOWAŁ<br><br><br><br>
			.............................................</span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Korekta przyjęcia zewnętrznego', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'korekta_przyjecia_zewnetrznego', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[23, 'IGDNC', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Korekta wydania na zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>$(translate : IGDNC|FL_ACCEPTANCE_DATE)$:</strong> $(record : acceptance_date)$</span><br /><span style="font-size:10px;"><strong>$(translate : IGDNC|Assigned To)$:</strong> $(record : assigned_user_id)$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $(record : number)$<br /><strong>Data wystawienia:</strong> $(general : CurrentDate)$</span></td>
		</tr></tbody></table><hr />', '<br><br><table style="width:100%;"><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br>
			$(reletedRecord : accountid|accountname|Accounts)$<br>
			$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ $(reletedRecord : accountid|localnumbera|Accounts)$<br>
			$(organization : code)$, $(reletedRecord : accountid|addresslevel5a|Accounts)$<br><strong>$(translate : Accounts|Vat ID)$: </strong>$(reletedRecord : accountid|vat_id|Accounts)$<br><strong>$(reletedRecord : accountid|registration_number_2|Accounts)$: </strong>$(reletedRecord : accountid|registration_number_2|Accounts)$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br>
			$(reletedRecord : storageid|number|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel8a|IStorages)$ $(reletedRecord : storageid|buildingnumbera|IStorages)$ $(reletedRecord : storageid|localnumbera|IStorages)$<br>
			$(reletedRecord : storageid|addresslevel7a|IStorages)$ $(reletedRecord : storageid|addresslevel5a|IStorages)$<br><br>
			 </td>
		</tr></table><hr><strong><span style="font-size:16px;">Było</span></strong><br><br>
$(custom : ProductsTableRelatedModule)$
<hr><strong><span style="font-size:16px;">Winno być</span></strong><br><br>
$(custom : ProductsTableNew)$<br>
 
<table style="width:100%;"><tr><td style="width:50%;"><span style="font-size:10px;">PRZYGOTOWAŁ<br><br><br><br>
			.............................................</span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;">ZAAKCEPTOWAŁ<br><br><br><br>
			.............................................</span></td>
		</tr></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Korekta wydania na zewnątrz', '*', '', '', '', 1, '', '', 'A4', NULL, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'korekta_wydania_na_zewnatrz', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[24, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>HIERARCHIA MAGAZYNÓW</b></td>
		</tr></table><hr><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tr><td style="padding:5px;">
			$(organization : name)$<br>
			$(organization : code)$ $(organization : city)$<br><strong>$(translate : Vat ID)$:</strong> $(organization : vatid)$<br><strong>$(translate : Registration number 2)$:</strong> $(organization : id1)$</td>
			<td style="padding:5px;text-align:right;">
			<div><b>$(translate : IStorages|FL_NUMBER)$</b>$(record : number)$<br><b>$(translate : IStorages|FL_SUBJECT)$</b> $(record : subject)$<br><strong>Data wygenerowania</strong> $(general : CurrentDate)$</div>
			<br>
			 </td>
		</tr></table></div>
<br>
$(custom : ProductsTableHierarchy|IStorages)$', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Hierarchia magazynów', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'hierarchia_magazynow', 'PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Roles:H2,RoleAndSubordinates:H2', NULL],
					[25, 'FInvoice', '', '<table border="0" style="margin:0 auto;" width="100%"><tr><td height="150" width="60%">
				<img alt="$(translate : LBL_COMPANY_LOGO)$" src="$(organization%20%3A%20logo_login)$" style="height:80px;float:left;"></td>
			<td height="150" width="40%">
				<table border="0" style="margin:0 auto;" width="100%"><tr><td height="20" width="100%">
								Miejsce wystawienia: <b>$(organization : city)$</b><br>
								Data wystawienia: <b>$(general : CurrentDate)$</b><br>
								$(translate : FInvoice|FL_SALE_DATE)$: <b>$(record : saledate)$</b><br>
								Faktura VAT:<b> $(record : number)$</b> 
							</td>
						</tr><tr><td height="130" width="100%"> 							
							</td>
						</tr></table></td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
			<td height="150" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="40%">
				<table border="0" style="margin:0 auto;" width="100%"><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);font-size:21px;text-align:left;"><b>Sprzedawca</b> </p>
							</td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;"><b>$(organization : name)$</b>
							</p></td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;">$(organization : code)$ - $(organization : city)$,<br>$(organization : street)$   </p>
							</td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;">NIP: $(organization : vatid)$</p>
							</td>
						</tr></table></td>
			<td height="150" width="20%"></td>
			<td height="150" width="40%">
				<table border="0" style="margin:0 auto;" width="100%"><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);font-size:21px;text-align:left;"><b>Nabywca</b> </p>
							</td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;"><b>$(reletedRecord : accountid|accountname|Accounts)$</b></p>
							</td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;">$(reletedRecord : accountid|addresslevel8a|Accounts)$ $(reletedRecord : accountid|buildingnumbera|Accounts)$ </p>
							</td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;">$(reletedRecord : accountid|addresslevel7a|Accounts)$ $(reletedRecord : accountid|addresslevel5a|Accounts)$ </p>
							</td>
						</tr><tr><td height="20" width="100%">
								<p style="color:rgb(0,0,0);text-align:left;">$(translate : Accounts|Vat ID)$: $(reletedRecord : accountid|vat_id|Accounts)$  </p>
							</td>
						</tr></table></td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
			<td height="150" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="100%">
				<b>$(translate : FInvoice|FL_FORM_PAYMENT)$:</b> $(record : finvoice_formpayment)$
			</td>
		</tr><tr><td height="140" width="100%">
				<b>Numer konta bankowego:</b> 00 0000 0000 0000 0000 0000 0000
			</td>
		</tr><tr><td height="140" width="100%">
				 
			</td>
		</tr><tr><td height="140" width="100%">
				<b>$(translate : FInvoice|Description)$:</b> $(record : description)$
			</td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
			<td height="150" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
		</tr></table><div>$(custom : ProductsTableNew)$</div>

<table border="0" style="margin:0 auto;" width="100%"><tr><td height="40" width="100%"> 
			</td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="40%">
				$(custom : TableTaxSummary)$
			</td>
			<td height="150" width="10%"></td>
			<td height="150" width="40%">
				$(custom : TableDiscountSummary)$
			</td>
			<td height="150" width="10%"></td>
		</tr></table><br><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="100%">
				<b>Do zapłaty:</b> $(record : sum_gross)$
			</td>
		</tr><tr><td height="140" width="100%">
				<b>Do zapłaty słownie:</b> $(custom : GrossAmountInWords)$
			</td>
		</tr></table><table border="0" style="margin:0 auto;" width="100%"><tr><td height="140" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
			<td height="150" width="40%" style="text-align:center;">
				<br></td>
			<td height="150" width="10%"></td>
		</tr></table><br><br><table border="0" style="margin:0 auto;" width="100%"><tr><td height="240" width="40%" style="text-align:center;">
				..............................................................<br><font>Osoba upoważniona do odbioru</font>
			</td>
			<td height="250" width="20%"></td>
			<td height="250" width="40%" style="text-align:center;">
				..............................................................<br><font>Osoba upoważniona do wystawienia</font>
			</td>
		</tr></table>', '<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center">
			<p style="font-size:12px;">Generated by YetiForce CRM</p>
			</td>
		</tr></tbody></table>', 1, 'Faktura', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'Faktura', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', '', NULL],
				]
			],
			'a_#__taxes_config' => [
				'columns' => ['param', 'value'],
				'values' => [
					['active', '0'],
					['aggregation', '0'],
					['taxs', '0,1,2,3'],
				]
			],
			'a_#__taxes_global' => [
				'columns' => ['id', 'name', 'value', 'status'],
				'values' => [
					[1, 'VAT', '23.00', 0],
				]
			],
			's_#__companies' => [
				'columns' => ['id', 'name', 'short_name', 'default', 'industry', 'street', 'city', 'code', 'state', 'country', 'phone', 'fax', 'website', 'vatid', 'id1', 'id2', 'email', 'logo_login', 'logo_login_height', 'logo_main', 'logo_main_height', 'logo_mail', 'logo_mail_height'],
				'values' => [
					[1, 'YetiForce Sp. z o.o. ', 'YetiForce', 1, NULL, 'ul. Marszałkowska 111', 'Warszawa', '00-102', 'Mazowieckie', 'Poland', '+48 22 415 49 34', NULL, 'yetiforce.com', NULL, NULL, NULL, NULL, 'logo_yetiforce.png', 200, 'blue_yetiforce_logo.png', 38, 'logo_yetiforce.png', 50],
				]
			],
		];
	}
}
