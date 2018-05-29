<?php
/**
 * Project RelationListView Model.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Project_RelationListView_Model extends Vtiger_RelationListView_Model
{
	public function getCreateViewUrl()
	{
		$createViewUrl = parent::getCreateViewUrl();

		$relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
		if ($relationModuleModel->getName() == 'HelpDesk') {
			if ($relationModuleModel->getField('parent_id')->isViewable()) {
				$createViewUrl .= '&parent_id=' . $this->getParentRecordModel()->get('linktoaccountscontacts');
			}
		}

		return $createViewUrl;
	}
}
