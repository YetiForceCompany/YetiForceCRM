<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Vtiger Widget Model Class
 */
class Vtiger_Widget_Model extends Vtiger_Base_Model
{

	public function getWidth()
	{
		$largerSizedWidgets = array('GroupedBySalesPerson', 'GroupedBySalesStage', 'Funnel Amount', 'LeadsByIndustry');
		$title = $this->getName();
		$size = \App\Json::decode(html_entity_decode($this->get('size')));
		$width = $size['width'];
		$this->set('width', $width);

		if (empty($width)) {
			$this->set('width', '4');
		}
		return $this->get('width');
	}

	public function getHeight()
	{
		//Special case for History widget
		$size = \App\Json::decode(html_entity_decode($this->get('size')));
		$height = $size['height'];
		$this->set('height', $height);

		if (empty($height)) {
			$this->set('height', '1');
		}
		return $this->get('height');
	}

	public function getPositionCol($default = 0)
	{
		$position = $this->get('position');
		if ($position) {
			$position = \App\Json::decode(decode_html($position));
			return intval($position['col']);
		}
		return $default;
	}

	public function getPositionRow($default = 0)
	{
		$position = $this->get('position');
		if ($position) {
			$position = \App\Json::decode(decode_html($position));
			return intval($position['row']);
		}
		return $default;
	}

	/**
	 * Function to get the url of the widget
	 * @return string
	 */
	public function getUrl()
	{
		$url = decode_html($this->get('linkurl')) . '&linkid=' . $this->get('linkid');
		$widgetid = $this->has('widgetid') ? $this->get('widgetid') : $this->get('id');
		$url .= '&widgetid=' . $widgetid . '&active=' . $this->get('active');

		return $url;
	}

	/**
	 *  Function to get the Title of the widget
	 */
	public function getTitle()
	{
		$title = $this->get('title');
		if (empty($title)) {
			$title = $this->get('linklabel');
		}
		return $title;
	}

	public function getName()
	{
		$widgetName = $this->get('name');
		if (empty($widgetName)) {
			$linkUrl = decode_html($this->getUrl());
			preg_match('/name=[a-zA-Z]+/', $linkUrl, $matches);
			$matches = explode('=', $matches[0]);
			$widgetName = $matches[1];
			$this->set('name', $widgetName);
		}
		return $widgetName;
	}

	/**
	 * Function to get the instance of Vtiger Widget Model from the given array of key-value mapping
	 * @param <Array> $valueMap
	 * @return Vtiger_Widget_Model instance
	 */
	public static function getInstanceFromValues($valueMap)
	{
		$self = new self();
		$self->setData($valueMap);
		return $self;
	}

	public static function getInstance($linkId, $userId)
	{
		$row = (new \App\Db\Query())->from('vtiger_module_dashboard_widgets')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid')
			->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_links.linkid' => $linkId, 'userid' => $userId])
			->one();
		$self = new self();
		if ($row) {
			$self->setData($row);
		}
		return $self;
	}

	public static function updateWidgetPosition($position, $linkId, $widgetId, $userId)
	{
		if (!$linkId && !$widgetId)
			return;
		if ($linkId) {
			$where = ['userid' => $userId, 'linkid' => $linkId];
		} else if ($widgetId) {
			$where = ['userid' => $userId, 'id' => $widgetId];
		}
		\App\Db::getInstance()->createCommand()->update(('vtiger_module_dashboard_widgets'), ['position' => $position], $where)->execute();
	}

	public static function getInstanceWithWidgetId($widgetId, $userId)
	{
		$row = (new \App\Db\Query())->from('vtiger_module_dashboard_widgets')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid')
			->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_module_dashboard_widgets.id' => $widgetId, 'userid' => $userId])
			->one();
		$self = new self();
		if ($row) {
			if ($row['linklabel'] == 'Mini List') {
				if (!$row['isdeafult'])
					$row['deleteFromList'] = true;
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$row['title'] = $minilistWidgetModel->getTitle();
			} else if ($row['linklabel'] == 'ChartFilter') {
				if (!$row['isdeafult'])
					$row['deleteFromList'] = true;
				$chartFilterWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$chartFilterWidgetModel = new Vtiger_ChartFilter_Model();
				$chartFilterWidgetModel->setWidgetModel($chartFilterWidget);
				$row['title'] = $chartFilterWidgetModel->getTitle();
			}
			$self->setData($row);
		}
		return $self;
	}

	/**
	 * Function to show a widget from the Users Dashboard
	 */
	public function show()
	{
		if (0 == $this->get('active')) {
			App\Db::getInstance()->createCommand()
				->update('vtiger_module_dashboard_widgets', ['active' => 1], ['id' => $this->get('widgetid')])
				->execute();
		}
		$this->set('id', $this->get('widgetid'));
	}

	/**
	 * Function to remove the widget from the Users Dashboard
	 * @param string $action
	 */
	public function remove($action = 'hide')
	{
		$db = App\Db::getInstance();
		if ($action == 'delete') {
			$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['id' => $this->get('id'), 'blockid' => $this->get('blockid')])
				->execute();
		} else if ($action == 'hide') {
			$db->createCommand()->update('vtiger_module_dashboard_widgets', ['active' => 0], ['id' => $this->get('id')])
				->execute();
			$this->set('active', 0);
		}
	}

	/**
	 * Function returns URL that will remove a widget for a User
	 * @return string
	 */
	public function getDeleteUrl()
	{
		$url = 'index.php?module=Vtiger&action=RemoveWidget&linkid=' . $this->get('linkid');
		$widgetid = $this->has('widgetid') ? $this->get('widgetid') : $this->get('id');
		if ($widgetid)
			$url .= '&widgetid=' . $widgetid;
		return $url;
	}

	/**
	 * Function to check the Widget is Default widget or not
	 * @return <boolean> true/false
	 */
	public function isDefault()
	{
		if ($this->get('isdefault') == 1) {
			return true;
		}
		return false;
	}

	/**
	 * Process the UI Widget requested
	 * @param vtlib\Link $widgetLink
	 * @param Current Smarty Context $context
	 */
	public function processWidget(Vtiger_Link_Model $widgetLink, Vtiger_Record_Model $recordModel)
	{
		if (preg_match("/^block:\/\/(.*)/", $widgetLink->get('linkurl'), $matches)) {
			list($widgetControllerClass, $widgetControllerClassFile) = explode(':', $matches[1]);
			if (!class_exists($widgetControllerClass)) {
				\vtlib\Deprecated::checkFileAccessForInclusion($widgetControllerClassFile);
				include_once $widgetControllerClassFile;
			}
			if (class_exists($widgetControllerClass)) {
				$widgetControllerInstance = new $widgetControllerClass;
				$widgetInstance = $widgetControllerInstance->getWidget($widgetLink);
				if ($widgetInstance) {
					return $widgetInstance->process($recordModel);
				}
			}
		}
	}

	public static function removeWidgetFromList($id)
	{
		$db = PearDatabase::getInstance();
		$query = "SELECT templateid FROM vtiger_module_dashboard_widgets WHERE id = ?";
		$result = $db->pquery($query, [$id]);
		$templateId = $db->getSingleValue($result);
		if ($templateId)
			$db->delete('vtiger_module_dashboard', 'id = ?', [$templateId]);
		$db->delete('vtiger_module_dashboard_widgets', 'id = ?', [$id]);
	}
}
