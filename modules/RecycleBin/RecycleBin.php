<?php

/**
 * RecycleBin class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Class RecycleBin.
 */
class RecycleBin extends Vtiger_CRMEntity
{
	/**
	 * List fields name.
	 *
	 * @var array
	 */
	public $list_fields_name = [];
	/**
	 * For Alphabetical search.
	 *
	 * @var string
	 */
	public $def_basicsearch_col = '';
	/**
	 * Default order by.
	 *
	 * @var string
	 */
	public $default_order_by = '';
	/**
	 * Default sort order.
	 *
	 * @var string
	 */
	public $default_sort_order = '';

	/** {@inheritdoc} */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		}
	}
}
