<?php

/**
 * RecycleBin class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin extends Vtiger_CRMEntity
{
	public $list_fields_name = [];
	public $def_basicsearch_col = '';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			\App\Db::getInstance()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		}
	}
}
