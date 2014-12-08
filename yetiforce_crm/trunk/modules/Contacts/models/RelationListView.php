<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_RelationListView_Model extends Vtiger_RelationListView_Model {
    
    public function getCreateViewUrl(){
        $createViewUrl = parent::getCreateViewUrl();
		$relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();

        //if parent module has account id it should be related to Potentials
        if($parentRecordModule->get('parent_id') && $relationModuleModel->getName() == 'Potentials') {
            $createViewUrl .= '&related_to='.$parentRecordModule->get('parent_id');
        }
		return $createViewUrl;
	}
}