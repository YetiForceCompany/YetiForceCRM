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

class Vtiger_History_Widget extends Vtiger_Basic_Widget
{

	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=getHistory&page=1&limit=' . $this->Data['limit'];
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'History.tpl';
		$this->Config['url'] = $this->getUrl();
		$widget = $this->Config;
		return $widget;
	}
	
	public function getHistory(Vtiger_Request $request, Vtiger_Paging_Model $pagingModel)
	{
		$recordId = $request->get('record');
		$pageNumber = $request->get('page');
		$pageLimit = $request->get('limit');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		if (empty($pageLimit)) {
			$pageLimit = 10;
		}
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM vtiger_activity 
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid 
				WHERE vtiger_activity.link = ? LIMIT ?';
		$results = $db->pquery($query, [$recordId, $pageLimit]);
		$history = [];
		while ($row = $db->getRow($results)) {
			$row['userModel'] = Users_Privileges_Model::getInstanceById($row['smownerid']);
			$history[] = $row;
		}
		return $history;
	}
}
