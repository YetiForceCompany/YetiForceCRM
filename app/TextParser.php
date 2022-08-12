<?php
/**
 * Text parser file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Text parser class.
 */
class TextParser
{
	/**
	 * Examples of supported variables.
	 *
	 * @var array
	 */
	public static $variableExamples = [
		'LBL_ORGANIZATION_NAME' => '$(organization : company_name)$',
		'LBL_ORGANIZATION_LOGO' => '$(organization : logo)$',
		'LBL_EMPLOYEE_NAME' => '$(employee : last_name)$',
		'LBL_CRM_DETAIL_VIEW_URL' => '$(record : CrmDetailViewURL)$',
		'LBL_PORTAL_DETAIL_VIEW_URL' => '$(record : PortalDetailViewURL)$',
		'LBL_RECORD_ID' => '$(record : RecordId)$',
		'LBL_RECORD_LABEL' => '$(record : RecordLabel)$',
		'LBL_LIST_OF_CHANGES_IN_RECORD' => '$(record : ChangesListChanges)$',
		'LBL_LIST_OF_NEW_VALUES_IN_RECORD' => '$(record : ChangesListValues)$',
		'LBL_RECORD_COMMENT' => '$(record : Comments 5)$, $(record : Comments)$',
		'LBL_RELATED_RECORD_LABEL' => '$(relatedRecord : parent_id|email1|Accounts)$, $(relatedRecord : parent_id|email1)$',
		'LBL_RELATED_NEXT_LEVEL_RECORD_LABEL' => '$(relatedRecordLevel : projectid|Project|linktoaccountscontacts|email1|Accounts)$',
		'LBL_OWNER_EMAIL' => '$(relatedRecord : assigned_user_id|email1|Users)$',
		'LBL_SOURCE_RECORD_LABEL' => '$(sourceRecord : RecordLabel)$',
		'LBL_CUSTOM_FUNCTION' => '$(custom : ContactsPortalPass)$',
		'LBL_RELATED_RECORDS_LIST' => '$(relatedRecordsList : Contacts|firstname,lastname,email|[[["firstname","a","Tom"]]]||5)$',
		'LBL_RECORDS_LIST' => '$(recordsList : Contacts|firstname,lastname,email|[[["firstname","a","Tom"]]]||5)$',
		'LBL_INVENTORY_TABLE' => '$(inventory : type=table columns=seq,name,qty,unit,price,total,net href=no)$',
		'LBL_DYNAMIC_INVENTORY_TABLE' => '$(custom : dynamicInventoryColumnsTable)$',
		'LBL_BARCODE' => '$(barcode : type=EAN13 class=DNS1D , value=12345678)$',
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
		'SummaryFields' => 'LBL_SUMMARY_FIELDS',
	];

	/**
	 * List of available functions.
	 *
	 * @var string[]
	 */
	protected static $baseFunctions = ['general', 'translate', 'record', 'relatedRecord', 'relatedRecordLevel', 'sourceRecord', 'organization', 'employee', 'params', 'custom', 'relatedRecordsList', 'recordsList', 'date', 'inventory', 'userVariable', 'barcode'];

	/**
	 * List of source modules.
	 *
	 * @var array
	 */
	public static $sourceModules = [
		'Campaigns' => ['Leads', 'Accounts', 'Contacts', 'Vendors', 'Partners', 'Competition'],
	];
	/**
	 * Record variables.
	 *
	 * @var array
	 */
	protected static $recordVariable = [];
	/**
	 * Related variables.
	 *
	 * @var array
	 */
	protected static $relatedVariable = [];
	/**
	 * Next level related variables.
	 *
	 * @var array
	 */
	protected static $relatedVariableLevel = [];

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
	protected $content = '';

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
	 * Use extended parsing.
	 *
	 * @var bool
	 */
	public $useExtension = false;

	/**
	 * Variable parser regex.
	 *
	 * @var string
	 */
	public const VARIABLE_REGEX = '/\$\((\w+) : ([,"\+\#\%\.\:\;\=\-\[\]\&\w\s\|\)\(\:]+)\)\$/u';

	/** @var bool Permissions condition */
	protected $permissions = true;

	/** @var string[] Uitypes with large data */
	protected $largeDataUiTypes = ['multiImage', 'image'];

	/**
	 * Get instanace by record id.
	 *
	 * @param int    $record     Record id
	 * @param string $moduleName Module name
	 *
	 * @return \self
	 */
	public static function getInstanceById(int $record, ?string $moduleName = null)
	{
		$class = static::class;
		$instance = new $class();
		$instance->record = $record;
		$instance->recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$instance->moduleName = $instance->recordModel->getModuleName();
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
		$class = static::class;
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
		$class = static::class;
		$instance = new $class();
		if ($moduleName) {
			$instance->moduleName = $moduleName;
		}
		return $instance;
	}

	/**
	 * Set the active state of extended parsing functionality.
	 *
	 * @param bool $state
	 *
	 * @return $this
	 */
	public function setExtensionState(bool $state)
	{
		$this->useExtension = $state;
		return $this;
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
	 * Set param value.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function setParam(string $key, $value)
	{
		$this->params[$key] = $value;
		return $this;
	}

	/**
	 * Get additional params.
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
	 * Set source record.
	 *
	 * @param int         $record
	 * @param bool|string $moduleName
	 * @param mixed       $recordModel
	 *
	 * @return $this
	 */
	public function setSourceRecord($record, $moduleName = false, $recordModel = false)
	{
		$this->sourceRecordModel = $recordModel ?: \Vtiger_Record_Model::getInstanceById($record, $moduleName ?: Record::getType($record));
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
		$this->rawContent = $this->content = str_replace(['%20%3A%20', '%20:%20'], ' : ', $content);
		return $this;
	}

	/**
	 * Get content.
	 *
	 * @param mixed $trim
	 */
	public function getContent($trim = false)
	{
		return $trim ? trim($this->content) : $this->content;
	}

	/**
	 * Function checks if its TextParser type.
	 *
	 * @param string $text
	 *
	 * @return int
	 */
	public static function isVaribleToParse($text)
	{
		return (int) preg_match(static::VARIABLE_REGEX, $text);
	}

	/**
	 * Set permissions condition.
	 *
	 * @param bool $permitted
	 *
	 * @return $this
	 */
	public function setGlobalPermissions(bool $permitted)
	{
		$this->permissions = $permitted;
		return $this;
	}

	/**
	 * All text parse function.
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
		$this->content = $this->parseData($this->content);
		Language::clearTemporaryLanguage();
		return $this;
	}

	/**
	 * Text parse function.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function parseData(string $content)
	{
		if ($this->useExtension) {
			$content = preg_replace_callback('/<!--[\s]+({% [\s\S]+? %})[\s]+-->/u', fn ($matches) => $matches[1] ?? '', $content);
			$twig = new \Twig\Environment(new \Twig\Loader\ArrayLoader(['index' => $content]));
			$sandbox = new \Twig\Extension\SandboxExtension(\App\Extension\Twig\SecurityPolicy::getPolicy(), true);
			$twig->addExtension($sandbox);
			$twig->addFunction(new \Twig\TwigFunction('YFParser', function ($text) {
				$value = '';
				preg_match(static::VARIABLE_REGEX, $text, $matches);
				if ($matches) {
					[, $function, $params] = array_pad($matches, 3, '');
					$value = \in_array($function, static::$baseFunctions) ? $this->{$function}($params) : '';
				}
				return $value;
			}));
			$content = $twig->render('index');
		}
		return preg_replace_callback(static::VARIABLE_REGEX, function ($matches) {
			[, $function, $params] = array_pad($matches, 3, '');
			return \in_array($function, static::$baseFunctions) ? $this->{$function}($params) : '';
		}, $content);
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
		$this->content = preg_replace_callback('/\$\(translate : ([,"\+\%\.\=\-\[\]\&\w\s\|]+)\)\$/u', function ($matches) {
			[, $params] = array_pad($matches, 2, '');
			return $this->translate($params);
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
		if (isset(\App\Condition::DATE_OPERATORS[$param])) {
			$date = implode(' - ', array_unique(\DateTimeRange::getDateRangeByType($param)));
		} else {
			$date = date('Y-m-d', strtotime($param));
		}
		return $date;
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
		if ($this->withoutTranslations) {
			return "$(translate : $params)$";
		}
		if (false === strpos($params, '|')) {
			return Language::translate($params);
		}
		$splitParams = explode('|', $params);
		$module = array_shift($splitParams);
		$key = array_shift($splitParams);
		return Language::translate($key, $module, $splitParams[0] ?? $this->language);
	}

	/**
	 * Parsing organization detail.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function organization(string $params): string
	{
		if (!$params) {
			return '';
		}
		$returnVal = '';
		if (false === strpos($params, '|')) {
			$id = User::getCurrentUserModel()->get('multiCompanyId');
			$fieldName = $params;
			$params = false;
		} else {
			[$id, $fieldName, $params] = array_pad(explode('|', $params, 3), 3, false);
		}
		if (Record::isExists($id, 'MultiCompany')) {
			$companyRecordModel = \Vtiger_Record_Model::getInstanceById($id, 'MultiCompany');
			if ($companyRecordModel->has($fieldName)) {
				$value = $companyRecordModel->get($fieldName);
				$fieldModel = $companyRecordModel->getModule()->getFieldByName($fieldName);
				if ('' === $value || !$fieldModel || !$this->useValue($fieldModel, 'MultiCompany')) {
					return '';
				}
				if ($this->withoutTranslations) {
					$returnVal = $this->getDisplayValueByType($value, $companyRecordModel, $fieldModel, $params);
				} else {
					$returnVal = $fieldModel->getUITypeModel()->getTextParserDisplayValue($value, $companyRecordModel, $params);
				}
			}
		}
		return $returnVal;
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
				->scalar();
			Cache::save('TextParserEmployeeDetailRows', $userId, $employee, Cache::LONG);
		}
		$value = '';
		if ($employee && Record::isExists($employee, 'OSSEmployees')) {
			$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($employee, 'OSSEmployees');
			$instance = static::getInstanceByModel($relatedRecordModel);
			foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
				if (isset($this->{$key})) {
					$instance->{$key} = $this->{$key};
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
				return \Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('H:i:s'));
			case 'CurrentDateTime':
				return Fields\DateTime::formatToDisplay('now');
			case 'SiteUrl':
				return Config::main('site_URL');
			case 'PortalUrl':
				return Config::main('PORTAL_URL');
			case 'BaseTimeZone':
				return Fields\DateTime::getTimeZone();
			case 'UserTimeZone':
				$userModel = User::getCurrentUserModel();
				return ($userModel && $userModel->getDetail('time_zone')) ? $userModel->getDetail('time_zone') : Config::main('default_timezone');
			default:
				return $key;
		}
	}

	/**
	 * Parsing record data.
	 *
	 * @param string $params
	 * @param mixed  $isPermitted
	 *
	 * @return string
	 */
	protected function record($params, $isPermitted = true)
	{
		if (!isset($this->recordModel) || ($isPermitted && !Privilege::isPermitted($this->moduleName, 'DetailView', $this->record))) {
			return '';
		}
		[$key, $params] = array_pad(explode('|', $params, 2), 2, false);
		if ($this->recordModel->has($key)) {
			$fieldModel = $this->recordModel->getModule()->getFieldByName($key);
			if (!$fieldModel || !$this->useValue($fieldModel, $this->moduleName)) {
				return '';
			}
			return $this->getDisplayValueByField($fieldModel, false, $params);
		}
		switch ($key) {
			case 'CrmDetailViewURL':
				return Config::main('site_URL') . 'index.php?module=' . $this->moduleName . '&view=Detail&record=' . $this->record;
			case 'PortalDetailViewURL':
				$recorIdName = 'id';
				if ('HelpDesk' === $this->moduleName) {
					$recorIdName = 'ticketid';
				} elseif ('Faq' === $this->moduleName) {
					$recorIdName = 'faqid';
				} elseif ('Products' === $this->moduleName) {
					$recorIdName = 'productid';
				}
				return Config::main('PORTAL_URL') . '/index.php?module=' . $this->moduleName . '&action=index&' . $recorIdName . '=' . $this->record;
			case 'ModuleName':
				return $this->moduleName;
			case 'RecordId':
				return $this->record;
			case 'RecordLabel':
				return $this->recordModel->getName();
			case 'ChangesListChanges':
				$value = '';
				foreach ($this->recordModel->getPreviousValue() as $fieldName => $oldValue) {
					$fieldModel = $this->recordModel->getModule()->getFieldByName($fieldName);
					if (!$fieldModel) {
						continue;
					}
					$oldValue = $this->getDisplayValueByField($fieldModel, $oldValue);
					$currentValue = $this->getDisplayValueByField($fieldModel);
					if ($this->withoutTranslations) {
						$value .= "\$(translate : {$this->moduleName}|{$fieldModel->getFieldLabel()})\$ \$(translate : LBL_FROM)\$ $oldValue \$(translate : LBL_TO)\$ " . $currentValue . ($this->isHtml ? '<br>' : PHP_EOL);
					} else {
						$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName, $this->language) . ' ';
						$value .= Language::translate('LBL_FROM') . " $oldValue " . Language::translate('LBL_TO') . " $currentValue" . ($this->isHtml ? '<br>' : PHP_EOL);
					}
				}
				return $value;
			case 'ChangesListValues':
				$value = '';
				$changes = $this->recordModel->getPreviousValue();
				if (empty($changes)) {
					$changes = array_filter($this->recordModel->getData());
					unset($changes['createdtime'], $changes['modifiedtime'], $changes['id'], $changes['newRecord'], $changes['modifiedby']);
				}
				foreach ($changes as $fieldName => $oldValue) {
					$fieldModel = $this->recordModel->getModule()->getFieldByName($fieldName);
					if (!$fieldModel) {
						continue;
					}
					$currentValue = \in_array($fieldModel->getFieldDataType(), $this->largeDataUiTypes) ? '' : $this->getDisplayValueByField($fieldModel);
					if ($this->withoutTranslations) {
						$value .= "\$(translate : {$this->moduleName}|{$fieldModel->getFieldLabel()})\$: $currentValue" . ($this->isHtml ? '<br>' : PHP_EOL);
					} else {
						$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName, $this->language) . ": $currentValue" . ($this->isHtml ? '<br>' : PHP_EOL);
					}
				}
				return $value;
			case 'SummaryFields':
					$value = '';
					$recordStructure = \Vtiger_RecordStructure_Model::getInstanceFromRecordModel($this->recordModel, \Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
					$fields = $recordStructure->getStructure()['SUMMARY_FIELDS'] ?? [];
					foreach ($fields as $fieldName => $fieldModel) {
						$currentValue = $this->getDisplayValueByField($fieldModel);
						if ($this->withoutTranslations) {
							$value .= "\$(translate : {$this->moduleName}|{$fieldModel->getFieldLabel()})\$: $currentValue" . ($this->isHtml ? '<br>' : PHP_EOL);
						} else {
							$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName, $this->language) . ": $currentValue" . ($this->isHtml ? '<br>' : PHP_EOL);
						}
					}
					return $value;
			default:
				if (false !== strpos($key, ' ')) {
					[$key, $params] = explode(' ', $key);
				}
				if ('Comments' === $key) {
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
		$params = explode('|', $params);
		$fieldName = array_shift($params);
		$relatedField = array_shift($params);
		$relatedModule = array_shift($params);
		$value = $params ? $relatedField . '|' . implode('|', $params) : $relatedField;
		if (
			!isset($this->recordModel)
			|| ($this->permissions && !Privilege::isPermitted($this->moduleName, 'DetailView', $this->record))
			|| $this->recordModel->isEmpty($fieldName)
		) {
			return '';
		}
		$relatedId = $this->recordModel->get($fieldName);
		if (empty($relatedId)) {
			return '';
		}
		if (empty($relatedModule) && \in_array($this->recordModel->getField($fieldName)->getFieldDataType(), ['owner', 'sharedOwner'])) {
			$relatedModule = 'Users';
		}
		if ('Users' === $relatedModule) {
			$return = [];
			foreach (explode(',', $relatedId) as $relatedValueId) {
				if ('Users' === Fields\Owner::getType($relatedValueId)) {
					$userRecordModel = \Vtiger_Record_Model::getInstanceById($relatedValueId, $relatedModule);
					if ('Active' === $userRecordModel->get('status')) {
						$instance = static::getInstanceByModel($userRecordModel);
						foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
							if (isset($this->{$key})) {
								$instance->{$key} = $this->{$key};
							}
						}
						$return[] = $instance->record($value, false);
					}
					continue;
				}
				foreach (PrivilegeUtil::getUsersByGroup($relatedValueId) as $userId) {
					$userRecordModel = \Vtiger_Record_Model::getInstanceById($userId, $relatedModule);
					if ('Active' === $userRecordModel->get('status')) {
						$instance = static::getInstanceByModel($userRecordModel);
						foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
							if (isset($this->{$key})) {
								$instance->{$key} = $this->{$key};
							}
						}
						$return[] = $instance->record($value, false);
					}
				}
			}
			return implode($this->relatedRecordSeparator, $return);
		}
		$module = Record::getType($relatedId);
		if (!Record::isExists($relatedId) || empty($module) || ($relatedModule && $relatedModule !== $module)) {
			return '';
		}
		$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedId, $module);
		if ($this->permissions && !$relatedRecordModel->isViewable()) {
			return '';
		}
		$instance = static::getInstanceByModel($relatedRecordModel);
		foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
			if (isset($this->{$key})) {
				$instance->{$key} = $this->{$key};
			}
		}
		return $instance->record($value);
	}

	/**
	 * Parsing related record data.
	 *
	 * @param string $params
	 *
	 * @return mixed
	 */
	protected function relatedRecordLevel($params)
	{
		[$fieldName, $relatedModule, $relatedRecord] = array_pad(explode('|', $params, 3), 3, '');
		if (
			!isset($this->recordModel)
			|| !Privilege::isPermitted($this->moduleName, 'DetailView', $this->record)
			|| $this->recordModel->isEmpty($fieldName)
		) {
			return '';
		}
		$relatedId = $this->recordModel->get($fieldName);
		if (empty($relatedId)) {
			return '';
		}
		$moduleName = Record::getType($relatedId);
		if (!empty($moduleName) && ($relatedModule && $relatedModule !== $moduleName)) {
			return '';
		}
		if ('Users' === $relatedModule && 'Users' === Fields\Owner::getType($relatedId)) {
			$relatedRecordModel = \Users_Privileges_Model::getInstanceById($relatedId);
			if ('Active' !== $relatedRecordModel->get('status')) {
				return '';
			}
		} else {
			$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedId, $moduleName);
			if (!$relatedRecordModel->isViewable()) {
				return '';
			}
		}
		$instance = static::getInstanceByModel($relatedRecordModel);
		foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
			if (isset($this->{$key})) {
				$instance->{$key} = $this->{$key};
			}
		}
		return $instance->relatedRecord($relatedRecord);
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
			if (isset($this->{$key})) {
				$instance->{$key} = $this->{$key};
			}
		}
		return $instance->record($fieldName);
	}

	/**
	 * Parsing related records list.
	 *
	 * @param string $params Parameter construction: RelatedModuleNameOrRelationId|Columns|Conditions|CustomViewIdOrName|Limit, Example: Contacts|firstname,lastname,modifiedtime|[[["firstname","a","Tom"]]]||2
	 *
	 * @return string
	 */
	protected function relatedRecordsList($params)
	{
		[$relatedModuleName, $columns, $conditions, $viewIdOrName, $limit, $maxLength] = array_pad(explode('|', $params), 6, '');
		if (is_numeric($relatedModuleName)) {
			if ($relationListView = \Vtiger_RelationListView_Model::getInstance($this->recordModel, '', $relatedModuleName)) {
				$relatedModuleName = $relationListView->getRelatedModuleModel()->getName();
			}
		} else {
			$relationListView = \Vtiger_RelationListView_Model::getInstance($this->recordModel, $relatedModuleName);
		}
		if (!$relationListView || !Privilege::isPermitted($relatedModuleName)) {
			return '';
		}
		$pagingModel = new \Vtiger_Paging_Model();
		$pagingModel->set('limit', (int) $limit);
		if ($viewIdOrName) {
			if (!is_numeric($viewIdOrName)) {
				$customView = CustomView::getInstance($relatedModuleName);
				if ($cvId = $customView->getViewIdByName($viewIdOrName)) {
					$viewIdOrName = $cvId;
				} else {
					$viewIdOrName = false;
					Log::warning("No view found. Module: $relatedModuleName, view name: $viewIdOrName", 'TextParser');
				}
			}
			if ($viewIdOrName) {
				$relationListView->getQueryGenerator()->initForCustomViewById($viewIdOrName);
			}
			if ($cvId && ($customViewModel = \CustomView_Record_Model::getInstanceById($cvId)) && ($orderBy = $customViewModel->getSortOrderBy())) {
				$relationListView->set('orderby', $orderBy);
			}
		}
		if ($columns) {
			$relationListView->setFields($columns);
		} else {
			$fields = array_filter($relationListView->getHeaders(), fn ($fieldModel) => !$fieldModel->get('fromOutsideList'));
			$relationListView->setFields(array_keys($fields));
		}
		if ($conditions) {
			$transformedSearchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition(Json::decode($conditions));
			$relationListView->set('search_params', $transformedSearchParams);
		}
		return $this->relatedRecordsListPrinter($relationListView, $pagingModel, (int) $maxLength);
	}

	/**
	 * Printer related records list.
	 *
	 * @param \Vtiger_RelationListView_Model $relationListView
	 * @param \Vtiger_Paging_Model           $pagingModel
	 * @param int                            $maxLength
	 *
	 * @return string
	 */
	protected function relatedRecordsListPrinter(\Vtiger_RelationListView_Model $relationListView, \Vtiger_Paging_Model $pagingModel, int $maxLength): string
	{
		$relatedModuleName = $relationListView->getRelationModel()->getRelationModuleName();
		$rows = $headers = '';
		$fields = $relationListView->getRelationModel()->getQueryFields();
		foreach ($fields as $fieldModel) {
			if ($fieldModel->isViewable() || $fieldModel->get('fromOutsideList')) {
				if ($this->withoutTranslations) {
					$headers .= "<th class=\"col-type-{$fieldModel->getFieldType()}\">$(translate : {$fieldModel->getFieldLabel()}|$relatedModuleName)$</th>";
				} else {
					$headers .= "<th class=\"col-type-{$fieldModel->getFieldType()}\">" . Language::translate($fieldModel->getFieldLabel(), $relatedModuleName) . '</th>';
				}
			}
		}
		$counter = 0;
		foreach ($relationListView->getEntries($pagingModel) as $relatedRecordModel) {
			++$counter;
			$rows .= '<tr class="row-' . $counter . '">';
			foreach ($fields as $fieldModel) {
				$value = $this->getDisplayValueByField($fieldModel, $relatedRecordModel);
				if (false !== $value) {
					if ($maxLength) {
						$value = TextUtils::textTruncate($value, $maxLength);
					}
					$rows .= "<td class=\"col-type-{$fieldModel->getFieldType()}\">{$value}</td>";
				}
			}
			$rows .= '</tr>';
		}
		return empty($rows) ? '' : "<table style=\"border-collapse:collapse;width:100%\" class=\"related-records-list\"><thead><tr>{$headers}</tr></thead><tbody>{$rows}</tbody></table>";
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
		[$moduleName, $columns, $conditions, $viewIdOrName, $limit, $maxLength, $params] = array_pad(explode('|', $params, 7), 7, '');
		$paramsArray = $params ? self::parseFieldParam($params) : [];
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
		if ($cvId && ($customViewModel = \CustomView_Record_Model::getInstanceById($cvId)) && ($orderBy = $customViewModel->getSortOrderBy())) {
			$listView->set('orderby', $orderBy);
		}
		$limit = (int) $limit;
		$listView->getQueryGenerator()->setLimit((int) ($limit ?: \App\Config::main('list_max_entries_per_page', 20)));
		if ($columns) {
			$headerFields = [];
			foreach (explode(',', $columns) as $fieldName) {
				$headerFields[] = [
					'field_name' => $fieldName,
					'module_name' => $moduleName,
				];
			}
			$listView->set('header_fields', $headerFields);
			$listView->getQueryGenerator()->setFields(explode(',', $columns));
			$listView->getQueryGenerator()->setField('id');
		}
		if ($conditions) {
			$transformedSearchParams = $listView->getQueryGenerator()->parseBaseSearchParamsToCondition(Json::decode($conditions));
			$listView->set('search_params', $transformedSearchParams);
		}
		if (($pdf = $this->getParam('pdf')) && $pdf->get('module_name') === $moduleName && ($ids = $pdf->getVariable('recordsId'))) {
			$listView->getQueryGenerator()->addCondition('id', $ids, 'e', 1);
		}
		$rows = $headers = $headerStyle = $borderStyle = '';
		$fields = $listView->getListViewHeaders();
		if (isset($paramsArray['headerStyle']) && 'background' === $paramsArray['headerStyle']) {
			$headerStyle = 'background-color:#ddd;';
		}
		if (isset($paramsArray['table']) && 'border' === $paramsArray['table']) {
			$borderStyle = 'border:1px solid  #ddd;';
		}
		foreach ($fields as $fieldModel) {
			if ($this->withoutTranslations) {
				$headers .= "<th class=\"col-type-{$fieldModel->getFieldType()}\" style=\"{$headerStyle}\">$(translate : {$fieldModel->getFieldLabel()}|$moduleName)$</th>";
			} else {
				$headers .= "<th class=\"col-type-{$fieldModel->getFieldType()}\" style=\"{$headerStyle}\">" . Language::translate($fieldModel->getFieldLabel(), $moduleName) . '</th>';
			}
		}
		$counter = 0;
		foreach ($listView->getAllEntries() as $relatedRecordModel) {
			++$counter;
			$rows .= '<tr class="row-' . $counter . '">';
			foreach ($fields as $fieldModel) {
				$value = $this->getDisplayValueByField($fieldModel, $relatedRecordModel, $params);
				if (false !== $value) {
					if ((int) $maxLength) {
						$value = TextUtils::textTruncate($value, (int) $maxLength);
					}
					$rows .= "<td class=\"col-type-{$fieldModel->getFieldType()}\" style=\"{$borderStyle}\">{$value}</td>";
				}
			}
			$rows .= '</tr>';
		}
		if (empty($rows)) {
			return '';
		}
		$headers = "<tr>{$headers}</tr>";
		$table = "class=\"records-list\" style=\"border-collapse:collapse;width:100%;{$borderStyle}\"";
		if (isset($paramsArray['addCounter']) && '1' === $paramsArray['addCounter']) {
			$headers = '<tr><th colspan="' . \count($fields) . '">' . Language::translate('LBL_NUMBER_OF_ALL_ENTRIES') . ": $counter</th></th></tr>$headers";
		}
		return "<table {$table}><thead>{$headers}</thead><tbody>{$rows}</tbody></table>";
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
		$model = $this->recordModel;
		if (false === $value) {
			$value = \App\Utils\Completions::decode($this->recordModel->get($fieldModel->getName()), \App\Utils\Completions::FORMAT_TEXT);
			if (!$fieldModel->isViewEnabled() && !$fieldModel->get('fromOutsideList')) {
				return '';
			}
		} elseif (\is_object($value)) {
			$model = $value;
			$value = $value->get($fieldModel->getName());
			if (!$fieldModel->isViewEnabled() && !$fieldModel->get('fromOutsideList')) {
				return false;
			}
		}
		if ('' === $value) {
			return '';
		}
		if ($this->withoutTranslations) {
			return $this->getDisplayValueByType($value, $model, $fieldModel, $params);
		}
		return $fieldModel->getUITypeModel()->getTextParserDisplayValue($value, $model, $params);
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
				$value = (1 === $value) ? 'LBL_YES' : 'LBL_NO';
				$value = "$(translate : $value)$";
				break;
			case 'multipicklist':
				$value = explode(' |##| ', $value);
				$trValue = [];
				$countValue = \count($value);
				for ($i = 0; $i < $countValue; ++$i) {
					$trValue[] = "$(translate : {$recordModel->getModuleName()}|{$value[$i]})$";
				}
				if (\is_array($trValue)) {
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
				if (12 === (int) $userModel->get('hour_format')) {
					if ($value) {
						[$hours, $minutes] = array_pad(explode(':', $value), 2, '');
						$format = '$(translate : PM)$';
						if ($hours > 12) {
							$hours = (int) $hours - 12;
						} elseif ($hours < 12) {
							$format = '$(translate : AM)$';
						}
						//If hours zero then we need to make it as 12 AM
						if ('00' == $hours) {
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
				$value = $parentName = '';
				if ($row = Fields\Tree::getValueByTreeId($template, $value)) {
					if ($row['depth'] > 0) {
						$pieces = explode('::', $row['parentTree']);
						end($pieces);
						$parent = prev($pieces);
						$parentRow = Fields\Tree::getValueByTreeId($template, $parent);
						$parentName = "($(translate : {$recordModel->getModuleName()}|{$parentRow['name']})$) ";
					}
					$value = $parentName . "$(translate : {$recordModel->getModuleName()}|{$row['name']})$";
				}
				break;
			default:
				return $fieldModel->getUITypeModel()->getTextParserDisplayValue($value, $recordModel, $params);
		}
		return $value;
	}

	/**
	 * Get last comments.
	 *
	 * @param mixed $params
	 *
	 * @return string
	 */
	protected function getComments($params = false)
	{
		[$limit, $showAuthor] = array_pad(explode('|', $params, 2), 2, false);
		$query = (new \App\Db\Query())->select(['commentcontent', 'userid'])->from('vtiger_modcomments')->where(['related_to' => $this->record])->orderBy(['modcommentsid' => SORT_DESC]);
		if ($limit) {
			$query->limit($limit);
		}
		$commentsList = '';
		foreach ($query->all() as $comment) {
			if ('' != $comment['commentcontent']) {
				$commentsList .= '<br><br>';
				if ('true' === $showAuthor) {
					$commentsList .= Purifier::encodeHtml(\App\Fields\Owner::getUserLabel($comment['userid'])) . ': ';
				}
				$commentsList .= nl2br($comment['commentcontent']);
			}
		}
		return ltrim($commentsList, '<br><br>');
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
	 * @param string $key
	 *
	 * @return string
	 */
	protected function params(string $key)
	{
		return isset($this->params[$key]) ? \App\Purifier::purifyHtml($this->params[$key]) : '';
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
		$instance = null;
		if (false !== strpos($params, '||')) {
			$params = explode('||', $params);
			$parserName = array_shift($params);
			$baseParams = $params;
			$params = [];
		} else {
			$params = explode('|', $params);
			$parserName = array_shift($params);
			$baseParams = $params;
		}
		$module = false;
		if (!empty($params)) {
			$module = array_shift($params);
			if (!Module::getModuleId($module)) {
				$module = $this->moduleName;
			}
		}
		$className = "\\App\\TextParser\\$parserName";
		if ($module && $handlerClass = \Vtiger_Loader::getComponentClassName('TextParser', $parserName, $module, false)) {
			$className = $handlerClass;
		}
		if (!class_exists($className)) {
			Log::error("Not found custom class: $parserName|{$module}");
		} else {
			$instance = new $className($this, $baseParams);
		}
		return $instance && $instance->isActive() ? $instance->process() : '';
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
		$cacheKey = "{$this->moduleName}|$fieldType";
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
		if (empty(self::$sourceModules[$this->moduleName])) {
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
		foreach (self::$sourceModules[$this->moduleName] as $moduleName) {
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
	 * @param bool        $skipEmpty
	 *
	 * @return array
	 */
	public function getRelatedVariable($fieldType = false, $skipEmpty = false)
	{
		$cacheKey = "{$this->moduleName}|$fieldType|{$skipEmpty}";
		if (isset(static::$relatedVariable[$cacheKey])) {
			return static::$relatedVariable[$cacheKey];
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($this->moduleName);
		$variables = [];
		$entityVariables = Language::translate('LBL_ENTITY_VARIABLES', 'Other.TextParser');
		foreach ($moduleModel->getFieldsByType(array_merge(\Vtiger_Field_Model::$referenceTypes, ['userCreator', 'owner', 'sharedOwner'])) as $parentFieldName => $field) {
			if ('owner' === $field->getFieldDataType() || 'sharedOwner' === $field->getFieldDataType()) {
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
			$relRecord = false;
			if ($skipEmpty && $this->recordModel && !(($relId = $this->recordModel->get($field->getName()))
				&& (
					\in_array($field->getFieldDataType(), ['userCreator', 'owner', 'sharedOwner'])
					|| ((Record::isExists($relId)) && ($relRecord = \Vtiger_Record_Model::getInstanceById($relId))->isViewable() && ($relatedModules = [Record::getType($relId)]))
				)
			)) {
				continue;
			}

			foreach ($relatedModules as $relatedModule) {
				$relatedModuleLang = Language::translate($relatedModule, $relatedModule);
				foreach (\Vtiger_Module_Model::getInstance($relatedModule)->getBlocks() as $blockModel) {
					foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
						if (
							$fieldModel->isViewable()
							&& !($fieldType && $fieldModel->getFieldDataType() !== $fieldType)
							&& (!$relRecord || ($relRecord && !$relRecord->isEmpty($fieldModel->getName())))
						) {
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
	 * Get related variables.
	 *
	 * @param bool|string $fieldType
	 *
	 * @return array
	 */
	public function getRelatedLevelVariable($fieldType = false)
	{
		$cacheKey = "{$this->moduleName}|$fieldType";
		if (isset(static::$relatedVariableLevel[$cacheKey])) {
			return static::$relatedVariableLevel[$cacheKey];
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($this->moduleName);
		$variables = [];
		foreach ($moduleModel->getFieldsByType(array_merge(\Vtiger_Field_Model::$referenceTypes, ['userCreator', 'owner'])) as $parentFieldName => $fieldModel) {
			if ('owner' === $fieldModel->getFieldDataType()) {
				$relatedModules = ['Users'];
			} else {
				$relatedModules = $fieldModel->getReferenceList();
			}
			$parentFieldNameLabel = Language::translate($fieldModel->getFieldLabel(), $this->moduleName);
			foreach ($relatedModules as $relatedModule) {
				$relatedModuleLang = Language::translate($relatedModule, $relatedModule);
				foreach (\Vtiger_Module_Model::getInstance($relatedModule)->getFieldsByType(array_merge(\Vtiger_Field_Model::$referenceTypes, ['userCreator', 'owner', 'sharedOwner'])) as $parentFieldNameNextLevel => $fieldModelNextLevel) {
					if ('owner' === $fieldModelNextLevel->getFieldDataType() || 'sharedOwner' === $fieldModelNextLevel->getFieldDataType()) {
						$relatedModulesNextLevel = ['Users'];
					} else {
						$relatedModulesNextLevel = $fieldModelNextLevel->getReferenceList();
					}
					$parentFieldNameLabelNextLevel = Language::translate($fieldModelNextLevel->getFieldLabel(), $relatedModule);
					foreach ($relatedModulesNextLevel as $relatedModuleNextLevel) {
						$relatedModuleLangNextLevel = Language::translate($relatedModuleNextLevel, $relatedModuleNextLevel);
						foreach (\Vtiger_Module_Model::getInstance($relatedModuleNextLevel)->getBlocks() as $blockModel) {
							foreach ($blockModel->getFields() as $fieldName => $fieldModel) {
								if ($fieldModel->isViewable() && !($fieldType && $fieldModel->getFieldDataType() !== $fieldType)) {
									$labelGroup = "{$parentFieldNameLabel}($relatedModuleLang) -> {$parentFieldNameLabelNextLevel}($relatedModuleLangNextLevel) " . Language::translate($blockModel->get('label'), $relatedModuleNextLevel);
									$variables[$labelGroup][] = [
										'var_value' => "$(relatedRecordLevel : $parentFieldName|$relatedModule|$parentFieldNameNextLevel|$fieldName|$relatedModuleNextLevel)$",
										'var_label' => "$(translate : $relatedModuleNextLevel|{$fieldModel->getFieldLabel()})$",
										'label' => "{$parentFieldNameLabel}($relatedModuleLang) -> {$parentFieldNameLabelNextLevel}($relatedModuleLangNextLevel) " . Language::translate($fieldModel->getFieldLabel(), $relatedModuleNextLevel),
									];
								}
							}
						}
					}
				}
			}
		}
		static::$relatedVariableLevel[$cacheKey] = $variables;
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
			'LBL_ENTITY_VARIABLES' => array_map(fn ($value) => Language::translate($value, 'Other.TextParser'), array_flip(static::$variableGeneral)),
		];
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
		foreach ((new \DirectoryIterator(__DIR__ . \DIRECTORY_SEPARATOR . 'TextParser')) as $fileInfo) {
			$fileName = $fileInfo->getBasename('.php');
			if ('dir' !== $fileInfo->getType() && 'Base' !== $fileName && 'php' === $fileInfo->getExtension()) {
				$className = '\App\TextParser\\' . $fileName;
				if (!class_exists($className)) {
					Log::warning('Not found custom class');
					continue;
				}
				$instance = new $className($this);
				if (isset($this->type) && $this->type !== $instance->type) {
					continue;
				}
				$key = $instance->default ?? "$(custom : $fileName)$";
				$variables[$key] = Language::translate($instance->name, 'Other.TextParser');
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
				if ('dir' !== $fileInfo->getType() && 'php' === $fileInfo->getExtension()) {
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
		foreach ($moduleModel->getRelations() as $relation) {
			$var = $relation->get('relatedModuleName');
			if ($relation->get('field_name')) {
				$var = $relation->get('relation_id');
			}
			$variables[] = [
				'key' => "$(relatedRecordsList : $var|__FIELDS_NAME__|__CONDITIONS__|__VIEW_ID_OR_NAME__|__LIMIT__|__MAX_LENGTH__)$",
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
	 * Gets user variables.
	 *
	 * @param string $text
	 * @param bool   $useRegex
	 *
	 * @return array
	 */
	public function getUserVariables(string $text, bool $useRegex = true)
	{
		$data = [];
		if ($useRegex) {
			preg_match_all('/\$\(userVariable : ([,"\+\%\.\=\-\[\]\&\w\s\|\)\(\:]+)\)\$/u', str_replace(['%20%3A%20', '%20:%20'], ' : ', $text), $matches);
			$matches = $matches[1] ?? [];
		} else {
			$matches = [$text];
		}
		foreach ($matches as $param) {
			$part = self::parseFieldParam($param);
			if (!empty($part['name']) && !(isset($data[$part['name']]))) {
				$data[$part['name']] = $part;
			}
		}
		return $data;
	}

	/**
	 * Parsing user variable.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function userVariable($params)
	{
		$instance = null;
		$className = '\\App\\TextParser\\' . ucfirst(__FUNCTION__);
		if (!class_exists($className)) {
			Log::error("Not found custom class: $className");
		} else {
			$instance = new $className($this, $params);
		}
		return $instance && $instance->isActive() ? $instance->process() : '';
	}

	/**
	 * Parsing inventory.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function inventory($params)
	{
		if (!$this->recordModel->getModule()->isInventory()) {
			return '';
		}
		$config = $this->parseParams($params);
		if ('table' === $config['type']) {
			return $this->getInventoryTable($config);
		}
		return '';
	}

	/**
	 * Get an instance of barcode text parser.
	 *
	 * @param string $params
	 *
	 * @return string
	 */
	protected function barcode($params): string
	{
		$params = $this->parseParams($params);
		if (isset($params['value'])) {
			$valueForParse = $params['value'];
		}
		if (isset($params['fieldName'])) {
			$valueForParse = $this->recordModel->get($params['fieldName']);
		}
		if ($valueForParse) {
			$className = '\Milon\Barcode\\' . $params['class'];
			if (!class_exists($className)) {
				throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND||' . $className);
			}
			$qrCodeGenerator = new $className();
			$qrCodeGenerator->setStorPath(__DIR__ . Config::main('tmp_dir'));
			$barcodeHeight = $this->params['height'] ?? 2;
			$barcodeWidth = $this->params['width'] ?? 30;
			$barcodeType = $this->params['type'] ?? 'EAN13';
			$showText = $this->params['showText'] ?? true;
			$png = $qrCodeGenerator->getBarcodePNG($valueForParse, $barcodeType, $barcodeHeight, $barcodeWidth, [0, 0, 0], $showText);
			return '<img src="data:image/png;base64,' . $png . '"/>';
		}
		return '';
	}

	/**
	 * Get inventory param.
	 *
	 * @param string $params
	 *
	 * @return array
	 */
	protected function parseParams(string $params): array
	{
		preg_match('/type=(\w+)/', $params, $matches);
		$config = [
			'type' => ($matches[1] ?? false),
		];
		$params = ltrim($params, $matches[0] . ' ');
		foreach (explode(' , ', $params) as $value) {
			parse_str($value, $row);
			$config += $row;
		}
		if (isset($config['columns'])) {
			$config['columns'] = explode(',', $config['columns']);
		}
		return $config;
	}

	/**
	 * Parsing inventory table.
	 *
	 * @param array $config
	 *
	 * @return string
	 */
	public function getInventoryTable(array $config): string
	{
		$rawText = empty($config['href']) || 'yes' !== $config['href'];
		$inventory = \Vtiger_Inventory_Model::getInstance($this->moduleName);
		$fields = $inventory->getFieldsByBlocks();
		$inventoryRows = $this->recordModel->getInventoryData();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		$html = '';
		if (!empty($fields[1])) {
			$fieldsTextAlignRight = ['Unit', 'TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity', 'TaxPercent'];
			$fieldsWithCurrency = ['TotalPrice', 'Purchase', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Discount', 'Margin', 'Tax'];
			$html .= '<table class="inventory-table" style="border-collapse:collapse;width:100%"><thead><tr>';
			$columns = [];
			$customFieldClassSeq = 0;
			$labels = isset($config['labels']) ? explode(',', $config['labels']) : [];
			$width = isset($config['width']) ? preg_replace('/[^[:alnum:]]/', '', explode(',', $config['width'])) : [];
			foreach ($config['columns'] as $key => $name) {
				if (false !== strpos($name, '||')) {
					[$title,$value] = explode('||', $name, 2);
					if ('(' === $title[0] && ')' === substr($title, -1)) {
						$title = $this->parseVariable("\${$title}\$");
					}
					++$customFieldClassSeq;
					$html .= '<th class="col-type-customField' . $customFieldClassSeq . '" style="border:1px solid #ddd">' . $title . '</th>';
					$columns[$title] = $value;
					continue;
				}
				if ('seq' === $name) {
					$html .= '<th class="col-type-ItemNumber" style="border:1px solid #ddd">' . Language::translate('LBL_ITEM_NUMBER', $this->moduleName) . '</th>';
					$columns[$name] = false;
					continue;
				}
				if (empty($fields[1][$name]) && empty($fields[2][$name])) {
					continue;
				}
				$field = $fields[1][$name] ?? $fields[2][$name];
				if (!$field->isVisible()) {
					continue;
				}
				$html .= '<th class="col-type-' . $field->getType() . '" style="border:1px solid #ddd ' . (empty($width[$key]) ? '' : ";width: {$width[$key]}") . '">' . (empty($labels[$key]) ? Language::translate($field->get('label'), $this->moduleName) : Purifier::encodeHtml($labels[$key])) . '</th>';
				$columns[$field->getColumnName()] = $field;
			}
			$html .= '</tr></thead><tbody>';
			$counter = 0;
			foreach ($inventoryRows as $inventoryRow) {
				++$counter;
				$html .= '<tr class="row-' . $counter . '">';
				$customFieldClassSeq = 0;
				foreach ($columns as $name => $field) {
					if ('seq' === $name) {
						$html .= '<td class="col-type-ItemNumber" style="border:1px solid #ddd;font-weight:bold;">' . $counter . '</td>';
					} elseif (!\is_object($field)) {
						if ('(' === $field[0] && ')' === substr($field, -1)) {
							$field = $this->parseVariable("\${$field}\$", $inventoryRow['name'] ?? 0);
						}
						++$customFieldClassSeq;
						$html .= '<td class="col-type-customField' . $customFieldClassSeq . '" style="border:1px solid #ddd;font-weight:bold;">' . $field . '</td>';
					} elseif ('ItemNumber' === $field->getType()) {
						$html .= '<td class="col-type-ItemNumber" style="border:1px solid #ddd;font-weight:bold;">' . $counter . '</td>';
					} elseif ('ean' === $name) {
						$itemValue = $inventoryRow[$name];
						$html .= '<td class="col-type-barcode" style="border:1px solid #ddd;padding:0px 4px;' . (\in_array($field->getType(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '"><div data-barcode="EAN13" data-code="' . $itemValue . '" data-size="1" data-height="16"></div></td>';
					} else {
						$itemValue = $inventoryRow[$name];
						$html .= '<td class="col-type-' . $field->getType() . '" style="border:1px solid #ddd;padding:0px 4px;' . (\in_array($field->getType(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '">';
						if ('Name' === $field->getType()) {
							$html .= '<strong>' . $field->getDisplayValue($itemValue, $inventoryRow, $rawText) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
									$comment = $commentField->getDisplayValue($value, $inventoryRow, $rawText);
									if ($comment) {
										$html .= '<br>' . $comment;
									}
								}
							}
						} elseif (\in_array($field->getType(), $fieldsWithCurrency, true)) {
							$html .= \CurrencyField::appendCurrencySymbol($field->getDisplayValue($itemValue, $inventoryRow, $rawText), $currencySymbol);
						} else {
							$html .= $field->getDisplayValue($itemValue, $inventoryRow, $rawText);
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}

			$html .= '</tbody><tfoot><tr>';
			foreach ($columns as $name => $field) {
				$tb = $style = '';
				if (\is_object($field) && $field->isSummary()) {
					$style = 'border:1px solid #ddd;';
					$sum = 0;
					foreach ($inventoryRows as $inventoryRow) {
						$sum += $inventoryRow[$name];
					}
					$tb = \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
				}
				$html .= '<th class="col-type-' . (\is_object($field) ? $field->getType() : $name) . '" style="padding:0px 4px;text-align:right;' . $style . '">' . $tb . '</th>';
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}

	/**
	 * Parse variable.
	 *
	 * @param string $variable
	 * @param int    $id
	 *
	 * @return string
	 */
	protected function parseVariable(string $variable, int $id = 0): string
	{
		if ($id && Record::isExists($id)) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id);
			if (!$recordModel->isViewable()) {
				return '';
			}
			$instance = static::getInstanceByModel($recordModel);
		} else {
			$instance = static::getInstance();
		}
		foreach (['withoutTranslations', 'language', 'emailoptout'] as $key) {
			if (isset($this->{$key})) {
				$instance->{$key} = $this->{$key};
			}
		}
		$instance->setContent($variable)->parse();
		return $instance->getContent();
	}

	/**
	 * Parse custom params.
	 *
	 * @param string $param
	 *
	 * @return array
	 */
	public static function parseFieldParam(string $param): array
	{
		$part = [];
		if ($param) {
			foreach (explode('|', $param) as $type) {
				[$name, $value] = array_pad(explode('=', $type, 2), 2, '');
				$part[$name] = $value;
			}
		}
		return $part;
	}
}
