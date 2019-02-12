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
 * Vtiger Widget Model Class.
 */
class Vtiger_Widget_Model extends \App\Base
{
	public function getWidth()
	{
		$defaultSize = 4;
		$size = $this->get('size');
		if ($size) {
			$size = \App\Json::decode(App\Purifier::decodeHtml($size));
			if (isset($size[App\Session::get('fingerprint')])) {
				return (int) $size[App\Session::get('fingerprint')]['width'];
			}
			return (int) ($size['width']);
		}
		return $defaultSize;
	}

	public function getHeight()
	{
		$defaultSize = 4;
		$size = $this->get('size');
		if ($size) {
			$size = \App\Json::decode(App\Purifier::decodeHtml($size));
			if (isset($size[App\Session::get('fingerprint')])) {
				return (int) $size[App\Session::get('fingerprint')]['height'];
			}
			return (int) ($size['height']);
		}
		return $defaultSize;
	}

	/**
	 * Function to get the position of the widget.
	 *
	 * @param int    $defaultPosition
	 * @param string $coordinate
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return int
	 */
	public function getPosition(int $position, string $coordinate)
	{
		if ($positionData = $this->get('position')) {
			$positionData = \App\Json::decode(App\Purifier::decodeHtml($positionData));
			if (isset($positionData[App\Session::get('fingerprint')])) {
				$position = (int) $positionData[App\Session::get('fingerprint')][$coordinate];
			}
			if (isset($positionData[$coordinate])) {
				$position = (int) ($positionData[$coordinate]);
			}
		}
		return $position;
	}

	/**
	 * Function to get the url of the widget.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		$url = App\Purifier::decodeHtml($this->get('linkurl')) . '&linkid=' . $this->get('linkid');
		$widgetid = $this->has('widgetid') ? $this->get('widgetid') : $this->get('id');
		return $url . '&widgetid=' . $widgetid . '&active=' . $this->get('active');
	}

	/**
	 *  Function to get the Title of the widget.
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
			$linkUrl = App\Purifier::decodeHtml($this->getUrl());
			preg_match('/name=[a-zA-Z]+/', $linkUrl, $matches);
			$matches = explode('=', $matches[0]);
			$widgetName = $matches[1];
			$this->set('name', $widgetName);
		}
		return $widgetName;
	}

	/**
	 * Function to get the instance of Vtiger Widget Model from the given array of key-value mapping.
	 *
	 * @param <Array> $valueMap
	 *
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
		if (!$linkId && !$widgetId) {
			return;
		}
		if ($linkId) {
			$where = ['userid' => $userId, 'linkid' => $linkId];
		} elseif ($widgetId) {
			$where = ['userid' => $userId, 'id' => $widgetId];
		}
		$currentPosition = App\Json::decode((new \App\Db\Query())->select(['position'])->from('vtiger_module_dashboard_widgets')->where($where)->scalar());
		$currentPosition[App\Session::get('fingerprint')] = App\Json::decode($position);
		\App\Db::getInstance()->createCommand()->update('vtiger_module_dashboard_widgets', ['position' => App\Json::encode($currentPosition)], $where)->execute();
	}

	/**
	 * Update widget size.
	 *
	 * @param string $size
	 * @param int    $linkId
	 * @param int    $widgetId
	 * @param int    $userId
	 */
	public static function updateWidgetSize($size, $linkId, $widgetId, $userId)
	{
		if (!$linkId && !$widgetId) {
			return;
		}
		if ($linkId) {
			$where = ['userid' => $userId, 'linkid' => $linkId];
		} elseif ($widgetId) {
			$where = ['userid' => $userId, 'id' => $widgetId];
		}
		$currentSize = App\Json::decode((new \App\Db\Query())->select(['size'])->from('vtiger_module_dashboard_widgets')->where($where)->scalar());
		$currentSize[App\Session::get('fingerprint')] = App\Json::decode($size);
		\App\Db::getInstance()->createCommand()->update('vtiger_module_dashboard_widgets', ['size' => App\Json::encode($currentSize)], $where)->execute();
	}

	/**
	 * Get widget instance by id.
	 *
	 * @param int $widgetId
	 * @param int $userId
	 *
	 * @return \self
	 */
	public static function getInstanceWithWidgetId($widgetId, $userId)
	{
		$row = (new \App\Db\Query())->from('vtiger_module_dashboard_widgets')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid')
			->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_module_dashboard_widgets.id' => $widgetId, 'userid' => $userId])
			->one();
		$self = new self();
		if ($row) {
			if ($row['linklabel'] === 'Mini List') {
				if (!$row['isdefault'] && \App\Privilege::isPermitted(\App\Module::getModuleName($row['module']), 'CreateDashboardFilter', false, $userId)) {
					$row['deleteFromList'] = true;
				}
				$minilistWidget = self::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$row['title'] = $minilistWidgetModel->getTitle();
			} elseif ($row['linklabel'] === 'ChartFilter') {
				if (!$row['isdefault'] && \App\Privilege::isPermitted(\App\Module::getModuleName($row['module']), 'CreateDashboardChartFilter', false, $userId)) {
					$row['deleteFromList'] = true;
				}
				$chartFilterWidget = self::getInstanceFromValues($row);
				$chartFilterWidgetModel = new Vtiger_ChartFilter_Model();
				$chartFilterWidgetModel->setWidgetModel($chartFilterWidget);
				$row['title'] = $chartFilterWidgetModel->getTitle();
			}
			$self->setData($row);
		}
		return $self;
	}

	/**
	 * Function to show a widget from the Users Dashboard.
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
	 * Function to remove the widget from the Users Dashboard.
	 *
	 * @param string $action
	 */
	public function remove($action = 'hide')
	{
		$db = App\Db::getInstance();
		if ($action == 'delete') {
			$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['id' => $this->get('id'), 'blockid' => $this->get('blockid')])
				->execute();
		} elseif ($action == 'hide') {
			$db->createCommand()->update('vtiger_module_dashboard_widgets', ['active' => 0], ['id' => $this->get('id')])
				->execute();
			$this->set('active', 0);
		}
	}

	/**
	 * Function returns URL that will remove a widget for a User.
	 *
	 * @return string
	 */
	public function getDeleteUrl()
	{
		$url = 'index.php?module=' . App\Module::getModuleName($this->get('module')) . '&action=Widget&mode=remove&linkid=' . $this->get('linkid');
		$widgetid = $this->has('widgetid') ? $this->get('widgetid') : $this->get('id');
		if ($widgetid) {
			$url .= '&widgetid=' . $widgetid;
		}
		return $url;
	}

	/**
	 * Function to check the Widget is Default widget or not.
	 *
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
	 * Process the UI Widget requested.
	 *
	 * @param vtlib\Link             $widgetLink
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
				$widgetControllerInstance = new $widgetControllerClass();
				$widgetInstance = $widgetControllerInstance->getWidget($widgetLink);
				if ($widgetInstance) {
					return $widgetInstance->process($recordModel);
				}
			}
		}
	}

	/**
	 * Remove widget from list in dashboard. Removing is possible only for widgets from filters.
	 *
	 * @param int $id
	 */
	public static function removeWidgetFromList($id)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$templateId = (new App\Db\Query())->select(['templateid'])->from('vtiger_module_dashboard_widgets')->where(['id' => $id])->scalar();
		if ($templateId) {
			$dbCommand->delete('vtiger_module_dashboard', ['id' => $templateId])->execute();
		}
		$dbCommand->delete('vtiger_module_dashboard_widgets', ['id' => $id])->execute();
	}
}
