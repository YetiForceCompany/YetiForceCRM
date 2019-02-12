<?php

/**
 * Settings HideBlocks module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_HideBlocks_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'vtiger_blocks_hide';
	public $baseIndex = 'id';
	public $nameFields = ['name'];
	public $listFields = ['name' => 'LBL_MODULE', 'blocklabel' => 'LBL_BLOCK_LABEL', 'enabled' => 'LBL_ENABLED', 'view' => 'LBL_VIEW'];
	public $name = 'HideBlocks';
	public $views = ['Detail', 'Edit'];

	/**
	 * Function to get Create view url.
	 *
	 * @return string Url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=HideBlocks&parent=Settings&view=Edit';
	}

	/**
	 * Function to get List view url.
	 *
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=HideBlocks&parent=Settings&view=List';
	}

	/**
	 * Function to get list of Blocks.
	 *
	 * @return array list of Block models Settings_HideBlocks_Module_Model
	 */
	public function getBlocks()
	{
		return $this->blocks;
	}

	public function getViews()
	{
		$viewsArray = [];
		foreach ($this->views as $view) {
			$viewsArray[$view] = 'LBL_VIEW_' . strtoupper($view);
		}
		return $viewsArray;
	}

	public function getAllBlock()
	{
		$dataReader = (new \App\Db\Query())->from('vtiger_blocks')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_blocks.tabid')
			->orderBy(['vtiger_blocks.tabid' => SORT_ASC, 'sequence' => SORT_ASC])->createCommand()->query();
		$rows = [];
		while ($row = $dataReader->read()) {
			$module = $row['name'];
			$rows[$module][$row['blockid']] = [
				'module' => $module,
				'blocklabel' => $row['blocklabel'],
			];
		}
		$dataReader->close();

		return $rows;
	}
}
