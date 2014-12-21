<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_PBXManager_Module_Model extends Settings_Vtiger_Module_Model{
    
    /**
	 * Function to get the module model
	 * @return string
	 */
    public static function getCleanInstance(){
        return new self;
    }
    
    /**
	 * Function to get the ListView Component Name
	 * @return string
	 */
    public function getDefaultViewName() {
		return 'Index';
	}
    
	/**
	 * Function to get the EditView Component Name
	 * @return string
	 */
	public function getEditViewName(){
		return 'Edit';
	}
    
    /**
	 * Function to get the Module Name
	 * @return string
	 */
    public function getModuleName(){
        return "PBXManager";
    }
    
     public function getParentName() {
        return parent::getParentName();
    }
    
    public function getModule($raw=true) {
		$moduleName = Settings_PBXManager_Module_Model::getModuleName();
		if(!$raw) {
			$parentModule = Settings_PBXManager_Module_Model::getParentName();
			if(!empty($parentModule)) {
				$moduleName = $parentModule.':'.$moduleName;
			}
		}
		return $moduleName;
	}
    
    public function getMenuItem() {
        $menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_PBXMANAGER');
        return $menuItem;
    }
    
    /**
    * Function to get the url for default view of the module
    * @return <string> - url
    */
    public function getDefaultUrl() {
            return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$this->getDefaultViewName();
    }

    public function getDetailViewUrl() {
        $menuItem = $this->getMenuItem();
        return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$this->getDefaultViewName().'&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
    }


   /**
    * Function to get the url for Edit view of the module
    * @return <string> - url
    */
    public function getEditViewUrl() {
            $menuItem = $this->getMenuItem();
            return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$this->getEditViewName().'&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
    }
    
}
