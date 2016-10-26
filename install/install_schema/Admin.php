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
					'time' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->null(),
					'attempts' => $this->smallInteger(2)->defaultValue(0),
					'blocked' => $this->boolean()->defaultValue(0),
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
					'status' => $this->boolean()->defaultValue(1)->notNull(),
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
					'status' => $this->boolean()->defaultValue(0)->notNull(),
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
					'status' => $this->boolean()->unsigned()->defaultValue(0),
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
					'status' => $this->boolean()->notNull()->defaultValue(0),
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
					'page_orientation' => $this->stringType(20)->notNull(),
					'language' => $this->stringType(7)->notNull(),
					'filename' => $this->stringType()->notNull(),
					'visibility' => $this->stringType(20)->notNull(),
					'default' => $this->boolean(),
					'conditions' => $this->text()->notNull(),
					'watermark_type' => $this->stringType(10)->notNull(),
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
					'status' => $this->boolean()->defaultValue(1)->notNull(),
				],
				'index' => [
				],
				'engine' => 'InnoDB',
				'charset' => 'utf8'
			],
		];
		$this->foreignKey = [
			['a_#__mapped_fields_ibfk_1', 'a_#__mapped_fields', 'mappedid', 'a_#__mapped_config', 'id', 'CASCADE'],
		];
	}

	public function data()
	{
		$this->data = [
			'a_#__bruteforce' => [
				'columns' => ['attempsnumber', 'timelock', 'active', 'sent'],
				'values' => [
					[10, 15, 1, 0]
				],
			]
		];
	}
}
