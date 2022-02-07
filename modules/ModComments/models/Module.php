<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class ModComments_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get the Quick Links for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);

		return $links;
	}

	/**
	 * Function to get the create url with parent id set.
	 *
	 * @param <type> $parentRecord - parent record for which comment need to be added
	 *
	 * @return string Url
	 */
	public function getCreateRecordUrlWithParent($parentRecord)
	{
		return $this->getCreateRecordUrl() . '&parent_id=' . $parentRecord->getId();
	}

	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => 'yfi yfi-workflows-2',
			];
		}
		return $settingsLinks;
	}

	/**
	 * Delete comments associated with record.
	 *
	 * @param int $recordId
	 */
	public static function deleteForRecord(int $recordId)
	{
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$dataReader = $queryGenerator->setFields(['id'])->setStateCondition('All')->addNativeCondition(['related_to' => $recordId])->createQuery()->createCommand()->query();
		while ($id = $dataReader->readColumn(0)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, $queryGenerator->getModule());
			$recordModel->delete();
			unset($recordModel);
		}
	}
}
