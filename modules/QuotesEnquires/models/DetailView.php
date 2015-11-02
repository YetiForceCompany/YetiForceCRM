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

class QuotesEnquires_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$targetModuleModel = Vtiger_Module_Model::getInstance('RequirementCards');
		if ($currentUserModel->hasModuleActionPermission($targetModuleModel->getId(), 'EditView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkurl' => "index.php?module=" . $targetModuleModel->getName() . "&view=" . $targetModuleModel->getEditViewName() . "&reference_id=" . $recordModel->getId(),
				'linkicon' => 'glyphicon glyphicon-level-up',
				'linkclass' => 'btn-success',
				'linkhint' => vtranslate('LBL_GENERATE_REQUIREMENTCARDS', 'QuotesEnquires')
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}
}
