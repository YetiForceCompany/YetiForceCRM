<?php
/**
 * Chat model class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */

/**
 * Class chat
 */
class Chat
{

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			\App\Db::getInstance()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
		}
	}
}
