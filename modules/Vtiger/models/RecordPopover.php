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
	 * @param string               $moduleName
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return \Vtiger_RecordPopover_Model
	 */
	public static function getInstance(string $moduleName, Vtiger_Record_Model $recordModel): self
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'RecordPopover', $moduleName);
		$instance = new $modelClassName();
		$instance->setRecord($recordModel);
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
	 * Returns array with model of buttons.
	 *
	 * @return array
	 */
	public function getHeaderLinks(): array
	{
		return [];
	}

	/**
	 * Returns list of fields to display.
	 *
	 * @return array
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
	 * @return array
	 */
	public function getFieldsIcon(): array
	{
		return [];
	}
}
