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

class SMSNotifier_Module_Model extends Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public function getSettingLinks(): array
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => 'yfi yfi-workflows-2',
			];
		}
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => \App\Language::translate('LBL_SERVER_CONFIG', $this->getName()),
			'linkurl' => 'index.php?module=SMSNotifier&parent=Settings&view=List',
			'linkicon' => 'yfm-SMSNotifier',
		];
		return $settingsLinks;
	}

	/**
	 * Function to get the url for MassSMS view.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getMassSMSUrlForModule(string $moduleName): string
	{
		return "index.php?module={$this->getName()}&view=MassSMS&source_module={$moduleName}";
	}

	/**
	 * Check if SMS is active for given module.
	 *
	 * @param string $moduleName
	 *
	 * @return bool
	 */
	public function isSMSActiveForModule(string $moduleName): bool
	{
		return $this->isPermitted('CreateView')
				&& \App\Integrations\SMSProvider::isActiveProvider()
				&& \in_array($moduleName, $this->getFieldByName('related_to')->getReferenceList());
	}

	/** {@inheritdoc} */
	public function isListViewNameFieldNavigationEnabled()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getValuesFromSource(App\Request $request, $moduleName = false)
	{
		$data = parent::getValuesFromSource($request, $moduleName);
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule && $request->has('sourceRecord')) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $sourceModule);
			$sourceModuleModel = $recordModel->getModule();
			$refField = $this->getField('related_to');
			$phoneField = $this->getField('phone');

			if ($refField && $refField->isActiveField() && $phoneField && $phoneField->isActiveField()) {
				foreach ($sourceModuleModel->getFieldsByType('phone') as $phoneModel) {
					if (!$recordModel->isEmpty($phoneModel->getName()) && $phoneModel->isViewable()) {
						$data[$phoneField->getName()] = $recordModel->get($phoneModel->getName());
						break;
					}
				}
			}
		}

		return $data;
	}
}
