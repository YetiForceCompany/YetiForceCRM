<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class OSSOutsourcedServices_ListView_Model extends Vtiger_ListView_Model {
	/*
	 * Function to get Setting links
	 * @return array of setting links
	 */
	public function getBasicLinks() {
		$basicLinks = parent::getBasicLinks();
		$createPermission = Users_Privileges_Model::isPermitted('Potentials', 'EditView');
		if($createPermission) {
			$basicLinks[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_GENERATE_EXTENSION',
				'linkurl' => 'javascript:Vtiger_List_Js.generatePotentials()',
				'linkicon' => 'icon-star-empty',
				'linkclass' => 'btn-success',
			);
		}
		return $basicLinks;
	}
}
