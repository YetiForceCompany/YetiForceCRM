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
class Vtiger_Delete_Model
{
	/** @var string Module name for delete */
	private $moduleNameForDelete;

	/** @var array Number of related records for display */
	private $recordsLimit = 10;

	/** @var int record model */
	private $recordId;

	/** @var Vtiger_Inventory_Model Related inventory model */
	private $relatedInventoryModel;

	/** @var Vtiger_Module_Model Related module model */
	private $relatedModuleModel;

	/** @var string[] Related records label */
	private $relatedRecordsLabel = [];

	/**
	 * Construct.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function __construct(Vtiger_Record_Model $recordModel)
	{
		$this->moduleNameForDelete = $recordModel->getModuleName();
		$this->recordId = $recordModel->getId();
	}

	/**
	 * Set related record labels with deleted record.
	 *
	 * @return void
	 */
	public function setRecordsWhereDeleteRecordIsSet(): void
	{
		$allModules = \vtlib\Functions::getAllModules(false, true);
		foreach ($allModules as $moduleData) {
			if (\count($this->relatedRecordsLabel) >= $this->recordsLimit) {
				break;
			}
			$this->relatedModuleModel = Vtiger_Module_Model::getInstance($moduleData['name']);
			if ($this->relatedModuleModel->isInventory() && $this->hasRelationWithDeletedRecord()) {
				$this->setRelatedRecordsLabels();
			}
		}
	}

	/**
	 * Check is has relation with deleted record.
	 *
	 * @return bool
	 */
	protected function hasRelationWithDeletedRecord(): bool
	{
		$this->relatedInventoryModel = Vtiger_Inventory_Model::getInstance($this->relatedModuleModel->getName());
		$invFieldTableName = $this->relatedInventoryModel->getTableName();
		$inventoryParams = (new App\Db\Query())->select(['params'])->from($invFieldTableName)->where(['columnname' => 'name'])->scalar();
		return $inventoryParams && $this->checkIfModuleIsSetInInventory($inventoryParams);
	}

	/**
	 * Check is in module is set deleted inventory module.
	 *
	 * @param string $inventoryParams
	 *
	 * @return bool
	 */
	protected function checkIfModuleIsSetInInventory(string $inventoryParams): bool
	{
		$result = false;
		$inventoryParams = App\Json::decode($inventoryParams);
		if ($inventoryParams['modules']) {
			$inventoryModules = !\is_array($inventoryParams['modules']) ? [$inventoryParams['modules']] : $inventoryParams['modules'];
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
		$inventoryTable = $this->relatedInventoryModel->getDataTableName();
		$records = (new App\Db\Query())->select(['crmid'])->from($inventoryTable)->where(['name' => $this->recordId])->distinct()->limit($this->recordsLimit)->column();
		foreach ($records as $recordId) {
			if (\count($this->relatedRecordsLabel) >= $this->recordsLimit) {
				break;
			}
			$this->relatedRecordsLabel[] = App\Record::getLabel($recordId);
		}
	}

	/**
	 * Check is related record exists.
	 *
	 * @return bool
	 */
	public function isRelatedRecordExists(): bool
	{
		return \count($this->relatedRecordsLabel) > 0;
	}

	/**
	 * Get related records display value.
	 *
	 * @return string
	 */
	public function getRelatedRecordsDisplayValue(): string
	{
		$value = '<ul>';
		$value .= '<li>' . implode('</li><li>', $this->relatedRecordsLabel) . '</li>';
		return $value .= '</ul>';
	}
}
