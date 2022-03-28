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

class Settings_Leads_Mapping_Model extends Settings_Vtiger_Module_Model
{
	public $name = 'Leads';

	/**
	 * Function to get detail view url of this model.
	 *
	 * @return string url
	 */
	public function getDetailViewUrl()
	{
		return 'index.php?parent=' . $this->getParentName() . '&module=' . $this->getName() . '&view=MappingDetail';
	}

	/**
	 * Function to get edit view url of this model.
	 *
	 * @return string url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?parent=' . $this->getParentName() . '&module=' . $this->getName() . '&view=MappingEdit';
	}

	/**
	 * Function to get delete url of this mapping model.
	 *
	 * @return string url
	 */
	public function getMappingDeleteUrl()
	{
		return 'index.php?parent=' . $this->getParentName() . '&module=' . $this->getName() . '&action=MappingDelete';
	}

	/**
	 * Function to get headers for detail view.
	 *
	 * @return <Array> headers list
	 */
	public function getHeaders()
	{
		return ['Leads' => 'Leads', 'Type' => 'Type', 'Accounts' => 'Accounts'];
	}

	/**
	 * Function to get list of detail view link models.
	 *
	 * @return <Array> list of detail view link models <Vtiger_Link_Model>
	 */
	public function getDetailViewLinks()
	{
		return [Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'DETAIL_VIEW_BASIC',
			'linklabel' => 'LBL_EDIT',
			'linkurl' => 'javascript:Settings_LeadMapping_Js.triggerEdit("' . $this->getEditViewUrl() . '")',
			'linkicon' => '',
		])];
	}

	/**
	 * Function to get list of mapping link models.
	 *
	 * @return <Array> list of mapping link models <Vtiger_Link_Model>
	 */
	public function getMappingLinks()
	{
		return [Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'DETAIL_VIEW_BASIC',
			'linklabel' => 'LBL_DELETE',
			'linkurl' => 'javascript:Settings_LeadMapping_Js.triggerDelete(event,"' . $this->getMappingDeleteUrl() . '")',
			'linkicon' => '',
		])];
	}

	/**
	 * Function to get mapping details.
	 *
	 * @param mixed $editable
	 *
	 * @return <Array> list of mapping details
	 */
	public function getMapping($editable = false)
	{
		if (empty($this->mapping)) {
			$query = (new \App\Db\Query())->from('vtiger_convertleadmapping');
			if ($editable) {
				$query->where(['editable' => 1]);
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$mapping[$row['cfmid']] = $row;
			}
			$dataReader->close();
			$finalMapping = $fieldIdsList = [];
			foreach ($mapping as $mappingDetails) {
				array_push($fieldIdsList, $mappingDetails['leadfid'], $mappingDetails['accountfid']);
			}
			$fieldLabelsList = [];
			if (!empty($fieldIdsList)) {
				$fieldLabelsList = $this->getFieldsInfo(array_unique($fieldIdsList));
			}
			foreach ($mapping as $mappingId => $mappingDetails) {
				if (isset($fieldLabelsList[$mappingDetails['leadfid']])) {
					$finalMapping[$mappingId] = [
						'editable' => $mappingDetails['editable'],
						'Leads' => $fieldLabelsList[$mappingDetails['leadfid']],
						'Accounts' => $fieldLabelsList[$mappingDetails['accountfid']] ?? null,
					];
				}
			}

			$this->mapping = $finalMapping;
		}
		return $this->mapping;
	}

	/**
	 * Function to get fields info.
	 *
	 * @param  <Array> list of field ids
	 * @param mixed $fieldIdsList
	 *
	 * @return <Array> list of field info
	 */
	public function getFieldsInfo($fieldIdsList)
	{
		$leadModel = Vtiger_Module_Model::getInstance($this->getName());
		$leadId = $leadModel->getId();
		$dataReader = (new App\Db\Query())->select(['fieldid', 'fieldlabel', 'uitype', 'typeofdata', 'fieldname', 'tablename', 'tabid'])
			->from('vtiger_field')
			->where(['fieldid' => $fieldIdsList, 'presence' => [0, 2]])
			->createCommand()->query();
		$fieldLabelsList = [];
		while ($rowData = $dataReader->read()) {
			$fieldInfo = ['id' => $rowData['fieldid'], 'label' => $rowData['fieldlabel']];
			if ((int) $rowData['tabid'] === $leadId) {
				$fieldModel = Settings_Leads_Field_Model::getCleanInstance();
				$fieldModel->set('uitype', $rowData['uitype']);
				$fieldModel->set('typeofdata', $rowData['typeofdata']);
				$fieldModel->set('name', $rowData['fieldname']);
				$fieldModel->set('table', $rowData['tablename']);

				$fieldInfo['fieldDataType'] = $fieldModel->getFieldDataType();
			}
			$fieldLabelsList[$rowData['fieldid']] = $fieldInfo;
		}
		$dataReader->close();
		return $fieldLabelsList;
	}

	/**
	 * Function to save the mapping info.
	 *
	 * @param array $mapping info
	 */
	public function save($mapping)
	{
		$db = \App\Db::getInstance();
		$deleteMappingsList = $updateMappingsList = $createMappingsList = [];
		foreach ($mapping as $mappingDetails) {
			if (\is_array($mappingDetails)) {
				$mappingId = $mappingDetails['mappingId'] ?? '';
				if ($mappingDetails['lead']) {
					if ($mappingId) {
						if ((\array_key_exists('deletable', $mappingDetails)) || !$mappingDetails['account']) {
							$deleteMappingsList[] = $mappingId;
						} elseif ($mappingDetails['account']) {
							$updateMappingsList[] = $mappingDetails;
						}
					} elseif ($mappingDetails['account']) {
						$createMappingsList[] = $mappingDetails;
					}
				}
			}
		}
		if ($deleteMappingsList) {
			self::deleteMapping($deleteMappingsList, true);
		}
		if ($createMappingsList) {
			$count = \count($createMappingsList);
			$insertedData = [];
			for ($i = 0; $i < $count; ++$i) {
				$mappingDetails = $createMappingsList[$i];
				$insertedData[] = [$mappingDetails['lead'], $mappingDetails['account']];
			}
			$db->createCommand()->batchInsert('vtiger_convertleadmapping', ['leadfid', 'accountfid'], $insertedData)
				->execute();
		}
		if ($updateMappingsList) {
			$leadExpression = 'CASE ';
			$accountExpression = 'CASE ';

			foreach ($updateMappingsList as $mappingDetails) {
				$mappingId = $mappingDetails['mappingId'];
				$leadExpression .= " WHEN cfmid = {$db->quoteValue($mappingId)} THEN {$db->quoteValue($mappingDetails['lead'])}";
				$accountExpression .= " WHEN cfmid = {$db->quoteValue($mappingId)} THEN {$db->quoteValue($mappingDetails['account'])}";
			}
			$leadExpression .= ' ELSE leadfid END';
			$accountExpression .= ' ELSE accountfid END';
			$db->createCommand()->update('vtiger_convertleadmapping', ['leadfid' => new yii\db\Expression($leadExpression), 'accountfid' => new yii\db\Expression($accountExpression)], ['editable' => 1])->execute();
		}
	}

	/**
	 * Function to get restricted field ids list.
	 *
	 * @return array list of field ids
	 */
	public static function getRestrictedFieldIdsList()
	{
		$dataReader = (new \App\Db\Query())->select(['accountfid'])->from('vtiger_convertleadmapping')
			->where(['editable' => 0])
			->createCommand()->query();
		$restrictedIdsList = [];
		while ($accountfId = $dataReader->readColumn(0)) {
			if ($accountfId) {
				$restrictedIdsList[] = $accountfId;
			}
		}
		$dataReader->close();

		return $restrictedIdsList;
	}

	/**
	 * Function to get mapping supported modules list.
	 *
	 * @return array
	 */
	public static function getSupportedModulesList()
	{
		return ['Accounts'];
	}

	/**
	 * Function to get instance.
	 *
	 * @param bool true/false
	 * @param mixed $editable
	 *
	 * @return <Settings_Leads_Mapping_Model>
	 */
	public static function getInstance($editable = false)
	{
		$instance = new self();
		$instance->getMapping($editable);

		return $instance;
	}

	/**
	 * Function to get instance.
	 *
	 * @return <Settings_Leads_Mapping_Model>
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Function to delate the mapping.
	 *
	 * @param array $mappingIdsList
	 * @param bool  $editableParam
	 */
	public static function deleteMapping($mappingIdsList, $editableParam = false)
	{
		if ($editableParam) {
			$params = ['cfmid' => $mappingIdsList, 'editable' => 1];
		} else {
			$params = ['cfmid' => $mappingIdsList];
		}
		\App\Db::getInstance()->createCommand()->delete('vtiger_convertleadmapping', $params)->execute();
	}
}
