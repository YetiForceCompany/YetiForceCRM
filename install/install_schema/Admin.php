<?php namespace Importers;

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
					'priority' => $this->smallInteger()->unsigned()->notNull(),
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
					'active' => $this->boolean()->defaultValue(0),
					'sent' => $this->boolean()->defaultValue(0),
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
					'time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
					'attempts' => $this->smallInteger(2),
					'blocked' => $this->boolean(),
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
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__discounts_global' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(50)->notNull(),
					'value' => $this->decimal('5,2')->defaultValue(0),
					'status' => $this->boolean()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__encryption' => [
				'columns' => [
					'method' => $this->stringType(40)->notNull(),
					'pass' => $this->stringType(11)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a__yf_featured_filter' => [
				'columns' => [
					'user' => $this->stringType(30),
					'cvid' => $this->integer(),
				],
				'index' => [
					['cvid', 'cvid'],
					['user', 'user'],
				],
				'primaryKeys' => [
					['cvid', 'cvid'],
					['user', 'user'],
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__inventory_limits' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'status' => $this->boolean()->defaultValue(0),
					'name' => $this->stringType(50),
					'value' => $this->integer(),
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
					'tabid' => $this->smallInteger(),
					'reltabid' => $this->smallInteger(),
					'status' => $this->boolean(),
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
					'status' => $this->boolean()->notNull(),
					'primary_name' => $this->stringType()->notNull(),
					'secondary_name' => $this->stringType()->notNull(),
					'meta_author' => $this->stringType()->notNull(),
					'meta_creator' => $this->stringType()->notNull(),
					'meta_keywords' => $this->stringType()->notNull(),
					'metatags_status' => $this->boolean(),
					'meta_subject' => $this->stringType()->notNull(),
					'meta_title' => $this->stringType()->notNull(),
					'page_format' => $this->stringType()->notNull(),
					'margin_chkbox' => $this->boolean(),
					'margin_top' => $this->smallInteger(2)->notNull(),
					'margin_bottom' => $this->smallInteger(2)->notNull(),
					'margin_left' => $this->smallInteger(2)->notNull(),
					'margin_right' => $this->smallInteger(2)->notNull(),
					'header_height' => $this->smallInteger(2)->notNull(),
					'footer_height' => $this->smallInteger(2)->notNull(),
					'page_orientation' => $this->stringType(20)->notNull(),
					'language' => $this->stringType(7)->notNull(),
					'filename' => $this->stringType()->notNull(),
					'visibility' => $this->stringType(20)->notNull(),
					'default' => $this->boolean(),
					'conditions' => $this->text()->notNull(),
					'watermark_type' => $this->stringType(10)->notNull(),
					'watermark_text' => $this->stringType()->notNull(),
					'watermark_size' => $this->smallInteger(2)->notNull(),
					'watermark_angle' => $this->smallInteger(3)->notNull(),
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
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
			'a_#__taxes_global' => [
				'columns' => [
					'id' => $this->primaryKey(),
					'name' => $this->stringType(50),
					'value' => $this->decimal('5,2')->defaultValue(0),
					'status' => $this->boolean()->defaultValue(1),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['fk_1_vtiger_bruteforce_users', 'a_yf_bruteforce_users', 'id', 'vtiger_users', 'id', 'CASCADE'],
			['a_yf_featured_filter_ibfk_1', 'a_yf_featured_filter', 'cvid', 'vtiger_customview', 'cvid', 'CASCADE'],
			['a_yf_mapped_fields_ibfk_1', 'a_yf_mapped_fields', 'mappedid', 'a_yf_mapped_config', 'id', 'CASCADE'],
		];
	}

	public function data()
	{
		$this->data = [
			'a_#__adv_permission' => [
				'columns' => ['name', 'tabid', 'status', 'action'],
				'values' => [
					[10, 15, 1, 0]
				],
			]
		];
	}
}
