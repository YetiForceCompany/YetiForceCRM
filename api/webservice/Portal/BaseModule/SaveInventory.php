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
	 * Module name.
	 *
	 * @var string
	 */
	private $moduleName;

	/**
	 * Module model.
	 *
	 * @var \Vtiger_Module_Model
	 */
	private $moduleModel;

	/**
	 * Record modle.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private $recordModel;

	/**
	 * Inventory object.
	 *
	 * @var \Api\Portal\Inventory
	 */
	private $inventory;

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function post(): array
	{
		$result = $this->checkBeforeSave();
		if (empty($result)) {
			foreach ($this->moduleModel->getFields() as $fieldName => $fieldModel) {
				if (!$fieldModel->isWritable()) {
					continue;
				}
				if ($this->controller->request->has($fieldName)) {
					$fieldModel->getUITypeModel()->setValueFromRequest($this->controller->request, $this->recordModel);
				}
			}
			$parentRecordId = $this->getParentCrmId();
			if (\Api\Portal\Privilege::USER_PERMISSIONS !== $this->getPermissionType()) {
				$fieldModel = current($this->moduleModel->getReferenceFieldsForModule('Accounts'));
				if ($fieldModel) {
					$this->recordModel->set($fieldModel->getFieldName(), $parentRecordId);
				}
			}
			$fieldModel = current($this->moduleModel->getReferenceFieldsForModule('IStorages'));
			if ($fieldModel) {
				$this->recordModel->set($fieldModel->getFieldName(), $this->getUserStorageId());
			}
			$fieldPermission = \Api\Core\Module::getApiFieldPermission($this->moduleName, (int) $this->controller->app['id']);
			if ($fieldPermission) {
				$this->recordModel->setDataForSave([$fieldPermission['tablename'] => [$fieldPermission['columnname'] => 1]]);
			}
			$inventoryData = [];
			if ($this->controller->request->has('reference_id') && $this->controller->request->has('reference_module')) {
				$inventoryData = $this->inventory->getInventoryFromRecord($this->controller->request->getInteger('reference_id'), $this->controller->request->getByType('reference_module', 'Alnum'));
			} else {
				$inventoryData = $this->inventory->getInventoryData();
			}
			$this->recordModel->initInventoryData($inventoryData, false);
			if (!empty($parentRecordId)) {
				$parentRecordModel = \Vtiger_Record_Model::getInstanceById($parentRecordId, 'Accounts');
				$creditLimitId = $parentRecordModel->get('creditlimit');
				if (!empty($creditLimitId)) {
					$grossFieldModel = \Vtiger_Inventory_Model::getInstance($this->moduleName)->getField('gross');
					$limits = \Vtiger_InventoryLimit_UIType::getLimits();
					if ($grossFieldModel && $grossFieldModel->getSummaryValuesFromData($inventoryData) > (($limits[$creditLimitId]['value'] ?? 0) - $parentRecordModel->get('sum_open_orders'))) {
						return [
							'errors' => [
								'limit' => 'Merchant limit was exceeded'
							],
						];
					}
				}
			}
			$this->recordModel->save();
			$result = [
				'id' => $this->recordModel->getId(),
				'moduleName' => $this->moduleName,
			];
		}
		return $result;
	}

	/**
	 * Check the request before the save.
	 *
	 * @return array
	 */
	private function checkBeforeSave(): array
	{
		$this->moduleName = $this->controller->request->getModule();
		if (!$this->controller->request->has('inventory')) {
			return [
				'errors' => [
					'record' => 'There are no inventory records'
				],
			];
		}
		$this->recordModel = \Vtiger_Record_Model::getCleanInstance($this->moduleName);
		$this->moduleModel = $this->recordModel->getModule();
		if (!$this->moduleModel->isInventory()) {
			return [
				'errors' => [
					'record' => 'This is not an inventory module'
				],
			];
		}
		$this->inventory = new \Api\Portal\Inventory($this->moduleName, $this->controller->request->getArray('inventory'), $this->getUserStorageId(), $this->getParentCrmId());
		if ($this->getCheckStockLevels() && !$this->inventory->validate()) {
			return [
				'errors' => $this->inventory->getErrors()
			];
		}
		return [];
	}
}
