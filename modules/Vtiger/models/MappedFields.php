<?php

/**
 * Basic MappedFields Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MappedFields_Model extends \App\Base
{
	public static $baseTable = 'a_yf_mapped_config';
	public static $mappingTable = 'a_yf_mapped_fields';
	public static $baseIndex = 'id';
	public static $mappingIndex = 'mappedid';
	protected $mapping = [];

	/**
	 * Function to get the id of the record.
	 *
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	public function get($key)
	{
		if (\in_array($key, ['conditions', 'params']) && !\is_array(parent::get($key))) {
			return \App\Json::decode(html_entity_decode(parent::get($key)));
		}
		return parent::get($key);
	}

	public function getRaw($key)
	{
		return parent::get($key);
	}

	public function getModule()
	{
		return Vtiger_Module_Model::getInstance($this->getName());
	}

	public function getRelatedModule()
	{
		return Vtiger_Module_Model::getInstance($this->getRelatedName());
	}

	/**
	 * Check if templates are avauble for this record, user and view.
	 *
	 * @param int    $recordId   - id of a record
	 * @param string $moduleName - name of the module
	 * @param string $view       - modules view - Detail or List
	 *
	 * @return bool true or false
	 */
	public function checkActiveTemplates($recordId, $moduleName, $view)
	{
		$templates = $this->getActiveTemplatesForRecord($recordId, $view, $moduleName);

		if (\count($templates) > 0) {
			return true;
		}
		return false;
	}

	public function getActiveTemplatesForRecord($recordId, $view, $moduleName = false)
	{
		\App\Log::trace('Entering ' . __METHOD__ . '(' . $recordId . ',' . $view . ',' . $moduleName . ') method ...');
		if (!\App\Record::isExists($recordId)) {
			\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

			return [];
		}
		if (!$moduleName) {
			$moduleName = \App\Record::getType($recordId);
		}

		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			if (!$template->checkFiltersForRecord($recordId) || !$template->checkUserPermissions() || !\App\Privilege::isPermitted($template->getRelatedName(), 'EditView')) {
				unset($templates[$id]);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $templates;
	}

	/**
	 * Returns template records by module name.
	 *
	 * @param string $moduleName - module name for which template was created
	 *
	 * @return array of template record models
	 */
	public static function getTemplatesByModule($moduleName)
	{
		\App\Log::trace('Entering ' . __METHOD__ . '(' . $moduleName . ') method ...');
		if (App\Cache::has('MappedFieldsTemplatesByModule', $moduleName)) {
			$rows = App\Cache::get('MappedFieldsTemplatesByModule', $moduleName);
		} else {
			$rows = (new \App\Db\Query())->from(self::$baseTable)
				->where(['tabid' => \App\Module::getModuleId($moduleName), 'status' => 1])
				->all();
			\App\Cache::save('MappedFieldsTemplatesByModule', $moduleName, $rows);
		}
		$templates = [];
		foreach ($rows as $row) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
			$mf = new $handlerClass();
			$mf->setData($row);
			$templates[$mf->getId()] = $mf;
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $templates;
	}

	public function getActiveTemplatesForModule($moduleName, $view)
	{
		\App\Log::trace('Entering ' . __METHOD__ . '(' . $moduleName . ',' . $view . ') method ...');
		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			if (!$template->checkUserPermissions()) {
				unset($templates[$id]);
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $templates;
	}

	public static function getInstanceByModules($tabId, $relTabId)
	{
		\App\Log::trace('Entering ' . __METHOD__ . '(' . $tabId . ',' . $relTabId . ') method ...');
		$row = (new \App\Db\Query())->from(self::$baseTable)->where(['tabid' => $tabId, 'reltabid' => $relTabId])->limit(1)->one();
		if (false === $row) {
			\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

			return false;
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', \App\Module::getModuleName($tabId));
		$mf = new $handlerClass();
		$mf->setData($row);
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $mf;
	}

	public static function getInstanceById($recordId, $moduleName = 'Vtiger')
	{
		\App\Log::trace('Entering ' . __METHOD__ . '(' . $recordId . ',' . $moduleName . ') method ...');
		$mf = Vtiger_Cache::get('MappedFieldsModel', $recordId);
		if ($mf) {
			\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

			return $mf;
		}
		$row = (new App\Db\Query())->from(self::$baseTable)
			->where([self::$baseIndex => $recordId])
			->one();
		if (false === $row) {
			\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

			return false;
		}
		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
		$mf = new $handlerClass();
		$mf->setData($row);
		Vtiger_Cache::set('MappedFieldsModel', $recordId, $mf);
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $mf;
	}

	public function setMapping($mapp = [])
	{
		$this->mapping = $mapp;

		return $this;
	}

	/**
	 * Function to get mapping details.
	 *
	 * @return array list of mapping details
	 */
	public function getMapping()
	{
		\App\Log::trace('Entering ' . __METHOD__ . '() method ...');
		if (!$this->mapping) {
			$dataReader = (new App\Db\Query())->from(self::$mappingTable)->where([self::$mappingIndex => $this->getId()])
				->createCommand()->query();
			$finalMapping = [];
			while ($mappingDetails = $dataReader->read()) {
				$finalMapping[] = [
					'type' => $mappingDetails['type'],
					'default' => $mappingDetails['default'],
					'source' => Settings_MappedFields_Field_Model::getInstance($mappingDetails['source'], $this->getModule(), $mappingDetails['type']),
					'target' => Settings_MappedFields_Field_Model::getInstance($mappingDetails['target'], $this->getRelatedModule(), $mappingDetails['type']),
				];
			}
			$this->mapping = $finalMapping;
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $this->mapping;
	}

	/**
	 * Function returns valuetype of the field filter.
	 *
	 * @param mixed $fieldname
	 *
	 * @return string
	 */
	public function getFieldFilterValueType($fieldname)
	{
		$conditions = $this->get('conditions');
		if (!empty($conditions) && \is_array($conditions)) {
			foreach ($conditions as $filter) {
				if ($fieldname == $filter['fieldname']) {
					return $filter['valuetype'];
				}
			}
		}
		return false;
	}

	public function getName()
	{
		return \App\Module::getModuleName($this->get('tabid'));
	}

	public function getRelatedName()
	{
		return \App\Module::getModuleName($this->get('reltabid'));
	}

	/**
	 * Function to check filters for record.
	 *
	 * @param int $recordId
	 *
	 * @return bool
	 */
	public function checkFiltersForRecord($recordId)
	{
		$key = $this->getId() . '_' . $recordId;
		if (\App\Cache::staticHas(__METHOD__, $key)) {
			return \App\Cache::staticGet(__METHOD__, $key);
		}
		Vtiger_Loader::includeOnce('~/modules/com_vtiger_workflow/VTJsonCondition.php');
		$conditionStrategy = new VTJsonCondition();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$test = $conditionStrategy->evaluate($this->getRaw('conditions'), $recordModel);
		\App\Cache::staticSave(__METHOD__, $key, $test);

		return $test;
	}

	public function checkUserPermissions()
	{
		\App\Log::trace('Entering ' . __METHOD__ . '() method ...');
		$permissions = $this->get('permissions');
		if (empty($permissions)) {
			\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

			return true;
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$permissions = explode(',', $permissions);
		$getTypes = [];
		$return = false;
		foreach ($permissions as $name) {
			$valueType = explode(':', $name);
			$getTypes[$valueType[0]][] = $valueType[1];
		}
		if (\in_array('Users:' . $currentUser->getId(), $permissions)) {
			$return = true;
		} elseif (\in_array('Roles:' . $currentUser->getRole(), $permissions)) {
			$return = true;
		} elseif (\array_key_exists('Groups', $getTypes)) {
			$accessibleGroups = array_keys(\App\Fields\Owner::getInstance($this->get('module_name'), $currentUser)->getAccessibleGroupForModule());
			$groups = array_intersect($getTypes['Groups'], $currentUser->getGroups());
			if (array_intersect($groups, $accessibleGroups)) {
				$return = true;
			}
		}
		if (\array_key_exists('RoleAndSubordinates', $getTypes) && !$return) {
			$roles = $currentUser->getParentRoles();
			$roles[] = $currentUser->getRole();
			if (array_intersect($getTypes['RoleAndSubordinates'], array_filter($roles))) {
				$return = true;
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');

		return $return;
	}
}
