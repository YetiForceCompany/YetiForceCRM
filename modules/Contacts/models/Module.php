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

class Contacts_Module_Model extends Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $allowTypeChange = false;

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
		if ('SRecurringOrders' === $sourceModule && $record) {
			$queryGenerator->addJoin(['INNER JOIN', 'u_#__srecurringorders', 'u_#__srecurringorders.accountid = vtiger_contactdetails.parentid AND u_#__srecurringorders.srecurringordersid = :records', [':records' => $record]]);
		}
		if (\in_array($sourceModule, ['Campaigns', 'Products', 'Services']) || ('Contacts' === $sourceModule && 'contact_id' === $field && $record)) {
			switch ($sourceModule) {
				case 'Campaigns':
					$tableName = 'vtiger_campaign_records';
					$fieldName = 'crmid';
					$relatedFieldName = 'campaignid';
					break;
				case 'Products':
					$tableName = 'vtiger_seproductsrel';
					$fieldName = 'crmid';
					$relatedFieldName = 'productid';
					break;
				default:
					break;
			}
			if ('Services' === $sourceModule) {
				$subQuery = (new App\Db\Query())
					->select(['relcrmid'])
					->from('vtiger_crmentityrel')
					->where(['crmid' => $record]);
				$secondSubQuery = (new App\Db\Query())
					->select(['crmid'])
					->from('vtiger_crmentityrel')
					->where(['relcrmid' => $record]);
				$condition = ['and', ['not in', 'vtiger_contactdetails.contactid', $subQuery], ['not in', 'vtiger_contactdetails.contactid', $secondSubQuery]];
			} elseif ('Contacts' === $sourceModule && 'contact_id' === $field) {
				$condition = ['<>', 'vtiger_contactdetails.contactid', $record];
			} else {
				$subQuery = (new App\Db\Query())->select([$fieldName])->from($tableName)->where([$relatedFieldName => $record]);
				$condition = ['not in', 'vtiger_contactdetails.contactid', $subQuery];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}
}
