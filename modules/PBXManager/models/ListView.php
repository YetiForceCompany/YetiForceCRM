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
 * PBXManager ListView Model Class
 */
class PBXManager_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Overrided to remove add button 
	 */
	public function getBasicLinks()
	{
		return [];
	}

	/**
	 * Overrided to remove Mass Edit Option 
	 */
	public function getListViewMassActions($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
				'linkicon' => ''
			);

			foreach ($massActionLinks as $massActionLink) {
				$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
			}
		}

		return $links;
	}

	/**
	 * Overrided to add HTML content for callstatus irrespective of the filters
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->setField('direction');
		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if (empty($orderBy) && empty($sortOrder) && $this->getModule()->getName() != "Users") {
			$this->set('orderby', 'modifiedtime');
			$this->set('sortorder', 'DESC');
		}
		/*/Adding the HTML content based on the callstatus and direction to the records
		foreach ($listViewEntries as $recordId => $record) {
			//To Replace RecordingUrl by Icon
			$recordingUrl = explode('>', $listViewEntries[$recordId]['recordingurl']);
			$url = explode('<', $recordingUrl[1]);
			if ($url[0] != '' && $listViewEntries[$recordId]['callstatus'] == 'completed') {
				$listViewEntries[$recordId]['recordingurl'] = $recordingUrl[0] . '>' . '<i class="icon-volume-up"></i>' . '</a>';
			} else {
				$listViewEntries[$recordId]['recordingurl'] = '';
			}


			if ($listViewEntries[$recordId]['direction'] == 'outbound') {
				if ($listViewEntries[$recordId]['callstatus'] == 'ringing' || $listViewEntries[$recordId]['callstatus'] == 'in-progress') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-info"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'completed') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-success"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'no-answer') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-important"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-warning"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				}
			} else if ($listViewEntries[$recordId]['direction'] == 'inbound') {
				if ($listViewEntries[$recordId]['callstatus'] == 'ringing' || $listViewEntries[$recordId]['callstatus'] == 'in-progress') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-info"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'completed') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-success"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else if ($listViewEntries[$recordId]['callstatus'] == 'no-answer') {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-important"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				} else {
					$listViewEntries[$recordId]['callstatus'] = '<span class="label label-warning"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
				}
			}
		}
		/*/
		return parent::getListViewEntries($pagingModel);
	}
}
