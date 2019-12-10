<?php

/**
 * Record Class for Occurrences.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Occurrences_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $storageId => $storageInfo) {
			preg_match('/<a href="+/', $storageInfo[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $storageInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i", $storageInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('SSalesProcesses');
				$recordModel->setId($storageId);
				$hierarchy['entries'][$storageId][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}

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
				'linkclass' => 'btn-sm btn-warning showModal js-pdf'
			]);
		}
		return $links;
	}
}
