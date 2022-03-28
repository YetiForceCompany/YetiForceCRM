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

class Accounts_Module_Model extends Vtiger_Module_Model
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
		if (('Accounts' === $sourceModule && 'account_id' === $field && $record) || \in_array($sourceModule, ['Campaigns', 'Products', 'Services'])) {
			if ('Campaigns' === $sourceModule && $record) {
				$subQuery = (new \App\Db\Query())->select(['crmid'])->from('vtiger_campaign_records')->where(['campaignid' => $record]);
				$queryGenerator->addNativeCondition(['not in', 'vtiger_account.accountid', $subQuery]);
			} elseif ('Products' === $sourceModule && $record) {
				$subQuery = (new \App\Db\Query())->select(['crmid'])->from('vtiger_seproductsrel')->where(['productid' => $record]);
				$queryGenerator->addNativeCondition(['not in', 'vtiger_account.accountid', $subQuery]);
			} elseif ('Services' === $sourceModule && $record) {
				$subQuery = (new \App\Db\Query())->select(['relcrmid'])->from('vtiger_crmentityrel')->where(['crmid' => $record]);
				$secondSubQuery = (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentityrel')->where(['relcrmid' => $record]);
				$queryGenerator->addNativeCondition(['and', ['not in', 'vtiger_account.accountid', $subQuery], ['not in', 'vtiger_account.accountid', $secondSubQuery]]);
			} else {
				$queryGenerator->addNativeCondition(['<>', 'vtiger_account.accountid', 0]);
			}
		}
	}
}
