<?php

/**
 * Settings HideBlocks ListView model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_HideBlocks_ListView_Model extends Settings_Vtiger_ListView_Model
{

	public function getBasicListQuery()
	{
		$query = parent::getBasicListQuery();
		$query->innerJoin('vtiger_blocks', 'vtiger_blocks.blockid = vtiger_blocks_hide.blockid');
		$query->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_blocks.tabid');
		return $query;
	}
}
