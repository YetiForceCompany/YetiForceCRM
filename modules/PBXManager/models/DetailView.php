<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_DetailView_Model extends Vtiger_DetailView_Model
{
	/**
	 * Overrided to remove Edit button, Duplicate button
	 * To remove related links.
	 */
	public function getDetailViewLinks($linkParams)
	{
		$linkTypes = ['DETAIL_VIEW_ADDITIONAL', 'DETAIL_VIEW_BASIC'];
		$moduleModel = $this->getModule();
		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasiclinks = $linkModelListDetails['DETAIL_VIEW_BASIC'];
		unset($linkModelListDetails['DETAIL_VIEW_BASIC']);
		if (!empty($detailViewBasiclinks)) {
			foreach ($detailViewBasiclinks as $linkModel) {
				// Remove view history, needed in vtiger5 to see history but not in vtiger6
				if ($linkModel->linklabel == 'View History') {
					continue;
				}
				$linkModelList['DETAIL_VIEW_BASIC'][] = $linkModel;
			}
		}

		$widgets = $this->getWidgets();
		if (!empty($widgets)) {
			foreach ($widgets as $widgetLinkModel) {
				$linkModelList['DETAILVIEWWIDGET'][] = $widgetLinkModel;
			}
		}

		return $linkModelList;
	}
}
