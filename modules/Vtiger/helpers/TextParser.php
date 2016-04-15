<?php

/**
 * Text Parser Class
 * @package YetiForce.Helpers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TextParser_Helper extends Vtiger_Base_Model
{

	protected $functionMap = ['general', 'translate', 'companyDetail'];

	public static function getFunctionVariables()
	{
		return [
			'Translate' => '(translate: LBL_YEAR)',
			'Company Detail' => '(companyDetail: organizationname)',
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

	public function setContent($content)
	{
		$this->set('content', $content);
		$this->get('orgContent', $content);
	}

	public function parse()
	{
		$this->parseFieldsValue();
		$this->parseFieldsLabel();
		$this->parseFunctions();
		return $this->get('content');
	}

	public function parseFieldsValue()
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

	public function parseFieldsLabel()
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

	public function parseFunctions()
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

	public function companyDetail($fieldName)
	{
		return Settings_Vtiger_CompanyDetails_Model::getSetting($fieldName);
	}

	public function translate($key)
	{
		return vtranslate($key, $this->get('moduleName'));
	}

	public function general($key)
	{
		switch ($key) {
			case 'CurrentDate':
				if ($this->get('recordModel')->has('assigned_user_id')) {
					$userId = $this->get('recordModel')->get('assigned_user_id');
					$nameList = Vtiger_Functions::getCRMRecordLabels('Users', [$userId]);
					$diffIds = array_diff([$userId], array_keys($nameList));
					if ($diffIds) {
						$recordMeta = Vtiger_Functions::getCRMRecordMetadata($this->get('record'));
						$userId = Vtiger_Util_Helper::getCreator($recordMeta['smcreatorid']);
					}
				}
				$ownerObject = new Users();
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
			case 'HelpdeskSupportEmail' : return AppConfig::main('HELPDESK_SUPPORT_EMAIL_ID');
			case 'RecordLabel' : return $this->get('recordModel')->getName();
		}
		return $key;
	}
}
