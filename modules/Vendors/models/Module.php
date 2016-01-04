<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vendors_Module_Model extends Vtiger_Module_Model {
    
    /**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationModel = false) {
		if ($functionName === 'get_mails' && $relatedModule->getName() == 'OSSMailView') {
			$query = OSSMailView_Record_Model::getMailsQuery($recordId, $relatedModule->getName());
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule, $relationModel);
		}
		
		return $query;
	}
}
