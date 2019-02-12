<?php

/**
 * Basic PDF Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radoslaw Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Vtiger_PDF_Model extends \App\Base
{
	/**
	 * Table name.
	 *
	 * @var string
	 */
	public static $baseTable = 'a_yf_pdf';

	/**
	 * Table index.
	 *
	 * @var string
	 */
	public static $baseIndex = 'pdfid';

	/**
	 * Records cache.
	 *
	 * @var array
	 */
	protected $recordCache = [];

	/**
	 * Current record id.
	 *
	 * @var int
	 */
	protected $recordId;

	/**
	 * View to picklist assigment array.
	 *
	 * @var array
	 */
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW'];

	/**
	 * Function to get watermark type.
	 *
	 * @return array
	 */
	public function getWatermarkType()
	{
		return [0 => 'PLL_TEXT', 1 => 'PLL_IMAGE'];
	}

	/**
	 * Function to get the id of the record.
	 *
	 * @return <Number> - Record Id
	 */
	public function getId()
	{
		return $this->get('pdfid');
	}

	/**
	 * Fuction to get the Name of the record.
	 *
	 * @return string - Entity Name of the record
	 */
	public function getName()
	{
		$displayName = $this->get('primary_name');

		return \App\Purifier::encodeHtml(App\Purifier::decodeHtml($displayName));
	}

	/**
	 *  Return key value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key)
	{
		if ($key === 'conditions' && !is_array(parent::get($key))) {
			return json_decode(parent::get($key), true);
		} else {
			return parent::get($key);
		}
	}

	/**
	 * Return raw key value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getRaw($key)
	{
		return parent::get($key);
	}

	/**
	 * Get record id for which template is generated.
	 *
	 * @return <Integer> - id of a main module record
	 */
	public function getMainRecordId()
	{
		if (is_array($this->recordId)) {
			return reset($this->recordId);
		}
		return $this->recordId;
	}

	/**
	 * Get records id for which template is generated.
	 *
	 * @return <Array> - ids of a main module record
	 */
	public function getRecordIds()
	{
		return $this->recordId;
	}

	/**
	 * Sets record id for which template will be generated.
	 *
	 * @param <Integer> $id
	 */
	public function setMainRecordId($id)
	{
		$this->recordId = $id;
	}

	/**
	 * Return module instance or false.
	 *
	 * @return object|false
	 */
	public function getModule()
	{
		return Vtiger_Module_Model::getInstance($this->get('module_name'));
	}

	/**
	 * Check if pdf templates are avauble for this record, user and view.
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

		if (count($templates) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return available templates for record.
	 *
	 * @param int    $recordId
	 * @param string $view
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function getActiveTemplatesForRecord($recordId, $view, $moduleName = false)
	{
		if (!\App\Record::isExists($recordId)) {
			return [];
		}
		if (!$moduleName) {
			$moduleName = \App\Record::getType($recordId);
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

	/**
	 * Return available templates for module.
	 *
	 * @param string $moduleName
	 * @param string $view
	 *
	 * @return array
	 */
	public function getActiveTemplatesForModule($moduleName, $view)
	{
		$templates = $this->getTemplatesByModule($moduleName);
		foreach ($templates as $id => &$template) {
			if (!$template->isVisible($view) || !$template->checkUserPermissions()) {
				unset($templates[$id]);
			}
		}
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
		$dataReader = (new \App\Db\Query())->from(self::$baseTable)
			->where(['module_name' => $moduleName, 'status' => 1])
			->createCommand()->query();
		$templates = [];
		while ($row = $dataReader->read()) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
			$pdf = new $handlerClass();
			$pdf->setData($row);
			$templates[] = $pdf;
		}
		return $templates;
	}

	/**
	 * Get PDF instance by id.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return Vtiger_PDF_Model|bool
	 */
	public static function getInstanceById($recordId, $moduleName = 'Vtiger')
	{
		$pdf = Vtiger_Cache::get('PDFModel', $recordId);
		if ($pdf) {
			return $pdf;
		}
		$row = (new \App\Db\Query())->from(self::$baseTable)->where([self::$baseIndex => $recordId])->one();
		if ($row === false) {
			return false;
		}
		if ($moduleName == 'Vtiger' && isset($row['module_name'])) {
			$moduleName = $row['module_name'];
		}

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdf = new $handlerClass();
		$pdf->setData($row);
		Vtiger_Cache::set('PDFModel', $recordId, $pdf);
		return $pdf;
	}

	/**
	 * Function returns valuetype of the field filter.
	 *
	 * @return string
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

	/**
	 * Remove conditions for current record.
	 */
	public function deleteConditions()
	{
		\App\Db::getInstance()->createCommand()
			->update(self::$baseTable, [
				'conditions' => '',
			], [self::$baseIndex => $this->getId()])
			->execute();
	}

	/**
	 * Check if is visible for provided view.
	 *
	 * @param string $view
	 *
	 * @return bool
	 */
	public function isVisible($view)
	{
		$visibility = explode(',', $this->get('visibility'));
		if (in_array($this->viewToPicklistValue[$view], $visibility)) {
			return true;
		}
		return false;
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
		$conditions = htmlspecialchars_decode($this->getRaw('conditions'));
		$test = $conditionStrategy->evaluate($conditions, $recordModel);
		\App\Cache::staticSave(__METHOD__, $key, $test);

		return $test;
	}

	/**
	 * Check if user has permissions to record.
	 *
	 * @return bool
	 */
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
			$accessibleGroups = array_keys(\App\Fields\Owner::getInstance($this->get('module_name'), $currentUser)->getAccessibleGroupForModule());
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
	 * Returns array of template parameters understood by the pdf engine.
	 *
	 * @return <Array> - array of parameters
	 */
	public function getParameters()
	{
		$parameters = [];
		$parameters['page_format'] = $this->get('page_format');
		$parameters['page_orientation'] = $this->get('page_orientation');
		$parameters['header_height'] = $this->get('header_height');
		$parameters['footer_height'] = $this->get('footer_height');

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
		$parameters['creator'] = 'YetiForce CRM';
		if ($this->get('metatags_status') == 1) {
			$parameters['title'] = $this->get('meta_title');
			$parameters['author'] = $this->get('meta_author');
			$parameters['subject'] = $this->get('meta_subject');
			$parameters['keywords'] = $this->get('meta_keywords');
		}
		return $parameters;
	}

	/**
	 * Returns page format.
	 *
	 * @return string page format
	 */
	public function getFormat()
	{
		return $this->get('page_format');
	}

	/**
	 * Get page orientation.
	 *
	 * @return string
	 */
	public function getOrientation()
	{
		$orientation = $this->get('page_orientation');
		if ($orientation === 'PLL_LANDSCAPE') {
			return 'L';
		} else {
			return 'P';
		}
	}

	/**
	 * Get header content.
	 *
	 * @return string - header content
	 */
	public function getHeader()
	{
		return $this->get('header_content');
	}

	/**
	 * Get body content.
	 *
	 * @return string - body content
	 */
	public function getFooter()
	{
		return $this->get('footer_content');
	}

	/**
	 * Get body content.
	 *
	 * @return string - body content
	 */
	public function getBody()
	{
		return $this->get('body_content');
	}

	/**
	 * Export record to PDF file.
	 *
	 * @param int    $recordId   - id of a record
	 * @param string $moduleName - name of records module
	 * @param int    $templateId - id of pdf template
	 * @param string $filePath   - path name for saving pdf file
	 * @param string $saveFlag   - save option flag
	 */
	public static function exportToPdf($recordId, $moduleName, $templateId, $filePath = '', $saveFlag = '')
	{
		(new \App\Pdf\YetiForcePDF())->export($recordId, $moduleName, $templateId, $filePath, $saveFlag);
	}

	/**
	 * Attach current record to email.
	 *
	 * @param string $salt
	 */
	public static function attachToEmail($salt)
	{
		header('location: index.php?module=OSSMail&view=Compose&pdf_path=' . $salt);
	}

	/**
	 * Compress files and send to browser.
	 *
	 * @param array $fileNames
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public static function zipAndDownload(array $fileNames)
	{
		//create the object
		$zip = new ZipArchive();

		mt_srand(time());
		$postfix = time() . '_' . random_int(0, 1000);
		$zipPath = 'storage/';
		$zipName = "pdfZipFile_{$postfix}.zip";
		$fileName = $zipPath . $zipName;

		//create the file and throw the error if unsuccessful
		if ($zip->open($zipPath . $zipName, ZIPARCHIVE::CREATE) !== true) {
			\App\Log::error("cannot open <$zipPath.$zipName>\n");
			throw new \App\Exceptions\NoPermitted("cannot open <$zipPath.$zipName>");
		}

		//add each files of $file_name array to archive
		foreach ($fileNames as $file) {
			$zip->addFile($file, basename($file));
		}
		$zip->close();
		$mimeType = \App\Fields\File::getMimeContentType($fileName);
		$size = filesize($fileName);
		$name = basename($fileName);

		header('expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header("content-type: $mimeType");
		header('content-disposition: attachment; filename="' . $name . '";');
		header('accept-ranges: bytes');
		header('content-length: ' . $size);

		readfile($fileName);
		// delete temporary zip file and saved pdf files
		unlink($fileName);
		foreach ($fileNames as $file) {
			unlink($file);
		}
	}
}
