<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSTimeControl_Module_Model extends Vtiger_Module_Model
{

	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=Calendar';
	}

	public function getSideBarLinks($linkParams)
	{
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = array();

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_VIEW',
				'linkurl' => $this->getCalendarViewUrl(),
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		if ($linkParams['ACTION'] == 'Calendar') {
			$quickWidgets = array();
			$quickWidgets[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_USERS',
				'linkurl' => 'module=' . $this->get('name') . '&view=UsersList&mode=getUsersList',
				'linkicon' => ''
			);
			foreach ($quickWidgets as $quickWidget) {
				$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
			}
		}

		return $links;
	}

	/**
	 * Function to get the Default View Component Name
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return 'Calendar';
	}

	public function getRelatedSummary($query)
	{
		$db = PearDatabase::getInstance();
		$relationQuery = preg_replace("/[ \t\n\r]+/", " ", $query);
		$position = stripos($relationQuery, ' from ');
		if ($position) {
			$split = explode(' FROM ', $relationQuery);
			$mainQuery = '';
			for ($i = 1; $i < count($split); $i++) {
				$mainQuery = $mainQuery . ' FROM ' . $split[$i];
			}
		}

		// Calculate total working time
		$result = $db->query('SELECT SUM(vtiger_osstimecontrol.sum_time) AS sumtime' . $mainQuery);
		$totalTime = $db->getSingleValue($result);

		// Calculate total working time divided into users
		$result = $db->query('SELECT SUM(vtiger_osstimecontrol.sum_time) AS sumtime, vtiger_crmentity.smownerid' . $mainQuery . ' GROUP BY vtiger_crmentity.smownerid');
		$userTime = [];
		$count = 1;
		while ($row = $db->fetch_array($result)) {
			$smownerid = Vtiger_Functions::getOwnerRecordLabel($row['smownerid']);

			$userTime[] = [
				'name' => [$count, $smownerid],
				'initial' => [$count, Vtiger_Functions::getInitials($smownerid)],
				'data' => [$count, $row['sumtime']]
			];
			$count++;
		}
		return ['totalTime' => $totalTime, 'userTime' => $userTime];
	}
}
