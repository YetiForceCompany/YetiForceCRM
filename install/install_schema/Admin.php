<?php namespace Importers;

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
					'status' => $this->boolean()->unsigned()->notNull(),
					'action' => $this->boolean()->unsigned()->notNull(),
					'conditions' => $this->text()->notNull(),
					'members' => $this->text()->notNull(),
					'priority' => $this->smallInteger(1)->unsigned()->notNull(),
				],
				'index' => [
					['tabid', 'tabid']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce' => [
				'columns' => [
					'attempsnumber' => $this->smallInteger(2)->notNull(),
					'timelock' => $this->smallInteger(5)->notNull(),
					'active' => $this->boolean()->defaultValue(false),
					'sent' => $this->boolean()->defaultValue(false),
				],
				'index' => [
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
					'blocked' => $this->boolean()->defaultValue(false),
					'userid' => $this->integer(),
				],
				'index' => [
					['bf1_mixed', ['ip', 'time', 'blocked']],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__bruteforce_users' => [
				'columns' => [
					'id' => $this->integer()->notNull(),
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'id']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__discounts_config' => [
				'columns' => [
					'param' => $this->stringType(30)->notNull(),
					'value' => $this->stringType()->notNull()
				],
				'index' => [
				],
				'primaryKeys' => [
					['PRIMARY KEY', 'param']
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__discounts_global' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->defaultValue(0)->unsigned()->notNull(),
					'status' => $this->boolean()->defaultValue(true)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__encryption' => [
				'columns' => [
					'method' => $this->stringType(40)->notNull(),
					'pass' => $this->stringType(16)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__featured_filter' => [
				'columns' => [
					'user' => $this->stringType(30)->notNull(),
					'cvid' => $this->integer()->notNull(),
				],
				'index' => [
					['cvid', 'cvid'],
					['user', 'user'],
				],
				'primaryKeys' => [
					['PRIMARY KEY', ['user', 'cvid']]
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__inventory_limits' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'status' => $this->boolean()->defaultValue(false)->notNull(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->integer(10)->unsigned()->notNull(),
				],
				'index' => [
					['status', 'status'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__mapped_config' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'tabid' => $this->smallInteger()->unsigned()->notNull(),
					'reltabid' => $this->smallInteger()->unsigned()->notNull(),
					'status' => $this->boolean()->unsigned()->defaultValue(false),
					'conditions' => $this->text(),
					'permissions' => $this->stringType(),
					'params' => $this->stringType(),
				],
				'index' => [
					['tabid', 'tabid'],
					['reltabid', 'reltabid'],
					['tabid_2', ['tabid', 'status']],
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
					['a_#__mapped_fields_ibfk_1', 'mappedid'],
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
					'status' => $this->boolean()->notNull()->defaultValue(false),
					'primary_name' => $this->stringType()->notNull(),
					'secondary_name' => $this->stringType()->notNull(),
					'meta_author' => $this->stringType()->notNull(),
					'meta_creator' => $this->stringType()->notNull(),
					'meta_keywords' => $this->stringType()->notNull(),
					'metatags_status' => $this->boolean()->notNull(),
					'meta_subject' => $this->stringType()->notNull(),
					'meta_title' => $this->stringType()->notNull(),
					'page_format' => $this->stringType()->notNull(),
					'margin_chkbox' => $this->boolean(),
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
					'default' => $this->boolean(),
					'conditions' => $this->text()->notNull(),
					'watermark_type' => $this->boolean()->notNull()->defaultValue(false),
					'watermark_text' => $this->stringType()->notNull(),
					'watermark_size' => $this->smallInteger(2)->notNull()->unsigned(),
					'watermark_angle' => $this->smallInteger(3)->notNull()->unsigned(),
					'watermark_image' => $this->stringType()->notNull(),
					'template_members' => $this->text()->notNull(),
					'one_pdf' => $this->boolean(),
				],
				'index' => [
					['module_name', ['module_name', 'status']],
					['module_name_2', 'module_name'],
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
					['relation_id', 'relation_id'],
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
					['PRIMARY KEY', 'param']
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__taxes_global' => [
				'columns' => [
					'id' => $this->primaryKey()->unsigned(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->defaultValue(0)->notNull()->unsigned(),
					'status' => $this->boolean()->defaultValue(true)->notNull(),
				],
				'index' => [
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
					['tabid', 'tabid'],
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
					['source_module', ['source_module', 'dest_module']],
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
					['module', ['module', 'crmid', 'type'], true],
					['crmid', 'crmid'],
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
					[1, 'SCalculations', '', '<table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td bgcolor="#13181A" height="25" width="5%"> </td>
			<td bgcolor="#13181A" height="25" width="65%"> </td>
			<td bgcolor="#A42022" height="25" width="25%"> </td>
			<td bgcolor="#A42022" height="25" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td bgcolor="#13181A" height="150" width="5%"> </td>
			<td bgcolor="#13181A" height="150" width="15%"><img alt="%Company+logoname%" src="$Company+logoname$" style="height:80px;float:left;" /></td>
			<td bgcolor="#13181A" height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;float:left;">SPRZEDAWCA:<br /><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$ $Company+city$</p>
			</td>
			<td align="right" height="150" valign="top" width="45%">
			<p style="color:#A42022;font-size:14px;">#CreatedDateTime#,$Company+city$</p>
			 

			<p style="color:#A42022;font-size:20px;">FAKTURA VAT NR<br />
			$subject$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td height="150" width="5%"> </td>
			<td height="150" width="60%">
			<p style="text-transform:uppercase;">Termin płatności: 2015-02-12<br />
			Data wykonania: #CreatedDateTime#<br />
			Forma płatności: PRZELEW<br /><br />
			Nr konta bankowego:<br />
			BZWBK: 69 1090 1056 0000 0001 2602 4598</p>
			</td>
			<td align="right" bgcolor="#A42022" height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;">NABYWCA:<br /><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$<br />
			$accountid+Accounts+addresslevel7a$ $accountid+Accounts+addresslevel5a$ $accountid+Accounts+addresslevel7b$ $accountid+Accounts+addresslevel5b$<br />
			%accountid+Accounts+vat_id% $accountid+Accounts+vat_id$</p>
			</td>
			<td align="right" bgcolor="#A42022" height="150" width="5%"> </td>
		</tr></tbody></table>
 

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td bgcolor="#ffffff" height="150" width="90%">#ProductsTableNew#</td>
			<td align="right" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td bgcolor="#eeeeee" style="font-size:13px;height:100px;" valign="top" width="45%">%attention%<br /><br />
			$attention$</td>
			<td align="right" width="45%">#TableTaxSummary#</td>
			<td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table>
 

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td style="font-size:13px;border:1px solid #ddd;" valign="top" width="45%">Wystawił:<br /><br />
			$assigned_user_id+Users+first_name$ $assigned_user_id+Users+last_name$</td>
			<td width="45%"> </td>
			<td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table>', '<div style="text-align:center;">{nb} z {PAGENO}</div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center" bgcolor="#13181A">
			<p style="color:#fff;font-size:10px;">WE CREATED AN INNOACTIVE CRM SYSTEM THAT IS OPEN AND ADAPTS PERFECTLY TO YOUR BUSINESS.<br />
			FIND OUT ABOUT ITS CAPABILITIES BY DOWNLOADING IT OR TESTING. CHANGE YOUR SYSTEM TO YETIFORCE!</p>
			</td>
		</tr></tbody></table>', 1, 'Kalkulacje', '*', '', '', '', 1, '', '', 'A4', 0, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'kalkulacje', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[2, 'SQuotes', '', '<table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td bgcolor="#13181A" height="25" width="5%"> </td>
			<td bgcolor="#13181A" height="25" width="65%"> </td>
			<td bgcolor="#A42022" height="25" width="25%"> </td>
			<td bgcolor="#A42022" height="25" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td bgcolor="#13181A" height="150" width="5%"> </td>
			<td bgcolor="#13181A" height="150" width="15%"><img alt="%Company+logoname%" src="$Company+logoname$" style="height:80px;float:left;" /></td>
			<td bgcolor="#13181A" height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;float:left;">SPRZEDAWCA:<br /><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$ $Company+city$<br />
			%Company+vatid% $Company+vatid$</p>
			</td>
			<td align="right" height="150" valign="top" width="45%">
			<p style="color:#A42022;font-size:14px;">#CreatedDateTime#,$Company+city$</p>
			 

			<p style="color:#A42022;font-size:20px;"><br />
			$subject$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td height="150" width="5%"> </td>
			<td height="150" width="60%">Data wykonania: #CreatedDateTime#<br /><br />
			Nr konta bankowego:<br />
			BZWBK: 69 1090 1056 0000 0001 2602 4598
			<p> </p>
			</td>
			<td align="right" bgcolor="#A42022" height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;">NABYWCA:<br /><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$<br />
			$accountid+Accounts+addresslevel7a$ $accountid+Accounts+addresslevel5a$ $accountid+Accounts+addresslevel7b$ $accountid+Accounts+addresslevel5b$<br />
			%accountid+Accounts+vat_id% $accountid+Accounts+vat_id$</p>
			</td>
			<td align="right" bgcolor="#A42022" height="150" width="5%"> </td>
		</tr></tbody></table><div style="padding-left:50px;padding-right:50px;">#ProductsTableNew#</div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td bgcolor="#eeeeee" style="font-size:13px;height:100px;" valign="top" width="45%">%attention%<br /><br />
			$attention$</td>
			<td align="right" width="45%">#TableTaxSummary#</td>
			<td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table>
 

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td style="font-size:13px;border:1px solid #ddd;" valign="top" width="45%">Wystawił:<br /><br />
			$assigned_user_id+Users+first_name$ $assigned_user_id+Users+last_name$</td>
			<td width="45%">#TableDiscountSummary#</td>
			<td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table>', '<div style="text-align:center;"> </div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center" bgcolor="#13181A">
			<p style="color:#fff;font-size:10px;">WE CREATED AN INNOACTIVE CRM SYSTEM THAT IS OPEN AND ADAPTS PERFECTLY TO YOUR BUSINESS.<br />
			FIND OUT ABOUT ITS CAPABILITIES BY DOWNLOADING IT OR TESTING. CHANGE YOUR SYSTEM TO YETIFORCE!</p>
			</td>
		</tr></tbody></table>', 1, 'Oferty', '*', '', '', '', 1, '', '', 'A4', 0, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'oferty', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[3, 'SSingleOrders', '', '<table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td bgcolor="#13181A" height="25" width="5%"> </td>
			<td bgcolor="#13181A" height="25" width="65%"> </td>
			<td bgcolor="#A42022" height="25" width="25%"> </td>
			<td bgcolor="#A42022" height="25" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td bgcolor="#13181A" height="150" width="5%"> </td>
			<td bgcolor="#13181A" height="150" width="15%"><img alt="%Company+logoname%" src="$Company+logoname$" style="height:80px;float:left;" /></td>
			<td bgcolor="#13181A" height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;float:left;">SPRZEDAWCA:<br /><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$ $Company+city$<br />
			%Company+vatid% $Company+vatid$</p>
			</td>
			<td align="right" height="150" valign="top" width="45%">
			<p style="color:#A42022;font-size:14px;">#CreatedDateTime#,$Company+city$</p>
			 

			<p style="color:#A42022;font-size:20px;"><br />
			$subject$</p>
			</td>
			<td height="150" width="5%"> </td>
		</tr></tbody></table><table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td height="150" width="5%"> </td>
			<td height="150" width="60%">Data wykonania: #CreatedDateTime#<br /><br />
			Nr konta bankowego:<br />
			BZWBK: 69 1090 1056 0000 0001 2602 4598
			<p> </p>
			</td>
			<td align="right" bgcolor="#A42022" height="150" width="30%">
			<p style="text-transform:uppercase;color:#fff;">NABYWCA:<br /><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$<br />
			$accountid+Accounts+addresslevel7a$ $accountid+Accounts+addresslevel5a$ $accountid+Accounts+addresslevel7b$ $accountid+Accounts+addresslevel5b$<br />
			%accountid+Accounts+vat_id% $accountid+Accounts+vat_id$</p>
			</td>
			<td align="right" bgcolor="#A42022" height="150" width="5%"> </td>
		</tr></tbody></table>
 
<div style="padding-left:50px;padding-right:50px;">
#ProductsTableNew#
</div>
<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td bgcolor="#eeeeee" style="font-size:13px;height:100px;" valign="top" width="45%">%attention%<br /><br />
			$attention$</td>
			<td align="right" width="45%">#TableTaxSummary#</td>
			<td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table>
 

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
			<td style="font-size:13px;border:1px solid #ddd;" valign="top" width="45%">Wystawił:<br /><br />
			$assigned_user_id+Users+first_name$ $assigned_user_id+Users+last_name$</td>
			<td width="45%">#TableDiscountSummary#</td>
			<td align="left" bgcolor="#ffffff" height="150" width="5%"> </td>
		</tr></tbody></table>', '<div style="text-align:center;"> </div>

<table border="0" cellpadding="10" cellspacing="0" style="margin:0 auto;" width="100%"><tbody><tr><td align="center" bgcolor="#13181A">
			<p style="color:#fff;font-size:10px;">WE CREATED AN INNOACTIVE CRM SYSTEM THAT IS OPEN AND ADAPTS PERFECTLY TO YOUR BUSINESS.<br />
			FIND OUT ABOUT ITS CAPABILITIES BY DOWNLOADING IT OR TESTING. CHANGE YOUR SYSTEM TO YETIFORCE!</p>
			</td>
		</tr></tbody></table>', 1, 'Zapytanie jednorazowe', '*', '', '', '', 1, '', '', 'A4', 0, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'zapytanie_jednorazowe', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, 'null', 0, '', 0, 0, '', 'Users:1', 0],
					[4, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>ARKUSZ KONTROLNY STANÓW MAGAZYNOWYCH</b></td>
		</tr></tbody></table><hr /><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;">$Company+organizationname$<br />
			$Company+address$<br />
			$Company+city$, $Company+code$<br /><strong>%Company+vatid%:</strong> $Company+vatid$<br /><strong>%Company+id1%:</strong> $Company+id1$</td>
			<td style="padding:5px;">
			<div style="text-align:right;"><b>%number%</b> $number$<br /><b>%subject%</b> $subject$<br /><strong>Data wygenerowania</strong> #TimeStamp#</div>
			<br />
			 </td>
		</tr></tbody></table></div>
<br />
#IStoragesProductsControlTable#', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>', 1, 'Arkusz kontrolny stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'arkusz_kont_stanow_magazynowych', 'PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[5, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>RAPORT STANÓW MAGAZYNOWYCH</b></td>
		</tr></tbody></table><hr /><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;">$Company+organizationname$<br />
			$Company+address$<br />
			$Company+city$, $Company+code$<br /><strong>%Company+vatid%:</strong> $Company+vatid$<br /><strong>%Company+id1%:</strong> $Company+id1$</td>
			<td style="padding:5px;">
			<div style="text-align:right;"><b>%number%</b> $number$<br /><b>%subject%</b> $subject$<br /><strong>Data wygenerowania</strong> #TimeStamp#</div>
			<br />
			 </td>
		</tr></tbody></table></div>
<br />
#IStoragesProductsTable#', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Raport stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'raport_stanow_magazynowych', 'PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[6, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>RAPORT WARTOŚCIOWY STANÓW MAGAZYNOWYCH</b></td>
		</tr></tbody></table><hr /><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;">$Company+organizationname$<br />
			$Company+address$<br />
			$Company+city$, $Company+code$<br /><strong>%Company+vatid%:</strong> $Company+vatid$<br /><strong>%Company+id1%:</strong> $Company+id1$</td>
			<td style="padding:5px;">
			<div style="text-align:right;"><b>%number%</b> $number$<br /><b>%subject%</b> $subject$<br /><strong>Data wygenerowania</strong> #TimeStamp#</div>
			<br />
			 </td>
		</tr></tbody></table></div>
<br />
#IStoragesProductsValueTable#', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Raport wartościowy stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'raport_wart_stanow_magazynowych', 'PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[7, 'IIDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Przyjęcie wewnętrzne</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><b>FIRMA</b><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$, $Company+city$<br /><b>%Company+vatid%: </b>$Company+vatid$<br /><b>%Company+id1%: </b>$Company+id1$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Przyjęcie wewnętrzne', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'przyjecie_wewnetrzne', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[8, 'IGRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Przyjęcie z zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>DOSTAWCA</strong><br />
			$vendorid+Vendors+vendorname$<br />
			$vendorid+Vendors+addresslevel8a$ $vendorid+Vendors+buildingnumbera$ $vendorid+Vendors+localnumbera$<br />
			$vendorid+Vendors+addresslevel7a$<span style="font-size:10px;">, $vendorid+Vendors+addresslevel5a$<br /><strong>%vendorid+Vendors+vat_id%:</strong> $vendorid+Vendors+vat_id$<br /><strong>%vendorid+Vendors+registration_number_2%: </strong>$vendorid+Vendors+registration_number_2$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Przyjęcie zewnętrzne', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'przyjecie_zewnetrzne', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[9, 'IGIN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Rozchód wewnętrzny</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><b>FIRMA</b><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$, $Company+city$<br /><b>%Company+vatid%: </b>$Company+vatid$<br /><b>%Company+id1%: </b>$Company+id1$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Rozchód wewnętrzny', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'rozchod_wewnetrzny', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[10, 'IGDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Wydanie na zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$Company+code$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Wydanie na zewnątrz', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'wydanie_na_zewnatrz', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[11, 'ISTRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Przyjęcie magazynowe</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>DOSTAWCA</strong><br />
			$vendorid+Vendors+vendorname$<br />
			$vendorid+Vendors+addresslevel8a$ $vendorid+Vendors+buildingnumbera$ $vendorid+Vendors+localnumbera$<br />
			$vendorid+Vendors+addresslevel7a$<span style="font-size:10px;">, $vendorid+Vendors+addresslevel5a$<br /><strong>%vendorid+Vendors+vat_id%:</strong> $vendorid+Vendors+vat_id$<br /><strong>%vendorid+Vendors+registration_number_2%: </strong>$vendorid+Vendors+registration_number_2$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Przyjęcie magazynowe', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'przyjecie_magazynowe', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[12, 'IPreOrder', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;line-height:25.6px;"><b>Rezerwacja magazynowa</b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$Company+code$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Rezerwacja magazynowa', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'rezerwacja_magazynowa', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[13, 'ISTDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Wydanie magazynowe</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$accountid+Accounts+addresslevel7a$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;">PRZYGOTOWAŁ<br /><br /><br /><br />
			.............................................</td>
			<td style="width:50%;text-align:right;">ZAAKCEPTOWAŁ<br /><br /><br /><br />
			.............................................</td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Wydanie magazynowe', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'wydanie_magazynowe', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[14, 'IIDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Internal Delivery Notes</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><b>COMPANY</b><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$, $Company+city$<br /><b>%Company+vatid%: </b>$Company+vatid$<br /><b>%Company+id1%: </b>$Company+id1$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Internal Delivery Notes', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'internal_delivery_notes', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[15, 'IGRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Goods Received Note</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><b>VENDOR</b><br />
			$vendorid+Vendors+vendorname$<br />
			$vendorid+Vendors+addresslevel8a$ $vendorid+Vendors+buildingnumbera$ $vendorid+Vendors+localnumbera$<br />
			$vendorid+Vendors+addresslevel7a$<span style="font-size:10px;">, $vendorid+Vendors+addresslevel5a$<br /><strong>%vendorid+Vendors+vat_id%:</strong> $vendorid+Vendors+vat_id$<br /><strong>%vendorid+Vendors+registration_number_2%: </strong>$vendorid+Vendors+registration_number_2$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Goods Received Note', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'goods_received_note', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[16, 'IGIN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Goods Issued Note</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><b>COMPANY</b><br />
			$Company+organizationname$<br />
			$Company+address$<br />
			$Company+code$, $Company+city$<br /><b>%Company+vatid%: </b>$Company+vatid$<br /><b>%Company+id1%: </b>$Company+id1$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Goods Issued Note', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'goods_issued_note', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[17, 'IGDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Goods Dispatched Note</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>RECIPIENT</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$Company+code$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;">PRZYGOTOWAŁ<br /><br /><br /><br />
			.............................................</td>
			<td style="width:50%;text-align:right;">ZAAKCEPTOWAŁ<br /><br /><br /><br />
			.............................................</td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Goods Dispatched Note', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'goods_dispatched_note', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[18, 'ISTRN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Storage Transfer Received Notes</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>VENDOR</strong><br />
			$vendorid+Vendors+vendorname$<br />
			$vendorid+Vendors+addresslevel8a$ $vendorid+Vendors+buildingnumbera$ $vendorid+Vendors+localnumbera$<br />
			$vendorid+Vendors+addresslevel7a$<span style="font-size:10px;">, $vendorid+Vendors+addresslevel5a$<br /><strong>%vendorid+Vendors+vat_id%:</strong> $vendorid+Vendors+vat_id$<br /><strong>%vendorid+Vendors+registration_number_2%: </strong>$vendorid+Vendors+registration_number_2$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Storage Transfer Received Notes', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'storage_transfer_received_notes', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[19, 'IPreOrder', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Pre-order</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>RECIPIENT</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$Company+code$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;"><b>PRZYGOTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><b>ZAAKCEPTOWAŁ<br /><br /><br /><br />
			............................................. </b></span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Pre-order', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'pre_order', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[20, 'ISTDN', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><strong><span style="font-size:16px;">Storage Transfer Dispatched Notes</span></strong></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Document number:</strong> $number$</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>RECIPIENT</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$accountid+Accounts+addresslevel7b$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>STORAGE</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><br />
#ProductsTableNew#<br />
#ShowDescription#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;">PRZYGOTOWAŁ<br /><br /><br /><br />
			.............................................</td>
			<td style="width:50%;text-align:right;">ZAAKCEPTOWAŁ<br /><br /><br /><br />
			.............................................</td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Storage Transfer Dispatched Notes', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'en_us', 'storage_transfer_dispatched_notes', 'PLL_LISTVIEW,PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[21, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>RAPORT CAŁKOWITY STANÓW MAGAZYNOWYCH</b></td>
		</tr></tbody></table><hr /><div style="width:50%;float:left;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;">$Company+organizationname$<br />
			$Company+address$<br />
			$Company+city$, $Company+code$<br /><strong>%Company+vatid%:</strong> $Company+vatid$<br /><strong>%Company+id1%:</strong> $Company+id1$<br /><b>%number%</b> $number$<br /><b>%subject%</b> $subject$<br /><strong>Data wygenerowania</strong> #TimeStamp#</td>
		</tr></tbody></table></div>
#IStoragesProductsTableHierarchy#', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Raport całkowity stanów magazynowych', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'raport_stanow_magazynowych', 'PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[22, 'IGRNC', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Korekta przyjęcia z zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$<br /><strong>Data wystawienia:</strong> #TimeStamp#</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>DOSTAWCA</strong><br />
			$vendorid+Vendors+vendorname$<br />
			$vendorid+Vendors+addresslevel8a$ $vendorid+Vendors+buildingnumbera$ $vendorid+Vendors+localnumbera$<br />
			$vendorid+Vendors+addresslevel7a$<span style="font-size:10px;">, $vendorid+Vendors+addresslevel5a$<br /><strong>%vendorid+Vendors+vat_id%:</strong> $vendorid+Vendors+vat_id$<br /><strong>%vendorid+Vendors+registration_number_2%: </strong>$vendorid+Vendors+registration_number_2$</span></td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><hr /><strong><span style="font-size:16px;">Było</span></strong><br /><br />
#ProductsTableRelatedModule#
<hr /><strong><span style="font-size:16px;">Winno być</span></strong><br /><br />
#ProductsTableNew#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;">PRZYGOTOWAŁ<br /><br /><br /><br />
			.............................................</span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;">ZAAKCEPTOWAŁ<br /><br /><br /><br />
			.............................................</span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Korekta przyjęcia zewnętrznego', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'korekta_przyjecia_zewnetrznego', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[23, 'IGDNC', '<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:16px;"><strong>Korekta wydania na zewnątrz</strong></span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;"><strong>%acceptance_date%:</strong> $acceptance_date$</span><br /><span style="font-size:10px;"><strong>%assigned_user_id%:</strong> $assigned_user_id$</span><br /><span style="font-size:10px;"><strong>Numer dokumentu:</strong> $number$<br /><strong>Data wystawienia:</strong> #TimeStamp#</span></td>
		</tr></tbody></table><hr />', '<table style="width:100%;"><tbody><tr><td style="width:50%;font-size:10px;"><strong>ODBIORCA</strong><br />
			$accountid+Accounts+accountname$<br />
			$accountid+Accounts+addresslevel8a$ $accountid+Accounts+buildingnumbera$ $accountid+Accounts+localnumbera$<br />
			$Company+code$, $accountid+Accounts+addresslevel5a$<br /><strong>%accountid+Accounts+vat_id%: </strong>$accountid+Accounts+vat_id$<br /><strong>%accountid+Accounts+registration_number_2%: </strong>$accountid+Accounts+registration_number_2$</td>
			<td style="width:50%;font-size:10px;text-align:right;"><b>MAGAZYN</b><br />
			$storageid$<br />
			$storageid+IStorages+addresslevel8a$ $storageid+IStorages+buildingnumbera$ $storageid+IStorages+localnumbera$<br />
			$storageid+IStorages+addresslevel7a$, $storageid+IStorages+addresslevel5a$<br /><br />
			 </td>
		</tr></tbody></table><hr /><strong><span style="font-size:16px;">Było</span></strong><br /><br />
#ProductsTableRelatedModule#
<hr /><strong><span style="font-size:16px;">Winno być</span></strong><br /><br />
#ProductsTableNew#<br />
 
<table style="width:100%;"><tbody><tr><td style="width:50%;"><span style="font-size:10px;">PRZYGOTOWAŁ<br /><br /><br /><br />
			.............................................</span></td>
			<td style="width:50%;text-align:right;"><span style="font-size:10px;">ZAAKCEPTOWAŁ<br /><br /><br /><br />
			.............................................</span></td>
		</tr></tbody></table>', '<div style="text-align:center;"><span style="font-size:8px;"><span style="line-height:20.8px;">{PAGENO} / </span>{nb}</span> </div>
', 1, 'Korekta wydania na zewnątrz', '*', '', '', '', 1, '', '', 'A4', 0, 35, 15, 15, 15, 15, 15, 'PLL_PORTRAIT', 'pl_pl', 'korekta_wydania_na_zewnatrz', 'PLL_LISTVIEW,PLL_DETAILVIEW', 1, '[]', 0, '', 0, 0, '', 'Users:1', 0],
					[24, 'IStorages', '', '<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;font-size:20px;text-align:center;"><b>HIERARCHIA MAGAZYNÓW</b></td>
		</tr></tbody></table><hr /><div style="width:100%;">
<table style="width:100%;border-collapse:collapse;font-size:10px;"><tbody><tr><td style="padding:5px;">$Company+organizationname$<br />
			$Company+address$<br />
			$Company+city$, $Company+code$<br /><strong>%Company+vatid%:</strong> $Company+vatid$<br /><strong>%Company+id1%:</strong> $Company+id1$</td>
			<td style="padding:5px;text-align:right;">
			<div><b>%number%</b> $number$<br /><b>%subject%</b> $subject$<br /><strong>Data wygenerowania</strong> #TimeStamp#</div>
			<br />
			 </td>
		</tr></tbody></table></div>
<br />
#IStoragesTableHierarchy#', '<div style="text-align:center;"><span style="font-size:8px;">{nb} / {PAGENO}</span></div>
', 1, 'Hierarchia magazynów', '*', '', '', '', 1, '', '', 'A4', 1, 0, 0, 0, 0, 0, 0, 'PLL_PORTRAIT', 'pl_pl', 'hierarchia_magazynow', 'PLL_DETAILVIEW', 0, '[]', 0, '', 0, 0, '', 'Users:1', 0],
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
		];
	}
}
