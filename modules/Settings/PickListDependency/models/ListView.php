<?php

/**
 * Settings pickList dependency list view model file.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings pickList dependency list view model class.
 */
class Settings_PickListDependency_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/** {@inheritdoc} */
	public function getBasicListQuery(): App\Db\Query
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
