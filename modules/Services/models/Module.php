<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Services_Module_Model extends Products_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, App\QueryGenerator $queryGenerator)
	{
		$supportedModulesList = ['Leads', 'Accounts', 'HelpDesk', 'PriceBooks'];
		if (\in_array($sourceModule, $supportedModulesList) || Vtiger_Module_Model::getInstance($sourceModule)->isInventory()) {
			$condition = ['and', ['vtiger_service.discontinued' => 1]];
			if ('PriceBooks' == $sourceModule) {
				$subQuery = (new App\Db\Query())
					->select(['productid'])
					->from('vtiger_pricebookproductrel')
					->where(['pricebookid' => $record]);
				$condition[] = ['not in', 'vtiger_service.serviceid', $subQuery];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}
}
