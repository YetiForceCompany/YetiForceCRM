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

class Leads_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function returns the Number of Leads created per week.
	 *
	 * @param int   $owner
	 * @param array $dateFilter
	 *
	 * @return <Array>
	 */
	public function getLeadsCreated($owner, $dateFilter)
	{
		$query = (new App\Db\Query())->select(['count' => 'COUNT(*)', 'time' => 'date(createdtime)'])
			->from('vtiger_leaddetails')
			->innerJoin('vtiger_crmentity', 'vtiger_leaddetails.leadid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'converted' => 0]);
		\App\PrivilegeQuery::getConditions($query, $this->getName());
		if (!empty($owner)) {
			$query->andWhere(['smownerid' => $owner]);
		}
		if (!empty($dateFilter)) {
			$query->andWhere(['between', 'createdtime', $dateFilter['start'] . ' 00:00:00', $dateFilter['end'] . ' 23:59:59']);
		}
		$dataReader = $query->groupBy('date(createdtime)')
			->createCommand()
			->query();

		$response = [];
		while ($row = $dataReader->read()) {
			$response[] = [
				$row['count'],
				$row['time'],
			];
		}
		return $response;
	}

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
		if (!empty($record) && \in_array($sourceModule, ['Campaigns', 'Products', 'Services'])) {
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
				$condition = ['and', ['not in', 'vtiger_leaddetails.leadid', $subQuery], ['not in', 'vtiger_leaddetails.leadid', $secondSubQuery]];
			} else {
				$condition = ['not in', 'vtiger_leaddetails.leadid', (new App\Db\Query())->select([$fieldName])->from($tableName)->where([$relatedFieldName => $record])];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}

	/**
	 * Function to search accounts.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function searchAccountsToConvert(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Start ' . __METHOD__);
		if ($recordModel) {
			$mappingFields = Vtiger_Processes_Model::getConfig('marketing', 'conversion', 'mapping');
			$mappingFields = \App\Json::decode($mappingFields);
			$query = (new App\Db\Query())->select(['vtiger_account.accountid'])
				->from('vtiger_account')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_account.accountid')
				->where(['vtiger_crmentity.deleted' => 0]);
			$joinTable = ['vtiger_account', 'vtiger_crmentity'];
			$moduleModel = Vtiger_Module_Model::getInstance('Accounts');
			$focus = $moduleModel->getEntityInstance();
			foreach ($mappingFields as $mappingField) {
				foreach ($mappingField as $leadFieldName => $accountFieldName) {
					$fieldModel = $moduleModel->getFieldByName($accountFieldName);
					if (!$fieldModel) {
						throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
					}
					$tableName = $fieldModel->get('table');
					if (!\in_array($tableName, $joinTable)) {
						$query->innerJoin($tableName, "{$tableName}.{$focus->tab_name_index[$tableName]} = vtiger_account.accountid");
						$joinTable[] = $tableName;
					}
					$query->andWhere(["{$tableName}.{$fieldModel->getColumnName()}" => $recordModel->get($leadFieldName)]);
				}
			}
			$query->limit(2);
			$dataReader = $query->createCommand()->query();
			$numberRows = $dataReader->count();
			if ($numberRows > 1) {
				$dataReader->close();
				\App\Log::trace('End ' . __METHOD__);

				return false;
			}
			if (1 === $numberRows) {
				\App\Log::trace('End ' . __METHOD__);

				return (int) $dataReader->readColumn(0);
			}
		}
		\App\Log::trace('End ' . __METHOD__);

		return true;
	}

	/**
	 * Function that returns status that allow to convert Lead.
	 *
	 * @return <Array> array of statuses
	 */
	public static function getConversionAvaibleStatuses()
	{
		$leadConfig = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		return $leadConfig['convert_status'];
	}

	/**
	 * Function that checks if lead record can be converted.
	 *
	 * @param string $status - lead status
	 *
	 * @return bool if or not allowed to convert
	 */
	public static function checkIfAllowedToConvert($status)
	{
		$leadConfig = Settings_MarketingProcesses_Module_Model::getConfig('lead');

		if (empty($leadConfig['convert_status'])) {
			return true;
		}
		return \in_array($status, $leadConfig['convert_status']);
	}

	/**
	 * The function adds restrictions to the functionality of searching for records.
	 *
	 * @param App\Db\Query     $query
	 * @param App\RecordSearch $recordSearch
	 *
	 * @return void
	 */
	public function searchRecordCondition(App\Db\Query $query, App\RecordSearch $recordSearch = null): void
	{
		if ($recordSearch->moduleName === $this->getName()) {
			$query->innerJoin('vtiger_leaddetails', 'csl.crmid = vtiger_leaddetails.leadid');
			$query->andWhere(['vtiger_leaddetails.converted' => 0]);
		} else {
			$query->andWhere(['not in', 'csl.crmid', (new \App\Db\Query())->select(['leadid'])->from('vtiger_leaddetails')->where(['converted' => 1])]);
		}
	}
}
