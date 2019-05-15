<?php
/**
 * The file contains a the SaveInventory class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Saving data to the inventory module.
 */
class SaveInventory extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['POST'];

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function post(): array
	{
		$moduleName = $this->controller->request->getModule();
		$inventory = $this->controller->request->getArray('inventory');
		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set('subject', $moduleName . '/' . date('Y-m-d'));
		$fields = \Vtiger_Module_Model::getInstance($moduleName)->getReferenceFieldsForModule('Accounts');
		if ($fields) {
			$recordModel->set(current($fields)->getFieldName(), $this->getParentCrmId());
		}
		$fieldPermission = \Api\Core\Module::getFieldPermission($moduleName, $this->controller->app['id']);
		if ($fieldPermission) {
			$recordModel->setDataForSave([$fieldPermission['tablename'] => [$fieldPermission['columnname'] => 1]]);
		}
		$recordModel->initInventoryData(
			(new \Api\Portal\BaseModel\SaveInventory($moduleName, $inventory))->getInventoryData(),
			false
		);
		$this->setAddressForRecordModel($recordModel);
		$recordModel->save();
		return [
			'id' => $recordModel->getId(),
			'moduleName' => $moduleName
		];
	}

	/**
	 * Set address for record model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	private function setAddressForRecordModel(\Vtiger_Record_Model $recordModel)
	{
		$address = $this->controller->request->getArray('address');
		$addressFields = [
			'addresslevel1',
			'addresslevel2',
			'addresslevel3',
			'addresslevel4',
			'addresslevel5',
			'addresslevel6',
			'addresslevel7',
			'addresslevel8',
			'localnumber',
			'buildingnumber',
			'pobox'
		];
		foreach ($addressFields as $fieldName) {
			if (isset($address[$fieldName])) {
				$recordModel->set("{$fieldName}a", $address[$fieldName]);
			}
		}
	}
}
