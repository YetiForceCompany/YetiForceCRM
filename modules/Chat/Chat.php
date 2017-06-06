<?php

/**
 * Chat model class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Chat
{

	public function vtlib_handler($moduleName, $eventType)
	{
		if ($eventType == 'module.postinstall') {
			$db = PearDatabase::getInstance();
			$db->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', [$moduleName]);
		}
	}
}
