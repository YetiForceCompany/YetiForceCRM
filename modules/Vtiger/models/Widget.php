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

use App\Json;

/**
 * Vtiger Widget Model Class.
 */
class Vtiger_Widget_Model extends \App\Base
{
	/** @var array Default labels */
	const DEFAULT_LABELS = [
		'Activities' => 'LBL_ACTIVITIES',
		'DetailView' => 'LBL_RECORD_DETAILS',
		'GeneralInfo' => 'LBL_RECORD_SUMMARY',
	];

	/**
	 * Get ID.
	 *
	 * @return int
	 */
	public function getId(): int
	{
		return (int) $this->get('id');
	}

	public function getWidth()
	{
		$defaultSize = 4;
		$size = $this->get('size');
		if ($size) {
			$size = Json::decode(App\Purifier::decodeHtml($size));
			if (isset($size[App\Session::get('fingerprint')], $size[App\Session::get('fingerprint')]['width'])) {
				$defaultSize = (int) $size[App\Session::get('fingerprint')]['width'];
			} elseif (!empty($size['width'])) {
				$defaultSize = (int) $size['width'];
			}
		}
		return $defaultSize;
	}

	public function getHeight()
	{
		$defaultSize = 4;
		$size = $this->get('size');
		if ($size) {
			$size = Json::decode(App\Purifier::decodeHtml($size));
			if (isset($size[App\Session::get('fingerprint')], $size[App\Session::get('fingerprint')]['height'])) {
				$defaultSize = (int) $size[App\Session::get('fingerprint')]['height'];
			} elseif (!empty($size['height'])) {
				$defaultSize = (int) ($size['height']);
			}
		}
		return $defaultSize;
	}

	/**
	 * Function to get the position of the widget.
	 *
	 * @param int    $defaultPosition
	 * @param string $coordinate
	 * @param int    $position
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return int
	 */
	public function getPosition(int $position, string $coordinate)
	{
		if ($positionData = $this->get('position')) {
			$positionData = Json::decode(App\Purifier::decodeHtml($positionData));
			if (!empty($positionData[App\Session::get('fingerprint')])) {
				$position = (int) $positionData[App\Session::get('fingerprint')][$coordinate];
			}
			if (!empty($positionData[$coordinate])) {
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

	/**
	 * Function to get the translated title.
	 *
	 * @return string
	 */
	public function getTranslatedTitle(): string
	{
		$queryParams = parse_url($this->get('linkurl'), PHP_URL_QUERY);
		parse_str($queryParams, $output);
		return \App\Language::translate($this->getTitle(), $output['module'], null, true, 'Dashboard');
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
	 * @param array $valueMap
	 *
	 * @return \Vtiger_Widget_Model instance
	 */
	public static function getInstanceFromValues($valueMap)
	{
		$className = '';
		if (!empty($valueMap['handler_class'])) {
			$className = $valueMap['handler_class'];
		} elseif (!empty($valueMap['linkid'])) {
			$className = \vtlib\Link::getLinkData($valueMap['linkid'])['handler_class'] ?? null;
		}
		$instance = $className ? new $className() : new static();
		$instance->setData($valueMap);

		return $instance;
	}

	public static function getInstance($linkId, $userId)
	{
		$row = (new \App\Db\Query())->from('vtiger_module_dashboard_widgets')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid')
			->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_links.linkid' => $linkId, 'userid' => $userId])
			->one();

		return $row ? static::getInstanceFromValues($row) : new static();
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

		return $row ? static::getInstanceFromValues($row) : new static();
	}

	public static function getInstanceWithTemplateId(int $widgetId)
	{
		$row = (new \App\Db\Query())->from('vtiger_module_dashboard')
			->innerJoin('vtiger_links', 'vtiger_links.linkid = vtiger_module_dashboard.linkid')
			->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_module_dashboard.id' => $widgetId])
			->one();

		return $row ? static::getInstanceFromValues($row) : new static();
	}

	public static function updateWidgetPosition($position, $linkId, $widgetId, $userId)
	{
		$currentPosition = [];
		if (!$linkId && !$widgetId) {
			return;
		}
		if ($linkId) {
			$where = ['userid' => $userId, 'linkid' => $linkId];
		} elseif ($widgetId) {
			$where = ['userid' => $userId, 'id' => $widgetId];
		}
		$lastSavedPosition = (new \App\Db\Query())->select(['position'])->from('vtiger_module_dashboard_widgets')->where($where)->scalar();
		if ($lastSavedPosition && !JSON::isEmpty($lastSavedPosition)) {
			$currentPosition = JSON::decode($lastSavedPosition);
		}
		$currentPosition[App\Session::get('fingerprint')] = $position;
		\App\Db::getInstance()->createCommand()
			->update('vtiger_module_dashboard_widgets', ['position' => Json::encode($currentPosition)], $where)
			->execute();
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
		$lastSize = (new \App\Db\Query())->select(['size'])->from('vtiger_module_dashboard_widgets')->where($where)->scalar();
		$currentSize = \App\Json::isEmpty($lastSize) ? [] : Json::decode($lastSize);
		$currentSize[App\Session::get('fingerprint')] = $size;
		\App\Db::getInstance()->createCommand()
			->update('vtiger_module_dashboard_widgets', ['size' => Json::encode($currentSize)], $where)
			->execute();
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
		if ('delete' == $action) {
			$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['id' => $this->get('id'), 'blockid' => $this->get('blockid')])
				->execute();
		} elseif ('hide' == $action) {
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
	 * @return bool
	 */
	public function isDefault(): bool
	{
		return 1 == $this->get('isdefault');
	}

	/**
	 * Process the UI Widget requested.
	 *
	 * @param Vtiger_Link_Model   $widgetLink
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function processWidget(Vtiger_Link_Model $widgetLink, Vtiger_Record_Model $recordModel)
	{
		if (preg_match('/^block:\\/\\/(.*)/', $widgetLink->get('linkurl') ?? '', $matches)) {
			[$widgetControllerClass, $widgetControllerClassFile] = explode(':', $matches[1]);
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

	/**
	 * Function to get the Quick Links in settings view.
	 *
	 * @return array List of Vtiger_Link_Model instances
	 */
	public function getSettingsLinks()
	{
		$links = [];
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_EDIT',
				'linkclass' => 'btn btn-success btn-xs js-edit-widget',
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkdata' => ['url' => "index.php?parent=Settings&module=WidgetsManagement&view=EditWidget&linkId={$this->get('linkid')}&blockId={$this->get('blockid')}&widgetId={$this->getId()}"],
			]);
		}
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_DELETE',
				'linkclass' => 'btn-danger btn-xs js-delete-widget',
				'linkicon' => 'fas fa-trash-alt',
				'linkdata' => ['id' => $this->getId()],
			]);
		}

		return $links;
	}

	/** @var array Custom fields for edit */
	public $customFields = [];

	/** @var array Fields for edit */
	public $editFields = [
		'isdefault' => ['label' => 'LBL_MANDATORY_WIDGET', 'purifyType' => \App\Purifier::BOOL],
		'cache' => ['label' => 'LBL_CACHE_WIDGET', 'purifyType' => \App\Purifier::BOOL],
		'width' => ['label' => 'LBL_WIDTH', 'purifyType' => \App\Purifier::INTEGER],
		'height' => ['label' => 'LBL_HEIGHT', 'purifyType' => \App\Purifier::INTEGER],
	];

	/**
	 * Gets fields for edit view.
	 *
	 * @return array
	 */
	public function getEditFields(): array
	{
		$fields = [];
		$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
		if (\in_array($this->get('linklabel'), $widgetsManagementModel->getWidgetsWithLimit())) {
			$fields['limit'] = ['label' => 'LBL_NUMBER_OF_RECORDS_DISPLAYED', 'purifyType' => \App\Purifier::INTEGER];
		}
		if (\in_array($this->get('linklabel'), $widgetsManagementModel->getWidgetsWithDate())) {
			$fields['default_date'] = ['label' => 'LBL_DEFAULT_DATE', 'purifyType' => \App\Purifier::STANDARD];
		}
		if (\in_array($this->get('linklabel'), $widgetsManagementModel->getWidgetsWithFilterUsers())) {
			$fields['default_owner'] = ['label' => 'LBL_DEFAULT_FILTER', 'purifyType' => \App\Purifier::STANDARD];
			$fields['owners_all'] = ['label' => 'LBL_FILTERS_AVAILABLE', 'purifyType' => \App\Purifier::STANDARD];
		}

		return $this->editFields + $fields + $this->customFields;
	}

	/**
	 * Gets field instance by name.
	 *
	 * @param string $name
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$moduleName = 'Settings:WidgetsManagement';
		$field = $this->getEditFields()[$name] ?? null;
		if (!$field) {
			return null;
		}
		$params = [
			'label' => $field['label'],
			'tooltip' => $field['tooltip'] ?? '',
		];
		switch ($name) {
			case 'cache':
			case 'isdefault':
				$params['uitype'] = 56;
				$params['typeofdata'] = 'C~O';
				$params['fieldvalue'] = (int) $this->get($name);
				break;
			case 'title':
				$params['uitype'] = 1;
				$params['typeofdata'] = empty($field['required']) ? 'V~O' : 'V~M';
				$params['maximumlength'] = '100';
				$params['fieldvalue'] = $this->get($name) ?: '';
				break;
			case 'width':
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~M';
				$params['maximumlength'] = '2';
				$params['picklistValues'] = [3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12];
				$params['fieldvalue'] = $this->getWidth();
				break;
			case 'height':
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~M';
				$params['maximumlength'] = '2';
				$params['picklistValues'] = [3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12];
				$params['fieldvalue'] = $this->getHeight();
				break;
			case 'limit':
				$params['uitype'] = 7;
				$params['typeofdata'] = 'I~M';
				$params['maximumlength'] = '127';
				$params['fieldvalue'] = $this->get('limit') ?: 10;
				break;
			case 'default_owner':
				$params['uitype'] = 16;
				$params['maximumlength'] = '100';
				$params['typeofdata'] = 'V~M';
				$picklistValue = ['mine' => 'LBL_MINE', 'all' => 'LBL_ALL'];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$value = $this->get('owners') ? Json::decode($this->get('owners')) : [];
				$params['fieldvalue'] = $value['default'] ?? 'mine';
				break;
			case 'owners_all':
				$params['uitype'] = 33;
				$params['maximumlength'] = '100';
				$params['typeofdata'] = 'V~M';
				$picklistValue = [
					'mine' => 'LBL_MINE',
					'all' => 'LBL_ALL',
					'users' => 'LBL_USERS',
					'groups' => 'LBL_GROUPS',
				];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$owners = $this->get('owners') ? Json::decode($this->get('owners')) : [];
				$value = $owners['available'] ?? ['mine'];
				$params['fieldvalue'] = implode(' |##| ', $value);
				break;
			case 'default_date':
				$params['uitype'] = 16;
				$params['typeofdata'] = 'V~M';
				$picklistValue = [
					'day' => 'PLL_CURRENT_DAY',
					'week' => 'PLL_CURRENT_WEEK',
					'month' => 'PLL_CURRENT_MONTH',
					'year' => 'PLL_CURRENT_YEAR',
				];
				foreach ($picklistValue as $key => $label) {
					$params['picklistValues'][$key] = \App\Language::translate($label, $moduleName);
				}
				$params['fieldvalue'] = $this->get('date');
				break;
			default: break;
		}
		return \Vtiger_Field_Model::init($moduleName, $params, $name);
	}

	/**
	 * Sets data from request.
	 *
	 * @param App\Request $request
	 */
	public function setDataFromRequest(App\Request $request)
	{
		foreach ($this->getEditFields() as $fieldName => $fieldInfo) {
			if ($request->has($fieldName) && !isset($this->customFields[$fieldName])) {
				$value = $request->getByType($fieldName, $fieldInfo['purifyType']);
				$fieldModel = $this->getFieldInstanceByName($fieldName)->getUITypeModel();
				$fieldModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);

				switch ($fieldName) {
					case 'width':
					case 'height':
						$size = $this->get('size') ? Json::decode($this->get('size')) : [];
						$size[$fieldName] = $value;
						$this->set('size', Json::encode($size));
						break;
					case 'default_owner':
						$owners = $this->get('owners') ? Json::decode($this->get('owners')) : [];
						$owners['default'] = $value;
						$this->set('owners', Json::encode($owners));
						break;
					case 'owners_all':
						$value = $value ? explode(' |##| ', $value) : [];
						$owners = $this->get('owners') ? Json::decode($this->get('owners')) : [];
						$owners['available'] = $value;
						$this->set('owners', Json::encode($owners));
						break;
					case 'default_date':
						$this->set('date', $value);
						break;
					default:
						$this->set($fieldName, $value);
						break;
				}
			}
		}
		if (!$this->getId() && !$request->isEmpty('blockId')) {
			$this->set('blockid', $request->getInteger('blockId'));
		}
	}

	/**
	 * Function to save.
	 *
	 * @return bool
	 */
	public function save(): bool
	{
		$db = App\Db::getInstance();
		$params = array_intersect_key($this->getData(), array_flip(['title', 'data', 'size', 'limit', 'isdefault', 'owners', 'cache', 'date', 'filterid']));
		$tableName = 'vtiger_module_dashboard';
		if ($this->getId()) {
			$result = $db->createCommand()->update($tableName, $params, ['id' => $this->getId()])->execute();
			if ($result) {
				$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['templateid' => $this->getId()])->execute();
			}
		} else {
			$params['blockid'] = $this->get('blockid');
			$params['linkid'] = $this->get('linkid');
			$result = $db->createCommand()->insert($tableName, $params)->execute();
			$this->set('id', $db->getLastInsertID("{$tableName}_id_seq"));
		}

		return (bool) $result;
	}

	/**
	 * Remove widget template.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()
			->delete('vtiger_module_dashboard', ['vtiger_module_dashboard.id' => $this->getId()])
			->execute();
	}

	/**
	 * Gets value from data column.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getDataValue(string $name)
	{
		$values = $this->get('data') ? Json::decode($this->get('data')) : [];
		return $values[$name] ?? null;
	}

	/**
	 * Get dashboard id.
	 *
	 * @param \App\Request $request
	 *
	 * @return int
	 */
	public static function getDashboardId(App\Request $request)
	{
		$dashboardId = false;
		if (!$request->isEmpty('dashboardId', true)) {
			$dashboardId = $request->getInteger('dashboardId');
		} elseif (isset($_SESSION['DashBoard'][$request->getModule()]['LastDashBoardId'])) {
			$dashboardId = $_SESSION['DashBoard'][$request->getModule()]['LastDashBoardId'];
		}
		if (!$dashboardId) {
			$dashboardId = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
		$request->set('dashboardId', $dashboardId);
		return $dashboardId;
	}

	/**
	 * Clear configuration of widgets for this device.
	 *
	 * @param int $dashboardId
	 *
	 * @return void
	 */
	public static function clearDeviceConf(int $dashboardId): void
	{
		$fingerPrint = App\Session::get('fingerprint');
		$dataReader = (new \App\Db\Query())->select(['id', 'position', 'size'])->from('vtiger_module_dashboard_widgets')->where([
			'userid' => \App\User::getCurrentUserId(),
			'dashboardid' => $dashboardId,
		])->andWhere([
			'or',
			['like', 'position', "\"$fingerPrint\""],
			['like', 'size', "\"$fingerPrint\""],
		], )->createCommand()->query();

		$createCommand = \App\Db::getInstance()->createCommand();
		while (['id' => $id,'position' => $position,'size' => $size] = $dataReader->read()) {
			$position = $position ? Json::decode($position) : [];
			if (isset($position[$fingerPrint])) {
				unset($position[$fingerPrint]);
			}
			$size = $size ? Json::decode($size) : [];
			if (isset($size[$fingerPrint])) {
				unset($size[$fingerPrint]);
			}
			$createCommand->update('vtiger_module_dashboard_widgets', ['position' => Json::encode($position), 'size' => Json::encode($size)], ['id' => $id])->execute();
		}
	}

	/**
	 * Check if the widget is removable.
	 *
	 * @return bool
	 */
	public function isDeletable(): bool
	{
		return !$this->get('isdefault');
	}

	/**
	 * Check if the widget is viewable.
	 *
	 * @return bool
	 */
	public function isViewable(): bool
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$params = vtlib\Functions::getQueryParams($this->get('linkurl'));
		$moduleName = $params['module'];
		$sourceModulePermission = true;
		if (($name = $params['name'] ?? '') && \in_array($name, ['CalendarActivities', 'OverdueActivities'])) {
			$sourceModulePermission = $userPrivModel->hasModulePermission('Calendar');
		}

		return 'ModTracker' === $moduleName || ($sourceModulePermission && $userPrivModel->hasModulePermission($moduleName));
	}

	/**
	 * Check if the widget is creatable.
	 *
	 * @return bool
	 */
	public function isCreatable(): bool
	{
		return false;
	}
}
