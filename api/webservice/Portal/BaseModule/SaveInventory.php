<?php
/**
 * The file contains a the SaveInventory class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
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
		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$fieldModelList = $recordModel->getModule()->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isWritable()) {
				continue;
			}
			if ($this->controller->request->has($fieldName)) {
				$fieldModel->getUITypeModel()->setValueFromRequest($this->controller->request, $recordModel);
			}
		}
		$moduleModel = $recordModel->getModule();
		if (\Api\Portal\Privilege::USER_PERMISSIONS !== $this->getPermissionType()) {
			$fields = $moduleModel->getReferenceFieldsForModule('Accounts');
			if ($fieldModel = current($fields)) {
				$recordModel->set($fieldModel->getFieldName(), $this->getParentCrmId());
			}
		}
		$fields = $moduleModel->getReferenceFieldsForModule('IStorages');
		if ($fieldModel = current($fields)) {
			$recordModel->set($fieldModel->getFieldName(), $this->getUserStorageId());
		}
		$fieldPermission = \Api\Core\Module::getApiFieldPermission($moduleName, (int) $this->controller->app['id']);
		if ($fieldPermission) {
			$recordModel->setDataForSave([$fieldPermission['tablename'] => [$fieldPermission['columnname'] => 1]]);
		}
		if ($this->controller->request->has('inventory')) {
			$inventory = new \Api\Portal\Inventory($moduleName, $this->controller->request->getArray('inventory'), $this->getUserStorageId());
			if (!$this->getCheckStockLevels() || $inventory->validate()) {
				$recordModel->initInventoryData(
					$inventory->getInventoryData(),
					false
				);
				$recordModel->save();
				$result = [
					'id' => $recordModel->getId(),
					'moduleName' => $moduleName,
				];
			} else {
				$result = [
					'errors' => $inventory->getErrors()
				];
			}
		} else {
			$result = [
				'errors' => [
					'record' => 'There are no inventory records'
				],
			];
		}

		return $result;
	}
}
