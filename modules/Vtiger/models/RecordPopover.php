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
	 * Model of record which is display.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $recordModel;

	/**
	 * Name of module.
	 *
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
		$fields = $this->recordModel->getModule()->getFields();
		foreach ($fields as $fieldName => $fieldModel) {
			if (!$this->recordModel->isEmpty($fieldName) && $fieldModel->isSummaryField() && $fieldModel->isViewableInDetailView()) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		if (!$summaryFields) {
			foreach ($this->recordModel->getEntity()->list_fields_name as $fieldLabel => $fieldName) {
				$fieldModel = $fields[$fieldName] ?? '';
				if ($fieldModel && !$this->recordModel->isEmpty($fieldName) && $fieldModel->isViewableInDetailView()) {
					$summaryFields[$fieldName] = $fieldModel;
				}
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
