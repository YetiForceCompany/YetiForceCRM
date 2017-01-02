<?php
namespace App;

/**
 * Text parser class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TextParser
{

	/** @var string[] List of available functions */
	protected static $baseFunctions = ['general', 'translate', 'record', 'companyDetail', 'employeeDetail'];

	/** @var array Examples of supported variables */
	public static $variableExamples = [
		'LBL_TRANSLATE' => '$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$, $(translate : LBL_SECONDS)$',
		'LBL_COMPANY_DETAIL' => '$(companyDetail : organizationname)$',
		'LBL_EMPLOYEE_NAME' => '$(employeeDetail : last_name)$',
		'LBL_CURRENT_DATE' => '$(general : CurrentDate)$',
		'LBL_CURRENT_TIME' => '$(general : CurrentTime)$',
		'LBL_SITE_URL' => '$(general : SiteUrl)$',
		'LBL_PORTAL_URL' => '$(general : PortalUrl)$',
		'LBL_CRM_DETAIL_VIEW_URL' => '$(record : CrmDetailViewURL)$',
		'LBL_PORTAL_DETAIL_VIEW_URL' => '$(record : PortalDetailViewURL)$',
		'LBL_RECORD_ID' => '$(record : RecordId)$',
		'LBL_RECORD_LABEL' => '$(record : RecordLabel)$',
		'LBL_LIST_OF_CHANGES_IN_RECORD' => '(record: ChangesListChanges)',
		'LBL_LIST_OF_NEW_VALUES_IN_RECORD' => '(record: ChangesListValues)',
	];

	/** @var int Record id */
	private $record;

	/** @var string Module name */
	private $moduleName;

	/** @var \Vtiger_Record_Model Record model */
	private $recordModel;

	/** @var string Content */
	private $content;

	/** @var string Rwa content */
	private $rawContent;
	private $withoutTranslations = false;

	/**
	 * Get instanace by record id
	 * @param int $record Record id
	 * @param string $moduleName Module name
	 * @return \self
	 */
	public static function getInstanceById($record, $moduleName)
	{
		$instance = new self();
		$instance->record = $record;
		$instance->moduleName = $moduleName;
		$instance->recordModel = \Vtiger_Record_Model::getInstanceById($record, $moduleName);
		return $instance;
	}

	/**
	 * Get instanace by record model
	 * @param \Vtiger_Record_Model $recordModel
	 * @return \self
	 */
	public static function getInstanceByModel(\Vtiger_Record_Model $recordModel)
	{
		$instance = new self();
		$instance->record = $recordModel->getId();
		$instance->moduleName = $recordModel->getModuleName();
		$instance->recordModel = $recordModel;
		return $instance;
	}

	/**
	 * Get clean instanace
	 * @param string $moduleName Module name
	 * @return \self
	 */
	public static function getInstance($moduleName = '')
	{
		$instance = new self();
		if ($moduleName) {
			$instance->moduleName = $moduleName;
		}
		return $instance;
	}

	/**
	 * Set content
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->rawContent = $this->content = $content;
	}

	/**
	 * Get content 
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Text parse function
	 * @return $this
	 */
	public function parse()
	{
		$this->content = preg_replace_callback('/\$\((\w+) : ([\w\s\|]+)\)\$/', function ($matches) {
			list($fullText, $function, $params) = $matches;
			if (in_array($function, static::$baseFunctions)) {
				return $this->$function($params);
			}
			return '';
		}, $this->content);
		return $this;
	}

	/**
	 * Parsing translations
	 * @param string $params
	 * @return string
	 */
	private function translate($params)
	{
		if (strpos($params, '|') === false) {
			return Language::translate($params);
		}
		$aparams = explode('|', $params);
		$moduleName = array_shift($aparams);
		if (Module::getModuleId($moduleName) !== false) {
			return Language::translate(ltrim($params, "$moduleName|"), $moduleName);
		}
		return Language::translate($params);
	}

	/**
	 * Parsing company detail
	 * @param string $fieldName
	 * @return string
	 */
	private function companyDetail($fieldName)
	{
		return \Vtiger_CompanyDetails_Model::getInstanceById()->get($fieldName);
	}

	/**
	 * Parsing employee detail
	 * @param string $fieldName
	 * @return mixed
	 */
	private function employeeDetail($fieldName)
	{
		$currentUserModel = \Users_Record_Model::getCurrentUserModel();
		$userId = $currentUserModel->getId();
		if (Cache::has('TextParserEmployeeDetail', $userId . $fieldName)) {
			return Cache::get('TextParserEmployeeDetail', $userId . $fieldName);
		}
		if (Cache::has('TextParserEmployeeDetailRows', $userId)) {
			$employee = Cache::get('TextParserEmployeeDetailRows', $userId);
		} else {
			$employee = (new Db\Query())->from('vtiger_ossemployees')
					->innerJoin('vtiger_crmentity', 'vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid')
					->innerJoin('vtiger_ossemployeescf', 'vtiger_ossemployees.ossemployeesid = vtiger_ossemployeescf.ossemployeesid')
					->where(['vtiger_crmentity.deleted' => 0, 'vtiger_crmentity.smownerid' => $userId])->limit(1)->one();
			if (!$employee) {
				$employee = '';
			}
			Cache::save('TextParserEmployeeDetailRows', $userId, $employee, Cache::LONG);
		}
		$value = '';
		if ($employee) {
			$moduleModel = \Vtiger_Module_Model::getInstance('OSSEmployees');
			$fieldModel = $moduleModel->getFieldByName($fieldName);
			$value = $fieldModel->getDisplayValue($employee[$fieldModel->get('column')], $employee['crmid'], false, true);
		}
		Cache::save('TextParserEmployeeDetail', $userId . $fieldName, $value, Cache::LONG);
		return $value;
	}

	/**
	 * Parsing general data
	 * @param string $key
	 * @return mixed
	 */
	private function general($key)
	{
		switch ($key) {
			case 'CurrentDate':
				return (new \DateTimeField(null))->getDisplayDate();
			case 'CurrentTime' : return \Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s'));
			case 'SiteUrl' : return \AppConfig::main('site_URL');
			case 'PortalUrl' : return \AppConfig::main('PORTAL_URL');
		}
		return $key;
	}

	/**
	 * Parsing record data
	 * @param string $key
	 * @return mixed
	 */
	private function record($key)
	{
		if (!isset($this->recordModel)) {
			return '';
		}
		if (!\Users_Privileges_Model::isPermitted($this->moduleName, 'DetailView', $this->record)) {
			return '';
		}
		switch ($key) {
			case 'CrmDetailViewURL' :
				return \AppConfig::main('site_URL') . 'index.php?module=' . $this->moduleName . '&view=Detail&record=' . $this->record;
			case 'PortalDetailViewURL' :
				$recorIdName = 'id';
				if ($this->moduleName === 'HelpDesk') {
					$recorIdName = 'ticketid';
				} elseif ($this->moduleName === 'Faq') {
					$recorIdName = 'faqid';
				} elseif ($this->moduleName === 'Products') {
					$recorIdName = 'productid';
				}
				return \AppConfig::main('PORTAL_URL') . '/index.php?module=' . $this->moduleName . '&action=index&' . $recorIdName . '=' . $this->record;
			case 'ModuleName' : return $this->moduleName;
			case 'RecordId' : return $this->record;
			case 'RecordLabel' : return $this->recordModel->getName();
			case 'ChangesListChanges':
				foreach ($this->recordModel->getPreviousValue() as $fieldName => $oldValue) {
					$fieldModel = $this->recordModel->getModule()->getField($fieldName);
					if (!$fieldModel) {
						continue;
					}
					$oldValue = $this->recordDisplayValue($oldValue, $fieldModel);
					$currentValue = $this->recordDisplayValue($this->recordModel->get($fieldName), $fieldModel);
					if ($this->withoutTranslations !== true) {
						$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName) . ' ';
						$value .= Language::translate('LBL_FROM') . " $oldValue " . Language::translate('LBL_TO') . " $currentValue" . PHP_EOL;
					} else {
						$value .= "$(translate: $this->moduleName|{$fieldModel->getFieldLabel()})$ $(translate: LBL_FROM)$ $oldValue $(translate: LBL_TO)$ " .
							$currentValue . PHP_EOL;
					}
				}
				return $value;
			case 'ChangesListValues':
				foreach ($this->recordModel->getPreviousValue() as $fieldName => $oldValue) {
					$fieldModel = $this->recordModel->getModule()->getField($fieldName);
					if (!$fieldModel) {
						continue;
					}
					$currentValue = $this->recordDisplayValue($this->recordModel->get($fieldName), $fieldModel);
					if ($this->withoutTranslations !== true) {
						$value .= Language::translate($fieldModel->getFieldLabel(), $this->moduleName) . ": $currentValue" . PHP_EOL;
					} else {
						$value .= "$(translate: $this->moduleName|{$fieldModel->getFieldLabel()})$: $currentValue" . PHP_EOL;
					}
				}
				return $value;
		}
		return $key;
	}

	/**
	 * Get record display value
	 * @param mixed $value
	 * @param \Vtiger_Field_Model $fieldModel
	 * @return string
	 */
	private function recordDisplayValue($value, \Vtiger_Field_Model $fieldModel)
	{
		if ($value === '') {
			return '-';
		}
		if ($this->withoutTranslations !== true) {
			return $fieldModel->getDisplayValue($value, $this->record, $this->recordModel, true);
		}
		switch ($fieldModel->getFieldDataType()) {
			case 'boolean':
				$value = ($value === 1) ? 'LBL_YES' : 'LBL_NO';
				break;
			case 'multipicklist':
				$value = explode(' |##| ', $value);
				$trValue = [];
				$countValue = count($value);
				for ($i = 0; $i < $countValue; $i++) {
					$trValue[] = "$(translate : $this->moduleName|{$value[$i]})$";
				}
				if (is_array($trValue)) {
					$trValue = implode(' |##| ', $trValue);
				}
				$value = str_ireplace(' |##| ', ', ', $trValue);
				break;
			case 'picklist':
				$value = "$(translate : $this->moduleName|$value)$";
				break;
			case 'time':
				$userModel = Users_Privileges_Model::getCurrentUserModel();
				$value = DateTimeField::convertToUserTimeZone(date('Y-m-d') . ' ' . $value)->format('H:i:s');
				if ($userModel->get('hour_format') === '12') {
					if ($value) {
						list($hours, $minutes, $seconds) = explode(':', $value);
						$format = '$(translate : PM)$';
						if ($hours > 12) {
							$hours = (int) $hours - 12;
						} else if ($hours < 12) {
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
				$parentName = '';
				$name = '';
				if ($row) {
					if ($row['depth'] > 0) {
						$parenttrre = $row['parenttrre'];
						$pieces = explode('::', $parenttrre);
						end($pieces);
						$parent = prev($pieces);
						$parentRow = Fields\Tree::getValueByTreeId($template, $parent);
						$parentName = "($(translate : $this->moduleName|{$parentRow['name']})$) ";
					}
					$name = $parentName . "$(translate : $this->moduleName|{$row['name']})$";
				}
				break;
			default:
				return $fieldModel->getDisplayValue($value, $this->record, $this->recordModel, true);
				break;
		}
		return "$(translate : $value)$";
	}
}
