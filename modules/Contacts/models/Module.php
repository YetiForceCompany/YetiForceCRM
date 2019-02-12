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
	 * Function returns query for module record's search.
	 *
	 * @param string $searchValue  - part of record name (label column of crmentity table)
	 * @param int    $parentId     - parent record id
	 * @param string $parentModule - parent module name
	 *
	 * @return App\Db\Query - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId = false, $parentModule = false)
	{
		$query = (new App\Db\Query())->select(['u_#__crmentity_search_label.crmid', 'u_#__crmentity_search_label.setype', 'u_#__crmentity_search_label.searchlabel'])
			->from('u_#__crmentity_search_label')
			->where(['and', ['like', 'u_#__crmentity_search_label.userid', ',' . App\User::getCurrentUserId() . ','], ['like', 'u_#__crmentity_search_label.searchlabel', $searchValue]]);
		if ($parentId && $parentModule == 'Accounts') {
			return $query->innerJoin('vtiger_contactdetails', 'vtiger_contactdetails.contactid = u_#__crmentity_search_label.crmid')
				->andWhere(['vtiger_contactdetails.parentid' => $parentId]);
		} elseif ($parentId && $parentModule == 'Campaigns') {
			return $query->innerJoin('vtiger_contactdetails', 'vtiger_contactdetails.contactid = u_#__crmentity_search_label.crmid')
				->innerJoin('vtiger_campaign_records', 'vtiger_campaign_records.crmid = vtiger_contactdetails.contactid')
				->andWhere(['vtiger_campaign_records.campaignid' => $parentId]);
		} elseif ($parentId && $parentModule == 'Vendors') {
			return $query->innerJoin('vtiger_contactdetails', 'vtiger_contactdetails.contactid = u_#__crmentity_search_label.crmid')
				->innerJoin('vtiger_vendorcontactrel', 'vtiger_vendorcontactrel.contactid = vtiger_contactdetails.contactid')
				->andWhere(['vtiger_vendorcontactrel.vendorid' => $parentId]);
		}
		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}

	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if ($sourceModule === 'SRecurringOrders' && $record) {
			$queryGenerator->addJoin(['INNER JOIN', 'u_#__srecurringorders', 'u_#__srecurringorders.accountid = vtiger_contactdetails.parentid AND u_#__srecurringorders.srecurringordersid = :records', [':records' => $record]]);
		}
		if (in_array($sourceModule, ['Campaigns', 'Vendors', 'Products', 'Services']) || ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns':
					$tableName = 'vtiger_campaign_records';
					$fieldName = 'crmid';
					$relatedFieldName = 'campaignid';
					break;
				case 'Vendors':
					$tableName = 'vtiger_vendorcontactrel';
					$fieldName = 'contactid';
					$relatedFieldName = 'vendorid';
					break;
				case 'Products':
					$tableName = 'vtiger_seproductsrel';
					$fieldName = 'crmid';
					$relatedFieldName = 'productid';
					break;
				default:
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
}
