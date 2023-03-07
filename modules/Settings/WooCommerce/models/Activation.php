<?php
/**
 * Activation file for WooCommerce integration model.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Activation class for WooCommerce integration model.
 */
class Settings_WooCommerce_Activation_Model
{
	/** @var array Map relation table name */
	private const FIELDS = [
		'Products' => [
			'block' => ['name' => 'LBL_WOOCOMMERCE', 'create' => true],
			'fields' => ['woocommerce_server_id', 'woocommerce_id', 'alias', 'short_description', 'tags',
				'woocommerce_product_status', 'woocommerce_product_visibility', 'woocommerce_permalink'],
			'fieldsData' => ['woocommerce_server_id' => ['displaytype' => 1]],
		],
		'ProductCategory' => [
			'block' => ['name' => 'LBL_BASIC_INFORMATION', 'create' => false],
			'fields' => ['woocommerce_server_id', 'woocommerce_id', 'alias', 'description'],
			'fieldsData' => ['woocommerce_server_id' => ['displaytype' => 1]],
		],
		'SSingleOrders' => [
			'block' => ['name' => 'LBL_CUSTOM_INFORMATION', 'create' => false],
			'fields' => ['woocommerce_server_id', 'woocommerce_id'],
		],
	];

	/**
	 * Check if the functionality has been activated.
	 *
	 * @return bool
	 */
	public static function check(): bool
	{
		$condition = ['or'];
		$i = 0;
		foreach (self::FIELDS as $moduleName => $value) {
			foreach ($value['fields'] as $fieldName) {
				++$i;
				$condition[] = ['tabid' => \App\Module::getModuleId($moduleName), 'fieldname' => $fieldName];
			}
		}
		return \App\Db::getInstance('log')->isTableExists(\App\Integrations\WooCommerce::LOG_TABLE_NAME)
		&& \App\Db::getInstance('log')->isTableExists(\App\Integrations\WooCommerce::MAP_TABLE_NAME)
		&& \App\Db::getInstance('log')->isTableExists(\App\Integrations\WooCommerce::CONFIG_TABLE_NAME)
		&& $i === (new \App\Db\Query())->from('vtiger_field')->where($condition)->count()
		&& \App\EventHandler::checkActive('Products_DuplicateEan_Handler', 'EditViewPreSave')
		&& \App\EventHandler::checkActive('Products_UpdateModifiedTime_Handler', 'EntityAfterSave')
		&& \App\Cron::checkActive('Vtiger_WooCommerce_Cron');
	}

	/**
	 * Activate integration, requires creation of additional integration data.
	 *
	 * @return bool
	 */
	public static function activate(): int
	{
		$importerType = new \App\Db\Importers\Base();
		$fields = [
			'woocommerce_server_id' => [
				'columntype' => $importerType->integer(10)->defaultValue(0)->notNull()->unsigned(),
				'label' => 'FL_WOOCOMMERCE_SERVER',
				'uitype' => 332,
				'maximumlength' => '4294967295',
				'typeofdata' => 'I~O'
			],
			'woocommerce_id' => [
				'columntype' => $importerType->integer(10)->defaultValue(0)->notNull()->unsigned(),
				'label' => 'FL_WOOCOMMERCE_ID',
				'uitype' => 7, 'displaytype' => 9,
				'maximumlength' => '4294967295', 'typeofdata' => 'I~O'
			],
			'alias' => [
				'columntype' => $importerType->stringType(255)->defaultValue(''),
				'label' => 'FL_ALIAS',
				'uitype' => 1,
				'maximumlength' => '255', 'typeofdata' => 'V~O'
			],
			'description' => \App\Field::SYSTEM_FIELDS['description'],
			'short_description' => [
				'label' => 'FL_SHORT_DESCRIPTION',
				'uitype' => 300, 'typeofdata' => 'V~O',
				'columntype' => 'text', 'maximumlength' => '65535'
			],
			'tags' => [
				'columntype' => 'text', 'maximumlength' => '65535',
				'label' => 'FL_TAGS', 'uitype' => 18, 'typeofdata' => 'V~O', 'values' => []
			],
			'woocommerce_product_status' => [
				'columntype' => $importerType->stringType(255)->defaultValue(''), 'maximumlength' => '255',
				'label' => 'FL_WOOCOMMERCE_PRODUCT_STATUS', 'uitype' => 16, 'typeofdata' => 'V~O',
				'values' => ['FL_WOO_PUBLISH', 'FL_WOO_PENDING', 'FL_WOO_DRAFT']
			],
			'woocommerce_product_visibility' => [
				'columntype' => $importerType->stringType(255)->defaultValue(''), 'maximumlength' => '255',
				'label' => 'FL_WOOCOMMERCE_PRODUCT_VISIBILITY', 'uitype' => 16, 'typeofdata' => 'V~O',
				'values' => ['FL_WOO_VISIBLE', 'FL_WOO_CATALOG', 'FL_WOO_SEARCH', 'FL_WOO_HIDDEN']
			],
			'woocommerce_permalink' => [
				'columntype' => $importerType->stringType(255)->defaultValue(''), 'maximumlength' => '255',
				'label' => 'FL_WOOCOMMERCE_PERMALINK', 'uitype' => 17, 'typeofdata' => 'V~O',
			],
		];
		$i = 0;
		foreach (self::FIELDS as $moduleName => $value) {
			$fieldsExists = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')
				->where(['tabid' => \App\Module::getModuleId($moduleName), 'fieldname' => array_keys($fields)])->column();
			if ($fieldsToAdd = array_diff_key(array_intersect_key($fields, array_flip($value['fields'])), array_flip($fieldsExists))) {
				$blockModel = vtlib\Block::getInstance($value['block']['name'], $moduleName);
				if (!$blockModel) {
					if ($value['block']['create']) {
						$blockModel = new vtlib\Block();
						$blockModel->label = $value['block']['name'];
						vtlib\Module::getInstance($moduleName)->addBlock($blockModel);
					} else {
						$blocks = vtlib\Block::getAllForModule(vtlib\Module::getInstance($moduleName));
						$blockModel = current($blocks);
					}
				}
				foreach ($fieldsToAdd as $fieldName => &$fieldData) {
					if (isset($value['fieldsData'][$fieldName])) {
						$fieldData = array_merge($fieldData, $value['fieldsData'][$fieldName]);
					}
				}
				self::addFields($fieldsToAdd, $blockModel);
				$i += \count($fieldsToAdd);
			}
		}
		$dbLog = \App\Db::getInstance('log');
		if (!$dbLog->isTableExists(\App\Integrations\WooCommerce::LOG_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$dbLog->createTable(\App\Integrations\WooCommerce::LOG_TABLE_NAME, [
				'id' => $importer->primaryKeyUnsigned(),
				'time' => $importer->dateTime()->notNull(),
				'message' => $importer->stringType(255),
				'params' => $importer->text(),
				'trace' => $importer->text(),
			]);
			++$i;
		}
		$db = \App\Db::getInstance();
		if (!$db->isTableExists(\App\Integrations\WooCommerce::MAP_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable(\App\Integrations\WooCommerce::MAP_TABLE_NAME, [
				'map' => $importer->stringType(50)->notNull(),
				'class' => $importer->stringType(100)->notNull(),
			]);
			++$i;
		}
		if (!$db->isTableExists(\App\Integrations\WooCommerce::CONFIG_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable(\App\Integrations\WooCommerce::CONFIG_TABLE_NAME, [
				'server_id' => $importer->integer(10)->unsigned()->notNull(),
				'name' => $importer->stringType(50)->notNull(),
				'value' => $importer->stringType(50)->null(),
			]);
			$db->createCommand()
				->addForeignKey(
					'i_yf_woocommerce_config_ibfk_1',
					\App\Integrations\WooCommerce::CONFIG_TABLE_NAME,
					'server_id',
					'i_yf_woocommerce_servers',
					'id',
					'CASCADE',
					null
				)
				->execute();
			++$i;
		}
		\App\EventHandler::setActive('Products_DuplicateEan_Handler', 'EditViewPreSave');
		\App\EventHandler::setActive('Products_UpdateModifiedTime_Handler', 'EntityAfterSave');
		\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_WOOCOMMERCE');
		return $i;
	}

	/**
	 * Add fields.
	 *
	 * @param array       $fieldsToAdd
	 * @param vtlib\Block $blockModel
	 *
	 * @return void
	 */
	public static function addFields(array $fieldsToAdd, vtlib\Block $blockModel): void
	{
		foreach ($fieldsToAdd as $fieldName => $fieldData) {
			if (empty($fieldData['table'])) {
				$fieldData['table'] = $blockModel->module->basetable;
			}
			$fieldInstance = \Vtiger_Field_Model::init($blockModel->module->name, $fieldData, $fieldName);
			$fieldInstance->save($blockModel);
			if (isset($fieldData['values'])) {
				$fieldInstance->setNoRolePicklistValues($fieldData['values']);
			}
			if (isset($fieldData['referenceModule'])) {
				if (!\is_array($fieldData['referenceModule'])) {
					$moduleList[] = $fieldData['referenceModule'];
				} else {
					$moduleList = $fieldData['referenceModule'];
				}
				$fieldInstance->setRelatedModules($moduleList);
				foreach ($moduleList as $module) {
					$targetModule = vtlib\Module::getInstance($module);
					$targetModule->setRelatedList($blockModel->module, $blockModel->module->name, ['Add'], 'getDependentsList', $fieldName);
				}
			}
		}
	}
}
