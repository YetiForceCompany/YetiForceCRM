<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_HideBlocks_Module_Model extends Settings_Vtiger_Module_Model
{

	public $baseTable = 'vtiger_blocks_hide';
	public $baseIndex = 'id';
	public $nameFields = array('name');
	public $listFields = array('name' => 'LBL_MODULE', 'blocklabel' => 'LBL_BLOCK_LABEL', 'enabled' => 'LBL_ENABLED', 'view' => 'LBL_VIEW');
	public $name = 'HideBlocks';
	public $views = array('Detail', 'Edit');

	/**
	 * Function to get Create view url
	 * @return string Url
	 */
	public function getCreateRecordUrl()
	{
		return "index.php?module=HideBlocks&parent=Settings&view=Edit";
	}

	/**
	 * Function to get List view url
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return "index.php?module=HideBlocks&parent=Settings&view=List";
	}

	/**
	 * Function to get list of Blocks
	 * @return <Array> list of Block models Settings_HideBlocks_Module_Model
	 */
	public function getBlocks()
	{
		if (empty($this->blocks)) {
			//$this->blocks = Settings_Webforms_Block_Model::getAllForModule($this);
		}
		return $this->blocks;
	}

	public function getViews()
	{
		$views = array();
		foreach ($this->views as $view) {
			$views[$view] = 'LBL_VIEW_' . strtoupper($view);
		}
		return $views;
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
				'blocklabel' => $row['blocklabel']
			];
		}
		return $rows;
	}
}
