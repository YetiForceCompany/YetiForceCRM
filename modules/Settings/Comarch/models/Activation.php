<?php
/**
 * Activation file for Comarch integration model.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

use App\Integrations\Comarch;

/**
 * Activation class for Comarch integration model.
 */
class Settings_Comarch_Activation_Model
{
	/** @var array Map relation table name */
	private const FIELDS = [
		'Accounts' => [
			'block' => ['name' => 'LBL_ADVANCED_BLOCK', 'create' => false],
			'fields' => [
				'comarch_server_id', 'comarch_id', 'account_short_name', 'account_second_name',
				'account_third_name', 'payment_methods'
			],
			'fieldsData' => ['comarch_server_id' => ['displaytype' => 1]],
		],
		'Products' => [
			'block' => ['name' => 'LBL_COMARCH', 'create' => true],
			'fields' => [
				'comarch_server_id', 'comarch_id',
			],
			'fieldsData' => ['comarch_server_id' => ['displaytype' => 1]],
		],
	];

	/**
	 * Get fields structure.
	 *
	 * @return array
	 */
	private static function getFieldsStructure(): array
	{
		$importerType = new \App\Db\Importers\Base();
		return [
			'comarch_server_id' => [
				'columntype' => $importerType->integer(10)->defaultValue(0)->notNull()->unsigned(),
				'label' => 'FL_COMARCH_SERVER',
				'uitype' => 334,
				'maximumlength' => '4294967295',
				'typeofdata' => 'I~O'
			],
			'comarch_id' => [
				'columntype' => $importerType->integer(10)->unsigned(),
				'label' => 'FL_COMARCH_ID',
				'uitype' => 7, 'displaytype' => 2,
				'maximumlength' => '4294967295', 'typeofdata' => 'I~O'
			],
			'account_short_name' => [
				'label' => 'FL_ACCOUNT_SHORT_NAME', 'columntype' => $importerType->stringType(255)->defaultValue(''),
				'uitype' => 1, 'maximumlength' => '255', 'typeofdata' => 'V~M'
			],
			'account_second_name' => [
				'label' => 'FL_ACCOUNT_SECOND_NAME', 'columntype' => $importerType->stringType(255)->defaultValue(''),
				'uitype' => 1, 'maximumlength' => '255', 'typeofdata' => 'V~O'
			],
			'account_third_name' => [
				'label' => 'FL_ACCOUNT_THIRD_NAME', 'columntype' => $importerType->stringType(255)->defaultValue(''),
				'uitype' => 1, 'maximumlength' => '255', 'typeofdata' => 'V~O'
			],
			'payment_methods' => [
				'label' => 'FL_PAYMENTS_METHOD', 'columntype' => $importerType->stringType(255)->defaultValue(''),
				'column' => 'accounts_formpayment', 'uitype' => 16, 'maximumlength' => '255', 'typeofdata' => 'V~O'
			],
		];
	}

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
		return \App\Db::getInstance('log')->isTableExists(Comarch::LOG_TABLE_NAME)
		&& \App\Db::getInstance('log')->isTableExists(Comarch::MAP_TABLE_NAME)
		&& \App\Db::getInstance('log')->isTableExists(Comarch::CONFIG_TABLE_NAME)
		&& $i === (new \App\Db\Query())->from('vtiger_field')->where($condition)->count()
		&& \App\EventHandler::checkActive('Products_DuplicateEan_Handler', 'EditViewPreSave')
		&& \App\Cron::checkActive('Vtiger_Comarch_Cron');
	}

	/**
	 * Activate integration, requires creation of additional integration data.
	 *
	 * @return bool
	 */
	public static function activate(): int
	{
		$fields = self::getFieldsStructure();
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
		if (!$dbLog->isTableExists(Comarch::LOG_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$dbLog->createTable(Comarch::LOG_TABLE_NAME, [
				'id' => $importer->primaryKeyUnsigned(),
				'server_id' => $importer->integer(10)->unsigned()->notNull(),
				'time' => $importer->dateTime()->notNull(),
				'error' => $importer->tinyInteger(1)->unsigned()->defaultValue(0),
				'message' => $importer->stringType(255),
				'params' => $importer->text(),
				'trace' => $importer->text(),
			]);
			++$i;
		}
		$db = \App\Db::getInstance();
		if (!$db->isTableExists(Comarch::MAP_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable(Comarch::MAP_TABLE_NAME, [
				'server_id' => $importer->integer(10)->unsigned()->notNull(),
				'map' => $importer->stringType(50)->notNull(),
				'class' => $importer->stringType(100)->notNull(),
			]);
			$db->createCommand()->addForeignKey(
				'i_yf_comarch_map_class_ibfk_1',
				Comarch::MAP_TABLE_NAME, 'server_id',
				'i_yf_comarch_servers', 'id',
				'CASCADE', null
			)->execute();
			++$i;
		}
		if (!$db->isTableExists(Comarch::CONFIG_TABLE_NAME)) {
			$importer = new \App\Db\Importers\Base();
			$db->createTable(Comarch::CONFIG_TABLE_NAME, [
				'server_id' => $importer->integer(10)->unsigned()->notNull(),
				'name' => $importer->stringType(50)->notNull(),
				'value' => $importer->stringType(50)->null(),
			]);
			$db->createCommand()->addForeignKey(
				'i_yf_comarch_config_ibfk_1',
				Comarch::CONFIG_TABLE_NAME, 'server_id',
				'i_yf_comarch_servers', 'id',
				'CASCADE', null
			)->execute();
			++$i;
		}
		\App\EventHandler::setActive('Products_DuplicateEan_Handler', 'EditViewPreSave');
		\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_COMARCH');
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
					$targetModule->setRelatedList(
						$blockModel->module,
						$blockModel->module->name,
						['Add'], 'getDependentsList', $fieldName
					);
				}
			}
		}
	}
}
