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
	protected static $baseFunctions = ['general', 'translate', 'companyDetail', 'recordChanges', 'employeeDetail'];

	/** @var array Examples of supported variables */
	public static $variableExamples = [
		'Translate' => '$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$',
		'Company Detail' => '(companyDetail: organizationname)',
		'LBL_LIST_OF_ALL_CHANGES_IN_RECORD' => '(recordChanges: listOfAllChanges)',
		'LBL_LIST_OF_ALL_VALUES_IN_RECORD' => '(recordChanges: listOfAllValues)',
		'Current Date' => '(general: CurrentDate)',
		'Current Time' => '(general: CurrentTime)',
		'CRM Detail View URL' => '(general: CrmDetailViewURL)',
		'Portal Detail View URL' => '(general: PortalDetailViewURL)',
		'Site Url' => '(general: SiteUrl)',
		'Portal Url' => '(general: PortalUrl)',
		'Record Id' => '(general: RecordId)',
		'Record Label' => '(general: RecordLabel)',
		'LBL_HELPDESK_SUPPORT_NAME' => '(general: HelpdeskSupportName)',
		'LBL_HELPDESK_SUPPORT_EMAILID' => '(general: HelpdeskSupportEmail)',
		'Employee Name' => '(employeeDetail: last_name)',
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
			$value = '';
			if (in_array($function, static::$baseFunctions)) {
				$value = $this->$function($params);
			}
			return $value;
		}, $this->content);
		return $this;
	}

	/**
	 * Parsing translations
	 * @param string $params
	 * @return string
	 */
	public function translate($params)
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
}
