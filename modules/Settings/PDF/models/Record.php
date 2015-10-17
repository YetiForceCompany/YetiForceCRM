<?php

/**
 * Record Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Record_Model extends Settings_Vtiger_Record_Model
{

	protected $recordCache = [];
	protected $fieldsCache = [];
	protected $moduleRecordId;
	protected $referenceUiTypes = ['10', '58', '51', '57', '59', '75', '80', '76', '73', '81', '53', '52', '78'];

	public function getId()
	{
		return $this->get('pdfid');
	}

	public function getName()
	{
		return $this->get('summary');
	}

	public function get($key)
	{
		if ($key === 'conditions' && !is_array(parent::get($key))) {
			return json_decode(parent::get($key), true);
		} else {
			return parent::get($key);
		}
	}

	public function getRaw($key)
	{
		return parent::get($key);
	}

	public function getEditViewUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=Edit&record=' . $this->getId();
	}

	public function getModule()
	{
		return $this->module;
	}

	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = [];

		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EXPORT_RECORD',
				'linkurl' => 'index.php?module=PDF&parent=Settings&action=ExportTemplate&id=' . $this->getId(),
				'linkicon' => 'glyphicon glyphicon-export'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => '#',
				'linkicon' => 'glyphicon glyphicon-trash'
			]
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	public static function getInstanceById($recordId)
	{
		$db = PearDatabase::getInstance();
		$moduleModel = Settings_Vtiger_Module_Model::getInstance('Settings:PDF');

		$query = 'SELECT `' . $moduleModel->baseIndex . '`,`' . implode('`,`', Settings_PDF_Module_Model::$allFields) . '` FROM `' . $moduleModel->baseTable . '` WHERE `' . $moduleModel->baseIndex . '` = ? LIMIT 1;';
		$result = $db->pquery($query, [$recordId]);

		if ($db->num_rows($result) == 0) {
			return false;
		}

		$row = $db->fetchByAssoc($result);

		$pdf = new self;
		$pdf->setData($row);

		return $pdf;
	}

	public static function getCleanInstance()
	{
		$pdf = new self;
		$data = [];
		$fields = Settings_PDF_Module_Model::getFieldsByStep();
		foreach ($fields as $field) {
			$data[$field] = '';
		}
		$pdf->setData($data);
		return $pdf;
	}

	public function save($step = 1)
	{
		$db = PearDatabase::getInstance();

		switch ($step) {
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				$params = [];
				$fields = [];
				foreach ($stepFields as $field) {
					if ($field === 'conditions') {
						$params[] = json_encode($this->get($field));
					} else {
						$params[] = $this->get($field);
					}
					$fields[] = "`$field` = ?";
				}

				$params[] = $this->getId();

				$query = 'UPDATE `a_yf_pdf` SET ' . implode(',', $fields) . ' WHERE `pdfid` = ? LIMIT 1;';
				$result = $db->pquery($query, $params);
				return $this->get('pdfid');

			case 1:
				$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
				if (!$this->getId()) {
					$params = [];
					foreach ($stepFields as $field) {
						$params[$field] = $this->get($field);
					}
					$db->insert('a_yf_pdf', $params);

					$this->set('pdfid', $db->getLastInsertID());
				} else {
					$params = [];
					$fields = [];
					foreach ($stepFields as $field) {
						$params[] = $this->get($field);
						$fields[] = "`$field` = ?";
					}

					$params[] = $this->getId();
					$query = 'UPDATE `a_yf_pdf` SET ' . implode(',', $fields) . ' WHERE `pdfid` = ? LIMIT 1;';
					$result = $db->pquery($query, $params);
				}
				return $this->get('pdfid');

			case 'import':
				$allFields = Settings_PDF_Module_Model::$allFields;
				$params = [];
				foreach ($allFields as $field) {
					if ($field === 'conditions') {
						$params[$field] = json_encode($this->get($field));
					} else {
						$params[$field] = $this->get($field);
					}
				}
				$db->insert('a_yf_pdf', $params);

				$this->set('pdfid', $db->getLastInsertID());
				return $this->get('pdfid');
		}
	}

	public function import()
	{
		$this->save('import');
	}

	public function delete()
	{
		$db = PearDatabase::getInstance();
		return $db->delete('a_yf_pdf', '`pdfid` = ?', [$this->getId()]);
	}

	/**
	 * Function returns valuetype of the field filter
	 * @return <String>
	 */
	function getFieldFilterValueType($fieldname)
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

	/**
	 * Function transforms Advance filter to workflow conditions
	 */
	function transformAdvanceFilterToWorkFlowFilter()
	{
		$conditions = $this->get('conditions');
		$wfCondition = array();

		if (!empty($conditions)) {
			foreach ($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if ($index == '1' && empty($columns)) {
					$wfCondition[] = array('fieldname' => '', 'operation' => '', 'value' => '', 'valuetype' => '',
						'joincondition' => '', 'groupid' => '0');
				}
				if (!empty($columns) && is_array($columns)) {
					foreach ($columns as $column) {
						$wfCondition[] = array('fieldname' => $column['columnname'], 'operation' => $column['comparator'],
							'value' => $column['value'], 'valuetype' => $column['valuetype'], 'joincondition' => $column['column_condition'],
							'groupjoin' => $condition['condition'], 'groupid' => $column['groupid']);
					}
				}
			}
		}
		$this->set('conditions', $wfCondition);
	}

	function transformToAdvancedFilterCondition($conditions = false)
	{
		if (!$conditions) {
			$conditions = $this->get('conditions');
		}
		$transformedConditions = array();

		if (!empty($conditions)) {
			foreach ($conditions as $info) {
				if (!($info['groupid'])) {
					$firstGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				} else {
					$secondGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				}
			}
		}
		$transformedConditions[1] = array('columns' => $firstGroup);
		$transformedConditions[2] = array('columns' => $secondGroup);
		return $transformedConditions;
	}

	function deleteWatermark()
	{
		$db = PearDatabase::getInstance();
		$watermarkImage = $this->get('watermark_image');

		$query = 'UPDATE `a_yf_pdf` SET `watermark_image` = ? WHERE `pdfid` = ? LIMIT 1;';
		$db->pquery($query, ['', $this->getId()]);

		if (file_exists($watermarkImage)) {
			return unlink($watermarkImage);
		}

		return false;
	}

	public function deleteConditions()
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE `a_yf_pdf` SET `conditions` = "" WHERE `pdfid` = ? LIMIT 1;';
		$db->pquery($query, [$this->getId()]);
	}

	public function checkFiltersForRecord($recordId)
	{
		vimport("~/modules/com_vtiger_workflow/VTJsonCondition.inc");
		vimport("~/modules/com_vtiger_workflow/VTEntityCache.inc");
		vimport("~/include/Webservices/Retrieve.php");

		$conditionStrategy = new VTJsonCondition();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$entityCache = new VTEntityCache($currentUser);
		$wsId = vtws_getWebserviceEntityId($this->get('module_name'), $recordId);

		$conditions = htmlspecialchars_decode($this->getRaw('conditions'));
		return $conditionStrategy->evaluate($conditions, $entityCache, $wsId);
	}

	public function checkUserPermissions($userId, $userGroups)
	{
		$permissions = $this->get('template_members');

		if (empty($permissions)) {
			return true;
		}

		$permissions = explode(',', $this->get('template_members'));

		if (in_array('Users:' . $userId, $permissions)) { // check user id
			return true;
		} else {
			foreach ($userGroups as $group) {
				if (in_array('Groups:' . $group, $permissions)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns array of template parameters understood by the pdf engine
	 * @return <Array> - array of parameters
	 */
	public function getParameters()
	{
		$parameters = [];
		$parameters['page_format'] = $this->get('page_format');
		$parameters['page_orientation'] = $this->get('page_orientation');
		// margins
		if ($this->get('margin_chkbox') == 0) {
			$parameters['margin-top'] = $this->get('margin_top');
			$parameters['margin-right'] = $this->get('margin_right');
			$parameters['margin-bottom'] = $this->get('margin_bottom');
			$parameters['margin-left'] = $this->get('margin_left');
		} else {
			$parameters['margin-top'] = '';
			$parameters['margin-right'] = '';
			$parameters['margin-bottom'] = '';
			$parameters['margin-left'] = '';
		}

		// metadata
		if ($this->get('metatags_status') == 0) {
			$parameters['title'] = $this->get('meta_title');
			$parameters['author'] = $this->get('meta_author');
			$parameters['creator'] = $this->get('meta_creator');
			$parameters['subject'] = $this->get('meta_subject');
			$parameters['keywords'] = $this->get('meta_keywords');
		} else {
			$companyDetails = getCompanyDetails();
			$parameters['title'] = $this->get('primary_name');
			$parameters['author'] = $companyDetails['organizationname'];
			$parameters['creator'] = $companyDetails['organizationname'];
			$parameters['subject'] = $this->get('secondary_name');

			// preparing keywords
			unset($companyDetails['organization_id']);
			unset($companyDetails['logo']);
			unset($companyDetails['logoname']);
			$parameters['keywords'] = implode(', ', $companyDetails);
		}

		return $parameters;
	}

	/**
	 * Sets record id for which template will be generated
	 * @param <Integer> $id
	 */
	public function setMainRecordId($id)
	{
		$this->moduleRecordId = $id;
	}

	/**
	 * Get record id for which template is generated
	 * @return <Integer> - id of a main module record
	 */
	public function getMainRecordId()
	{
		return $this->moduleRecordId;
	}

	/**
	 * Get cached record model by id
	 * @param <Integer> $recordId - id of a record
	 * @return <Vtiger_Record_Model> record module model
	 */
	public function getRecordModelById($recordId)
	{
		if (array_key_exists($recordId, $this->recordCache)) {
			return $this->recordCache[$recordId];
		}

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);

		$this->recordCache[$recordId] = &$recordModel;

		return $this->recordCache[$recordId];
	}

	/**
	 * Get all field instances related to module
	 * @param <Vtiger_Module> - instance of module to use
	 * @return <Array> array of field instances for this module model
	 */
	static function getFieldsForModule(&$moduleInstance)
	{
		$db = PearDatabase::getInstance();
		$instances = false;

		$query = 'SELECT * FROM `vtiger_field` WHERE `tabid` = ?;';
		$result = $db->pquery($query, [$moduleInstance->getId()]);
		while ($row = $db->fetchByAssoc($result)) {
			$instance = new Vtiger_Field();
			$instance->initialize($row, $moduleInstance);
			$instances[$instance->name] = $instance;
		}
		return $instances;
	}

	/**
	 * Get list of field names by module
	 * @param <String> $moduleName - name of module
	 * @return <Array> of field names
	 */
//	public function getFieldNamesForModule($moduleName) {
//		$db = PearDatabase::getInstance();
//		$tabId = getTabid($moduleName);
//		$fields = false;
//
//		$query = 'SELECT `fieldname` FROM `vtiger_field` WHERE `tabid` = ?;';
//
//		$result = $db->pquery($query, [$tabId]);
//		while($row = $db->fetchByAssoc($result)) {
//			$fields[] = $row['fieldname'];
//		}
//		return $fields;
//	}

	public function getFieldsById($recordId)
	{
		$moduleModel = $this->recordCache[$recordId]->getModule();
		$moduleName = $moduleModel->getName();

		if ($this->fieldsCache[$moduleName]) {
			return $this->fieldsCache[$moduleName];
		}

		$this->fieldsCache[$moduleName] = $this->getFieldsForModule($moduleModel);

		return $this->fieldsCache[$moduleName];
	}

	/**
	 * Returns list of reference fields
	 * @param <Array> $fields - array of module fields models
	 * @return <Array> list of reference fields
	 */
	public function getReferenceFields(array &$fields)
	{
		$reference = [];
		foreach ($fields as &$field) {
			if (in_array($field->uitype, $this->referenceUiTypes)) {
				$id = $field->id;
				$name = $field->name;
				$uiType = $field->uitype;
				$reference[$name] = Settings_PDF_Module_Model::getReferencedModuleName($uiType, $id);
			}
		}

		return $reference;
	}

	/**
	 * Get header content
	 * @param bool $raw - if true return unparsed header
	 * @return string - header content
	 */
	public function getHeader($raw = false)
	{
		if ($raw) {
			return $this->get('header_content');
		}
		$recordId = $this->getMainRecordId();
		$moduleName = $this->get('module_name');

		$content = $this->get('header_content');
		$content = $this->replaceModuleFields($content, $recordId, $moduleName);
		$content = $this->replaceRelatedModuleFields($content, $recordId);
		$content = $this->replaceCompanyFields($content);
		$content = $this->replaceSpecialFunctions($content);

		return $content;
	}

	/**
	 * Get body content
	 * @param bool $raw - if true return unparsed header
	 * @return string - body content
	 */
	public function getBody($raw = false)
	{
		if ($raw) {
			return $this->get('body_content');
		}
		$recordId = $this->getMainRecordId();
		$moduleName = $this->get('module_name');

		$content = $this->get('body_content');
		$content = $this->replaceModuleFields($content, $recordId, $moduleName);
		$content = $this->replaceRelatedModuleFields($content, $recordId);
		$content = $this->replaceCompanyFields($content);
		$content = $this->replaceSpecialFunctions($content);

		return $content;
	}

	/**
	 * Get body content
	 * @param bool $raw - if true return unparsed header
	 * @return string - body content
	 */
	public function getFooter($raw = false)
	{
		if ($raw) {
			return $this->get('footer_content');
		}
		$recordId = $this->getMainRecordId();
		$moduleName = $this->get('module_name');

		$content = $this->get('footer_content');
		$content = $this->replaceModuleFields($content, $recordId, $moduleName);
		$content = $this->replaceRelatedModuleFields($content, $recordId);
		$content = $this->replaceCompanyFields($content);
		$content = $this->replaceSpecialFunctions($content);

		return $content;
	}

	/**
	 * Replaces main module variables with values
	 * @param string $content - text
	 * @param integer $recordId - if od main module record
	 * @param string $moduleName - main module name
	 * @return string text with replaced values
	 */
	public function replaceModuleFields(&$content, $recordId, $moduleName)
	{
		$recordModule = $this->getRecordModelById($recordId);
		$fieldsModel = $this->getFieldsById($recordId);

		foreach ($fieldsModel as $name => &$field) {
			if (in_array($field->uitype, $this->referenceUiTypes)) {
				if ($field->uitype == '53') {
					$replaceBy = trim(getUserFullName($recordModule->get('assigned_user_id')));
				} else {
					$replaceBy = Vtiger_Functions::getCRMRecordLabel($recordModule->get($name));
				}
			} else {
				$replaceBy = $recordModule->getDisplayValue($name);
			}
			$content = str_replace('$' . $name . '$', $replaceBy, $content);
			$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $field->label, $moduleName);
			$content = str_replace('%' . $name . '%', $newLabel, $content);
		}

		return $content;
	}

	/**
	 * Replaces related module variables with values
	 * @param string $content - text
	 * @param integer $recordId - if od main module record
	 * @return string text with replaced values
	 */
	public function replaceRelatedModuleFields(&$content, $recordId)
	{
		$recordModule = $this->getRecordModelById($recordId);
		$fieldsModel = $this->getFieldsById($recordId);
		$referenceFields = $this->getReferenceFields($fieldsModel);

		// loop thrue related modules
		foreach ($referenceFields as $fieldName => $modules) {
			$value = $recordModule->get($fieldName);
			if ($modules == 'Users') {
				$referenceRecord = Users_Record_Model::getInstanceById($value, 'Users');

				if (!$referenceRecord) {
					continue;
				}
				$moduleModel = $referenceRecord->getModule();
				$referenceFieldsModel = $this->getFieldsForModule($moduleModel);
			}  // module with records
			elseif (!isRecordExists($value)) {
				continue;
			} else {
				$referenceRecord = $this->getRecordModelById($value);
				$referenceFieldsModel = $this->getFieldsById($referenceRecord->getId());
			}
			$moduleName = $referenceRecord->getModuleName();

			foreach ($referenceFieldsModel as $name => &$field) {
				if (in_array($field->uitype, $this->referenceUiTypes)) {
					if ($field->uitype == '53') {
						$replaceBy = trim(getUserFullName($referenceRecord->get('assigned_user_id')));
					} else {
						$replaceBy = Vtiger_Functions::getCRMRecordLabel($referenceRecord->get($name));
					}
				} else {
					$replaceBy = $referenceRecord->getDisplayValue($name);
				}
				$content = str_replace('$' . $moduleName . '_' . $name . '$', $replaceBy, $content);
				$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $field->label, $moduleName);
				$content = str_replace('%' . $moduleName . '_' . $name . '%', $newLabel, $content);
			}
			if (is_array($modules)) {
				unset($modules[$moduleName]);
				foreach ($modules as $moduleName) {
					$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
					$moduleFields = $this->getFieldsForModule($moduleModel);
					foreach ($moduleFields as $name => &$field) {
						$content = str_replace('$' . $moduleName . '_' . $name . '$', '', $content);
						$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $field->label, $moduleName);
						$content = str_replace('%' . $moduleName . '_' . $name . '%', $newLabel, $content);
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Replaces Company details variables with values
	 * @param string $content - text
	 * @return string text with replaced values
	 */
	public function replaceCompanyFields(&$content)
	{
		$companyDetails = getCompanyDetails();

		foreach ($companyDetails as $name => $value) {
			if ($name === 'logoname') {
				$value = 'storage/Logo/' . $value;
			}
			$content = str_replace('$Company_' . $name . '$', $value, $content);

			$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $name, 'Settings:Vtiger');
			$content = str_replace('%Company_' . $name . '%', $newLabel, $content);
		}

		return $content;
	}

	/**
	 * Replaces special functions with their returned values
	 * @param string $content - text of content
	 * @return string $content - text with replaced values
	 */
	public function replaceSpecialFunctions(&$content)
	{
		$moduleName = $this->get('module_name');
		$specialFunctions = Settings_PDF_Module_Model::getSpecialFunctions($moduleName);

		foreach ($specialFunctions as $specialFunction => $function) {
			if (strpos($content, '#' . $specialFunction . '#') !== false && file_exists('modules/Settings/PDF/special_functions/' . $function . '.php')) {
				include('modules/Settings/PDF/special_functions/' . $specialFunction . '.php');
				$replaceBy = $specialFunction($moduleName, $this->getMainRecordId());
				$content = str_replace('#' . $specialFunction . '#', $replaceBy, $content);
			}
		}

		return $content;
	}

	/**
	 * Returns page format
	 * @return string page format
	 */
	public function getFormat()
	{
		$format = $this->get('page_format');
		$orientation = $this->get('page_orientation');
		if ($orientation === 'PLL_LANDSCAPE') {
			$format .= '-L';
		} else {
			$format .= '-P';
		}
		return $format;
	} 
}
