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
		$linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
		$links = [];

		$quickLinks = [
			[
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_VIEW',
				'linkurl' => $this->getCalendarViewUrl(),
				'linkicon' => '',
			],
			[
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			],
		];
		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		if ($linkParams['ACTION'] == 'Calendar') {
			$quickWidgets = [];
			$quickWidgets[] = [
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_USERS',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getUsersList',
				'linkicon' => ''
			];
			$quickWidgets[] = [
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_TIMECONTROL_TYPE',
				'linkurl' => 'module=' . $this->get('name') . '&view=RightPanel&mode=getTypesList',
				'linkicon' => ''
			];
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
			$split = preg_split('/ FROM /i', $relationQuery);
			$mainQuery = '';
			for ($i = 1; $i < count($split); $i++) {
				$mainQuery = $mainQuery . ' FROM ' . $split[$i];
			}
		}

		// Calculate total working time
		$query = sprintf('SELECT SUM(vtiger_osstimecontrol.sum_time) AS sumtime %s', $mainQuery);
		$result = $db->query($query);
		$totalTime = $db->getSingleValue($result);

		// Calculate total working time divided into users
		$query = sprintf('SELECT SUM(vtiger_osstimecontrol.sum_time) AS sumtime, vtiger_crmentity.smownerid %s GROUP BY vtiger_crmentity.smownerid', $mainQuery);
		$result = $db->query($query);
		$userTime = [];
		$count = 1;
		while ($row = $db->fetch_array($result)) {
			$smownerid = vtlib\Functions::getOwnerRecordLabel($row['smownerid']);

			$userTime[] = [
				'name' => [$count, $smownerid],
				'initial' => [$count, vtlib\Functions::getInitials($smownerid)],
				'data' => [$count, $row['sumtime']]
			];
			$count++;
		}
		return ['totalTime' => $totalTime, 'userTime' => $userTime];
	}

	public function getTimeUsers($id, $moduleName)
	{
		$db = PearDatabase::getInstance();
		$fieldName = Vtiger_ModulesHierarchy_Model::getMappingRelatedField($moduleName);

		if (empty($id) || empty($fieldName))
			$response = false;
		else {
			$instance = CRMEntity::getInstance($this->getName());
			$securityParameter = $instance->getUserAccessConditionsQuerySR($this->getName(), false);
			$userSqlFullName = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

			$sql = sprintf('SELECT count(*) AS count, %s as name, vtiger_users.id as id, SUM(vtiger_osstimecontrol.sum_time) as time FROM vtiger_osstimecontrol
							INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
							INNER JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid && vtiger_users.status="ACTIVE"
							AND vtiger_crmentity.deleted = 0'
				. ' WHERE vtiger_osstimecontrol.%s = ? && vtiger_osstimecontrol.osstimecontrol_status = ? %s GROUP BY smownerid'
				, $userSqlFullName, $fieldName, $securityParameter);
			$result = $db->pquery($sql, [$id, OSSTimeControl_Record_Model::recalculateStatus]);

			$data = [];
			$i = 0;
			while ($row = $db->getRow($result)) {
				$data[$i]['label'] = $row['name'];
				$ticks[$i][0] = $i;
				$ticks[$i][1] = $row['name'];
				$data[$i]['data'][0][0] = $i;
				$data[$i]['data'][0][1] = $row['time'];
				++$i;
			}
			$response['ticks'] = $ticks;
			$response['chart'] = $data;
		}
		return $response;
	}
}
