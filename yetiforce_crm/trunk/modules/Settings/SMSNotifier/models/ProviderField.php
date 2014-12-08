<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_SMSNotifier_ProviderField_Model extends Settings_SMSNotifier_Field_Model {
	
	/**
	 * Function to get all provider field models
	 * @return <Array> field models list 
	 */
	public static function getAll() {
		$providers = SMSNotifier_Provider_Model::getAll();
		$providersFieldModelsList = array();
		foreach($providers as $provider){
			$fieldsInfo = self::getInstanceByProvider($provider);
			$fieldModelsList = array();
			foreach ($fieldsInfo as $fieldRow) {
				$fieldModelsList[$fieldRow['name']] = self::getInstanceByRow($fieldRow);
			}
			$providersFieldModelsList[$provider->getName()] = $fieldModelsList;
		}
		return $providersFieldModelsList;
	}
	
	/**
	 * Function to get all provider field models
	 * @param <Object> provider instance
	 * @return <Array> field Info 
	 */
	public static function getInstanceByProvider($provider) {
		$fieldsInfo = $provider->getRequiredParams();
		if(!is_array($fieldsInfo[0])){
			$newFieldInfo = array();
			foreach($fieldsInfo as $key) {
				array_push($newFieldInfo,array('name' => $key, 'label' => $key, 'type' => 'text'));
			}
			return $newFieldInfo;
		} else {
			return $fieldsInfo;
		}
	}
	
	public static function getEditFieldTemplateName($providerName) {
		$providerInstance = SMSNotifier_Provider_Model::getInstance($providerName);
		if(method_exists($providerInstance,'getProviderEditFieldTemplateName')) {
			return $providerInstance->getProviderEditFieldTemplateName();
		} else{
			return 'ProviderEditFields.tpl';
		}
	}

}