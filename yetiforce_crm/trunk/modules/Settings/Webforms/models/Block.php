<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Webforms_Block_Model extends Vtiger_Block_Model {

	/**
	 * Function to get fields for this block
	 * @return <Array> list of Field models list <Settings_Webforms_Field_Model>
	 */
	public function getFields() {
		if(empty($this->fields)) {
			$tableName = 'vtiger_webforms';
			$tabId = getTabid('Webforms');
            $blockName = $this->get('name');
            switch ($blockName) {
                case 'LBL_WEBFORM_INFORMATION' : 
                            $fieldsList = array(
                            'name' => array(
                                    'uitype' => '1',
                                    'name' => 'name',
                                    'label' => 'Webform Name',
                                    'typeofdata' => 'V~M',
                                    'diplaytype' => '1',
                            ),
                            'targetmodule' => array(
                                    'uitype' => '16',
                                    'name' => 'targetmodule',
                                    'label' => 'Module',
                                    'typeofdata' => 'V~O',
                                    'diplaytype' => '1',
                            ),
                            'returnurl' => array(
                                    'uitype' => '17',
                                    'name' => 'returnurl',
                                    'label' => 'Return Url',
                                    'typeofdata' => 'V~O',
                                    'diplaytype' => '1',
                                    'defaultvalue' => '',
                            ),
                            'ownerid' => array(
                                    'uitype' => '53',
                                    'name' => 'ownerid',
                                    'label' => 'Assigned To',
                                    'typeofdata' => 'V~M',
                                    'diplaytype' => '1',
                            ),
                            'posturl' => array(
                                    'uitype' => '1',
                                    'name' => 'posturl',
                                    'label' => 'Post Url',
                                    'typeofdata' => 'V~O',
                                    'diplaytype' => '5',
                            ),
                            'publicid' => array(
                                    'uitype' => '1',
                                    'name' => 'publicid',
                                    'label' => 'Public Id',
                                    'typeofdata' => 'V~O',
                                    'diplaytype' => '5',
                            ),
                            'enabled' => array(
                                    'uitype' => '56',
                                    'name' => 'enabled',
                                    'label' => 'Status',
                                    'typeofdata' => 'C~O',
                                    'diplaytype' => '1',
                                    'defaultvalue' => '1',
                            ),
                    'captcha' => array(
							'uitype' => '56',
							'name' => 'captcha',
							'label' => 'Captcha Enabled',
							'typeofdata' => 'C~O',
							'diplaytype' => '1',
							'defaultvalue' => '0',
					),
                            'description' => array(
                                    'uitype' => '19',
                                    'name' => 'description',
                                    'label' => 'Description',
                                    'typeofdata' => 'V~O',
                                    'defaultvalue' => '',
                            )
                    );
                    break;
                
                case 'LBL_ASSIGN_USERS' : 
                    $fieldsList = array(
                            'roundrobin' =>array(
                                    'uitype' => '56',
                                    'name' => 'roundrobin',
                                    'label' => 'LBL_ASSIGN_ROUND_ROBIN',
                                    'typeofdata' => 'C~O',
                                    'diplaytype' => '1',
                                    'defaultvalue' => '0',
                            ),
                            'roundrobin_userid' =>array(
                                    'uitype' => '54',	                                                        
                                    'name' => 'roundrobin_userid',
                                    'label' => 'LBL_ROUNDROBIN_USERS_LIST',
                                    'typeofdata' => 'V~M',
                                    'diplaytype' => '1',
                                    'defaultvalue' => 'NULL',
                            )
                    );
                    break;
            }
			
			foreach ($fieldsList as $fieldName => $fieldDetails) {
				$fieldModel = Settings_Webforms_Field_Model::getInstanceByRow($fieldDetails);
				$fieldModel->block = $this;
				$fieldModel->module = $this->module;
				$fieldModelsList[$fieldName] = $fieldModel;
			}
			$this->fields = $fieldModelsList;
		}
		return $this->fields;
	}

	/**
	 * Function to get list of all blocks for selected module
	 * @param <Settings_Webforms_Module_Model> $moduleModel
	 * @return <Array> list of Block models
	 */
	public static function getAllForModule($moduleModel) {
		$blockLabels = array('LBL_WEBFORM_INFORMATION','LBL_ASSIGN_USERS');

		foreach ($blockLabels as $blockName) {
			$blockModels[$blockName] = Settings_Webforms_Block_Model::getInstanceFromName($blockName, $moduleModel);
		}
		return $blockModels;
	}

	/**
	 * Function to get Instance for Block by using name
	 * @param <String> $blockName
	 * @param <Settings_Webforms_Module_Model> $moduleModel
	 * @return <Settings_Webforms_Block_Model> BlockModel
	 */
	public static function getInstanceFromName($blockName, $moduleModel) {
		$blockModel = new self();
		$blockModel->name = $blockName;
		$blockModel->blocklabel = $blockName;
		$blockModel->module = $moduleModel;

		return $blockModel;
	}
}
