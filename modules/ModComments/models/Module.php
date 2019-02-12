<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModComments_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get the Quick Links for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);

		return $links;
	}

	/**
	 * Function to get the create url with parent id set.
	 *
	 * @param <type> $parentRecord - parent record for which comment need to be added
	 *
	 * @return string Url
	 */
	public function getCreateRecordUrlWithParent($parentRecord)
	{
		return $this->getCreateRecordUrl() . '&parent_id=' . $parentRecord->getId();
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
		return $settingsLinks;
	}

	/**
	 * Delete coments associated with module.
	 *
	 * @param vtlib\ModuleBasic Instnace of module to use
	 */
	public static function deleteForModule(vtlib\ModuleBasic $moduleInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_modcomments', ['related_to' => (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['setype' => $moduleInstance->name])])->execute();
	}
}
