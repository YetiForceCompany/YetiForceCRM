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

class Settings_PickListDependency_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function creates preliminary database query.
	 *
	 * @return App\Db\Query()
	 */
	public function getBasicListQuery()
	{
		$module = $this->getModule();
		$query = (new App\Db\Query())->from($module->getBaseTable());
		$sourceModule = $this->get('sourceModule');
		if ($sourceModule) {
			$query->where(['tabid' => \App\Module::getModuleId($sourceModule)]);
		}
		return $query;
	}
}
