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

class Documents_Module_Model extends Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $allowTypeChange = false;

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
	 * @return bool
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
	public function getQueryByModuleField($sourceModule, $field, $record, App\QueryGenerator $queryGenerator)
	{
		$queryGenerator->addNativeCondition(['and',
			['not in', 'vtiger_notes.notesid', (new App\Db\Query())->select(['notesid'])->from('vtiger_senotesrel')->where(['crmid' => $record])],
			['vtiger_notes.filestatus' => 1],
		]);
	}

	/** {@inheritdoc} */
	public function getModalRecordsListFields(App\QueryGenerator $queryGenerator, $sourceModule = false)
	{
		$popupFields = parent::getModalRecordsListFields($queryGenerator, $sourceModule);
		$headerFields = $queryGenerator->getListViewFields();
		foreach (['filestatus', 'filesize', 'filelocationtype'] as $fieldName) {
			if (!isset($headerFields[$fieldName])) {
				$fieldModel = $this->getFieldByName($fieldName);
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

	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if (\App\Security\AdminAccess::isPermitted('Workflows') && VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => 'yfi yfi-workflows-2',
			];
		}
		if (\App\Security\AdminAccess::isPermitted('LayoutEditor')) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_FIELDS',
				'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
				'linkicon' => 'adminIcon-modules-fields',
			];
		}
		if (\App\Security\AdminAccess::isPermitted('Picklist')) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
				'linkurl' => 'index.php?parent=Settings&module=Picklist&source_module=' . $this->getName(),
				'linkicon' => 'adminIcon-fields-picklists',
			];
		}
		if (\App\Security\AdminAccess::isPermitted('RecordNumbering') && $this->hasSequenceNumberField()) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
				'linkurl' => 'index.php?parent=Settings&module=RecordNumbering&view=CustomRecordNumbering&sourceModule=' . $this->getName(),
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

	/** {@inheritdoc} */
	public function getCustomLinkLabel(int $id, string $label): string
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($id, $this->getName());
		$link = '';
		if ('I' === $recordModel->get('filelocationtype') && ($href = $recordModel->getDownloadFileURL())) {
			$title = App\Language::translate('LBL_DOWNLOAD_FILE', 'Documents');
			$link = "<a href=\"{$href}\" title=\"{$title}\"><span class=\"fas fa-download ml-1\"></span></a>";
		} elseif ($recordModel->get('filename')) {
			$href = \App\Purifier::encodeHtml($recordModel->get('filename'));
			$link = "<a href=\"{$href}\" title=\"{$href}\" target=\"_blank\" rel=\"noreferrer noopener\"><span class=\"fa-solid fa-link ml-1\"></span></a>";
		}
		return \App\Purifier::encodeHtml($label) . $link;
	}
}
