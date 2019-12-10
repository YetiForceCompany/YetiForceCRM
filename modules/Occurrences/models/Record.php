<?php

/**
 * Record Class for Occurrences.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
/**
* Class Occurrences record model
*/
class Occurrences_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Get the related list action for the record.
	 *
	 * @param Vtiger_RelationListView_Model $viewModel
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordRelatedListViewLinksLeftSide(Vtiger_RelationListView_Model $viewModel)
	{
		$relationModel = $viewModel->getRelationModel();
		$parentRecord = $relationModel->get('parentRecord');
		$links = parent::getRecordRelatedListViewLinksLeftSide($viewModel);
		if (\in_array($relationModel->get('name'), ['getRelatedContacts', 'getRelatedMembers']) && $parentRecord->isEditable() && $this->isEditable()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_CHANGE_RELATION_DATA',
				'dataUrl' => "index.php?module={$this->getModuleName()}&view=ChangeRelationData&record={$this->getId()}&fromRecord={$parentRecord->getId()}&relationId={$relationModel->getId()}",
				'linkicon' => 'mdi mdi-briefcase-edit-outline',
				'linkclass' => 'btn-sm btn-warning showModal'
			]);
		}
		return $links;
	}
}
