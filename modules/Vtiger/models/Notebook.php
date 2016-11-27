<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Notebook_Model extends Vtiger_Widget_Model
{

	public function getContent()
	{
		$data = \App\Json::decode(decode_html($this->get('data')));
		return $data['contents'];
	}

	public function getLastSavedDate()
	{
		$data = \App\Json::decode(decode_html($this->get('data')));
		return $data['lastSavedOn'];
	}

	public function save($request)
	{
		$db = PearDatabase::getInstance();
		$content = $request->get('contents');
		$noteBookId = $request->get('widgetid');
		$date_var = date("Y-m-d H:i:s");
		$date = $db->formatDate($date_var, true);

		$dataValue = [];
		$dataValue['contents'] = strip_tags($content);
		$dataValue['lastSavedOn'] = $date;

		$data = \App\Json::encode((object) $dataValue);
		$this->set('data', $data);


		$db->pquery('UPDATE vtiger_module_dashboard_widgets SET data=? WHERE id=?', array($data, $noteBookId));
	}

	public static function getUserInstance($widgetId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_module_dashboard_widgets 
			INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid 
			WHERE linktype = ? AND vtiger_module_dashboard_widgets.id = ? AND vtiger_module_dashboard_widgets.userid = ?', ['DASHBOARDWIDGET', $widgetId, \App\User::getCurrentUserId()]);
		$self = new self();
		if ($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			$self->setData($row);
		}
		return $self;
	}
}
