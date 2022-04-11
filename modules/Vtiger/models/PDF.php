<?php

/**
 * Basic PDF Model Class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radoslaw Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Vtiger_PDF_Model extends \App\Base
{
	/**
	 * Template type standard.
	 *
	 * @var int
	 */
	public const TEMPLATE_TYPE_STANDARD = 0;
	/**
	 * Template type summary.
	 *
	 * @var int
	 */
	public const TEMPLATE_TYPE_SUMMARY = 1;
	/**
	 * Template type dynamic.
	 *
	 * @var int
	 */
	public const TEMPLATE_TYPE_DYNAMIC = 2;

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
	 * Variables.
	 *
	 * @var array
	 */
	protected $variables = [];

	/**
	 * View to picklist assignment array.
	 *
	 * @var array
	 */
	protected $viewToPicklistValue = ['Detail' => 'PLL_DETAILVIEW', 'List' => 'PLL_LISTVIEW', 'RelatedList' => 'PLL_RELATEDLISTVIEW'];

	/**
	 * Custom columns.
	 *
	 * @var bool
	 */
	public static $customColumns = false;

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
		if ('conditions' === $key && ($value = parent::get($key)) && !\is_array($value)) {
			return json_decode($value, true);
		}
		return parent::get($key);
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
	 * Sets custom variable.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setVariable(string $key, $value)
	{
		$this->variables[$key] = $value;
		return $this;
	}

	/**
	 * Gets custom variable.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getVariable(string $key)
	{
		return $this->variables[$key] ?? null;
	}

	/**
	 * Return module instance or false.
	 *
	 * @return false|object
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

		if (\count($templates) > 0) {
			return true;
		}
		return false;
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
		foreach ($templates as $id => $template) {
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
			$templates[$pdf->getId()] = $pdf;
		}
		return $templates;
	}

	/**
	 * Get PDF instance by id.
	 *
	 * @param int $recordId
	 *
	 * @return bool|Vtiger_PDF_Model
	 */
	public static function getInstanceById(int $recordId)
	{
		$pdf = false;
		$cache = __CLASS__;
		if (\App\Cache::has($cache, $recordId)) {
			$pdf = \App\Cache::get($cache, $recordId);
		} else {
			$row = (new \App\Db\Query())->from(self::$baseTable)->where([self::$baseIndex => $recordId])->one();
			if ($row) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $row['module_name']);
				$pdf = new $handlerClass();
				$pdf->setData($row);
			}
			\App\Cache::save($cache, $recordId, $pdf);
		}
		return $pdf ? clone $pdf : $pdf;
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
		return \in_array($this->viewToPicklistValue[$view], $visibility);
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
		$test = \App\Json::isEmpty($this->getRaw('conditions'));
		if (!$test) {
			Vtiger_Loader::includeOnce('~/modules/com_vtiger_workflow/VTJsonCondition.php');
			$conditionStrategy = new VTJsonCondition();
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$conditions = htmlspecialchars_decode($this->getRaw('conditions'));
			$test = $conditionStrategy->evaluate($conditions, $recordModel);
		}
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
		if (\in_array('Users:' . $currentUser->getId(), $permissions)) { // check user id
			return true;
		}
		if (\in_array('Roles:' . $currentUser->getRole(), $permissions)) {
			return true;
		}
		if (\array_key_exists('Groups', $getTypes)) {
			$accessibleGroups = array_keys(\App\Fields\Owner::getInstance($this->get('module_name'), $currentUser)->getAccessibleGroupForModule());
			$groups = array_intersect($getTypes['Groups'], $currentUser->getGroups());
			if (array_intersect($groups, $accessibleGroups)) {
				return true;
			}
		}
		if (\array_key_exists('RoleAndSubordinates', $getTypes)) {
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
	 * @return array
	 */
	public function getParameters()
	{
		$parameters = [];
		$parameters['page_format'] = $this->get('page_format');
		$parameters['page_orientation'] = $this->getOrientation();
		$parameters['header_height'] = $this->get('header_height');
		$parameters['footer_height'] = $this->get('footer_height');

		// margins
		if (0 == $this->get('margin_chkbox')) {
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
		if (1 === (int) $this->get('metatags_status')) {
			$parameters['title'] = $this->parseVariables($this->get('meta_title'));
			$parameters['author'] = $this->parseVariables($this->get('meta_author'));
			$parameters['subject'] = $this->parseVariables($this->get('meta_subject'));
			$parameters['keywords'] = $this->parseVariables($this->get('meta_keywords'));
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
		if ('PLL_LANDSCAPE' === $orientation) {
			return 'L';
		}
		return 'P';
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
	 * Gets TextParser.
	 *
	 * @return \App\TextParser
	 */
	public function getParser()
	{
		if (!isset($this->textParser)) {
			if (isset($this->variables['recordId'])) {
				$this->textParser = \App\TextParser::getInstanceById($this->variables['recordId'], $this->get('module_name'));
			} else {
				$this->textParser = \App\TextParser::getInstance($this->get('module_name'));
			}
			if ($this->get('language')) {
				$this->textParser->setLanguage($this->get('language'));
			}
			$this->textParser->setType('pdf');
			$this->textParser->useExtension = true;
			$this->textParser->setParams(['pdf' => $this]);
		} elseif (($this->variables['recordId'] ?? null) !== $this->textParser->record) {
			$this->textParser = null;
			$this->textParser = $this->getParser();
		}
		return $this->textParser;
	}

	/**
	 * Parse variables.
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public function parseVariables(string $str)
	{
		return $str ? $this->getParser()->setContent($str)->parse()->getContent() : '';
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
		$zipPath = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR;
		$tmpFileName = tempnam($zipPath, 'PDFZIP' . time());
		$zipPath .= basename($tmpFileName);

		$zip = \App\Zip::createFile($zipPath);
		foreach ($fileNames as $file) {
			$zip->addFile($file['path'], $file['name']);
		}

		$zip->download('PdfZipFile_' . time());
		foreach ($fileNames as $file) {
			unlink($file['path']);
		}
	}

	/**
	 * Gets path.
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function getPath(string $prefix = '')
	{
		return \App\Fields\File::createTempFile($prefix, 'pdf');
	}
}
