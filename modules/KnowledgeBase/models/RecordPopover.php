<?php
/**
 * RecordPopover model class for Knowledge Base.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class KnowledgeBase_RecordPopover_Model.
 */
class KnowledgeBase_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
	/** {@inheritdoc} */
	public function getFields(): array
	{
		$summaryFields = [];
		$fields = $this->recordModel->getModule()->getFields();
		foreach ($this->recordModel->getEntity()->list_fields_name as $fieldLabel => $fieldName) {
			$fieldModel = $fields[$fieldName] ?? '';
			if ($fieldModel && !$this->recordModel->isEmpty($fieldName) && $fieldModel->isViewableInDetailView()) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		return $summaryFields;
	}
}
