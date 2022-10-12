<?php

/**
 * Mail serwers module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailServers_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'MailServers';
	/** {@inheritdoc} */
	public $baseTable = 's_#__mail_servers';
	/** {@inheritdoc} */
	public $baseIndex = 'id';

	/** {@inheritdoc} */
	public $listFields = ['name' => 'FL_SUBJECT', 'status' => 'FL_ACTIVE',  'imap_host' => 'FL_IMAP_HOST'];

	/**
	 * Function to get the url for Create view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for edit view of the module.
	 *
	 * @return string - url
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=Edit';
	}

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=Settings&view=List';
	}

	/**
	 * Function verifies if it is possible to sort by given field in list view.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function isSortByName($fieldName)
	{
		return \in_array($fieldName, ['name', 'status', 'imap_host']);
	}

	/** {@inheritdoc} */
	public function getListFields(): array
	{
		if (!isset($this->listFieldModels)) {
			$fields = $this->listFields;
			$fieldObjects = [];
			foreach ($fields as $fieldName => $fieldLabel) {
				$fieldObject = new \App\Base(['name' => $fieldName, 'label' => $fieldLabel]);
				if (!$this->isSortByName($fieldName)) {
					$fieldObject->set('sort', true);
				}
				$fieldObjects[$fieldName] = $fieldObject;
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/** @var string[] Fields name for edit view */
	public $editFields = [
		'name', 'auth_method', 'oauth_provider', 'client_id', 'client_secret', 'redirect_uri_id', 'status', 'visible', 'validate_cert', 'imap_encrypt', 'imap_host', 'imap_port', 'smtp_encrypt', 'smtp_host', 'smtp_port', 'spellcheck', 'ip_check', 'identities_level'
	];

	/**
	 * Editable fields.
	 *
	 * @return array
	 */
	public function getEditableFields(): array
	{
		return $this->editFields;
	}

	/**
	 * Get structure fields.
	 *
	 * @param Settings_AutomaticAssignment_Record_Model|null $recordModel
	 *
	 * @return array
	 */
	public function getEditViewStructure($recordModel = null): array
	{
		$structure = [];
		foreach ($this->editFields as $fieldName) {
			$fieldModel = $this->getFieldInstanceByName($fieldName);
			if ($recordModel && $recordModel->has($fieldName)) {
				$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
			} else {
				$defaultValue = $fieldModel->get('defaultvalue');
				$fieldModel->set('fieldvalue', $defaultValue ?? '');
			}
			$block = $fieldModel->get('blockLabel') ?: '';
			$structure[$block][$fieldName] = $fieldModel;
		}

		return $structure;
	}

	/**
	 * Get block icon.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getBlockIcon($name): string
	{
		$blocks = [
			'BL_BASIC_DATA' => ['icon' => 'yfi-company-detlis'],
			'BL_CONDITIONS' => ['icon' => 'fas fa-filter fa-sm'],
			'BL_ASSIGN_USERS' => ['icon' => 'yfi yfi-users-2'],
			'BL_USER_SELECTION_CONDITIONS' => ['icon' => 'mdi mdi-account-filter-outline'],
		];
		return $blocks[$name]['icon'] ?? '';
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName($name)
	{
		$params = [];
		switch ($name) {
			case 'name':
				$params = [
					'name' => $name,
					'label' => 'FL_SUBJECT',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'auth_method':
				$params = [
					'name' => $name,
					'label' => 'FL_AUTH_METHOD',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::ALNUM,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable(),
					'defaultvalue' => 'basic',
					'picklistValues' => [
						'basic' => \App\Language::translate('LBL_BASIC_AUTH', $this->getName(true)),
						'oauth2' => \App\Language::translate('LBL_OAUTH2', $this->getName(true))
					]
				];
				break;
			case 'status':
				$params = [
					'name' => $name,
					'label' => 'FL_ACTIVE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'imap_host':
				$params = [
					'name' => $name,
					'label' => 'FL_IMAP_HOST',
					'uitype' => 17,
					'typeofdata' => 'C~O',
					'maximumlength' => '128',
					'tooltip' => 'LBL_IMAP_HOST_DESC',
					'purifyType' => \App\Purifier::URL,
					'blockLabel' => 'BL_IMAP',
					'table' => $this->getBaseTable()
				];
				break;
			case 'smtp_host':
				$params = [
					'name' => $name,
					'label' => 'FL_SMTP_HOST',
					'uitype' => 17,
					'typeofdata' => 'V~O',
					'maximumlength' => '128',
					'tooltip' => 'LBL_SMTP_HOST_DESC',
					'purifyType' => \App\Purifier::URL,
					'blockLabel' => 'BL_SMTP',
					'table' => $this->getBaseTable()
				];
				break;
			case 'imap_port':
				$params = [
					'name' => $name,
					'label' => 'FL_IMAP_PORT',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,65535',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'BL_IMAP',
					'table' => $this->getBaseTable()
				];
				break;
			case 'smtp_port':
				$params = [
					'name' => $name,
					'label' => 'FL_SMTP_PORT',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,65535',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'BL_SMTP',
					'table' => $this->getBaseTable()
				];
				break;
			case 'imap_encrypt':
				$params = [
					'name' => $name,
					'label' => 'FL_IMAP_ENCRYPT',
					'uitype' => 16,
					'typeofdata' => 'V~O',
					'maximumlength' => '5',
					'purifyType' => \App\Purifier::STANDARD,
					'blockLabel' => 'BL_IMAP',
					'defaultvalue' => '',
					'table' => $this->getBaseTable()
				];
				$params['picklistValues'] = [
					'ssl' => \App\Language::translate('ssl', $this->getName(true)),
					'tls' => \App\Language::translate('tls', $this->getName(true))
				];
				break;
			case 'smtp_encrypt':
				$params = [
					'name' => $name,
					'label' => 'FL_SMTP_ENCRYPT',
					'uitype' => 16,
					'typeofdata' => 'V~O',
					'maximumlength' => '5',
					'purifyType' => \App\Purifier::STANDARD,
					'blockLabel' => 'BL_SMTP',
					'defaultvalue' => '',
					'table' => $this->getBaseTable()
				];
				$params['picklistValues'] = [
					'ssl' => \App\Language::translate('ssl', $this->getName(true)),
					'tls' => \App\Language::translate('tls', $this->getName(true))
				];
				break;
			case 'session_lifetime':
				$params = [
					'name' => $name,
					'label' => 'FL_SESSION_LIFETIME',
					'uitype' => 7,
					'typeofdata' => 'I~M',
					'maximumlength' => '0,65535',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_OTHER',
					'table' => $this->getBaseTable()
				];
				break;
			case 'validate_cert':
				$params = [
					'name' => $name,
					'label' => 'FL_VALIDATE_CERT',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'spellcheck':
				$params = [
					'name' => $name,
					'label' => 'FL_SPELL_CHECK',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_OTHER',
					'table' => $this->getBaseTable()
				];
				break;
			case 'ip_check':
				$params = [
					'name' => $name,
					'label' => 'FL_IP_CHECK',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_OTHER',
					'table' => $this->getBaseTable()
				];
				break;
			case 'identities_level':
				$params = [
					'name' => $name,
					'label' => 'FL_IDENTITIES_LEVEL',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'BL_OTHER',
					'defaultvalue' => 0,
					'table' => $this->getBaseTable()
				];
				$params['picklistValues'] = [
					0 => \App\Language::translate('identities_level_0', $this->getName(true)),
					1 => \App\Language::translate('identities_level_1', $this->getName(true)),
					2 => \App\Language::translate('identities_level_2', $this->getName(true)),
					3 => \App\Language::translate('identities_level_3', $this->getName(true)),
					4 => \App\Language::translate('identities_level_4', $this->getName(true))
				];
				break;
			case 'visible':
				$params = [
					'name' => $name,
					'label' => 'FL_VISIBLE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'oauth_provider':
				$params = [
					'name' => $name,
					'label' => 'FL_OAUTH_PROVIDER',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'BL_BASE',
					'defaultvalue' => '',
					'table' => $this->getBaseTable(),
					'picklistValues' => array_map(fn ($provider) => \App\Language::translate($provider->getLabel(), $this->getName(true)), \App\Integrations\OAuth::getProviders())
				];
				break;
			case 'redirect_uri_id':
				$params = [
					'name' => $name,
					'label' => 'FL_REDIRECT_URI_ID',
					'uitype' => 16,
					'typeofdata' => 'I~M',
					'maximumlength' => '2147483647',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'BL_BASE',
					'defaultvalue' => '',
					'table' => $this->getBaseTable(),
					'picklistValues' => array_map(fn ($service) => $service['name'], \App\Integrations\Services::getByType(\App\Integrations\Services::OAUTH))
				];
				break;
			case 'client_id':
				$params = [
					'name' => $name,
					'label' => 'FL_CLIENT_ID',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'BL_BASE',
					'table' => $this->getBaseTable()
				];
				break;
			case 'client_secret':
				$params = [
					'name' => $name,
					'label' => 'FL_CLIENT_SECRET',
					'uitype' => 99,
					'typeofdata' => 'V~M',
					'maximumlength' => '255',
					'purifyType' => 'raw',
					'blockLabel' => 'BL_BASE',
					'fromOutsideList' => true,
					'table' => $this->getBaseTable()
				];
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($this->getName(true), $params, $name) : null;
	}
}
