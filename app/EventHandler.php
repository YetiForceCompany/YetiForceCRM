<?php

namespace App;

/**
 * Event Handler main class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class EventHandler
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected static $baseTable = 'vtiger_eventhandlers';
	private static $mandatoryEventClass = ['ModTracker_ModTrackerHandler_Handler'];
	private $recordModel;
	private $moduleName;
	private $params;
	private $exceptions = [];
	private $handlers = [];

	/** @var int Handler is in system mode, no editing possible */
	public const SYSTEM = 0;
	/** @var int Handler is in edit mode */
	public const EDITABLE = 1;

	/** @var string Edit view, validation before saving */
	public const EDIT_VIEW_PRE_SAVE = 'EditViewPreSave';
	/** @var string Edit view, change value */
	public const EDIT_VIEW_CHANGE_VALUE = 'EditViewChangeValue';
	/** @var string Record converter after create record */
	public const RECORD_CONVERTER_AFTER_SAVE = 'RecordConverterAfterSave';

	/**
	 * Handler types.
	 *
	 * @var array
	 */
	public const HANDLER_TYPES = [
		'EditViewPreSave' => [
			'label' => 'LBL_EDIT_VIEW_PRESAVE',
			'icon' => 'fas fa-step-backward',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'EntityChangeState' => [
			'label' => 'LBL_ENTITY_CHANGE_STATE',
			'icon' => 'fas fa-compass',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'EntityBeforeSave' => [
			'label' => 'LBL_ENTITY_BEFORE_SAVE',
			'icon' => 'fas fa-save',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'EntityAfterSave' => [
			'label' => 'LBL_ENTITY_AFTER_SAVE',
			'icon' => 'far fa-save',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'DetailViewBefore' => [
			'label' => 'LBL_DETAIL_VIEW_BEFORE',
			'icon' => 'mdi mdi-account-details c-mdi',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'EditViewBefore' => [
			'label' => 'LBL_EDIT_VIEW_BEFORE',
			'icon' => 'yfi yfi-full-editing-view ',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'EditViewDuplicate' => [
			'label' => 'LBL_EDIT_VIEW_DUPLICATE',
			'icon' => 'fas fa-clone',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
		'InventoryRecordDetails' => [
			'label' => 'LBL_INVENTORY_RECORD_DETAILS',
			'icon' => 'fas fa-pallet',
			'columns' => [
				'eventName' => ['label' => 'LBL_EVENT_NAME'],
				'eventDescription' => ['label' => 'LBL_EVENT_DESC'],
				'modules' => ['label' => 'LBL_INCLUDE_MODULES'],
				'modulesExcluded' => ['label' => 'LBL_EXCLUDE_MODULES'],
				'active' => ['label' => 'LBL_EVENT_IS_ACTIVE'],
			],
		],
	];

	/**
	 * Get all event handlers.
	 *
	 * @param bool $active
	 *
	 * @return array
	 */
	public static function getAll(bool $active = true): array
	{
		$query = (new \App\Db\Query())->from(self::$baseTable)->orderBy(['priority' => SORT_DESC]);
		if ($active) {
			$query->where(['is_active' => 1]);
		}
		return $query->indexBy('eventhandler_id')->all();
	}

	/**
	 * Get active event handlers by type (event_name).
	 *
	 * @param string $name
	 * @param string $moduleName
	 * @param bool   $active
	 *
	 * @return array
	 */
	public static function getByType(string $name, ?string $moduleName = '', bool $active = true): array
	{
		$handlersByType = [];
		$cacheName = 'All' . ($active ? ':active' : '');
		if (Cache::has('EventHandlerByType', $cacheName)) {
			$handlersByType = Cache::get('EventHandlerByType', $cacheName);
		} else {
			foreach (self::getAll($active) as $handler) {
				$handlersByType[$handler['event_name']][$handler['handler_class']] = $handler;
			}
			Cache::save('EventHandlerByType', $cacheName, $handlersByType, Cache::LONG);
		}
		$handlers = $handlersByType[$name] ?? [];
		if ($moduleName) {
			foreach ($handlers as $key => $handler) {
				if ((!empty($handler['include_modules']) && !\in_array($moduleName, explode(',', $handler['include_modules']))) || (!empty($handler['exclude_modules']) && \in_array($moduleName, explode(',', $handler['exclude_modules'])))) {
					unset($handlers[$key]);
				}
			}
		}
		return $handlers;
	}

	/**
	 * Get vars event handlers by type (event_name).
	 *
	 * @param string $name
	 * @param string $moduleName
	 * @param array  $params
	 * @param bool   $byKey
	 *
	 * @return string
	 */
	public static function getVarsByType(string $name, string $moduleName, array $params, bool $byKey = false): string
	{
		$return = [];
		foreach (self::getByType($name, $moduleName) as $key => $handler) {
			$className = $handler['handler_class'];
			if (method_exists($className, 'vars') && ($vars = (new $className())->vars($name, $params, $moduleName))) {
				if ($byKey) {
					$return[$key] = $vars;
				} else {
					$return = array_values(array_unique(array_merge($return, $vars)));
				}
			}
		}
		return Purifier::encodeHtml(Json::encode($return));
	}

	/**
	 * Register an event handler.
	 *
	 * @param string $eventName      The name of the event to handle
	 * @param string $className
	 * @param string $includeModules
	 * @param string $excludeModules
	 * @param int    $priority
	 * @param bool   $isActive
	 * @param int    $ownerId
	 * @param int    $mode
	 *
	 * @return bool
	 */
	public static function registerHandler(string $eventName, string $className, $includeModules = '', $excludeModules = '', $priority = 5, $isActive = true, $ownerId = 0, $mode = 1): bool
	{
		$return = false;
		$isExists = (new \App\Db\Query())->from(self::$baseTable)->where(['event_name' => $eventName, 'handler_class' => $className])->exists();
		if (!$isExists) {
			$return = \App\Db::getInstance()->createCommand()
				->insert(self::$baseTable, [
					'event_name' => $eventName,
					'handler_class' => $className,
					'is_active' => $isActive,
					'include_modules' => $includeModules,
					'exclude_modules' => $excludeModules,
					'priority' => $priority,
					'owner_id' => $ownerId,
					'privileges' => $mode,
				])->execute();
			static::clearCache();
		}
		return $return;
	}

	/**
	 * Clear cache.
	 *
	 * @return void
	 */
	public static function clearCache(): void
	{
		Cache::delete('EventHandlerByType', 'All');
		Cache::delete('EventHandlerByType', 'All:active');
	}

	/**
	 * Unregister a registered handler.
	 *
	 * @param string      $className
	 * @param bool|string $eventName
	 */
	public static function deleteHandler($className, $eventName = false)
	{
		$params = ['handler_class' => $className];
		if ($eventName) {
			$params['event_name'] = $eventName;
		}
		\App\Db::getInstance()->createCommand()->delete(self::$baseTable, $params)->execute();
		static::clearCache();
	}

	/**
	 * Update an event handler.
	 *
	 * @param array $params
	 * @param int   $id
	 *
	 * @return void
	 */
	public static function update(array $params, int $id)
	{
		Db::getInstance()->createCommand()->update(self::$baseTable, $params, ['eventhandler_id' => $id])->execute();
		static::clearCache();
	}

	/**
	 * Check if it is active function.
	 *
	 * @param string      $className
	 * @param string|null $eventName
	 *
	 * @return bool
	 */
	public static function checkActive(string $className, ?string $eventName = null): bool
	{
		$rows = (new \App\Db\Query())->from(self::$baseTable)->where(['handler_class' => $className])->all();
		$status = false;
		foreach ($rows as $row) {
			if (isset($eventName) && $eventName !== $row['event_name']) {
				continue;
			}
			if (empty($row['is_active'])) {
				return false;
			}
			$status = true;
		}
		return $status;
	}

	/**
	 * Set an event handler as inactive.
	 *
	 * @param string      $className
	 * @param bool|string $eventName
	 */
	public static function setInActive($className, $eventName = false)
	{
		$params = ['handler_class' => $className];
		if ($eventName) {
			$params['event_name'] = $eventName;
		}
		\App\Db::getInstance()->createCommand()
			->update(self::$baseTable, ['is_active' => false], $params)->execute();
		static::clearCache();
	}

	/**
	 * Set an event handler as active.
	 *
	 * @param string      $className
	 * @param bool|string $eventName
	 */
	public static function setActive($className, $eventName = false)
	{
		$params = ['handler_class' => $className];
		if ($eventName) {
			$params['event_name'] = $eventName;
		}
		\App\Db::getInstance()->createCommand()->update(self::$baseTable, ['is_active' => true], $params)->execute();
		static::clearCache();
	}

	/**
	 * Set record model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return $this
	 */
	public function setRecordModel(\Vtiger_Record_Model $recordModel)
	{
		$this->recordModel = $recordModel;
		$this->moduleName = $recordModel->getModuleName();
		return $this;
	}

	/**
	 * Set module name.
	 *
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public function setModuleName($moduleName)
	{
		$this->moduleName = $moduleName;
		return $this;
	}

	/**
	 * Set params.
	 *
	 * @param array $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Add param.
	 *
	 * @param array $params
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function addParams($key, $value)
	{
		$this->params[$key] = $value;
	}

	/**
	 * Get record model.
	 *
	 * @return \Vtiger_Record_Model
	 */
	public function getRecordModel()
	{
		return $this->recordModel;
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->moduleName;
	}

	/**
	 * Get params.
	 *
	 * @return array Additional parameters
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Get param.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getParam(string $key)
	{
		return $this->params[$key] ?? null;
	}

	/**
	 * Set exceptions.
	 *
	 * @param array $exceptions
	 */
	public function setExceptions(array $exceptions)
	{
		$this->exceptions = $exceptions;
		return $this;
	}

	/**
	 * @param string $name Event name
	 *
	 * @return array Handlers list
	 */
	public function getHandlers(string $name): array
	{
		$handlers = static::getByType($name, $this->moduleName);
		if ($this->exceptions['disableHandlers'] ?? null) {
			$handlers = array_intersect_key($handlers, array_flip(self::$mandatoryEventClass));
		} elseif ($disableHandlers = $this->exceptions['disableHandlerClasses'] ?? null) {
			foreach ($disableHandlers as $className) {
				if (isset($handlers[$className])) {
					unset($handlers[$className]);
				}
			}
		}
		return $handlers;
	}

	/**
	 * Trigger an event.
	 *
	 * @param string $name Event name
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function trigger(string $name)
	{
		foreach ($this->getHandlers($name) as $handler) {
			$this->triggerHandler($handler);
		}
	}

	/**
	 * Trigger handler.
	 *
	 * @param array $handler
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function triggerHandler(array $handler)
	{
		$className = $handler['handler_class'];
		$function = lcfirst($handler['event_name']);
		if (!method_exists($className, $function)) {
			Log::error("Handler not found, class: {$className} | {$function}");
			throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND');
		}
		if (isset($this->handlers[$className])) {
			$handler = $this->handlers[$className];
		} else {
			$handler = $this->handlers[$className] = new $className();
		}
		return $handler->{$function}($this);
	}
}
