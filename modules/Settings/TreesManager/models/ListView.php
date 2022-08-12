<?php

/**
 * Settings TreesManager ListView model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/** {@inheritdoc} */
	public function loadListViewCondition(): App\Db\Query
	{
		$listQuery = $this->getBasicListQuery();
		$sourceModule = $this->get('sourceModule');
		if (!empty($sourceModule)) {
			$listQuery->where(['tabid' => \App\Module::getModuleId($sourceModule)]);
		}
		return $listQuery;
	}
}
