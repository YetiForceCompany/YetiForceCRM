<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Contacts_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function returns query for module record's search
	 * @param string $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param string $parentModule - parent module name
	 * @return string - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId = false, $parentModule = false)
	{
		$queryFrom = 'SELECT `u_yf_crmentity_search_label`.`crmid`,`u_yf_crmentity_search_label`.`setype`,`u_yf_crmentity_search_label`.`searchlabel` FROM `u_yf_crmentity_search_label` ';
		$queryWhere = ' WHERE `u_yf_crmentity_search_label`.`userid` LIKE \'%s\' && `u_yf_crmentity_search_label`.`searchlabel` LIKE \'%s\' ';

		if ($parentId && $parentModule == 'Accounts') {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
			$queryFrom .= 'INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = u_yf_crmentity_search_label.crmid';
			$queryWhere .= 'AND vtiger_contactdetails.parentid = %s';
			return sprintf($queryFrom . $queryWhere, '%,' . $currentUser->getId() . ',%', "%$searchValue%", $parentId);
		} else if ($parentId && $parentModule == 'HelpDesk') {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
			$queryFrom .= 'INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = u_yf_crmentity_search_label.crmid INNER JOIN vtiger_contactdetails ON ON vtiger_troubletickets.contact_id = vtiger_contactdetails.contactid';
			$queryWhere .= 'AND vtiger_troubletickets.ticketid = %s';
			return sprintf($queryFrom . $queryWhere, '%,' . $currentUser->getId() . ',%', "%$searchValue%", $parentId);
		} else if ($parentId && $parentModule == 'Campaigns') {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
			$queryFrom .= 'INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = u_yf_crmentity_search_label.crmid INNER JOIN vtiger_campaign_records ON vtiger_campaign_records.crmid = vtiger_contactdetails.contactid';
			$queryWhere .= 'AND vtiger_campaign_records.campaignid = %s';
			return sprintf($queryFrom . $queryWhere, '%,' . $currentUser->getId() . ',%', "%$searchValue%", $parentId);
		} else if ($parentId && $parentModule == 'Vendors') {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
			$queryFrom .= 'INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = u_yf_crmentity_search_label.crmid INNER JOIN vtiger_vendorcontactrel ON vtiger_vendorcontactrel.contactid = vtiger_contactdetails.contactid';
			$queryWhere .= 'AND vtiger_vendorcontactrel.vendorid = %s';
			return sprintf($queryFrom . $queryWhere, '%,' . $currentUser->getId() . ',%', "%$searchValue%", $parentId);
		}
		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}

	/**
	 * Function to get list view query for popup window
	 * @param string $sourceModule Parent module
	 * @param string $field parent fieldname
	 * @param string $record parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if (in_array($sourceModule, array('Campaigns', 'Vendors', 'Products', 'Services')) || ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns' :
					$tableName = 'vtiger_campaign_records';
					$fieldName = 'crmid';
					$relatedFieldName = 'campaignid';
					break;
				case 'Vendors' :
					$tableName = 'vtiger_vendorcontactrel';
					$fieldName = 'contactid';
					$relatedFieldName = 'vendorid';
					break;
				case 'Products' :
					$tableName = 'vtiger_seproductsrel';
					$fieldName = 'crmid';
					$relatedFieldName = 'productid';
					break;
			}
			if ($sourceModule === 'Services') {
				$subQuery = (new App\Db\Query())
					->select(['relcrmid'])
					->from('vtiger_crmentityrel')
					->where(['crmid' => $record]);
				$secondSubQuery = (new App\Db\Query())
					->select(['crmid'])
					->from('vtiger_crmentityrel')
					->where(['relcrmid' => $record]);
				$condition = ['and', ['not in', 'vtiger_contactdetails.contactid', $subQuery], ['not in', 'vtiger_contactdetails.contactid', $secondSubQuery]];
			} elseif ($sourceModule === 'Contacts' && $field === 'contact_id') {
				$condition = ['<>', 'vtiger_contactdetails.contactid', $record];
			} else {
				$subQuery = (new App\Db\Query())->select([$fieldName])->from($tableName)->where([$relatedFieldName => $record]);
				$condition = ['not in', 'vtiger_contactdetails.contactid', $subQuery];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}

	public function getDefaultSearchField()
	{
		return "lastname";
	}
}
