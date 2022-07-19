<?php

/**
 * Check is inventory exists in related records file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Check is inventory exists in related records class.
 */
class Vtiger_InventoryDelete_Model extends \App\Base
{
	/** @var string Module name for delete */
	private $moduleNameForDelete;

	/** @var array Records where record for delete is set */
	private $recordsWhereInvIsSet = [];

	/** @var array Number of related records for display user */
	public $recordsLimit = 10;

	/** @var int Record for delete id */
	private $recordId;

	/** @var Vtiger_Inventory_Model Inventory model */
	private $inventoryModel;

	/** @var Vtiger_Module_Model Module model */
	private $moduleModel;

	/** @var string Content for display */
	private $recordsWhereInvIsHtml = '';

	/**
	 * Construct.
	 *
	 * @param string $moduleName
	 * @param int    $recordId
	 */
	public function __construct(string $moduleName, int $recordId)
	{
		$this->moduleNameForDelete = $moduleName;
		$this->recordId = $recordId;
	}

	/**
	 * Get records where deleted record is set.
	 *
	 * @return array
	 */
	public function getRecordsWhereRecordIsSet(): array
	{
		$this->recordsWhereInvIsHtml .= '<uL>';
		$allModules = \vtlib\Functions::getAllModules(false, true);
		foreach ($allModules as $moduleData) {
			$this->moduleModel = Vtiger_Module_Model::getInstance($moduleData['name']);
			if ($this->moduleModel->isInventory() && $this->ifModuleHasSetInvName()) {
				$this->setRelatedRecordsLabels();
			}
		}
		$this->recordsWhereInvIsHtml .= '</uL>';
		return $this->recordsWhereInvIsSet;
	}

	/**
	 * Check is inventory module has set inventory name .
	 *
	 * @return bool
	 */
	protected function ifModuleHasSetInvName(): bool
	{
		$this->inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleModel->getName());
		$invFieldTableName = $this->inventoryModel->getTableName();
		$invFieldParams = (new App\Db\Query())->select(['params'])->from($invFieldTableName)->where(['columnname' => 'name'])->scalar();
		return $invFieldParams && $this->checkIfModuleIsSetInInventory($invFieldParams);
	}

	/**
	 * Check is in module is set deleted inventory module.
	 *
	 * @param string $invFieldParams
	 *
	 * @return bool
	 */
	protected function checkIfModuleIsSetInInventory(string $invFieldParams): bool
	{
		$result = false;
		$invFieldParams = App\Json::decode($invFieldParams);
		if ($invFieldParams['modules']) {
			$inventoryModules = !\is_array($invFieldParams['modules']) ? [$invFieldParams['modules']] : $invFieldParams['modules'];
			$result = \in_array($this->moduleNameForDelete, $inventoryModules);
		}
		return $result;
	}

	/**
	 * Set labels for related records.
	 *
	 * @return void
	 */
	protected function setRelatedRecordsLabels(): void
	{
		$inventoryTable = $this->inventoryModel->getDataTableName();
		$records = (new App\Db\Query())->select(['crmid'])->from($inventoryTable)->where(['name' => $this->recordId])->distinct()->limit($this->recordsLimit)->column();
		foreach ($records as $recordId) {
			if (\count($this->recordsWhereInvIsSet) >= $this->recordsLimit) {
				break;
			}
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->moduleModel->getName());
			$recordLabel = App\Record::getLabel($recordId);
			$this->recordsWhereInvIsSet[$recordId] = $recordLabel;
			$this->recordsWhereInvIsHtml .= "<li><a href='{$recordModel->getDetailViewUrl()}'>{$recordLabel}</a></li>";
		}
	}

	/**
	 * Get related records labels.
	 *
	 * @return string
	 */
	public function getRelatedRecordsLabels(): string
	{
		return $this->recordsWhereInvIsHtml;
	}
}
