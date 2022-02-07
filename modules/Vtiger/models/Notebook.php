<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_Notebook_Model extends Vtiger_Widget_Model
{
	public function getContent()
	{
		$data = \App\Json::decode(App\Purifier::decodeHtml($this->get('data')));

		return $data['contents'];
	}

	public function getLastSavedDate()
	{
		$data = \App\Json::decode(App\Purifier::decodeHtml($this->get('data')));

		return $data['lastSavedOn'];
	}

	/**
	 * Function to update the widget.
	 */
	public function save(): bool
	{
		return App\Db::getInstance()->createCommand()->update('vtiger_module_dashboard_widgets', ['data' => $this->get('data')], ['id' => $this->get('id')])->execute();
	}

	/**
	 * Function to get info about widget.
	 *
	 * @param int $widgetId
	 *
	 * @return \self
	 */
	public static function getUserInstance($widgetId)
	{
		$row = (new \App\Db\Query())->from('vtiger_module_dashboard_widgets')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid')
			->where(['vtiger_links.linktype' => 'DASHBOARDWIDGET', 'vtiger_module_dashboard_widgets.id' => $widgetId, 'vtiger_module_dashboard_widgets.userid' => \App\User::getCurrentUserId()])
			->one();
		$self = new self();
		if ($row) {
			$self->setData($row);
		}
		return $self;
	}
}
