<?php
/**
 * RecordPopover model Class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class Vtiger_RecordPopover_Model.
 */
class Vtiger_RecordPopover_Model extends \App\Base
{
	/**
	 * @var Vtiger_Record_Model
	 */
	protected $recordModel;

	/**
	 * @var string
	 */
	public $moduleName;

	/**
	 * Function to get model of view "RecordPopover".
	 *
	 * @param string $moduleName
	 * @param int    $recordId
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_RecordPopover_Model
	 */
	public static function getInstance(string $moduleName, int $recordId): self
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'RecordPopover', $moduleName);
		$instance = new $modelClassName();
		$instance->setRecord(Vtiger_Record_Model::getInstanceById($recordId, $moduleName));
		$instance->moduleName = $moduleName;
		return $instance;
	}

	/**
	 * Sets model of record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public function setRecord(Vtiger_Record_Model $recordModel)
	{
		$this->recordModel = $recordModel;
	}

	/**
	 * Gets model of record.
	 *
	 * @return \Vtiger_Record_Model
	 */
	public function getRecord(): Vtiger_Record_Model
	{
		return $this->recordModel;
	}

	/**
	 * Returns array with model of buttons.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getHeaderLinks(): array
	{
		return [];
	}

	/**
	 * Returns list of fields to display.
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getFields(): array
	{
		$summaryFields = [];
		foreach ($this->recordModel->getModule()->getFields() as $fieldName => &$fieldModel) {
			if ($fieldModel->isSummaryField() && $fieldModel->isViewableInDetailView() && !$this->recordModel->isEmpty($fieldName)) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		return $summaryFields;
	}

	/**
	 * Returns icons for fields.
	 *
	 * @return array
	 */
	public function getFieldsIcon(): array
	{
		return [];
	}
}
