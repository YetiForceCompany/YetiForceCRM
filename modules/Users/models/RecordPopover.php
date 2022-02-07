<?php
/**
 * RecordPopover model class for Users.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class Users_RecordPopover_Model.
 */
class Users_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
	/** {@inheritdoc} */
	public function getFields(): array
	{
		$summaryFields = [];
		$fields = $this->recordModel->getModule()->getFields();
		foreach (['first_name', 'last_name', 'roleid', 'email1', 'primary_phone'] as $fieldName) {
			$fieldModel = $fields[$fieldName];
			if ($fieldModel && !$this->recordModel->isEmpty($fieldName) && $fieldModel->isViewableInDetailView()) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		return $summaryFields;
	}

	/** {@inheritdoc} */
	public function getHeaderLinks(): array
	{
		$links = [];
		$detailUrl = $this->recordModel->getFullDetailViewUrl();
		$editUrl = $this->recordModel->isEditable() ? $this->recordModel->getEditViewUrl() : '';
		if (\App\User::getCurrentUserModel()->isAdmin() && $this->recordModel->isEditable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'LBL_EDIT',
				'linkhref' => true,
				'linkurl' => $editUrl,
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn-sm btn-outline-secondary',
			];
		}
		if ($this->recordModel->isViewable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'DetailView',
				'linkhref' => true,
				'linkurl' => $detailUrl,
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-outline-secondary',
			];
		}
		$linksModels = [];
		foreach ($links as $link) {
			$linksModels[] = Vtiger_Link_Model::getInstanceFromValues($link);
		}
		return $linksModels;
	}
}
