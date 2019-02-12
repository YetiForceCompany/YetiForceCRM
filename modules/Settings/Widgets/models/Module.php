<?php

/**
 * Settings Widgets Module Model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Widgets_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Function to get widgets.
	 *
	 * @param int|string $module
	 *
	 * @return array
	 */
	public static function getWidgets($module = false)
	{
		if ($module && !is_numeric($module)) {
			$module = \App\Module::getModuleId($module);
		}
		if (\App\Cache::has('ModuleWidgets', $module)) {
			return \App\Cache::get('ModuleWidgets', $module);
		}
		$query = (new App\Db\Query())->from('vtiger_widgets');
		if ($module) {
			$query->where(['tabid' => $module]);
		}
		$dataReader = $query->orderBy(['tabid' => SORT_ASC, 'sequence' => SORT_ASC])
			->createCommand()->query();
		$widgets = [1 => [], 2 => [], 3 => []];
		while ($row = $dataReader->read()) {
			$row['data'] = \App\Json::decode($row['data']);
			$widgets[$row['wcol']][$row['id']] = $row;
		}
		$dataReader->close();
		App\Cache::save('ModuleWidgets', $module, $widgets);

		return $widgets;
	}

	/**
	 * Return list of modules which have summary view.
	 *
	 * @return array
	 */
	public function getModulesList()
	{
		$modules = \vtlib\Functions::getAllModules();
		foreach ($modules as $id => $module) {
			$moduleModel = Vtiger_Module_Model::getInstance($module['name']);
			if (!$moduleModel->isSummaryViewSupported()) {
				unset($modules[$id]);
			}
		}
		return $modules;
	}

	/**
	 * Return available sizes of widgets.
	 *
	 * @return int[]
	 */
	public function getSize()
	{
		return [1, 2, 3];
	}

	/**
	 * Function to get types.
	 *
	 * @param int $module
	 *
	 * @return array
	 */
	public function getType($module = false)
	{
		$moduleName = \App\Module::getModuleName($module);

		$dir = 'modules/Vtiger/widgets/';
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$ffs = scandir($dir);
		foreach ($ffs as $ff) {
			$action = str_replace('.php', '', $ff);
			if ($ff != '.' && $ff != '..' && !is_dir($dir . '/' . $ff) && $action != 'Basic') {
				$folderFiles[$action] = $action;
				Vtiger_Loader::includeOnce('~~' . $dir . $ff);
				$modelClassName = Vtiger_Loader::getComponentClassName('Widget', $action, 'Vtiger');
				$instance = new $modelClassName();
				if ($instance->allowedModules && !in_array($moduleName, $instance->allowedModules) || ($action == 'Comments' && !$moduleModel->isCommentEnabled())) {
					unset($folderFiles[$action]);
				}
			}
		}
		return $folderFiles;
	}

	/**
	 * Return available columns of widgets.
	 *
	 * @return int[]
	 */
	public function getColumns()
	{
		return [1, 2, 3, 4, 5, 6];
	}

	/**
	 * Function to get related modules for module.
	 *
	 * @param int $tabid
	 *
	 * @return array
	 */
	public function getRelatedModule($tabid)
	{
		return (new \App\Db\Query())->select(['vtiger_relatedlists.*', 'vtiger_tab.name'])
			->from('vtiger_relatedlists')
			->leftJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_relatedlists.related_tabid')
			->where(['and', ['vtiger_relatedlists.tabid' => $tabid], ['<>', 'vtiger_relatedlists.related_tabid', '0']])
			->indexBy('relation_id')->all();
	}

	/**
	 * Function to get filters.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	public function getFiletrs($modules)
	{
		$filetrs = [];
		$tabid = [];
		foreach ($modules as $value) {
			if (!in_array($value['related_tabid'], $tabid)) {
				$dataReader = (new \App\Db\Query())->select(['columnname', 'tablename', 'fieldlabel', 'fieldname'])
					->from('vtiger_field')
					->where(['tabid' => $value['related_tabid'], 'uitype' => [15, 16]])
					->createCommand()->query();
				while ($row = $dataReader->read()) {
					$filetrs[$value['related_tabid']][$row['fieldname']] = \App\Language::translate($row['fieldlabel'], $value['name']);
				}
				$dataReader->close();
				$tabid[] = $value['related_tabid'];
			}
		}
		return $filetrs;
	}

	/**
	 * Function to get checkboxes.
	 *
	 * @param array $modules
	 *
	 * @return array
	 */
	public function getCheckboxs($modules)
	{
		$checkboxs = [];
		$tabid = [];
		foreach ($modules as $value) {
			if (!in_array($value['related_tabid'], $tabid)) {
				$dataReader = (new \App\Db\Query())->select(['columnname', 'tablename', 'fieldlabel', 'fieldname'])
					->from('vtiger_field')
					->where(['tabid' => $value['related_tabid'], 'uitype' => [56]])
					->andWhere(['<>', 'columnname', 'was_read'])
					->createCommand()->query();
				while ($row = $dataReader->read()) {
					$checkboxs[$value['related_tabid']][$row['tablename'] . '.' . $row['fieldname']] = \App\Language::translate($row['fieldlabel'], $value['name']);
				}
				$dataReader->close();
				$tabid[] = $value['related_tabid'];
			}
		}
		return $checkboxs;
	}

	/**
	 * Return list of fields for module and uitypes.
	 *
	 * @param int        $tabid
	 * @param int[]|bool $uitype
	 *
	 * @return array
	 */
	public function getFields($tabid, $uitype = false)
	{
		$fieldlabel = $fieldsList = [];
		$query = (new \App\Db\Query())->select(['fieldid', 'columnname', 'tablename', 'fieldname', 'fieldlabel'])
			->from('vtiger_field')
			->where(['and', ['tabid' => $tabid], ['<>', 'displaytype', 2], ['presence' => [0, 2]]]);
		if ($uitype) {
			$query->andWhere(['uitype' => $uitype]);
		}
		$dataReader = $query->createCommand()->query();
		$moduleName = App\Module::getModuleName($tabid);
		while ($row = $dataReader->read()) {
			$fieldlabel[$row['fieldid']] = \App\Language::translate($row['fieldlabel'], $moduleName);
			$fieldsList[$tabid][$row['tablename'] . '::' . $row['columnname'] . '::' . $row['fieldname']] = \App\Language::translate($row['fieldlabel'], $moduleName);
		}
		$dataReader->close();

		return ['labels' => $fieldlabel, 'table' => $fieldsList];
	}

	/**
	 * Save widget.
	 *
	 * @param array $params
	 */
	public static function saveWidget($params)
	{
		$db = App\Db::getInstance();
		$tabid = $params['tabid'];
		$data = $params['data'];
		$wid = $data['wid'] ?? '';
		$widgetName = 'Vtiger_' . $data['type'] . '_Widget';
		if (class_exists($widgetName)) {
			$widgetInstance = new $widgetName();
			$dbParams = $widgetInstance->dbParams;
		}
		$data = array_merge($dbParams, $data);
		$label = $data['label'];
		unset($data['label']);
		$type = $data['type'];
		unset($data['type']);
		if (isset($data['FastEdit'])) {
			$FastEdit = [];
			if (!is_array($data['FastEdit'])) {
				$FastEdit[] = $data['FastEdit'];
				$data['FastEdit'] = $FastEdit;
			}
		}
		unset($data['filter_selected'], $data['wid']);

		$serializeData = \App\Json::encode($data);
		$sequence = self::getLastSequence($tabid) + 1;
		if ($wid) {
			$db->createCommand()->update('vtiger_widgets', [
				'label' => $label,
				'data' => $serializeData,
			], ['id' => $wid])->execute();
		} else {
			$db->createCommand()->insert('vtiger_widgets', [
				'tabid' => $tabid,
				'type' => $type,
				'label' => $label,
				'sequence' => $sequence,
				'data' => $serializeData,
			])->execute();
		}
		\App\Cache::delete('ModuleWidgets', $tabid);
	}

	/**
	 * Remove widget.
	 *
	 * @param int $wid
	 */
	public static function removeWidget($wid)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_widgets', ['id' => $wid])->execute();
		\App\Cache::clear();
	}

	/**
	 * Return information about widget.
	 *
	 * @param int $wid
	 *
	 * @return type
	 */
	public function getWidgetInfo($wid)
	{
		$resultrow = (new \App\Db\Query())->from('vtiger_widgets')
			->where(['id' => $wid])
			->one();
		$resultrow['data'] = \App\Json::decode($resultrow['data']);

		return $resultrow;
	}

	/**
	 * Function to get last sequence number.
	 *
	 * @param int $tabid
	 *
	 * @return int
	 */
	public static function getLastSequence($tabid)
	{
		return (new \App\Db\Query())->from('vtiger_widgets')->where(['tabid' => $tabid])->max('sequence');
	}

	/**
	 * Update sequence number.
	 *
	 * @param array $params
	 */
	public static function updateSequence($params)
	{
		$db = App\Db::getInstance();
		$tabid = $params['tabid'];
		$data = $params['data'];
		foreach ($data as $key => $value) {
			$db->createCommand()
				->update('vtiger_widgets', ['sequence' => $value['index'], 'wcol' => $value['column']], ['tabid' => $tabid, 'id' => $key])
				->execute();
		}
		\App\Cache::delete('ModuleWidgets', $tabid);
	}

	/**
	 * Return available fields with WYSIWYG.
	 *
	 * @param int    $tabid
	 * @param string $module
	 *
	 * @return array
	 */
	public function getWYSIWYGFields($tabid, $module)
	{
		$field = [];
		$dataReader = (new \App\Db\Query())->select(['fieldlabel', 'fieldname'])
			->from('vtiger_field')
			->where(['tabid' => $tabid, 'uitype' => 300])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$field[$row['fieldname']] = \App\Language::translate($row['fieldlabel'], $module);
		}
		$dataReader->close();

		return $field;
	}

	/**
	 * Function to get switch buttons for widget.
	 *
	 * @param int $index
	 *
	 * @return array
	 */
	public static function getHeaderSwitch($index = [])
	{
		$data = [
			\App\Module::getModuleId('SSalesProcesses') => [
				[
					'type' => 1,
					'label' => \App\Language::translate('LBL_HEADERSWITCH_OPEN_CLOSED', 'SSalesProcesses'), // used only in configuration
					'value' => ['ssalesprocesses_status' => ['PLL_SALE_COMPLETED', 'PLL_SALE_FAILED', 'PLL_SALE_CANCELLED']],
				],
			],
		];
		if (empty($index)) {
			return $data;
		} elseif ($data[$index[0]]) {
			return $data[$index[0]][$index[1]];
		} else {
			return [];
		}
	}

	/**
	 * Function to get buttons which visible in header widget.
	 *
	 * @param int $moduleId Number id module
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public static function getHeaderButtons($moduleId)
	{
		$linkList = [];
		$moduleName = \App\Module::getModuleName($moduleId);
		if ($moduleName === 'Documents') {
			$linkList[] = [
				'linklabel' => App\Language::translate('LBL_MASS_ADD', $moduleName),
				'linkurl' => 'javascript:Vtiger_Index_Js.massAddDocuments("index.php?module=Documents&view=MassAddDocuments")',
				'linkicon' => 'adminIcon-document-templates',
				'linkclass' => 'btn-light btn-sm',
			];
		}
		$buttons = [];
		foreach ($linkList as &$link) {
			$buttons[] = Vtiger_Link_Model::getInstanceFromValues($link);
		}
		return $buttons;
	}
}
