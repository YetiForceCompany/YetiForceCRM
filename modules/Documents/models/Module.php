<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Documents_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Functions tells if the module supports workflow.
	 *
	 * @return bool
	 */
	public function isWorkflowSupported()
	{
		return true;
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
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
		$queryGenerator->addNativeCondition(['and',
			['not in', 'vtiger_notes.notesid', (new App\Db\Query())->select(['notesid'])->from('vtiger_senotesrel')->where(['crmid' => $record])],
			['vtiger_notes.filestatus' => 1],
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalRecordsListFields(\App\QueryGenerator $queryGenerator, $sourceModule = false)
	{
		$popupFields = parent::getModalRecordsListFields($queryGenerator, $sourceModule);
		$headerFields = $queryGenerator->getListViewFields();
		foreach (['filestatus', 'filesize', 'filelocationtype'] as $fieldName) {
			if (!isset($headerFields[$fieldName])) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $this);
				if ($fieldModel->getPermissions()) {
					$queryGenerator->setField($fieldName);
				}
			}
		}
		return $popupFields;
	}

	/**
	 * Function to get Alphabet Search Field.
	 */
	public function getAlphabetSearchField()
	{
		return 'notes_title';
	}

	/**
	 * Function to get Settings links.
	 *
	 * @return <Array>
	 */
	public function getSettingLinks()
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => 'adminIcon-triggers',
			];
		}
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_FIELDS',
			'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
			'linkicon' => 'adminIcon-modules-fields',
		];

		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
			'linkurl' => 'index.php?parent=Settings&module=Picklist&source_module=' . $this->getName(),
			'linkicon' => 'adminIcon-fields-picklists',
		];

		if ($this->hasSequenceNumberField()) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
				'linkurl' => 'index.php?parent=Settings&module=Vtiger&view=CustomRecordNumbering&sourceModule=' . $this->getName(),
				'linkicon' => 'fas fa-exchange-alt',
			];
		}
		return $settingsLinks;
	}

	/**
	 * Added function that returns the folders in a Document.
	 *
	 * @return array
	 */
	public function getAllFolders()
	{
		$templateId = (new \App\Db\Query())->select(['vtiger_field.fieldparams'])
			->from('vtiger_field')
			->where(['vtiger_field.columnname' => 'folderid', 'vtiger_field.tablename' => 'vtiger_notes'])
			->scalar();

		return (new \App\Db\Query())
			->select(['tree', 'name'])
			->from('vtiger_trees_templates_data')
			->where(['templateid' => $templateId])
			->createCommand()->queryAllByGroup();
	}
}
