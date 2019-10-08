<?php
/**
 * RecordPopover model class for Calendar.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class Calendar_RecordPopover_Model.
 */
class Calendar_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getFields(): array
	{
		$summaryFields = [];
		$fieldsModel = $this->recordModel->getModule()->getFields();
		foreach ($this->getFieldsIcon() as $fieldName => $icon) {
			$fieldModel = $fieldsModel[$fieldName] ?? '';
			if ($fieldModel && $fieldModel->isViewableInDetailView() && !$this->recordModel->isEmpty($fieldName)) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		return $summaryFields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldsIcon(): array
	{
		return ['date_start' => 'far fa-clock', 'due_date' => 'far fa-clock', 'location' => 'fas fa-globe',
			'taskpriority' => 'fas fa-exclamation-circle', 'activitystatus' => 'fas fa-question-circle',
			'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'state' => 'far fa-star',
			'visibility' => 'fas fa-eye', 'assigned_user_id' => 'fas fa-user'];
	}
}
