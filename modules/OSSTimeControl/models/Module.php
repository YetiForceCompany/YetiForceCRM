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

	/**
	 * Function to get data of charts
	 * @param App\Db\Query $query
	 * @return array
	 */
	public function getRelatedSummary(App\Db\Query $query)
	{
	
		// Calculate total working time
		$totalTime = $query->limit(null)->orderBy('')->sum('vtiger_osstimecontrol.sum_time');

		// Calculate total working time divided into users
		$dataReader = $query->select(['sumtime' => new \yii\db\Expression('SUM(vtiger_osstimecontrol.sum_time)'), 'vtiger_crmentity.smownerid'])
				->groupBy('vtiger_crmentity.smownerid')
				->createCommand()->query();
		$userTime = [];
		$count = 1;
		while ($row = $dataReader->read()) {
			$smownerid = App\Fields\Owner::getLabel($row['smownerid']);
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
		$fieldName = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		if (empty($id) || empty($fieldName))
			$response = false;
		else {
			$query = (new \App\Db\Query())->select([
				'vtiger_crmentity.smownerid',
				'time' => new \yii\db\Expression('SUM(vtiger_osstimecontrol.sum_time)')
			])->from('vtiger_osstimecontrol')->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
					->where(['vtiger_crmentity.deleted' => 0, "vtiger_osstimecontrol.$fieldName" => $id, 'vtiger_osstimecontrol.osstimecontrol_status' => OSSTimeControl_Record_Model::recalculateStatus])
					->groupBy('smownerid');
			App\PrivilegeQuery::getConditions($query, $this->getName());
			$dataReader = $query->createCommand()->query();
			$data = [];
			$i = 0;
			while ($row = $dataReader->read()) {
				$name = App\Fields\Owner::getLabel($row['smownerid']);
				$data[$i]['label'] = $name;
				$ticks[$i][0] = $i;
				$ticks[$i][1] = $name;
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
