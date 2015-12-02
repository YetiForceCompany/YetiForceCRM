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

	var $baseTable = 'vtiger_blocks_hide';
	var $baseIndex = 'id';
	var $nameFields = array('name');
	var $listFields = array('name' => 'LBL_MODULE', 'blocklabel' => 'LBL_BLOCK_LABEL', 'enabled' => 'LBL_ENABLED', 'view' => 'LBL_VIEW');
	var $name = 'HideBlocks';
	var $views = array('Detail', 'Edit');

	/**
	 * Function to get Create view url
	 * @return <String> Url
	 */
	public function getCreateRecordUrl()
	{
		return "index.php?module=HideBlocks&parent=Settings&view=Edit";
	}

	/**
	 * Function to get List view url
	 * @return <String> Url
	 */
	public function getListViewUrl()
	{
		return "index.php?module=HideBlocks&parent=Settings&view=List";
	}

	/**
	 * Function to get list of Blocks
	 * @return <Array> list of Block models <Settings_Webforms_Block_Model>
	 */
	public function getBlocks()
	{
		if (empty($this->blocks)) {
			$this->blocks = Settings_Webforms_Block_Model::getAllForModule($this);
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
		$adb = PearDatabase::getInstance();
		$result = $adb->query('SELECT * FROM vtiger_blocks INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_blocks.tabid ORDER BY vtiger_blocks.tabid,sequence ASC');
		$rows = array();
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$module = $adb->query_result($result, $i, 'name');
			$rows[$module][$adb->query_result($result, $i, 'blockid')] = array(
				'module' => $module,
				'blocklabel' => $adb->query_result($result, $i, 'blocklabel'),
			);
		}
		return $rows;
	}
}
