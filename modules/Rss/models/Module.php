<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Rss_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get the Quick Links for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getId(), ['SIDEBARLINK', 'SIDEBARWIDGET'], $linkParams);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_ADD_FEED_SOURCE',
			'linkurl' => $this->getDefaultUrl(),
			'linkicon' => 'fas fa-rss',
		]);
		$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_RSS_FEED_SOURCES',
			'linkurl' => 'module=' . $this->getName() . '&view=ViewTypes&mode=getRssWidget',
			'linkicon' => '',
		]);

		return $links;
	}

	/**
	 * Function to get rss sources list.
	 */
	public function getRssSources()
	{
		$dataReader = (new \App\Db\Query())->from('vtiger_rss')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['id'] = $row['rssid'];
			$records[$row['id']] = $this->getRecordFromArray($row);
		}
		$dataReader->close();

		return $records;
	}
}
