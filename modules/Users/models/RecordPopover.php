<?php
/**
 * RecordPopover model class for Users.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class Users_RecordPopover_Model.
 */
class Users_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getFields(): array
	{
		$summaryFields = [];
		$fields = $this->recordModel->getModule()->getFields();
		foreach (['first_name', 'last_name', 'roleid', 'email1'] as $fieldName) {
			$fieldModel = $fields[$fieldName];
			if ($fieldModel && !$this->recordModel->isEmpty($fieldName) && $fieldModel->isViewableInDetailView()) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		return $summaryFields;
	}
}
