<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * PBXManager ListView Model Class.
 */
class PBXManager_ListView_Model extends Vtiger_ListView_Model
{
	/**
	 * Overrided to remove add button.
	 */
	public function getBasicLinks()
	{
		return [];
	}

	/**
	 * Overrided to remove Mass Edit Option.
	 */
	public function getListViewMassActions($linkParams)
	{
		$moduleModel = $this->getModule();
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);
		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassActive')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ACTIVATE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Active&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-undo-alt',
			];
		}
		if ($moduleModel->isPermitted('MassArchived')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ARCHIVE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Archived&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-archive',
			];
		}
		if ($moduleModel->isPermitted('MassTrash')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_MOVE_TO_TRASH',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Trash&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-trash-alt',
			];
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassDelete&sourceView=List',
				'linkclass' => 'massRecordEvent',
				'linkicon' => 'fas fa-eraser',
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Overrided to add HTML content for callstatus irrespective of the filters.
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->setField('direction');
		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if (empty($orderBy) && empty($sortOrder) && $this->getModule()->getName() != 'Users') {
			$this->set('orderby', 'modifiedtime');
			$this->set('sortorder', 'DESC');
		}

		return parent::getListViewEntries($pagingModel);
	}
}
