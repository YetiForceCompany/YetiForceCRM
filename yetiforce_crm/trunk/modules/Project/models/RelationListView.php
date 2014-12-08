<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Project_RelationListView_Model extends Vtiger_RelationListView_Model {

	public function getCreateViewUrl() {
		$createViewUrl = parent::getCreateViewUrl();
		
		$relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
		if($relationModuleModel->getName() == 'HelpDesk') {
			if($relationModuleModel->getField('parent_id')->isViewable()) {
				$createViewUrl .='&parent_id='.$this->getParentRecordModel()->get('linktoaccountscontacts');
			}
		}

		return $createViewUrl;
	}

}
