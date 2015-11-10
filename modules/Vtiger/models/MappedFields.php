<?php

/**
 * Basic MappedFields Model Class
 * @package YetiForce.MappedFields
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_MappedFields_Model extends Vtiger_Base_Model
{

	public static $baseTable = 'a_yf_mapped_config';
	public static $mappingTable = 'a_yf_mapped_fields';
	public static $baseIndex = 'id';
	public static $mappingIndex = 'mappedid';
	protected $mapping = [];

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	public function get($key)
	{
		if (in_array($key, ['conditions', 'params']) && !is_array(parent::get($key))) {
			return Zend_Json::decode(html_entity_decode(parent::get($key)));
		} else {
			return parent::get($key);
		}
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
	 * Check if templates are avauble for this record, user and view
	 * @param integer $recordId - id of a record
	 * @param string $moduleName - name of the module
	 * @param string $view - modules view - Detail or List
	 * @return bool true or false
	 */
	public function checkActiveTemplates($recordId, $moduleName, $view)
	{
		$templates = $this->getActiveTemplatesForRecord($recordId, $view, $moduleName);

		if (count($templates) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getActiveTemplatesForRecord($recordId, $view, $moduleName = false)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . $recordId . ',' . $view . ',' . $moduleName . ') method ...');
		if (!isRecordExists($recordId)) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return [];
		}
		if (!$moduleName) {
			$moduleName = Vtiger_Functions::getCRMRecordType($recordId);
		}

		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			$active = true;
			if (!$template->checkFiltersForRecord($recordId) || !$template->checkUserPermissions() || !Users_Privileges_Model::isPermitted($template->getRelatedName(), 'EditView')) {
				unset($templates[$id]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $templates;
	}

	/**
	 * Returns template records by module name
	 * @param string $moduleName - module name for which template was created
	 * @return array of template record models
	 */
	public static function getTemplatesByModule($moduleName)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . $moduleName . ') method ...');
		$db = PearDatabase::getInstance();
		$moduleId = Vtiger_Functions::getModuleId($moduleName);
		$query = 'SELECT * FROM `' . self::$baseTable . '` WHERE `tabid` = ? and `status` = ?;';
		$result = $db->pquery($query, [$moduleId, 'active']);
		$templates = [];

		while ($row = $db->fetchByAssoc($result)) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
			$mf = new $handlerClass();
			$mf->setData($row);
			$templates[$mf->getId()] = $mf;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $templates;
	}

	public function getActiveTemplatesForModule($moduleName, $view)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . $moduleName . ',' . $view . ') method ...');
		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			$active = true;
			if (!$template->checkUserPermissions()) {
				unset($templates[$id]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $templates;
	}

	public static function getInstanceByModules($tabId, $relTabId)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . $tabId . ',' . $relTabId . ') method ...');
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM `' . self::$baseTable . '` WHERE `tabid` = ? AND `reltabid` = ? LIMIT 1;';
		$result = $db->pquery($query, [$tabId, $relTabId]);
		if ($result->rowCount() == 0) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return false;
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
		$mf = new $handlerClass();
		$mf->setData($db->fetchByAssoc($result));
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $mf;
	}

	public static function getInstanceById($recordId, $moduleName = 'Vtiger')
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . $recordId . ',' . $moduleName . ') method ...');
		$mf = Vtiger_Cache::get('MappedFieldsModel', $recordId);
		if ($mf) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return $mf;
		}
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM `' . self::$baseTable . '` WHERE `' . self::$baseIndex . '` = ? LIMIT 1;';
		$result = $db->pquery($query, [$recordId]);
		if ($result->rowCount() == 0) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return false;
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
		$mf = new $handlerClass();
		$mf->setData($db->fetchByAssoc($result));
		Vtiger_Cache::set('MappedFieldsModel', $recordId, $mf);
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $mf;
	}

	public function setMapping($mapp = [])
	{
		$this->mapping = $mapp;
		return $this;
	}

	/**
	 * Function to get mapping details
	 * @return <Array> list of mapping details
	 */
	public function getMapping()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '() method ...');
		if (!$this->mapping) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT * FROM ' . self::$mappingTable . ' WHERE ' . self::$mappingIndex . ' = ?;';
			$result = $db->pquery($query, [$this->getId()]);
			$mapping = $db->getArray($result);
			$finalMapping = [];
			if ($mapping) {
				foreach ($mapping as $mappingId => $mappingDetails) {
					$finalMapping[$mappingId] = [
						'default' => $mappingDetails['default'],
						'source' => Vtiger_Field_Model::getInstanceFromFieldId($mappingDetails['source']),
						'target' => Vtiger_Field_Model::getInstanceFromFieldId($mappingDetails['target'])
					];
				}
			}
			$this->mapping = $finalMapping;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $this->mapping;
	}

	/**
	 * Function returns valuetype of the field filter
	 * @return <String>
	 */
	public function getFieldFilterValueType($fieldname)
	{
		$conditions = $this->get('conditions');
		if (!empty($conditions) && is_array($conditions)) {
			foreach ($conditions as $filter) {
				if ($fieldname == $filter['fieldname']) {
					return $filter['valuetype'];
				}
			}
		}
		return false;
	}

	public function getGenerateModalView()
	{
		return 'index.php?module=Vtiger&view=GenerateModal';
	}

	public function getName()
	{
		return Vtiger_Functions::getModuleName($this->get('tabid'));
	}

	public function getRelatedName()
	{
		return Vtiger_Functions::getModuleName($this->get('reltabid'));
	}

	public function checkFiltersForRecord($recordId)
	{
		$test = Vtiger_Cache::get('mfCheckFiltersForRecord' . $this->getId(), $recordId);
		if ($test !== false) {
			return $test;
		}
		vimport("~/modules/com_vtiger_workflow/VTJsonCondition.inc");
		vimport("~/modules/com_vtiger_workflow/VTEntityCache.inc");
		vimport("~/include/Webservices/Retrieve.php");

		$conditionStrategy = new VTJsonCondition();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$entityCache = new VTEntityCache($currentUser);
		$wsId = vtws_getWebserviceEntityId($this->getName(), $recordId);
		$test = $conditionStrategy->evaluate($this->getRaw('conditions'), $entityCache, $wsId);
		Vtiger_Cache::set('mfCheckFiltersForRecord' . $this->getId(), $recordId, $test);
		return $test;
	}

	public function checkUserPermissions()
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '() method ...');
		$permissions = $this->get('permissions');
		if (empty($permissions)) {
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return true;
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$permissions = explode(',', $permissions);

		if (in_array('Users:' . $currentUser->getId(), $permissions)) { // check user id
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
			return true;
		} else {
			$userGroups = new GetUserGroups();
			$userGroups->getAllUserGroups($currentUser->getId());
			foreach ($userGroups->user_groups as $group) {
				if (in_array('Groups:' . $group, $permissions)) {
					$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
					return true;
				}
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return false;
	}
}
