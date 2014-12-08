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
class OSSCosts_Module_Model extends Inventory_Module_Model{
	var $modules_fields_ids = Array('Potentials' => 'potentialid','HelpDesk' => 'ticketid','Project' => 'projectid');
	var $widget_no_rows = 5;
	
	public function getSettingLinks() {
		$settingsLinks = parent::getSettingLinks();
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_MODULE_CONFIGURATION',
			'linkurl' => 'index.php?module=OSSCosts&parent=Settings&view=index',
			'linkicon' => ''
		);
		return $settingsLinks;
	}
}
?>
