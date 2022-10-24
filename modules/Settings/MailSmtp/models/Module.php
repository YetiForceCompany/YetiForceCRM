<?php

/**
 * MailSmtp module model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MailSmtp_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 's_#__mail_smtp';
	public $baseIndex = 'id';
	public $listFields = ['name' => 'LBL_NAME', 'host' => 'LBL_HOST', 'port' => 'LBL_PORT', 'username' => 'LBL_USERNAME', 'from_email' => 'LBL_FROM_EMAIL', 'default' => 'LBL_DEFAULT'];
	public $name = 'MailSmtp';

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=MailSmtp&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string URL
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=MailSmtp&parent=Settings&view=Edit';
	}

	public static function getSmtpNames()
	{
		return (new \App\Db\Query())->select(['id', 'name'])->from('s_#__mail_smtp')->all(\App\Db::getInstance('admin'));
	}

	/** @var string[] Fields name for edit view */
	public $editFields = [
		'name', 'mailer_type', 'default', 'mail_account', 'secure', 'host', 'port', 'username', 'password', 'from_name', 'from_email', 'reply_to', 'authentication', 'individual_delivery', 'priority', 'confirm_reading_to', 'organization', 'unsubscribe', 'options', 'save_send_mail', 'imap_host', 'imap_port', 'imap_username', 'imap_password', 'imap_folder', 'imap_validate_cert'
	];

	/**
	 * Get block icon.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public function getBlockIcon($name): string
	{
		return '';
	}

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name)
	{
		$moduleName = $this->getName(true);
		// $fieldsLabel = $this->getEditFields();
		$params = [];
		switch ($name) {
			case 'mail_account':
				$params = ['column' => $name, 'name' => $name,  'displaytype' => 1, 'typeofdata' => 'I~M', 'presence' => 0, 'isEditableReadOnly' => false, 'defaultvalue' => '0'];
				$params['uitype'] = 10;
				$params['label'] = 'FL_MAIL_ACCOUNT';
				$params['referenceList'] = ['MailAccount'];
				$params['blockLabel'] = 'BL_BASE';
				$params['fieldparams'] = [
					'searchParams' => '[[["mailaccount_status","e","PLL_ACTIVE"]]]',
				];
				break;
			case 'name':
				$params = [
					'name' => $name,
					'label' => 'LBL_NAME',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '255',
					'defaultvalue' => '',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'mailer_type':
				$params = [
					'name' => $name,
					'label' => 'LBL_MAILER_TYPE',
					'uitype' => 16,
					'typeofdata' => 'V~M',
					'maximumlength' => '10',
					'purifyType' => \App\Purifier::ALNUM,
					'blockLabel' => 'BL_BASE',
					'defaultvalue' => 'yfsmtp',
					'picklistValues' => [
						'yfsmtp' => \App\Language::translate('LBL_SMTP_MAIL_ACCOUNT', $moduleName),
						'smtp' => \App\Language::translate('LBL_SMTP', $moduleName),
						'sendmail' => \App\Language::translate('LBL_SENDMAIL', $moduleName),
						'mail' => \App\Language::translate('LBL_MAIL', $moduleName),
						'qmail' => \App\Language::translate('LBL_QMAIL', $moduleName),
					]
				];
				break;
			case 'default':
				$params = [
					'name' => $name,
					'label' => 'LBL_DEFAULT',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'defaultvalue' => 0,
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'host':
				$params = [
					'name' => $name,
					'label' => 'LBL_HOST',
					'uitype' => 17,
					'typeofdata' => 'V~O',
					'maximumlength' => '240',
					'purifyType' => \App\Purifier::URL,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'port':
				$params = [
					'name' => $name,
					'label' => 'LBL_PORT',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,65535',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'authentication':
				$params = [
					'name' => $name,
					'label' => 'LBL_AUTHENTICATION',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'username':
				$params = [
					'name' => $name,
					'label' => 'LBL_USERNAME',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'password':
				$params = [
					'name' => $name,
					'label' => 'LBL_PASSWORD',
					'uitype' => 99,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => 'raw',
					'blockLabel' => 'BL_BASE',
					'fromOutsideList' => true
				];
				break;
			case 'individual_delivery':
				$params = [
					'name' => $name,
					'label' => 'LBL_INDIVIDUAL_DELIVERY',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'BL_BASE',
					'tooltip' => 'LBL_INDIVIDUAL_DELIVERY_INFO'
				];
				break;
			case 'secure':
				$params = [
					'name' => $name,
					'label' => 'LBL_SECURE',
					'uitype' => 16,
					'typeofdata' => 'V~O',
					'maximumlength' => '5',
					'purifyType' => \App\Purifier::STANDARD,
					'blockLabel' => 'BL_BASE',
					'defaultvalue' => ''
				];
				$params['picklistValues'] = [
					'ssl' => \App\Language::translate('LBL_SSL', $moduleName),
					'tls' => \App\Language::translate('LBL_TLS', $moduleName)
				];
				break;
			case 'from_name':
				$params = [
					'name' => $name,
					'label' => 'LBL_FROM_NAME',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'from_email':
				$params = [
					'name' => $name,
					'label' => 'LBL_FROM_EMAIL',
					'uitype' => 13,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => 'Email',
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'reply_to':
				$params = [
					'name' => $name,
					'label' => 'LBL_REPLY_TO',
					'uitype' => 13,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => 'Email',
					'blockLabel' => 'BL_BASE'
				];
				break;
			case 'priority':
				$params = [
					'name' => $name,
					'label' => 'LBL_MAIL_PRIORITY',
					'uitype' => 16,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::ALNUM_EXTENDED,
					'blockLabel' => 'LBL_ADDITIONAL_HEADERS',
					'defaultvalue' => '',
					'picklistValues' => [
						'normal' => \App\Language::translate('LBL_NORMAL', $moduleName),
						'non-urgent' => \App\Language::translate('LBL_NO_URGENT', $moduleName),
						'urgent' => \App\Language::translate('LBL_URGENT', $moduleName)
					]
				];
				break;
			case 'confirm_reading_to':
				$params = [
					'name' => $name,
					'label' => 'LBL_CONFIRM_READING_TO',
					'uitype' => 13,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => 'Email',
					'blockLabel' => 'LBL_ADDITIONAL_HEADERS'
				];
				break;
			case 'organization':
				$params = [
					'name' => $name,
					'label' => 'LBL_ORGANIZATION',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'LBL_ADDITIONAL_HEADERS'
				];
				break;
			case 'unsubscribe':
				$params = [
					'label' => 'LBL_UNSUBSCIBE',
					'uitype' => 33,
					'maximumlength' => '255',
					'typeofdata' => 'V~O',
					'purifyType' => \App\Purifier::TEXT,
					'createTags' => true,
					'tooltip' => 'LBL_UNSUBSCRIBE_INFO',
					'picklistValues' => [],
					'blockLabel' => 'LBL_ADDITIONAL_HEADERS'
				];
				break;
			case 'options':
				$params = [
					'label' => 'LBL_OPTIONS',
					'uitype' => 21,
					'maximumlength' => '6500',
					'typeofdata' => 'V~O',
					'purifyType' => \App\Purifier::TEXT,
					'tooltip' => 'LBL_OPTIONS_INFO',
					'blockLabel' => 'LBL_ADDITIONAL_HEADERS'
				];
				break;
			case 'save_send_mail':
				$params = [
					'name' => $name,
					'label' => 'LBL_SAVE_SEND_MAIL',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'defaultvalue' => 0,
					'purifyType' => \App\Purifier::BOOL,
					'tooltip' => 'LBL_SAVE_SEND_MAIL_INFO',
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE'
				];
				break;
			case 'imap_host':
				$params = [
					'name' => $name,
					'label' => 'LBL_HOST',
					'uitype' => 17,
					'typeofdata' => 'V~O',
					'maximumlength' => '240',
					'purifyType' => \App\Purifier::URL,
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE'
				];
				break;
			case 'imap_port':
				$params = [
					'name' => $name,
					'label' => 'LBL_PORT',
					'uitype' => 7,
					'typeofdata' => 'I~O',
					'maximumlength' => '0,65535',
					'purifyType' => \App\Purifier::INTEGER,
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE'
				];
				break;
			case 'imap_validate_cert':
				$params = [
					'name' => $name,
					'label' => 'LBL_VALIDATE_CERT',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE'
				];
				break;
			case 'imap_username':
				$params = [
					'name' => $name,
					'label' => 'LBL_USERNAME',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE'
				];
				break;
			case 'imap_password':
				$params = [
					'name' => $name,
					'label' => 'LBL_PASSWORD',
					'uitype' => 99,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => 'raw',
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE',
					'fromOutsideList' => true
				];
				break;
			case 'imap_folder':
				$params = [
					'name' => $name,
					'label' => 'LBL_SEND_FOLDER',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'blockLabel' => 'LBL_SAVE_SENT_MESSAGE'
				];
				break;
			default:
				break;
		}

		return $params ? \Vtiger_Field_Model::init($this->getName(true), $params, $name) : null;
	}

	public function dependency(string $field = '')
	{
		$dependency = [
			//hide if only one condition is valid
			'mail_account' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'n']], 'default' => 0],
			'host' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e']], 'default' => ''],
			'port' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e']], 'default' => 0],
			'authentication' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e']], 'default' => 1],
			'username' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e']], 'default' => ''],
			'password' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e']], 'default' => ''],
			'secure' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e']], 'default' => ''],
			'imap_host' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e'], 'save_send_mail' => ['value' => 1, 'operator' => 'n']], 'default' => 0],
			'imap_port' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e'], 'save_send_mail' => ['value' => 1, 'operator' => 'n']], 'default' => ''],
			'imap_username' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e'], 'save_send_mail' => ['value' => 1, 'operator' => 'n']], 'default' => ''],
			'imap_password' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e'], 'save_send_mail' => ['value' => 1, 'operator' => 'n']], 'default' => ''],
			'imap_validate_cert' => ['condition' => ['mailer_type' => ['value' => 'yfsmtp', 'operator' => 'e'], 'save_send_mail' => ['value' => 1, 'operator' => 'n']], 'default' => 0],
			'imap_folder' => ['condition' => ['save_send_mail' => ['value' => 1, 'operator' => 'n']], 'default' => '']
		];

		return $field ? ($dependency[$field] ?? '') : $dependency;
	}

	/**
	 * Get structure fields.
	 *
	 * @param Settings_MailSmtp_Record_Model|null $recordModel
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
				$defaultValue = $fieldModel->get('defaultvalue') ?? '';
				$fieldModel->set('fieldvalue', $defaultValue);
				if ($recordModel) {
					$recordModel->set($fieldName, $defaultValue);
				}
			}
			$block = $fieldModel->get('blockLabel') ?: '';
			$structure[$block][$fieldName] = $fieldModel;
		}

		return $structure;
	}
}
