<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides API to work with vtiger CRM Modules.
 */
class Module extends ModuleBasic
{
	/**
	 * Allow export.
	 *
	 * @var bool
	 */
	public $allowExport = false;

	/**
	 * Get related list sequence to use.
	 *
	 * @return int
	 */
	public function __getNextRelatedListSequence()
	{
		return (new \App\Db\Query())->from('vtiger_relatedlists')->where(['tabid' => $this->id])->max('sequence') + 1;
	}

	/**
	 * Set related list information between other module.
	 *
	 * @param Module Instance of target module with which relation should be setup
	 * @param string Label to display in related list (default is target module name)
	 * @param array List of action button to show ('ADD', 'SELECT')
	 * @param string Callback function name of this module to use as handler
	 * @param mixed $moduleInstance
	 * @param mixed $label
	 * @param mixed $actions
	 * @param mixed $functionName
	 * @param mixed $fieldName
	 * @param mixed $fields
	 */
	public function setRelatedList($moduleInstance, $label = '', $actions = false, $functionName = 'getRelatedList', $fieldName = null, $fields = [])
	{
		$db = \App\Db::getInstance();
		if (empty($moduleInstance)) {
			return;
		}
		if (empty($label)) {
			$label = $moduleInstance->name;
		}
		// Allow ADD action of other module records (default)
		if (false === $actions) {
			$actions = ['ADD'];
		}
		$useactionsText = $actions;
		if (\is_array($actions)) {
			$useactionsText = implode(',', $actions);
		}
		$useactionsText = strtoupper($useactionsText);
		$isExists = (new \App\Db\Query())
			->select(['relation_id'])
			->from('vtiger_relatedlists')
			->where(['tabid' => $this->id, 'related_tabid' => $moduleInstance->id, 'name' => $functionName, 'label' => $label, 'field_name' => $fieldName])
			->exists();
		if ($isExists) {
			\App\Log::trace("Setting relation with $moduleInstance->name [$useactionsText] ... Error, the related module already exists", __METHOD__);
			return;
		}
		$sequence = $this->__getNextRelatedListSequence();
		$presence = 0; // 0 - Enabled, 1 - Disabled

		$db->createCommand()->insert('vtiger_relatedlists', [
			'tabid' => $this->id,
			'related_tabid' => $moduleInstance->id,
			'name' => $functionName,
			'sequence' => $sequence,
			'label' => $label,
			'presence' => $presence,
			'actions' => $useactionsText,
			'field_name' => $fieldName,
		])->execute();
		if ($fields) {
			$id = $db->getLastInsertID('vtiger_relatedlists_relation_id_seq');
			$allFields = (new \App\Db\Query())->select(['fieldid', 'fieldname'])
				->from('vtiger_field')
				->where(['tabid' => $moduleInstance->id])
				->indexBy('fieldname')->all();
			foreach ($fields as $key => $value) {
				$db->createCommand()->insert('vtiger_relatedlists_fields', [
					'relation_id' => $id,
					'fieldid' => $allFields[$value]['fieldid'],
					'sequence' => $key,
				])->execute();
			}
		}
		if ('getManyToMany' === $functionName) {
			$refTableName = \Vtiger_Relation_Model::getReferenceTableInfo($moduleInstance->name, $this->name);
			$schema = $db->getSchema();
			if (!$schema->getTableSchema($refTableName['table'])) {
				$db->createTable($refTableName['table'], [
					'crmid' => 'int',
					'relcrmid' => 'int',
				]);
				$db->createCommand()->createIndex("{$refTableName['table']}_crmid_idx", $refTableName['table'], 'crmid')->execute();
				$db->createCommand()->createIndex("{$refTableName['table']}_relcrmid_idx", $refTableName['table'], 'relcrmid')->execute();
				$db->createCommand()->addForeignKey(
					"fk_1_{$refTableName['table']}", $refTableName['table'], 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'
				)->execute();
				$db->createCommand()->addForeignKey(
					"fk_2_{$refTableName['table']}", $refTableName['table'], 'relcrmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT'
				)->execute();
			}
		}
		\App\Cache::clear();
		\App\Log::trace("Setting relation with $moduleInstance->name  ... DONE", __METHOD__);
	}

	/**
	 * Unset related list information that exists with other module.
	 *
	 * @param \Module Instance of target module with which relation should be setup
	 * @param string Label to display in related list (default is target module name)
	 * @param string Callback function name of this module to use as handler
	 * @param mixed $moduleInstance
	 * @param mixed $label
	 * @param mixed $function_name
	 */
	public function unsetRelatedList($moduleInstance, $label = '', $function_name = 'getRelatedList')
	{
		if (empty($moduleInstance)) {
			return;
		}
		if (empty($label)) {
			$label = $moduleInstance->name;
		}
		$id = (new \App\Db\Query())
			->select(['relation_id'])
			->from('vtiger_relatedlists')
			->where(['tabid' => $this->id, 'related_tabid' => $moduleInstance->id, 'name' => $function_name, 'label' => $label])
			->scalar();
		$createCommand = \App\Db::getInstance()->createCommand();
		$createCommand->delete('vtiger_relatedlists', ['relation_id' => $id])->execute();
		$createCommand->delete('vtiger_relatedlists_fields', ['relation_id' => $id])->execute();
		\App\Relation::clearCacheById($id);
		\App\Log::trace("Unsetting relation with $moduleInstance->name ... DONE", __METHOD__);
	}

	/**
	 * Add custom link for a module page.
	 *
	 * @param string Type can be like 'DETAIL_VIEW_BASIC', 'LISTVIEW' etc..
	 * @param string Label to use for display
	 * @param string HREF value to use for generated link
	 * @param string Path to the image file (relative or absolute)
	 * @param int Sequence of appearance
	 *
	 * NOTE: $url can have variables like $MODULE (module for which link is associated),
	 * $RECORD (record on which link is dispalyed)
	 * @param mixed      $type
	 * @param mixed      $label
	 * @param mixed      $iconpath
	 * @param mixed      $sequence
	 * @param mixed|null $handlerInfo
	 */
	public function addLink($type, $label, $url, $iconpath = '', $sequence = 0, $handlerInfo = null)
	{
		Link::addLink($this->id, $type, $label, $url, $iconpath, $sequence, $handlerInfo);
	}

	/**
	 * Delete custom link of a module.
	 *
	 * @param string Type can be like 'DETAIL_VIEW_BASIC', 'LISTVIEW' etc..
	 * @param string Display label to lookup
	 * @param string URL value to lookup
	 * @param mixed $type
	 * @param mixed $label
	 * @param mixed $url
	 */
	public function deleteLink($type, $label, $url = false)
	{
		Link::deleteLink($this->id, $type, $label, $url);
	}

	/**
	 * Get all the custom links related to this module.
	 */
	public function getLinks()
	{
		return Link::getAll($this->id);
	}

	/**
	 * Initialize webservice setup for this module instance.
	 */
	public function initWebservice()
	{
		Webservice::initialize($this);
	}

	public function createFiles(Field $entityField)
	{
		$targetpath = 'modules/' . $this->name;

		if (!is_file($targetpath)) {
			$templatepath = 'vtlib/ModuleDir/BaseModule/';
			$flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
			$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($templatepath, $flags), \RecursiveIteratorIterator::SELF_FIRST);
			foreach ($objects as $name => $object) {
				$targetPath = str_replace($templatepath, '', $name);
				$targetPath = str_replace('_ModuleName_', $this->name, $targetPath);
				if (is_dir($name)) {
					if (!is_dir($targetPath)) {
						mkdir($targetPath, 0755);
					}
				} else {
					$fileContent = file_get_contents($name);
					$replacevars = [
						'<ModuleName>' => $this->name,
						'<ModuleLabel>' => $this->label,
						'<modulename>' => strtolower($this->name),
						'<entityfieldlabel>' => $entityField->label,
						'<entitycolumn>' => $entityField->column,
						'<entityfieldname>' => $entityField->name,
						'_ModuleName_' => $this->name,
						'<_baseTableName_>' => 'u_' . (\App\Db::getInstance()->getConfig('base')['tablePrefix']) . strtolower($this->name),
					];
					foreach ($replacevars as $key => $value) {
						$fileContent = str_replace($key, addslashes($value), $fileContent);
					}
					file_put_contents($targetPath, $fileContent);
				}
			}
			$languages = \App\Language::getAll(false);
			$langFile = 'languages/' . \App\Language::DEFAULT_LANG . '/' . $this->name . '.json';
			foreach ($languages as $prefix => $language) {
				if (\App\Language::DEFAULT_LANG !== $prefix) {
					copy($langFile, 'languages/' . $prefix . '/' . $this->name . '.json');
				}
			}
		}
	}

	/**
	 * Get instance by id or name.
	 *
	 * @param mixed id or name of the module
	 * @param mixed $value
	 *
	 * @return self
	 */
	public static function getInstance($value)
	{
		$instance = false;
		$data = Functions::getModuleData($value);
		if ($data) {
			$instance = new self();
			$instance->initialize($data);
		}
		return $instance;
	}

	/**
	 * Get instance of the module class.
	 *
	 * @param string Module name
	 * @param mixed $modulename
	 */
	public static function getClassInstance($modulename)
	{
		$instance = false;
		$filepath = "modules/$modulename/$modulename.php";
		if (Utils::checkFileAccessForInclusion($filepath, false)) {
			Deprecated::checkFileAccessForInclusion($filepath);
			include_once $filepath;
			if (class_exists($modulename)) {
				$instance = new $modulename();
			}
		}
		return $instance;
	}

	/**
	 * Fire the event for the module (if moduleHandler is defined).
	 *
	 * @param mixed $modulename
	 * @param mixed $eventType
	 */
	public static function fireEvent($modulename, $eventType)
	{
		$return = true;
		$instance = self::getClassInstance((string) $modulename);
		if ($instance && method_exists($instance, 'moduleHandler')) {
			\App\Log::trace("Invoking moduleHandler for $eventType ...START", __METHOD__);
			$fire = $instance->moduleHandler((string) $modulename, (string) $eventType);
			if (null !== $fire && true !== $fire) {
				$return = false;
			}
			\App\Log::trace("Invoking moduleHandler for $eventType ...DONE", __METHOD__);
		}
		return $return;
	}

	/**
	 * Toggle the module (enable/disable).
	 *
	 * @param mixed $moduleName
	 * @param mixed $enableDisable
	 */
	public static function toggleModuleAccess($moduleName, $enableDisable)
	{
		$eventType = false;
		if (true === $enableDisable) {
			$enableDisable = 0;
			$eventType = self::EVENT_MODULE_ENABLED;
		} elseif (false === $enableDisable) {
			$enableDisable = 1;
			$eventType = self::EVENT_MODULE_DISABLED;
		}
		$fire = self::fireEvent($moduleName, $eventType);
		if ($fire) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['presence' => $enableDisable], ['name' => $moduleName])->execute();
			$tabId = \App\Module::getModuleId($moduleName);
			\App\Cache::delete('moduleTabByName', $moduleName);
			\App\Cache::delete('moduleTabById', $tabId);
			\App\Cache::delete('moduleTabs', 'all');
			\App\Cache::staticDelete('module', $moduleName);
			\App\Cache::staticDelete('module', $tabId);
			\App\Module::createModuleMetaFile();
			\Settings_GlobalPermission_Record_Model::recalculate();
			$menuRecordModel = new \Settings_Menu_Record_Model();
			$menuRecordModel->refreshMenuFiles();
		}
	}

	/**
	 * Check if this module is customized.
	 *
	 * @return bool
	 */
	public function isCustomizable(): bool
	{
		return 1 === $this->customized;
	}

	/**
	 * Check if this module is upgradable.
	 *
	 * @return bool
	 */
	public function isModuleUpgradable(): bool
	{
		return $this->isCustomizable() && 0 === $this->premium;
	}

	/**
	 * Check if this module is exportable.
	 *
	 * @return bool
	 */
	public function isExportable(): bool
	{
		return $this->allowExport || ($this->isCustomizable() && 0 === $this->premium);
	}
}
