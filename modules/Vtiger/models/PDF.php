<?php

/**
 * Basic PDF Model Class
 * @package YetiForce.PDF
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_PDF_Model extends Vtiger_Base_Model
{

	public static $baseTable = 'a_yf_pdf';
	public static $baseIndex = 'pdfid';
	protected $recordCache = [];
	protected $recordId;
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW'];

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('pdfid');
	}

	/**
	 * Fuction to get the Name of the record
	 * @return <String> - Entity Name of the record
	 */
	public function getName()
	{
		$displayName = $this->get('primary_name');
		return Vtiger_Util_Helper::toSafeHTML(decode_html($displayName));
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

	/**
	 * Get record id for which template is generated
	 * @return <Integer> - id of a main module record
	 */
	public function getMainRecordId()
	{
		if (is_array($this->recordId))
			return reset($this->recordId);
		return $this->recordId;
	}

	/**
	 * Get records id for which template is generated
	 * @return <Array> - ids of a main module record
	 */
	public function getRecordIds()
	{
		return $this->recordId;
	}

	/**
	 * Sets record id for which template will be generated
	 * @param <Integer> $id
	 */
	public function setMainRecordId($id)
	{
		$this->recordId = $id;
	}

	public function getModule()
	{
		return Vtiger_Module_Model::getInstance($this->get('module_name'));
	}

	/**
	 * Check if pdf templates are avauble for this record, user and view
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

		if (!isRecordExists($recordId)) {
			return [];
		}
		if (!$moduleName) {
			$moduleName = vtlib\Functions::getCRMRecordType($recordId);
		}

		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			$template->setMainRecordId($recordId);
			if (!$template->isVisible($view) || !$template->checkFiltersForRecord($recordId) || !$template->checkUserPermissions()) {
				unset($templates[$id]);
			}
		}
		return $templates;
	}

	public function getActiveTemplatesForModule($moduleName, $view)
	{
		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			$active = true;
			if (!$template->isVisible($view) || !$template->checkUserPermissions()) {
				unset($templates[$id]);
			}
		}
		return $templates;
	}

	/**
	 * Returns template records by module name
	 * @param string $moduleName - module name for which template was created
	 * @return array of template record models
	 */
	public static function getTemplatesByModule($moduleName)
	{
		$db = PearDatabase::getInstance();

		$query = sprintf('SELECT * FROM `%s` WHERE `module_name` = ? and `status` = ?;', self::$baseTable);
		$result = $db->pquery($query, [$moduleName, 'active']);
		$templates = [];

		while ($row = $db->fetchByAssoc($result)) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
			$pdf = new $handlerClass();
			$pdf->setData($row);
			$templates[] = $pdf;
		}
		return $templates;
	}

	public static function getInstanceById($recordId, $moduleName = 'Vtiger')
	{
		$pdf = Vtiger_Cache::get('PDFModel', $recordId);
		if ($pdf) {
			return $pdf;
		}
		$db = PearDatabase::getInstance();
		$query = sprintf('SELECT * FROM `%s` WHERE `%s` = ? LIMIT 1', self::$baseTable, self::$baseIndex);
		$result = $db->pquery($query, [$recordId]);
		if ($result->rowCount() == 0) {
			return false;
		}
		$data = $db->fetchByAssoc($result);
		if ($moduleName == 'Vtiger' && isset($data['module_name'])) {
			$moduleName = $data['module_name'];
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdf = new $handlerClass();
		$pdf->setData($data);
		Vtiger_Cache::set('PDFModel', $recordId, $pdf);
		return $pdf;
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

	public function deleteConditions()
	{
		$db = PearDatabase::getInstance();
		$db->update(self::$baseTable, [
			'conditions' => ''
			], self::$baseIndex . ' = ? LIMIT 1', [$this->getId()]
		);
	}

	public function isVisible($view)
	{
		$visibility = explode(',', $this->get('visibility'));
		if (in_array($this->viewToPicklistValue[$view], $visibility)) {
			return true;
		}
		return false;
	}

	public function checkFiltersForRecord($recordId)
	{
		$test = Vtiger_Cache::get('PdfCheckFiltersForRecord' . $this->getId(), $recordId);
		if ($test !== false) {
			return (bool) $test;
		}
		vimport("~/modules/com_vtiger_workflow/VTJsonCondition.inc");
		vimport("~/modules/com_vtiger_workflow/VTEntityCache.inc");
		vimport("~/include/Webservices/Retrieve.php");

		$conditionStrategy = new VTJsonCondition();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$entityCache = new VTEntityCache($currentUser);
		$wsId = vtws_getWebserviceEntityId($this->get('module_name'), $recordId);
		$conditions = htmlspecialchars_decode($this->getRaw('conditions'));
		$test = $conditionStrategy->evaluate($conditions, $entityCache, $wsId);
		Vtiger_Cache::set('PdfCheckFiltersForRecord' . $this->getId(), $recordId, intval($test));
		return $test;
	}

	public function checkUserPermissions()
	{
		$permissions = $this->get('template_members');
		if (empty($permissions)) {
			return true;
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$permissions = explode(',', $permissions);
		$getTypes = [];
		foreach ($permissions as $name) {
			$valueType = explode(':', $name);
			$getTypes[$valueType[0]][] = $valueType[1];
		}
		if (in_array('Users:' . $currentUser->getId(), $permissions)) { // check user id
			return true;
		} elseif (in_array('Roles:' . $currentUser->getRole(), $permissions)) {
			return true;
		} elseif (array_key_exists('Groups', $getTypes)) {
			$accessibleGroups = array_keys(\includes\fields\Owner::getInstance($this->get('module_name'), $currentUser)->getAccessibleGroupForModule());
			$groups = array_intersect($getTypes['Groups'], $currentUser->getGroups());
			if (array_intersect($groups, $accessibleGroups)) {
				return true;
			}
		}
		if (array_key_exists('RoleAndSubordinates', $getTypes)) {
			$roles = $currentUser->getParentRoles();
			$roles[] = $currentUser->getRole();
			if (array_intersect($getTypes['RoleAndSubordinates'], array_filter($roles))) {
				return true;
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

		$content = html_entity_decode($this->get('header_content'));
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

		$content = html_entity_decode($this->get('footer_content'));
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

		$content = html_entity_decode($this->get('body_content'));
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
		if (empty($content)) {
			return $content;
		}
		$recordModule = $this->getRecordModelById($recordId);
		$fieldsModel = $this->getFieldsById($recordId);
		foreach ($fieldsModel as $fieldName => &$fieldModel) {
			$replaceBy = $recordModule->getDisplayValue($fieldName, $recordId, true);
			$content = str_replace('$' . $fieldName . '$', $replaceBy, $content);
			$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $fieldModel->get('label'), $moduleName);
			$content = str_replace('%' . $fieldName . '%', $newLabel, $content);
		}

		return $content;
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

	public function getFieldsById($recordId)
	{
		$moduleModel = $this->recordCache[$recordId]->getModule();
		return $moduleModel->getFields();
	}

	/**
	 * Replaces related module variables with values
	 * @param string $content - text
	 * @param integer $recordId - if od main module record
	 * @return string text with replaced values
	 */
	public function replaceRelatedModuleFields(&$content, $recordId)
	{
		if (empty($content)) {
			return $content;
		}
		$recordModel = $this->getRecordModelById($recordId);
		$fieldsModel = $this->getFieldsById($recordId);
		$fieldsTypes = ['reference', 'owner', 'multireference'];
		foreach ($fieldsModel as $fieldName => &$fieldModel) {
			$fieldType = $fieldModel->getFieldDataType();
			if (in_array($fieldType, $fieldsTypes)) {
				$value = $recordModel->get($fieldName);
				$referenceModules = $fieldModel->getReferenceList();
				if ($fieldType == 'owner')
					$referenceModules = ['Users'];
				foreach ($referenceModules as $module) {
					if ($module == 'Users') {
						$referenceRecordModel = Users_Record_Model::getInstanceById($value, $module);
					} else {
						if (empty($value)) {
							$referenceRecordModel = Vtiger_Record_Model::getCleanInstance($module);
						} else {
							$referenceRecordModel = $this->getRecordModelById($value);
						}
					}
					$moduleModel = $referenceRecordModel->getModule();
					$fields = $moduleModel->getFields();
					foreach ($fields as $referenceFieldName => &$referenceFieldModel) {
						if (empty($value)) {
							$replaceBy = '';
						} else {
							$replaceBy = $referenceRecordModel->getDisplayValue($referenceFieldName, $value, true);
						}
						$content = str_replace('$' . $fieldName . '+' . $module . '+' . $referenceFieldName . '$', $replaceBy, $content);
						$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $referenceFieldModel->get('label'), $module);
						$content = str_replace('%' . $fieldName . '+' . $module . '+' . $referenceFieldName . '%', $newLabel, $content);
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
		if (empty($content)) {
			return $content;
		}
		$companyDetails = getCompanyDetails();

		foreach ($companyDetails as $name => $value) {
			if ($name === 'logoname') {
				$value = 'storage/Logo/' . $value;
			}
			$content = str_replace('$Company+' . $name . '$', $value, $content);

			$newLabel = Vtiger_Language_Handler::getLanguageTranslatedString($this->get('language'), $name, 'Settings:Vtiger');
			$content = str_replace('%Company+' . $name . '%', $newLabel, $content);
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
		if (empty($content)) {
			return $content;
		}
		$moduleName = $this->get('module_name');
		$specialFunctions = self::getSpecialFunctions($moduleName);

		foreach ($specialFunctions as $name => &$sfInstance) {
			if (strpos($content, '#' . $name . '#') !== false) {
				$replaceBy = $sfInstance->process($moduleName, $this->getMainRecordId(), $this);
				$content = str_replace('#' . $name . '#', $replaceBy, $content);
			}
		}

		return $content;
	}

	/**
	 * Return list of special functions for chosen module
	 * @param string $moduleName - name of the module
	 * @return array array of special functions
	 */
	public static function getSpecialFunctions($moduleName)
	{
		$specialFunctions = Vtiger_Cache::get('PdfSpecialFunctions', $moduleName);
		if ($specialFunctions) {
			return $specialFunctions;
		}
		$specialFunctions = [];
		if (file_exists('modules/' . $moduleName . '/pdfs/special_functions')) {
			foreach (new DirectoryIterator('modules/' . $moduleName . '/pdfs/special_functions') as $file) {
				if ($file->isFile() && $file->getExtension() == 'php' && $file->getFilename() != 'example.php') {
					include('modules/' . $moduleName . '/pdfs/special_functions/' . $file->getFilename());
					$functionName = $file->getBasename('.php');
					$sfClassName = 'Pdf_' . $functionName;
					$pdfInstance = new $sfClassName();
					if (in_array('all', $pdfInstance->permittedModules) || in_array($moduleName, $pdfInstance->permittedModules)) {
						$specialFunctions[$functionName] = $pdfInstance;
					}
				}
			}
		}
		foreach (new DirectoryIterator('modules/Vtiger/pdfs/special_functions/') as $file) {
			if ($file->isFile() && $file->getExtension() == 'php' && $file->getFilename() != 'example.php' && !in_array($file->getBasename('.php'), $specialFunctions)) {
				include('modules/Vtiger/pdfs/special_functions/' . $file->getFilename());
				$functionName = $file->getBasename('.php');
				$sfClassName = 'Pdf_' . $functionName;
				$pdfInstance = new $sfClassName();
				if (in_array('all', $pdfInstance->permittedModules) || in_array($moduleName, $pdfInstance->permittedModules)) {
					$specialFunctions[$functionName] = $pdfInstance;
				}
			}
		}
		Vtiger_Cache::set('PdfSpecialFunctions', $moduleName, $specialFunctions);
		return $specialFunctions;
	}

	/**
	 * Export record to PDF file
	 * @param int $recordId - id of a record
	 * @param string $moduleName - name of records module
	 * @param int $templateId - id of pdf template
	 * @param string $filePath - path name for saving pdf file
	 * @param string $saveFlag - save option flag
	 */
	public static function exportToPdf($recordId, $moduleName, $templateId, $filePath = '', $saveFlag = '')
	{
		$handlerClass = Vtiger_Loader::getComponentClassName('Pdf', 'mPDF', $moduleName);
		$pdf = new $handlerClass();
		$pdf->export($recordId, $moduleName, $templateId, $filePath, $saveFlag);
	}

	public static function attachToEmail($salt)
	{
		header('Location: index.php?module=OSSMail&view=compose&pdf_path=' . $salt);
		exit;
	}

	public static function zipAndDownload(array $fileNames)
	{
		$log = vglobal('log');
		//create the object
		$zip = new ZipArchive();

		mt_srand(time());
		$postfix = time() . '_' . mt_rand(0, 1000);
		$zipPath = 'storage/';
		$zipName = "pdfZipFile_{$postfix}.zip";
		$fileName = $zipPath . $zipName;

		//create the file and throw the error if unsuccessful
		if ($zip->open($zipPath . $zipName, ZIPARCHIVE::CREATE) !== true) {
			$log->error("cannot open <$zipPath.$zipName>\n");
			exit(__CLASS__ . ':' . __METHOD__ . " | cannot open <$zipPath.$zipName>\n");
		}

		//add each files of $file_name array to archive
		foreach ($fileNames as $file) {
			$zip->addFile($file, basename($file));
		}
		$zip->close();

		// delete added pdf files
		foreach ($fileNames as $file) {
			unlink($file);
		}
		$mimeType = \includes\fields\File::getMimeContentType($fileName);
		$size = filesize($fileName);
		$name = basename($fileName);

		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Content-Type: $mimeType");
		header('Content-Disposition: attachment; filename="' . $name . '";');
		header("Accept-Ranges: bytes");
		header('Content-Length: ' . $size);

		print readfile($fileName);
		// delete temporary zip file and saved pdf files
		unlink($fileName);
	}
}
