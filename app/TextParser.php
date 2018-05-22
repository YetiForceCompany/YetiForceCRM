<?php

namespace App;

/**
 * Text parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class TextParser
{
	/**
	 * Examples of supported variables.
	 *
	 * @var array
	 */
	public static $variableExamples = [
		'LBL_ORGANIZATION_NAME' => '$(organization : name)$',
		'LBL_ORGANIZATION_LOGO' => '$(organization : mailLogo)$',
		'LBL_EMPLOYEE_NAME' => '$(employee : last_name)$',
		'LBL_CRM_DETAIL_VIEW_URL' => '$(record : CrmDetailViewURL)$',
		'LBL_PORTAL_DETAIL_VIEW_URL' => '$(record : PortalDetailViewURL)$',
		'LBL_RECORD_ID' => '$(record : RecordId)$',
		'LBL_RECORD_LABEL' => '$(record : RecordLabel)$',
		'LBL_LIST_OF_CHANGES_IN_RECORD' => '$(record : ChangesListChanges)$',
		'LBL_LIST_OF_NEW_VALUES_IN_RECORD' => '$(record : ChangesListValues)$',
		'LBL_RECORD_COMMENT' => '$(record : Comments 5)$, $(record : Comments)$',
		'LBL_RELATED_RECORD_LABEL' => '$(relatedRecord : parent_id|email1|Accounts)$, $(relatedRecord : parent_id|email1)$',
		'LBL_OWNER_EMAIL' => '$(relatedRecord : assigned_user_id|email1|Users)$',
		'LBL_SOURCE_RECORD_LABEL' => '$(sourceRecord : RecordLabel)$',
		'LBL_CUSTOM_FUNCTION' => '$(custom : ContactsPortalPass)$',
		'LBL_RELATED_RECORDS_LIST' => '$(relatedRecordsList : Contacts|firstname,lastname,email|[[["firstname","a","Tom"]]]||5)$',
		'LBL_RECORDS_LIST' => '$(recordsList : Contacts|firstname,lastname,email|[[["firstname","a","Tom"]]]||5)$',
	];

	/**
	 * Default date list.
	 *
	 * @var string[]
	 */
	public static $variableDates = [
		'LBL_DATE_TODAY' => '$(date : now)$',
		'LBL_DATE_TOMORROW' => '$(date : tomorrow)$',
		'LBL_DATE_YESTERDAY' => '$(date : yesterday)$',
		'LBL_DATE_FIRST_DAY_OF_THIS_WEEK' => '$(date : monday this week)$',
		'LBL_DATE_MONDAY_NEXT_WEEK' => '$(date : monday next week)$',
		'LBL_DATE_FIRST_DAY_OF_THIS_MONTH' => '$(date : first day of this month)$',
		'LBL_DATE_LAST_DAY_OF_THIS_MONTH' => '$(date : last day of this month)$',
		'LBL_DATE_FIRST_DAY_OF_NEXT_MONTH' => '$(date : first day of next month)$',
	];

	/**
	 * Variables for entity modules.
	 *
	 * @var array
	 */
	public static $variableGeneral = [
		'LBL_CURRENT_DATE' => '$(general : CurrentDate)$',
		'LBL_CURRENT_TIME' => '$(general : CurrentTime)$',
		'LBL_BASE_TIMEZONE' => '$(general : BaseTimeZone)$',
		'LBL_USER_TIMEZONE' => '$(general : UserTimeZone)$',
		'LBL_SITE_URL' => '$(general : SiteUrl)$',
		'LBL_PORTAL_URL' => '$(general : PortalUrl)$',
		'LBL_TRANSLATE' => '$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$, $(translate : LBL_SECONDS)$',
	];

	/**
	 * Variables for entity modules.
	 *
	 * @var array
	 */
	public static $variableEntity = [
		'CrmDetailViewURL' => 'LBL_CRM_DETAIL_VIEW_URL',
		'PortalDetailViewURL' => 'LBL_PORTAL_DETAIL_VIEW_URL',
		'RecordId' => 'LBL_RECORD_ID',
		'RecordLabel' => 'LBL_RECORD_LABEL',
		'ChangesListChanges' => 'LBL_LIST_OF_CHANGES_IN_RECORD',
		'ChangesListValues' => 'LBL_LIST_OF_NEW_VALUES_IN_RECORD',
		'Comments' => 'LBL_RECORD_COMMENT',
	];

	/**
	 * List of available functions.
	 *
	 * @var string[]
	 */
	protected static $baseFunctions = ['general', 'translate', 'record', 'relatedRecord', 'sourceRecord', 'organization', 'employee', 'params', 'custom', 'relatedRecordsList', 'recordsList', 'date'];

	/**
	 * List of source modules.
	 *
	 * @var string[]
	 */
	public static $sourceModules = [
		'Campaigns' => ['Leads', 'Accounts', 'Contacts', 'Vendors', 'Partners', 'Competition'],
	];
	private static $recordVariable;
	private static $relatedVariable;

	/**
	 * Record id.
	 *
	 * @var int
	 */
	public $record;

	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName;

	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	public $recordModel;

	/**
	 * Parser type.
	 *
	 * @var string|null
	 */
	public $type;

	/**
	 * Source record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected $sourceRecordModel;

	/**
	 * Content.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Rwa content.
	 *
	 * @var string
	 */
	protected $rawContent;

	/**
	 * without translations.
	 *
	 * @var bool
	 */
	protected $withoutTranslations = false;

	/**
	 * Language content.
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * Additional params.
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * Separator to display data when there are several values.
	 *
	 * @var string
	 */
	public $relatedRecordSeparator = ',';

	/**
	 * Is the parsing text content html?
	 *
	 * @var bool
	 */
	public $isHtml = true;

	/**
	 * Get instanace by record id.
	 *
	 * @param int    $record     Record id
	 * @param string $moduleName Module name
	 *
	 * @return \self
	 */
	public static function getInstanceById($record, $moduleName)
	{
		$class = get_called_class();
		$instance = new $class();
		$instance->record = $record;
		$instance->moduleName = $moduleName;
		$instance->recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		return $instance;
	}

	/**
	 * Get instanace by record model.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return \self
	 */
	public static function getInstanceByModel(\Vtiger_Record_Model $recordModel)
	{
		$class = get_called_class();
		$instance = new $class();
		$instance->record = $recordModel->getId();
		$instance->moduleName = $recordModel->getModuleName();
		$instance->recordModel = $recordModel;
		return $instance;
	}

	/**
	 * Get clean instanace.
	 *
	 * @param string $moduleName Module name
	 *
	 * @return \self
	 */
	public static function getInstance($moduleName = '')
	{
		$class = get_called_class();
		$instance = new $class();
		if ($moduleName) {
			$instance->moduleName = $moduleName;
		}
		return $instance;
	}

	/**
	 * Set without translations.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function withoutTranslations($type = true)
	{
		$this->withoutTranslations = $type;
		return $this;
	}

	/**
	 * Set language.
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setLanguage($name = true)
	{
		$this->language = $name;
		return $this;
	}

	/**
	 * Set parser type.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Set additional params.
	 *
	 * @param array $params
	 *
	 * @return $this
	 */
	public function setParams($params)
	{
		$this->params = $params;
		return $this;
	}

	/**
	 * Get additional params.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : false;
	}

	/**
	 * Set source record.
	 *
	 * @param int         $record
	 * @param string|bool $moduleName
	 *
	 * @return $this
	 */
	public function setSourceRecord($record, $moduleName = false, $recordModel = false)
	{
		$this->sourceRecordModel = $recordModel ? $recordModel : \Vtiger_Record_Model::getInstanceById($record, $moduleName ? $moduleName : Record::getType($record));
		return $this;
	}

	/**
	 * Set content.
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->rawContent = $this->content = str_replace('%20%3A%20', ' : ', $content);
		return $this;
	}

	/**
	 * Get content.
	 */
	public function getContent($trim = false)
	{
		return $trim ? trim($this->content) : $this->content;
	}

	/**
	 * Text parse function.
	 *
	 * @return $this
	 */
	public function parse()
	{
		if (empty($this->content)) {
			return $this;
		}
		if (isset($this->language)) {
			Language::setTemporaryLanguage($this->language);
		}
		$this->content = preg_replace_callback('/\$\((\w+) : ([,"\+\%\.\-\[\]\&\w\s\|]+)\)\$/u', function ($matches) {
			list(, $function, $params) = array_pad($matches, 3, '');
			if (in_array($function, static::$baseFunctions)) {
				return $this->$function($params);
			}
			return '';
		}, $this->content);
		Language::clearTemporaryLanguage();
		return $this;
	}

	/**
	 * Function parse date.
	 *
	 * @param string $param
	 *
	 * @return string
	 */
	public function date($param)
	{
		$timestamp = strtotime($param);
		return $timestamp ? date('Y-m-d', $timestamp) : '';
	}

	/**
	 * Text parse function.
	 *
	 * @return $this
	 */
	public function parseTranslations()
	{
		if (isset($this->language)) {
			Language::setTemporaryLanguage($this->language);
		}
		$this->content = preg_replace_callback('/\$\(translate : ([\.\&\w\s\|]+)\)\$/', function ($matches) {
			list(, $params) = $matches;

			return $this->translate($params);
		}, $this->content);
		Language::clearTemporaryLanguage();
		return $this;
	}

	/**
	 * Parsing translations.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function translate($params)
	{
		if (strpos($params, '|') === false) {
			return Language::translate($params);
		}
		$aparams = explode('|', $params);
		$moduleName = array_shift($aparams);
		return Language::translate(reset($aparams), $moduleName, $this->language);
	}

	/**
	 * Parsing organization detail.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	protected function organization($fieldName)
	{
		$id = false;
		if (strpos($fieldName, '|') !== false) {
			$params = explode('|', $fieldName);
			$fieldName = array_shift($params);
			$id = array_shift($params);
		}
		$company = Company::getInstanceById($id);
		if ($fieldName === 'mailLogo' || $fieldName === 'loginLogo') {
			$fieldName = ($fieldName === 'mailLogo') ? 'logo_mail' : 'logo_main';
			$logo = $company->getLogo($fieldName);
			if (!$logo || $logo->get('fileExists') === false) {
				return '';
			}
			$logoTitle = $company->get('name');
			$logoAlt = Language::translate('LBL_COMPANY_LOGO_TITLE');
			$logoHeight = $company->get($fieldName . '_height');
			$src = \App\Fields\File::getImageBaseData($logo->get('imagePath'));

			return "<img class=\"organizationLogo\" src=\"$src\" title=\"$logoTitle\" alt=\"$logoAlt\" height=\"{$logoHeight}px\">";
		} elseif (in_array($fieldName, ['logo_login', 'logo_main', 'logo_mail'])) {
			return Company::$logoPath . $company->get($fieldName);
		}
		return $company->get($fieldName);
	}

	/**
	 * Parsing employee detail.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	protected function employee($fieldName)
	{
		$userId = User::getCurrentUserId();
		if (Cache::has('TextParserEmployeeDetail', $userId . $fieldName)) {
			return Cache::get('TextParserEmployeeDetail', $userId . $fieldName);
		}
		if (Cache::has('TextParserEmployeeDetailRows', $userId)) {
			$employee = Cache::get('TextParserEmployeeDetailRows', $userId);
		} else {
			$employee = (new Db\Query())->select(['crmid'])->from('vtiger_crmentity')->where(['deleted' => 0, 'setype' => 'OSSEmployees', 'smownerid' => $userId])
				->limit(1)->scalar();
			Cache::save('TextParserEmployeeDetailRows', $userId, $employee, Cache::LONG);
		}
		$value = '';
		if ($employee) {
			$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($employee, 'OSSEmployees');
			$instance = static::getInstanceByModel($relatedRecordModel);
			foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
				if (isset($this->$key)) {
					$instance->$key = $this->$key;
				}
			}
			$value = $instance->record($fieldName);
		}
		Cache::save('TextParserEmployeeDetail', $userId . $fieldName, $value, Cache::LONG);
		return $value;
	}

	/**
	 * Parsing general data.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function general($key)
	{
		switch ($key) {
			case 'CurrentDate':
				return (new \DateTimeField(null))->getDisplayDate();
			case 'CurrentTime':
				return \Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s'));
			case 'SiteUrl':
				return \AppConfig::main('site_URL');
			case 'PortalUrl':
				return \AppConfig::main('PORTAL_URL');
			case 'BaseTimeZone':
				return Fields\DateTime::getTimeZone();
		}
		return $key;
	}

	/**
	 * Parsing record data.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function record($params, $isPermitted = true)
	{
		if (!isset($this->recordModel) || ($isPermitted && !Privilege::isPermitted($this->moduleName, 'DetailView', $this->record))) {
			return '';
		}
		list($key, $params) = explode('|', $params, 2);
		if ($this->recordModel->has($key)) {
			$fieldModel = $this->recordModel->getModule()->getField($key);
			if (!$fieldModel || !$this->useValue($fieldModel, $this->moduleName)) {
				return '';
			}
			return $this->getDisplayValueByField($fieldModel, false, $params);
		}
		switch ($key) {
			case 'CrmDetailViewURL':
				return \AppConfig::main('site_URL') . 'index.php?module=' . $this->moduleName . '&view=Detail&record=' . $this->record;
			case 'PortalDetailViewURL':
				$recorIdName = 'id';
				if ($this->moduleName === 'HelpDesk') {
					$recorIdName = 'ticketid';
				} elseif ($this->moduleName === 'Faq') {
					$recorIdName = 'faqid';
				} elseif ($this->moduleName === 'Products') {
					$recorIdName = 'productid';
				}
				return \AppConfig::main('PORTAL_URL') . '/index.php?module=' . $this->moduleName . '&action=index&' . $recorIdName . '=' . $this->record;
			case 'ModuleName':
				return $this->moduleName;
			case 'RecordId':
				return $this->record;
			case 'RecordLabel':
				return $this->recordModel->getName();
			case 'ChangesListChanges':
				foreach ($this->recordModel->getPreviousValue() as $fieldName => $oldValue) {
					$fieldModel = $this->recordModel->getModule()->getField($fieldName);
					if (!$fieldModel) {
						continue;
					}
					$oldValue = $this->getDisplayValueByField($fieldModel, $oldValue);
					$currentValue = $this->getDisplayValueByField($fieldModel);
					if ($this->withoutTranslations !== true) {
						$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName, $this->language) . ' ';
						$value .= Language::translate('LBL_FROM') . " $oldValue " . Language::translate('LBL_TO') . " $currentValue" . ($this->isHtml ? '<br />' : PHP_EOL);
					} else {
						$value .= "\$(translate : $this->moduleName|{$fieldModel->getFieldLabel()})\$ \$(translate : LBL_FROM)\$ $oldValue \$(translate : LBL_TO)\$ " . $currentValue . ($this->isHtml ? '<br />' : PHP_EOL);
					}
				}
				return $value;
			case 'ChangesListValues':
				$changes = $this->recordModel->getPreviousValue();
				if (empty($changes)) {
					$changes = array_filter($this->recordModel->getData());
					unset($changes['createdtime'], $changes['modifiedtime'], $changes['id'], $changes['newRecord'], $changes['modifiedby']);
				}
				foreach ($changes as $fieldName => $oldValue) {
					$fieldModel = $this->recordModel->getModule()->getField($fieldName);
					if (!$fieldModel || $oldValue !== '') {
						continue;
					}
					$currentValue = $this->getDisplayValueByField($fieldModel);
					if ($this->withoutTranslations !== true) {
						$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName, $this->language) . ": $currentValue" . ($this->isHtml ? '<br />' : PHP_EOL);
					} else {
						$value .= "\$(translate : $this->moduleName|{$fieldModel->getFieldLabel()})\$: $currentValue" . ($this->isHtml ? '<br />' : PHP_EOL);
					}
				}
				return $value;
			default:
				if (strpos($key, ' ') !== false) {
					list($key, $params) = explode(' ', $key);
				}
				switch ($key) {
					case 'Comments':
						return $this->getComments($params);
				}
				break;
		}
		return '';
	}

	/**
	 * Parsing related record data.
	 *
	 * @param string $params
	 *
	 * @return mixed
	 */
	protected function relatedRecord($params)
	{
		list($fieldName, $relatedField, $relatedModule) = explode('|', $params);
		if (!isset($this->recordModel) ||
			!Privilege::isPermitted($this->moduleName, 'DetailView', $this->record) ||
			$this->recordModel->isEmpty($fieldName)) {
			return '';
		}
		$relatedId = $this->recordModel->get($fieldName);
		if (empty($relatedId)) {
			return '';
		}
		if ($relatedModule === 'Users') {
			$ownerType = Fields\Owner::getType($relatedId);
			if ($ownerType === 'Users') {
				$userRecordModel = \Users_Privileges_Model::getInstanceById($relatedId);
				if ($userRecordModel->get('status') === 'Active') {
					$instance = static::getInstanceByModel($userRecordModel);
					foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
						if (isset($this->$key)) {
							$instance->$key = $this->$key;
						}
					}

					return $instance->record($relatedField, false);
				}

				return '';
			}
			$return = [];
			foreach (PrivilegeUtil::getUsersByGroup($relatedId) as $userId) {
				$userRecordModel = \Users_Privileges_Model::getInstanceById($userId);
				if ($userRecordModel->get('status') === 'Active') {
					$instance = static::getInstanceByModel($userRecordModel);
					foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
						if (isset($this->$key)) {
							$instance->$key = $this->$key;
						}
					}
					$return[] = $instance->record($relatedField, false);
				}
			}

			return implode($this->relatedRecordSeparator, $return);
		}
		$moduleName = Record::getType($relatedId);
		if (!empty($moduleName)) {
			if (($relatedModule && $relatedModule !== $moduleName)) {
				return '';
			}
		}
		$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedId, $moduleName);
		$instance = static::getInstanceByModel($relatedRecordModel);
		foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
			if (isset($this->$key)) {
				$instance->$key = $this->$key;
			}
		}
		return $instance->record($relatedField);
	}

	/**
	 * Parsing source record data.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	protected function sourceRecord($fieldName)
	{
		if (empty($this->sourceRecordModel) || !Privilege::isPermitted($this->sourceRecordModel->getModuleName(), 'DetailView', $this->sourceRecordModel->getId())) {
			return '';
		}
		$instance = static::getInstanceByModel($this->sourceRecordModel);
		foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
			if (isset($this->$key)) {
				$instance->$key = $this->$key;
			}
		}
		return $instance->record($fieldName);
	}

	/**
	 * Parsing related records list.
	 *
	 * @param string $params Parameter construction: RelatedModuleName|Columns|Conditions|CustomViewIdOrName|Limit, Example: Contacts|firstname,lastname,modifiedtime|[[["firstname","a","Tom"]]]||2
	 *
	 * @return string
	 */
	protected function relatedRecordsList($params)
	{
		list($reletedModuleName, $columns, $conditions, $viewIdOrName, $limit) = array_pad(explode('|', $params), 5, '');
		$relationListView = \Vtiger_RelationListView_Model::getInstance($this->recordModel, $reletedModuleName, '');
		if (!$relationListView || !Privilege::isPermitted($reletedModuleName)) {
			return '';
		}
		$pagingModel = new \Vtiger_Paging_Model();
		if ((int) $limit) {
			$pagingModel->set('limit', (int) $limit);
		}
		if ($viewIdOrName) {
			if (!is_numeric($viewIdOrName)) {
				$customView = CustomView::getInstance($reletedModuleName);
				if ($cvId = $customView->getViewIdByName($viewIdOrName)) {
					$viewIdOrName = $cvId;
				} else {
					$viewIdOrName = false;
					Log::warning("No view found. Module: $reletedModuleName, view name: $viewIdOrName", 'TextParser');
				}
			}
			if ($viewIdOrName) {
				$relationListView->getQueryGenerator()->initForCustomViewById($viewIdOrName);
			}
		}
		if ($columns) {
			$relationListView->setFields($columns);
		}
		if ($conditions) {
			$transformedSearchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition(Json::decode($conditions));
			$relationListView->set('search_params', $transformedSearchParams);
		}
		$rows = $headers = '';
		$fields = $relationListView->getHeaders();
		foreach ($fields as $fieldModel) {
			if ($fieldModel->isViewable()) {
				$headers .= '<th>' . \App\Language::translate($fieldModel->getFieldLabel(), $reletedModuleName) . '</th>';
			}
		}
		foreach ($relationListView->getEntries($pagingModel) as $reletedRecordModel) {
			$rows .= '<tr>';
			foreach ($fields as $fieldModel) {
				$value = $this->getDisplayValueByField($fieldModel, $reletedRecordModel);
				if ($value !== false) {
					$rows .= "<td>$value</td>";
				}
			}
			$rows .= '</tr>';
		}
		return empty($rows) ? '' : "<table><thead><tr>{$headers}</tr></thead><tbody>{$rows}</tbody></table>";
	}

	/**
	 * Parsing records list.
	 *
	 * @param string $params Parameter construction: ModuleName|Columns|Conditions|CustomViewIdOrName|Limit, Example: Contacts|firstname,lastname,modifiedtime|[[["firstname","a","Tom"]]]||2
	 *
	 * @return string
	 */
	protected function recordsList($params)
	{
		list($moduleName, $columns, $conditions, $viewIdOrName, $limit) = array_pad(explode('|', $params), 5, '');
		$cvId = 0;
		if ($viewIdOrName) {
			if (!is_numeric($viewIdOrName)) {
				$customView = CustomView::getInstance($moduleName);
				if ($cvIdByName = $customView->getViewIdByName($viewIdOrName)) {
					$viewIdOrName = $cvIdByName;
				} else {
					$viewIdOrName = false;
					Log::warning("No view found. Module: $moduleName, view name: $viewIdOrName", 'TextParser');
				}
			}
			if ($viewIdOrName) {
				$cvId = $viewIdOrName;
			}
		}
		$listView = \Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		$pagingModel = new \Vtiger_Paging_Model();
		if ((int) $limit) {
			$pagingModel->set('limit', (int) $limit);
		}
		if ($columns) {
			$listView->getQueryGenerator()->setFields(explode(',', $columns));
		}
		if ($conditions) {
			$transformedSearchParams = $listView->getQueryGenerator()->parseBaseSearchParamsToCondition(Json::decode($conditions));
			$listView->set('search_params', $transformedSearchParams);
		}
		$rows = $headers = '';
		$fields = $listView->getListViewHeaders();
		foreach ($fields as $fieldModel) {
			$headers .= '<th>' . \App\Language::translate($fieldModel->getFieldLabel(), $moduleName) . '</th>';
		}
		foreach ($listView->getListViewEntries($pagingModel) as $reletedRecordModel) {
			$rows .= '<tr>';
			foreach ($fields as $fieldModel) {
				$value = $this->getDisplayValueByField($fieldModel, $reletedRecordModel);
				if ($value !== false) {
					$rows .= "<td>$value</td>";
				}
			}
			$rows .= '</tr>';
		}
		return empty($rows) ? '' : "<table><thead><tr>{$headers}</tr></thead><tbody>{$rows}</tbody></table>";
	}

	/**
	 * Get record display value.
	 *
	 * @param \Vtiger_Field_Model             $fieldModel
	 * @param bool|mixed|\Vtiger_Record_Model $value
	 * @param string                          $params
	 *
	 * @return array|bool|mixed|string
	 */
	protected function getDisplayValueByField(\Vtiger_Field_Model $fieldModel, $value = false, $params = null)
	{
		$recordModel = $this->recordModel;
		if ($value === false) {
			$value = $this->recordModel->get($fieldModel->getName());
			if (!$fieldModel->isViewEnabled()) {
				return '';
			}
		} elseif (is_object($value)) {
			$recordModel = $value;
			$value = $value->get($fieldModel->getName());
			if (!$fieldModel->isViewEnabled()) {
				return false;
			}
		}
		if ($value === '') {
			return '';
		}
		if ($this->withoutTranslations !== true) {
			return $fieldModel->getUITypeModel()->getTextParserDisplayValue($value, $recordModel, $params);
		}
		return $this->getDisplayValueByType($value, $recordModel, $fieldModel, $params);
	}

	/**
	 * Get record display value by type.
	 *
	 * @param mixed                $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @param \Vtiger_Field_Model  $fieldModel
	 * @param string               $params
	 *
	 * @return array|mixed|string
	 */
	protected function getDisplayValueByType($value, \Vtiger_Record_Model $recordModel, \Vtiger_Field_Model $fieldModel, $params)
	{
		switch ($fieldModel->getFieldDataType()) {
			case 'boolean':
				$value = ($value === 1) ? 'LBL_YES' : 'LBL_NO';
				$value = "$(translate : $value)$";
				break;
			case 'multipicklist':
				$value = explode(' |##| ', $value);
				$trValue = [];
				$countValue = count($value);
				for ($i = 0; $i < $countValue; ++$i) {
					$trValue[] = "$(translate : {$recordModel->getModuleName()}|{$value[$i]})$";
				}
				if (is_array($trValue)) {
					$trValue = implode(' |##| ', $trValue);
				}
				$value = str_ireplace(' |##| ', ', ', $trValue);
				break;
			case 'picklist':
				$value = "$(translate : {$recordModel->getModuleName()}|$value)$";
				break;
			case 'time':
				$userModel = \Users_Privileges_Model::getCurrentUserModel();
				$value = \DateTimeField::convertToUserTimeZone(date('Y-m-d') . ' ' . $value)->format('H:i:s');
				if ((int) $userModel->get('hour_format') === 12) {
					if ($value) {
						list($hours, $minutes) = explode(':', $value);
						$format = '$(translate : PM)$';
						if ($hours > 12) {
							$hours = (int) $hours - 12;
						} elseif ($hours < 12) {
							$format = '$(translate : AM)$';
						}
						//If hours zero then we need to make it as 12 AM
						if ($hours == '00') {
							$hours = '12';
							$format = '$(translate : AM)$';
						}
						$value = "$hours:$minutes $format";
					} else {
						$value = '';
					}
				}
				break;
			case 'tree':
				$template = $fieldModel->getFieldParams();
				$row = Fields\Tree::getValueByTreeId($template, $value);
				$value = $parentName = '';
				if ($row) {
					if ($row['depth'] > 0) {
						$parenttrre = $row['parenttrre'];
						$pieces = explode('::', $parenttrre);
						end($pieces);
						$parent = prev($pieces);
						$parentRow = Fields\Tree::getValueByTreeId($template, $parent);
						$parentName = "($(translate : {$recordModel->getModuleName()}|{$parentRow['name']})$) ";
					}
					$value = $parentName . "$(translate : {$recordModel->getModuleName()}|{$row['name']})$";
				}
				break;
			default:
				return $fieldModel->getTextParserDisplayValue($value, $recordModel, $params);
		}
		return $value;
	}

	/**
	 * Get last comments.
	 *
	 * @param int|bool $limit
	 *
	 * @return string
	 */
	protected function getComments($limit = false)
	{
		$query = (new \App\Db\Query())->select(['commentcontent'])->from('vtiger_modcomments')->where(['related_to' => $this->record])->orderBy(['modcommentsid' => SORT_DESC]);
		if ($limit) {
			$query->limit($limit);
		}
		$commentsList = '';
		foreach ($query->column() as $comment) {
			if ($comment != '') {
				$commentsList .= '<br /><br />' . nl2br($comment);
			}
		}

		return ltrim($commentsList, '<br /><br />');
	}

	/**
	 * Check if this content can be used.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param string              $moduleName
	 *
	 * @return bool
	 */
	protected function useValue($fieldModel, $moduleName)
	{
		return true;
	}

	/**
	 * Parsing params.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function params($params)
	{
		if (isset($this->params[$params])) {
			return $this->params[$params];
		}

		return '';
	}

	/**
	 * Parsing custom.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function custom($params)
	{
		$params = explode('|', $params);
		$parserName = array_shift($params);
		$aparams = $params;
		$moduleName = array_shift($params);

		if (Module::getModuleId($moduleName)) {
			$handlerClass = \Vtiger_Loader::getComponentClassName('TextParser', $parserName, $this->moduleName, false);
			if (!$handlerClass) {
				Log::error("Not found custom class: $parserName|{$this->moduleName}");
			}
			$instance = new $handlerClass($this, $params);
		} else {
			$className = "\App\TextParser\\$parserName";
			if (!class_exists($className)) {
				Log::error("Not found custom class $parserName");

				return '';
			}
			$instance = new $className($this, $aparams);
		}

		if ($instance->isActive()) {
			return $instance->process();
		}

		return '';
	}

	/**
	 * Get record variables.
	 *
	 * @param bool|string $fieldType
	 *
	 * @return array
	 */
	public function getRecordVariable($fieldType = false)
	{
		$cacheKey = "$this->moduleName|$fieldType";
		if (isset(static::$recordVariable[$cacheKey])) {
			return static::$recordVariable[$cacheKey];
		}
		$variables = [];
		if (!$fieldType) {
			foreach (static::$variableEntity as $key => $name) {
				$variables[Language::translate('LBL_ENTITY_VARIABLES', 'Other.TextParser')][] = [
					'var_value' => "$(record : $key)$",
					'var_label' => "$(translate : Other.TextParser|$name)$",
					'label' => Language::translate($name, 'Other.TextParser'),
				];
			}
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($this->moduleName);
		foreach ($moduleModel->getBlocks() as $blockModel) {
			foreach ($blockModel->getFields() as $fieldModel) {
				if ($fieldModel->isViewable() && !($fieldType && $fieldModel->getFieldDataType() !== $fieldType)) {
					$variables[Language::translate($blockModel->get('label'), $this->moduleName)][] = [
						'var_value' => "$(record : {$fieldModel->getName()})$",
						'var_label' => "$(translate : {$this->moduleName}|{$fieldModel->getFieldLabel()})$",
						'label' => Language::translate($fieldModel->getFieldLabel(), $this->moduleName),
					];
				}
			}
		}
		static::$recordVariable[$cacheKey] = $variables;

		return $variables;
	}

	/**
	 * Get source variables.
	 *
	 * @return array
	 */
	public function getSourceVariable()
	{
		if (empty(\App\TextParser::$sourceModules[$this->moduleName])) {
			return false;
		}
		$variables = [];
		foreach (static::$variableEntity as $key => $name) {
			$variables['LBL_ENTITY_VARIABLES'][] = [
				'var_value' => "$(sourceRecord : $key)$",
				'var_label' => "$(translate : Other.TextParser|$name)$",
				'label' => Language::translate($name, 'Other.TextParser'),
			];
		}
		foreach (\App\TextParser::$sourceModules[$this->moduleName] as $moduleName) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			foreach ($moduleModel->getBlocks() as $blockModel) {
				foreach ($blockModel->getFields() as $fieldModel) {
					if ($fieldModel->isViewable()) {
						$variables[$moduleName][$blockModel->get('label')][] = [
							'var_value' => "$(sourceRecord : {$fieldModel->getName()})$",
							'var_label' => "$(translate : $moduleName|{$fieldModel->getFieldLabel()})$",
							'label' => Language::translate($fieldModel->getFieldLabel(), $moduleName),
						];
					}
				}
			}
		}

		return $variables;
	}

	/**
	 * Get related variables.
	 *
	 * @param bool|string $fieldType
	 *
	 * @return array
	 */
	public function getRelatedVariable($fieldType = false)
	{
		$cacheKey = "$this->moduleName|$fieldType";
		if (isset(static::$relatedVariable[$cacheKey])) {
			return static::$relatedVariable[$cacheKey];
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($this->moduleName);
		$variables = [];
		$entityVariables = Language::translate('LBL_ENTITY_VARIABLES', 'Other.TextParser');
		foreach ($moduleModel->getFieldsByType(array_merge(\Vtiger_Field_Model::$referenceTypes, ['owner', 'multireference'])) as $parentFieldName => $field) {
			if ($field->getFieldDataType() === 'owner') {
				$relatedModules = ['Users'];
			} else {
				$relatedModules = $field->getReferenceList();
			}
			$parentFieldNameLabel = Language::translate($field->getFieldLabel(), $this->moduleName);
			if (!$fieldType) {
				foreach (static::$variableEntity as $key => $name) {
					$variables[$parentFieldName]["$parentFieldNameLabel - $entityVariables"][] = [
						'var_value' => "$(relatedRecord : $parentFieldName|$key)$",
						'var_label' => "$(translate : Other.TextParser|$key)$",
						'label' => $parentFieldNameLabel . ': ' . Language::translate($name, 'Other.TextParser'),
					];
				}
			}
			foreach ($relatedModules as $relatedModule) {
				$relatedModuleLang = Language::translate($relatedModule, $relatedModule);
				$moduleModel = \Vtiger_Module_Model::getInstance($relatedModule);
				foreach ($moduleModel->getBlocks() as $blockModel) {
					foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
						if ($fieldModel->isViewable() && !($fieldType && $fieldModel->getFieldDataType() !== $fieldType)) {
							$labelGroup = "$parentFieldNameLabel: ($relatedModuleLang) " . Language::translate($blockModel->get('label'), $relatedModule);
							$variables[$parentFieldName][$labelGroup][] = [
								'var_value' => "$(relatedRecord : $parentFieldName|$fieldName|$relatedModule)$",
								'var_label' => "$(translate : $relatedModule|{$fieldModel->getFieldLabel()})$",
								'label' => "$parentFieldNameLabel: ($relatedModuleLang) " . Language::translate($fieldModel->getFieldLabel(), $relatedModule),
							];
						}
					}
				}
			}
		}
		static::$relatedVariable[$cacheKey] = $variables;

		return $variables;
	}

	/**
	 * Get general variables.
	 *
	 * @return array
	 */
	public function getGeneralVariable()
	{
		$variables = [
			'LBL_ENTITY_VARIABLES' => array_map(function ($value) {
				return Language::translate($value, 'Other.TextParser');
			}, array_flip(static::$variableGeneral)),
		];
		$companyDetails = Company::getInstanceById()->getData();
		unset($companyDetails['id'], $companyDetails['logo_login'], $companyDetails['logo_login_height'], $companyDetails['logo_main'], $companyDetails['logo_main_height'], $companyDetails['logo_mail'], $companyDetails['logo_mail_height'], $companyDetails['default']);
		$companyVariables = [];
		foreach (array_keys($companyDetails) as $name) {
			$companyVariables["$(organization : $name)$"] = Language::translate('LBL_' . strtoupper($name), 'Settings:Companies');
		}
		$companyVariables['$(organization : mailLogo)$'] = Language::translate('LBL_LOGO_IMG_MAIL', 'Settings:Companies');
		$companyVariables['$(organization : loginLogo)$'] = Language::translate('LBL_LOGO_IMG_LOGIN', 'Settings:Companies');
		$companyVariables['$(organization : logo_login)$'] = Language::translate('LBL_LOGO_PATH_LOGIN', 'Settings:Companies');
		$companyVariables['$(organization : logo_main)$'] = Language::translate('LBL_LOGO_PATH_MAIN', 'Settings:Companies');
		$companyVariables['$(organization : logo_mail)$'] = Language::translate('LBL_LOGO_PATH_MAIL', 'Settings:Companies');
		$variables['LBL_COMPANY_VARIABLES'] = $companyVariables;
		$variables['LBL_CUSTOM_VARIABLES'] = array_merge($this->getBaseGeneralVariable(), $this->getModuleGeneralVariable());

		return $variables;
	}

	/**
	 * Get general variables base function.
	 *
	 * @return array
	 */
	protected function getBaseGeneralVariable()
	{
		$variables = [];
		foreach ((new \DirectoryIterator(__DIR__ . DIRECTORY_SEPARATOR . 'TextParser')) as $fileInfo) {
			$fileName = $fileInfo->getBasename('.php');
			if ($fileInfo->getType() !== 'dir' && $fileName !== 'Base' && $fileInfo->getExtension() === 'php') {
				$className = '\App\TextParser\\' . $fileName;
				if (!class_exists($className)) {
					Log::warning('Not found custom class');
					continue;
				}
				$instance = new $className($this);
				if (isset($this->type) && $this->type !== $instance->type) {
					continue;
				}
				$variables["$(custom : $fileName)$"] = Language::translate($instance->name, 'Other.TextParser');
			}
		}

		return $variables;
	}

	/**
	 * Get general variables module function.
	 *
	 * @return array
	 */
	protected function getModuleGeneralVariable()
	{
		$variables = [];
		if ($this->moduleName && is_dir(("modules/{$this->moduleName}/textparsers/"))) {
			foreach ((new \DirectoryIterator("modules/{$this->moduleName}/textparsers/")) as $fileInfo) {
				$fileName = $fileInfo->getBasename('.php');
				if ($fileInfo->getType() !== 'dir' && $fileInfo->getExtension() === 'php') {
					$handlerClass = \Vtiger_Loader::getComponentClassName('TextParser', $fileName, $this->moduleName);
					$instanceClass = new $handlerClass($this);
					if (isset($this->type) && $this->type !== $instanceClass->type) {
						continue;
					}
					$variables["$(custom : $fileName|{$this->moduleName})$"] = Language::translate($instanceClass->name, $this->moduleName);
				}
			}
		}

		return $variables;
	}

	/**
	 * Get related modules list.
	 *
	 * @return array
	 */
	public function getRelatedListVariable()
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($this->moduleName);
		$variables = [];
		$relationModels = $moduleModel->getRelations();
		foreach ($relationModels as $relation) {
			$variables[] = [
				'key' => '$(relatedRecordsList : ' . $relation->get('relatedModuleName') . ')$',
				'label' => Language::translate($relation->get('label'), $relation->get('relatedModuleName')),
			];
		}

		return $variables;
	}

	/**
	 * Get base modules list.
	 *
	 * @return array
	 */
	public function getBaseListVariable()
	{
		$variables = [];
		foreach (\vtlib\Functions::getAllModules() as $module) {
			$variables[] = [
				'key' => "$(recordsList : {$module['name']})$",
				'label' => Language::translate($module['name'], $module['name']),
			];
		}

		return $variables;
	}

	/**
	 * Function checks if its TextParser type.
	 *
	 * @param string $text
	 *
	 * @return bool
	 */
	public static function isVaribleToParse($text)
	{
		return preg_match('/^\$\((\w+) : ([,"\+\-\[\]\&\w\s\|]+)\)\$$/', $text);
	}

	/**
	 * Truncating HTML.
	 *
	 * @param          $html
	 * @param int|bool $length
	 * @param bool     $addDots
	 *
	 * @throws \HTMLPurifier_Exception
	 *
	 * @return string
	 */
	public static function htmlTruncate($html, $length = false, $addDots = true)
	{
		if (!$length) {
			$length = \AppConfig::main('listview_max_textlength');
		}
		$encoding = \AppConfig::main('default_charset');
		$config = \HTMLPurifier_Config::create(null);
		$config->set('Cache.SerializerPath', ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'vtlib');
		$lexer = \HTMLPurifier_Lexer::create($config);
		$tokens = $lexer->tokenizeHTML($html, $config, new \HTMLPurifier_Context());
		$truncated = $openTokens = [];
		$depth = $totalCount = 0;
		foreach ($tokens as $token) {
			if ($token instanceof \HTMLPurifier_Token_Start) {
				$openTokens[$depth] = $token->name;
				$truncated[] = $token;
				++$depth;
			} elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $length) {
				if (false === $encoding) {
					preg_match('/^(\s*)/um', $token->data, $prefixSpace) ?: $prefixSpace = ['', ''];
					$token->data = $prefixSpace[1] . self::truncateWords(ltrim($token->data), $length - $totalCount, '');
					$currentCount = self::countWords($token->data);
				} else {
					if (mb_strlen($token->data, $encoding) > $length - $totalCount) {
						$token->data = rtrim(mb_substr($token->data, 0, $length - $totalCount, $encoding));
					}
					$currentCount = mb_strlen($token->data, $encoding);
				}
				$totalCount += $currentCount;
				$truncated[] = $token;
			} elseif ($token instanceof \HTMLPurifier_Token_End) {
				if ($token->name === $openTokens[$depth - 1]) {
					--$depth;
					unset($openTokens[$depth]);
					$truncated[] = $token;
				}
			} elseif ($token instanceof \HTMLPurifier_Token_Empty) {
				$truncated[] = $token;
			}
			if ($totalCount >= $length) {
				if (0 < count($openTokens)) {
					krsort($openTokens);
					foreach ($openTokens as $name) {
						$truncated[] = new \HTMLPurifier_Token_End($name);
					}
				}
				break;
			}
		}
		$generator = new \HTMLPurifier_Generator($config, new \HTMLPurifier_Context());
		$html = preg_replace_callback('/<*([A-Za-z_]\w*)\s\/>/', function ($matches) {
			if (\in_array($matches[1], ['div'])) {
				return "<{$matches[1]}></{$matches[1]}>";
			}
			return $matches[0];
		}, $generator->generateFromTokens($truncated));
		return $html . ($totalCount >= $length ? ($addDots ? '...' : '') : '');
	}

	/**
	 * Truncating text.
	 *
	 * @param string   $text
	 * @param int|bool $length
	 * @param bool     $addDots
	 *
	 * @return string
	 */
	public static function textTruncate($text, $length = false, $addDots = true)
	{
		if (!$length) {
			$length = \AppConfig::main('listview_max_textlength');
		}
		if (function_exists('mb_strlen')) {
			if (mb_strlen($text) > $length) {
				$text = mb_substr($text, 0, $length, \AppConfig::main('default_charset'));
				if ($addDots) {
					$text .= '...';
				}
			}
		} elseif (strlen($text) > $length) {
			$text = substr($text, 0, $length);
			if ($addDots) {
				$text .= '...';
			}
		}
		return $text;
	}

	/**
	 * Get text length.
	 *
	 * @param string $text
	 *
	 * @return int
	 */
	public static function getTextLength($text)
	{
		if (function_exists('mb_strlen')) {
			return mb_strlen($text);
		} else {
			return strlen($text);
		}
	}
}
