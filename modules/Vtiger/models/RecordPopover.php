<?php
/**
 * RecordPopover model Class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$links = [];
		$detailUrl = $this->recordModel->getFullDetailViewUrl();
		$editUrl = $this->recordModel->isEditable() ? $this->recordModel->getEditViewUrl() : '';
		if ($this->recordModel->isEditable()) {
			if ($this->recordModel->getModule()->isQuickCreateSupported()) {
				$links[] = [
					'linktype' => 'RECORD_POPOVER_VIEW',
					'linklabel' => 'LBL_QUICK_EDIT',
					'linkicon' => 'yfi yfi-quick-creation',
					'linkclass' => 'btn-sm btn-outline-primary js-quick-edit-modal',
					'linkdata' => [
						'module' => $this->recordModel->getModuleName(),
						'record' => $this->recordModel->getId(),
					],
				];
			}
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'LBL_EDIT',
				'linkhref' => true,
				'linkurl' => $editUrl,
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn-sm btn-outline-secondary js-calendar-popover__button',
			];
		}
		if ($this->recordModel->isViewable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'DetailView',
				'linkhref' => true,
				'linkurl' => $detailUrl,
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-outline-secondary js-calendar-popover__button',
			];
		}
		$linksModels = [];
		foreach ($links as $link) {
			$linksModels[] = Vtiger_Link_Model::getInstanceFromValues($link);
		}
		return $linksModels;
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
