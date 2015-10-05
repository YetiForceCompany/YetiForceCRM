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

class CallHistory_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();
		$recordId = $recordModel->getId();

		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasiclinks = $linkModelListDetails['DETAILVIEWBASIC'];
		unset($linkModelListDetails['DETAILVIEWBASIC']);

		/*
		  if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId) && $recordPermissionToEditView) {
		  $deletelinkModel = array(
		  'linktype' => 'DETAILVIEW',
		  'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_'. $moduleName, $moduleName)),
		  'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("'.$recordModel->getDeleteUrl().'")',
		  'linkicon' => ''
		  );
		  $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		  }
		 */
		if (!empty($detailViewBasiclinks)) {
			foreach ($detailViewBasiclinks as $linkModel) {
				// Remove view history, needed in vtiger5 to see history but not in vtiger6
				if ($linkModel->linklabel == 'View History') {
					continue;
				}
				$linkModelList['DETAILVIEW'][] = $linkModel;
			}
		}

		return $linkModelList;
	}
}
