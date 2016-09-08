<?php

/**
 * Text Parser Class
 * @package YetiForce.Helpers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TextParser_Helper extends Vtiger_Base_Model
{

	protected $functionMap = ['general', 'companyDetail', 'recordChanges', 'employeeDetail'];

	public static function getFunctionVariables()
	{
		return [
			'Translate' => '(translate: [LBL_YEAR])',
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
	}

	public static function getInstanceById($record, $moduleName)
	{
		$instance = new self();
		$instance->set('record', $record);
		$instance->set('moduleName', $moduleName);
		$instance->set('recordModel', Vtiger_Record_Model::getInstanceById($record, $moduleName));
		return $instance;
	}

	public static function getInstanceByModel(Vtiger_Record_Model $recordModel)
	{
		$instance = new self();
		$instance->set('record', $recordModel->getId());
		$instance->set('moduleName', $recordModel->getModuleName());
		$instance->set('recordModel', $recordModel);
		return $instance;
	}

	public static function getCleanInstance($moduleName = '')
	{
		$instance = new self();
		$instance->set('moduleName', $moduleName);
		return $instance;
	}

	public function setContent($content)
	{
		$this->set('content', $content);
		$this->get('orgContent', $content);
	}

	public function parse()
	{
		$this->parseTranslations();
		if ($this->has('recordModel')) {
			$this->parseFieldsValue();
			$this->parseFieldsLabel();
		}
		$this->parseFunctions();
		return $this->get('content');
	}

	private function parseFieldsValue()
	{
		$content = preg_replace_callback('/\$(\w+)\$/', function ($matches) {
			$value = $matches[0];
			$fieldName = $matches[1];
			if ($this->get('recordModel')->has($fieldName)) {
				$value = $this->get('recordModel')->getDisplayValue($fieldName, $this->get('record'), true);
			}
			return $value;
		}, $this->get('content'));
		$this->set('content', $content);
	}

	private function parseFieldsLabel()
	{
		$content = preg_replace_callback('/\ %([\w\s]+)\% /', function ($matches) {
			$value = $matches[0];
			$variable = $matches[1];
			$translate = vtranslate($variable, $this->get('moduleName'));
			if ($translate != $variable) {
				$value = ' ' . $translate . ' ';
			}
			return $value;
		}, $this->get('content'));
		$this->set('content', $content);
	}

	private function parseFunctions()
	{
		$content = preg_replace_callback('/\((\w+): ([\w\s]+)\)/', function ($matches) {
			$value = $matches[0];
			$variable = $matches[1];
			if (in_array($variable, $this->functionMap)) {
				$value = $this->$variable($matches[2]);
			}
			return $value;
		}, $this->get('content'));
		$this->set('content', $content);
	}

	private function companyDetail($fieldName)
	{
		return Settings_Vtiger_CompanyDetails_Model::getSetting($fieldName);
	}

	public function parseTranslations()
	{
		if ($this->get('withoutTranslations') === true) {
			return $this->get('content');
		}
		$content = preg_replace_callback('/\((\w+): \[([\w\s]+)\]\)/', function ($matches) {
			$value = $matches[0];
			$variable = $matches[2];
			$translate = vtranslate($variable, $this->get('moduleName'));
			if ($translate != $variable) {
				$value = $translate;
			}
			return $value;
		}, $this->get('content'));
		$content = preg_replace_callback('/\((\w+): \[([\w\s\&]+)\|\|\|(\w+)\]\)/', function ($matches) {
			$variable = $matches[2];
			if (key_exists(3, $matches)) {
				return vtranslate($variable, $matches[3]);
			}
			return vtranslate($variable, $this->get('moduleName'));
		}, $content);
		$this->set('content', $content);
		return $content;
	}

	private function general($key)
	{
		switch ($key) {
			case 'CurrentDate':
				if ($this->get('recordModel')->has('assigned_user_id')) {
					$userId = $this->get('recordModel')->get('assigned_user_id');
					$nameList = \includes\fields\Owner::getUserLabel($userId);
					if (empty($nameList)) {
						$recordMeta = vtlib\Functions::getCRMRecordMetadata($this->get('record'));
						$userId = Vtiger_Util_Helper::getCreator($recordMeta['smcreatorid']);
					}
				}
				$ownerObject = CRMEntity::getInstance('Users');
				$ownerObject->retrieveCurrentUserInfoFromFile($userId);

				$date = new DateTimeField(null);
				return $date->getDisplayDate($ownerObject);
			case 'CurrentTime' : return Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s'));
			case 'CrmDetailViewURL' :
				return AppConfig::main('site_URL') . '/index.php?module=' . $this->get('moduleName') . '&view=Detail&record=' . $this->get('record');
			case 'PortalDetailViewURL' :
				$recorIdName = 'id';
				if ($this->get('moduleName') == 'HelpDesk')
					$recorIdName = 'ticketid';
				if ($this->get('moduleName') == 'Faq')
					$recorIdName = 'faqid';
				if ($this->get('moduleName') == 'Products')
					$recorIdName = 'productid';
				return AppConfig::main('PORTAL_URL') . '/index.php?module=' . $this->get('moduleName') . '&action=index&' . $recorIdName . '=' . $this->get('record');
			case 'SiteUrl' : return AppConfig::main('site_URL');
			case 'PortalUrl' : return AppConfig::main('PORTAL_URL');
			case 'ModuleName' : return $this->get('moduleName');
			case 'RecordId' : return $this->get('record');
			case 'HelpdeskSupportName' : return AppConfig::main('HELPDESK_SUPPORT_NAME');
			case 'HelpdeskSupportEmail' : return AppConfig::main('HELPDESK_SUPPORT_EMAIL_REPLY');
			case 'RecordLabel' : return $this->get('recordModel')->getName();
		}
		return $key;
	}

	private function recordChanges($key)
	{
		if (!Users_Privileges_Model::isPermitted($this->get('moduleName'), 'DetailView', $this->get('record'))) {
			return '';
		}

		$vtEntityDelta = new VTEntityDelta();
		$delta = $vtEntityDelta->getEntityDelta($this->get('moduleName'), $this->get('record'));
		unset($delta['modifiedtime']);
		unset($delta['record_id']);
		unset($delta['record_module']);
		if (empty($delta)) {
			return '';
		}
		$value = '';
		switch ($key) {
			case 'listOfAllChanges':
				foreach ($delta as $fieldName => $delta) {
					$fieldModel = $this->get('recordModel')->getModule()->getField($fieldName);
					$oldValue = $this->recordChangesDisplayValue($delta['oldValue'], $fieldModel);
					$currentValue = $this->recordChangesDisplayValue($delta['currentValue'], $fieldModel);
					$value.= '(translate: [' . $fieldModel->getFieldLabel() . '|||' . $this->get('moduleName') . '])' . ' ' .
						'(translate: [LBL_FROM])' . ' ' . $oldValue . ' ' . '(translate: [LBL_TO])' . ' ' . $currentValue . PHP_EOL;
				}
				return $value;
			case 'listOfAllValues':
				foreach ($delta as $fieldName => $delta) {
					$fieldModel = $this->get('recordModel')->getModule()->getField($fieldName);
					if ($fieldModel) {
						$value.= '(translate: [' . $fieldModel->getFieldLabel() . '|||' . $this->get('moduleName') . '])' . ': ' .
							$this->recordChangesDisplayValue($delta['currentValue'], $fieldModel) . PHP_EOL;
					}
				}
				return $value;
		}
		return $key;
	}

	private function recordChangesDisplayValue($value, Vtiger_Field_Model $fieldModel)
	{
		if ($this->get('withoutTranslations') !== true) {
			return $fieldModel->getDisplayValue($value, $this->get('record'), $this->get('recordModel'), true);
		}
		if ($value == '') {
			return '-';
		}
		$translateValue = false;
		switch ($fieldModel->getFieldDataType()) {
			case 'Boolean':
				$value = ($value == 1) ? 'LBL_YES' : 'LBL_NO';
				break;
			case 'Multipicklist':
				$value = explode(' |##| ', $value);
				$trValue = [];
				for ($i = 0; $i < count($value); $i++) {
					$trValue[] = '(translate: [' . $value[$i] . '|||' . $fieldModel->getModuleName() . '])';
				}
				if (is_array($trValue)) {
					$trValue = implode(' |##| ', $trValue);
				}
				$value = str_ireplace(' |##| ', ', ', $trValue);
				break;
			case 'Picklist':
				$value = '(translate: [' . $value . '|||' . $fieldModel->getModuleName() . '])';
				break;
			case 'Time':
				$userModel = Users_Privileges_Model::getCurrentUserModel();
				$value = DateTimeField::convertToUserTimeZone(date('Y-m-d') . ' ' . $value);
				$value = $value->format('H:i:s');
				if ($userModel->get('hour_format') == '12') {
					if ($value) {
						list($hours, $minutes, $seconds) = explode(':', $value);
						$format = '(translate: [PM])';
						if ($hours > 12) {
							$hours = (int) $hours - 12;
						} else if ($hours < 12) {
							$format = '(translate: [AM])';
						}
						//If hours zero then we need to make it as 12 AM
						if ($hours == '00') {
							$hours = '12';
							$format = '(translate: [AM])';
						}
						$value = "$hours:$minutes $format";
					} else {
						$value = '';
					}
				}
				break;
			case 'Tree':
				$template = $fieldModel->getFieldParams();
				$value = Vtiger_Cache::get('recordChangesTreeData' . $template, $value);
				if (!$value) {
					$adb = PearDatabase::getInstance();
					$result = $adb->pquery('SELECT * FROM vtiger_trees_templates_data WHERE templateid = ? && tree = ?', [$template, $value]);
					$parentName = '';
					$module = $fieldModel->getModuleName();
					$name = false;
					if ($adb->getRowCount($result)) {
						$row = $adb->getRow($result);
						if ($row['depth'] > 0) {
							$parenttrre = $row['parenttrre'];
							$pieces = explode('::', $parenttrre);
							end($pieces);
							$parent = prev($pieces);

							$result2 = $adb->pquery('SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? && tree = ?', [$template, $parent]);
							$parentName = $adb->getSingleValue($result2);

							$parentName = '((translate: [' . $parentName . '|||' . $module . '])) ';
						}
						$name = $parentName . '(translate: [' . $row['name'] . '|||' . $module . '])';
					}
					Vtiger_Cache::set('recordChangesTreeData' . $template, $value, $name);
					$value = $name;
				}
				break;
			default:
				$value = $fieldModel->getDisplayValue($value, $this->get('record'), $this->get('recordModel'), true);
				break;
		}
		if (in_array($fieldModel->getFieldDataType(), ['Boolean', 'CurrencyList', 'Languages', 'Salutation', 'Boolean',
				'Boolean', 'Boolean', 'Boolean',])) {
			return '(translate: [' . $value . '])';
		}
		return $value;
	}

	protected static $employee = [];

	private function employeeDetail($fieldName)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUserModel->getId();
		if (!isset(self::$employee[$userId])) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT * FROM vtiger_ossemployees INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossemployees.ossemployeesid INNER JOIN vtiger_ossemployeescf ON vtiger_ossemployeescf.ossemployeesid = vtiger_ossemployees.ossemployeesid WHERE vtiger_crmentity.deleted = ? && vtiger_crmentity.smownerid = ? LIMIT 1;';
			$result = $db->pquery($query, [0, $userId]);
			if ($db->getRowCount($result)) {
				$columns = $db->getRow($result);
				$fields = [];
				$moduleModel = Vtiger_Module_Model::getInstance('OSSEmployees');
				foreach ($moduleModel->getFields() as $fieldModel) {
					if ($columns[$fieldModel->get('column')] != '') {
						$fields[$fieldModel->getFieldName()] = $fieldModel->getDisplayValue($columns[$fieldModel->get('column')], $columns['ossemployeesid'], false, true);
					}
				}
				self::$employee[$userId] = $fields;
			} else {
				return '';
			}
		}
		if (isset(self::$employee[$userId][$fieldName])) {
			return self::$employee[$userId][$fieldName];
		}
		return '';
	}
}
