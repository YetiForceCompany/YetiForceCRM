<?php
/**
 * Settings WooCommerce module model file.
 *
 * @package   Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings WooCommerce module model class.
 */
class Settings_WooCommerce_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'WooCommerce';
	/** {@inheritdoc} */
	public $baseTable = 'i_#__woocommerce_servers';
	/** {@inheritdoc} */
	public $baseIndex = 'id';
	/** {@inheritdoc} */
	public $listFields = [
		'name' => 'LBL_NAME',
		'status' => 'LBL_STATUS',
		'url' => 'LBL_URL',
		'user_name' => 'LBL_USER_NAME',
	];
	/** @var array[] Field form array. */
	public static $formFields = [
		'status' => ['required' => 0, 'purifyType' => 'Integer'],
		'name' => ['required' => 1, 'purifyType' => 'Text'],
		'url' => ['required' => 1, 'purifyType' => 'Url'],
		'user_name' => ['required' => 1, 'default' => '', 'purifyType' => 'Text'],
		'password' => ['required' => 1, 'default' => '', 'purifyType' => ''],
		'connector' => ['required' => 1, 'default' => 'HttpAuth', 'purifyType' => 'Standard'],
		'verify_ssl' => ['required' => 1, 'default' => 1, 'purifyType' => 'Integer'],
		'master' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_currency' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'sync_categories' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'direction_categories' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_tags' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'direction_tags' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_products' => ['required' => 1, 'default' => true, 'purifyType' => 'Integer'],
		'direction_products' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'sync_orders' => ['required' => 1, 'default' => true, 'tooltip' => true, 'purifyType' => 'Integer'],
		'direction_orders' => ['required' => 1, 'default' => 0, 'purifyType' => 'Integer'],
		'shipping_service_id' => ['required' => 0, 'default' => 0, 'tooltip' => true, 'min' => 0, 'purifyType' => 'Integer'],
		'products_limit' => ['required' => 1, 'default' => 100, 'min' => 1, 'purifyType' => 'Text'],
		'orders_limit' => ['required' => 1, 'default' => 50, 'min' => 1, 'purifyType' => 'Text'],
		'product_map_class' => ['required' => 0, 'default' => '', 'tooltip' => true, 'purifyType' => 'ClassName'],
		'order_map_class' => ['required' => 0, 'default' => '', 'tooltip' => true, 'purifyType' => 'ClassName'],
	];
	/** @var array Fields. */
	const NEW_FIELDS = [
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

	/** {@inheritdoc} */
	public function getDefaultUrl()
	{
		return 'index.php?parent=Settings&module=WooCommerce&view=List';
	}

	/** {@inheritdoc} */
	public function getCreateRecordUrl()
	{
		return 'index.php?parent=Settings&module=WooCommerce&view=Edit';
	}

	/**
	 * Return list fields in form.
	 *
	 * @return array[]
	 */
	public function getFormFields(): array
	{
		return static::$formFields;
	}

	/**
	 * Function to check if the functionality is enabled.
	 *
	 * @return bool
	 */
	public static function isActive(): bool
	{
		$condition = ['or'];
		$i = 0;
		foreach (self::NEW_FIELDS as $moduleName => $value) {
			foreach ($value['fields'] as $fieldName) {
				++$i;
				$condition[] = ['tabid' => \App\Module::getModuleId($moduleName), 'fieldname' => $fieldName];
			}
		}
		return \App\Db::getInstance('log')->isTableExists(\App\Integrations\WooCommerce\Config::LOG_TABLE_NAME)
		&& $i === (new \App\Db\Query())->from('vtiger_field')->where($condition)->count()
		&& \App\EventHandler::checkActive('Products_DuplicateEan_Handler', 'EntityAfterSave')
		&& \App\EventHandler::checkActive('Products_UpdateModifiedTime_Handler', 'EntityAfterSave')
		&& \App\Cron::checkActive('Vtiger_WooCommerce_Cron');
	}

	/**
	 * Activate functionality.
	 *
	 * @return int
	 */
	public static function active(): int
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
		foreach (self::NEW_FIELDS as $moduleName => $value) {
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
		if (!$dbLog->isTableExists(\App\Integrations\WooCommerce\Config::LOG_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$dbLog->createTable(\App\Integrations\WooCommerce\Config::LOG_TABLE_NAME, [
				'id' => $importer->primaryKeyUnsigned(),
				'time' => $importer->dateTime()->notNull(),
				'category' => $importer->stringType(100),
				'message' => $importer->stringType(255),
				'params' => $importer->text(),
				'trace' => $importer->text(),
			]);
			++$i;
		}
		\App\EventHandler::setActive('Products_DuplicateEan_Handler', 'EntityAfterSave');
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
		}
	}
}
