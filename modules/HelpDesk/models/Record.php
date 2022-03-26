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

class HelpDesk_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to get Comments List of this Record.
	 *
	 * @return string
	 */
	public function getCommentsList()
	{
		return (new \App\Db\Query())
			->select(['comments' => 'commentcontent'])
			->from('vtiger_modcomments')
			->where(['related_to' => $this->getId()])->column();
	}

	/**
	 * Get active service contracts.
	 *
	 * @return array
	 */
	public function getActiveServiceContracts()
	{
		$query = (new \App\Db\Query())->from('vtiger_servicecontracts')
			->select(['servicecontractsid', 'subject', 'due_date'])
			->innerJoin('vtiger_crmentity', 'vtiger_servicecontracts.servicecontractsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'contract_status' => 'In Progress', 'sc_related_to' => $this->get('parent_id')])
			->orderBy(['due_date' => SORT_ASC]);
		\App\PrivilegeQuery::getConditions($query, 'ServiceContracts');
		return $query->all();
	}

	/**
	 * Function to save record.
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		$forModule = \App\Request::_get('return_module');
		$forCrmId = \App\Request::_get('return_id');
		if (\App\Request::_get('return_action') && $forModule && $forCrmId && 'ServiceContracts' === $forModule) {
			\Vtiger_Relation_Model::getInstance(\Vtiger_Module_Model::getInstance($forModule), $this->getModule())
				->addRelation($forCrmId, $this->getId());
		}
	}

	/**
	 * Function returns the details of Hierarchy.
	 *
	 * @return array
	 */
	public function getHierarchyDetails(): array
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($this->getModuleName());
		$hierarchy = $moduleModel->getHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $id => $info) {
			preg_match('/<a href="+/', $info[0], $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $info[0], $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $info[0], $name);
				$recordModel = Vtiger_Record_Model::getCleanInstance('HelpDesk');
				$recordModel->setId($id);
				$hierarchy['entries'][$id][0] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}

	/**
	 * Function check if record can be closed.
	 *
	 * @param string $status
	 *
	 * @return array
	 */
	public function checkValidateToClose(string $status): array
	{
		if ((\App\Config::module($this->getModuleName(), 'CHECK_IF_RECORDS_HAS_TIME_CONTROL')
		 || \App\Config::module($this->getModuleName(), 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED'))
		  && \in_array($status, \App\RecordStatus::getStates($this->getModuleName(), \App\RecordStatus::RECORD_STATE_CLOSED))) {
			return [
				'hasTimeControl' => [
					'result' => $this->checkIfHasTimeControl(),
					'message' => \App\Language::translate('LBL_ADD_TIME_CONTROL', $this->getModuleName()), ],
				'relatedTicketsClosed' => [
					'result' => $this->checkIfRelatedTicketsClosed(),
					'message' => \App\Language::translate('LBL_CLOSE_RELATED_TICKETS', $this->getModuleName()), ],
			];
		}
		return ['hasTimeControl' => ['result' => true], 'relatedTicketsClosed' => ['result' => true]];
	}

	/**
	 * Function check if records has fill time control.
	 *
	 * @return bool
	 */
	public function checkIfHasTimeControl(): bool
	{
		if (\App\Config::module($this->getModuleName(), 'CHECK_IF_RECORDS_HAS_TIME_CONTROL') && \App\Module::isModuleActive('OSSTimeControl')) {
			$queryGenerator = new App\QueryGenerator('OSSTimeControl');
			$queryGenerator->permissions = false;
			$queryGenerator->addNativeCondition([\App\ModuleHierarchy::getMappingRelatedField($this->getModuleName()) => $this->getId()]);
			return $queryGenerator->createQuery()->exists();
		}
		return true;
	}

	/**
	 *Function check if related records are close.
	 *
	 * @return bool
	 */
	public function checkIfRelatedTicketsClosed(): bool
	{
		if (\App\Config::module($this->getModuleName(), 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED')) {
			$queryGenerator = new App\QueryGenerator($this->getModuleName());
			$queryGenerator->permissions = false;
			$queryGenerator->addCondition('parentid', $this->getId(), 'eid');
			$statusFieldName = \App\RecordStatus::getFieldName($this->getModuleName());
			$queryGenerator->addCondition($statusFieldName, array_merge(
				\App\RecordStatus::getStates($this->getModuleName(), \App\RecordStatus::RECORD_STATE_NO_CONCERN),
				\App\RecordStatus::getStates($this->getModuleName(), \App\RecordStatus::RECORD_STATE_OPEN)
				), 'e', false);
			return !$queryGenerator->createQuery()->exists();
		}
		return true;
	}
}
